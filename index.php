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
		$func->counter();                                                       // Start counter function
		$preset = $func->load_settings("settings");                             // Load settings
		$ov = $func->load_settings("overview");                                 // Load leaderboard columns
		date_default_timezone_set($preset["settings_timezone"]);                // Set timezone
		$lastupdate = date("d.m.Y - H:i", $preset["update_date_last_refresh"]); // Last update of all players
		$data = Array( "player"  => Array(), "summary" => Array() );            // Array with the leaderboard values
	}
	
	
	### LOAD COLUMNS / CREATE TABLE HEADER ###
	if (true) {
		$i = 0;
		$thead = "<tr>";
		$sortList = "[[0,1], [2,1]]";
		
		foreach ($ov as $name => $active) {
			if ($active) {
				// Create all table headers, which are activated in Adminpanel->Customize
				// Append tooltip if there is a extended Table Head description
				$a = $tpl->phrase('head_'.$name, false);
				$b = $tpl->phrase($name);
				
				if ($a === false)			$thead .= '<th><span>'.$b.'</span></th>';
				else if ($a == $b)			$thead .= '<th><span>'.$a.'</span></th>';
				else if ($a != "&nbsp;")	$thead .= '<th><span title="'.$b.'">'.$a.'</span></th>';
				else						$thead .= '<th><span style="width: 75%; height: 1em; display: inline-block" title="'.$b.'">'.$a.'</span></th>';
				
				// Save this column to data array
				$data['summary'][$name] = Array();
				
				// Reverse sortation, if "Position" is the first column
				if ($i == 0 AND $name == 'position') $sortList = "[[0,0]]";
				
				$i++;
			}
		}
		
		$thead .= "</tr>";
	}
	
	
	### LOAD DATA ###
	if (true) {
		$i = 0;
		$path   = (defined('PLUGIN')) ? $settings->get("properties_path") : "";
		$plugin = (defined('PLUGIN')) ? "1" : "0";
		
		// Function for creating cell- and tooltipp-HTML
		function html($result, $class="", $tooltip=false) {
			$class = ($class != "") ? ' class="'.$class.'"' : "";
			
			if (!$tooltip) {
				return '<td'.$class.' sort="'.$result['sort'].'"><div title="'.strip_tags($result['tip']).'">'.$result['format'].'</div></td>';
			}
			
			else {
				$style       = ($tooltip['style'] != "")       ? ' style="'.$tooltip['style'].'"'             : "";
				$trigger     = ($tooltip['trigger'] != "")     ? ' trigger-style="'.$tooltip['trigger'].'"'   : "";
				$orientation = ($tooltip['orientation'] != "") ? ' orientation="'.$tooltip['orientation'].'"' : "";
				
				return '<td'.$class.' sort="'.$result['sort'].'"><div>'.$result['format'].'</div><x-tooltip '.$orientation.' '.$trigger.' '.$style.'>'.$result['tip'].'</x-tooltip></td>';
			}
		}
		
		
		// Load IDs of player ordered by xp
		if (true) {
			$sql->query('
				SELECT id
				FROM '.$sql->prefix.'stats__basic
				ORDER BY xp DESC
			');
		}
		
		$players_in_leaderboard = $sql->count();
		
		// Iterate through players
		if ($players_in_leaderboard) {
			while ($row = $sql->fetchAssoc()) {
				$i++;
				$stats = new Stats($row['id']);
				$data['player'][$i] = Array();
				
				
				if ($ov['position']) {
					$column = 'position';
					$result = $stats->form($i, Array('number', 0, '', 'position'), false);
					
					$data['player'][$i][$column]  = html($result);
					
					// Summary
					if ($i == $sql->count()) {
						$data['summary'][$column] = Array(
							'avg' => '<td> </td>',
							'med' => '<td> </td>',
							'sum' => '<td> </td>'
						);
					}
				}
				
				
				if ($ov['name']) {
					$column = 'name';
					
					$url = Array(
						'eu' => 'http://worldofwarships.eu',
						'na' => 'http://worldofwarplanes.com',
						'ru' => 'http://worldofwarplanes.ru',
						'asia' => 'http://worldofwarplanes.asia'
					);
					
					$nickname = $stats->get('player', 'nickname');
					$name     = $stats->get('player', 'name');
					$region   = $stats->get("player", "region");
					$aid      = $stats->get("player", "account_id");
					$nname    = ($nickname != "") ? $nickname : $name;
					$tag      = $stats->get('basic', 'clan_tag') ? '['.$stats->get('basic', 'clan_tag').']' : "";
					$clantag  = $settings->get('settings_clantag') ? $settings->get('settings_clantag') : $tag;
					$tip      = $tpl->phrase("open_detail_view"); eval ( "\$tip = \"$tip\";" );
					
					
					
					$data['player'][$i][$column]  = html(Array(
						'sort' =>   $nname,
						'format' => '<a class="ezPopup" href="'.$path.'detail.php?pid='.$row['id'].'&plugin='.$plugin.'">'.$clantag.$nname.'</a>',
						##'format' => '<a target="_blank" href="'.$url[$region].'/community/accounts/'.$aid.'-'.$name.'/#tab=pvp/account-tab-overview-pvp">'.$clantag.$nname.'</a>',
						'tip' =>    $tpl->phrase("click_to_get_detailed_view"),
					), "tleft");
					
					// Summary
					if ($i == $sql->count()) {
						$data['summary'][$column] = Array(
							'avg' => '<td>'.$tpl->phrase("ov_averages").': </td>',
							'med' => '<td>'.$tpl->phrase("ov_median").': </td>',
							'sum' => '<td>'.$tpl->phrase("ov_summary").': </td>'
						);
					}
				}
				
				
				
				
				if ($ov['xp']) {
					$column = 'xp';
					$result = $stats->get('basic', $column, Array('number'), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$result = array_sum($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['sum'] = html($result);
						}
					}
				}
				
				
				if ($ov['battle_avg_xp']) {
					$column = 'battle_avg_xp';
					$battles = $stats->get('basic', 'battles');
					$result  = $stats->get('basic', 'xp', Array('ratio', $battles, 0), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$data['summary'][$column]['sum'] = '<td></td>';
						}
					}
				}
				
				
				if ($ov['frags']) {
					$column = 'frags';
					$result = $stats->get('basic', $column, Array('number'), false);
					
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$result = array_sum($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['sum'] = html($result);
						}
					}
				}
				
				
				if ($ov['frags_per_battle']) {
					$column = 'frags_per_battle';
					$battles = $stats->get('basic', 'battles');
					$frags   = $stats->get('basic', 'frags');
					$result  = $stats->form($frags, Array('ratio', $battles, 2), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number', 2), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number', 2), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$data['summary'][$column]['sum'] = '<td></td>';
						}
					}
				}
				
				
				if ($ov['shots']) {
					$column = 'shots';
					$result = $stats->get('statistics', $column);
					$result = $stats->form($result->total, Array('number'), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$result = array_sum($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['sum'] = html($result);
						}
					}
				}
				
				
				if ($ov['ARSONIST']) {
					$column = 'ARSONIST';
					#$result = $stats->get('achievements', 'medals', $column);
					$result  = $stats->get('achievements', 'battle', $column);
					$result = $stats->form($result->total, Array('number'), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$result = array_sum($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['sum'] = html($result);
						}
					}
				}			
				
				if ($ov['battles']) {
					$column = 'battles';
					$result = $stats->get('basic', $column, Array('number'), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$result = array_sum($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['sum'] = html($result);
						}
					}
				}
				
				
				
				if ($ov['survived_battles']) {
					$column = 'survived_battles';
					$result = $stats->get('basic', $column, Array('number'), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$result = array_sum($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['sum'] = html($result);
						}
					}
				}
				
				
				if ($ov['survived_ratio']) {
					$column = 'survived_ratio';
					$shots  = $stats->get('statistics', 'battles');
					$result = $stats->get('statistics', 'survived_battles', Array('ratio', $shots, 0, '%'), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number', 0, '%'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number', 0, '%'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$data['summary'][$column]['sum'] = '<td></td>';
						}
					}
				}
				
				
				
				if ($ov['damage_dealt']) {
					$column = 'damage_dealt';
					$result = $stats->get('basic', $column, Array('number', 0, "k"), false);
					#$result = $stats->form($result, Array(), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['raw'],
						'sort' =>   $result['sort']
					));
					
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$result = array_sum($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['sum'] = html($result);
						}
					}
				}				
				
				if ($ov['wins']) {
					$column = 'wins';
					$result = $stats->get('basic', $column, Array('number'), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$result = array_sum($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['sum'] = html($result);
						}
					}
				}
				
				
				if ($ov['losses']) {
					$column = 'losses';
					$result = $stats->get('basic', $column, Array('number'), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$result = array_sum($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['sum'] = html($result);
						}
					}
				}
				
				
				if ($ov['draws']) {
					$column = 'draws';
					$result = $stats->get('basic', $column, Array('number'), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number'), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$result = array_sum($array);
							$result = $stats->form($result, Array('number', 0, "k"), false);
							$data['summary'][$column]['sum'] = html($result);
						}
					}
				}
				
				
				if ($ov['wlr']) {
					$column = 'wlr';
					$loss  = $stats->get('basic', 'losses');
					$result = $stats->get('basic', 'wins', Array('ratio', $loss, 2), false);
					
					$data['player'][$i][$column]  = html(Array(
						'format' => $result['format'],
						'tip' =>    $tpl->phrase($column).": ".$result['format'],
						'sort' =>   $result['sort']
					));
					$data['summary'][$column][$i] = $result['raw'];
					
					// Summary
					if ($i == $sql->count()) {
						$array = $data['summary'][$column];
						$data['summary'][$column] = Array();
						
						if ($preset['features_averages']) {
							$result = $func->calculate_average($array);
							$result = $stats->form($result, Array('number', 2), false);
							$data['summary'][$column]['avg'] = html($result);
						}
						
						if ($preset['features_median']) {
							$result = $func->calculate_median($array);
							$result = $stats->form($result, Array('number', 2), false);
							$data['summary'][$column]['med'] = html($result);
						}
						
						if ($preset['features_summary']) {
							$data['summary'][$column]['sum'] = '<td></td>';
						}
					}
				}
				
			}
		}
	}
	
	
	### CREATE TABLE BODY AND FOOT ###
	if (true) {
		$tbody = Array();
		foreach ($data['player'] as $p) {
			$html = "";
			
			foreach ($ov as $name => $active) {
				if ($active) {
					$html .= $p[$name];
				}
			}
			$tbody[] = $html;
		}
		$tbody = "<tr>". implode("</tr><tr>", $tbody) ."</tr>";
		
		
		$tfoot = Array('avg'=>'', 'med'=>'', 'sum'=>'');
		foreach ($ov as $name => $active) {
			$html = "";
			
			if ($active) {
				if ($preset['features_averages']) $tfoot['avg'][] = $data['summary'][$name]['avg'];
				if ($preset['features_median'])   $tfoot['med'][] = $data['summary'][$name]['med'];
				if ($preset['features_summary'])  $tfoot['sum'][] = $data['summary'][$name]['sum'];
			}
		}
		
		if ($preset['features_averages']) $tfoot['avg'] = "<tr>". implode($tfoot['avg'])."</tr>";
		if ($preset['features_median'])   $tfoot['med'] = "<tr>". implode($tfoot['med'])."</tr>";
		if ($preset['features_summary'])  $tfoot['sum'] = "<tr>". implode($tfoot['sum'])."</tr>";
		
		$tfoot = implode($tfoot);
	}
	
	
	### PAGE OUTPUT ###
	if (true) {
		if (defined('PLUGIN')) {
			$selfadd = $preset['features_selfadd'] == "1" ? '<td id="ezSelfadd"><a href="'.$preset['properties_path'].'admin/index.php?selfadd" target="_blank">'.$tpl->phrase("selfadd").'</a></td>' : "";
			
			eval ("\$html = \"".$tpl->template("plugin")."\";");
			$html = str_replace('#PATH#', $preset['properties_path'], $html);
			$html = new PQLite($html);
		} else {
			$selfadd = $preset['features_selfadd'] == "1" ? '<a href="admin/index.php?selfadd" id="ezSelfadd" target="_blank">'.$tpl->phrase("selfadd").'</a>' : "";
			
			eval ("\$html = \"".$tpl->template("standalone")."\";");
			$html = new PQLite($html);
		}
	}
	
	
	$html->find('#ezOverview')->appendHTML("<thead>".$thead."</thead>");
	$html->find('#ezOverview')->appendHTML("<tfoot>".$tfoot."</tfoot>");
	$html->find('#ezOverview')->appendHTML("<tbody>".$tbody."</tbody>");
	
	if (!$preset['features_infobox']) $html->find('#ezAside')->remSelf();
	if (!$preset['features_brand'])   $html->find('#ezBrand')->remSelf();
	
	
    $request_url = 'http://worldofwarships.eu/game-server-status/';
    $request_header = array( "X-Requested-With: XMLHttpRequest" );
    $curl_request = curl_init();
    curl_setopt( $curl_request, CURLOPT_URL, $request_url );
    curl_setopt( $curl_request, CURLOPT_HTTPHEADER, $request_header );
    curl_setopt( $curl_request, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $curl_request, CURLOPT_HTTPGET, true );
    $curl_reply = curl_exec( $curl_request );
    curl_close( $curl_request );
    $wows_serverstatus = json_decode( $curl_reply, true );
    echo '<div id="PozycjaStatusSerwera">Status serwera WoWs: ' . ( $wows_serverstatus['is_available'] ? "ONLINE" : "OFFLINE" ) . '<br></div>';
    echo '<div id="PozycjaGraczyObecnie">Graczy obecnie: ' . ( $wows_serverstatus['online_players'] ? $wows_serverstatus['online_players'] : "NO" ) . '</div>';

	
	echo $html->getHTML();
?>