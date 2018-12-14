<?php
	class Player2 {
		// CLASSES
		private $sql = NULL;
		private $tpl = NULL;
		private $func = NULL;
		private $settings = NULL;
		private $stats = NULL;
		
		
		// VARIABLES
		private $appid = NULL;
		private $url = NULL;
		
		// Array of Stats-tables
		private $tables = Array(
			"achievements", 
			"statistics", 
			"ratings",
			"membership",
			"ships",
			"encyclopedia"
		);
		
		
		public function __construct() {
			$this->sql  = new MySQL();
			$this->func = new Functions();
			$this->settings = new Settings();
			#$this->stats = new Stats();
			#$this->tpl  = new Templates();
			#$this->tpl->load_phrases("admin", true);
			#$this->tpl->load_phrases("_wot_admin", true);
			
									
			$this->appid = json_decode($this->settings->get("properties_key"));
			$this->url = Array(
				'eu' => 'api.worldofwarships.eu',
				'na' => 'api.worldofwarplanes.com',
				'ru' => 'api.worldofwarplanes.ru',
				'asia' => 'api.worldofwarplanes.asia'
			);
		}
		
		
		public function add($name, $region, $update=true, $clanname) {
			$name = trim($name);
			
			// Check if name is given
			if ($name == "") {
				return $this->tpl->phrase("message_no_player_given");
			}
			
			// Name is given...
			else {
				// Check if the name is already in the database
				$this->sql->query('SELECT id FROM '.$this->sql->prefix.'player WHERE name = "'.$name.'" AND region = "'.$region.'"');
				
				if($this->sql->count()) {
					// Name is already in the database. Generate message with $name in phrase
					$message = $this->tpl->phrase("message_player_already_added");
					eval ( "\$message = \"$message\";" );
					return $message;
				} 
				else {
					// Name is not in the database yet. Search for the player
					$c=curl_init('http://'.$this->url[$region].'/wows/account/list/?application_id='.$this->appid->$region.'&search='.$name.'&limit=1');
					curl_setopt($c,CURLOPT_HEADER,false);
					curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
					
					$result = json_decode(curl_exec($c));
					$statuscode = curl_getinfo($c,CURLINFO_HTTP_CODE);
					curl_close($c);
					
					// No connection to API
					if ($statuscode != 200) return "Can't connect to Wargaming API. Please try again later!";
					
					
					// Error thrown from API
					else if (isset($result->status) AND $result->status == "error") return "ERROR: ".$result->error->message;
					
					// No Player was found
					else if (isset($result->count) AND $result->count == 0) {
						$message = $this->tpl->phrase("player_not_found");
						eval ( "\$message = \"$message\";" );
						return $message;
					}
					
					// The wrong Player was found
					else if (strtolower($name) != strtolower($result->data[0]->nickname)) {
						$message = $this->tpl->phrase("player_not_found");
						eval ( "\$message = \"$message\";" );
						return $message;
					}
					
					// Player was found
					else {
						//Create a entry in the "player" table ...
						$this->sql->query('INSERT INTO '.$this->sql->prefix.'player (name, region, account_id, custom) VALUES ("'.$result->data[0]->nickname.'", "'.$region.'", "'.$result->data[0]->account_id.'", "'.$clanname.'")');
						$playerid = $this->sql->id;
						
						// ... and in all stats tables
						$this->sql->query('INSERT INTO '.$this->sql->prefix.'stats__basic (id) VALUES ("'.$playerid.'")');
						$this->sql->query('INSERT INTO '.$this->sql->prefix.'stats_ships (id) VALUES ("'.$playerid.'")');
						$this->sql->query('INSERT INTO '.$this->sql->prefix.'stats__toplist (id) VALUES ("'.$playerid.'")');
						foreach ($this->tables as $table) {
							$this->sql->query('INSERT INTO '.$this->sql->prefix.'stats_'.$table.' (id) VALUES ("'.$playerid.'")');
						}
						
						// Update the players stats
						if ($update) {
							$this->update($playerid, $result->data[0]->nickname, $result->data[0]->account_id, $region);
						}
						
						
						// Generate message with $name in phrase
						$message = $this->tpl->phrase("message_add_player");
						eval ( "\$message = \"$message\";" );
						return $message;
					}
				}
			}
		}
		
		
		public function delete($playerid = NULL, $name = "NoName") {
			if ($playerid === NULL) {
				return "ERROR: No Player-ID given";
			} else {
				$this->sql->query('DELETE FROM '.$this->sql->prefix.'player WHERE id = "'.$playerid.'"');
				$this->sql->query('DELETE FROM '.$this->sql->prefix.'stats__basic WHERE id = "'.$playerid.'"');
				$this->sql->query('DELETE FROM '.$this->sql->prefix.'stats_ships WHERE id = "'.$playerid.'"');
				
				foreach ($this->tables as $table) {
					$this->sql->query('DELETE FROM '.$this->sql->prefix.'stats_'.$table.' WHERE id = "'.$playerid.'"');
				}
				
				$message = $this->tpl->phrase("message_delete_player");
				eval ( "\$message = \"$message\";" );
				return $message;
			}
		}
		
		
		public function update($playerid, $name, $account_id, $region) {
			$connection = true;
			
			### ACCOUNT INFO ###
			if (true) {
				$c=curl_init('http://'.$this->url[$region].'/wows/account/info/?application_id='.$this->appid->$region.'&account_id='.$account_id);
				curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
				$result = json_decode(curl_exec($c)); curl_close($c);
				
				if(isset($result->status)) {
					$this->sql->query('
						UPDATE `'.$this->sql->prefix.'player` SET
							`date_stats_update` = "'.time().'",
							`api_player_status` = "'.$result->status.'"
						WHERE `id` = "'.$playerid.'"
					');
					
					if (isset($result->data->$account_id)) {
						$data = $result->data->$account_id;
						
						$this->sql->query('
							UPDATE `'.$this->sql->prefix.'stats__basic` SET
								`account_id`        = "'.$account_id.'",
								`name`              = "'.$data->nickname.'",
								`last_battle_time` = "'.$data->last_battle_time.'",
								`created_at` = "'.$data->created_at.'",
								`updated_at` = "'.$data->updated_at.'",
								`leveling_points` = "'.$data->leveling_points.'",
								`leveling_tier` = "'.$data->leveling_tier.'",
								`xp` = "'.$data->statistics->pvp->xp.'",
								`battles` = "'.$data->statistics->pvp->battles.'",
								`activity` = "'.$data->statistics->pvp->battles.'",
								`capture_points` = "'.$data->statistics->pvp->capture_points.'",
								`dropped_capture_points` = "'.$data->statistics->pvp->dropped_capture_points.'",
								`frags` = "'.$data->statistics->pvp->frags.'",
								`wins` = "'.$data->statistics->pvp->wins.'",
								`draws` = "'.$data->statistics->pvp->draws.'",
								`losses` = "'.$data->statistics->pvp->losses.'",
								`survived_battles` = "'.$data->statistics->pvp->survived_battles.'",
								`damage_dealt` = "'.$data->statistics->pvp->damage_dealt.'",
								`max_damage_dealt` = "'.$data->statistics->pvp->max_damage_dealt.'",
								`max_damage_dealt_ship_id` = "'.$data->statistics->pvp->max_damage_dealt_ship_id.'",
								`max_frags_battle` = "'.$data->statistics->pvp->max_frags_battle.'",
								`max_frags_ship_id` = "'.$data->statistics->pvp->max_frags_ship_id.'",
								`max_planes_killed` = "'.$data->statistics->pvp->max_planes_killed.'",
								`max_planes_killed_ship_id` = "'.$data->statistics->pvp->max_planes_killed_ship_id.'",
								`max_xp` = "'.$data->statistics->pvp->max_xp.'",
								`max_xp_ship_id` = "'.$data->statistics->pvp->max_xp_ship_id.'",
								`planes_killed` = "'.$data->statistics->pvp->planes_killed.'",
								`survived_wins` = "'.$data->statistics->pvp->survived_wins.'",
								`aircraft_frags` = "'.$data->statistics->pvp->aircraft->frags.'",
								`aircraft_max_frags_battle` = "'.$data->statistics->pvp->aircraft->max_frags_battle.'",
								`aircraft_max_frags_ship_id` = "'.$data->statistics->pvp->aircraft->max_frags_ship_id.'",
								`main_battery_frags` = "'.$data->statistics->pvp->main_battery->frags.'",
								`main_battery_hits` = "'.$data->statistics->pvp->main_battery->hits.'",
								`main_battery_max_frags_battle` = "'.$data->statistics->pvp->main_battery->max_frags_battle.'",
								`main_battery_max_frags_ship_id` = "'.$data->statistics->pvp->main_battery->max_frags_ship_id.'",
								`main_battery_shots` = "'.$data->statistics->pvp->main_battery->shots.'",
								`ramming_frags` = "'.$data->statistics->pvp->ramming->frags.'",
								`ramming_max_frags_battle` = "'.$data->statistics->pvp->ramming->max_frags_battle.'",
								`ramming_max_frags_ship_id` = "'.$data->statistics->pvp->ramming->max_frags_ship_id.'",
								`second_battery_frags` = "'.$data->statistics->pvp->second_battery->frags.'",
								`second_battery_hits` = "'.$data->statistics->pvp->second_battery->hits.'",
								`second_battery_max_frags_battle` = "'.$data->statistics->pvp->second_battery->max_frags_battle.'",
								`second_battery_max_frags_ship_id` = "'.$data->statistics->pvp->second_battery->max_frags_ship_id.'",
								`second_battery_shots` = "'.$data->statistics->pvp->second_battery->shots.'",
								`torpedoes_frags` = "'.$data->statistics->pvp->torpedoes->frags.'",
								`torpedoes_hits` = "'.$data->statistics->pvp->torpedoes->hits.'",
								`torpedoes_max_frags_battle` = "'.$data->statistics->pvp->torpedoes->max_frags_battle.'",
								`torpedoes_max_frags_ship_id` = "'.$data->statistics->pvp->torpedoes->max_frags_ship_id.'",
								`torpedoes_shots` = "'.$data->statistics->pvp->torpedoes->shots.'"
							WHERE `id` = "'.$playerid.'"
						');
						
						
				

					}
				}
				
				else $connection = false;
			}
			
			
			### ACCOUNT RATINGS ###
			/*if (true) {
				$c=curl_init('http://'.$this->url[$region].'/wows/ratings/accounts/?application_id='.$this->appid->$region.'&account_id='.$account_id.'&type=all');
				curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
				$statuscode = curl_getinfo($c,CURLINFO_HTTP_CODE);
				$result = json_decode(curl_exec($c)); curl_close($c);
				
				if(isset($result->status) AND isset($result->data->$account_id)) {
					$data = $result->data->$account_id;
					
					$this->sql->query('
						UPDATE `'.$this->sql->prefix.'stats_ratings` SET
							`data` = "'.$this->func->convert_object($data).'"
						WHERE `id` = "'.$playerid.'"
					');
				} 
				
				else $connection = false;
			}*/
			
			### ACHIEVEMENTS ###
			if (true) {
				$c=curl_init('http://'.$this->url[$region].'/wows/account/achievements/?application_id='.$this->appid->$region.'&account_id='.$account_id.'&type=all');
				curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
				$statuscode = curl_getinfo($c,CURLINFO_HTTP_CODE);
				$result = json_decode(curl_exec($c)); curl_close($c);
										
			
				if(isset($result->status) AND isset($result->data->$account_id)) {
				//dane z API
				#var_dump($result->data);
				$data = $result->data->$account_id;
					
					@$this->sql->query('
							UPDATE `'.$this->sql->prefix.'stats_achievements` SET
							`data` = "'.$this->func->convert_object($data->battle).'" ,
							`RETRIBUTION` = "'.$this->func->convert_object($data->battle->RETRIBUTION).'" ,		
							`FIRST_BLOOD` = "'.$this->func->convert_object($data->battle->FIRST_BLOOD).'" ,	
							`ARSONIST` = "'.$this->func->convert_object($data->battle->ARSONIST).'" ,	
							`LIQUIDATOR` = "'.$this->func->convert_object($data->battle->LIQUIDATOR).'" ,	
							`MAIN_CALIBER` = "'.$this->func->convert_object($data->battle->MAIN_CALIBER).'" ,	
							`HEADBUTT` = "'.$this->func->convert_object($data->battle->HEADBUTT).'" ,	
							`INSTANT_KILL` = "'.$this->func->convert_object($data->battle->INSTANT_KILL).'" ,	
							`FIREPROOF` = "'.$this->func->convert_object($data->battle->FIREPROOF).'" ,	
							`DETONATED` = "'.$this->func->convert_object($data->battle->DETONATED).'" ,		
							`SUPPORT` = "'.$this->func->convert_object($data->battle->SUPPORT).'" ,	
							`DOUBLE_KILL` = "'.$this->func->convert_object($data->battle->DOUBLE_KILL).'" ,
							`ATBA_CALIBER` = "'.$this->func->convert_object($data->battle->ATBA_CALIBER).'" ,	
							`DREADNOUGHT` = "'.$this->func->convert_object($data->battle->DREADNOUGHT).'" ,	
							`ONE_SOLDIER_IN_THE_FIELD` = "'.$this->func->convert_object($data->battle->ONE_SOLDIER_IN_THE_FIELD).'" ,
							`UNSINKABLE` = "'.$this->func->convert_object($data->battle->UNSINKABLE).'" ,
							`WITHERING` = "'.$this->func->convert_object($data->battle->WITHERING).'" ,		
							`CLEAR_SKY` = "'.$this->func->convert_object($data->battle->CLEAR_SKY).'"													
							WHERE `id` = "'.$playerid.'"	
						');
						
				} 
				else $connection = false;
			}
			
			### SHIPS ###
			if (true) {
				$c=curl_init('http://'.$this->url[$region].'/wows/ships/stats/?application_id='.$this->appid->$region.'&account_id='.$account_id);
				curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
				$statuscode = curl_getinfo($c,CURLINFO_HTTP_CODE);
				$result = json_decode(curl_exec($c)); curl_close($c);
				
				if(isset($result->status) AND isset($result->data->$account_id)) {
				$data = $result->data->$account_id;
				
					
					@$this->sql->query('
							UPDATE `'.$this->sql->prefix.'stats_ships` SET
								`data` = "'.$this->func->convert_object($data).'"
								
							WHERE `id` = "'.$playerid.'"
							
						');
						
				}		
				else $connection = false;
			}
			
			### ENCYCLOPEDIA (SHIP)###
			if (true) {
				$c=curl_init('http://'.$this->url[$region].'/wows/encyclopedia/ships/?application_id='.$this->appid->$region.'&fields=ship_id%2Cname%2Ctier%2Ctype%2Cnation%2Cimages.small');
				curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
				$statuscode = curl_getinfo($c,CURLINFO_HTTP_CODE);
				$result = json_decode(curl_exec($c)); curl_close($c);
				
				if(isset($result->status) AND isset($result->data)) {
					
					#var_dump($result->data);
					#$shipname = $result->data->$ship_id->'name;
					#$ship_id = $result->data->$ship_id;
					$data = $result->data;
					$this->sql->query('
						UPDATE `'.$this->sql->prefix.'encyclopedia` SET
							
							`ship` = "'.$this->func->convert_object($data).'"
							
							
							
							
							 
					');
						
				} 
				else $connection = false;
			}
			
			### ENCYCLOPEDIA (PLAYER)###
			if (true) {
				$c=curl_init('http://'.$this->url[$region].'/wows/encyclopedia/accountlevels/?application_id='.$this->appid->$region.'&fields=image%2Cpoints%2Ctier');
				curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
				$statuscode = curl_getinfo($c,CURLINFO_HTTP_CODE);
				$result = json_decode(curl_exec($c)); curl_close($c);
				
				if(isset($result->status) AND isset($result->data)) {
					
					#var_dump($result->data);
					
					
					$data = $result->data;
					$this->sql->query('
						UPDATE `'.$this->sql->prefix.'encyclopedia` SET
							
							`ranks` = "'.$this->func->convert_object($data).'"
							');
						
				} 
				else $connection = false;
			}
			
			
			### RETURN RESULT ###
			if ($connection) {
				return "OK"; 
			} else {
				$this->sql->query('
					UPDATE `'.$this->sql->prefix.'player` SET
						`date_stats_update` = "'.time().'",
						`api_player_status` = "API Error"
					WHERE `id` = "'.$playerid.'"
				');
				return "API Error";
			}
		}
	}
?>