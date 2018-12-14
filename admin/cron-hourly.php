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
	
	$secure = new Security();
	$settings = new Settings();
	$func = new Functions();
	$player = new Player();
	$tpl = new Templates();
	$tpl->load_phrases("admin", true);
	date_default_timezone_set($settings->get("settings_timezone"));
	
	$appid    = json_decode($settings->get("properties_key"));
	$url      = Array(
		'eu' => 'api.worldoftanks.eu',
		'na' => 'api.worldoftanks.com',
		'ru' => 'api.worldoftanks.ru',
		'asia' => 'api.worldoftanks.asia'
	);
	
	
	
	### CLAN  SYNCRONISATION ###
	// Check if Clan Sync is activated, else go ahead
	if ($settings->get("update_platoon_sync_option")) {
		// Check if the Clan Sync now have to run. Otherwise Counter+1
		if ($settings->get("update_platoon_sync_count") >= $settings->get("update_platoon_sync_frequency")) {
			// Check if there are any Clans to sync. If not, go ahead to players update
			$sql->query('SELECT * FROM '.$sql->prefix.'platoons ORDER BY name');
			
			if ($sql->count()) {
				$settings->set("update_platoon_sync_count", "1", true); // Set counter to 1
				$player_in_clans = Array();  // Array for all player of all clans
				$player_in_database = Array(); // Array for all player in the database
				$message = Array(); // Array for the messages
				
				
				// Loop through all Clans
				while ($row = $sql->fetchRow()) {
					$id = $row['id'];
					$region = $row['region'];
					$clan_id = $row['clan_id'];
					
					// cURL call 
					$c=curl_init('http://'.$url[$region].'/wot/clan/info/?application_id='.$appid->$region.'&clan_id='.$clan_id);
					curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
					$result = json_decode(curl_exec($c)); curl_close($c);
					
					
					// Check if API responds
					if(!isset($result->status)) die("It seems that Wargaming API is not available at the moment");
					
					
					// Save properties of clan
					$sq2 = new MySQL();
					$sq2->query('
						UPDATE '.$sq2->prefix.'platoons 
						SET    obj = "'.$func->convert_object($result->data->$clan_id).'"
						WHERE  id = "'.$id.'"
					');
					$sq2->disconnect();
					
					
					// Array of players of the actual Clan
					$players = $result->data->$clan_id->members;
					
					
					// Save all playernames and regions in the array
					foreach ($players as $playerobj) {
						$player_in_clans[] = $playerobj->account_name.'||'.$region;
					}
				}
				//die(print_r($player_in_clans));
				
				// Copy the player from database into the array
				$sql->query('SELECT * FROM '.$sql->prefix.'player ORDER BY name');
				while ($row = $sql->fetchAssoc()) {
					$player_in_database[$row['id']] = $row['name'].'||'.$row['region'];
				}
				
				
				// Add players, who are in the Clan, to the database, if they aren't yet
				foreach ($player_in_clans as $p) {
					if (!in_array($p, $player_in_database)) {
						$val = explode('||', $p);
						$message[] = $player->add($val[0], $val[1], false);
					}
				}
				
				// Delete player from database, if they aren't in any Clan
				if ($settings->get("update_platoon_sync_option") == "2") {
					foreach ($player_in_database as $id => $p) {
						if (!in_array($p, $player_in_clans)) {
							$val = explode('||', $p);
							$message[] = $player->delete($id, $val[0]);
						}
					}
				}
				
				// Ergebnis ausgeben
				if (isset ($_POST['request']) AND $_POST['request'] == "admin") {
					// Abruf aus dem Adminpanel heraus
					die (json_encode(array(
						"message" => $tpl->phrase('platoon_sync_complete')."<br/>".implode("<br/>", $message),
						"time" => date("d.m.Y - H:i", time())
					)));
				} 
				else {
					// Direkter Abruf
					eval ("\$html = \"".$tpl->template("update")."\";");
					$html = new PQLite($html);
					
					$html->find('ul')->appendHTML('<li>'.$tpl->phrase('platoon_sync_complete').'</li>');
					
					foreach ($message as $string) $html->find('ul')->appendHTML('<li>'.$string.'</li>');
					die ($html->getHTML());
				}
			}
		} 
		
		else {
			// No Clan Sync now
			$count = $settings->get("update_platoon_sync_count");
			$settings->set("update_platoon_sync_count", $count+1, true); // Der Counter wird um 1 erhöht
		}
	}
	
	
	
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
		
		eval ("\$html = \"".$tpl->template("update")."\";");
		$html   = new PQLite($html);
		
		foreach ($message as $string) $html->find('ul')->appendHTML('<li>'.$string.'</li>');
		echo $html->getHTML();
	}
?>