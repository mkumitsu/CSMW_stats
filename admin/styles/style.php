<?php
	// Settings
	$settings = Array(
		"path" => "",                 // Relativer Pfad zu den CSS-Dateien
		"mode" => "file",             // Modus des CSS-Wrappers (array, file, sql)
		"file_name" => "vars.st"      // Pfad und Dateiname indem die CSS-Werte gespeichert sind (Modus "file")
	);
	
	
	if ($settings['mode'] == "array") {
		$values = Array(
			"var_name" => "css_value"
		);
	}
	else if ($settings['mode'] == "file") {
		if (file_exists($settings['file_name'])) {
			$file = file($settings['file_name'], FILE_IGNORE_NEW_LINES);   // Array mit den Zeilen der CSS-Werte-Datei
			$values = Array();
			
			foreach ($file as $line) {
				$parts = explode(":", $line);
				
				if (is_array($parts)) {
					if (isset($parts[0])) $key = trim($parts[0]); else $key = "";
					if (isset($parts[1])) $value = trim($parts[1]); else $value = "";
					$values[$key] = $value;
				}
			}
		}
	}
	
	// Array mit den CSS3-Befehlen
	$css = Array(
		"box-shadow"    => Array("-webkit-box-shadow", "-moz-box-shadow", "box-shadow"),
		"column-count"  => Array("-webkit-column-count", "-moz-column-count", "column-count"),
		"column-gap"    => Array("-webkit-column-gap", "-moz-column-gap", "column-gap"),
		"border-radius" => Array("-webkit-border-radius", "-moz-border-radius", "border-radius")
	);
	
	
	$styles = explode(",", $_GET['files']);                                                // Array mit den zu verwendeten Stylesheets, ausgehend von den übergebenden GET-Variablen
	header('Content-Type: text/css');                                                      // Header-Information
	
	foreach ($styles as $style) {
		if (file_exists($settings['path'].trim($style).'.css')) {
			$file = file($settings['path'].trim($style).'.css', FILE_IGNORE_NEW_LINES);   // Array mit den Zeilen der Stylesheet-Datei
			
			foreach ($file as $line) {
				// VARIABLEN
				if (strpos($line, "$") !== FALSE) {                                        // Check ob in einer Zeile ein "$" vorhanden ist
					$parts = explode("$", $line);                                          // Die Zeile wird anhand der "$" in ein Array gespalten
					
					for ($i = 1; $i < count($parts); $i++) {
						$check = Array();                                                  // Check mit welchem Zeichen die Variable abgeschlossen wird
						$marks = Array("space" => " ", "semi" => ";", "quote" => "'", "dquote" => '"', "comma" => ",", "clamp" => ")");
						
						foreach ($marks as $key => $value) {
							$pos = strpos($parts[$i], $value); if ($pos !== FALSE) $check[$key] = $pos; // Das erste Vorkommen jedes Zeichens nach dem "$" wird im Array gespeichert
						}
						
						arsort($check);
						$check = array_flip($check);
						$check = array_pop($check);
						$result = $marks[$check];                                           // Das die Variable abschließende Zeichen
						
						$var_end = strpos($parts[$i], $result);                             // Austausch der Variablen
						$var_name = substr($parts[$i], 0, $var_end);
						
						if (is_string($values[$var_name])) $var_value = $values[$var_name]; else $var_value = "0";
						$parts[$i] = substr_replace($parts[$i], $var_value, 0, $var_end);
					}
					
					$line = implode($parts);                                               // Zusammenfügen der Fragmente zur bearbeiteten Zeile
				}
				
				// CSS-BEFEHLE
				if (strpos($line, "?") !== FALSE) {                                        // Check ob in einer Zeile ein "?" vorhanden ist
					$parts = explode("?", $line);                                          // Die Zeile wird anhand der "?" in ein Array gespalten
					
					for ($i = 1; $i < count($parts); $i++) {
						$css_name = substr($parts[$i], 0, strpos($parts[$i], ":"));        // Name der CSS3-Eigenschaft
						$css_value = substr($parts[$i], strpos($parts[$i], ":"));          // Wert der CSS3-Eigenschaft (Hinweis: weitere CSS-Eigenschaften die nicht den Präfix "?" werden der Einfachheit halber dem vorhergehenden Wert zugeordnet)
						
						if (is_array($css[$css_name])) {
							$parts[$i] = "";
							foreach ($css[$css_name] as $name) {                           // Erstellen der CSS-Eigenschaften samt der Werte
								$parts[$i] = $parts[$i].$name.$css_value;
							}
						}
					}
					
					$line = implode($parts);                                               // Zusammenfügen der Fragmente zur bearbeiteten Zeile
				}
				
				echo $line;                                                                // Ausgabe der Zeile
			}
		}
	}
?>