<?php
	## OUTPUT BUFFER START ##
	include("../inc/buffer.php");
	
	## INCLUDES ##
	include(basePath."/inc/config.php");
	include(basePath."/inc/bbcode.php");
	
	## SETTINGS ##
	$time_start = generatetime();
	lang($language);
	$dir = "ezstats3_wowp";
	$where = "ezStats 3 for World of Warplanes";
	
	## SECTIONS ##
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	
	$index = show($dir."/ezstats3_wowp", array(
		"head" => "%HEADLINE%",
		"show" => $output)
	);
	
	## SETTINGS ##
	$title = $pagetitle." - ".$where."";
	$time_end = generatetime();
	$time = round($time_end - $time_start,4);
	page($index, $title, $where,$time);
	
	## OUTPUT BUFFER END ##
	header('Content-Type:text/html; charset=UTF-8');
	gz_output();
?>
