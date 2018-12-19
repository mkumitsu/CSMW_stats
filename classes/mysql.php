<?php
	class MySQL {
		public $result = NULL;
		private $counter = NULL;
		public  $connection = NULL;
		public  $id = NULL;
		public  $debug = NULL;
		public  $prefix = "";
		public  $mysqli = 0;
		public function __construct() {
			if (defined('SQL')) {
				$sql_data = json_decode(SQL);
				$this->prefix = $sql_data->prefix;
				$this->debug = $sql_data->debug;
				$this->mysqli = $sql_data->mysqli;
				
				if ($this->mysqli) {
					$this->connection = new mysqli($sql_data->sqlhost, $sql_data->sqluser, $sql_data->sqlpwd, $sql_data->sqldb);
					if (mysqli_connect_error()) {
						die('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
					}
				} else {
					$this->connection = mysql_connect($sql_data->sqlhost, $sql_data->sqluser, $sql_data->sqlpwd) or die('ERROR: Can not connect to MySQL-Server');
					mysql_select_db($sql_data->sqldb, $this->connection) or die('ERROR: Can not connect to database "'.$sql_data->sqldb.'"');
				}
			}
		}
		public function disconnect() {
			if ($this->mysqli) {
				$this->connection->close();
			} else {
				if (is_resource($this->connection === true)) mysql_close($this->connection);
			}
		}
		public function query($query) {
			if ($this->debug) {
				if ($this->mysqli) {
					$this->result = $this->connection->query($query) or die('Query failed: <br />errorno='.$this->connection->errno.'<br />error='.$this->connection->error.'<br />query='.$query);
				} else {
					$this->result = mysql_query($query, $this->connection) or die('Query failed: <br />errorno='.mysql_errno().'<br />error='.mysql_error().'<br />query='.$query);
				}
			} else {
				if ($this->mysqli) {
					$this->result = $this->connection->query($query) or die('Query failed!');
				} else {
					$this->result = mysql_query($query, $this->connection) or die('Query failed!');
				}
			}
			
			$this->id = $this->mysqli ? $this->connection->insert_id : mysql_insert_id($this->connection);
			$this->counter = NULL;
		}
		public function fetchRow($column=false) {
			if ($column === false) {
				if ($this->mysqli) return $this->result->fetch_array();
				else               return mysql_fetch_array($this->result);
			} else {
				if ($this->mysqli)  $array = $this->result->fetch_array();
				else                $array = mysql_fetch_array($this->result);
				
				return $array[$column];
			}
		}
		public function fetchAssoc() {
			if ($this->mysqli) return $this->result->fetch_assoc();
			else               return mysql_fetch_assoc($this->result);
		}
		public function count() {
			if ($this->mysqli) {
				if ($this->counter === NULL) {
					$this->counter = $this->result->num_rows;
				}
			} else {
				if($this->counter === NULL && is_resource($this->result) === true) {
					$this->counter=mysql_num_rows($this->result);
				}
			}
			
			return $this->counter;
		}
	}
?>