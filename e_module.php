<?php
/* e107 website system
* Multiple Languages for e107.
* Copyright (C) 2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*/

if (!defined('e107_INIT')) { exit; }


// e107::getEvent()->register("newspost", array("multilan_copymodule", "syncNews"));
// e107::getEvent()->register("newsupd", array("multilan_copymodule", "syncNews"));

e107::getEvent()->register("admin_news_updated", array("multilan_copymodule", "syncNews"));
e107::getEvent()->register("admin_news_created", array("multilan_copymodule", "syncNews"));
e107::getEvent()->register("admin_page_updated", array("multilan_copymodule", "syncPage"));
e107::getEvent()->register("admin_page_created", array("multilan_copymodule", "syncPage"));
e107::getEvent()->register("admin_faqs_updated", array("multilan_copymodule", "syncFaqs"));
e107::getEvent()->register("admin_faqs_created", array("multilan_copymodule", "syncFaqs"));



class multilan_copymodule
{
	private $untranslatedClass;
	private $untranslatedFAQCat;
	private $languages;
	private $sitelanguage;

	function __construct()
	{
		$this->untranslatedClass    = e107::pref('multilan','untranslatedClass');
		$this->untranslatedFAQCat   = e107::pref('multilan','untranslatedFAQCat');
		$this->languages            = e107::pref('multilan','syncLanguages');
		$this->sitelanguage         = e107::getPref('sitelanguage');
		$this->publicOnly            = e107::pref('multilan','syncPublicOnly');
	}

	/**
	 * Make sure duplication only occurs from the primary site language
	 * @return bool
	 */
	function wrongLanguage()
	{
		if(e_LANGUAGE != $this->sitelanguage)
		{
			return true;
		}

		return false;
	}


	private function isPublic($classData)
	{
		if(empty($this->publicOnly))
		{
			return true;
		}

		if(intval($classData) === e_UC_PUBLIC)
		{
			return true;
		}

		return false;
	}


	function syncNews($data, $event='', $languages=array())
	{

		if($this->wrongLanguage())
		{
			return false;
		}



		$data = $data['newData'];

		if($this->isPublic($data['news_class']) === false)
		{
			return false;
		}

		$langs = (empty($languages)) ? $this->languages['news'] : $languages;

		foreach($langs as $k=>$lng)
		{
			$this->insert('news', $lng, array('news_id', $data['news_id']), 'news_class');
		}
	}


	function syncPage($data, $event = '', $languages=array())
	{

		if($this->wrongLanguage())
		{
			return false;
		}

		$data = $data['newData'];

		if($this->isPublic($data['page_class']) === false)
		{
			return false;
		}

		$langs = (empty($languages)) ? $this->languages['page'] : $languages;

		foreach($langs as $k=>$lng)
		{
			$this->insert('page', $lng, array('page_id', $data['page_id']), 'page_class');
		}
	}

	function syncGeneric($data, $event = '', $languages=array())
	{

		if($this->wrongLanguage())
		{
			return false;
		}

		$data = $data['newData'];

		$langs = (empty($languages)) ? $this->languages['generic'] : $languages;

		foreach($langs as $k=>$lng)
		{
			$this->insert('generic', $lng, array('gen_id', $data['gen_id']), 'gen_intdata', "gen_type='wmessage'");
		}
	}


	function syncFaqs($data, $event='', $languages = array())
	{

		if($this->wrongLanguage())
		{
			return false;
		}

		$data = $data['newData'];

		$this->untranslatedClass = $this->untranslatedFAQCat;

		//	e107::getMessage()->addDebug(print_a($data,true));

		$langs = (empty($languages)) ? $this->languages['faqs'] : $languages;

		foreach($langs as $k=>$lng)
		{
			$this->insert('faqs', $lng, array('faq_id', $data['faq_id']), 'faq_parent');
		}


	}




	/**
	 * Insert or Update language table.
	 * @param $table
	 * @param array $data
	 * @param string $lng eg. English
	 * @param array $pid Primary ID eg. array('news_id', $val)
	 * @param string $classReset eg. news_class
	 */
	function insert($table, $lng, $pid=array(), $classReset='', $filter='')
	{
		$sql        = e107::getDb();
		$tp         = e107::getParser();

		$lanTable = MPREFIX."lan_".strtolower($lng)."_".$table;

		$query = "SELECT * FROM ".$lanTable." WHERE  ".$pid[0]." = ".intval($pid[1])." AND ".$classReset." != ".$this->untranslatedClass." LIMIT 1";

		// e107::getMessage()->addDebug("Copy Table Check: ".$query.print_a($pid,true));

		if(!empty($classReset) && $sql->gen($query))
		{
			e107::getMessage()->addInfo("Update not possible - translation has already begun on destination article in ".$lng, 'default', true);
			return;
		}


	//	$query = "REPLACE INTO `".$lanTable."` ({$keyList}) VALUES ({$valList}) ; ";
		$query = "REPLACE INTO `".$lanTable."` SELECT * FROM ".MPREFIX.$table." WHERE ".$pid[0]." = ".intval($pid[1])." ";
		$query .= ($filter) ? " AND ".$filter." " : '';
		$query .= "LIMIT 1";

		if(!$sql->db_Query($query, null, '', false))
		{
			e107::getMessage()->addError("Couldn't copy ".$table." to ".$lng." table.", 'default', true);
		}
		else
		{
			// eg. news_class reset back to translator class.
			if($sql->gen("UPDATE `".$lanTable."` SET ".$classReset ." = ".$this->untranslatedClass." WHERE ".$pid[0]." = ".intval($pid[1])." LIMIT 1")===false)
			{
				e107::getMessage()->addError("Couldn't reset translator class for ".$table." :: ".$lng);
			}
			e107::getMessage()->addSuccess("Copied # ".intval($pid[1])." into  ".$lng." ".$table." table.", 'default', true);
		}


	}




}



class multilan_offline
{
	private $pref;
	private $excludeAdmin   = false;

	function __construct()
	{

		$lang 		        = e_LANGUAGE;
		$loc 		        = '';
		$disabled 	        = false;
		$sitetheme          = e107::getPref('sitetheme');

		$this->pref         = e107::pref('multilan', 'offline_languages');
		$this->excludeAdmin = e107::pref('multilan', 'offline_excludeadmins');
		$tp                 = e107::getParser();


		if(!isset($_SERVER["HTTP_USER_AGENT"]) || (e_ADMIN_AREA == true) || (e_PAGE == 'get.php') || deftrue('MULTILAN_NO_OFFLINE'))
		{
			$this->active = false;
			return false;
		}

		if(isset($this->pref[$lang]) && $this->pref[$lang])
		{
			if($this->pref[$lang] == 2 && trim($this->pref[$lang."-url"])!='')
			{
				$url = $this->pref[$lang."-url"];
				if(substr($url,0,4)=="www.")
				{
					$loc = "http://".$url;
				}
				elseif(substr($url,0,4)!="http")
				{
					$loc = SITEURL.$url;
				}
				else
				{
					$loc = $tp->replaceConstants($url,'full');
				}
			}

			if($this->pref[$lang] == 1)
			{
				$loc = 'SITEDOWN';
			}

			if($this->excludeAdmin ==1 && ADMIN)
			{
				$disabled = true;
			}
		}


		if($loc !='' && (e_PAGE != 'sitedown.php') && (e_SELF != $loc) && ($disabled !== true) )
		{
			if(!defined('THEME'))
			{
				define('THEME', e_THEME.$sitetheme."/");
				define('THEME_ABS', e_THEME_ABS.$sitetheme."/");
			}

			/*
			$message = date('r')."\n";
			$message .= "theme=".THEME;
			$message .= "\nepage=".e_PAGE;
			$message .= "\nself=".dirname(e_SELF);
			$message .= "\nLoc=".$loc;
			$message .= "\nUrl=".$url;
			$message .= print_r($_SERVER,true);
			$message .= "\n\n";

			file_put_contents(e_PLUGIN.'sitedown/redirect.log',$message,FILE_APPEND);
			*/

			if($loc != 'SITEDOWN')
			{
			//	header("Location: ".$loc, false, 302);
				e107::getRedirect()->go($loc, false, 302);
			//	header('Content-Length: 0');
			//	exit;
			}


			header('Content-type: text/html; charset='.CHARSET);
			include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_sitedown.php');

			global $SITEDOWN_TABLE;

			require_once(e_CORE.'shortcodes/batch/sitedown_shortcodes.php');

			if (!$SITEDOWN_TABLE)
			{
				if (file_exists(THEME.'templates/sitedown_template.php')) //v2.x location.
				{
					require_once(THEME.'templates/sitedown_template.php');
				}
				elseif (file_exists(THEME.'sitedown_template.php')) //v1.x location
				{
					require_once(THEME.'sitedown_template.php');
				}
				else
				{
					require_once(e_CORE.'templates/sitedown_template.php');
				}
			}

			echo $tp->parseTemplate($SITEDOWN_TABLE, TRUE, $sitedown_shortcodes);
			exit;


		}


	}




}



new multilan_offline;

	if(!empty($_GET['__mstto']))
	{
		$_SESSION["MULTILAN_BING_LANGUAGE"] =  e107::getParser()->filter($_GET['__mstto'],'w');

		$self = str_replace('?__mstto='.$_GET['__mstto'], '', e_REQUEST_URI);
		e107::getRedirect()->go($self);
	}

	if(!empty($_SESSION["MULTILAN_BING_LANGUAGE"]))
	{
		define('MULTILAN_BING_LANGUAGE', $_SESSION["MULTILAN_BING_LANGUAGE"]);
		unset($_SESSION["MULTILAN_BING_LANGUAGE"]);
	}



?>