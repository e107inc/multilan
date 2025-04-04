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
        $files = e107::getFile()->get_files($path, '\.php$', 'standard', 2);
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
        $lines = file($file, FILE_IGNORE_NEW_LINES); // Keep empty lines for parsing
        $outputLines = [];
        $headerLines = [];
        $active = [];
        $commented = [];
        $inMultiLineComment = false;
        $headerDone = false;
        $bodyIndex = 0;

        foreach ($lines as $index => $line)
        {
            $trimmedLine = trim($line);

            // Collect the initial <?php line and header comments
            if ($index === 0 && preg_match('/^\s*<\?php/', $trimmedLine))
            {
                $headerLines[] = '<?php';
                continue;
            }

            // Track multi-line comment state
            if (preg_match('/^\s*\/\*/', $trimmedLine) && !$inMultiLineComment)
            {
                $inMultiLineComment = true;
                if (!$headerDone)
                {
                    $headerLines[] = $line;
                }
                continue;
            }
            if (preg_match('/\s*\*\/\s*$/', $trimmedLine) && $inMultiLineComment)
            {
                $inMultiLineComment = false;
                if (!$headerDone)
                {
                    $headerLines[] = $line;
                }
                continue;
            }

            // Inside header multi-line comment
            if ($inMultiLineComment && !$headerDone)
            {
                $headerLines[] = $line;
                continue;
            }

            // End of header when we hit the first non-comment line
            if (!$headerDone && !$inMultiLineComment && !preg_match('/^\s*\/\//', $trimmedLine) && !empty($trimmedLine))
            {
                $headerDone = true;
            }

            // Process lines after header, excluding blank lines
            if ($headerDone && !empty($trimmedLine))
            {
                // Match single-line commented define()
                if (preg_match("/\s*\/\/\s*define\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)\s*;/", $trimmedLine, $match))
                {
                    $commented[$match[1]] = $match[2];
                    $outputLines['body'][$bodyIndex] = "    // '$match[1]' => \"$match[2]\",";
                    $bodyIndex++;
                }
                // Match multi-line commented define() within /* */
                elseif ($inMultiLineComment && preg_match("/\s*define\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)\s*;/", $trimmedLine, $match))
                {
                    $commented[$match[1]] = $match[2];
                    $outputLines['body'][$bodyIndex] = "    // '$match[1]' => \"$match[2]\",";
                    $bodyIndex++;
                }
                // Match active define() with optional inline comment
                elseif (!$inMultiLineComment && preg_match("/^\s*define\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)\s*;(\s*\/\/.*)?$/", $trimmedLine, $match))
                {
                    $active[$match[1]] = $match[2];
                    $inlineComment = isset($match[3]) ? $match[3] : '';
                    $outputLines['body'][$bodyIndex] = "    '$match[1]' => \"$match[2]\",$inlineComment";
                    $bodyIndex++;
                }
            }
        }

        // Update the header with the new year range if "2008-2013" is found
        $headerContent = implode("\n", $headerLines);
        if (preg_match('/2008-2013/', $headerContent))
        {
            $headerContent = str_replace('2008-2013', '2008-2025', $headerContent);
            self::log("Updated header: Replaced '2008-2013' with '2008-2025' in $file");
        }

        // Debug: Log parse results
        self::log("Active defines: " . json_encode($active));
        self::log("Commented defines: " . json_encode($commented));

        return ['header' => $headerContent, 'body' => $outputLines['body'] ?? []];
    }

    /**
     * Log conversion results or issues to a file in the plugin directory
     * @param string $message Message to log
     * @return void
     */
    private static function log($message)
    {
        $logFile = __DIR__ . '/multilan.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}