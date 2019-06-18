<?php

/*
* e107 website system
* Multiple Languages Plugin for e107.
* Copyright (C) 2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*/

if (!defined('e107_INIT')) { exit; }
if(!e107::isInstalled('multilan'))
{ 
	return;
}


class multilan_sitelink // include plugin-folder in the name.
{
	function config()
	{

		$links = array();
			
		$links[] = array(
			'name'			=> "Language Links",
			'function'		=> "language"
		);	
		



		return $links;
	}
	
	

	function language()
	{
		$tp = e107::getParser();
		$sublinks = array();
		$lng = e107::getLanguage();

		$data = $lng->installed('native');

		$activeLangs = e107::pref('multilan','language_navigation');
		$flags = e107::pref('multilan','language_nav_dropflag', false);


		foreach($data as $ln=>$name)
		{

			if($lng->isValid($ln))
			{
				$redirect = deftrue("MULTILANG_SUBDOMAIN") ? $lng->subdomainUrl($ln) : e_SELF."?elan=".$ln;

			//	$name = $lng->toNative($ln);
				$iso = $lng->convert($ln);

				if(!isset($activeLangs[$ln]) || empty($activeLangs[$ln]))
				{
					if(!ADMIN)
					{
						continue;
					}
					else
					{
						$name .= " (hidden)";
					}
				}


				$sublinks[] = array(
					'link_name'			=> $tp->toHTML($name,'','TITLE'),
					'link_url'			=> $redirect,
					'link_description'	=> $ln,
					'link_button'		=> ($flags) ? $this->getFlag($iso, $ln) : '',
					'link_category'		=> '',
					'link_order'		=> '',
					'link_parent'		=> '',
					'link_open'			=> '',
					'link_class'		=> e_UC_PUBLIC,
					'link_active'       => (e_LANGUAGE == $ln) ? 1 : 0,
				);
				//	break;
			}
		}






		
		return $sublinks;
	    
	}


	private function getFlag($lan, $language)
	{
		return "<i class='multilan flag-".$lan."'></i>";
	}
	
}
