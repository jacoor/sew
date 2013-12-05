<?php
/** rename this to config.php
 * configuration data
 * @author jacek(at)acoor.net
 *and yes, it could be an array, but I don't like arrays
 *and it is like this b'cause __callStatic will not be avaliable before PHP 5.3 
 */
final class config{
	
	private static $hostname = "";  #mysql host name
	private static $username = "";	#mysql user name
	private static $pass = ""; #mysql pass
	private static $dbname = "";
	private static $port = '3306';
	private static $mail_host="";
	private static $mail_user="";
	private static $mail_pass=""; 
	private static $mail_from_name="";//@FIXME utf8 characters
	private static $mail_from_email="";
	private static $mail_reply_to="";
	private static $mail_port="";//int
	private static $mail_auth=""; //bolean
	private static $finalNr = '22';
	
	private static $required_photo_type = 'image/jpeg';
	private static $photo_width = 800;
	private static $photo_height = 800;
	private static $photo_max_size = 2097152; //2 MB
	
	private static $photo_save_path = '/photos/';
	
	private static $photo_deadline = '2013/12/26';

	private static $statements_path ="/statements/";
		
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
	
	public static function mail_reply_to(){
		return self::$mail_reply_to;
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
	
	public static function required_photo_type(){
			return self::$required_photo_type;
	}
	
	public static function photo_width(){
			return self::$photo_width;
	}
	
	public static function photo_height(){
			return self::$photo_height;
	}
	
	public static function photo_max_size(){
			return self::$photo_max_size;
	}
	
	public static function photo_save_path(){
			return self::$photo_save_path;
	}
	
	public static function photo_deadline(){
			return self::$photo_deadline;
	}
	public static function statements_path(){
			return self::$statements_path;
	}
}
?>
