<?php
require_once	$_SERVER['DOCUMENT_ROOT'].'/class/generic.Class.php';
require_once ($_SERVER['DOCUMENT_ROOT'].'/class/sqlManager.Class.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/class/volunteer.Class.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/class/meeting.Class.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/class/notice.Class.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/class/session.Class.php');
/**
 * 
 * @todo wziasc sessionManager z innego projektu (Maziak) i tu wrzucic jako zmienną publiczną.
 * zmodyfikować też odpowiednio do tego projektu. 
 *
 */
final class EngineClass{
	private $db;	
	private static $table_names = array(
																			 	'volunteer' 	=> 'volunteers',
																			 	'meeting' 		=> 'meetings',
																				'notice' 			=> 'notices',	
																			);
	private static $Instance;
	public $session;
	
	
	public static function GetInstance()
	{
		
		if ( !isset(self::$Instance))
		{
			self::$Instance=new EngineClass();
		}
		return self::$Instance;
	}

	/**
	 * private constructor of EngineClass - singletone
	 *
	 */
	private function __construct()
	{		
 		$path = ini_get('include_path');
 		ini_set('include_path', $_SERVER['DOCUMENT_ROOT']."/pear" .PATH_SEPARATOR.$path);
		
		$this->db = sqlManager::GetInstance();
		$this->session = session::GetInstance($this->db, $this);
	}
	
	/**
	 * load volunteers
	 * @param $data associative array with data to search for
	 * @param $data indexed array of what has to be returned
	 * @return array | false
	 */
	public function loadVolunteers($data = null, $res = null){
		$ar = $this->db->getRows(self::$table_names['volunteer'], $data, $res);
		if ($ar){
			foreach ($ar as $val)
				$result[] = new volunteer($this,$val);
		}
		return $result ? $result : false;
	}
	
	/**
	 * loads single notice
	 * @param $id
	 * @return notice object
	 */
	public function loadNotice($id){
		$ar = $this->db->getRows(self::$table_names['notice'], array('id' => $id));
		if ($ar)
			$result = new notice($this,$ar[0]);
		return $result ? $result : false;
	}	
	/**
	 * loads notices related to meeting and volunteer
	 * @param $mid meeting id
	 * @param $vid volunteer id
	 * @return notice obj or false
	 */
	public function loadNoticeRelatedToMeetingAndVolunteer($mid,$vid){
		$ar = $this->db->getRows(self::$table_names['notice'], array('vid'=>$vid, 'mid' => $mid));
		if ($ar)
			$result = new notice($this,$ar[0]);
		return $result ? $result : false;
	}	
	
	/**
	 * loads notices related to volunteer
	 * @param $vid volunteer id
	 * @return array of noticeObj or false
	 */
	public function loadNotices($vid){
		$ar = $this->db->getRows(self::$table_names['notice'], array('vid'=>$vid));
		if (is_array($ar)){
			foreach ($ar as $val)
				$result[] = new notice($this,$val);
		}
		return $result ? $result : false;
	}

	/**
	 * loads all meetings
	 * @return array
	 */
	public function loadMeetings(){
		$ar = $this->db->getRows(self::$table_names['meeting'],null,null,'date asc, time asc');
		if ($ar){
			foreach ($ar as $val)
				$result[] = new meeting($this,$val);
		}
		return $result ? $result : false;
	}

	
	/**
	 * loads  meeting
	 * @return array
	 */
	public function loadMeeting($id){
		$ar = $this->db->getRows(self::$table_names['meeting'],array('id'=>$id));
		if ($ar){
				return new meeting($this,$ar[0]);
		}
		return false;
	}

	
	/*
	 * saves object
	 * @param $obj genericClass
	 * @return void
	 */
	public function save(genericClass $obj){
		$class=get_class($obj);
		$data = $this->prepate_data_for_db($obj->get());
		$this->db->insertRow(self::$table_names[$class],$data);
	}
	
	/**
	 * updates object
	 * @param $obj
	 * @return void
	 */
	public function update(genericClass $obj){		
		$class=get_class($obj);
		$what = $this->prepate_data_for_db($obj->get());
		$where['id'] = $what['id'];  
		unset($what['id']);
		$this->db->updateRow(self::$table_names[$class],$what,$where);
	}	
	
	/**
	 * deletes object
	 * @param $obj
	 * @return void
	 */
	public function delete(genericClass $obj){
		$class=get_class($obj);
		$where['id'] = $this->prepate_data_for_db($obj->id);
		$what['deleted'] = 1; 
		$this->db->updateRow(self::$table_names[$class],$what,$where);
	}
	
	/**
	 * restores object
	 * @param $obj
	 * @return void
	 */
	public function restore(genericClass $obj){
		$class=get_class($obj);
		$where['id'] = $this->prepate_data_for_db($obj->id);
		$what['deleted'] = 0; 
		$this->db->updateRow(self::$table_names[$class],$what,$where);
	}
	
	/**
	 * wipes object
	 * @param $obj
	 * @return void
	 */
	public function wipe(genericClass $obj){
		$class=get_class($obj);
		$where['id'] = $this->prepate_data_for_db($obj->id);
		$this->db->deleteRows(self::$table_names[$class],$where);
	}
		
	/**
	 * prepares data for sqlmanager
	 * @param $data array of data
	 * @return array
	 */
	private function prepate_data_for_db(array &$data){
		unset($data['changed_flag']);
		unset($data['notices']);										
		foreach ($data as $key => &$val)
			if ($val == '\'\'' || $val === null) unset ($data[$key]);
		if (isset($data['ACL'])) $data['ACL'] = serialize($data['ACL']);
		return $data;
	}
	
	/**
	 * sends email to desired address
	 * @param $msg html msg
	 * @param $txt txt msg
	 * @param $topic
	 * @param $receiver
	 * @return Pear:error or true
	 */
	public function sendMail($msg, $txt, $topic, $receiver){
		require_once	$_SERVER['DOCUMENT_ROOT'].'/pear/Mail.php';
		require_once	$_SERVER['DOCUMENT_ROOT'].'/pear/Mail/mime.php';

		$crlf = "\n";
		$mime = new Mail_mime($crlf);
		$to = $mime->encodeRecipients($receiver);
		$from='"'.config::mail_from_name().'"<'.config::mail_from_email().'>';
		$replyTo='"'.config::mail_from_name().'"<'.config::mail_from_email().'>';
		$mime->setTXTBody($text);
		$mime->setHTMLBody($msg);
		$build_params = array(
 			'head_encoding' => 'base64',
			'text_encoding' => '8bit',
			'html_encoding' => '8bit',
 			'head_charset' => 'utf-8',
			'text_charset' => 'utf-8',
			'html_charset' => 'utf-8'
		);
		$hdrs = array( 'From' => $from,
	    'Reply-To'=> $replyTo,
    	'To' => $to,
    	'Subject' => $topic ); 
		$body = $mime->get($build_params);
		$hdrs = $mime->headers($hdrs);

		$mail = Mail::factory('smtp',
  		array (
  			'host' 			=> config::mail_host(),
    		'auth' 			=> config::mail_auth(),
   			'port' 			=> config::mail_port(),
    		'username' 	=> config::mail_user(),
    		'password' 	=> config::mail_pass()
  			)
  		);
		$mail->send($to, $hdrs, $body);
		if (PEAR::isError($mail)) {
			return $mail->getMessage();
		} else {
		 	return true;
		}
	}
	/**
	 * checks if data can be safely injected into DB without breaking foreign keys.
	 * @param $data array
	 * @param $ojb_name
	 * @return true if ok, array with field names otherwise
	 */
	public function check_if_object_exists($data, $obj_name){
		$table = self::$table_names[$obj_name];
		switch ($table){
			case 'volunteers':
				$unique = array('login','email','PESEL');
			break; 
			}			
		if (isset($unique)) {
			$get = array('id');
			foreach ($unique as $val) {
				$search[$val] = $data[$val];
				if ($row = $this->db->getRows($table,$search, $get)){
					$returner[$val] = $row[0]['id']; 
				}
				unset($search);
			}
		}
		return $returner ? $returner : true;
	}
	
	public function getIdentNr($id, $finalNr){
		$finalNr = addslashes($finalNr);
		$id = addslashes($id);
		$query = "SELECT DISTINCT ident_nr 
							FROM notices 
							WHERE type_of IN ('rozliczenie', 'numer identyfikatora')
							AND final_nr = {$finalNr} 
							AND vid = {$id}
							AND deleted = 0
							";
		$res = $this->db->query($query);
		if (!$this->db->error){
			while ($row = $res->fetch_assoc())
				$result = $row['ident_nr'];
		}	
		//var_dump($result);die;
		return $result;
	}
}
?>