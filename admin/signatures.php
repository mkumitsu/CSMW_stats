<?php
	require '../tmp/sql.php';
	require '../classes/mysql.php';
	
	$sql = new MySQL(); if ($sql->debug) error_reporting (E_ALL|E_STRICT); else error_reporting (0);
	
	require '../scripts/vendor/pqlite.php';
	require '../classes/settings.php';
	require '../classes/security.php';
	require '../classes/functions.php';
	require '../classes/template.php';
	require '../classes/player.php';
	require '../classes/stats.php';
	require '../classes/signatures.php';
	
	$secure = new Security();
	$settings = new Settings();
	$tpl = new Templates();
	$tpl->load_phrases("admin", true);
	date_default_timezone_set($settings->get("settings_timezone"));
	
	// Standard-Variablen belegen
	$message = Array();
	$time = time();
	
	
	// Signaturen sind deaktiviert
	if ($settings->get("functions_signatures") == "0") {
		if (isset ($_POST['request']) AND $_POST['request'] == "admin") {
			// Abruf aus dem Adminpanel heraus
			die (json_encode(array(
				"message" => $tpl->phrase('signatures_deactivated'),
				"time" => date("d.m.Y - H:i", 0)
			)));
		} 
		else {
			// Direkter Abruf
			$func = new Functions();
			$preset = $func->load_settings("settings");
			
			eval ("\$html = \"".$tpl->template("update")."\";");
			$html = new PQLite($html);
			
			$html->find('ul')->appendHTML('<li>'.$tpl->phrase('signatures_deactivated').'</li>');
			die ($html->getHTML());
		}
	}
	
	
	// Spieler abrufen, die am längsten nicht mehr aktualisiert wurden
	$sql->query('
		SELECT id, name
		FROM '.$sql->prefix.'player 
		ORDER BY date_signature_update ASC
		LIMIT '.$settings->get("update_sigs_per_update").'
	');
	
	
	// Signaturen erstellen
	while ($player = $sql->fetchRow()) {
		$sig = new Signatures($player['id']);
		
		if ($sig->create_signature()) {
			$message[] = $player['name'].': '.$tpl->phrase("signatur_created");
		} else {
			$message[] = $player['name'].': '.$tpl->phrase("signatur_not_created");
		}
		
		// Datum der Aktualisierung in die Spielereigenschaften schreiben
		$sq1 = new MySQL();
		$sq1->query('UPDATE '.$sql->prefix.'player SET date_signature_update = "'.$time.'" WHERE id = "'.$player['id'].'"');
		$sq1->disconnect();
	}
	
	
	// Datum der Aktualisierung in die Settings schreiben
	$sql->query('UPDATE '.$sql->prefix.'settings SET value="'.$time.'" WHERE name = "update_sigs_date_last_refresh"');
	
	
	// Ergebnis ausgeben
	if (isset ($_POST['request']) AND $_POST['request'] == "admin") {
		// Abruf aus dem Adminpanel heraus
		die (json_encode(array(
			"message" => $tpl->phrase('update_message').implode("<br/>", $message),
			"time" => date("d.m.Y - H:i", $time)
		)));
	} 
	else {
		// Direkter Abruf
		$func = new Functions();
		$preset = $func->load_settings("settings");
		
		eval ("\$html = \"".$tpl->template("update")."\";");
		$html = new PQLite($html);
		
		foreach ($message as $string) $html->find('ul')->appendHTML('<li>'.$string.'</li>');
		echo $html->getHTML();
	}
?>