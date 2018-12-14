<?php
	defined ("main") or die ("no direct access");
	$title = $allgAr["title"]." :: ";
	$hmenu = "%TITLE%";
	$design = new design ( $title , $hmenu );
	$design->header();
	
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	
	/* ezStats Start */
	echo utf8_decode($output);
	/* ezStats End */
	
	$design->footer();
?>