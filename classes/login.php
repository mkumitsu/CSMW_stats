<?php
	class Login {
		private $id = NULL;
		private $name = NULL;
		private $passwort = NULL;
		private $email = NULL;
		private $session = NULL;
		private $dbconnection = NULL;
		
		public function __construct($sessionid){
			$this->settings = new Settings();
			$this->dbconnection = new MySQL();
			$this->session = $sessionid;
			
			if (isset($_POST['login'])===true && 
				isset($_POST['username'])===true && 
				isset($_POST['userpass'])===true) {
				
				$this->name = $_POST['username'];
				$this->passwort = $_POST['userpass'];
				$this->log_in();
				return true;
			}
			
			if (isset($_GET['logout']) === true && $_GET['logout'] === '1'){
				$this->log_out();
			}
		}
		
		private function log_in(){
			$query = '
				SELECT * FROM '.$this->dbconnection->prefix.'users WHERE
					name     = "'.$this->name.'" AND
					password = "'.MD5($this->passwort).'"
				LIMIT 1;
			';
			
			$this->dbconnection->query($query);
			
			if($this->dbconnection->count() !== 1) {
				return false;
			} else {
				$row = $this->dbconnection->fetchRow();
				$this->email      = $row['email'];
				$this->id         = $row['id'];
				
				$this->log_out();
				
				$query = '
					UPDATE '.$this->dbconnection->prefix.'users
					SET  
						session = "'.$this->session.'"
					WHERE 
						id = '.$this->id.' 
					LIMIT 1;
				';
				
				$this->dbconnection->query($query);
				
				return true;
			}
		}
		
		public function logged_in(){
			$query = '
				SELECT * FROM '.$this->dbconnection->prefix.'users WHERE  
					session = "'.$this->session.'"
				LIMIT 1;
			';
			
			$this->dbconnection->query($query);
			
			return ($this->dbconnection->count()!==1) ? false : true;
		}
		
		public function showLogin(){
			echo '<form method="post"><fieldset"><legend>Benutzeranmeldung</legend><br /><label for="textinput1">Benutzername</label><input type="text" name="username"><br /><label>Passwort</label><input type="password" name="userpass" id="textinput2"><br /><br /><button name="login" type="submit">Anmelden</button> </fieldset></form>';
		}
		
		public function showLogout($text='Logout'){
			echo '<a href="?logout=1&sid='.$this->session.'">'.$text.'</a>';
		}
		
		private function log_out(){
			$query = '
				UPDATE  
					'.$this->dbconnection->prefix.'users
				SET  
					session = NULL 
				WHERE 
					session = "'.$this->session.'"
				LIMIT 1;
			';
			
			$this->dbconnection->query($query);
		}
	}
?>