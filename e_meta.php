<?php

if (!defined('e107_INIT')) { exit; }


class multilan_nav
{

		protected $type = 'globe';

		function __construct()
		{
			$this->type = e107::pref('multilan','language_nav_icon');

			define('LAN_MULTILAN_NAVICON', $this->icon());
		}


		function icon()
		{

			if($this->type == 'flag' && ($img = $this->getFlag()))
			{
				return $img;
			}

			return e107::getParser()->toGlyph('globe.glyph');
		}


		function getFlag()
		{

			if(file_exists(e_PLUGIN."multilan/images/flags/16/".e_LAN.".png"))
			{
				return "<img src='".e_PLUGIN_ABS."multilan/images/flags/16/".e_LAN.".png' alt='".e_LANGUAGE."' />";
			}

			e107::getMessage()->addDebug("Couldn't find:  ".e_PLUGIN_ABS."multilan/images/flags/16/".e_LAN.".png");

			return false;
		}

}

new multilan_nav;

?>