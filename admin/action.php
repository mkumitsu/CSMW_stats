<?php
	require '../tmp/sql.php';
	require '../classes/mysql.php';
	
	$sql = new MySQL(); if ($sql->debug) error_reporting (E_ALL|E_STRICT); else error_reporting (0);
	
	require '../scripts/vendor/pqlite.php';
	require '../classes/settings.php';
	require '../classes/security.php';
	require '../classes/functions.php';
	require '../classes/session.php';
	require '../classes/login.php';
	require '../classes/template.php';
	require '../classes/player.php';
	require '../classes/stats.php';
	
	$secure = new Security();
	$settings = new Settings();
	$player = new Player();
	$login = new Login(Session::getSID());
	$tpl = new Templates();
	$tpl->load_phrases("default", true);
	$tpl->load_phrases("admin", true);
	$tpl->load_phrases("_bf4_admin", true);
	$tpl->load_phrases("_wot_admin", true);
	
	
	
	### LOGIN-CHECK // SELFADD // DEFAULTS ###
	if(!$login->logged_in()) {
		### SELFADD ### Function with which users can add themselves to the leaderboard. This must work without logged in
		if ($_POST['action'] == "add_player" AND $settings->get("features_selfadd")) {
			if (isset($_POST['input']['name']))     $name = $_POST['input']['name'];          else $name = "";
			if (isset($_POST['input']['region']))   $region = $_POST['input']['region'];      else $region = "pc";
			if (isset($_POST['input']['clanname'])) $clanname = $_POST['input']['clanname'];  else $clanname = "";
			
			$message = $player->add($name, $region, true, $clanname);
			die (json_encode(array("message" => $message)));
		}
		
		### LOGIN-CHECK ### User is not logged in -> Error message
		die (json_encode(array(
			"message" => "Error: Please login to perform this action!",
			"href" => "index.php"
		)));
	}
	else {
		### DEFAULTS ###
		// Zeitzone
		date_default_timezone_set($settings->get("settings_timezone"));
		
		// Userinformationen
		$sql->query('SELECT * FROM '.$sql->prefix.'users WHERE session = "'.$_POST['sid'].'"');
		$user = $sql->fetchRow();
		
		// Given values
		if (isset($_POST['action'])) $action = $_POST['action']; else $action = "";
		if (isset($_POST['input']))  $input = $_POST['input'];   else $input = "";
	}
	
	
	### ADD PLAYER TO DATAEBASE ###
	if ($action == "add_player") {
		if (isset($input['name']))     $name = $input['name'];            else $name = "";
		if (isset($input['region']))   $region = $input['region'];        else $region = "eu";
		if (isset($input['clanname'])) $clanname = $input['clanname'];    else $clanname = "";
		
		$message = $player->add($name, $region, true, $clanname);
		die (json_encode(array( "message" => $message)));
	}
	
	
	### CREATE LIST OF PLAYERS ###
	if ($action == "load_player") {
		$sql->query('
			SELECT * 
			FROM '.$sql->prefix.'stats__basic b
			LEFT JOIN '.$sql->prefix.'player p
				ON p.id = b.id
			ORDER BY 
				p.date_stats_update DESC, 
				p.name ASC
		');
		
		while ($player = $sql->fetchRow()) {
			if (strlen($player['nickname']) < 6 ) $size1 = 6; else  $size1 = strlen($player['nickname']) + 1;
			if (strlen($player['custom']) < 6 ) $size2 = 6; else  $size2 = strlen($player['custom']) + 1;
			
			echo '<tr>';
				echo '<td class="tleft nowrap">'.$player['account_id'].'</td>';
				echo '<td class="tleft nowrap">'.$player['name'].'</td>';
				echo '<td><input playerid="'.$player['id'].'" name="nickname" type="text" size="'.$size1.'" placeholder="n/a" value="'.htmlentities($player['nickname']).'" /></td>';
				echo '<td><input playerid="'.$player['id'].'" name="custom" type="text" size="'.$size2.'" placeholder="n/a" value="'.htmlentities($player['custom']).'" /></td>';
				echo '<td class="tleft nowrap">'.strtoupper($player['region']).'</td>';
				echo '<td style="font-size: 0.8em">'.date("d.m.Y - H:i", $player['date_stats_update']).'</td>';
				echo '<td style="font-size: 0.8em">'.date("d.m.Y - H:i", $player['updated_at']).'</td>';
				echo '<td>'.$player['api_player_status'].'</td>';
				echo '<td><a href="#" class="button" action="delete" playerid="'.$player['id'].'" playername="'.$player['name'].'" delphrase="'.$tpl->phrase('really_delete').'" defphrase="'.$tpl->phrase('delete').'">'.$tpl->phrase('delete').'</a></td>';
			echo '</tr>';
		}
		die();
	}
	
	
	### EDIT PLAYER ###
	if ($action == "edit_player") {
		$sql->query('UPDATE '.$sql->prefix.'player SET '.$input['name'].' = "'.$input['value'].'" WHERE id = "'.$input['id'].'"');
		die (json_encode(array( "result" => "OK")));
	}
	
	
	### UPDATE PLAYER BY ID ###
	if ($action == "update_player_by_id") {
		if (isset($input['id']))       $playerid = $input['id']; else $playerid = NULL;
		if (isset($input['name']))     $name = $input['name'];
		if (isset($input['platform'])) $platform = $input['platform']; else $platform = "pc";
		
		$array = $player->update($playerid, $name, $platform);
		$message = $tpl->phrase('update_message').$name.' ('.$array['api_player_status'].')';
		
		die (json_encode(array( "message" => $message)));
	}
	
	
	### DELETE PLAYER ###
	if ($action == "delete_player") {
		if (isset($input['id']))   $playerid = $input['id']; else $playerid = NULL;
		if (isset($input['name'])) $name = $input['name'];
		
		$message = $player->delete($playerid, $name);
		die (json_encode(array( "message" => $message)));
	}
	
	
	### ADD PLATOON ###
	if ($action == "add_platoon") {
		if (!$input) die();
		
		$func     = new Functions();
		$region   = $input['platoon_region'];
		$appid    = json_decode($settings->get("properties_key"));
		$url      = Array(
			'eu' => 'api.worldoftanks.eu',
			'na' => 'api.worldoftanks.com',
			'ru' => 'api.worldoftanks.ru',
			'asia' => 'api.worldoftanks.asia'
		);
		
		
		// User sent the Clan-ID
		if (is_numeric(trim($input['platoonid']))) {
			$clan_id = trim($input['platoonid']);
			
			// Test, if the platoon is already in the database
			$sql->query('SELECT id FROM '.$sql->prefix.'platoons WHERE clan_id="'.$clan_id.'"');
			if ($sql->count()) die (json_encode(array("message" => $tpl->phrase("platoon_already_added"))));
			
			// Search for clan in API
			$c=curl_init('http://'.$url[$region].'/wot/clan/info/?application_id='.$appid->$region.'&clan_id='.$clan_id);
			curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
			$result = json_decode(curl_exec($c)); curl_close($c);
			
			if(isset($result->status)) {
				// Search gave error
				if ($result->status == 'error') {
					die (json_encode(array( "message" => $result->error->message, "sync" => false)));
				}
				
				// Test, if any clan was found
				else if ($result->data->$clan_id === NULL) {
					die (json_encode(array("message" => $tpl->phrase("platoon_not_found"), "sync" => false)));
				}
				
				// Save platoon into the database
				else {
					$r = $result->data->$clan_id;
					$sql->query('
						INSERT INTO '.$sql->prefix.'platoons (
							clan_id, 
							name, 
							region,
							obj
						) VALUES (
							"'.$r->clan_id.'", 
							"'.$func->convert_object($r->name, false).'", 
							"'.$region.'",
							"'.$func->convert_object($r).'"
						)');
					
					$platoonname = $r->name;
					eval ("\$message = \"".$tpl->phrase("platoon_added")."\";");
					die (json_encode(array( "message" => $message, "sync" => true)));
				}
			}
			
			else die (json_encode(array( "message" => "Can't connect to Wargaming API. Please try later", "sync" => false)));
		}
		
		
		// User sent the Clanname
		else {
			$clanname = str_replace('\\', '', trim($input['platoonid']));
			
			$c=curl_init('http://'.$url[$region].'/wot/clan/list/?application_id='.$appid->$region.'&search='.$clanname);
			curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
			$result = json_decode(curl_exec($c)); curl_close($c);
			
			if(isset($result->status)) {
				// Search gave error
				if ($result->status == 'error') {
					die (json_encode(array( "message" => $result->error->message, "sync" => false)));
				}
				
				// Test, if any clan was found
				else if (!$result->count) {
					die (json_encode(array("message" => $tpl->phrase("platoon_not_found"), "sync" => false)));
				}
				
				else {
					foreach ($result->data as $r) {
						if ($r->name == $clanname) {
							// Test, if the platoon is already in the database
							$sql->query('SELECT id FROM '.$sql->prefix.'platoons WHERE clan_id="'.$r->clan_id.'"');
							if ($sql->count()) die (json_encode(array("message" => $tpl->phrase("platoon_already_added"))));
							
							// Save platoon into the database
							$sql->query('
								INSERT INTO '.$sql->prefix.'platoons (
									clan_id, 
									name, 
									region,
									obj
								) VALUES (
									"'.$r->clan_id.'", 
									"'.$func->convert_object($r->name, false).'", 
									"'.$region.'",
									"'.$func->convert_object($r).'"
								)');
							
							$platoonname = $r->name;
							eval ("\$message = \"".$tpl->phrase("platoon_added")."\";");
							die (json_encode(array( "message" => $message, "sync" => true)));
						}
					}
					
					// No result has the equal clan name
					die (json_encode(array("message" => $tpl->phrase("platoon_not_found"))));
				}
			} 
			
			else die (json_encode(array( "message" => "Can't connect to Wargaming API. Please try later", "sync" => false)));
		}
	}
	
	
	### LOAD LIST OF PLATOONS ###
	if ($action == "load_platoons") {
		$sql->query('SELECT * FROM '.$sql->prefix.'platoons ORDER BY name');
		while ($platoon = $sql->fetchRow()) {
			$obj = json_decode($platoon['obj']);
			
			echo '<tr>';
				echo '<td><img src="'.$obj->emblems->small.'" /></td>';
				echo '<td>'.$platoon['name'].'</td>';
				echo '<td class="uppercase">'.$platoon['region'].'</td>';
				echo '<td class="tright"><a href="#" class="button" action="delete" platoonid="'.$platoon['clan_id'].'" platoonname="'.$platoon['name'].'" delphrase="'.$tpl->phrase('really_delete').'" defphrase="'.$tpl->phrase('delete').'">'.$tpl->phrase('delete').'</a></td>';
			echo '</tr>';
		}
		die();
	}
	
	
	### DELETE PLATOON ###
	if ($action == "delete_platoon") {
		if (isset($input['id']))   $platoonid   = $input['id'];   else die (json_encode(array( "message" => "Error: No ID given")));
		if (isset($input['name'])) $platoonname = $input['name'];
		
		$sql->query('DELETE FROM '.$sql->prefix.'platoons WHERE clan_id = "'.$platoonid.'"');
		eval ("\$message = \"".$tpl->phrase("platoon_deleted")."\";");
		
		die (json_encode(array( "message" => $message)));
	}
	
	
	### SYNC PLATOON ###
	if ($action == "sync_platoon") {
		// The "update_platoon_sync_count" is set to "update_platoon_sync_frequency", so the next update of the player results in a update of the platoons
		$settings->set("update_platoon_sync_count", $settings->get("update_platoon_sync_frequency"), true);
		
		$message = $tpl->phrase("push_platoon_sync");
		die (json_encode(array( "message" => $message)));
	}
	
	
	### SAVE GENERAL SETTINGS ###
	if ($action == "settings") {
		if ($user['adminpower'] != "1") die (json_encode(array("message" => "Error: You have not the required rights to perform this action")));
		
		if ($input['name'] == "debug") {
			// Set Debug-Mode 
			$sql_data = json_decode(SQL);
			$handle = fopen('../tmp/sql.php', "w") 
				or die (json_encode(Array("message" => "Error: Failure during writing of file sql.php")));
			
			fwrite($handle, '<?php define (\'SQL\', \' {"debug": "'.$input['value'].'", "mysqli": "'.$sql_data->mysqli.'", "sqldb": "'.$sql_data->sqldb.'", "sqlhost": "'.$sql_data->sqlhost.'", "sqluser": "'.$sql_data->sqluser.'", "sqlpwd": "'.$sql_data->sqlpwd.'", "prefix": "'.$sql_data->prefix.'"} \'); ?>');
			fclose($handle);
		}
		
		else if ($input['name'] == "mysqli") {
			// Set Database extension 
			$sql_data = json_decode(SQL);
			$handle = fopen('../tmp/sql.php', "w") 
				or die (json_encode(Array("message" => "Error: Failure during writing of file sql.php")));
			
			fwrite($handle, '<?php define (\'SQL\', \' {"debug": "'.$sql_data->debug.'", "mysqli": "'.$input['value'].'", "sqldb": "'.$sql_data->sqldb.'", "sqlhost": "'.$sql_data->sqlhost.'", "sqluser": "'.$sql_data->sqluser.'", "sqlpwd": "'.$sql_data->sqlpwd.'", "prefix": "'.$sql_data->prefix.'"} \'); ?>');
			fclose($handle);
		}
		
		else {
			if ($input['name'] == "properties_path") {
				$value = $input['value'];
				$value = substr($value, -1)   != "/"       ? $value."/"       : $value;
				$value = substr($value, 0, 7) != "http://" ? "http://".$value : $value;
				$value = $value == "http:///"              ? ""               : $value;
			} else {
				$value = $input['value'];
			}
			$settings->set($input['name'], $value, true);
		}
		
		die (json_encode(array( "result" => "OK")));
	}
	
	
	### CUSTOMIZATION ###
	if ($action == "custom") {
		if ($user['adminpower'] != "1") die (json_encode(array("message" => "Error: You have not the required rights to perform this action")));
		
		for ($i = 0; $i < count($input); $i++) {
			$sql->query('
				UPDATE 
					'.$sql->prefix.'overview 
				SET 
					name = "'.$input[$i]['name'].'",
					value = "'.$input[$i]['value'].'"
				WHERE 
					id = "'.($i + 1).'"
			');
		}
		
		die (json_encode(array( "result" => "OK")));
	}
	
	
	### CMS-PLUGINS ###
	if ($action == "plugins") {
		if ($user['adminpower'] != "1") die (json_encode(array("message" => "Error: You have not the required rights to perform this action")));
		
		$settings->set('functions_cms', $input, true);
		
		if ($input == "standalone") {
			die ("<p>".$tpl->phrase("select_cms")."</p>");
		}
		else {
			$tpl->load_phrases("manual", true);
			$tpl->load_phrases("_bf4_admin", true);
			$url_support = $settings->get('default_project_url')."tickets/";
			$path = $settings->get('properties_path');
			
			eval ("\$manual = \"".$tpl->template("manual", "html", "plugins/".$input)."\";");
			$manual = str_replace(Array('default_plugin_name', 'ezStats2_BF3', 'ezstats2_bf3', 'ezstats_bf3', 'ezStats_BF3', 'ezStats2', 'phpcode'), $settings->get('default_plugin_name'), $manual);
			
			die($manual);
		}
	}
	
	
	### STYLE ###
	if ($action == "style") {
		if ($user['adminpower'] != "1") die (json_encode(array("message" => "Error: You have not the required rights to perform this action")));
		
		// Reset stylesheet to default values
		if (isset($input['button']) AND $input['button'] == "reset") {
			if ($defaults = json_decode(@file_get_contents("../tmp/defaults.js"))) {
				$sql->query('TRUNCATE TABLE '.$sql->prefix.'style');
				
				foreach ($defaults->style as $name => $value) {
					$sql->query('INSERT INTO '.$sql->prefix.'style (name, value) VALUES ("'.$name.'", "'.$value.'")');
				}
				
				die (json_encode(array("href" => "index.php?style&sid=".$_POST['sid'])));
			} else die ("Error: Failure during loading of file defaults.js");
		} 
		
		// Override stylesheet with preset values
		else if (isset($input['button']) AND $input['button'] == "preset") {
			if ($input['preset'] != "...") {
				if ($presets = json_decode(@file_get_contents("../tmp/styles.js"))) {
					$presets = $presets->$input['preset'];
					
					foreach ($presets as $name => $value) {
						$sql->query('
							UPDATE 
								'.$sql->prefix.'style 
							SET 
								value = "'.$value.'"
							WHERE 
								name = "'.$name.'"
						');
					}
					
					die (json_encode(array("href" => "index.php?style&sid=".$_POST['sid'])));
				} die ("Error: Failure during loading of file styles.js");
			} else die ("No preset given");
		}
		
		// Save manual values
		else {
			$sql->query('
				UPDATE 
					'.$sql->prefix.'style 
				SET 
					value = "'.$input['value'].'"
				WHERE 
					name = "'.$input['name'].'"
			');
			
			die (json_encode(array( "result" => "OK")));
		}
	}
	
	
	### ADD ADMIN ###
	if ($action == "add_user") {
		if ($user['adminpower'] != "1") die (json_encode(array("message" => "Error: You have not the required rights to perform this action")));
		
		// Check if all values are given
		if (trim($input['username']) == "" OR trim($input['password']) == "") die (json_encode(array("message" => $tpl->phrase("message_no_user_or_pass"))));
		
		// Check if the username exists
		$sql->query('SELECT id FROM '.$sql->prefix.'users WHERE name="'.$input['username'].'"');
		if ($sql->count()) die (json_encode(array("message" => $tpl->phrase("message_user_already_added"))));
		
		// Save values
		$sql->query('
			INSERT INTO '.$sql->prefix.'users (
				name, password, adminpower
			) VALUES (
				"'.$input['username'].'", "'.md5($input['password']).'", "'.$input['adminpower'].'"
			)
		');
		
		$name = $input['username'];
		$message = $tpl->phrase("message_user_saved");
		eval ( "\$message = \"$message\";" );
		
		die (json_encode(array("message" => $message, "success" => "1")));
	}
	
	
	### LOAD ADMIN LIST ###
	if ($action == "load_user") {
		$sql->query('SELECT * FROM '.$sql->prefix.'users ORDER BY id');
		while ($user = $sql->fetchRow()) {
			$adminpower = $user['adminpower'] ? $tpl->phrase("user_full_rights") : $tpl->phrase("user_manage_player_only");
			
			echo '<tr>';
				echo '<td>'.$user['name'].'</td>';
				echo '<td>'.$adminpower.'</td>';
			if ($user['id'] != "1")
				echo '<td><a href="#" class="button" action="delete" userid="'.$user['id'].'" username="'.$user['name'].'" delphrase="'.$tpl->phrase('really_delete').'" defphrase="'.$tpl->phrase('delete').'">'.$tpl->phrase('delete').'</a></td>';
			else echo '<td></td>';
			echo '</tr>';
		}
		die();
	}
	
	
	### DELETE ADMIN ###
	if ($action == "delete_user") {
		$sql->query('DELETE FROM '.$sql->prefix.'users WHERE (id = "'.$input['id'].'")');
		
		$name = $input['name'];
		$message = $tpl->phrase("message_delete_user");
		eval ( "\$message = \"$message\";" );
		
		die (json_encode(array("message" => $message)));
	}
	
	
	### SIGNATUREN ###
	if ($action == "signatures") {
		if ($user['adminpower'] != "1") die (json_encode(array("message" => "Error: You have not the required rights to perform this action")));
		
		if ($input == "reset_settings") {
			// Werte zurücksetzen
			if ($defaults = json_decode(@file_get_contents("../tmp/defaults.js"))) {
				$sql->query('TRUNCATE TABLE '.$sql->prefix.'signatures');
				foreach ($defaults->signatures as $name => $value) {
					$sql->query('INSERT INTO '.$sql->prefix.'signatures (name, value) VALUES ("'.$name.'", "'.$value.'")');
				}
			} else {
				die ("Error: Failure during loading of file defaults.js");
			}
		} else {
			// Werte speichern
			$sql->query('
				UPDATE 
					'.$sql->prefix.'signatures 
				SET 
					value = "'.$input['value'].'"
				WHERE 
					name = "'.$input['name'].'"
			');
		}
	}
?>