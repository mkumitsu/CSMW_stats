<?php
	class Settings {
		private $sql = NULL;
		public $settings = NULL;
		
		public function __construct() {
			$this->sql = new MySQL();
			$this->settings = new stdClass();
			
			$this->sql->query('SELECT * FROM '.$this->sql->prefix.'settings');
			if ($this->sql->count()) {
				while($row = $this->sql->fetchRow()) {
					$this->settings->$row['name'] = $row['value'];
				}
			}
			$this->sql->disconnect();
		}
		
		public function all() {
			return $this->settings;
		}
		
		public function get($key) {
			return $this->settings->$key;
		}
		
		public function set($key, $value, $save=false) {
			$this->settings->$key = $value;
			
			if ($save) {
				$this->sql = new MySQL();
				$this->sql->query('UPDATE '.$this->sql->prefix.'settings SET value = "'.$value.'" WHERE name = "'.$key.'"');
				$this->sql->disconnect();
			}
			
			return true;
		}
	}
?>