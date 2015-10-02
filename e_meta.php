<?php

if (!defined('e107_INIT')) { exit; }


class multilan_nav
{

		protected $type = 'globe';

		function __construct()
		{
			define('LAN_MULTILAN_NAVICON', $this->icon());
			$this->type = e107::pref('multilan','language_nav_icon', 'globe');
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
			if(is_readable(e_PLUGIN."multilan/images/flags/16/".e_LAN.".png"))
			{
				return "<img src='".e_PLUGIN_ABS."multilan/images/flags/16/".e_LAN.".png' alt='".e_LANGUAGE."' />";
			}
		}

}

new multilan_nav;

?>