<?php

/*
* e107 website system
* Multiple Languages Plugin for e107.
* Copyright (C) 2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*/

define('e_PAGE_LANGUAGE', 'E_SITELANGUAGE'); // Force language as english.
$_E107['no_language_perm_check'] = true;

require_once('../../class2.php');

if (!getperms('P') || !e107::isInstalled('multilan'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}

if(!empty($_GET['iframe']))
{
	define('e_IFRAME', true);
}

define('ADMIN_BING_ICON', "<img src='".e_PLUGIN."multilan/images/bing_16.png' class='auto-translated' alt='auto-translated' />");
define('ADMIN_FLAG_ICON', "<img src='".e_PLUGIN."multilan/images/flag_16.png' class='un-translated' alt='un-translated' />");
define('ADMIN_CLEAN_ICON', "<span class='fa fa-star-o'></span>");
define('ADMIN_REFRESH_ICON',  "<span class='fa fa-refresh'></span>");


e107::css('inline', "

.help-table td {    padding:7px 0;   }
 td.chars { padding-right:15px; border-right:1px solid black }
.toggle-icon { cursor: pointer }
td.lan-odd { background-color: rgba(255,255,255,0.05); }


 ");



class multilan_adminArea extends e_admin_dispatcher
{

	protected $modes = array(
		'news'		=> array(
			'controller' 	=> 'status_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'status_form_ui',
			'uipath' 		=> null,
			'perm'          => null
		),
		'page'		=> array(
			'controller' 	=> 'status_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'status_form_ui',
			'uipath' 		=> null,
			'perm'          => null
		),
		'generic'		=> array(
			'controller' 	=> 'status_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'status_form_ui',
			'uipath' 		=> null,
			'perm'          => null
		),
		'faqs'		=> array(
			'controller' 	=> 'status_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'status_form_ui',
			'uipath' 		=> null,
			'perm'          => null
		),
		'main'		=> array(
			'controller' 	=> 'status_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'status_form_ui',
			'uipath' 		=> null,
			'perm'          => null
		),
	);


	protected $adminMenu = array(



		'news/list'			=> array('caption'=> 'News', 'perm' => 'P'),
		'page/list' 		=> array('caption'=> 'Page', 'perm' => 'P'),
		'faqs/list' 		=> array('caption'=> 'FAQs', 'perm' => 'P'),
		'generic/list' 		=> array('caption'=> 'Welcome Message', 'perm' => 'P'),
		'option3'           => array('divider'=>true, 'perm'=>'0'),
		'main/core'         => array('caption'=>'Core Translator', 'perm'=>'0'),
		'main/editor'         => array('caption'=>'Core Editor', 'perm'=>'0'),
		'option2'           => array('divider'=>true, 'perm'=>'0'),
		'main/prefs' 	    => array('caption'=> LAN_PREFS, 'perm' => '0'), // Preferences
		'main/tools'       =>array('caption'=>'Tools', 'perm'=>'0'),
		'main/tables'       => array()
	);


	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'Multiple Languages';


	function init()
	{
		$this->adminMenu['main/tables'] = array('caption'=>'Tables', 'modal-caption'=>'Database Tables', 'perm'=>0, 'modal'=>true, 'uri'=>e_ADMIN.'language.php?mode=main&iframe=1&action=db');

		e107::css('inline', " #etrigger-batch { width: 300px } ");

		$sitelanguage = e107::getPref('sitelanguage');
		if(e_LANGUAGE != $sitelanguage)
		{

			e107::getMessage()->addWarning("Please switch to ".$sitelanguage." to view.");
			$this->adminMenu = array();

			return false;
		}

		if(e_AJAX_REQUEST)
		{
			$this->handleAjax();
		}


		if(!$multi = e107::getPref('multilanguage'))
		{
			e107::getMessage()->addWarning("Multilanguage tables are disabled. Enabling now.");
			e107::getConfig()->set('multilanguage',1)->save(false,false,false);
		}

	}


	private function handleAjax()
	{


		if(!empty($_GET['itemid']) && !empty($_GET['language']) &&  !empty($_GET['type']) && !empty($_GET['table']))
		{
			switch($_GET['type'])
			{
				case "copy":
					if($this->copyItem($_GET['table'], $_GET['language'], $_GET['itemid']))
					{
						echo ADMIN_FLAG_ICON;
					}
					else
					{
						echo ADMIN_WARNING_ICON;
					}
					break;

				case "delete":
					if($this->deleteItem($_GET['table'], $_GET['language'], $_GET['itemid']))
					{
						echo '&middot;';
					}
					else
					{
						echo ADMIN_WARNING_ICON;
					}
					break;

				case "class":
					if($this->classItem($_GET['table'], $_GET['language'], $_GET['itemid']))
					{
						echo ADMIN_FLAG_ICON;
					}
					else
					{
						echo ADMIN_FALSE_ICON;
					}
					break;

				case "bing":
					if(!$this->copyItem($_GET['table'], $_GET['language'], $_GET['itemid']))
					{
						//echo ADMIN_WARNING_ICON;
						//exit;

					}

					if($this->translateItem($_GET['table'], $_GET['language'], $_GET['itemid']))
					{
						echo ADMIN_BING_ICON;
					}
					else
					{
						echo ADMIN_WARNING_ICON;
					}
					break;

			}


		}

		if(!empty($_GET['action']) && !empty($_GET['lanid']) && !empty($_GET['language']) )
		{

			switch($_GET['action'])
			{
				case "bing":

					if($this->translateFile($_GET['lanid'],$_GET['language']))
					{
						echo ADMIN_BING_ICON; // e107::getParser()->toGlyph('fa-check');
					}
					else
					{
						echo ADMIN_WARNING_ICON;
					}

					break;

				case "comment":

					if($this->cleanFile($_GET['lanid'],$_GET['language']))
					{
						echo ADMIN_CLEAN_ICON; // e107::getParser()->toGlyph('fa-check');
					}
					else
					{
						echo ADMIN_WARNING_ICON;
					}

					break;

				default:
					// code to be executed if n is different from all labels;
			}
/*

			if(!empty($_GET['lanid']) && !empty($_GET['language']) )
			{
				if($this->translateFile($_GET['lanid'],$_GET['language']))
				{
					echo ADMIN_BING_ICON; // e107::getParser()->toGlyph('fa-check');
				}
				else
				{
					echo ADMIN_WARNING_ICON;
				}
			}

			if(!empty($_GET['lanid']) && !empty($_GET['language']) )
			{
				if($this->commentFile($_GET['lanid'],$_GET['language']))
				{
					//echo ADMIN_BING_ICON; // e107::getParser()->toGlyph('fa-check');
				}
				else
				{
					//echo ADMIN_WARNING_ICON;
				}
			}*/
		}
		exit;

	}


	private function cleanFile($id,$lan)
	{

		$_SESSION['multilan_lanfiledata'][$id] ;

		if(empty($_SESSION['multilan_lanfilelist_existing'][$id]))
		{
			return null;
		}

		$path = $_SESSION['multilan_lanfilelist_existing'][$id];

		$tmp = $_SESSION['multilan_lanfiledata'][$id];
		$tmp2 = $_SESSION['multilan_lanfiledata_existing'][$id];

		unset($tmp['bom'],$tmp2['bom']);

		$diff = array_diff_key($tmp2,$tmp);

		if(empty($diff))
		{
			return null;
		}

		require_once(e_ADMIN."lancheck.php");
		$lck = new lancheck;

		$keys = array_keys($diff);
		// print_r($keys);

		return $lck->commentOut($keys,$path);




	}


	private function publicItem($type,$lan,$id)
	{
		$table = '';
		$pid = '';

		switch($type)
		{
			case "news":
				$table = 'news';
				$pid = 'news_id';

				break;

			case "page":
				$table = 'page';
				$pid = 'page_id';

				break;

			case "generic":
				$table = 'generic';
				$pid = 'gen_id';

				break;

			case "faqs":
				$table = 'faqs';
				$pid = 'faq_id';


				break;
		}

		if(empty($table) || empty($pid))
		{
			return false; // "Invalid";
		}

		$lanTable = "lan_".strtolower($lan)."_".$table;

		if(e107::getDb()->update($lanTable, $pid. ' = '.intval($id))) // already exists.
		{
			return true;
		}
		else
		{
			return false;
		}

	}


	private function classItem($type, $lan, $id)
	{

		$table = '';
		$pid = '';


		switch($type)
		{
			case "news":
				$table = 'news';
				$pid = 'news_id';

				break;

			case "page":
				$table = 'page';
				$pid = 'page_id';

				break;

			case "generic":
				$table = 'generic';
				$pid = 'gen_id';

				break;

			case "faqs":
				$table = 'faqs';
				$pid = 'faq_id';


				break;
		}


		$uclass = $this->getVisibilityField($type);

		if(empty($table) || empty($pid))
		{
			return false; // "Invalid";
		}

		$lanTable = "lan_".strtolower($lan)."_".$table;

		if($type == 'faqs')
		{
			$value = e107::pref('multilan','untranslatedFAQCat', 1);
		}
		else
		{
			$value = e107::pref('multilan','untranslatedClass', 0);
		}



		$qry =  $uclass." = ".$value." WHERE  ". $pid. ' = '.intval($id);



		if(e107::getDb()->update($lanTable,$qry)) // already exists.
		{
			e107::getLog()->addDebug("Attempting to Update visibility\nQuery: ".$qry);
			$this->logAjax();
			return true;
		}
		else
		{
			e107::getLog()->addDebug("Attempting to Update visibility\nQuery: ".$qry);
			$this->logAjax();
			return false;
		}
	}


	private function deleteItem($type, $lan, $id)
	{

		$table = '';
		$pid = '';

		switch($type)
		{
			case "news":
				$table = 'news';
				$pid = 'news_id';

				break;

			case "page":
				$table = 'page';
				$pid = 'page_id';

				break;

			case "generic":
				$table = 'generic';
				$pid = 'gen_id';

				break;

			case "faqs":
				$table = 'faqs';
				$pid = 'faq_id';


				break;
		}

		if(empty($table) || empty($pid))
		{
			return false; // "Invalid";
		}

		$lanTable = "lan_".strtolower($lan)."_".$table;

		if(e107::getDb()->delete($lanTable, $pid. ' = '.intval($id))) // already exists.
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function getTranslationFields($type)
	{
		switch($type)
		{
			case "news":
				return array('news_title', 'news_body', 'news_extended', 'news_meta_description', 'news_summary');
				break;

			case "page":
				return array('page_title', 'page_text', 'menu_title', 'menu_text');
				break;

			case "generic":
				return array('gen_ip', 'gen_chardata');
				break;

			case "faqs":
				return array('faq_question', 'faq_answer');
			break;

			default:
				// code to be executed if n is different from all labels;
		}


	}


	private function getVisibilityField($type)
	{
		switch($type)
		{
			case "news":
				return 'news_class';
				break;

			case "page":
				return 'page_class';
				break;

			case "generic":
				return 'gen_intdata';
				break;

			case "faqs":
				return 'faq_parent';
				break;

			default:
				// code to be executed if n is different from all labels;
		}

	}


	/**
	 * @param $type
	 * @param $lan
	 * @param $id
	 * @return bool
	 */
	private function translateItem($type, $lan, $id)
	{


		$table = '';
		$fields = '';
		$pid = '';
		$update = array();
		$insert = 'update';

		switch($type)
		{
			case "news":
				$table = 'news';
				$pid = 'news_id';
				$fields = $this->getTranslationFields('news'); // translatable fields.
				$ucfield = 'news_class';
				break;

			case "page":
				$table = 'page';
				$pid = 'page_id';
				$fields = $this->getTranslationFields('page');
				$ucfield = 'page_class';

				break;

			case "generic":
				$table = 'generic';
				$pid = 'gen_id';
				$fields = $this->getTranslationFields('genetic');
				$ucfield = 'gen_intdata';
				$insert = 'replace';
				$update['gen_id'] = $id;
				$update['gen_type'] = 'wmessage';
				$update['gen_datestamp'] = time();
				break;

			case "faqs":
				$table = 'faqs';
				$pid = 'faq_id';
				$fields = $this->getTranslationFields('faqs');
			//	$ucfield = 'page_class';

				break;
		}

		if(empty($fields) || empty($pid))
		{
			e107::getLog()->addError("Fields is empty for type:".$type);
			$this->logAjax();
			return false; // "Invalid";
		}

		$sql            = e107::getDb();
		$bng            = e107::getSingleton('bingTranslate', e_PLUGIN."multilan/bing.class.php");
		$languageCode   = e107::getLanguage()->convert($lan);
		$tp = e107::getParser();
		$row = $sql->retrieve($table, implode(",",$fields), $pid. ' = '.intval($id));



		foreach($row as $field=>$value)
		{
			if(!empty($value))
			{
				$html = false;
			//	$newValue = $bng->getTranslation('en', $languageCode, e107::getParser()->toHtml($value,true));
				if(strpos($value, '[html]')!==false)
				{
					$value = str_replace(array("[html]","[/html]"), "", $value);
					$html = true;
				}

				$newValue = $bng->getTranslation('en', $languageCode, $value);

				if($html === true)
				{
					$update[$field] = "[html]".$newValue."[/html]";
				}
				else
				{
					$update[$field] = $newValue;
				}
			}
			elseif(E107_DBG_BASIC)
			{
				e107::getLog()->addError("Empty value for ".$field." using type:".$type);
			}
		}

		if(empty($update))
		{
			$this->logAjax();
			return false;
		}

		$autoClass = e107::pref('multilan', 'autotranslatedClass');
		if(!empty($ucfield) && !empty($autoClass))
		{
			$update[$ucfield] = $autoClass;
		}

		$update['WHERE'] = $pid.' = '.intval($id). ' LIMIT 1';

		$lanTable = "lan_".strtolower($lan)."_".$table;

		if($sql->$insert($lanTable, $update))
		{
			return true;

		}
		elseif(E107_DBG_BASIC)
		{
			e107::getLog()->addError("Couldn't update table: ".$lanTable." with ".print_r($update,true));
		}

		$this->logAjax();
		return false;
	}


	private function logAjax()
	{
	//	if(E107_DBG_BASIC)
		{
			$log = e107::getLog();
			$log->toFile('multilan',"Multi-Language Plugin Log", true);
		}

	}


	/**
	 * @param $type
	 * @param $lan
	 * @param $id
	 * @return bool|string
	 */
	private function copyItem($type, $lan, $id)
	{

		if(empty($type))
		{
			return "Type not set";
		}

		if(empty($lan))
		{
			return "Language not set";
		}

		if(empty($id))
		{
			return "Item ID not set";
		}

		$pid = '';
		$method = '';
		$table = '';

		switch($type)
		{
			case "news":
				$table = 'news';
				$pid = 'news_id';
				$method = 'syncNews';
				break;

			case "page":
				$table = 'page';
				$pid = 'page_id';
				$method = 'syncPage';
				break;

			case "generic":
				$table = 'generic';
				$pid = 'gen_id';
				$method = 'syncGeneric';
				break;

			case "faqs":
				$table = 'faqs';
				$pid = 'faq_id';
				$method = 'syncFAQs';
				break;
		}

		if(empty($pid) || empty($method) || empty($table))
		{
			return false;
		}

		$lanTable = "lan_".strtolower($lan)."_".$table;

		if(e107::getDb()->select($lanTable,'*', $pid. ' = '.intval($id))) // already exists.
		{
			// echo "Already exists";
			//return false;
		}


		$mlan = new multilan_copymodule;
		$data = array();
		$data['newData'] = array($pid=>$id);
		$languages = array($lan);

	//	return print_a($data,true);


		$mlan->$method($data, null,  $languages); // eg syncNews.

		e107::getMessage()->resetSession();

		//TODO The Bing translation part.

		return true;
	}


	private function translateFile($key, $lan)
	{
		$bng = e107::getSingleton('bingTranslate', e_PLUGIN."multilan/bing.class.php");
		$lng = e107::getLanguage();

		$id             = $_GET['lanid'];
	//	$languageCode   = e107::getParser()->filter($_GET['language'], 'w');
		$languageCode   = $_GET['language'];

		$language       = $lng->convert($languageCode);

		if($languageCode == 'zh-CHS')
		{
			$language = 'ChineseSimp';
		}

		if($languageCode == 'zh-CHT')
		{
			$language = 'ChineseTrad';
		}


		$newFile        = str_replace(array('-core-','-plugin-','-theme-','English'), array(e_LANGUAGEDIR.'English/', e_PLUGIN, e_THEME, $language), $_SESSION['multilan_lanfilelist'][$id]);



	//	$log = "Bing: ".$languageCode."\ne107Language: ".$language."\n";

		//file_put_contents(e_LOG."multilanBing.log", date('r').$log, FILE_APPEND);

		//return true;

		if(file_exists($newFile))
		{
	//		return false;
		}

		$srch = array('en', 'GB', 'US', 'gb');
		$repl = array($languageCode, strtoupper($languageCode), strtoupper($languageCode), $languageCode);

		$lc = strtolower($languageCode);
		$lc2 = strtoupper($languageCode);


		$existingArray  = null;

		if(!empty($_SESSION['multilan_lanfiledata_existing'][$id])) // Existing file, so find what hasn't been translated yet.
		{
			$this->logDebug(basename($newFile), "Existing File detected");
			$toTranslate = array_diff_key($_SESSION['multilan_lanfiledata'][$id],$_SESSION['multilan_lanfiledata_existing'][$id]);
		//	$existingArray = $_SESSION['multilan_lanfiledata_existing'][$id];
		}
		else
		{
			$toTranslate = $_SESSION['multilan_lanfiledata'][$id];
		}

		if(empty($toTranslate))
		{
			return false;
		}



		foreach($toTranslate as $k=>$v)
		{

			if($k == 'LC_ALL' || $k == 'CORE_LC' || $k == 'CORE_LC2')
			{
				$translation = str_replace($srch,$repl, $v);

				if($k == 'LC_ALL')
				{
					$wmode = 'setlocale';
					$translation = "'".$lc."_".$lc2.".UTF-8', '".$lc.".utf8', '".$lc."_".$lc2.".utf8', '".$lc.".UTF-8'";
				}
				else
				{
					$wmode = '';
				}

			//	$wmode = ($k == 'LC_ALL') ? 'setlocale' : '';
				$this->writeFile($newFile, $k, $translation, $wmode);
				unset($toTranslate[$k]); // remove from the list.
				//break;
			}
		}


		$transArray = $bng->getTranslation('en', $languageCode, $toTranslate, true, str_replace('../','',$newFile));

		if(!empty($existingArray))
		{
		//	$transArray = array_merge($existingArray,$transArray);
		}

		if(!empty($transArray))
		{
		/*	$message = print_r($transArray,true);
			$tag = basename($newFile);
			$this->logDebug($tag,$message);*/
			$this->writeFile($newFile, $transArray);
			return true;
		}

		return false;




	}

	private function logDebug($tag, $message)
	{
		file_put_contents(e_LOG."multilan_bing.log", "\n".date('r')."\t\t".$tag."\t\t".$message, FILE_APPEND);
	}


	private function writeFile($file, $key, $value='', $opt = '')
	{
		$output = '';

		$dir =  dirname($file);

	//	file_put_contents(e_LOG."multilanBing.log", date('r')."\t\tDirectory ".$dir."\n", FILE_APPEND);



		if(!is_dir($dir))
		{
			mkdir($dir, 0755);
		}


		if(!file_exists($file))
		{
			$output .= chr(60)."?php\n\n";
			$output .= "// Bing-Translated Language file \n";
			$output .= "// Generated for e107 v2.x by the Multi-Language Plugin\n";
			$output .= "// https://github.com/e107inc/multilan\n\n";

		}
		else
		{
		//	return false;
		}

		if(is_array($key))
		{
			foreach($key as $k=>$v)
			{
				$v = str_replace('"', "'", $v);
				$output .= 'define("'.$k.'", "'.$v.'");';
				$output .= "\n";
			}
		}
		else
		{
			$output .= ($opt == 'setlocale') ? 'setlocale('.$key.', '.$value.');' : 'define("'.$key.'", "'.$value.'");';
			$output .= "\n";
		}

		if(!file_put_contents($file, $output, FILE_APPEND))
		{
			file_put_contents(e_LOG."multilanBing.log", date('r')."\t\tCouldn't save to ".$file."\n", FILE_APPEND);

		}


		e107::debug('multilanFile',$file);
	}



}

class status_admin_ui extends e_admin_ui
{
		protected $pluginTitle	= 'Multiple Languages'; // "News"
		protected $pluginName	= 'multilan';
	//	protected $table 		= "";
	//	protected $pid			= null;
		protected $perPage      = 10; //no limit
		protected $batchDelete  = false;
		protected $batchCopy    = false;
		protected $batchOptions = array();
	//	protected $listOrder	= null;

		protected $fields       = array();
		protected $fieldpref    = array();

		public $langData        = array();
		public $statusField     = null;  // field name.
		public $statusLink      = null;
		public $statusTitle     = null; // fieldName

		protected $preftabs        = array("Data Sync", "Offline", "Bing", "Navigation", LAN_ADMIN );

		protected $prefs = array(
			'syncLanguages'         => array('title'=> "Sync Table Content",  'tab'=>0, 'type'=>'method', 'data'=>'str'),
			'syncPerPage'           => array('title'=> "Sync Items per page",  'tab'=>0, 'type'=>'number', 'data'=>'int'),
			'syncPublicOnly'        => array('title'=> "Sync Public Only ",  'tab'=>0, 'type'=>'boolean', 'data'=>'int', 'help'=>'Sync only when Visibility is set to Everyone/Public'),
			'syncRemoveSef'        => array('title'=> "Remove SEF URLs during Sync ",  'tab'=>0, 'type'=>'boolean', 'data'=>'int', 'help'=>'Delete the English SEF Url during copying/syncing'),
			'untranslatedClass'	    => array('title'=> "Untranslated Class", 'tab'=>0, 'type'=>'userclass', 'writeParms'=>array('default'=>'TRANSLATE_ME')),
			'autotranslatedClass'	=> array('title'=> "Auto-Translated Class", 'tab'=>0, 'type'=>'userclass', 'writeParms'=>array('default'=>'REVIEW_ME')),


			'offline_languages'     => array('title' => "Offline", 'tab'=>1, 'type'=>'method', 'data'=>'str'),
			'offline_excludeadmins' => array('title'=>'Exclude Admins from redirect', 'tab'=>1, 'type'=>'boolean'),
			'language_nav_icon'       => array('title' => 'Main Navigation Icon', 'type'=>'dropdown', 'data'=>'str', 'tab'=>3, 'help'=>'Use: LAN_MULTILAN_NAVICON', 'writeParms'=>array( 'globe'=>'Globe', 'flag'=>'Flags')),
			'language_nav_dropflag' => array('title'=>'Display flags in dropdown menu', 'tab'=>3, 'type'=>'boolean'),
			'language_navigation'    => array('title'=>"Dropdown Navigation Options", 'type'=>'method', 'tab'=>3),
			'bing_translator'       => array('title' => 'Frontend Auto-Translator', 'type'=>'dropdown', 'tab'=>2,'writeParms'=>array(0=>'Off', 'auto'=>'Auto', 'notify'=>'Notify')),

			'bing_exclude_installed'=>  array('title' => 'Exclude installed languages', 'type'=>'boolean', 'tab'=>2, 'help'=>"If enabled, will exclude languages currently installed in e107 from the available bing translations."),
		//	'bing_client_id'    => array('title'=>"Client ID", 'type'=>'text', 'data'=>'str',  'tab'=>2,  'writeParms'=>array('tdClassRight'=>'form-inline','post'=>" <a class='btn btn-primary btn-mini btn-xs' target='_blank' href='https://msdn.microsoft.com/en-us/library/mt146806.aspx'>More Info.</a>")),
			'bing_client_secret'    => array('title'=>"Authentication Key 1", 'type'=>'text', 'data'=>'str', 'tab'=>2, 'writeParms'=>array('tdClassRight'=>'form-inline', 'size'=>'xxlarge', 'post'=>"&nbsp;<a target='_blank' title=\"More Information\" href='https://www.microsoft.com/en-us/translator/getstarted.aspx'><span class='fa fa-info-circle'></span></a><div>Please note: The maximum throughput is 400,000 characters per hour or 2 million characters a day.</div>")),

			'admin_translations_tab'  => array('title'=> "Translations Tab Class", 'tab'=>4, 'type'=>'userclass', 'help'=>'Class who can view the status of translations when editing news items'),


		);


		protected $languageTables = array();
		protected $totalCharCount  = 0;
		protected $localPacks = array();
		protected $languageList = array();

		function init()
		{

			// $this->addHeader("My Content");

			$this->languageTables = e107::getDb()->db_IsLang(array('news','page','faqs','generic'),true);

			if(e107::isInstalled("faqs"))
			{
				$this->initFaqsPrefs();
			}

			$this->perPage = e107::pref('multilan','syncPerPage',10);

			if(!empty($_POST['generate_lanlinks']))
			{
				$this->generateSystemLANS();
			}

			if($this->getMode()== 'main')
			{
				return;
			}

			$js = <<<JS

				$('.toggle-icon').on('click', function(){

					var type = $(this).attr('data-type');
					$("."+ type).fadeToggle();
				});

				$('#e--execute-batch').on('click', function(){

					tmp = $('#etrigger-batch').val().split('_');


    				$( "#uiAlert" ).html("<div class='alert fade in alert-success'>Processing</div>").show();
						// .fadeIn({ duration: 3000,  queue: false })

					var type = tmp[0];
					var table = tmp[1];
					var lancode = tmp[2];
					var handler = window.location.href;

					if(lancode == '')
					{
						alert("No Language Selected");
						return false;
					}

					$('#plugin-multilan-list-form').find('.lanfile').each(function(e){

						var indicator = $(this).attr('id');
						tmp = indicator.split('-');

						var language = tmp[1];
						var id = tmp[2];

						if(language != lancode)
						{
							return;
						}

						var cbox = '#multiselect-'+id+'-'+id;

						if($(cbox).is(":not(:checked)")){

	                        return;
	                    }

						$('#'+indicator).html('<i class="fa fa-spin fa-spinner"></i>');



					    $.ajax({
						type: 'get',
						async: false,
						url: handler,
						data: { itemid: id, language: lancode, table: table, type: type},
						success: function(data)
							{
									 // 	console.log(data);
								//	 alert('Done:'+ theid);
								$('#'+indicator).html(data);
								$(cbox).removeAttr('checked');

							//	$('tr#row-'+id);
							//	 $('#status-'+theid).html(data);

								//	$('#uiAlert').notify({
								//		type: 'success',
				                //        message: { text: 'Completed' },
				                //        fadeOut: { enabled: true, delay: 2000 }
				                //    }).show();

							 }
						});



					});

					 $('#uiAlert').fadeOut(2000);



					return false;
				});


JS;

			e107::js('footer-inline', $js);



			if($this->initAll() === false)
			{
				return false;
			}



		}

/*
 *      //XXX No longer used - using ajax now.
		function handleListBatch($selected, $value)
		{

		//	print_a($selected);
		//	echo "Val: ".print_a($value,true);
		//	e107::getMessage()->addInfo("Translating...");
		//	e107::getMessage()->addInfo(print_a($selected,true));
		//	e107::getMessage()->addInfo(print_a($value,true));


			list($mode,$type, $language) = explode("_",$value);


			if($mode == 'copy')
			{

				$mode = $this->getMode();
				$pid = '';
				$method = '';

				switch($mode)
				{
					case "news":
						$pid = 'news_id';
						$method = 'syncNews';
					break;

					case "page":
						$pid = 'page_id';
						$method = 'syncPage';
						break;

					case "faqs":
						$pid = 'faq_id';
						$method = 'syncFAQs';
						break;
				}

				if(empty($pid) || empty($method))
				{
					return false;
				}

				$mlan = new multilan_copymodule;
				$data = array();
				foreach($selected as $id)
				{
					$data['newData'] = array($pid=>$id);
					$languages = array($language);
					$mlan->$method($data, null,  $languages); // eg syncNews.
				}

				e107::getRedirect()->go(e_REQUEST_URI);
			}
		}
*/


		/**
		 * Initial all Language-Sync Options for the current mode.
		 */
		private function initAll()
		{
			$lng = e107::getLanguage();

			$this->fields['checkboxes'] =  array('title'=> '',	'type' => null, 'width' =>'5%', 'forced'=> true, 'thclass'=>'center', 'class'=>'center');

			$languages = $lng->installed();

			sort($languages);

			$sitelanguage = e107::getPref('sitelanguage');

			if(e_LANGUAGE != $sitelanguage)
			{
				$this->pid                  = 'news_id';
				return false;
			}

			$mode =$this->getMode();
			$initType = 'init'.ucfirst($mode);

			$this->$initType(); // eg. initNews();

			$this->langData = $this->getLangData($languages);

			$this->fields['chars'] = array('title'=> "Chars",	'type' => 'method', 	'data' => 'str',  'method'=>'characterCount',	'width' => '100px',	'thclass' => 'right', 'class'=>'right chars', 'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);

			$style = 'lan-odd';



			foreach($languages as $k=>$v)
			{
				if($v == $sitelanguage)
				{
					continue;
				}

				$key = $lng->convert($v);


				$this->fields[$key] = array('title'=> $key,	'type' => 'method', 	'data' => 'str',  'method'=>'findTranslations',	'width' => '60px',	'thclass' => 'center', 'class'=>'center '.$style, 'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);

				$style = ($style == 'lan-odd') ? 'lan-even' : 'lan-odd';
			}

			foreach($languages as $v)
			{
				$lowerLang = strtolower($v);
				if($v == $sitelanguage || !isset($this->languageTables[$lowerLang][MPREFIX.$mode ]))
				{
					continue;
				}

				$this->batchOptions['delete_'.$mode.'_'.$v] = "Delete from ".$v.' table';
			}

			foreach($languages as $v)
			{
				$lowerLang = strtolower($v);
				if($v == $sitelanguage || !isset($this->languageTables[$lowerLang][MPREFIX.$mode ]))
				{
					continue;
				}

				$this->batchOptions['class_'.$mode.'_'.$v] = "Flag ".$v.' for translation ';
			}


			foreach($languages as $v)
			{
				$lowerLang = strtolower($v);
				if($v == $sitelanguage || !isset($this->languageTables[$lowerLang][MPREFIX.$mode ]))
				{
					continue;
				}

				$this->batchOptions['copy_'.$mode.'_'.$v] = "Copy ".$sitelanguage." into ".$v.' table';
			}

			$bingClient = e107::pref('multilan', 'bing_client_id');
			if(!empty($bingClient))
			{
				foreach($languages as $v)
				{
					$lowerLang = strtolower($v);
					if($v == $sitelanguage || !isset($this->languageTables[$lowerLang][MPREFIX.$mode ]))
					{
						continue;
					}

					$this->batchOptions['bing_'.$mode."_".$v] = "Translate into ".$v." table";
				}
			}



			$this->fields['options']    = array('title'=> 'Status',			'type' => 'method',		'nolist'=>true,		'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center');
			$this->fieldpref = array_keys($this->fields);


		}


		public function initNews()
		{
			$this->pid                  = 'news_id';
			$this->table                = 'news';
			$this->listOrder            = 'news_datestamp DESC';
			$this->statusField          = 'news_class';
			$this->statusLink           = "{e_ADMIN}newspost.php?mode=main&amp;action=edit&amp;iframe=1&amp;id={ID}"; // (no SEFs)
			$this->statusTitle          = "news_title";

			$this->fields['news_id']        = array('title'=> LAN_ID,			'type' => 'number',			'width' =>'3%', 'forced'=> TRUE, 'readonly'=>TRUE);
			$this->fields['news_title']     = array('title'=> LAN_TITLE,	 '__tableField'=>'news_title', 'method'=>'news_title', 'type' => 'method', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>true, 'readParms'=>'truncate=60');
			$this->fields['news_datestamp'] = array('title'=> LAN_DATESTAMP,	'type' => 'datestamp', 		'readParms'=>array('mask'=>'M.dd.yyyy'),	'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
			$this->fields['news_class']     = array( 'nolist'=>true ); // to retrieve it for comparison.
			$this->fields['news_category']     = array('title'=>LAN_CATEGORY, '__tableField'=>'news_category', 'type'=>'dropdown', 'data'=>'int', 'filter'=>true, 'nolist'=>true );

			$data = e107::getDb()->retrieve('news_category','category_id,category_name', '', true);

			foreach($data as $row)
			{
				$id = $row['category_id'];
				$this->fields['news_category']['writeParms']['optArray'][$id] = $row['category_name'];
			}

			$this->fieldpref = array_keys($this->fields);
		}


		public function initPage()
		{
			$this->pid                  = 'page_id';
			$this->table                = 'page';
			$this->listOrder            = 'page_id DESC';
			$this->statusField          = 'page_class';
			$this->statusLink           = "{e_ADMIN}cpage.php?action=edit&amp;iframe=1&amp;id={ID}"; // (no SEFs)
			$this->statusTitle          = "page_title";

			$this->fields['page_id']        = array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE);
			$this->fields['page_title']     = array('title'=> LAN_TITLE,		 '__tableField'=>'page_title', 'type' => 'text', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE, 'readParms'=>'truncate=60');
			$this->fields['page_datestamp'] = array('title'=> LAN_DATESTAMP,	'type' => 'datestamp', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
			$this->fields['page_class']     = array( 'nolist'=>true ); // to retrieve it for comparison.
		}


	public function initGeneric()
	{
		$this->pid                  = 'gen_id';
		$this->table                = 'generic';
		$this->listOrder            = 'gen_id DESC';
		$this->statusField          = 'gen_intdata';
		$this->statusLink           = "{e_ADMIN}wmessage.php?action=edit&amp;iframe=1&amp;id={ID}"; // (no SEFs)
		$this->statusTitle          = "gen_chardata";

		$this->listQry      	    = "SELECT * FROM `#generic` WHERE gen_type='wmessage'  "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		$this->fields['gen_id']         = array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE);
		$this->fields['gen_ip']         = array('title'=> LAN_TITLE,	 '__tableField'=>'gen_ip',	'type' => 'text', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
		$this->fields['gen_datestamp']  = array('title'=> LAN_DATESTAMP,	'type' => 'datestamp', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
		$this->fields['gen_intdata']    = array( 'nolist'=>true ); // to retrieve it for comparison.
	}

		public function initFaqs()
		{
			$this->pid                  = 'faq_id';
			$this->table                = 'faqs';
			$this->listOrder            = 'faq_id DESC';
			$this->statusField          = 'faq_parent';
			$this->statusLink           = "{e_PLUGIN}faqs/admin_config.php?mode=main&amp;action=edit&amp;iframe=1&amp;id={ID}"; // (no SEFs)
			$this->statusTitle          = "faq_question";

			$this->listQry 	= "SELECT  f.*, u.* FROM #faqs AS f LEFT JOIN #user AS u ON f.faq_author = u.user_id WHERE f.faq_parent != 0"; // Should not be necessary.


			$this->fields['faq_id']        = array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE);
			$this->fields['faq_question']     = array('title'=> LAN_TITLE,	 '__tableField'=>'f.faq_question',	'type' => 'text', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE, 'readParms'=>'truncate=60');
			$this->fields['faq_datestamp'] = array('title'=> LAN_DATESTAMP,	'type' => 'datestamp', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
			$this->fields['faq_parent']     = array( 'nolist'=>true ); // to retrieve it for comparison.
		}



		private function initFaqsPrefs()
		{
			$sql = e107::getDb();
			$faqCats = array();
			$sql->select('faqs_info', 'faq_info_id,faq_info_title');
			while($row = $sql->fetch())
			{
				$id = $row['faq_info_id'];
				$faqCats[$id] = 	$row['faq_info_title'];
			}

			$this->prefs['untranslatedFAQCat'] = array('title'=> "Untranslated FAQ Category", 'tab'=>0, 'type'=>'dropdown' );
			$this->prefs['untranslatedFAQCat']['writeParms']['optArray'] = $faqCats;

		}



		private function generateSystemLANS()
		{
			$sql = e107::getDb();
			$frm = e107::getForm();
			$rows = $sql->retrieve('links','*','',true);

			$writeFile = e_SYSTEM."lans/English_custom.php";

			$text = '<?php';

			$text .= "\n// e107 Custom Language File \n\n";
			$update = array();

			foreach($rows as $row)
			{
				if(empty($row['link_name']))
				{
					continue;
				}

				$name = str_replace('-','_',$frm->name2id($row['link_name']));
				$key = "CUSTLAN_".strtoupper($name);
				$text .= 'define("'.$key.'", "'.$row['link_name'].'");';
				$text .= "\n";
				$id = $row['link_id'];
				$update[$id] = $key;

				// $sql->update('links', 'link_name= "'.$key.'" WHERE link_id = '.$row['link_id'].' LIMIT 1');
			}

			if(!is_dir(e_SYSTEM."lans"))
			{
				mkdir(e_SYSTEM.'lans',0755);
			}

			if(file_exists($writeFile))
			{
				e107::getMessage()->addWarning("File already exists");
				e107::getMessage()->addWarning(print_a($text,true));
				return;
			}


			if(file_put_contents($writeFile, $text))
			{
				foreach($update as $id=>$val)
				{
					$sql->update('links', 'link_name= "'.$val.'" WHERE link_id = '.$id.' LIMIT 1'); //TODO add a checkbox preference for this.
				}

				e107::getMessage()->addSuccess(LAN_CREATED);
			}
			else
			{
				e107::getMessage()->addError(LAN_CREATED);
			}


		}



		/**
		 * @param array $languages
		 * @return array
		 */
		function getLangData($languages)
		{
			$sql2 = e107::getDb('sql2');
			$lng = e107::getLanguage();

			$fields = $this->fields;

			$sitelanguage = e107::getPref('sitelanguage');

			unset($fields['checkboxes'], $fields['options']);

			$selectFields = implode(",", array_keys($fields));

			$from = $this->getQuery('from',0);

			$query = "SELECT ".$selectFields." FROM #lan_{LANGUAGE}_".$this->table." WHERE ".$this->pid." !='' ORDER BY ".$this->listOrder; // ."  LIMIT ".$from.", ".$this->perPage;

			$langData = array();
			foreach($languages as $langu)
			{
				if($langu == $sitelanguage || !$sql2->isTable($this->table, $langu))
				{
					continue;
				}

				$lg = strtolower($langu);
				$qry = str_replace("{LANGUAGE}",$lg,$query);
				$key = $lng->convert($langu);
				$res =$sql2->gen($qry);
				while($row = $sql2->fetch())
				{
					$langData[$key][] = $row;
				}
				if($res === 0) // table empty but not missing.
				{
					$langData[$key][] = array();
				}
			}
			// print_a($langData['fr']);

			return $langData;
		}





		public function corePage()
		{
			$frm = e107::getForm();
			$lng = e107::getLanguage();
			$bng = e107::getSingleton('bingTranslate', e_PLUGIN."multilan/bing.class.php");


			$authKey = e107::pref('multilan','bing_client_secret');

			if(empty($authKey))
			{
				e107::getMessage()->addWarning("Bing Translation requires an API key. Please see the 'Bing' tab in the <a href='".e_PLUGIN."multilan/admin_config.php?mode=main&action=prefs'>preferences</a>");
				return false;
			}

			if(!empty($_GET['lanlanguage']))
			{
				$title = $lng->convert($_GET['lanlanguage']);
			}
			else
			{
				$title = "Choose Language";
			}

			$this->addTitle($title);

			$_SESSION['multilan_lanfilelist'] = array();

			$this->languageList = $bng->supportedLanguages();

			unset($this->languageList['en']);

			require_once(e_ADMIN."lancheck.php");
			$lck = new lancheck;
			$lck->thirdPartyPlugins(false);


			$text = $frm->open('corePage', 'get', e_SELF);
			$text .= $frm->hidden('mode', 'main');
			$text .= $frm->hidden('action', 'core');


			$text .= "<div class='alert-block' style='margin-bottom:10px'>";

			if(empty($_GET['lanlanguage']))
			{
				$text .= $frm->select('lanlanguage', $this->languageList, varset($_GET['lanlanguage']), array('class'=>'filter'), 'Select Language');
			}
			else
			{
				$text .= $frm->hidden('lanlanguage',$_GET['lanlanguage'],array('id'=>'lanlanguage'));
				$text .= "<button type='button' data-loading='".e_IMAGE."generic/loading_32.gif' class='btn btn-primary e-ajax-post' data-action='bing' value='Translate' data-src='".e_SELF."' ><span>".ADMIN_BING_ICON." Bing Translate</span></button>";
				$text .= "<button type='button' data-loading='".e_IMAGE."generic/loading_32.gif' class='btn btn-primary e-ajax-post' data-action='comment' value='Comment Out Deprecated LANs' data-src='".e_SELF."' ><span>".ADMIN_CLEAN_ICON." CleanUp LANs</span></button>";

				$text .= "<a class='btn btn-primary' href='".e_SELF."'>".ADMIN_REFRESH_ICON." Refresh</a>";
				$text .= " <span id='total-status'></span>";
			}
			$text .= "</div>";

			$text2 = '';

			if(!empty($_GET['lanlanguage']))
			{
				$newLanguage = $title;

				$tmp = $lck->get_comp_lan_phrases(e_LANGUAGEDIR."English/","English",1);
				$tmpExst = $lck->get_comp_lan_phrases(e_LANGUAGEDIR.$newLanguage."/",$newLanguage,1);
				unset($tmp['bom'],$tmpExst['bom']);

				$text2 .= $this->renderTable($tmp, 'core',$tmpExst);

				$tmp2 = $lck->get_comp_lan_phrases(e_PLUGIN,"English",3);
				$tmp2Exst = $lck->get_comp_lan_phrases(e_PLUGIN,$newLanguage,3);

				$tmp2Exst = $this->resetKeysToEnglish($tmp2Exst);

				unset($tmp2['bom'],$tmp2Exst['bom']);
				$text2 .= $this->renderTable($tmp2, 'plugin',$tmp2Exst);

				$tmp3 = $lck->get_comp_lan_phrases(e_THEME,"English",3);
				$tmp3Exst = $lck->get_comp_lan_phrases(e_THEME, $newLanguage,3);
				unset($tmp3['bom']);

				$tmp3Exst = $this->resetKeysToEnglish($tmp3Exst);

				$text2 .= $this->renderTable($tmp3, 'theme', $tmp3Exst);
			}

			$text2 .= $frm->close();


			$js = <<<JS

				$('.e-ajax-post').on('click', function(){

		            var form		= $(this).closest('form').attr('id');
		            var target 		= $(this).attr('data-target'); // support for input buttons etc.
		            var loading 	= $(this).attr('data-loading'); // image to show loading.
		            var handler		= $(this).attr('data-src');
		            var action		= $(this).attr('data-action');
		     		 // var data 	= $('#'+form).serialize();

					var lancode = $('#lanlanguage').val();

					if(lancode === '' || lancode === undefined)
					{
						alert("No Language Selected");
						return false;
					}
					
					$('#total-status').html('<i class="fa fa-spin fa-spinner"></i>');


					$('#' + form).find('.lanfile').each(function(e){
						val = $(this).text();
						theid = $(this).attr('id');

						if($('#check-'+theid).is(":not(:checked)")){

	                     //   alert("Checkbox is not checked."+theid);
	                        return;
	                    }

					//	$('#status-'+theid).html("<img src='"+loading+"' alt='' />");

						$('#status-'+theid).html('<i class="fa fa-spin fa-spinner"></i>');

				//		 alert(theid);
					    $.ajax({
						type: 'get',
						async: false,
						url: handler,
						data: { lanid: theid, language: lancode, action: action},
						success: function(data)
							{
								//	  	console.log(data);
								//	 alert('Done:'+ theid);
								 $('#status-'+theid).html(data);
							 }
						});



					});
					
					$('#total-status').html('');

					alert('Complete');
					return false;

				});
JS;





			e107::js('footer-inline', $js);


			$count = ($this->totalCharCount) ? "<div class='right' style='margin-top: -40px; padding: 10px;'><small>Total Chars: ".number_format($this->totalCharCount)."</small></div>" : '';

			return  $text . $count . $text2;

		// 	print_a($tmp);

		}


		function resetKeysToEnglish($array)
		{
			if(empty($array))
			{
				return $array;
			}

			$newArray = array();
			foreach($array as $k=>$v)
			{
				$key = str_replace($this->languageList,'English',$k);
				$newArray[$key] = $v;
			}

			return $newArray;
		}


		private function renderTable($data, $mode, $existing)
		{
			$frm = e107::getForm();
			$lng = e107::getLanguage();
			$languageCode   = e107::getParser()->filter($_GET['lanlanguage'], 'w');
			$language       = $lng->convert($languageCode);

			if($mode == 'core')
			{
				$toggleButton= $frm->checkbox_toggle('tog', 'lancheckbox');
			}
			else
			{
				$toggleButton= '';
			}

		//	$toggleButton = '<input name="e-column-toggle" value="jstarget:lancheckbox" class="btn btn-small checkbox toggle-all" type="button" />';


			$text = "<table class='table table-striped adminlist'>
				<colgroup>
					<col />
					<col style='width:10%' />
					<col style='width:10%' />
					<col style='width:50%' />
				</colgroup>
				<thead>
				<tr class='first'>
				<th>".$toggleButton." <span style='vertical-align: bottom;'>Language File</span></th>
				<th class='right' style='padding-right:40px'>Translated</th>
				<th class='right' style='padding-right:40px'>Character Count</th>
				<th>".LAN_STATUS."</th></tr>
				</thead>";

		//	var_dump($data);
			if(!empty($existing))
			{
		//		var_dump($existing);
			}

			foreach($data as $file => $lans)
			{

				$id = $frm->name2id($file);
				$status = '-';

				$_SESSION['multilan_lanfilelist'][$id] = '-'.$mode.'-'.$file;
				$_SESSION['multilan_lanfiledata'][$id] = $lans;
				$_SESSION['multilan_lanfiledata_existing'][$id] = null;

				$origCount = count($lans);
				$lanCount = 0;

				if(!empty($existing[$file]))
				{
					//$status .= "Exists ";
					$newid = str_replace($this->languageList,'english',$id);
					$_SESSION['multilan_lanfilelist_existing'][$newid] = '-'.$mode.'-'.$file;
					$_SESSION['multilan_lanfiledata_existing'][$newid] = $existing[$file];
					$lanCount = count($existing[$file]);

				}
				else
				{
					$lanCount = " Missing :".$file;
				}
				//var_dump($id);
				//	var_dump($newid);

				$charCount = $this->countChars($lans);

				$preFile = '';
				$postFile = '';

				if(!empty($language))
				{

					$newFile  = str_replace(array('-core-','-plugin-','-theme-','English'), array(e_LANGUAGEDIR.'English/',  e_PLUGIN, e_THEME, $language), $_SESSION['multilan_lanfilelist'][$id]);
					$origFile  = str_replace(array('-core-','-plugin-','-theme-'), array('',  e_PLUGIN, e_THEME), $_SESSION['multilan_lanfilelist'][$id]);



					if(file_exists($newFile))
					{

						$_SESSION['multilan_lanfilelist_existing'][$id] = $newFile;

						$typeArray = array('core'=>'', 'plugin'=>'P', 'theme'=>'T');
						$parms = array();
						$parms['mode'] = 'main';
						$parms['action'] = 'tools';
						$parms['sub'] = 'edit';
						$parms['file'] = str_replace('../../','../',$origFile);
						$parms['lan'] = $language;
						$parms['iframe'] = 1;
						$parms['type'] = $typeArray[$mode];

						$editUrl = e_ADMIN."language.php?".http_build_query($parms,'&amp;');


						$status = ADMIN_TRUE_ICON; // e107::getParser()->toGlyph('fa-check');
						$preFile= "<a href='".$editUrl."' class='e-modal' data-modal-caption=\"".str_replace('../','',$newFile)."\">";
						$postFile = '</a>';
					}
				}

				$text .= "
				<tr>

					<td id='".$id."' class='lanfile'>
						<label class='checkbox'><input name='lancheckbox[]' value='1' id='check-".$id."' type='checkbox'>".$preFile.$file.$postFile."</label>
					</td>
					<td class='right' style='padding-right:40px'>".$this->getPerc($lanCount,$origCount)."</td>
					<td class='right' style='padding-right:40px'>".$charCount."</td>
					<td id='status-".$id."'>".$status."</td>
				</tr>";
			}

			$text .= "</table>";

		//	var_dump($_SESSION['multilan_lanfilelist_existing']);

			return $text;
		}

		private function getPerc($lanCount,$origCount)
		{
		//	return $lanCount .' / '.$origCount;

			$val = round(($lanCount / $origCount) * 100);

			if($val> 100)
			{
				return "<span class='text-danger'>".$val."%</span>";
			}

			$text = ($val != 100) ? "<span class='text-warning'>".$val."%</span>" : $val . '%';

			return $text;
		}

		private function countChars($lans)
		{
			$count = 0;

			foreach($lans as $value)
			{
				$count += strlen($value);
			}

			$this->totalCharCount += $count;

			if($count > 1500)
			{
				return "<span title='high character count' class='label label-important label-danger'>".$count."</span>";
			}

			return $count;
		}


		public function editorPage()
		{

			$lck = e107::getSingleton('lancheck', e_ADMIN."lancheck.php");


			if($return = $lck->init())
			{
				if($return['file'])
				{
					$this->addTitle($return['file']);
				}

				return $return['text'];
			}


			// show_packs();

			$this->localPacks = $lck->getLocalLanguagePacks();

			return $this->renderLanguagePacks();

		}

			/**
		 * List the installed language packs.
		 * @return string
		 */
		private function renderLanguagePacks()
		{
			$frm = e107::getForm();
			$ns = e107::getRender();
			$tp = e107::getParser();

		//	if(is_readable(e_ADMIN."ver.php"))
			{
			//	include(e_ADMIN."ver.php");
				list($ver, $tmp) = explode(" ", e_VERSION);
			}

			$lck = e107::getSingleton('lancheck', e_ADMIN."lancheck.php");

			$release_diz = defset("LANG_LAN_30","Release Date");
			$compat_diz = defset("LANG_LAN_31", "Compatibility");
		//	$lan_pleasewait = (deftrue('LAN_PLEASEWAIT')) ?  $tp->toJS(LAN_PLEASEWAIT) : "Please Wait";

			$buttonLink = e_ADMIN."language.php?mode=main&amp;action=tools";


			$text = "<form id='lancheck' method='post' action='".e_REQUEST_URI."'>
				<table class='table adminlist table-striped'>
				<colgroup>
					<col style='width:20%' />
					<col style='width:20%' />
					<col style='width:20%' />
					<col style='width:15%' />
					<col style='width:25%' />
				</colgroup>";
			$text .= "<thead>
			<tr>
			<th>".ADLAN_132."</th>
			<th class='text-center'>".$release_diz."</th>
			<th class='text-center'>".$compat_diz."</th>
			<th class='text-center'>".LAN_STATUS."</td>
			<th class='text-right' style='white-space:nowrap'>".LAN_OPTIONS."</td>
			</tr>
			</thead>
			";

	//		$text .= "<tr><th colspan='5'>".LAN_INSTALLED."</th></tr>";

		//	$onlinePacks = $lck->getOnlineLanguagePacks();
		//	$localPacks = $lck->getLocalLanguagePacks();

			foreach($this->localPacks as $language=>$value)
			{

				$errFound = (isset($_SESSION['lancheck'][$language]['total']) && $_SESSION['lancheck'][$language]['total'] > 0) ?  TRUE : FALSE;


				$text .= "<tr>
				<td><span class='language-name'>".$language."</a></td>
				<td class='text-center'>".$value['date']."</td>
				<td class='text-center'>".$value['compatibility']."</td>
				<td class='text-center'>".( $errFound ? ADMIN_FALSE_ICON : ADMIN_TRUE_ICON )."</td>
				<td class='text-right'>";

			//	$text .= "<input type='submit' name='language_sel[{$language}]' value=\"".LAN_CHECK_2."\" class='btn btn-primary' />";
				$text .= "<a href='".$buttonLink."&amp;sub=verify&amp;lan=".$language."' class='btn btn-default' >".$tp->toGlyph('fa-search').LAN_CHECK_2."</a>";

			/*	$text .= "
				<input type='submit' name='ziplang[{$language}]' value=\"".LANG_LAN_23."\" class='btn btn-default' onclick=\"this.value = '".$lan_pleasewait."'\" />";
			*/
				$text .= "</td>
				</tr>";
			}

		//	$text .= "<tr><th colspan='5'>".defset('LANG_LAN_151','Available')."</th></tr>"; // don't translate this.

		//	$text .= $this->renderOnlineLanguagePacks();

			$text .= "
			</tr></table>";


			$text .= "<div class='nav navbar'><small class='navbar-text'>&nbsp;</small></div>";



			$text .= "</form>";


			return $text;



		}




		public function toolsPage()
		{



			$frm = e107::getForm();

			$text2 = $frm->open('multilan-links');
			$text2 .= "<table class='table table-bordered'><tr><td>
		This will generate LAN definitions for all your sitelinks and store them in a custom language file within your system folder and then update all your link names to use them.
		</td>
		<td>

			".$frm->admin_button('generate_lanlinks', 'no-value', 'delete', "Generate LANs")."
			</td></tr>





			</table>";

			$text2 .= $frm->close();

			return $text2;

		}

	}

	class status_form_ui extends e_admin_form_ui
	{

		function news_title($curval,$mode,$att)
		{
			$tp = e107::getParser();
			if($mode == 'read')
			{
				$row            = $this->getController()->getListModel()->getData();

				return $curval ."<div><small><small>".$tp->text_truncate($row['news_summary'],60)."</small></small></div>";
			}
		//	return print_a($att,true);

		//	$id = $this->getController()->getMode();
		//	$row    = $this->getController()->getListModel()->getData();

		//	print_a($row);

		//	return $curVal; // ."<small>".$row['news_summary']."</small>";



		}



		function language_navigation($curVal,$mode)
		{

			$lng = e107::getLanguage();
			$frm = e107::getForm();
			$languages = $lng->installed();

			sort($languages);

			$text = "<table class='table table-striped table-bordered table-condensed'>
					<colgroup>
					<col style='width:20%' />
					<col style='width:80%' />
					</colgroup>

		        <tr>
			        <th>Language</th>
			        <th>Sitelink Status</th>
		        </tr>";

			foreach($languages as $v)
			{
				$value = isset($curVal[$v]) ? $curVal[$v] : 1;
				$text .= "<tr><td>".$v."</td><td>".$frm->radio_switch('language_navigation['.$v.']', $value)."</td></tr>";
			}

			$text .= "</table>";

			return $text;

		}


		function syncLanguages($curVal) // preference.
		{

			$frm = e107::getForm();
			$sql = e107::getDb('sql2');
			$modeData = $this->getController()->getDispatcher()->getMenuData();

			$text2 = "<table class='table table-striped table-condensed table-bordered'>";

			$options = array();
			$tableInstalled = array();

			$opts = e107::getLanguage()->installed();

			foreach($opts as $v)
			{
				if($v == 'English')
				{
					continue;
				}

				$options[$v] = $v;

				foreach($modeData as $key=>$val)
				{
					list($mode,$action) = explode("/",$key);
					if($action != 'list')
					{
						continue;
					}

					$tableInstalled[$mode][$v] = $sql->db_Table_exists($mode,$v);
				}
			}


			foreach($modeData as $k=>$v)
			{
				list($mode,$action) = explode("/",$k);
				if($action != 'list')
				{
					continue;
				}

				$lanOpts = $options;

				foreach($lanOpts as $keyOpt=>$opt)
				{
					if(empty($tableInstalled[$mode][$opt]))
					{
						$lanOpts[$opt] .= " (not installed)" ; // " <span class='label label-warning'>Not installed</span>";
					}
				}

				$curValMode= isset($curVal[$mode]) ? $curVal[$mode] : '';

				$text2 .= "<tr><td>".$v['caption']."</td><td>".$frm->checkboxes('syncLanguages['.$mode.'][]', $lanOpts, $curValMode, array('useKeyValues'=>1));

				$text2 .= "</td></tr>";
			}


		//	$text2 .= "<tr><td>Pages</td><td>".$frm->checkboxes('syncLangs[page][]', $options, $prefs['page'], array('useKeyValues'=>1))."</td></tr>";
		//	$text2 .= "<tr><td>FAQs</td><td>".$frm->checkboxes('syncLangs[faqs][]', $options, $prefs['faqs'], array('useKeyValues'=>1))."</td></tr>";



			$text2 .= "</table>";

			return $text2;
		}


		function characterCount($curval,$mode,$att)
		{
			$row            = $this->getController()->getListModel()->getData();
			$id = $this->getController()->getMode();

			$fields         = multilan_adminArea::getTranslationFields($id);

			$sum = array();

			foreach($fields as $k)
			{
				$sum[] = strlen($row[$k]);
			}

			return array_sum($sum);

		}


		function findTranslations($curval,$mode,$att)
		{

			$langs = $att['field'];

			$lng = e107::getLanguage();
			$tp = e107::getParser();

			$langData       = $this->getController()->langData;
			$row            = $this->getController()->getListModel()->getData();
			$pid            = $this->getController()->getPrimaryName();
			$transField     = $this->getController()->statusField;
			$statusLink     = $this->getController()->statusLink;
			$statusTitle    = $this->getController()->statusTitle;

			$itemid             = $row[$pid];

		//	print_a($row);

			if(!isset($langData[$langs]))
			{
				return "&nbsp;";
			}

			$language = e107::getLanguage()->convert($langs);

			$text = "<b>&middot;</b>";

			foreach($langData[$langs] as $rw)
			{

				if(($rw[$pid]==$row[$pid]))
				{
				//	print_a('lang: '.$rw[$transField].' => orig:'.$row[$transField]);
				//	$icon = ($rw[$transField] == $row[$transField]) ?  ADMIN_TRUE_ICON : ADMIN_FALSE_ICON;
					$icon = $this->getStatusIcon($rw,$row);
					$link = $tp->replaceConstants(str_replace('{ID}', $rw[$pid], $statusLink),'full');
					$subUrl = $lng->subdomainUrl($langs, $link);

					$text =  "<a class='e-modal'  href='".$subUrl."' data-modal-caption=\"".$language.": #".$itemid." ".$tp->toAttribute($row[$statusTitle])."\" title=\"".$tp->toAttribute($rw[$statusTitle])."\">".$icon."</a></span>";
					break;
				}
			}

			return "<span id='status-".$language."-".$itemid ."' class='lanfile'>".$text."</span>";


		}


		/**
		 * @param $rw  Language Table
		 * @param $row  English table
		 * @return string
		 */
		function getStatusIcon($rw,$row)
		{
			$transField     = $this->getController()->statusField;
			$reviewField    = e107::pref('multilan','autotranslatedClass');
			$statusTitle    = $this->getController()->statusTitle;


	e107::getDebug()->log('transField: '.$transField.'    statusField: '.$statusTitle);

		//	print_a($transField);

	//	print_a($rw);
	//	print_a($row);

		/*
			$id = $this->getController()->getMode();
			$translatedFields  = multilan_adminArea::getTranslationFields($id);

			$translated = false;

			foreach($translatedFields as $fld)
			{
				if($rw[$fld] != $row[$fld])
				{
					$translated = true;
					break;
				}
			}
*/

		//	return $transField."(".$rw[$transField].")";

			if($rw[$transField] == $reviewField)
			{
				return ADMIN_BING_ICON;
			}

			if($rw[$transField] == $row[$transField] && ($rw[$statusTitle] != $row[$statusTitle] ))
			{
				return ADMIN_TRUE_ICON;
			}

			if($rw[$transField] != $row[$transField])
			{
				return ADMIN_FLAG_ICON;
			}

			return ADMIN_FALSE_ICON;

		}



		function offline_languages($curval)
		{
			$lng = e107::getLanguage();


			$text = "<table class='table table-striped table-bordered table-condensed'>
					<colgroup>
					<col style='width:20%' />
					<col style='width:10%' />
					<col style='width:10%' />
					<col style='width:60%' />
					</colgroup>



";

			$text .="
		        <tr>
			        <th>Language</th>
			        <th class='center'>Visible</th>
			        <th class='center'>Maintenance</th>
			        <th>Redirect</th>
		        </tr>";

		//	$tmp = explode(",",e_LANLIST);

			$tmp = $lng->installed();

			sort($tmp);

			foreach($tmp as $lang)
			{
				// if($lang == $pref['sitelanguage']){ continue; }

				$checked_0 = (empty($curval[$lang])) ? "checked='checked'" : "";
				$checked_1 = (!empty($curval[$lang]) && $curval[$lang] == 1) ? "checked='checked'" : "";
				$checked_2 = (!empty($curval[$lang]) &&$curval[$lang] == 2) ? "checked='checked'" : "";


				if(!empty($curval[$lang]) && strlen($curval[$lang])>3)
				{
					$url_value = $curval[$lang];
					$checked_2 = "checked='checked'";
				}
				else
				{
					$url_value = '';
				}

				$fieldName = "offline_languages[".$lang."]";
				$fieldNameUrl = "offline_languages[".$lang."-url]";


				$curValURL = isset($curval[$lang."-url"]) ? $curval[$lang."-url"] : '';

				$text .="
		        <tr>
			        <td>{$lang} (".$lng->convert($lang).")</td>
			        <td class='center'>".$this->radio($fieldName, 0, $checked_0)."

			        </td>
			        <td class='center'>".$this->radio($fieldName, 1, $checked_1)."

			        </td>
			        <td class='form-inline'>
			            ".$this->radio($fieldName, 2, $checked_2)." ".
			            $this->text($fieldNameUrl, $curValURL, 255, array('size'=>'xxlarge'))."
			            <div class='field-help' data-placement='top'>eg. http://wherever.com or {e_PLUGIN}myplugin/myplugin.php</div>
			        </td>

				</tr>";
			}


			$text .= "
		    </table>
		  	 ";


			return $text;


		}



}

		
new multilan_adminArea();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

?>