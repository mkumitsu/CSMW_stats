<?php
	header('Content-Type:text/html; charset=UTF-8');
	
	if (!defined("MODULE_FILE")) { die ("You can not access this file directly...");}
	require_once("mainfile.php");
	$module_name = basename(dirname(__FILE__));
	get_lang($module_name);
	$pagetitle = "- $module_name";
	
	include("header.php");
	OpenTable();
	
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	
	/* ezStats Start */
	echo "<h1>%HEADLINE%</h1>";
	echo $output;
	/* ezStats End */
	
	CloseTable();
	include("footer.php");
?>