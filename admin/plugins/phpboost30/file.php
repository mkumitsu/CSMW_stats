<?php
	$ch = curl_init("%PATH%");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch);
	curl_close($ch);

	require_once('../kernel/begin.php');

	if (defined('PHPBOOST') !== true) exit;
	define('TITLE', "%HEADLINE%");

	require_once('../kernel/header.php');

	$Template->set_filenames(array(
		'ezstats3_wot'=> 'ezstats3_wot/ezstats3_wot.tpl'
		));
	$Template->assign_vars(array(
		'result' => utf8_decode($output)
	));
	$Template->pparse('ezstats3_wot');

	require_once('../kernel/footer.php');
?>