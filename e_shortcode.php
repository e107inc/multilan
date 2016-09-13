<?php
/*
* Copyright (C) 2008-2013 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
*
* Siteinfo shortcode batch
*/
if (!defined('e107_INIT')) { exit; }

class multilan_shortcodes extends e_shortcode // must match the folder name of the plugin.
{




	function sc_multilan_nav($parm=null)
	{

		if(empty($parm))
		{
			$parm = 'main';
		}

		$lng = e107::getLanguage();

		$activeLangs = e107::pref('multilan','language_navigation');

		$languageList = $lng->installed('native');

		if(count($languageList) < 2)
		{
			return '';
		}

		$ret = array();

		foreach($languageList as $languageFolder=>$natName)
		{

			$name = $natName;

			$class = ($languageFolder == e_LANGUAGE) ? ' class="disabled"' : '';

			if(!isset($activeLangs[$languageFolder]) || empty($activeLangs[$languageFolder]))
			{
				if(!ADMIN)
				{
					continue;
				}
				else
				{
					$class = ' class="multilan-hidden"';
				}

			}

			$code = $lng->convert($languageFolder);

			$link = deftrue("MULTILANG_SUBDOMAIN") ? $lng->subdomainUrl($languageFolder) : e_SELF."?elan=".$code;

			$ret[] =  "<li role='presentation' {$class}><a  href='{$link}'>{$name}</a></li>";
		}

		if(!empty($ret))
		{
			$class = "multilan-nav-".$parm;

			return '<ul class="multilan-nav '.$class.' nav  nav-justified" >'.implode("\n", $ret).'</ul>';
		}

		return '';
	}

}
?>