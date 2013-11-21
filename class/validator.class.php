<?php
/**
 * validates data
 *
 */
class validator{
	
	public static function check_zipcode($zipcode)
	{
    $zipcode = str_replace('-', '', $zipcode);
    $zipcode = str_replace(' ', '', $zipcode);
    if (!is_numeric($zipcode) || strlen($zipcode) != 5)
    {
        return false;
    }
    
    return true;
	}

	public static function check_onlyalphabetical($text)
	{
	setlocale(LC_COLLATE, 'en_US.utf8');
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = str_replace('?', '', $text);
    $text = str_replace("'", '', $text);
    $text = str_replace('-', '', $text);
    $text = str_replace(' ', '', $text);
    if (!ctype_alpha($text))
    {
        return false;
    }
    return true;
	}
		
	public static function check_phone($phone)
	{
    $phone = str_replace('-', '', $phone);
    $phone = str_replace(' ', '', $phone);
    $phone = str_replace('(', '', $phone);
    $phone = str_replace(')', '', $phone);
    if (!is_numeric($phone) || strlen($phone)<9)
    {
        return false;
    }  
    return true;
	}
	
	public static function check_pesel($pesel){
		if (strlen($pesel) != 11 || !is_numeric($pesel))
		{
			return false;
		}
		$multipliers=('13791379131'); 
		$sum=0;
		for($i=1;$i<=11;$i++) $sum+=substr($pesel,$i-1,1)*substr($multipliers,$i-1,1);
		return ($sum%10==0);
	}

	public static function check_email($email){ 
		if(ereg("^.+@.+\..+$", $email)) 
			return (true); 
		else
			return (false); 
	}
	
	public static function check_zip($zip){
		if(ereg('^[0-9]{2,2}-[0-9]{3,3}$', $zip)){
			return true;
		}else{
			return false;
		}
	}

}
?>