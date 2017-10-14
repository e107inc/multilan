<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2017 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	require_once("../../class2.php");

if(!ADMIN)
{
	exit;
}

	require_once('bing.class.php');
	$bng = new bingTranslate;

$xml = <<<TMPL

TMPL;

	$text = "Hello World";


	$bng->test($text,false);


