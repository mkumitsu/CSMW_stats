<?php
	class Session {
		private static $instance = NULL;
		private static $sid = NULL;
		private static $sessionpasswort = 'ezzemm_session_password';
		
		private function __construct(){
			if (isset($_GET['sid']) === true) {
				self::$sid = $_GET['sid'];
			} else {
				if (isset($_POST['sid']) === true){
					self::$sid = $_POST['sid'];
				}
			}
			$testSession = explode('-', self::$sid);
			$testMD5 = md5($testSession[0].self::$sessionpasswort);
			
			if(!isset($testSession[1]) OR $testMD5 !== $testSession[1]){
				self::erzeugeSID();
			}
		}
		
		public static function getInstance(){
			if (self::$instance === NULL){
				self::$instance = new self;
			}
			return self::$instance;
		}
		
		private function __clone() {}
		
		private static function erzeugeSID(){
			$tmp = md5((string)mt_rand() . $_SERVER['REMOTE_ADDR'] . time());
			self::$sid=$tmp .'-'.md5($tmp.self::$sessionpasswort) ;
		}
		
		public static function showLink($mitFragezeichen=false){
			echo ($mitFragezeichen!==true) ? '' : '?', 'sid=',self::getSID();
		}
		
		public static function getSID(){
			return self::$sid;
		}
		
		public static function sid(){
			return 'sid='.self::getSID();
		}
	}
	$singlesession = session::getInstance();
?>