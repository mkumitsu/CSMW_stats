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
	
	$stats = new Stats($playerid);
?>

<!DOCTYPE html>
<!--[if lt IE 7]>	  <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>		 <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>		 <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Type" content="image/png; />
		
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="author" content="ezzemm" />
		<meta name="Description" content="This is the ezStats leaderboard for $preset[default_gamename] of $preset[settings_clanname]." />
		<meta name="Keywords" content="leaderboard, $preset[default_gamename], $preset[settings_clanname]" />
		
		<title></title>
		
		<link rel="shortcut icon" href="favicon.ico" />
		
		<!-- Loading of stylesheets -->
		<link rel="stylesheet" href="styles/normalize.css" />
		<link rel="stylesheet" href="styles/fonts.css" />
		<link rel="stylesheet" href="styles/_style.php?files=main,standalone,detail" />
		<link rel="stylesheet" href="styles/tooltipp.css" />
		<link rel="stylesheet" href="styles/magnific-popup.css" />
		
		<!-- Loading of JS scripts -->
		<script src="scripts/vendor/modernizr-2.6.2.min.js"></script>
		<script src="scripts/vendor/jquery-1.11.0.min.js"></script>
		<!--<script src="scripts/vendor/jquery-migrate-1.2.1.js"></script>-->
		<script src="scripts/vendor/jquery.tablesorter.js"></script>
		<script src="scripts/vendor/jquery.magnific-popup.min.js"></script>
		<script src="scripts/vendor/brick-0.9.1.min.js"></script>
		<script src="scripts/main.js"></script>
		<script src="scripts/overview.js"></script>
		
		<!-- Start of JS scripts -->
		<script type="text/javascript" charset="utf-8">
			jQuery.noConflict();
			
			(function($) {
				$(document).ready(function(){
					var ezstats = $("#ezStats");
					var elements = $("#ezHeader, #ezStats, #ezAside");
					
					// Initialize Lightbox
					$('.ezPopup').magnificPopup({
						type:'ajax',
						closeOnContentClick: false,
						closeOnBgClick: false,
						callbacks: {
							open:  function() { elements.hide(); },
							close: function() { elements.show(); }
						}
					});
					
					// Initialize Tablesorter
					$('#ezOverview', ezstats).tablesorter({
						sortList: $sortList,
						textExtraction: function(node) { return node.getAttribute("sort") }
					});
					
					// Initialize further scripts
					$.tableHover();
					$.asideHover();
					ezstats.grow();
				});
			})(jQuery);
		</script>
	</head>
	<body>
		<header id="ezHeader">
			<hgroup class="wrap" onclick="window.location.href='$preset[settings_clanpage]'">
				<img style="position: absolute; left:-20px;" src="/styles/images/VETO_icon.png" width="100px" height="100px"  />
				<h1>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;VETO Gaming</h1>
				<br>
				<h2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Statystyki graczy sekcji World of Warships (PVP)</h2>
			</hgroup>
		</header>
		
		
		<div id="ezStats" class="wrap">
			<section>
				<h1>Szczegółowe statystyki okrętu</h1>
				<div>
					<a href="javascript:history.back()" align="right">Wróć!</a>
					<table id="ezOverview" class="sort">
					<?php
					$ship_id = $_GET['ShipID'];
					$h= $stats->get('ships');


					for($i3 = 0; $i3 < count($h); $i3++){
						if($h[$i3]->ship_id == $ship_id){
							$ship_id_1 = $h[$i3]->ship_id;
							$distance_1 = $h[$i3]->distance;
							$last_battle_time_1 = date("d.m.Y - H:i", $h[$i3]->last_battle_time);
							$battles_1 = $h[$i3]->pvp->battles;
							$survived_battles_1 = $h[$i3]->pvp->survived_battles;
							$survived_wins_1 = $h[$i3]->pvp->survived_wins;
							$wins_1 = $h[$i3]->pvp->wins;
							$draws_1 = $h[$i3]->pvp->draws;
							$losses_1 = $h[$i3]->pvp->losses;
							$capture_points_1 = $h[$i3]->pvp->capture_points;
							$dropped_capture_points_1 = $h[$i3]->pvp->dropped_capture_points;
							$losses_1 = $h[$i3]->pvp->losses;
							$xp_1 = $h[$i3]->pvp->xp;
							$max_xp_1 = $h[$i3]->pvp->max_xp;
							$frags_1 = $h[$i3]->pvp->frags;
							$max_frags_battle_1 = $h[$i3]->pvp->max_frags_battle;
							$planes_killed_1 = $h[$i3]->pvp->planes_killed;
							$max_planes_killed_1 = $h[$i3]->pvp->max_planes_killed;
							$damage_dealt_1 = $h[$i3]->pvp->damage_dealt;
							$max_damage_dealt_1 = $h[$i3]->pvp->max_damage_dealt;
							
							$second_battery_max_frags_battle_1 = $h[$i3]->pvp->second_battery->max_frags_battle;
							$second_battery_frags_1 = $h[$i3]->pvp->second_battery->frags;
							$second_battery_hits_1 = $h[$i3]->pvp->second_battery->hits;
							$second_battery_shots_1 = $h[$i3]->pvp->second_battery->shots;
							
							$torpedoes_max_frags_battle_1 = $h[$i3]->pvp->torpedoes->max_frags_battle;
							$torpedoes_frags_1 = $h[$i3]->pvp->torpedoes->frags;
							$torpedoes_hits_1 = $h[$i3]->pvp->torpedoes->hits;
							$torpedoes_shots_1 = $h[$i3]->pvp->torpedoes->shots;
							
							$aircraft_max_frags_battle_1 = $h[$i3]->pvp->aircraft->max_frags_battle;
							$aircraft_frags_1 = $h[$i3]->pvp->aircraft->frags;
							
							$ramming_max_frags_battle_1 = $h[$i3]->pvp->ramming->max_frags_battle;
							$ramming_frags_1 = $h[$i3]->pvp->ramming->frags;
							
							$main_battery_max_frags_battle_1 = $h[$i3]->pvp->main_battery->max_frags_battle;
							$main_battery_frags_1 = $h[$i3]->pvp->main_battery->frags;
							$main_battery_hits_1 = $h[$i3]->pvp->main_battery->hits;
							$main_battery_shots_1 = $h[$i3]->pvp->main_battery->shots;
						}
					}
					
			?>
					<div class="ezContent">
						<div class="ez3">
							<h1>Ogólne</h1>
							<dl>
								<dt>ID Okrętu:</dt><dd><?php echo $ship_id_1; ?></dd>
								<dt>Dystans:</dt><dd><?php echo $distance_1; ?></dd>
								<dt>Ostatnia bitwa:</dt><dd><?php echo $last_battle_time_1; ?></dd>
								<dt>XP:</dt><dd><?php echo $xp_1; ?></dd>
								<dt>Zatopienia:</dt><dd><?php echo $frags_1; ?></dd>
								<dt>Zestrzelone samoloty:</dt><dd><?php echo $planes_killed_1; ?></dd>
								<dt>Bitwy:</dt><dd><?php echo $battles_1; ?></dd>
								<dt>Przetrwane bitwy:</dt><dd><?php echo $survived_battles_1; ?></dd>
								<dt>Wygrane bitwy:</dt><dd><?php echo $wins_1; ?></dd>
								<dt>Przetrwane wygrane bitwy:</dt><dd><?php echo $survived_wins_1; ?></dd>
								<dt>Remisy:</dt><dd><?php echo $draws_1; ?></dd>
								<dt>Przegrane bitwy:</dt><dd><?php echo $losses_1; ?></dd>
								<dt>Punkty przejęcia bazy:</dt><dd><?php echo $capture_points_1; ?></dd>
								<dt>Punkty obrony bazy:</dt><dd><?php echo $dropped_capture_points_1; ?></dd>
								<dt>Zadane obrażenia:</dt><dd><?php echo $damage_dealt_1; ?></dd>
								<dt> </dt>
								<dt>Maks. doświadczenie w bitwie:</dt><dd><?php echo $max_xp_1; ?></dd>
								<dt>Maks. zatopionych w bitwie:</dt><dd><?php echo $max_frags_battle_1; ?></dd>
								<dt>Maks. zestrzelonych samolotów:</dt><dd><?php echo $max_planes_killed_1; ?></dd>
								<dt>Maks. zadane obrażenia:</dt><dd><?php echo $max_damage_dealt_1; ?></dd>
								</dl>
						</div>	
						
						<div class="ez3">
							<h1>Szczegółowe</h1>
							<dl>
								<dt><b>Bateria główna</b></dt>
								<dt>Zatopienia:</dt><dd><?php echo $main_battery_frags_1; ?></dd>
								<dt>Trafienia:</dt><dd><?php echo $main_battery_hits_1; ?></dd>
								<dt>Strzały:</dt><dd><?php echo $main_battery_shots_1; ?></dd>
								<dt>Maks. zatopionych w bitwie:</dt><dd><?php echo $main_battery_max_frags_battle_1; ?></dd>
								<dt> </dt>
								<dt><b>Bateria pomocnicza</b></dt>
								<dt>Zatopienia:</dt><dd><?php echo $second_battery_frags_1; ?></dd>
								<dt>Trafienia:</dt><dd><?php echo $second_battery_hits_1; ?></dd>
								<dt>Strzały:</dt><dd><?php echo $second_battery_shots_1; ?></dd>
								<dt>Maks. zatopionych w bitwie:</dt><dd><?php echo $second_battery_max_frags_battle_1; ?></dd>
								<dt> </dt>
								<dt><b>Torpedy</b></dt>
								<dt>Zatopienia:</dt><dd><?php echo $torpedoes_frags_1; ?></dd>
								<dt>Trafienia:</dt><dd><?php echo $torpedoes_hits_1; ?></dd>
								<dt>Wystrzelenia:</dt><dd><?php echo $torpedoes_shots_1; ?></dd>
								<dt>Maks. zatopionych w bitwie:</dt><dd><?php echo $torpedoes_max_frags_battle_1; ?></dd>
								<dt> </dt>
								<dt><b>Samoloty</b></dt>
								<dt>Zatopienia:</dt><dd><?php echo $aircraft_frags_1; ?></dd>
								<dt>Maks. zatopionych w bitwie:</dt><dd><?php echo $aircraft_max_frags_battle_1; ?></dd>
								<dt> </dt>
								<dt><b>Taranowanie</b></dt>
								<dt>Zatopienia:</dt><dd><?php echo $ramming_frags_1; ?></dd>
								<dt>Maks. zatopionych w bitwie:</dt><dd><?php echo $ramming_max_frags_battle_1; ?></dd>
								</dl>
						</div>	
						
						<div class="ez3">
							<h1>Obrazek</h1>
							<dl>
							<dt></dt>
							</dl>
							</div>
						
						<div class="clearfix"></div>
						
						<br/>
					</div>
					
					</table>
				</div>
			</section>
		</div>
		
		<br/><br/><br/><br/>
		
		<aside id="ezAside" pos="right">
			<h1>%info%</h1>
			<ul>
				<li><b>$preset[settings_clanname]</b></li>
				<li>%Homepage%: <a href="$preset[settings_clanpage]">$preset[settings_clanpage]</a></li>
				<li>&nbsp;</li>
				<li>Forum: <a href="http://forum.vetogaming.pl">http://forum.vetogaming.pl</a></li>
				<li>&nbsp;</li>
				<li>%Lastupdate%: $lastupdate</li>
				<li>$preset[update_player_per_update] %updated%</li>
				<li><a href="admin/update.php">%update_now%</a></li>
				<li>&nbsp;</li>
				<li>%Hits%: $preset[functions_hits]</li>
				<li><a href="admin/index.php" target="_blank">%open_adminpanel%</a></li>
				<li>&nbsp;</li>
				<li>%poweredby% <a href="http://www.wargaming.com">Wargaming</a></li>
				<li>&nbsp;</li>
				<li>Zmodyfikowano przez <b>Kumitsu</b></li>
			</ul>
		</aside>
	</body>
</html>