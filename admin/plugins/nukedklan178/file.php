<?php
	if (!defined("INDEX_CHECK"))
		die ("<div style=\"text-align: center;\">You cannot open this page directly</div>");
	
	header('Content-Type:text/html; charset=UTF-8');
	
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	
	opentable();
	/* ezStats Start */
	echo "<div style=\"text-align: center;\"><big><b>%HEADLINE%</b></big></div>\n";
	echo $output;
	/* ezStats End */
	closetable();
?>