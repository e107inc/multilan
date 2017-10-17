<?php
/*
	require_once('translate.class.php');
	$BingTranslator = new BingTranslator('AppID', 'secret');
	$translation = $BingTranslator->getTranslation('en', 'de', 'What time does the hotel close in the evening?');
	echo $translation;

 */



	class bingTranslate
	{
		private $_client_id     = '';
		private $_client_secret = '';
		private $_type          = 'client_credentials';
		private $_token         = '';
		private $_url           = 'http://api.microsofttranslator.com';
		private $_urlPost       = 'http://api.microsofttranslator.com/v2/Http.svc/TranslateArray';
		private $_method        = 'post';
		private $_paragraphs    = false;
		private $_debug         = false;
		private $_xml           = false;
		private $_tag           = null;
		private $_maxChars      = 5500;

		public function __construct()
		{

			$this->_client_id = e107::pref('multilan', 'bing_client_id');
			$this->_client_secret = e107::pref('multilan', 'bing_client_secret');

		}


		/**
		 * @param string $value
		 * @param bool   $xml - enable if testing a returned XML from Bing.
		 */
		public function test($value="Hello World!", $xml=false)
		{
			$this->_debug = true;

			 $languageCode = 'fr';

			 $this->_xml = $xml;

			 $newValue = $this->getTranslation('en', $languageCode, $value, true);

			  echo "<h3>Submitted</h3>";
			 print_a($value);

			 echo "<h3>Result</h3>";
			 print_a($newValue);

		}


		public function supportedLanguages()
		{
			return array(
				'ar'    => 'Arabic',
				'bg'    => 'Bulgarian',
				'ca'    => 'Catalan',
				'zh-CHS' => 'Chinese Simplified',
				'zh-CHT' => 'Chinese Traditional',
				'hr'    => 'Croatian',
				'cs'    => 'Czech',
				'da'    => 'Danish',
				'nl'    => 'Dutch',
				'en'    => 'English',
				'et'    => 'Estonian',
				'fi'    => 'Finnish',
				'fr'    => 'French',
				'de'    => 'German',
				'el'    => 'Greek',
				'he'    => 'Hebrew',
				'hi'    => 'Hindi',
				'ht'    => 'Haitian Creole',
				'hu'    => 'Hungarian',
				'id'    => 'Indonesian',
				'it'    => 'Italian',
				'ja'    => 'Japanese',
				'ko'    => 'Korean',
				'lv'    => 'Latvian',
				'lt'    => 'Lithuanian',
				'ms'    => 'Malay',
				'mt'    => 'Maltese',
				'no'    => 'Norwegian',
				'fa'    => 'Persian',
				'pl'    => 'Polish',
				'pt'    => 'Portuguese',
				'ro'    => 'Romanian',
				'ru'    => 'Russian',
				'sk'    => 'Slovak',
				'sl'    => 'Slovenian',
				'es'    => 'Spanish',
				'sv'    => 'Swedish',
				'th'    => 'Thai',
				'tr'    => 'Turkish',
				'uk'    => 'Ukrainian',
				'ur'    => 'Urdu',
				'vi'    => 'Vietnamese',
				'cy'    => 'Welsh'

			);

		}
/*
		public function getResponse($url)
		{

			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->getToken(), 'Content-Type: text/xml'));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($curl);

			curl_close($curl);

			return $response;
		}
*/

		function getToken()
		{
		    $url = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';
		    $ch = curl_init();
		    $data_string = json_encode('{body}');
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		            'Content-Type: application/json',
		            'Content-Length: ' . strlen($data_string),
		            'Ocp-Apim-Subscription-Key: ' . $this->_client_secret
		        )
		    );
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_HEADER, false);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    $strResponse = curl_exec($ch);
		    curl_close($ch);
		    return $strResponse;
		}


/*

		public function getTokenOld()
		{

			if(!empty($this->_token))
			{
				$this->log("No Token Pref Found");
				return $this->_token;
			}

			$curl = curl_init();
			$request = 'grant_type='.urlencode($this->_type).'&scope='.urlencode($this->_url).'&client_id='.urlencode($this->_client_id ).'&client_secret='.urlencode($this->_client_secret);

			curl_setopt($curl, CURLOPT_URL, 'https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/');
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($curl);

		//	if(empty($response))
			{
				$this->log("getToken() Response:\n".$response."\n");
			}

			curl_close($curl);

			$resp = json_decode($response);
			$this->_token = $resp->access_token;

			return $resp->access_token;
		}
*/

		public function getTranslation($fromLanguage, $toLanguage, $text, $returnArray=false, $tag=null)
		{

			if(!empty($tag))
			{
				$this->_tag = $tag;
			}

			$this->log("-----------------------------");

			if(empty($text))
			{
				$this->log("Translation text was empty");
				return false;
			}



			if($this->_method == 'get') // unused.
			{
			//	$response = $this->getResponse($this->getURL($fromLanguage, $toLanguage, $text));
			//	$response = strip_tags($response);
			//	$decoded = html_entity_decode($response, null, 'UTF-8');
			}
			else // 'post'
			{
				if($this->_xml === true)
				{
					$textArray = trim($text);
				}
				elseif(is_array($text))
				{
					$textArray = $text;
					$arrayKeys = array_keys($text);



					if($this->_debug === true)
					{

					}
				}
				else
				{
					$textArray = $this->getParagraphs($text);
				}

				$textCount = count($textArray);

				if($textCount > 2000)
				{
					$this->log("More than 2000 translation elements detected.");
					return false;
				}
				else
				{
					$this->log($textCount." terms require translation.");
				}



				if($this->_xml === true)
				{
					$requestXml = $textArray;
				}
				else
				{
					$requestXml = $this->createReqXML($fromLanguage, $toLanguage, 'text/html', $textArray);
				}

				if($this->_debug === true)
				{
					echo "<h3>createReqXML</h3>";
					var_dump($requestXml);
				}

				$transArray = $this->getXMLResponse($requestXml);

				if($this->_debug === true)
				{
					echo "<h3>getXMLResponse</h3>";
					var_dump($transArray);
				}

			//	$transArray = $textArray;

				if($this->_paragraphs == true)
				{
					$decoded = '';
					foreach($transArray as $val)
					{
						$decoded .= "<p>".$val."</p>\n";
					}
				}
				else
				{
					$decoded = implode("\n", $transArray);
				}

				if($returnArray === true)
				{

					if(!empty($arrayKeys)) // keep original array keys intact.
					{
						$newArray = array();
						foreach($transArray as $k=>$v)
						{
							$newkey = $arrayKeys[$k];

							$newArray[$newkey] = $v;
						}

						return $newArray;
					}

					return $transArray;
				}
			}





			return $decoded;

		}


		/**
		 * Generated 'get' URL request.
		 * @param $fromLanguage
		 * @param $toLanguage
		 * @param $text
		 * @return string
		 *//*
		public function getURL($fromLanguage, $toLanguage, $text)
		{
			$params = "text=" . urlencode($text) . "&to=" . $toLanguage . "&from=" . $fromLanguage . "&appId=Bearer+" . $accessToken;
$translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
			return 'http://api.microsofttranslator.com/v2/Http.svc/Translate?text='.urlencode($text).'&to='.$toLanguage.'&from='.$fromLanguage.'&contentType=text/plain';
		}*/






	    /*
	     * Create Request XML Format.
	     *
	     * @param string $fromLanguage   Source language Code.
	     * @param string $toLanguage     Target language Code.
	     * @param string $contentType    Content Type.
	     * @param string $inputStrArr    Input String Array.
	     *
	     * @return string.
	     */
	    function createReqXML($fromLanguage,$toLanguage,$contentType,$inputStr)
	    {
		    if(is_string($inputStr))
			{
				$data = array($inputStr);
			}
			else
			{
				$data = $inputStr;
			}



	        $requestXml = "<TranslateArrayRequest>".
	            "<AppId/>".
	            "<From>$fromLanguage</From>".
	            "<Options>" .
	             "<Category xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
	              "<ContentType xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">$contentType</ContentType>" .
	              "<ReservedFlags xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
	              "<State xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
	              "<Uri xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
	              "<User xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
	            "</Options>" .
	            "<Texts>";

	         $count = 0;
	         $charCount = 0;
	        foreach ($data as $str)
	        {

	            if($charCount > $this->_maxChars || $count > 10000)
				{
					$this->log("Max Character Count (".$this->_maxChars.") Exceeded - Skipping remainder.");
					break;
				}

	            $str = htmlspecialchars($str);
	            $charCount = $charCount + strlen($str);
	            $requestXml .=  "<string xmlns=\"http://schemas.microsoft.com/2003/10/Serialization/Arrays\">$str</string>" ;

				$count++;
	        }

	        $requestXml .= "</Texts>".
	            "<To>$toLanguage</To>" .
	          "</TranslateArrayRequest>";

			$this->log("Character Count: ".$charCount);

	        return $requestXml;
	    }

		/**
		 * Create Request XML Format.
		 *
		 * @param string $fromLanguage   Source language Code.
		 * @param string $toLanguage     Target language Code.
		 * @param string $contentType    Content Type.
		 * @param string $inputStrArr    Input String Array.
		 *
		 * @return string.
		 *//*
		function createXMLRequest($fromLanguage, $toLanguage,  $inputStr, $contentType)
		{

			if(is_string($inputStr))
			{
				$data = array($inputStr);
			}
			else
			{
				$data = $inputStr;
			}

		//	$inputStr = urlencode($inputStr);

			//Create the XML string for passing the values.
			$requestXml = "<TranslateArrayRequest>".
				"<AppId/>".
				"<From>$fromLanguage</From>".
				"<Options>" .
				"<Category xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
				"<ContentType xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">".$contentType."</ContentType>" .
				"<ReservedFlags xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
				"<State xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
				"<Uri xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
				"<User xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
				"</Options>" .
				"<Texts>";

				$count= 0;
				foreach($data as $str)
				{
					$str = htmlspecialchars($str);
					$requestXml .=  "<string xmlns=\"http://schemas.microsoft.com/2003/10/Serialization/Arrays\">".$str."</string>" ;

				}

			$requestXml .= "</Texts>".
				"<To>$toLanguage</To>" .
				"</TranslateArrayRequest>";


		//	print_a($requestXml);
			return $requestXml;
		}
*/

		private function log($message)
		{
			file_put_contents(e_LOG."multilan_bing.log", "\n".date('r')."\t\t".$this->_tag."\t\t".$message, FILE_APPEND);

		}


		/*
         * Create and execute the HTTP CURL request.
	     *
	     * @param string $url        HTTP Url.
	     * @param string $authHeader Authorization Header string.
	     * @param string $postData   Data to post.
	     *
	     * @return string.
	     *
	     */
	    function curlRequest($url, $authHeader, $postData='')
	    {

	        $ch = curl_init();

	        curl_setopt ($ch, CURLOPT_URL, $url);
	        curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml")); //; charset=UTF-8
	        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);

	        if($postData)
	        {
	            curl_setopt($ch, CURLOPT_POST, true);
	            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	        }

	        $curlResponse = curl_exec($ch);
	        $curlErrno = curl_errno($ch);

	        if ($curlErrno)
	        {
	            $curlError = curl_error($ch);
	            $this->log($curlError);
	            throw new Exception($curlError);
	        }

	        curl_close($ch);

	        return $curlResponse;
	    }



		function getXMLResponse($requestXml='')
		{

			if(empty($requestXml))
			{
				if($this->_debug=== true)
				{

					echo 'requestXml was empty';
				}

				$this->log('requestXml was empty');
				return '';
			}

			$ret = array();

			$authHeader = "Authorization: Bearer ". $this->getToken();

			if($this->_debug === true)
			{
				echo "<br />".$authHeader;
			}

/*			$url = $this->_urlPost;
			$ch = curl_init();

			curl_setopt ($ch, CURLOPT_URL, $url);
		//	curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml"));
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, array ($authHeader,'Content-Type: application/xml; charset=UTF-8' ) );
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, False);

			if($postData)
			{
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			}

			$curlResponse = curl_exec($ch);
			*/


			$curlResponse = $this->curlRequest($this->_urlPost, $authHeader, $requestXml);

			if($this->_debug === true)
			{
				echo print_a($curlResponse);
			}

			if(empty($curlResponse))
			{
				$this->log("Empty XML Response from Bing");
				return false;
			}

			try {

				// print_a($curlResponse);
				$xml = e107::getXml();
				$xml->setOptArrayTags('TranslateArrayResponse');

				$tmp = $xml->parseXml($curlResponse, true);

				if(!empty($tmp['TranslateArrayResponse'][0]))
				{
					foreach($tmp['TranslateArrayResponse'] as $val)
					{
						$ret[] = html_entity_decode($val['TranslatedText'], null, 'UTF-8');
					}
				}

				if(!empty($ret))
				{
					$this->log('Success!');
					return $ret;
				}


				$this->log("Empty Parsed XML Response");

			}
			catch (Exception $e)
			{
				$message = "Exception: " . $e->getMessage();
				$this->log($message);

			}

			return false;


		}



		function getParagraphs($sourceText)
		{
			$matches = array ();

			$pattern = '/.*<P>(.*)<\/P>/iU';
			$result = preg_match_all($pattern, $sourceText, $matches );

			if (false === $result)
			{
				die( 'preg_match_all failed' );
			}

			if ($result == 0) // plain text, not HTML paragraphs.
			{
				$matches[1] = explode("\n", $sourceText);
			}
			else
			{
				$this->_paragraphs = true;
			}

			return $matches[1];

		}

}

