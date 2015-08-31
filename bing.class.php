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

		public function __construct()
		{

			$this->_client_id = e107::pref('multilan', 'bing_client_id');
			$this->_client_secret = e107::pref('multilan', 'bing_client_secret');
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


		public function getToken()
		{

			if(!empty($this->_token))
			{
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

			curl_close($curl);

			$resp = json_decode($response);
			$this->_token = $resp->access_token;

			return $resp->access_token;
		}


		public function getTranslation($fromLanguage, $toLanguage, $text, $returnArray=false)
		{

			if($this->_method == 'get')
			{
				$response = $this->getResponse($this->getURL($fromLanguage, $toLanguage, $text));
				$response = strip_tags($response);
				$decoded = html_entity_decode($response, null, 'UTF-8');
			}
			else // 'post'
			{
				if(is_array($text))
				{
					$textArray = $text;
					$arrayKeys = array_keys($text);
				}
				else
				{
					$textArray = $this->getParagraphs($text);
				}

				$requestXml = $this->createXMLRequest($fromLanguage, $toLanguage, $textArray, 'text/html');
				$transArray = $this->getXMLResponse($requestXml);

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
		 */
		public function getURL($fromLanguage, $toLanguage, $text)
		{
			return 'http://api.microsofttranslator.com/v2/Http.svc/Translate?text='.urlencode($text).'&to='.$toLanguage.'&from='.$fromLanguage.'&contentType=text/plain';
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
		 */
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








		function getXMLResponse($postData='')
		{

			if(empty($postData))
			{
				return '';
			}

			$ret = array();

			$authHeader = "Authorization: Bearer ". $this->getToken();
			$url = $this->_urlPost;
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

			file_put_contents(e_LOG."multilan_bing.log", "\n\n\n".$curlResponse, FILE_APPEND);

			$curlErrno = curl_errno($ch);
			if ($curlErrno)
			{
				$curlError = curl_error($ch);


				throw new Exception($curlError);

			}
			else
			{
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


			}

			curl_close($ch);
			return (!empty($ret)) ? $ret : '';


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

