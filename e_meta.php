<?php

if (!defined('e107_INIT')) { exit; }


class multilan_nav
{

		protected $type = 'globe';

		function __construct()
		{
			e107::css('multilan','multilan.css');
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
			return "<i class='multilan flag-".e_LAN."'></i>";
		}

}

new multilan_nav;

