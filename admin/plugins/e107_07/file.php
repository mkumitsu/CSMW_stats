<?php
	require_once("../../class2.php");
	if (!defined("e107_INIT")) { exit(); }
	require_once(HEADERF);
	
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	
	/* ezStats Start */
	$ns -> tablerender("%HEADLINE%", $output);    
	/* ezStats End */
	
	require_once(FOOTERF);
?>