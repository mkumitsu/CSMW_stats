<?php
	class Stats {
		private $sql = NULL;
		private $tpl = NULL;
		private $id = NULL;
		private $stats = NULL;
		private $settings = NULL;
		private $result = NULL;
		
		
		public function __construct($playerid = NULL) {
			$this->tpl       = new Templates();
			$this->settings  = new Settings();
			
			$this->tpl->load_phrases("overview", true);
			$this->tpl->load_phrases("_bf4_overview", true);
			$this->stats = Array();
			
			if ($playerid) $this->id = $playerid;
		}
		
		
		/**
			* @desc  RETURNS A STATS VALUE FROM DATABASE
			*
			* @param table   - string - Name of the table, in which the required values are stored
			* @param value   - string - Name of the column, which shall be returned. If false, all values of the table are returned in a array
			* @param options - array  - Options, according to which the values are converted
			* @param plain   - bool   - Switch, if the result shall be plain text or a array
			
			* @return string/array
		*/
		public function get($table = "player", $value = false, $options = false, $plain = true) {
			// Check, if the data is already saved in $this->stats
			// If not, they will be JSON-decoded and saved in there
			if (!isset($this->stats[$table])) {
				$this->sql = new MySQL();
				
				// Create tablename
				if      ($table == "player")  $tablename = $this->sql->prefix.'player'; 
				else if      ($table == "encyclopedia")  $tablename = $this->sql->prefix.'encyclopedia';
				else if ($table == "basic")   $tablename = $this->sql->prefix.'stats__basic'; 
				else                          $tablename = $this->sql->prefix.'stats_'.$table;
				
				// Load data from table
				$this->sql->query('SELECT * FROM '.$tablename.' WHERE id = "'.$this->id.'"');
				
				// Do a JSON decode of the data
				$row = $this->sql->fetchAssoc();
				$this->sql->disconnect();
				$data = Array();
				
				if ($table == "player" OR $table == "basic") {
					// Tables with data in different columns
					foreach ($row as $cell => $content) {
						if ($cell != "id") {
							$obj_content = json_decode($content);
							if (is_object($obj_content) OR is_array($obj_content)) 
								 $data[$cell] = $obj_content;
							else $data[$cell] = $content;
						}
					}
				} 
				else if ($table == "rankings") {
					// Tables with data stored in one column
					$row = json_decode($row['data']);
					$row = $row->rankings;
					
					foreach ($row as $cell => $content) {
						if (is_numeric($content->ident)) {
							$data[$content->label] = $content;
						} else {
							$data[$content->ident] = $content;
						}
					}
				}
				else if ($table == "ships") {
					// Tables with data stored in one column
					$row = json_decode($row['data']);
					
					
					foreach ($row as $cell => $content) {
							$data[$cell] = $content;
					}
				}
								
				else {
					// Tables with data stored in one column
					$row = json_decode($row['data']);
					
					if ($row !== NULL) {
						foreach ($row as $cell => $content) {
							$data[$cell] = $content;
						}
					}
				}
				
				// Save the decoded data into $this->stats
				$this->stats[$table] = $data;
			}
			
			
			// Save the desired variable(s) into $this->result
			if ($value) {
				if (isset($this->stats[$table][$value]))
					 $this->result = $this->stats[$table][$value];
				else $this->result = "n/a";
			} else {
				$this->result = $this->stats[$table];
			}
			
			
			// Do a format onto the variable(s)
			if ($options) {
				$data = $this->result;
				
				// Convert strings into array if neccessary
				if (!is_array($data))    $data = Array($data); 
				if (!is_array($options)) $options = Array($options);
				
				// All desired data-sets will be formatted
				foreach ($data as $key => $value) {
					$data[$key] = $this->form($value, $options, $plain);
				}
				
				// If just one result is in the data-array, only this one will be returned
				if (count($data) == 1) $data = $data[0]; // Falls nur ein Datensatz im Datenarray ist, wird nur der Inhalt des Datensatzes zurückgegeben
				
				// Save the result
				$this->result = $data;
			}
			
			
			// Return the result
			return $this->result;
		}
		
		
		/**
			* @desc   FORMATS RAW STATS VALUES
			*
			* @param  value   - string - Value, which has to be formatted
			* @param  options - array  - Options, according to which the values are converted
			* @param  plain   - bool   - Switch, if the result shall be plain text or a array
			
			* @return string or array:
										['format'] = Formatted value for output
										['raw']    = Raw value from database for later operations
										['sort']   = Value converted for tablesorter
										['tip']    = String with text for tooltipp
		*/
		public function form($value, $options, $plain=true) {
			/* Variablen */
			$path = $this->settings->get('functions_cms') == "standalone" ? "" : $this->settings->get('properties_path');
			
			
			### NUMBERS ###
			if ($options[0] == 'number') {
				if (isset($options[3]) AND $options[3]) $string  = $options[3]; else $string = "";
				if (isset($options[2]) AND $options[2]) $suffix  = $options[2]; else $suffix = "";
				if (isset($options[1]) AND $options[1]) $decimal = $options[1]; else $decimal = 0;
				
				// Format
				if ($value) {
					$separator = $this->settings->get("settings_language") != "hebrew" ? " " : ""; // Space as thousands separator: Workaround for RTL languages
					
					switch ($suffix) {
						case "k": $form_value = number_format($value/1000, $decimal, ".", $separator); break;
						case "%": $form_value = number_format($value*100, $decimal, ".", $separator); break;
						default:  $form_value = number_format($value, $decimal, ".", $separator);
					}
				} else {
					$form_value = $value;
				}
				
				$name = $this->get('player', 'name');
				
				// Tip
				if ($string) {
					switch ($string) {
						case "ribbons": case "medals":
							$number = $form_value.$suffix;
							$tip = $this->tpl->phrase($string.'_foot_desc');
							eval ( "\$tip = \"$tip\";" );
							break;
						default:
							$number = number_format($value, $decimal, ".", " ");
							$tip = $this->tpl->phrase($string.'_desc');
							eval ( "\$tip = \"$tip\";" );
					}
				} else {
					$tip = ""; //number_format($value, $decimal, ".", " ");;
				}
				
				// Output
				if (!$plain) {
					return Array(
						'raw'    => $value,
						'sort'   => $this->sort_value($value),
						'format' => $form_value.$suffix,
						'tip'    => $tip
					);
				} else return $form_value.$suffix;
			}
			
			
			### DATE & TIME ###
			if ($options[0] == 'time') {
				if (isset($options[2]) AND $options[2]) $suffix = $options[2];  else $suffix = "";
				if (isset($options[1]) AND $options[1]) $decimal = $options[1]; else $decimal = 0;
				
				// Format
				if ($value) {
					$time = Array();
					$time['sec'] = $value % 60;
					$time['min'] = (($value - $time['sec']) / 60) % 60;
					$time['std'] = (((($value - $time['sec']) /60) - $time['min']) / 60) % 24;
					$time['day'] = floor( ((((($value - $time['sec']) /60) - $time['min']) / 60) / 24) ); 
					$time_string = sprintf('%dd %sh %sm', $time['day'],str_pad($time['std'], 2, "0", STR_PAD_LEFT), str_pad($time['min'], 2, "0", STR_PAD_LEFT));
					$time_hhmm   = sprintf('%s:%s', str_pad($time['std'], 2, "0", STR_PAD_LEFT), str_pad($time['min'], 2, "0", STR_PAD_LEFT));
					
					if ($suffix == "h") 	$form_value = number_format(round($value/3600, $decimal), $decimal, ".", " ");
					if ($suffix == "min") 	$form_value = number_format(round($value/60, $decimal), $decimal, ".", " ");
					if ($suffix == "hhmm") {
						$form_value = $time_hhmm;
						$suffix = "";
					}
					if ($suffix == "dynamic") {
						if      ($value < 60)   $form_value = round($value)."s";
						else if ($value < 3600) $form_value = number_format(round($value/60), 0, ".", " ")."m";
						else                    $form_value = number_format(round($value/3600, 1), 1, ".", " ")."h";;
						$suffix = "";
					}
					if ($suffix == "date") {
						$form_value = $time_string;
						$suffix = "";
					}
				} else {
					$value = 0;
					$form_value = 0;
					$time_string = "0 min";
					if ($suffix == "date") $suffix = "";
					if ($suffix == "dynamic") $suffix = "s";
				}
				
				// Tip
				$tip = $this->tpl->phrase('time_desc');
				eval ( "\$tip = \"$tip\";" );
				
				// Output
				if (!$plain) {
					return Array(
						'raw'    => $value,
						'sort'   => $this->sort_value($value),
						'format' => $form_value.$suffix,
						'tip'    => $tip
					);
				} else return $form_value.$suffix;
			}
			
			
			### RATIOS ###
			if ($options[0] == 'ratio') {
				if (isset($options[4]) AND $options[4]) $string = $options[4];  else $string = "";
				if (isset($options[3]) AND $options[3]) $suffix = $options[3];  else $suffix = "";
				if (isset($options[2]) AND $options[2]) $decimal = $options[2]; else $decimal = 0;
				if (isset($options[1]) AND $options[1]) $divisor = $options[1]; else $divisor = 1;
				
				
				// Format
				if ($value) {
					$result = ($divisor != 0) ? $value / $divisor : 0;
					$form_value = number_format($result, $decimal, ".", " ");
					
					if ($suffix == "%") $form_value = number_format($result * 100, $decimal, ".", " ");
				} else {
					$result = 0;
					$value = 0;
					$form_value = 0;
				}
				
				// Tip
				if ($string) {
					$tip = $this->tpl->phrase($string.'_desc');
					eval ( "\$tip = \"$tip\";" );
				} else {
					$tip = $form_value.$suffix;
				}
				
				// Ausgabe
				if (!$plain) {
					return Array(
						'raw'    => $result,
						'sort'   => $this->sort_value($result),
						'format' => $form_value.$suffix,
						'tip'    => $tip
					);
				} else return $form_value.$suffix;
			}
		}
		
		
		/**
			* @desc   RETURNS A CONVERTED INTEGER FOR TABLE SORTER
			* @param  value
			* @return int
		*/
		private function sort_value($value) {
			$value = round($value, 2);							// Rounded to two decimal places
			$value = $value * 100;								// Remove the decimal places
			$value = $value + 1000000;							// Add a million to avoid negative values
			$value = str_pad($value, 16, "0", STR_PAD_LEFT);	// Fill up with zeros
			return $value;
		}
	}
?>