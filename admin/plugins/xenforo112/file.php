<?php
	$startTime = microtime(true);
	$kotomi_indexFile = "./";
	$kotomi_container = true;
	$fileDir = dirname(__FILE__)."/{$kotomi_indexFile}";
	require "{$fileDir}/library/Dark/Kotomi/KotomiHeader.php";
	
	/* ezStats Start */
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	echo $output;
	/* ezStats End */
	
	require "{$fileDir}/library/Dark/Kotomi/KotomiFooter.php";
?>