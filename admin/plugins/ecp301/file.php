<?php
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);
	
	main_content("%HEADLINE%", utf8_decode($output), '', 1);
?>