<?php
	require '../tmp/sql.php';
	require '../classes/mysql.php';
	
	$sql = new MySQL(); if ($sql->debug) error_reporting (E_ALL|E_STRICT); else error_reporting (0);
	
	require '../scripts/vendor/pqlite.php';
	require '../classes/settings.php';
	require '../classes/security.php';
	require '../classes/functions.php';
	require '../classes/template.php';
	#require '../classes/player.php';
	require '../classes/player2.php';
	require '../classes/stats.php';
	
	$secure = new Security();
	$settings = new Settings();
	$func = new Functions();
	$player = new Player2();
	#$tpl = new Templates();
	#$tpl->load_phrases("admin", true);
	date_default_timezone_set($settings->get("settings_timezone"));
	
	$appid    = json_decode($settings->get("properties_key"));
	$url      = Array(
		'eu' => 'api.worldofwarships.eu',
		'na' => 'api.worldoftanks.com',
		'ru' => 'api.worldoftanks.ru',
		'asia' => 'api.worldoftanks.asia'
	);
	
	
	
	
	
	
	
	### UPDATE OF THE PLAYERS ###
	$message = Array();
	$players = Array();
	$time = time();
	
	
	$sql->query('
		SELECT id, name, account_id, region
		FROM '.$sql->prefix.'player
		ORDER BY date_stats_update ASC
		LIMIT '.$settings->get('update_player_per_update').'
	');
	
	while ($row = $sql->fetchRow()) {
		$players[] = $row;
	}
	
	
	foreach ($players as $array) {
		$result = $player->update($array['id'], $array['name'], $array['account_id'], $array['region']);
		$message[] = $array['name'].' ('.$result.')';
	}
	
	$settings->set("update_date_last_refresh", $time, true);
	
	if (isset ($_POST['request']) AND $_POST['request'] == "admin") {
		die (json_encode(array(
			"message" => $tpl->phrase('update_message').implode("<br/>", $message),
			"time" => date("d.m.Y - H:i", $time)
		)));
	} 
	else {
		$func = new Functions();
		$preset = $func->load_settings("settings");
		
		#eval ("\$html = \"".$tpl->template("update")."\";");
		#$html   = new PQLite($html);
		
		#foreach ($message as $string) $html->find('ul')->appendHTML('<li>'.$string.'</li>');
		#echo $html->getHTML();
	}
?>