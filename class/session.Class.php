<?php
/**
 *	Jacoor, 19.11.2007 
 **/
		
  class session{
		private 					$php_session_id;
		private 					$logged_in;
		private 					$user;
		private 					$user_id;
		private 					$session_timeout = 600; // default 10 minutes session inactivity time
		private 					$session_lifespan = 3600; // default maximum session lifetime: 1h		
		private 					$error;
		private 					$errno;
		private static		$Instance;
		private 					$key='klucz sprawdzenia sesji';
		private						$strUserAgentPlusKey;
		private 					$db;
		private 					$engine;
		
	public static function GetInstance($dbManager, $engine)
		{
			if ( !isset(self::$Instance))
				{
					self::$Instance=new session($dbManager, $engine);
				}
			return self::$Instance;
		}
		
		private function __construct($dbManager, $engine){
		$this->db = $dbManager;
		$this->engine = $engine;
		$this->key = sha1($this->key); 
		session_start();
		$this->strUserAgentPlusKey=sha1($_SERVER['HTTP_USER_AGENT'].$this->key);
		if ($_COOKIE["PHPSESSID"] )
			{
				$correct=true;
				$this->php_session_id=$_COOKIE['PHPSESSID'] ;
				if		(!isset($_SESSION['key'])) {
						$_SESSION['key']=$this->key;
				}elseif($_SESSION['key']!=$this->key) {
						$correct=false;
				}
				if		(!isset($_SESSION['browser'])) {
						$_SESSION['browser']=$this->strUserAgentPlusKey;
				}elseif($_SESSION['browser']!=$this->strUserAgentPlusKey) {
						$correct=false;
				}
				if ($correct==true){
					session_regenerate_id();
		  		session_set_cookie_params($this->session_lifespan);
					
					if (isset($_SESSION['user_id'])) {
						if ($_SESSION['logged_in']==true) 
							$this->logged_in=true;
						$this->user_id=$_SESSION['user_id'];
						$user_data = $this->db->getRows('volunteers',array('id'=>$this->user_id));
						if (is_array($user_data)){
							$this->user= new volunteer($this->engine, $user_data[0]);}
						else session_destroy();
					}
					
  			}else{
  				unset($_COOKIE['PHPSESSID']);
					session_destroy();
  			}
			}
    }
		
		public function login($username, $pass){
			$user_login=trim($user_login);
			$pass=trim($pass);
			$user_data = $this->db->getRows('volunteers',array('login'=>$username, 'password'=>sha1($pass), 'active'=>1));
			if (is_array($user_data)){
					$this->user = new volunteer($this->engine, $user_data[0]);				
					$this->logged_in=true;
					$_SESSION['logged_in']=true;
					$_SESSION['user_id']=$this->user->id;
					$_SESSION['key']=$this->key;
					return(true);					
				}
			else 
				{
					$this->logged_in=false;
					$_SESSION['logged_in']=false;
					$_SESSION['user_id']='';
					$this->user='';
					session_destroy();
					return (false);
			}
		}
		
		public function logOut(){
			session_destroy();
			$this->logged_in=false;
			unset($_SESSION);
		}
		
		public function isLoggedIn(){
			return $this->logged_in;
		}
		
		public function getUser(){
			return $this->user;
		}
	
		public function __get($name){
			return unserialize($_SESSION[$name]);
		}
		
		public function __set($name,$value){
			$_SESSION[$name]=serialize($value);
		}
		
	}
?>
