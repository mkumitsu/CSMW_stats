<?php
	class Security {
		private $sql = NULL;
		
		public function __construct() {
			$this->sql = new MySQL();
			
			$this->security_slashes($_GET);
			$this->security_slashes($_POST);
			$this->security_slashes($_COOKIE);
			
			$this->sql->disconnect();
		}
		
		public function security_slashes(&$array) {
			foreach($array as $key => $value) {
				if(is_array($array[$key])) {
					$this -> security_slashes($array[$key]);
				}
				else {
					if (function_exists('get_magic_quotes_gpc') AND @get_magic_quotes_gpc()) {
						$tmp = stripslashes($value);
					}
					else {
						$tmp = $value;
					}
					if($this->sql->mysqli AND function_exists("mysqli_real_escape_string")) {
						$array[$key] = $this->sql->connection->real_escape_string($tmp);
					}
					else if(!$this->sql->mysqli AND function_exists("mysql_real_escape_string")) {
						$array[$key] = mysql_real_escape_string($tmp);
					}
					else {
						$array[$key] = addslashes($tmp);
					}
					unset($tmp);
				}
			}
		}
	}
?>