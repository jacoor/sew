<?php
/** rename this to config.php
 * configuration data
 * @author jacek(at)acoor.net
 *and yes, it could be an array, but I don't like arrays
 *and it is like this b'cause __callStatic will not be avaliable before PHP 5.3 
 */
final class config{
	
	private static $hostname = "";
	private static $username = "";
	private static $pass = "";
	private static $dbname = "";
	private static $port = '3306';
	private static $mail_host="";
	private static $mail_user="";
	private static $mail_pass=""; 
	private static $mail_from_name="";//@FIXME utf8 characters
	private static $mail_from_email="";
	private static $mail_port="";//int
	private static $mail_auth=""; //bolean
	private static $finalNr = '17';
		
	public static function hostname(){
		return self::$hostname;
	}
	
	public static function username(){
		return self::$username;
	}
	
	public static function pass(){
		return self::$pass;
	}
	
	public static function dbname(){
		return self::$dbname;
	}
	
	public static function port(){
		return self::$port;
	}

	public static function mail_host(){
		return self::$mail_host;
	}
	
	public static function mail_user(){
		return self::$mail_user;
	}
	
	public static function mail_pass(){
		return self::$mail_pass;
	}
	
	public static function mail_from_email(){
		return self::$mail_from_email;
	}
	
	public static function mail_from_name(){
		return self::$mail_from_name;
	}
	
	public static function mail_port(){
		return self::$mail_port;
	}
	
	public static function mail_auth(){
		return self::$mail_auth;
	}
	
	public static function finalNr(){
		return self::$finalNr;
	}
}
?>
