<?php
	if(!defined('pkFRONTEND') || pkFRONTEND!='public')
		die('Direct access to this location is not permitted.');
	
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	
	/* ezStats Start */
	echo "<h1>%HEADLINE%</h1>";
	echo $output;
	/* ezStats End */
?>