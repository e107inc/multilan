<?php

class multilan
{
    /**
     * Convert a single language file from define() to array format
     * @param string $path Full path to the language file
     * @return bool Success status
     */
    public static function convertFile($path)
    {
        self::log("Processing file: $path");

        if (!is_readable($path))
        {
            self::log("Cannot read file: $path");
            return false;
        }

        // Check if the file already returns an array
        $content = @include($path);
        if (is_array($content))
        {
            self::log("Skipped: $path (already returns an array)");
            return false;
        }

        // Parse the file and build the output
        $outputLines = self::parseLanFile($path);

        if (empty($outputLines['body']))
        {
            self::log("$path: No define() calls found.");
            return false;
        }

        // Build the final content with header and body
        $newContent = $outputLines['header'] . "\nreturn [\n" . implode("\n", $outputLines['body']) . "\n];\n";

        // Save the updated content
        if (file_put_contents($path, $newContent) !== false)
        {
            self::log("Converted: $path");
            return true;
        }

        self::log("Failed to convert: $path");
        return false;
    }

    /**
     * Convert all language files in a specific plugin's language folder from define() to array format
     * @param string $pluginFolder Plugin folder name (e.g., 'forum', 'news')
     * @return void
     */
    public static function convertPlugin($pluginFolder)
    {
        $path = e_PLUGIN . $pluginFolder . '/languages/';
        self::convertFilesInPath($path, $pluginFolder);
    }

    /**
     * Convert all language files in a specific theme's language folder from define() to array format
     * @param string $themeFolder Theme folder name (e.g., 'bootstrap3', 'voux')
     * @return void
     */
    public static function convertTheme($themeFolder)
    {
        $path = e_THEME . $themeFolder . '/languages/';
        self::convertFilesInPath($path, $themeFolder);
    }

    /**
     * Convert all language files in a given path from define() to array format
     * @param string $path Full path to the language directory
     * @param string $folderName Name of the plugin or theme folder for logging
     * @return void
     */
    private static function convertFilesInPath($path, $folderName)
    {
        $fl = e107::getFile();
        $fl->setMode('full');
        $files = $fl->get_files($path, '\.php$', 'standard', 2);

        $converted = 0;
        $skipped = 0;

        foreach ($files as $file)
        {
            if (self::convertFile($file))
            {
                $converted++;
            }
            else
            {
                $skipped++;
            }
        }

        self::log("\nSummary for $folderName:\nConverted: $converted files\nSkipped: $skipped files");
    }

    /**
     * Parse a language file and build output lines, preserving commented define() positions, header comments, and inline comments
     * @param string $file Full path to the language file
     * @return array Array with 'header' (top comments) and 'body' (array lines)
     */

	private static function parseLanFile($file)
	{
		$lines = file($file);
		$headerLines = [];
		$active = [];
		$commented = [];
		$inMultiLineComment = false;
		$headerDone = false;
		$outputLines = ['body' => []];
		$lineCount = count($lines);
		$i = 0;

		while ($i < $lineCount) {
			$currentLine = $lines[$i];
			$trimmedLine = trim($currentLine);

			// Handle header section
			if (!$headerDone) {
				if ($i === 0 && strpos($trimmedLine, '<?php') === 0) {
					$headerLines[] = $currentLine;
					$i++;
					continue;
				}
				if (!$inMultiLineComment && preg_match('/^\s*\/\*/', $trimmedLine)) {
					$inMultiLineComment = true;
				}
				if ($inMultiLineComment || preg_match('/^\s*(\/\/|#)/', $trimmedLine) || empty($trimmedLine)) {
					$headerLines[] = $currentLine;
					if ($inMultiLineComment && preg_match('/\*\//', $trimmedLine)) {
						$inMultiLineComment = false;
					}
					$i++;
					continue;
				} else {
					$headerDone = true;
				}
			}

			// Skip fully commented lines, including commented-out array entries
			if (preg_match('/^\s*(\/\/|#)/', $trimmedLine) || empty($trimmedLine)) {
				$i++;
				continue;
			}

			// Detect define statement start
			if (preg_match('/^define\s*\(\s*[\'"](.+?)[\'"]\s*,\s*([\'"])(.*)$/', $trimmedLine, $matches)) {
				$constName = $matches[1];
				$quoteType = $matches[2];
				$defineValuePart = $matches[3];
				$defineEndPattern = '/' . preg_quote($quoteType) . '\s*\)\s*;\s*(?:\/\/.*|#.*)?$/';

				$defineValueLines = [];

				if (preg_match($defineEndPattern, $defineValuePart)) {
					// Single-line define
					$defineValuePartCleaned = preg_replace($defineEndPattern, '', $defineValuePart);
					$defineValueLines[] = trim($defineValuePartCleaned);
				} else {
					// Multi-line define
					$defineValueLines[] = $defineValuePart;
					$i++;
					while ($i < $lineCount) {
						$nextLine = $lines[$i];
						$nextLineTrimmed = trim($nextLine);

						// Completely ignore commented lines in between
						if (preg_match('/^\s*(\/\/|#)/', $nextLineTrimmed) || empty($nextLineTrimmed)) {
							$i++;
							continue;
						}

						if (preg_match($defineEndPattern, $nextLine)) {
							$lineTextWithoutEnd = preg_replace($defineEndPattern, '', $nextLine);
							$defineValueLines[] = trim($lineTextWithoutEnd);
							break;
						} else {
							$defineValueLines[] = $nextLine;
						}
						$i++;
					}
				}

				$finalValue = implode("\n", $defineValueLines);
				$finalValue = trim($finalValue);
				$finalValue = stripslashes($finalValue);

				$active[$constName] = $finalValue;
				$outputLines['body'][] = "    '{$constName}' => \"" . str_replace('"', '\"', $finalValue) . "\",";
			}

			$i++;
		}

		$headerContent = implode("", $headerLines);
		if (strpos($headerContent, '2008-2013') !== false) {
			$headerContent = str_replace('2008-2013', '2008-2025', $headerContent);
			self::log("Updated header year in file: $file");
		}

		self::log("Active defines extracted:\n" . json_encode($active, JSON_PRETTY_PRINT));

		return [
			'header' => $headerContent,
			'body'   => $outputLines['body']
		];
	}





    /**
     * Log conversion results or issues to a file in the plugin directory
     * @param string $message Message to log
     * @return void
     */
    public static function log($message)
    {
        $logFile = __DIR__ . '/multilan.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}