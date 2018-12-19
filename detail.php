<?php
	### LOAD CLASSES ###
	if (true) {
		require 'tmp/sql.php';
		require 'classes/mysql.php';
		
		$sql = new MySQL(); if ($sql->debug) error_reporting (E_ALL|E_STRICT); else error_reporting (0);
		
		require 'scripts/vendor/pqlite.php';
		require 'classes/settings.php';
		require 'classes/security.php';
		require 'classes/functions.php';
		require 'classes/template.php';
		require 'classes/player.php';
		require 'classes/stats.php';
		require 'classes/signatures.php';
		
		
		$func = new Functions();
		$secure = new Security();
		$settings = new Settings();
		$tpl = new Templates();
		$tpl->load_phrases("overview", true);
		$tpl->load_phrases("_wot_detail", true);
		$tpl->load_phrases("admin", true);
		$tpl->load_phrases("_wot_admin", true);
		$tpl->load_phrases("detail", true);
	}
	
	
	### SET COMMOM VARIABLES ###
	if (true) {
		$playerid = (int)$_GET['pid'];
		$plugin   = (int)$_GET['plugin'];
		$path     = ($plugin) ? $settings->get("properties_path") : "";
		date_default_timezone_set($settings->get("settings_timezone"));
		
		$action = (isset($_GET['action'])) ? $_GET['action'] : "default";
		
		$url = Array(
			'eu' => 'http://worldofwarships.eu',
			'na' => 'http://worldoftanks.com',
			'ru' => 'http://worldoftanks.ru',
			'asia' => 'http://worldoftanks.asia'
		);
	}
	
	
	### FUNCTIONS ###
	// Function for creating cell- and tooltipp-HTML
	function row($result, $class="", $tooltip=false) {
		$class = ($class != "") ? ' class="'.$class.'"' : "";
		
		if (!$tooltip AND !isset($result['tip'])) {
			return '<td'.$class.' sort="'.$result['sort'].'">'.$result['format'].'</td>';
		} 
		
		else if (!$tooltip AND isset($result['tip'])) {
			return '<td'.$class.' sort="'.$result['sort'].'"><div title="'.strip_tags($result['tip']).'">'.$result['format'].'</div></td>';
		}
		
		else {
			return '<td'.$class.' sort="'.$result['sort'].'"><a href="'.$tooltip.'">'.$result['format'].'</a><div id="'.$tooltip.'" style="display: none">'.$result['tip'].'</div></td>';
		}
	}
	
	// Function for creating ranking info
	function ranking($name_of_ranking, $cell_array=false) {
		global $playerid, $tpl;
		$stats = new Stats($playerid);
		$data  = $stats->get('rankings', $name_of_ranking);
		
		if ($data->rank) {
			$top = $stats->form($data->rank, Array('ratio', $data->count, 0, '%'), false);
			
			if      ($top["raw"] >= 0.90) { $pos = 12; }
			else if ($top["raw"] >= 0.70) { $pos = 24; }
			else if ($top["raw"] >= 0.30) { $pos = 36; }
			else if ($top["raw"] >= 0.10) { $pos = 48; }
			else                          { $pos = 60; }
			
			$sort = $top['sort'];
			$tip = $tpl->phrase('global_position').': '.$data->rank.'/'.$data->count.' ('.$top['format'].')';
			$string = '<a href="'.$data->url.'" target="_blank"><div class="ezDetail-Arrows" style="background-position: 0 -'.$pos.'px"></div></a>';
		} else {
			$sort = "0000000002000000";
			$tip = "";
			$string = '<div class="ezDetail-Arrows" style="background-position: 0 0px"></div>';
		}
		
		if ($cell_array) {
			return Array(
				'format' => $string,
				'tip' =>    $tip,
				'sort' =>   $sort
			);
		} else {
			return '<span title="'.$tip.'">'.$string.'</span>';
		}
	}
	
	
	### CREATE CONTENT ###
	function create_html($action) {
		global $sql, $func, $tpl, $playerid, $path, $plugin, $rankicon;
		$stats = new Stats($playerid);
		$html = "";
		
		if ($action == "detail_overview") {
			$size = 8;
			$s = $stats->get('basic');
			$r = $stats->get('ratings');
			
			$last_battle_time = date("d.m.Y - H:i", $stats->get('basic', 'last_battle_time'));
			$xp = $stats->get('basic', 'xp', Array('number'), false);
			$frags = $stats->get('basic', 'frags', Array('number'), false);
			$planes_killed = $stats->get('basic', 'planes_killed', Array('number'), false);
			$battles = $stats->get('basic', 'battles', Array('number'), false);
			$survived_battles = $stats->get('basic', 'survived_battles', Array('number'), false);
			$wins = $stats->get('basic', 'wins', Array('number'), false);
			$survived_wins = $stats->get('basic', 'survived_wins', Array('number'), false);
			$draws = $stats->get('basic', 'draws', Array('number'), false);
			$losses = $stats->get('basic', 'losses', Array('number'), false);
			$capture_points = $stats->get('basic', 'capture_points', Array('number'), false);
			$dropped_capture_points = $stats->get('basic', 'dropped_capture_points', Array('number'), false);
			$damage_dealt = $stats->get('basic', 'damage_dealt', Array('number'), false);
			$activity = $s['activity'];
			$w_battles = $s['battles'] - $s['activity'];
			
			mysql_select_db('18528617_wows');
			mysql_query('SET NAMES utf8');
			$encyklopedia = mysql_query('SELECT * FROM ez3wows__encyclopedia');
			$encyklopedia = mysql_fetch_array($encyklopedia);
			$encyklopedia = json_decode($encyklopedia[0] , true);
			
			if(!empty($s['max_damage_dealt_ship_id'])){ 
				$s['max_damage_dealt_ship_id'] = $encyklopedia[$s['max_damage_dealt_ship_id']]['name']; 
			}else{
				$s['max_damage_dealt_ship_id'] = 'Brak';
			}
			
			if(!empty($s['max_frags_ship_id'])){ 
				$s['max_frags_ship_id'] = $encyklopedia[$s['max_frags_ship_id']]['name']; 
			}else{
				$s['max_frags_ship_id'] = 'Brak';
			}		
			
			if(!empty($s['max_planes_killed_ship_id'])){ 
				$s['max_planes_killed_ship_id'] = $encyklopedia[$s['max_planes_killed_ship_id']]['name']; 
			}else{
				$s['max_planes_killed_ship_id'] = 'Brak';
			}	
			
			if(!empty($s['max_xp_ship_id'])){ 
				$s['max_xp_ship_id'] = $encyklopedia[$s['max_xp_ship_id']]['name']; 
			}else{
				$s['max_xp_ship_id'] = 'Brak';
			}		
			
			if(!empty($s['aircraft_max_frags_ship_id'])){ 
				$s['aircraft_max_frags_ship_id'] = $encyklopedia[$s['aircraft_max_frags_ship_id']]['name']; 
			}else{
				$s['aircraft_max_frags_ship_id'] = 'Brak';
			}		
			
			if(!empty($s['main_battery_max_frags_ship_id'])){ 
				$s['main_battery_max_frags_ship_id'] = $encyklopedia[$s['main_battery_max_frags_ship_id']]['name']; 
			}else{
				$s['main_battery_max_frags_ship_id'] = 'Brak';
			}	
			
			if(!empty($s['ramming_max_frags_ship_id'])){ 
				$s['ramming_max_frags_ship_id'] = $encyklopedia[$s['ramming_max_frags_ship_id']]['name']; 
			}else{
				$s['ramming_max_frags_ship_id'] = 'Brak';
			}		
			
			if(!empty($s['second_battery_max_frags_ship_id'])){ 
				$s['second_battery_max_frags_ship_id'] = $encyklopedia[$s['second_battery_max_frags_ship_id']]['name']; 
			}else{
				$s['second_battery_max_frags_ship_id'] = 'Brak';
			}	
			
			if(!empty($s['torpedoes_max_frags_ship_id'])){ 
				$s['torpedoes_max_frags_ship_id'] = $encyklopedia[$s['torpedoes_max_frags_ship_id']]['name']; 
			}else{
				$s['torpedoes_max_frags_ship_id'] = 'Brak';
			}	
			
			$created = date("d.m.Y - H:i", $stats->get('basic', 'created_at'));
			$state = date("d.m.Y - H:i", $stats->get('basic', 'updated_at'));
			eval ("\$html = \"".$tpl->template("detail_overview")."\";");
		}
		
		
		if ($action == "detail_signatures") {
			$sig = new Signatures($playerid);
			$sig->create_signature('');
			$vars = $func->load_settings("signatures");
			
			eval ("\$html = \"".$tpl->template("detail_signatures")."\";");
			$html = new PQLite($html);
			
			// Create Array with types of signatures
			$data = $func->load_settings("signatures");
			$types = Array();
			foreach ($data as $name => $value) $types[] = substr($name, 0, strpos($name, "_"));
			$types = $func->array_unique($types);
			unset($types[3]);
			
			// Add signatures to template
			foreach ($types as $type) {
				$url  = (empty($_SERVER['HTTPS']) ? "http://" : "https://").$_SERVER['HTTP_HOST'].str_replace("detail.php", "signatures/".$playerid."_".$type.".png", $_SERVER['PHP_SELF']);
				$bb   = "[IMG]".$url."[/IMG]";
				$htm  = htmlentities('<img src="'.$url.'" />');
				
				eval ("\$signature = \"".$tpl->template("detail_signatures_signature")."\";");
				$html->find('ul')->appendHTML($signature);
			}
			
			$html = $html->getHTML();
		}
		
		
		if ($action == "detail_achievements") {
			$size = 8;
			$a = $stats->get('achievements');
			
			$arsonist_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/arsonist.png);"></div>';
			$clear_sky_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/clear_sky.png);"></div>';
			$confederate_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/confederate.png);"></div>';
			$cqe_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/cqe.png);"></div>';
			$detonation_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/detonation.png);"></div>';
			$devastating_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/devastating.png);"></div>';
			$die_hard_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/die_hard.png);"></div>';
			$double_strike_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/double_strike.png);"></div>';
			$dreadnought_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/dreadnought.png);"></div>';
			$fireproof_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/fireproof.png);"></div>';
			$first_blood_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/first_blood.png);"></div>';
			$flesh_wound_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/flesh_wound.png);"></div>';
			$high_caliber_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/high_caliber.png);"></div>';
			$liquidator_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/liquidator.png);"></div>';
			$solo_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/solo.png);"></div>';
			$unsinkable_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/unsinkable.png);"></div>';
			$witherer_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/witherer.png);"></div>';
			$warrior_icon = '<div class="ezPlaceIcon" style="background-image: url('.$path.'styles/images/achievements/warrior.png);"></div>';				
			
			$created = date("d.m.Y - H:i", $stats->get('basic', 'created_at'));
			$state = date("d.m.Y - H:i", $stats->get('basic', 'updated_at'));
			
			if(!isset($a['RETRIBUTION'])){
				$a['RETRIBUTION'] = 0;
			}
			if(!isset($a['FIRST_BLOOD'])){
				$a['FIRST_BLOOD'] = 0;
			}
			if(!isset($a['ARSONIST'])){
				$a['ARSONIST'] = 0;
			}
			if(!isset($a['LIQUIDATOR'])){
				$a['LIQUIDATOR'] = 0;
			}
			if(!isset($a['MAIN_CALIBER'])){
				$a['MAIN_CALIBER'] = 0;
			}
			if(!isset($a['HEADBUTT'])){
				$a['HEADBUTT'] = 0;
			}
			if(!isset($a['INSTANT_KILL'])){
				$a['INSTANT_KILL'] = 0;
			}
			if(!isset($a['FIREPROOF'])){
				$a['FIREPROOF'] = 0;
			}			
			if(!isset($a['DETONATED'])){
				$a['DETONATED'] = 0;
			}
			if(!isset($a['SUPPORT'])){
				$a['SUPPORT'] = 0;
			}
			if(!isset($a['DOUBLE_KILL'])){
				$a['DOUBLE_KILL'] = 0;
			}
			if(!isset($a['ATBA_CALIBER'])){
				$a['ATBA_CALIBER'] = 0;
			}
			if(!isset($a['DREADNOUGHT'])){
				$a['DREADNOUGHT'] = 0;
			}
			if(!isset($a['ONE_SOLDIER_IN_THE_FIELD'])){
				$a['ONE_SOLDIER_IN_THE_FIELD'] = 0;
			}
			if(!isset($a['UNSINKABLE'])){
				$a['UNSINKABLE'] = 0;
			}
			if(!isset($a['WITHERING'])){
				$a['WITHERING'] = 0;
			}	
			if(!isset($a['CLEAR_SKY'])){
				$a['CLEAR_SKY'] = 0;
			}					
			eval ("\$html = \"".$tpl->template("detail_achievements")."\";");
			
		}
		
		if ($action == "detail_ships") {
			$size = 8;
			$h = $stats->get('ships');
		
			//pobranie wszystkich okrętów danego gracza z bazy z tabeli stats_ships
			foreach($h as $key=>$ship_id)
			{
				$ship_id2[] = $ship_id->ship_id;
			}
				
			
			mysql_select_db('18528617_wows');
			$encyklopedia = mysql_query('SELECT * FROM ez3wows__encyclopedia');
			$encyklopedia = mysql_fetch_array($encyklopedia);
			$encyklopedia = json_decode($encyklopedia[0] , true);
			
			for($i = 0; $i< count($ship_id2); $i++){
				$ship_name[] = $encyklopedia["$ship_id2[$i]"]['name'];
				$ship_tier[] = $encyklopedia["$ship_id2[$i]"]['tier'];			
				$ship_img[] = $encyklopedia["$ship_id2[$i]"]['images']['small'];		
			}
			
			/*echo '<pre>';
			print_r($h);
			echo '</pre>';*/
			
			
			echo '<pre>';
			print_r($ship_id2 , $ship_name);
			echo '</pre>';

			$dlugosc = count($ship_name);	
			
			$created = date("d.m.Y - H:i", $stats->get('basic', 'created_at'));
			$state = date("d.m.Y - H:i", $stats->get('basic', 'updated_at'));
			
			include('templates/detail_shipsPHP.php');
			
		}
		
		if ($action == "detail_ship") {
			$size = 8;
			$h= $stats->get('ships');
			$ship_id_1 = $h[0]->ship_id;
			$distance_1 = $h[0]->distance;
			$last_battle_time_1 = $h[0]->last_battle_time;
			$battles_1 = $h[0]->pvp->battles;
			$survived_battles_1 = $h[0]->pvp->survived_battles;
			$survived_wins_1 = $h[0]->pvp->survived_wins;
			$wins_1 = $h[0]->pvp->wins;
			$draws_1 = $h[0]->pvp->draws;
			$losses_1 = $h[0]->pvp->losses;
			$capture_points_1 = $h[0]->pvp->capture_points;
			$dropped_capture_points_1 = $h[0]->pvp->dropped_capture_points;
			$losses_1 = $h[0]->pvp->losses;
			$xp_1 = $h[0]->pvp->xp;
			$max_xp_1 = $h[0]->pvp->max_xp;
			$frags_1 = $h[0]->pvp->frags;
			$max_frags_battle_1 = $h[0]->pvp->max_frags_battle;
			$planes_killed_1 = $h[0]->pvp->planes_killed;
			$max_planes_killed_1 = $h[0]->pvp->max_planes_killed;
			$damage_dealt_1 = $h[0]->pvp->damage_dealt;
			$max_damage_dealt_1 = $h[0]->pvp->max_damage_dealt;
			
			$second_battery_max_frags_battle_1 = $h[0]->pvp->second_battery->max_frags_battle;
			$second_battery_frags_1 = $h[0]->pvp->second_battery->frags;
			$second_battery_hits_1 = $h[0]->pvp->second_battery->hits;
			$second_battery_shots_1 = $h[0]->pvp->second_battery->shots;
			
			$torpedoes_max_frags_battle_1 = $h[0]->pvp->torpedoes->max_frags_battle;
			$torpedoes_frags_1 = $h[0]->pvp->torpedoes->frags;
			$torpedoes_hits_1 = $h[0]->pvp->torpedoes->hits;
			$torpedoes_shots_1 = $h[0]->pvp->torpedoes->shots;
			
			$aircraft_max_frags_battle_1 = $h[0]->pvp->aircraft->max_frags_battle;
			$aircraft_frags_1 = $h[0]->pvp->aircraft->frags;
			
			$ramming_max_frags_battle_1 = $h[0]->pvp->ramming->max_frags_battle;
			$ramming_frags_1 = $h[0]->pvp->ramming->frags;
			
			$main_battery_max_frags_battle_1 = $h[0]->pvp->main_battery->max_frags_battle;
			$main_battery_frags_1 = $h[0]->pvp->main_battery->frags;
			$main_battery_hits_1 = $h[0]->pvp->main_battery->hits;
			$main_battery_shots_1 = $h[0]->pvp->main_battery->shots;
			
					
			$created = date("d.m.Y - H:i", $stats->get('basic', 'created_at'));
			$state = date("d.m.Y - H:i", $stats->get('basic', 'updated_at'));
			eval ("\$html = \"".$tpl->template("detail_ship")."\";");
			
		}
		
		if ($action == "detail_ranked") {
			$size = 8;
			$s = $stats->get('seasons');
			$r = $stats->get('ratings');
			$jeden = 1;
			$dwa = 2;
			
			$s1['max_rank'] = $s['seasons']->$jeden->rank_info->max_rank;
			$s1['start_rank'] = $s['seasons']->$jeden->rank_info->start_rank;
			$s1['stars'] = $s['seasons']->$jeden->rank_info->stars;
			$s1['rank'] = $s['seasons']->$jeden->rank_info->rank;
			$s1['stage'] = $s['seasons']->$jeden->rank_info->stage;

			//$s1['xp'] = $s['seasons']->$jeden->rank_solo->xp;
			//$s1['frags'] =  $s['seasons']->$jeden->rank_solo->max_frags_battle;
			//$s1['planes_killed'] = $s['seasons']->$jeden->rank_solo->planes_killed;
			//$s1['battles'] = $s['seasons']->$jeden->rank_solo->battles;
			//$s1['survived_battles'] = $s['seasons']->$jeden->rank_solo->survived_battles;
			//$s1['wins'] = $s['seasons']->$jeden->rank_solo->wins;
			//$s1['survived_wins'] = $s['seasons']->$jeden->rank_solo->survived_wins;
			//$s1['draws'] = $s['seasons']->$jeden->rank_solo->draws;
			//$s1['losses'] = $s['seasons']->$jeden->rank_solo->losses;
			//$s1['capture_points'] = $s['seasons']->$jeden->rank_solo->capture_points;
			//$s1['dropped_capture_points'] = $s['seasons']->$jeden->rank_solo->dropped_capture_points;
			//$s1['damage_dealt'] = $s['seasons']->$jeden->rank_solo->damage_dealt;
			//$s1['max_frags_battle'] = $s['seasons']->$jeden->rank_solo->max_frags_battle;
			//$s1['max_xp'] = $s['seasons']->$jeden->rank_solo->max_xp;
			//$s1['max_planes_killed'] = $s['seasons']->$jeden->rank_solo->max_planes_killed;
			//$s1['max_damage_dealt'] = $s['seasons']->$jeden->rank_solo->max_damage_dealt;
			//$s1['max_frags_battle'] = $s['seasons']->$jeden->rank_solo->max_frags_battle;
			
			$s2['max_rank'] = $s['seasons']->$dwa->rank_info->max_rank;
			$s2['start_rank'] = $s['seasons']->$dwa->rank_info->start_rank;
			$s2['stars'] = $s['seasons']->$dwa->rank_info->stars;
			$s2['rank'] = $s['seasons']->$dwa->rank_info->rank;
			$s2['stage'] = $s['seasons']->$dwa->rank_info->stage;

			$s2['xp'] = $s['seasons']->$dwa->rank_solo->xp;
			$s2['frags'] =  $s['seasons']->$dwa->rank_solo->max_frags_battle;
			$s2['planes_killed'] = $s['seasons']->$dwa->rank_solo->planes_killed;
			$s2['battles'] = $s['seasons']->$dwa->rank_solo->battles;
			$s2['survived_battles'] = $s['seasons']->$dwa->rank_solo->survived_battles;
			$s2['wins'] = $s['seasons']->$dwa->rank_solo->wins;
			$s2['survived_wins'] = $s['seasons']->$dwa->rank_solo->survived_wins;
			$s2['draws'] = $s['seasons']->$dwa->rank_solo->draws;
			$s2['losses'] = $s['seasons']->$dwa->rank_solo->losses;
			$s2['capture_points'] = $s['seasons']->$dwa->rank_solo->capture_points;
			$s2['dropped_capture_points'] = $s['seasons']->$dwa->rank_solo->dropped_capture_points;
			$s2['damage_dealt'] = $s['seasons']->$dwa->rank_solo->damage_dealt;
			$s2['max_frags_battle'] = $s['seasons']->$dwa->rank_solo->max_frags_battle;
			$s2['max_xp'] = $s['seasons']->$dwa->rank_solo->max_xp;
			$s2['max_planes_killed'] = $s['seasons']->$dwa->rank_solo->max_planes_killed;
			$s2['max_damage_dealt'] = $s['seasons']->$dwa->rank_solo->max_damage_dealt;
			$s2['max_frags_battle'] = $s['seasons']->$dwa->rank_solo->max_frags_battle;
			
			
			mysql_select_db('18528617_wows');
			$encyklopedia = mysql_query('SELECT * FROM ez3wows__encyclopedia');
			$encyklopedia = mysql_fetch_array($encyklopedia);
			$encyklopedia = json_decode($encyklopedia[0] , true);
			
			if(!empty($s['max_damage_dealt_ship_id'])){ 
				$s['max_damage_dealt_ship_id'] = $encyklopedia[$s['max_damage_dealt_ship_id']]['name']; 
			}else{
				$s['max_damage_dealt_ship_id'] = 'Brak';
			}
			
			if(!empty($s['max_frags_ship_id'])){ 
				$s['max_frags_ship_id'] = $encyklopedia[$s['max_frags_ship_id']]['name']; 
			}else{
				$s['max_frags_ship_id'] = 'Brak';
			}		
			
			if(!empty($s['max_planes_killed_ship_id'])){ 
				$s['max_planes_killed_ship_id'] = $encyklopedia[$s['max_planes_killed_ship_id']]['name']; 
			}else{
				$s['max_planes_killed_ship_id'] = 'Brak';
			}	
						
			if(!empty($s['aircraft_max_frags_ship_id'])){ 
				$s['aircraft_max_frags_ship_id'] = $encyklopedia[$s['aircraft_max_frags_ship_id']]['name']; 
			}else{
				$s['aircraft_max_frags_ship_id'] = 'Brak';
			}		
			
			if(!empty($s['main_battery_max_frags_ship_id'])){ 
				$s['main_battery_max_frags_ship_id'] = $encyklopedia[$s['main_battery_max_frags_ship_id']]['name']; 
			}else{
				$s['main_battery_max_frags_ship_id'] = 'Brak';
			}	
			
			if(!empty($s['ramming_max_frags_ship_id'])){ 
				$s['ramming_max_frags_ship_id'] = $encyklopedia[$s['ramming_max_frags_ship_id']]['name']; 
			}else{
				$s['ramming_max_frags_ship_id'] = 'Brak';
			}		
			
			if(!empty($s['second_battery_max_frags_ship_id'])){ 
				$s['second_battery_max_frags_ship_id'] = $encyklopedia[$s['second_battery_max_frags_ship_id']]['name']; 
			}else{
				$s['second_battery_max_frags_ship_id'] = 'Brak';
			}	
			
			if(!empty($s['torpedoes_max_frags_ship_id'])){ 
				$s['torpedoes_max_frags_ship_id'] = $encyklopedia[$s['torpedoes_max_frags_ship_id']]['name']; 
			}else{
				$s['torpedoes_max_frags_ship_id'] = 'Brak';
			}	
			
			$created = date("d.m.Y - H:i", $stats->get('basic', 'created_at'));
			$state = date("d.m.Y - H:i", $stats->get('basic', 'updated_at'));
			eval ("\$html = \"".$tpl->template("detail_overviewSeason")."\";");
				
				//$max_rank = $seasons->rank_info->max_rank;
			//	echo '<pre>';
			//print_r($numer);
			//echo '</pre>'; */ 
			
		}
		return $html;
		
	}
	
	
	### LOAD PAGES ###
	if ($action == "default") {
		if ($playerid) {
			$stats = new Stats($playerid);
		} else die("ERROR: No playerid given");
		
		$s = $stats->get('basic');
		$w_battles = $s['battles'] - $s['activity'];
		$name = $stats->get("player", "name");
		$region = $stats->get("player", "region");
		$aid = $stats->get("player", "account_id");
		$showsig = $settings->get("functions_signatures") ? "display: block" : "display: none";
		$url = $url[$region].'/community/accounts/'.$aid.'-'.$name.'/#tab=pvp/account-tab-overview-pvp';
		
		eval ("\$html = \"".$tpl->template("detail")."\";");
		
		$html = new PQLite($html);
		$html->find('#ezContent')->appendHTML(create_html('detail_overview'));
		
		die ($html->getHTML());
	} else {
		die(create_html($action));
	}
?>