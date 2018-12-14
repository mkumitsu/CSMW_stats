<?php
	require '../../tmp/sql.php';
	require '../../classes/mysql.php';
	
	$sql = new MySQL(); if ($sql->debug) error_reporting (E_ALL|E_STRICT); else error_reporting (0);
	
	require '../../classes/functions.php';
	require '../../classes/settings.php';
	require '../../classes/template.php';
	
	$settings = new Settings();
	$tpl = new Templates();
	$plugin = isset($_GET['plugin']) ? $_GET['plugin'] : "";
	
	switch ($plugin) {
		case "cotonti096":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('cotonti096/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "csphere20113":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="list.php"');
			$file = file_get_contents('csphere20113/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "dragonfly932":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="index.php"');
			$file = file_get_contents('dragonfly932/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "drupal712":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.txt"');
			$file = file_get_contents('drupal712/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
			
		case "dzcp155":
			if (isset($_GET['index'])) {
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="index.php"');
				$file = file_get_contents('dzcp155/plugin.php');
				$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
				$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			} else {
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.html"');
				$file = file_get_contents('dzcp155/template.html');
			}
			echo $file;
			break;
			
		case "e107_07":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('e107_07/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "ecp301":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="index.php"');
			$file = file_get_contents('ecp301/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "ilch11":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('ilch11/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%TITLE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "ipb3":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="phpcode.txt"');
			$file = file_get_contents('ipb3/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
			
		case "joomla253":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="code.txt"');
			$file = file_get_contents('joomla253/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
			
		case "mybb165":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('mybb165/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "nukedklan178":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="index.php"');
			$file = file_get_contents('nukedklan178/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "ocportal716":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('ocportal716/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
			
		case "phpboost30":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('phpboost30/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "phpfox3":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('phpfox3/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
			
		case "phpfusion702":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('phpfusion702/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "phpkit161":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('phpkit161/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "phpkit165":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('phpkit165/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "phpnuke824":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="index.php"');
			$file = file_get_contents('phpnuke824/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "smf202":
			if (isset($_GET['file1'])) {
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
				$file = file_get_contents('smf202/file1.php');
				$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
				$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			} else {
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.template.php"');
				$file = file_get_contents('smf202/file2.php');
				$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
				$file = str_replace('%HEADLINE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			}
			echo $file;
			break;
			
		case "vbadvanced":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('vbadvanced/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
			
		case "vbulletin41":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('vbulletin41/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
			
		case "wbb3x":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('wbb3x/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			$file = str_replace('%TITLE%', $tpl->phrase('Leaderboard').' '.$settings->get('settings_clanname'), $file);
			echo $file;
			break;
			
		case "webspell423":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('webspell423/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
		
		case "wordpress292":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('wordpress292/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
		
		case "xenforo112":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('xenforo112/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
			
		case "default_ajax":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.html"');
			$file = file_get_contents('default_ajax/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
			
		case "default_curl":
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'.$settings->get("default_plugin_name").'.php"');
			$file = file_get_contents('default_curl/file.php');
			$file = str_replace('%PATH%', $settings->get('properties_path').'plugin.php', $file);
			echo $file;
			break;
	}
?>