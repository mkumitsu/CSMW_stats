<?php
	define('IN_MYBB', 1); 
	require "./global.php"; 

	add_breadcrumb("%HEADLINE%", "ezstats3_wowp.php");

	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);

	$example = '
		<html>
			<head>
				<title>'.$mybb->settings[bbname].' - %HEADLINE%</title>
				'.$headerinclude.'
			</head>
			<body>
				'.$header.'
				<br />
				<!-- Content: Start -->
					'.$output.'
				<!-- Content: End -->
				'.$footer.'
			</body>
		</html>
	';

	output_page($example); 
?>