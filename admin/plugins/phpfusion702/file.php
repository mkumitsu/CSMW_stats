<?php
	header('Content-Type:text/html; charset=UTF-8');
	
	require_once "maincore.php";
	require_once THEMES."templates/header.php";

	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);

	add_to_title($locale['global_200']."%HEADLINE%");
	opentable("%HEADLINE%");

	/* ezStats Start */
	echo $output;
	/* ezStats End */

	closetable();

	require_once THEMES."templates/footer.php";
?>