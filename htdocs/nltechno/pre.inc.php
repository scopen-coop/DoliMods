<?php
/* Copyright (C) 2008-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
 */

/**
 *		\file 		htdocs/nltechno/pre.inc.php
 *		\ingroup    nltechno
 *		\brief      File to manage left menu for NLTechno module
 *		\version    $Id: pre.inc.php,v 1.6 2011/01/16 17:00:20 eldy Exp $
 */

define('NOCSRFCHECK',1);

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../dolibarr/htdocs/main.inc.php");     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (! $res && file_exists("../../../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (! $res) die("Include of main fails");

$user->getrights('nltechno');


function llxHeader($head = "", $title="", $help_url='')
{
	global $conf,$langs;
	$langs->load("agenda");

	top_menu($head, $title);

	$menu = new Menu();

	left_menu($menu->liste, $help_url);
}
?>
