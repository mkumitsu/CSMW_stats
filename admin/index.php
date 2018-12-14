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
	
	$secure = new Security();
	$settings = new Settings();
	$func = new Functions();
	$login = new Login(Session::getSID());
	$tpl = new Templates();
	$tpl->load_phrases("gui", true);
	$tpl->load_phrases("default", true);
	$tpl->load_phrases("admin", true);
	$tpl->load_phrases("_bf4_admin", true);
	$tpl->load_phrases("_wot_admin", true);
	
	// Set default variables
	$preset    = $func->load_settings("settings");
	$headline  = (trim($preset["settings_clanname"] != "")) ? $tpl->phrase("headline_clanname")." ".$preset["settings_clanname"] : $headline = "ezStats3 for ".$preset['default_gamename'];
	$sid       = session::getSID();
	date_default_timezone_set($preset["settings_timezone"]);
	
	
	### LOGINSCREEN // PLAYER SELFADD // DEFAULTS ###
	if(!$login->logged_in()) {
		// Load template
		eval ("\$html = \"".$tpl->template("_index")."\";");
		$html = new PQLite($html);
		$html->find('nav')->addClass('hidden');
		
		// Player-Selfadd (Function with which users can add themselves to the leaderboard)
		if (isset($_GET['selfadd']) AND $preset["features_selfadd"]) {
			// Load template
			eval ("\$player_add = \"".$tpl->template("player_add")."\";");
			$player_add = new PQLite($player_add);
			$player_add->find('#region_'.$preset['settings_region'])->setAttr('selected', 'selected');
			
			
			// Page output
			$html->find('#wrap')->appendHTML($player_add->getHTML());
			die ($html->getHTML());
		}
		
		// Error, if install folder still exists
		if (!$sql->debug AND file_exists('../install/index.php')) {
			$path = $_SERVER['HTTP_HOST']."/".str_replace(array($_SERVER['DOCUMENT_ROOT'], "admin/index.php"), array("", "install/"), $_SERVER['SCRIPT_FILENAME']);
			$path = str_replace("//", "/", $path);
			
			eval ("\$foldercheck = \"".$tpl->template("foldercheck")."\";");
			$html->find('#wrap')->appendHTML($foldercheck);
			die ($html->getHTML());
		}
		
		// Show login form
		else {
			// Error, if login or password is wrong
			if (isset($_POST['login'])) $html->find("aside")->setInnerHTML($tpl->phrase("error_login"))->setAttr("style", "display: block");
			
			
			// Variables
			$url_support = $preset['default_project_url']."tickets/";
			$url_download = $preset['default_project_url']."files/";
			$installed = $preset["properties_version"];
			$ch = curl_init("http://curl.ezstats.org/?version&game=".$preset['default_gameabbr']); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$actual = curl_exec($ch); curl_close($ch);
			
			
			// Page output
			eval ("\$login = \"".$tpl->template("login")."\";");
			$html->find('#wrap')->appendHTML($login);
			die ($html->getHTML());
		}
	}
	else {
		// Fetch information about the user
		$sql->query('SELECT * FROM '.$sql->prefix.'users WHERE session = "'.$sid.'"');
		$user = $sql->fetchRow();
		
		// Load template
		eval ("\$html = \"".$tpl->template("_index")."\";");  $html = new PQLite($html);
		
		// Highlight actual page in navigation
		if (count($_GET)) {
			$page = array_flip($_GET);
			$page = array_shift($page);
			$html->find('#'.$page.' a')->addClass('hover');
		}
		
		// Hide some pages, if required rights are too low
		if ($user['adminpower'] != "1") $html->find('nav li.admin')->addClass('hidden'); 
		
	}
	
	
	### STARTING PAGE ###
	if (!count($_GET)) {
		// Load template
		eval ("\$welcome = \"".$tpl->template("welcome")."\";");
		
		// Page output
		$html->find('#wrap')
			->appendHTML($welcome);
		$html->find('#player')
			->appendHTML('<div class="explain_nav"></div>');
		die ($html->getHTML());
	}
	
	
	### PLAYER MANAGEMENT ###
	if (isset($_GET['player'])) {
		### ADD PLAYER ###
		eval ("\$player_add = \"".$tpl->template("player_add")."\";");
		$player_add = new PQLite($player_add);
		$player_add->find('#region_'.$preset['settings_region'])->setAttr('selected', 'selected');
		
		
		### PLATOON SYNCRONISATION ###
		$url_wiki = $preset['default_project_url']."wiki/Clan%20Syncronisation/";
		eval ("\$player_platoon = \"".$tpl->template("player_platoon")."\";");
		$player_platoon = new PQLite($player_platoon);
		$player_platoon->find('#update_platoon_sync_option_'.$preset['update_platoon_sync_option'])->setAttr('checked', 'checked');
		$player_platoon->find('#platoon_region_'.$preset['settings_region'])->setAttr('selected', 'selected');
		
		
		### UPDATE PLAYER ###
		$lastcheckall = date("d.m.Y - H:i", (int)$preset["update_date_last_refresh"]);
		$max_execution_time = ini_get("max_execution_time");
		$message = $tpl->phrase("update_help_4");
		$url_wiki = $preset['default_project_url']."wiki/Stats%20Update/";
		eval ( "\$message = \"$message\";" );
		eval ("\$player_update = \"".$tpl->template("player_update")."\";");
		
		
		### PLAYER MANAGEMENT ###
		eval ("\$player_edit = \"".$tpl->template("player_edit")."\";");
		
		// Page output
		//$html->find('#wrap')->appendHTML($player_platoon->getHTML());
		$html->find('#wrap')->appendHTML($player_add->getHTML());
		$html->find('#wrap')->appendHTML($player_update);
		$html->find('#wrap')->appendHTML($player_edit);
		die ($html->getHTML());
	}
	
	
	### SETTINGS ###
	if (isset($_GET['settings'])) {
		// Load presets (Language)
		if (true) {
			$languages = Array();
			$sql->query('SHOW COLUMNS FROM '.$sql->prefix.'localization');
			
			while ($row = $sql->fetchRow()) {
				if ($row['Field'] != "id" AND $row['Field'] != "category" AND $row['Field'] != "wildcard") 
				$languages[] = $row['Field'];
			}
			
			$settings_language = "";
			foreach ($languages as $lang) {
				if ($lang == $preset['settings_language'])
					 $settings_language .= '<option selected="selected">'.$lang.'</option>';
				else $settings_language .= '<option>'.$lang.'</option>';
			}
		}
		
		
		// Load presets (Timezone)
		$settings_timezone = $func->select_timezone($preset['settings_timezone']);
		
		
		// Load template
		eval ("\$settings = \"".$tpl->template("settings")."\";");
		$settings = new PQLite($settings);
		
		
		// Load presets (Radio-buttons)
		$preset['debug'] = $sql->debug;
		$preset['mysqli'] = $sql->mysqli;
		$radio = Array("settings_region", "features_selfadd", "features_rankicon", "features_averages", "features_median", "features_summary", "features_compare", "features_infobox", "features_brand", "mysqli", "debug");
		foreach ($radio as $name)
			$settings->find('#'.$name.'_'.$preset[$name])->setAttr("checked", "checked");
		
		
		// Page output
		$html->find('#wrap')->appendHTML($settings->getHTML());
		die ($html->getHTML());
	}
	
	
	### CUSTOMIZATION ###
	if (isset($_GET['custom'])) {
		// Load saved settings
		$columns = $func->load_settings("overview");
		
		
		// Load template
		eval ("\$custom = \"".$tpl->template("custom")."\";");
		$custom = new PQLite($custom);
		
		
		// Generate list elements
		foreach ($columns as $colname => $value) {
			if ($value)	$string = '<li><input type="checkbox" name="custom" value="'.$colname.'" checked="checked "/> <span>'.$tpl->phrase($colname).'</span></li>';
			else 		$string = '<li><input type="checkbox" name="custom" value="'.$colname.'" /> <span>'.$tpl->phrase($colname).'</span></li>';
			$custom->find('ul')->appendHTML($string);
		}
		
		
		// Seite ausgeben
		$html->find('#wrap')->appendHTML($custom->getHTML());
		die ($html->getHTML());
	}
	
	
	### CMS-PLUGINS ###
	if (isset($_GET['plugin'])) {
		// Generate path to ezStats
		if (!$settings->get("properties_path")) {
			$path = $_SERVER['HTTP_HOST']."/".str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']);
			$path = str_replace("\\", "/", $path);
			$path = str_replace("//", "/", $path);
			$path = str_replace("admin/index.php", "", $path);
			
			$path = substr($path, -1)   != "/"       ? $path."/"       : $path;
			$path = substr($path, 0, 7) != "http://" ? "http://".$path : $path;
			
			$settings->set('properties_path', $path, true);
		} else {
			$path = $settings->get('properties_path');
			$settings->set('properties_path', $path, true);
		}
		
		
		// Generate list of CMS plugins
		$plugins = ""; 
		$sql->query('SELECT code, name FROM '.$sql->prefix.'plugins ORDER BY id');
		
		while ($row = $sql->fetchRow()) {
			if ($settings->get('functions_cms') == $row['code'])
				 $plugins .= '<option selected="selected" value="'.$row['code'].'">'.$row['name'].'</option>';
			else $plugins .= '<option value="'.$row['code'].'">'.$row['name'].'</option>';
		}
		
		
		// Create some variables
		$path_description = str_replace('ezStats2', $preset['default_program_folder'], $tpl->phrase('plugins_step2')); // Corrects the old folder name in the translations
		$url_support = $preset['default_project_url']."tickets/"; // URL to ezStats support
		
		
		// Page output
		eval ("\$plugin = \"".$tpl->template("plugin")."\";");
		$html->find('#wrap')->appendHTML($plugin);
		die ($html->getHTML());
	}
	
	
	### STYLE ### 
	if (isset($_GET['style'])) {
		$tpl->load_phrases("style", true);
		$tpl->load_phrases("_bf4_admin", true);
		$styles = $func->load_settings("style");
		$url_support = $preset['default_project_url']."tickets/"; // URL to ezStats support
		
		// Dropdown-menue
		$pics = $func->get_elements_in_folder("../styles/wallpaper/");
		$options_body_bg_image = '<option value="">...</option>';
		
		foreach ($pics as $pic) {
			$options_body_bg_image .= '<option>wallpaper/'.$pic.'</option>';
		}
		
		// Presets
		$options_preset = '<option>...</option>';
		if ($presets = json_decode(@file_get_contents("../tmp/styles.js"))) {
			foreach ($presets as $title => $obj) $options_preset .= '<option>'.$title.'</option>';
		} else {
			$options_preset = '<option value="">found no styles.js</option>';
		}
		
		
		// Page output
		eval ("\$style = \"".$tpl->template("style")."\";");
		$html->find('#wrap')->appendHTML($style);
		die ($html->getHTML());
	}
	
	
	### USER ###
	if (isset($_GET['user'])) {
		eval ("\$user_add = \"".$tpl->template("user_add")."\";");
		eval ("\$user_edit = \"".$tpl->template("user_edit")."\";");
		eval ("\$user = \"".$tpl->template("user")."\";");
		
		$user = new PQLite($user);
		$user->find('#user')->appendHTML($user_add);
		$user->find('#user')->appendHTML($user_edit);
		
		$html->find('#wrap')->appendHTML($user->getHTML());
		die ($html->getHTML());
	}
	
	
	### SIGNATURES ###
	if (isset($_GET['signatures'])) {
		// CHMOD-Test
		if (!is_writable("../signatures")) {
			$path = "'http://".$_SERVER['HTTP_HOST'].str_replace("admin/index.php", "signatures/'", $_SERVER['PHP_SELF']);
			$message = $tpl->phrase("signatures_chmod");
			eval ( "\$message = \"$message\";" );
			$html->find('#wrap')->appendHTML('<section><h1>'.$tpl->phrase("nav_signatures").'</h1><div>'.$message.'</div></section>');
			die ($html->getHTML());
		}
		
		
		// Load variables (Signature update)
		$sig_lastupdate = date("d.m.Y - H:i", (int)$preset['update_sigs_date_last_refresh']);
		$max_execution_time = ini_get("max_execution_time");
		$url_wiki = $preset['default_project_url']."wiki/Stats%20Update/";
		$url_tickets = $preset['default_project_url']."wiki/tickets/";
		$message = $tpl->phrase("update_help_4");
		eval ( "\$message = \"$message\";" );
		
		
		// Load template
		eval ("\$signatures = \"".$tpl->template("signatures")."\";");
		$signatures = new PQLite($signatures);
		$signatures->find('#functions_signatures_'.$preset['functions_signatures'])->setAttr("checked", "checked");
		
		
		// Generate list elements for signatures modifications
		$sigs = $func->load_settings("signatures");
		foreach ($sigs as $name => $value) {
			$type      = substr($name, 0, strpos($name, "_"));
			$shortname = substr($name, strpos($name, "_")+1);
			$cutename  = ucwords(str_replace("_", " ", $shortname));
			
			if ($shortname != "signature_type") {
				$signatures->find('#'.$type)->appendHTML('
					<li>
						<label style="width: 260px" for="'.$name.'">'.$cutename.'</label>
						<input type="text" size="50" name="'.$name.'" value="'.$value.'" />
					</li>
				');
			}
		}
		
		
		// Output page
		$html->find('#wrap')->appendHTML($signatures->getHTML());
		die ($html->getHTML());
	}
?>