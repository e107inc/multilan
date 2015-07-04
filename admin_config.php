<?php

/*
* e107 website system
* Multiple Languages Plugin for e107.
* Copyright (C) 2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*/

require_once('../../class2.php');
if (!getperms('P') || !e107::isInstalled('multilan'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}



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
		'main/prefs' 	    => array('caption'=> LAN_PREFS, 'perm' => '0'), // Preferences
		'main/tools'       =>array('caption'=>'Tools', 'perm'=>'0'),
		'option2'           => array('divider'=>true),
		'news/list'			=> array('caption'=> 'News', 'perm' => 'P'),
		'page/list' 		=> array('caption'=> 'Page', 'perm' => 'P'),
		'faqs/list' 		=> array('caption'=> 'FAQs', 'perm' => 'P'),



	);


	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'Multiple Languages';


	function init()
	{
		$sitelanguage = e107::getPref('sitelanguage');
		if(e_LANGUAGE != $sitelanguage)
		{

			e107::getMessage()->addWarning("Please switch to ".$sitelanguage." to view.");
			$this->adminMenu = array();

			return false;
		}


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
	//	protected $listOrder	= null;

		protected $fields       = array();
		protected $fieldpref    = array();

		public $langData        = array();
		public $statusField     = null;  // field name.
		public $statusLink      = null;
		public $statusTitle     = null; // fieldName

		protected $preftabs        = array("Data Sync", "Offline" );

		protected $prefs = array(
			'syncLanguages'         => array('title'=> "Sync Table Content",  'tab'=>0, 'type'=>'method', 'data'=>'str'),
			'untranslatedClass'	    => array('title'=> "Untranslated Class", 'tab'=>0, 'type'=>'userclass', 'writeParms'=>array('default'=>'TRANSLATE_ME')),
			'offline_languages'     => array('title' => "Offline", 'tab'=>1, 'type'=>'method', 'data'=>'str'),
			'offline_excludeadmins' => array('title'=>'Exclude Admins from redirect', 'tab'=>1, 'type'=>'boolean'),
			'language_navigation'    => array('title'=>"Language Navigation", 'type'=>'method', 'tab'=>1)

		//	'retain sefurls'	  => array('title'=> "Untranslated Class", 'tab'=>0, 'type'=>'userclass' ),
		);


		function init()
		{
			if(e107::isInstalled("faqs"))
			{
				$this->initFaqsPrefs();
			}

			if(!empty($_POST['generate_lanlinks']))
			{
				$this->generateSystemLANS();
			}

			if($this->getMode()== 'main')
			{
				return;
			}


			if($this->initAll() === false)
			{
				return false;
			}



		}


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


			$initType = 'init'.ucfirst($this->getMode());

			$this->$initType(); // eg. initNews();

			$this->langData = $this->getLangData($languages);

			foreach($languages as $k=>$v)
			{
				if($v == $sitelanguage)
				{
					continue;
				}

				$key = $lng->convert($v);
				$this->fields[$key] = array('title'=> $key,	'type' => 'method', 	'data' => 'str',  'method'=>'findTranslations',	'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
			}

			$this->fields['options']    = array('title'=> 'Status',			'type' => 'method',		'nolist'=>true,		'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center');
			$this->fieldpref = array_keys($this->fields);


		}


		public function initNews()
		{
			$this->pid                  = 'news_id';
			$this->table                = 'news';
			$this->listOrder            = 'news_id DESC';
			$this->statusField          = 'news_class';
			$this->statusLink           = "{e_BASE}news.php?item.{ID}"; // (no SEFs)
			$this->statusTitle          = "news_title";

			$this->fields['news_id']        = array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE);
			$this->fields['news_title']     = array('title'=> LAN_TITLE,		'type' => 'text', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
			$this->fields['news_datestamp'] = array('title'=> LAN_DATESTAMP,	'type' => 'datestamp', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
			$this->fields['news_class']     = array( 'nolist'=>true ); // to retrieve it for comparison.
		}


		public function initPage()
		{
			$this->pid                  = 'page_id';
			$this->table                = 'page';
			$this->listOrder            = 'page_id DESC';
			$this->statusField          = 'page_class';
			$this->statusLink           = "{e_BASE}page.php?id={ID}"; // (no SEFs)
			$this->statusTitle          = "page_title";

			$this->fields['page_id']        = array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE);
			$this->fields['page_title']     = array('title'=> LAN_TITLE,		'type' => 'text', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
			$this->fields['page_datestamp'] = array('title'=> LAN_DATESTAMP,	'type' => 'datestamp', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
			$this->fields['page_class']     = array( 'nolist'=>true ); // to retrieve it for comparison.

		}


		public function initFaqs()
		{
			$this->pid                  = 'faq_id';
			$this->table                = 'faqs';
			$this->listOrder            = 'faq_id DESC';
			$this->statusField          = 'faq_parent';
			$this->statusLink           = "{e_PLUGIN}faqs/faqs.php?id={ID}"; // (no SEFs)
			$this->statusTitle          = "faq_question";

			$this->fields['faq_id']        = array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE);
			$this->fields['faq_question']     = array('title'=> LAN_TITLE,		'type' => 'text', 			'data' => 'str',		'width' => 'auto',	'thclass' => 'left', 'class'=>'left',  'readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE);
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

			unset($fields['checkboxes'], $fields['options']);

			$selectFields = implode(",", array_keys($fields));

			$from = $this->getQuery('from',0);

			$query = "SELECT ".$selectFields." FROM #lan_{LANGUAGE}_".$this->table." WHERE ".$this->pid." !='' ORDER BY ".$this->listOrder; // ."  LIMIT ".$from.", ".$this->perPage;

			$langData = array();
			foreach($languages as $langu)
			{
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


			return $langData;
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
				$text .= "<tr><td>".$v."</td><td>".$frm->radio_switch('language_navigation['.$v.']', varset($curVal[$v],1))."</td></tr>";
			}

			$text .= "</table>";

			return $text;

		}


		function syncLanguages($curVal) // preference.
		{

			$frm = e107::getForm();
			$modeData = $this->getController()->getDispatcher()->getMenuData();

			$text2 = "<table class='table table-striped table-condensed table-bordered'>";

			$options = array();
			$opts = e107::getLanguage()->installed();

			foreach($opts as $v)
			{
				if($v == 'English')
				{
					continue;
				}

				$options[$v] = $v;
			}

	//		print_a($curVal);

			foreach($modeData as $k=>$v)
			{
				list($mode,$action) = explode("/",$k);
				if($action != 'list')
				{
					continue;
				}

				$text2 .= "<tr><td>".$v['caption']."</td><td>".$frm->checkboxes('syncLanguages['.$mode.'][]', $options, varset($curVal[$mode]), array('useKeyValues'=>1))."</td></tr>";
			}


		//	$text2 .= "<tr><td>Pages</td><td>".$frm->checkboxes('syncLangs[page][]', $options, $prefs['page'], array('useKeyValues'=>1))."</td></tr>";
		//	$text2 .= "<tr><td>FAQs</td><td>".$frm->checkboxes('syncLangs[faqs][]', $options, $prefs['faqs'], array('useKeyValues'=>1))."</td></tr>";



			$text2 .= "</table>";

			return $text2;
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

		//	print_a($langData);
		//	print_a($row);

			if(!isset($langData[$langs]))
			{
				return "&nbsp;";
			}

			foreach($langData[$langs] as $rw)
			{
				if(($rw[$pid]==$row[$pid]))
				{
				//	print_a('lang: '.$rw[$transField].' => orig:'.$row[$transField]);
					$icon = ($rw[$transField] == $row[$transField]) ?  ADMIN_TRUE_ICON : ADMIN_FALSE_ICON;
					$link = $tp->replaceConstants(str_replace('{ID}', $rw[$pid], $statusLink),'full');
					$subUrl = $lng->subdomainUrl($langs, $link);

					return  "<a class='e-modal' href='".$subUrl."' title=\"".$rw[$statusTitle]."\">".$icon."</a>";
				}
			}

			return "&nbsp;-";
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

				$text .="
		        <tr>
			        <td>{$lang} (".$lng->convert($lang).")</td>
			        <td class='center'>".$this->radio($fieldName, 0, $checked_0)."

			        </td>
			        <td class='center'>".$this->radio($fieldName, 1, $checked_1)."

			        </td>
			        <td class='form-inline'>
			            ".$this->radio($fieldName, 2, $checked_2)." ".
			            $this->text($fieldNameUrl, varset($curval[$lang."-url"],''), 255, array('size'=>'xxlarge'))."
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