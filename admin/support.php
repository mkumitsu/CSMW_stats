<?php
	error_reporting (E_ALL|E_STRICT);
	
	require '../tmp/sql.php';
	require '../classes/mysql.php';
	require '../classes/settings.php';
	require '../classes/security.php';
	require '../classes/functions.php';
	require '../classes/session.php';
	require '../classes/login.php';
	require '../classes/template.php';
	require '../classes/player.php';
	require '../classes/stats.php';
	require '../classes/signatures.php';
	
	
	// Navigation
	echo '<a href="support.php?info">PHP-Info</a><br/>';
	echo '<a href="support.php?more">Settings</a><br/>';
	echo '<a href="support.php?playerlist">Player List</a><br/>';
	echo '<a href="support.php?curl">cURL Test</a><br/>';
	echo '<a href="support.php?signatures">Signature Test</a><br/>';
	echo 'Local Stats: 
			<a href="support.php?localstats&basic">Basic</a> 
			<a href="support.php?localstats&achievements">Achievements</a> 
			<a href="support.php?localstats&statistics">Statistics</a> 
			<a href="support.php?localstats&ratings">Ratings</a> 
			<a href="support.php?localstats&membership">Membership</a> 
		<br/>';
	echo 'API Stats: 
			<a href="support.php?apistats&clanlist">Clan List</a> 
			<a href="support.php?apistats&claninfo">Clan Info</a> 
			<a href="support.php?apistats&accountlist">Account List</a> 
			<a href="support.php?apistats&accountinfo">Account Info</a> 
			<a href="support.php?apistats&ratingstypes">Ratings Types</a> 
			<a href="support.php?apistats&ratingsaccounts">Ratings Accounts</a> 
		<br/>';
	echo '<br/><br/><br/>';
	
	
	// PHP-Info
	if (isset($_GET['info'])) {
		die(phpinfo());
	}
	
	
	// Settings
	if (isset($_GET['more'])) {
		$sql = new MySQL();
		date_default_timezone_set('Europe/Paris');
		
		$sql->query('
			SELECT *
			FROM '.$sql->prefix.'settings
			ORDER BY name ASC
		');
		
		echo "<table><tbody>";
		
		while ($row = $sql->fetchAssoc()) {
			echo "<tr>";
			echo "<td>".$row['name']."<td>";
			
			if ($row['name'] == "update_date_last_refresh") {
				echo "<td>".date("d.m.Y - H:i", $row['value'])."</td>";
			} else {
				echo "<td>".$row['value']."</td>";
			}
			
			echo "</tr>";
		}
		
		echo "</tbody></table>";
		die();
	}
	
	
	// cURL-Test
	if (isset($_GET['curl'])) {
		// BF4STATS-API
		$c=curl_init('http://api.bf4stats.com/api/playerInfo');
		$postdata = Array(
			'plat' => 'pc',
			'name' => '1',
			'output' => 'json'
		);
		curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_POST,true); curl_setopt($c,CURLOPT_USERAGENT,'BF3StatsAPI/0.1'); curl_setopt($c,CURLOPT_HTTPHEADER,array('Expect:')); curl_setopt($c,CURLOPT_RETURNTRANSFER,true); curl_setopt($c,CURLOPT_CONNECTTIMEOUT,5); curl_setopt($c,CURLOPT_POSTFIELDS,$postdata);
		
		$result = curl_exec($c);
		$status = curl_getinfo($c,CURLINFO_HTTP_CODE); $error = curl_error($c); $errno = curl_errno($c);
		curl_close($c);
		
		echo "<br><br>BF4STATS:<br>Status: $status<br>Error: $error ($errno)<br>Result: $result";
		
		// BF3STATS-API
		$c=curl_init('http://api.bf3stats.com/pc/player/');
		$postdata = Array(
			'player' => 'ezzemm',
			'opt' => 'clear,global'
		);
		curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_POST,true); curl_setopt($c,CURLOPT_USERAGENT,'BF3StatsAPI/0.1'); curl_setopt($c,CURLOPT_HTTPHEADER,array('Expect:')); curl_setopt($c,CURLOPT_RETURNTRANSFER,true); curl_setopt($c,CURLOPT_CONNECTTIMEOUT,5); curl_setopt($c,CURLOPT_POSTFIELDS,$postdata);
		
		$result = curl_exec($c);
		$status = curl_getinfo($c,CURLINFO_HTTP_CODE); $error = curl_error($c); $errno = curl_errno($c);
		curl_close($c);
		
		echo "<br><br>BF3STATS:<br>Status: $status<br>Error: $error ($errno)<br>Result: $result";
		
		
		// EZSTATS
		$c=curl_init('http://curl.ezstats.org/?version');
		curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_POST,true); curl_setopt($c,CURLOPT_USERAGENT,'BF3StatsAPI/0.1'); curl_setopt($c,CURLOPT_HTTPHEADER,array('Expect:')); curl_setopt($c,CURLOPT_RETURNTRANSFER,true); curl_setopt($c,CURLOPT_CONNECTTIMEOUT,5); curl_setopt($c,CURLOPT_POSTFIELDS,$postdata);
		
		$result = json_decode(curl_exec($c));
		$status = curl_getinfo($c,CURLINFO_HTTP_CODE); $error = curl_error($c); $errno = curl_errno($c);
		curl_close($c);
		
		echo "<br><br>EZSTATS:<br>Status: $status<br>Error: $error ($errno)<br>Result: $result";
		
		
		// THIS PAGE
		$settings = new Settings();
		$c=curl_init($settings->get('properties_path').'/admin/support.php');
		curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_POST,true); curl_setopt($c,CURLOPT_USERAGENT,'BF3StatsAPI/0.1'); curl_setopt($c,CURLOPT_HTTPHEADER,array('Expect:')); curl_setopt($c,CURLOPT_RETURNTRANSFER,true); curl_setopt($c,CURLOPT_CONNECTTIMEOUT,5); curl_setopt($c,CURLOPT_POSTFIELDS,$postdata);
		
		$result = curl_exec($c);
		$status = curl_getinfo($c,CURLINFO_HTTP_CODE); $error = curl_error($c); $errno = curl_errno($c);
		curl_close($c);
		
		echo "<br><br>THIS PAGE:<br>Status: $status<br>Error: $error ($errno)<br>Result: $result";
		
		die();
	}
	
	
	// Spielerliste
	if (isset($_GET['playerlist'])) {
		$sql = new MySQL();
		date_default_timezone_set('Europe/Paris');
		
		$sql->query('
			SELECT * 
			FROM '.$sql->prefix.'stats__basic b
			LEFT JOIN '.$sql->prefix.'player p
				ON p.id = b.id
			ORDER BY 
				p.id
		');
		
		echo "<pre>";
		
		while ($player = $sql->fetchRow()) {
			$result = Array(
				'ID' => $player['id'],
				'Name' => $player['name'],
				'Region' => $player['region'],
				'Account ID' => $player['account_id'],
				'LB Update' => date("d.m.Y - H:i", $player['date_stats_update']),
				'Created at' => date("d.m.Y - H:i", $player['created_at']),
				'Updated at' => date("d.m.Y - H:i", $player['updated_at']),
				'API State' => $player['api_player_status']
			);
			
			print_r($result);
		}
	}
	
	
	// Abruf der lokalen Statsdaten
	if (isset($_GET['localstats'])) {
		$p = 111;
		echo "<pre>";
		
		if (isset($_GET['basic'])) { 
			$stats = new Stats($p);
			$result = $stats->get('basic');
			print_r($result);
		}
		
		if (isset($_GET['achievements'])) { 
			$stats = new Stats($p);
			$result = $stats->get('achievements');
			print_r($result);
		}
		
		if (isset($_GET['statistics'])) { 
			$stats = new Stats($p);
			$result = $stats->get('statistics');
			print_r($result);
		}
		
		if (isset($_GET['ratings'])) { 
			$stats = new Stats($p);
			$result = $stats->get('ratings');
			print_r($result);
		}
		
		if (isset($_GET['membership'])) { 
			$stats = new Stats($p);
			$result = $stats->get('membership');
			print_r($result);
			print_r(count($result));
		}
		
	}
	
	
	// Stats-Abfrage bei Wargaming
	if (isset($_GET['apistats'])) {
		$func     = new Functions();
		$settings = new Settings();
		$region   = "eu";
		$appid    = json_decode($settings->get("properties_key"));
		$url      = Array(
			'eu' => 'api.worldofwarships.eu',
			'na' => 'api.worldoftanks.com',
			'ru' => 'api.worldoftanks.ru',
			'asia' => 'api.worldoftanks.asia'
		);
		echo "<pre>";
		
		if (isset($_GET['clanlist'])) { 
			$c=curl_init('http://'.$url[$region].'/wot/clan/list/?application_id='.$appid->$region.'&search=Foc in teava');
			curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
			$result = json_decode(curl_exec($c)); curl_close($c);
			print_r($result); die();
		}
		
		if (isset($_GET['claninfo'])) { 
			$c=curl_init('http://'.$url[$region].'/wot/clan/info/?application_id='.$appid->$region.'&clan_id=22223');
			curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
			$result = json_decode(curl_exec($c)); curl_close($c);
			print_r($result); die();
		}
		
		if (isset($_GET['accountlist'])) { 
			$c=curl_init('http://'.$url[$region].'/wot/account/list/?application_id='.$appid->$region.'&search=SIMURQ');
			curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
			$result = json_decode(curl_exec($c)); curl_close($c);
			print_r($result); die();
		}
		
		if (isset($_GET['accountinfo'])) { 
			$c=curl_init('http://'.$url[$region].'/wot/account/info/?application_id='.$appid->$region.'&account_id=509132050');
			curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
			$result = json_decode(curl_exec($c)); curl_close($c);
			print_r($result);die();
		}
		
		if (isset($_GET['ratingstypes'])) { 
			$c=curl_init('http://'.$url[$region].'/wot/ratings/types/?application_id='.$appid->$region);
			curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
			$result = json_decode(curl_exec($c)); curl_close($c);
			print_r($result);die();
		}
		
		if (isset($_GET['ratingsaccounts'])) { 
			$c=curl_init('http://'.$url[$region].'/wot/ratings/accounts/?application_id='.$appid->$region.'&account_id=509132050&type=all');
			curl_setopt($c,CURLOPT_HEADER,false); curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
			$result = json_decode(curl_exec($c)); curl_close($c);
			print_r($result);die();
		}
	}
	
	
	// Signaturen-Test
	if (isset($_GET['signatures'])) {
		echo "<pre>";
		
		$sig = new Signatures(111);
		echo $sig->create_signature().'<br/><br/>';
		echo '<img src="../signatures/111_min.png" /><br/><br/>';
		echo '<img src="../signatures/111_med.png" /><br/><br/>';
		echo '<img src="../signatures/111_max.png" /><br/><br/>';
		echo '<img src="../signatures/1_x01.png" /><br/><br/>';
	}
?>