<?php

// https://msdn.microsoft.com/en-us/library/dn735968.aspx
// https://msdn.microsoft.com/en-us/library/dn341982.aspx

	$sitelanguage = e107::getPref('sitelanguage');
	$mpref = e107::pref('multilan');

	if(USER_AREA === true && ($sitelanguage == e_LANGUAGE) && !empty($mpref['bing_translator']))
	{

		if(!empty($mpref['bing_exclude_installed']))
		{
			$lng = e107::getLanguage();

			$installedLanguages = $lng->installed();
			$iso = array();

			foreach($installedLanguages as $lang)
			{
				$iso[] = $lng->convert($lang);
			}

			$hidden = implode(",",$iso); // exclude installed languages.
		}
		else
		{
			$hidden = '';
		}

		$from = e_LAN;

	 	$loc = deftrue("MULTILAN_BING_LANGUAGE", '');

	//	$loc = '';

		$setting = $mpref['bing_translator'];
		//<div id='MicrosoftTranslatorWidget' class='Dark' style='color:white;background-color:#555555'></div>


		if(!empty($mpref['bing_map_to_subdomain']) || e_SUBDOMAIN == 'fr')
		{

			e107::js('footer', 'http://www.microsoftTranslator.com/ajax/v3/WidgetV3.ashx?siteData=ueOIGRSKkd965FeEGM5JtQ**');
			e107::js('footer-inline', "

			document.onreadystatechange = function () {


	            if (document.readyState == 'complete') {
	                Microsoft.Translator.Widget.Translate('".e_LAN."', '".e_SUBDOMAIN."', onProgress, onError, onComplete, onRestoreOriginal);
				}
	        }
	        //You can use Microsoft.Translator.Widget.GetLanguagesForTranslate to map the language code with the language name
	        function onProgress(value) {
	            document.getElementById('counter').innerHTML = Math.round(value);
	        }

	        function onError(error) {
	            alert('Translation Error: ' + error);
	        }

	        function onComplete() {

	        }
	        //fires when the user clicks on the exit box of the floating widget
	        function onRestoreOriginal() {
	            alert('The page was reverted to the original language. This message is not part of the widget.');
	        }


			");
		}
		else
		{
			$auto = "setTimeout(function(){{var s=document.createElement('script');s.type='text/javascript';s.charset='UTF-8';s.src=((location && location.href && location.href.indexOf('https') == 0)?'https://ssl.microsofttranslator.com':'http://www.microsofttranslator.com')+'/ajax/v3/WidgetV3.ashx?siteData=ueOIGRSKkd965FeEGM5JtQ**&ctf=False&ui=false&settings=".$setting."&loc=".$loc."&from=".$from."&hidelanguages=".$hidden."';var p=document.getElementsByTagName('head')[0]||document.documentElement;p.insertBefore(s,p.firstChild); }},0);";

			e107::js('footer-inline', $auto);
			unset($auto);
		}



	}

	// http://www.spherebeingalliance.com/?__mstto=th