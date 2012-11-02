<?php
/** rename this to config.php
 * configuration data
 * @author jacek(at)acoor.net
 *and yes, it could be an array, but I don't like arrays
 *and it is like this b'cause __callStatic will not be avaliable before PHP 5.3
 */
final class config{

	private static $hostname = "127.0.0.1";
	private static $username = "sew";
	private static $pass = "123456";
	private static $dbname = "sew";
	private static $port = "3306";
	//private static $mail_host="ssl://mail.icenter.pl";
	private static $mail_host="ssl://mail.icenter.pl";
	private static $mail_user="wosp_kontakt";
	private static $mail_pass="";
	private static $mail_from_name="Wroclawski Sztab WOSP";//@FIXME utf8 characters
	private static $mail_from_email="kontakt@wosp.wroc.pl";
	private static $mail_port=465;//int
	//private static $mail_port=587;//int
	private static $mail_auth=true; //bolean
	private static $finalNr = '20';

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