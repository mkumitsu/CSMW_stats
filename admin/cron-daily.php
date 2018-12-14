<?php
$servername = "mysql28.mydevil.net";
$username = "m1163_fragmast3r";
$password = "alpha201RED4";
$dbname = "m1163_csmw-stats";

$mysqli = new mysqli($servername, $username, $password, $dbname);

/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$query = "SELECT name, battles FROM ez3wows__stats__basic";

if ($result = $mysqli->query($query)) {

    /* fetch associative array */
    while ($row = $result->fetch_assoc()) {
		$wynik[] = array($row["name"], $row["battles"]);
	}	
		
		$wielkosc_wynik = count($wynik);
		for($i = 0; $i < $wielkosc_wynik; $i++){
			$string = $string."\r\n".$wynik[$i][0]." ".$wynik[$i][1];
		}
		echo $string;
		
		$to = "fragmast3r@gmail.com";
		$subject = "Aktywność graczy CSMW";
		//$headers = "MIME-Version: 1.0\r\n";
		$headers = "from: kumitsu@csmw.pl";
		$message = "Results: " . print_r( $value, true );

		mail($to , $subject , $string , $headers);

		$result->free();
}

/* close connection */
$mysqli->close();
?>