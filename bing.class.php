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


		public function getTranslation($fromLanguage, $toLanguage, $text)
		{
			$response = $this->getResponse($this->getURL($fromLanguage, $toLanguage, $text));

			$response = strip_tags($response);

			$decoded = html_entity_decode($response, null, 'UTF-8');

			return $decoded;

		}


		public function getURL($fromLanguage, $toLanguage, $text)
		{
			return 'http://api.microsofttranslator.com/v2/Http.svc/Translate?text='.urlencode($text).'&to='.$toLanguage.'&from='.$fromLanguage.'&contentType=text/plain';
		}



	}