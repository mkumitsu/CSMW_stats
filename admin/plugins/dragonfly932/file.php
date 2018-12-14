<?php
	header('Content-Type:text/html; charset=UTF-8');
	
	if (!defined('CPG_NUKE')) { exit; }
	$pagetitle .= "%HEADLINE%";
	require_once('header.php');
	OpenTable();
	
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	
	/* ezStats Start */
	echo $output;
	/* ezStats End */
	
	CloseTable();
?>