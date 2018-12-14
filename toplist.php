<?php
	### FORWARDING TO INSTALLER ###
	if (!defined('PLUGIN') AND !file_exists('tmp/sql.php')) {
		header("Status: 301 Moved Permanently");
		header("Location:install/");
		exit; 
	}
	
	
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
		
		
		$func = new Functions();
		$secure = new Security();
		$settings = new Settings();
		$tpl = new Templates();
		$tpl->load_phrases("overview", true);
		$tpl->load_phrases("_wot_overview", true);
		$tpl->load_phrases("admin", true);
		$tpl->load_phrases("_wot_admin", true);
	}
	
	
	### SET COMMOM VARIABLES ###
	if (true) {
		$limit = 50;
		$url = Array(
			'eu' => 'http://worldofwarships.eu',
			'na' => 'http://worldofwarplanes.com',
			'ru' => 'http://worldofwarplanes.ru',
			'asia' => 'http://worldofwarplanes.asia'
		);
		
		$func->counter();                                                       // Start counter function
		$preset = $func->load_settings("settings");                             // Load settings
		$ov = $func->load_settings("overview");                                 // Load leaderboard columns
		date_default_timezone_set($preset["settings_timezone"]);                // Set timezone
		$lastupdate = date("d.m.Y - H:i", $preset["update_date_last_refresh"]); // Last update of all players
		$data = Array( "player"  => Array(), "summary" => Array() );            // Array with the leaderboard values
	}
	
	
	
	### LOAD DATA ###
	if (true) {
		// kills
		if (true) {
			$kills = "";
			$i = 0;
			
			$sql->query('
				SELECT id, name, region, account_id, kills
				FROM '.$sql->prefix.'stats__toplist
				ORDER BY kills DESC
				LIMIT '.$limit.'
			');
			
			while ($row = $sql->fetchAssoc()) {
				$stats = new Stats($row['id']);
				$i++;
				
				$kills .= '<tr>';
				$kills .= '<td>#'.$i.'</td>'; // Position
				$kills .= '<td><a target="_blank" href="'.$url[$row['region']].'/community/accounts/'.$row['account_id'].'">'.$row['name'].'</a></td>'; // Name
				$kills .= '<td>'.$row['kills'].'</td>';
				$kills .= '</tr>';
			}
		}
		
		// ground_targets
		if (true) {
			$ground_targets = "";
			$i = 0;
			
			$sql->query('
				SELECT id, name, region, account_id, ground_targets
				FROM '.$sql->prefix.'stats__toplist
				ORDER BY ground_targets DESC
				LIMIT '.$limit.'
			');
			
			while ($row = $sql->fetchAssoc()) {
				$stats = new Stats($row['id']);
				$i++;
				
				$ground_targets .= '<tr>';
				$ground_targets .= '<td>#'.$i.'</td>'; // Position
				$ground_targets .= '<td><a target="_blank" href="'.$url[$row['region']].'/community/accounts/'.$row['account_id'].'">'.$row['name'].'</a></td>'; // Name
				$ground_targets .= '<td>'.$row['ground_targets'].'</td>';
				$ground_targets .= '</tr>';
			}
		}
		
		// accuracy
		if (true) {
			$accuracy = "";
			$i = 0;
			
			$sql->query('
				SELECT id, name, region, account_id, accuracy
				FROM '.$sql->prefix.'stats__toplist
				ORDER BY accuracy DESC
				LIMIT '.$limit.'
			');
			
			while ($row = $sql->fetchAssoc()) {
				$stats = new Stats($row['id']);
				$i++;
				
				$accuracy .= '<tr>';
				$accuracy .= '<td>#'.$i.'</td>'; // Position
				$accuracy .= '<td><a target="_blank" href="'.$url[$row['region']].'/community/accounts/'.$row['account_id'].'">'.$row['name'].'</a></td>'; // Name
				$accuracy .= '<td>'.$row['accuracy'].'%</td>';
				$accuracy .= '</tr>';
			}
		}
		
		// kills_per_battle
		if (true) {
			$kills_per_battle = "";
			$i = 0;
			
			$sql->query('
				SELECT id, name, region, account_id, kills_per_battle
				FROM '.$sql->prefix.'stats__toplist
				ORDER BY kills_per_battle DESC
				LIMIT '.$limit.'
			');
			
			while ($row = $sql->fetchAssoc()) {
				$stats = new Stats($row['id']);
				$i++;
				
				$kills_per_battle .= '<tr>';
				$kills_per_battle .= '<td>#'.$i.'</td>'; // Position
				$kills_per_battle .= '<td><a target="_blank" href="'.$url[$row['region']].'/community/accounts/'.$row['account_id'].'">'.$row['name'].'</a></td>'; // Name
				$kills_per_battle .= '<td>'.$row['kills_per_battle'].'</td>';
				$kills_per_battle .= '</tr>';
			}
		}
		
		// damage_per_battle
		if (true) {
			$damage_per_battle = "";
			$i = 0;
			
			$sql->query('
				SELECT id, name, region, account_id, damage_per_battle
				FROM '.$sql->prefix.'stats__toplist
				ORDER BY damage_per_battle DESC
				LIMIT '.$limit.'
			');
			
			while ($row = $sql->fetchAssoc()) {
				$stats = new Stats($row['id']);
				$i++;
				
				$damage_per_battle .= '<tr>';
				$damage_per_battle .= '<td>#'.$i.'</td>'; // Position
				$damage_per_battle .= '<td><a target="_blank" href="'.$url[$row['region']].'/community/accounts/'.$row['account_id'].'">'.$row['name'].'</a></td>'; // Name
				$damage_per_battle .= '<td>'.$row['damage_per_battle'].'</td>';
				$damage_per_battle .= '</tr>';
			}
		}
		
		// wlr
		if (true) {
			$wlr = "";
			$i = 0;
			
			$sql->query('
				SELECT id, name, region, account_id, wlr
				FROM '.$sql->prefix.'stats__toplist
				ORDER BY wlr DESC
				LIMIT '.$limit.'
			');
			
			while ($row = $sql->fetchAssoc()) {
				$stats = new Stats($row['id']);
				$i++;
				
				$wlr .= '<tr>';
				$wlr .= '<td>#'.$i.'</td>'; // Position
				$wlr .= '<td><a target="_blank" href="'.$url[$row['region']].'/community/accounts/'.$row['account_id'].'">'.$row['name'].'</a></td>'; // Name
				$wlr .= '<td>'.$row['wlr'].'</td>';
				$wlr .= '</tr>';
			}
		}
		
		// experience_wo_bonus
		if (true) {
			$experience_wo_bonus = "";
			$i = 0;
			
			$sql->query('
				SELECT id, name, region, account_id, experience_wo_bonus
				FROM '.$sql->prefix.'stats__toplist
				ORDER BY experience_wo_bonus DESC
				LIMIT '.$limit.'
			');
			
			while ($row = $sql->fetchAssoc()) {
				$stats = new Stats($row['id']);
				$i++;
				
				$experience_wo_bonus .= '<tr>';
				$experience_wo_bonus .= '<td>#'.$i.'</td>'; // Position
				$experience_wo_bonus .= '<td><a target="_blank" href="'.$url[$row['region']].'/community/accounts/'.$row['account_id'].'">'.$row['name'].'</a></td>'; // Name
				$experience_wo_bonus .= '<td>'.$row['experience_wo_bonus'].'</td>';
				$experience_wo_bonus .= '</tr>';
			}
		}
		
		// experience_w_bonus
		if (true) {
			$experience_w_bonus = "";
			$i = 0;
			
			$sql->query('
				SELECT id, name, region, account_id, experience_w_bonus
				FROM '.$sql->prefix.'stats__toplist
				ORDER BY experience_w_bonus DESC
				LIMIT '.$limit.'
			');
			
			while ($row = $sql->fetchAssoc()) {
				$stats = new Stats($row['id']);
				$i++;
				
				$experience_w_bonus .= '<tr>';
				$experience_w_bonus .= '<td>#'.$i.'</td>'; // Position
				$experience_w_bonus .= '<td><a target="_blank" href="'.$url[$row['region']].'/community/accounts/'.$row['account_id'].'">'.$row['name'].'</a></td>'; // Name
				$experience_w_bonus .= '<td>'.$row['experience_w_bonus'].'</td>';
				$experience_w_bonus .= '</tr>';
			}
		}
		
		// xp_per_battle
		if (true) {
			$xp_per_battle = "";
			$i = 0;
			
			$sql->query('
				SELECT id, name, region, account_id, xp_per_battle
				FROM '.$sql->prefix.'stats__toplist
				ORDER BY xp_per_battle DESC
				LIMIT '.$limit.'
			');
			
			while ($row = $sql->fetchAssoc()) {
				$stats = new Stats($row['id']);
				$i++;
				
				$xp_per_battle .= '<tr>';
				$xp_per_battle .= '<td>#'.$i.'</td>'; // Position
				$xp_per_battle .= '<td><a target="_blank" href="'.$url[$row['region']].'/community/accounts/'.$row['account_id'].'">'.$row['name'].'</a></td>'; // Name
				$xp_per_battle .= '<td>'.$row['xp_per_battle'].'</td>';
				$xp_per_battle .= '</tr>';
			}
		}
	}
	
	
	
	### PAGE OUTPUT ###
	if (true) {
		if (defined('PLUGIN')) {
			eval ("\$html = \"".$tpl->template("plugin_top")."\";");
			$html = str_replace('#PATH#', $preset['properties_path'], $html);
			$html = new PQLite($html);
		} else {
			eval ("\$html = \"".$tpl->template("standalone_top")."\";");
			$html = new PQLite($html);
		}
	}
	
	
	//$html->find('#ezOverview')->appendHTML("<thead>".$thead."</thead>");
	
	if (!$preset['features_infobox']) $html->find('#ezAside')->remSelf();
	if (!$preset['features_brand'])   $html->find('#ezBrand')->remSelf();
	
	echo $html->getHTML();
?>