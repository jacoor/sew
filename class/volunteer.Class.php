<?php
require_once	$_SERVER['DOCUMENT_ROOT'].'/class/generic.Class.php';
/**
 * volunteer.class
 * @author jacek(at)jacoor.net
 *
 */
final class volunteer extends genericClass implements PHPSucks{
	private $noticesLoadedFlag 	= 0	;
	private $id 										;
	private $name 									;
	private $surname 								;
	private $login 									;
	private $password 							;
	private $email 									;
	private $photo;
	private $h_street 							;
	private $h_building 							;
	private $h_loc 							;
	private $h_zip 							;
	private $h_city									;
	private $school_name					;
	private $school_street					;
	private $school_building					;
	private $school_loc					;
	private $school_zip					;
	private $school_city					;
	private $birth_date 						;
	private $PESEL 									;
	private $phone 									;
	private $p_phone 								;
	private $r_date 								;
	private $rank 									;
	private $active 								;
	private $doc_id 								;
	private $doc_type 							; 	//enum: available: legitymacja szkolna, legitymacja studencka, 
													//dowód osobisty, paszport, karta stałego pobytu, pravo jazdy, 
													//książeczka wojskowa, inne.
	private $type 									; 	//enum : available: ppatrol, sztab, zaufany, czarna lista, 
														//nie dotyczy, zakwalifikowany na finał, dane w systemie fundacyjnym 
														//(zakwalifikowany na finał)
	private $token 									; //token for email address checking
	private $consent_processing_of_personal_data		; 	// zgoda na przetwarzanie danych. Wymagana do rejestracji. 
															//Jedyna możliwa wartość - true
	private $date_consent_processing_of_personal_data	; // zgoda na przetwarzanie danych - data wyrażenia zgody.	
	/*private $processing_of_personal_data_for_marketing_purposes  ;  //zgoda na przetwarzanie danych w celach marketingowych. 
																	//Dopuszczalne wartosci - tak, nie
	private $date_processing_of_personal_data_for_marketing_purposes  ;  //data zmiany statusu wyrażenia zgodny na 
																		//przerwarzanie danych w celach marketingowych
	*/
	private $accept_of_sending_data_to_WOSP							;	//zgoda na przekazanie danych do Fundacji WOSP. 
																		//wymagana do rejestracji w systemie. Jedyna możliwa wartość: true

	private $date_accept_of_sending_data_to_WOSP 					; 	//data wyrażenia powyższej zgody
	private $deleted						=	 0; //is user deleted?
	private $notices 						= array(); //array of notice objects related to this user 
	private $ACL 								= array(
															'self' 				=> 1,
															'view' 				=> 0,
															'notices' 		=> 0,
															'edit' 				=> 0,
															'a_edit' 			=> 0,
															'admin' 			=> 0,
															'superadmin' 	=> 0,
															);
/*
 * Access control list: - role based
 * - self - sees only his data, can change email, phone, and nothing more. Wanna change something more? visit us
 * - view - can browse thru users database without changing anything
 * - notices - can change notices for users (good for police for example)
 * - edit - can edit all user data - new user has to be registered via normal way, then we can change his data
 * - a_edit - can edit articles on site
 * - admin - can change user privileges of any user, can't delete himself, cant ! create new admins
 * - superadmin - me :-) can do anything, even delete myself
 * 
 *  The idea is to serialize this to DB 
 */
															
	/**
	 * 
	 * @param $engine instance of engineClass
	 * @param $data array of data
	 * @return void
	 */
	public function __construct(EngineClass $engine,array $data = array()){ //remember about the flag
		$this->engine = $engine;
		foreach ($data as $var => $val){
			if (array_key_exists($var, $this->getVariables())){
				$this->$var = $val;
				}
			else throw new InvalidVariableNameException($var);
				}
			if(!is_array($this->ACL))
				$this->ACL = unserialize($this->ACL);

	}
	
	/**
	 * 
	 * @param $notices [0/1] whether to load notices or not
	 * @return array of user data
	 */
	public function get($notices=false){
		$vars = get_object_vars($this);
		if ($notices){
			if (!$this->noticesLoadedFlag)
				$this->notices = $this->engine->loadNotices($this->id);
			$this->noticesLoadedFlag = true;
		}
		else $vars['notices'] = 'not loaded';
		unset ($vars['noticesLoadedFlag']);
		unset ($vars['engine']);
		return $vars;
	}

	/**
	 * 
	 * @return array of user notices
	 */
	public function getNotices(){
		if (!$this->noticesLoadedFlag)
			$this->notices = $this->engine->loadNotices($this->id);
		$this->noticesLoadedFlag = true;
		return $this->notices;
	}
	
	/**
	 * check user rights
	 * @param $role_name
	 * @return boolean
	 */
	public function ACL_check($role_name){
		if ($this->ACL['superadmin']==1) return 1;
		elseif (($this->ACL['admin']==1) && ($role_name !='superadmin')) return 1;
		elseif ($this->ACL[$role_name]==1) return 1;
		elseif (($this->ACL['edit'] || $this->ACL['notices']) && $role_name=='view') return 1;
		elseif ($this->ACL['edit'] && $role_name=='notices') return 1;
		else return 0;
		//think it can't be done better
	}

	/**
	 * set any declared variable in object
	 * @param $var variable name
	 * @param $val new value
	 */
	public function __set($var, $val){
		if (array_key_exists($var, $this->getVariables())){
			$this->changed_flag = 'updated';
			$this->$var = $val;
		}
		else throw new InvalidVariableNameException($var);
	}
	
	/**
	 * returns desired variable value
	 * @param $var
	 * @return unknown_type
	 */
	public function __get($var){
		if (array_key_exists($var, $this->getVariables()))
			return $this->$var;
		else throw new InvalidVariableNameException($var);		
	}
	
	/**
	 * (non-PHPdoc) PHP sucks so this is workaround
	 * @see class/genericClass#getVariables()
	 */
	protected function getVariables(){
		return get_object_vars($this);
	}
	
}
?>