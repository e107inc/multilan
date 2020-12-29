<?php


//v2.x Standard for extending admin areas.


class multilan_admin
{
	private $active = false;
	private $languageTables = array();


	function __construct()
	{
		// $pref = e107::pref('core','trackbackEnabled');
		$this->active = e107::pref('multilan','admin_translations_tab', e_UC_NOBODY);

	//	e107::js('footer', e_PLUGIN_ABS.'reference/reference.js');
		if(!defined('ADMIN_FLAG_ICON'))
		{
			define('ADMIN_FLAG_ICON', "<img src='".e_PLUGIN."multilan/images/flag_16.png' class='un-translated' alt='un-translated' />");
			define('ADMIN_BING_ICON', "<img src='".e_PLUGIN."multilan/images/bing_16.png' class='auto-translated' alt='auto-translated' />");
		}
	}


	/**
	 * Extend Admin-ui Parameters
	 * @param $ui admin-ui object
	 * @return array
	 */
	public function config($ui)
	{
		$action     = $ui->getAction(); // current mode: create, edit, list
		$type       = $ui->getEventName(); // 'wmessage', 'news' etc.
		$id         = $ui->getId();
		$sql        = e107::getDb();

		$config = array();

		if($action !== 'edit' || !check_class($this->active))
		{
			return $config;
		}


		switch($type)
		{
			case "news":

				$config['tabs'] = array('multilan'=>'Translations');
				$config['fields']['translations'] =   array ( 'title' =>"", 'type' => 'method',  'tab'=>'multilan',  'writeParms'=> array('nolabel'=>true, 'size'=>'xxlarge', 'placeholder'=>''), 'width' => 'auto', 'help' => '', 'readParms' => '', 'class' => 'left', 'thclass' => 'left',  );

				break;
		}

		//Note: 'url' will be returned as $_POST['x_reference_url']. ie. x_{PLUGIN_FOLDER}_{YOURKEY}

		return $config;

	}


	/**
	 * Process Posted Data.
	 * @param $ui admin-ui object
	 */
	public function process($ui, $id=0)
	{

		$data       = $ui->getPosted();
		$type       = $ui->getEventName();
		$action     = $ui->getAction(); // current mode: create, edit, list

	/*	$sql = e107::getDb();

	//	e107::getMessage()->addDebug("Object: ".print_a($ui,true));
	//	e107::getMessage()->addInfo("ID: ".$id);
	//	e107::getMessage()->addInfo("Action: ".$action);
	//	e107::getMessage()->addInfo(print_a($data,true));

		if($action == 'delete')
		{
			return;
		}

		if(e_LANGUAGE != 'English')
		{
			return;
		}

		if(!empty($id) && $this->active)
		{

			if(!empty($data['x_reference_url']))
			{

				$refData = json_encode($data['x_reference_url'], JSON_PRETTY_PRINT);

				$insert = array(
						"ref_pid"=> $id,
						"ref_table"=>$type,
						'ref_title'=>$data['news_title'],
						'ref_data'=> $refData,
						'_DUPLICATE_KEY_UPDATE' => true
				);

				$result = $sql->insert("reference", $insert);

				if($result !==false)
				{
					e107::getMessage()->addSuccess("References Saved");
				}
				else
				{
					e107::getMessage()->addError("Couldn't save references: ".var_export($result,true));
					e107::getMessage()->addDebug(var_export($insert,true));
				}


			}


		}

*/

	}



}

class multilan_admin_form
{

	function __construct()
	{


	}

	/**
	 * @param array $rw  Language Table
	 * @param array $row  English table
	 * @return string
	 */
	function getStatusIcon($rw,$row)
	{
		$transField     = 'news_class';
		$reviewField    = e107::pref('multilan','autotranslatedClass');
		$statusTitle    = 'news_title';

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



	function x_multilan_translations($curval,$mode,$att)
	{

		$ui = e107::getAdminUI();

		$reviewField    = e107::pref('multilan','autotranslatedClass');

		$englishRow = $ui->getController()->getModel()->getData();

		if($mode !== 'write'|| empty($_GET['id']))
		{
			return '';
		}

		$vals = array();

		$languageTables = e107::getDb()->db_IsLang(array('news'/*,'page','faqs','generic'*/),true);

		if(empty($languageTables))
		{
			return '';
		}

		$sql = e107::getDb();

		$key = MPREFIX."news";

		$lng = e107::getLanguage();

		$text = "<table class='table table-striped table-condensed table-bordered'>
		<tr><th style='width:25%'>Language</th><th>".LAN_TITLE."</th><th style='width:25%'>Status</th></tr>";

		foreach($languageTables as $lang => $val)
		{
			$rows = $sql->retrieve('SELECT news_id,news_title,news_class FROM '.$val[$key].' WHERE news_id = '.intval($_GET['id']).' LIMIT 1', true);

			$row = !empty($rows[0]) ? $rows[0] : array();

			$url = !empty($row['news_title']) ? $lng->subdomainUrl($lang, e_REQUEST_URI) : '';

			$status = ($url) ? "<a href='".$url."' rel='external'>".$row['news_title']."</a>" : $row['news_title'];

			 $text .= "<tr>
		            <td class='text-left'>".ucfirst($lang)."</td>
		            <td>". $status."</td>
		             <td>".$this->getStatusIcon($row,$englishRow)."</td>
		            </tr>";

		}

	
		$text .= "</table>";

		return $text;



	}

}

