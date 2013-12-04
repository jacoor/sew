<?php
/*
 * display management
 */
require_once	$_SERVER['DOCUMENT_ROOT'].'/class/engine.Class.php';
require_once	$_SERVER['DOCUMENT_ROOT'].'/class/validator.class.php';
require_once	$_SERVER['DOCUMENT_ROOT'].'/pear/smarty/Smarty.class.php';
require_once    $_SERVER['DOCUMENT_ROOT'].'/configs/config.php';
require_once	$_SERVER['DOCUMENT_ROOT'].'/pear/HTTP.php';


class displayManager extends smarty{
	public $engine;
	private $site_root;
	private $user = false;

	public function __construct(){
		parent::Smarty();
		$this->engine = EngineClass::GetInstance();
		$this->template_dir  	 		= 	$_SERVER['DOCUMENT_ROOT'].'/templates';
		$this->compile_dir     		= 	$_SERVER['DOCUMENT_ROOT'].'/cache/templates/';
		$this->config_dir      		= 	$_SERVER['DOCUMENT_ROOT'].'/configs';
		$this->cache_dir 					= 	$_SERVER['DOCUMENT_ROOT'].'/cache/templates/';
		$this->protocol = "http";
  		if (isset($_SERVER['HTTPS']) && !strcasecmp($_SERVER['HTTPS'], 'on')) {
			$this->protocol = 'https';
  		}
		$this->site_root= $this->protocol.'://' .
		($_SERVER["HTTP_HOST"]
			?
		$_SERVER["HTTP_HOST"]
			:
		$_SERVER["SERVER_NAME"]
		);

		$this->assign('site_root', $this->site_root);
		$this->assign('finalNr',$this->engine->session->finalNr ? $this->engine->session->finalNr : config::finalNr());
		if ($this->engine->session->isLoggedIn()){
			$this->user = $this->engine->session->getUser();
			$this->assign_by_ref('user', $this->user);
		}
	}

	/**
	 * displays default template
	 * @param $login_error
	 * @return void
	 */
	public function default_action($login_error=false){
		if ($this->engine->session->isLoggedIn()){
			$this->assign_by_ref('user', $this->user);
			$notices = $this->user->getNotices();
			if ($notices) {
				foreach ($notices as $k => $n){
					if ($n->type_of=="spotkanie"){
						$date = explode('-',$n->m_date);
						if ($date[0]== date('Y')){
							$this->assign_by_ref('meeting',$this->engine->loadMeeting($n->mid));
						}
						elseif ($date[0] < date('Y')) unset ($notices[$k]); //poprzednie lata
					}else unset ($notices[$k]);//czyszczenei innych
				}
			}
			if (strtotime(config::photo_deadline())>=time()){
				if ($_FILES['fields']['name']['photo']){
					if($_POST['token'] == sha1($this->user->id.$this->user->password)){
						$photo = $this->save_photo(sha1($this->user->PESEL).'.jpg');
						if ($photo['saved'] && $_FILES['fields']['name']['photo']){
							$this->user->photo = sha1($this->user->PESEL).'.jpg';
							HTTP::redirect('/');					
						}else if($_FILES['fields']['name']['photo']){
							$correct= false;
							$error_fields['photo'] = true;
							$this->assign('file_error', $photo['error']);
						}
					}else{
						$this->display('security_alert.html');
						session_destroy();
						die;
					}
				}
			}
			$this->assign('photo_deadline', config::photo_deadline());
			$this->assign('photo_deadline_timestamp',strtotime(config::photo_deadline()));
			$this->assign('security_token',sha1($this->user->id.$this->user->password));
			$this->display('account.html');
		}else{
			if ($login_error){
					$this->assign('login_error','<h2>logowanie niepoprawne</h2>');
				}
			$this->assign('photo_deadline', config::photo_deadline());
			$this->display('index.html');
		}
	}
	/*
	 * security check
	 * @param $privilege from volunteer->ACL
	 * @return unknown_type
	 */
	private function secure($privilege){
		if (!$this->user->ACL_check($privilege)){
			$this->display('security_alert.html');
			session_destroy();
			die;
		}

	}
	/**
	 * ajax for loading meeting dates
	 * @return unknown_type
	 */
	public function ajax_m_date(){
		$this->secure('self');
		$meetings = $this->engine->loadMeetings();
		foreach ($meetings as $key => $val){
			if ($val->date < date('Y-m-d', strtotime('+ 1 day')) || $val->r_amount >= $val->persons_limit)
				unset($meetings[$key]);
		}
		reset($meetings);
		$this->assign('key',key($meetings));
		$this->assign_by_ref('meetings', $meetings);
		$this->display('ajax/m_date_form.html');
	}

	/**
	 * ajax for loading meeting times
	 * @return unknown_type
	 */
	public function ajax_m_time($data){
		$this->secure('self');
		$meetings = $this->engine->loadMeetings();
		foreach ($meetings as $key => $val){
			if ($val->date != $data['date'] || $val->r_amount >= $val->persons_limit)
				unset($meetings[$key]);
		}
		$this->assign_by_ref('meetings', $meetings); //będzie ajaxem
		$this->display('ajax/m_time.html');
	}

	public function add_volunteer_to_meeting($data){
		$this->secure('self');
		$meeting =  $this->engine->loadMeeting($data['m_id']);
		$notice = $this->engine->loadNoticeRelatedToMeetingAndVolunteer($meeting->id, $this->user->id);
		if (!$notice){
			$meeting->r_amount = $meeting->r_amount+1;
			$array = array(
										'vid' 						=> 		$this->user->id,
										'type_of' 				=> 		'spotkanie',
										'mid' 						=> 		$meeting->id,
										'm_date' 					=> 		$meeting->date.' '.$meeting->time,
										'author' 					=> 		'self',
										'changed_flag' 		=>	 	'created'
										);
			$notice  = new notice($this->engine, $array);
			$this->assign_by_ref('meeting', $meeting);
			$msg = $this->fetch('emails/mail_confirm_meeting.html');
			$res = $this->engine->sendMail($msg,$msg,'Wiadomość z Systemu Ewidencji Wolontariuszy Wrocławskiego Sztabu WOŚP',$this->user->email);
			$this->display('confirm_meeting.html');
		}else{
			$this->display('already_registered_for_meeting.html');
		}
	}



	/**
	 * logs user
	 * @param $data
	 * @return unknown_type
	 */
	public function login($data){
		$this->engine->session->login($data['fields']['username'], $data['fields']['password']);
		HTTP::redirect('/');
	}

	public function logout(){
		$this->engine->session->logOut();
		$this->default_action();
	}
	/**
	 * Resizes the photo to dimensions set in config
	 * @return file name/false
	 */
	private function save_photo($filename){
		//photo upload stuff
		//validate file
		if ($_FILES['fields']['name']['photo']){
			if ($_FILES['fields']['type']['photo'] == config::required_photo_type() && $_FILES['fields']['size']['photo'] <= config::photo_max_size()){
				//var_dump($_FILES); die();
				//resize 
				$imageprops = getimagesize($_FILES['fields']['tmp_name']['photo']);
				//$image = new Imagick( $_FILES['fields']['tmp_name']['photo'] );
				
				if ($imageprops[0] < config::photo_width() || $imageprops[1] < config::photo_height()) {
					$error = 'Zbyt mały rozmiar zdjęcia. Wymagany rozmiar: 800 na 800 px';
					$saved = false;
				} else {
					$image = imagecreatefromjpeg($_FILES['fields']['tmp_name']['photo']);
					$img_width = $imageprops[0];
					$img_height = $imageprops[1];
					if ($img_width < $img_height){
						//wysokosc wieksza od szerokosci
						//skalujemy po mniejszym - szerokosci
						$temp_gdim = imagecreatetruecolor( config::photo_width(), config::photo_height());
						//to tylko resize, jeszcze crop
						//var_dump( (int)round(($img_height-$img_width)/2)); die();
						$copied = imagecopyresized($temp_gdim, $image, 0, 0, 0, (int)round(($img_height-$img_width)/2), config::photo_width(), config::photo_height(), $img_width, $img_width);
						$saved = imagejpeg($temp_gdim, $_SERVER['DOCUMENT_ROOT'].config::photo_save_path().$filename, 90);
						imagedestroy($temp_gdim);
						imagedestroy($image);
					}else{
						//szerokosc wieksza od wysokosci
						$temp_gdim = imagecreatetruecolor( config::photo_width(), config::photo_height());
						//to tylko resize, jeszcze crop
						$copied = imagecopyresized($temp_gdim, $image, 0, 0, (int)round(($img_width-$img_height)/2), 0, config::photo_width(), config::photo_height(), $img_height, $img_height);
						$saved = imagejpeg($temp_gdim, $_SERVER['DOCUMENT_ROOT'].config::photo_save_path().$filename, 90);
						imagedestroy($temp_gdim);
						imagedestroy($image);
					}
					//$image->cropThumbnailImage(config::photo_width(),config::photo_height());
					//$saved = $image->writeImage($_SERVER['DOCUMENT_ROOT'].config::photo_save_path().$filename);
				}
			}else{
				$saved = false;
				$error = 'Nieprawidłowe zdjęcie. Zdjęcie musi być w formacie jpg, o rozmiarach conajmniej 800 x 800px oraz maksymalnym rozmiarze 2 MB';
			}
			return  array(
				'file' => $filename,
				'saved' => $saved,
				'error' => $error
			);
		}
		return  array(
			'saved' => 1,
		);
	}
	
	/**
	 * registers new volunteer
	 * @param $data volunteer data
	 * @return void
	 */
	public function register($data){
		//not logged in - no security check
		$required = array(
								'name',
								'surname',
								'email',
								'h_city',
								'h_zip',
								'h_street',
								'h_building',
								//'h_loc',
								'school_name',
								//'school_street',
								//'school_building',
								//'school_loc',
								//'school_city',
								//'school_zip',
								'birth_date',
								'PESEL',
								'phone',
								'doc_type',
								'doc_id',
								'login',
								'password',
								'password_repeat',
								'consent_processing_of_personal_data',
								'accept_of_sending_data_to_WOSP',
								);
		$allowed_doc_type = array(
												'legitymacja szkolna',
												'legitymacja studencka',
												'dowód osobisty',
												'paszport',
												'karta stałego pobytu',
												'prawo jazdy',
												'książeczka wojskowa',
												'inne'
												);
		$correct = true;
		foreach ($data as $key => &$val){
			$data[$key] = trim($val);
		}
		unset($key);// ale php jest zjebane....
		unset($val);

		foreach ($required as $val){
			if (!isset($data[$val])||$data[$val]==''){
				$correct = false;
				$error_fields[$val] = true;
			}
		}

		if (!validator::check_pesel($data['PESEL'])){
			$error_fields['PESEL'] = true;
			$correct = false;
		}

		if (!validator::check_phone($data['phone'])){
			$error_fields['phone'] = true;
			$correct = false;
		}

		$date_for_checking = explode('-',$data['birth_date']);
		if (!checkdate((int)$date_for_checking[1], (int)$date_for_checking[2], (int)$date_for_checking[0])){
			$error_fields['birth_date'] = true;
			$correct = false;
		}
		//parents phone number only for underage people
		if (!$error_fields['birth_date']){
			if ((int)$date_for_checking[0] > (int)date('Y',strtotime('-18 years'))){
				if (!validator::check_phone($data['p_phone'])){
					$error_fields['p_phone'] = true;
					$correct = false;
				}
			}
		}

		if (!validator::check_onlyalphabetical($data['name'])){
			$error_fields['name'] = true;
			$correct = false;
		}

		if (!validator::check_email($data['email'])){
			$error_fields['email'] = true;
			$correct = false;
		}

		if (!validator::check_onlyalphabetical($data['surname'])){
			$error_fields['surname'] = true;
			$correct = false;
		}
		
		if (!validator::check_zip($data['h_zip'])){
			$error_fields['h_zip'] = true;
			$correct = false;
		}

		if (!empty($data['school_zip']) && !validator::check_zip($data['school_zip'])){
			$error_fields['school_zip'] = true;
			$correct = false;
		}

		if (array_search($data['doc_type'], $allowed_doc_type)===false){
			$error_fields['doc_type'] = true;
			$correct = false;
		}

		if ($data['password']!==$data['password_repeat']){
			$this->assign('pass_error','<p class="error"><strong>Wartości pól "hasło" oraz "potwierdź hasło" są różne. Wprowadź hasła ponownie.</strong></p>');
			$error_fields['password'] = true;
			$error_fields['password_repeat'] = true;
			$correct = false;
		}

		if (validator::check_onlyalphabetical($data['password'] || strlen($data['password'])<6)){
			$this->assign('pass_error','<p class="error">Wprowadzone hasło jest niepoprawne. Hasło musi być dłuższe niż 6 znaków i posiadać przynajmniej jedną cyfrę.</p>');
		}
		

		//check if user with thist data already exist

		if ($correct) { //not to kill database
			$ar = $this->engine->check_if_object_exists($data, 'volunteer');
			if (is_array($ar)) {
				unset ($val);
				unset ($key);
				$correct= false;
				$errors = array(
												'login' => 'login',
												'PESEL' => 'numer PESEL',
												'email' => 'adres e-mail',
												);
				foreach ($ar as $key => $val) {
					$error_fields[$key] = true;
					$this->assign($key.'_unique_error','<p class="error">Ten '.$errors[$key].' już istnieje w naszej bazie. Prawdopodobnie jesteś już zarejestrowany/a więc skorzystaj z formularza logowania (jeśli nie pamiętasz hasła to zostanie wygenerowane nowe). Jeśli mimo wszytko masz problemy - skontaktuj się z nami.</p>');
				}
			}
		}
		
		if ($correct && strtotime(config::photo_deadline())>=time()){
			$photo = $this->save_photo(sha1($data['PESEL']).'.jpg');
			if ($photo['saved'] && $_FILES['fields']['name']['photo']){
				$data['photo'] = sha1($data['PESEL']).'.jpg';
			}else if($_FILES['fields']['name']['photo']){
				$correct= false;
				$error_fields['photo'] = true;
				$this->assign('file_error', $photo['error']);
			}
		}
		if($correct){
			//create new volunteer
			unset($data['password_repeat']);
			$activation = sha1(time().$data['name'].$data['surname'].$data['email'].$data['PESEL']);
			$activation_link = "<a href=\"$this->site_root?action=activate&link=$activation\">$this->site_root?action=activate&link=$activation</a>";
			$this->assign('user',$data['name'] .' '.$data['surname']);
			$this->assign('activation_link',$activation_link);
			$msg = $this->fetch('emails/mail_confirm.html');
			$res = $this->engine->sendMail($msg,$msg,'Wiadomość z Systemu Ewidencji Wolontariuszy Wrocławskiego Sztabu WOŚP',$data['email']);
			if ($res){
				unset($key);
				unset($val);
				foreach ($required as $key => $val){
					if (!array_key_exists($val, $data))
						unset ($data[$val]);
				}
				$data['changed_flag'] = 'created';
				$data['token'] = $activation;
				$data['password'] = sha1($data['password']);
				new volunteer($this->engine,$data);
			}
			$this->display('account_added.html');
		}
		else{
			$error_fields['password'] = true;
			$error_fields['password_repeat'] = true;
			$this->assign_by_ref('error_fields',$error_fields);
			$this->assign('register_error','<h2>Nie wszystkie pola formularza zostały wypełnione prawidłowo. Popraw błędy i spróbuj jeszcze raz.</h2>');
			$this->display('index.html');
		}
	}

	/**
	 * activates account
	 * @param $data
	 * @return void
	 */
	public function activate($data){
		$correct = true;
		if (!isset($data['link'])){
			$this->assign('error_msg','<p class="error">Niepoprawny link.</p>');
			$correct = false;
		}
		if ($correct){
			$user =  $this->engine->loadVolunteers(array('token'=>$data['link'],'active'=>0));
			if (is_array($user)){
			$user = $user[0];
			$user->active = 1;
			}else{
				$correct = false;
				$this->assign('error_msg','<p class="error">Nie ma użytkownika do aktywowania.<br/>
				Być może Twoje konto już jest aktywne lub zostało usunięte lub też link aktywujący jest niepoprawny. <br />
				Spróbuj ponownie wejść na tą stronę poprzez kliknięcie linka w wiadomości e-mail. <br/>
				Jeśli to nie pomoże, porównaj adres  bieżącej strony z linkiem w wiadomości e-mail(link mógł zostać zniekształcony).<br>
				Jeśli dalej nie możesz aktywować swojego konta - spróbuj się zalogować, być może konto już jest aktywne. Jeśli nie pamiętasz hasła - zostanie wygenerowane nowe.<br />
				Jeśli żaden z powyższych sposobów nie pomógł - skontaktuj się z nami.
				</p>');
			}
		}
		$this->display('account_active.html');
	}

	/**
	 * list all volunteers
	 * @return void
	 */
	public function volunteer_list(){
		$this->secure('view');
		$volunteers = $this->engine->loadVolunteers(null, 
														array(
															'surname', 
															'name', 
															'PESEL', 
															'login',
															'type',
															'id',
															'photo',
															'statement',
															'statement_downloaded',
															'statement_downloaded_timestamp',
														)
													);
		$idents = array();
		foreach ($volunteers as $v){
			 $idents[$v->id]= $this->engine->getIdentNr($v->id, $finalNr) ? $this->engine->getIdentNr($v->id, $finalNr) : 'nie ma';
		}
		//var_dump($idents); die;
		$this->assign_by_ref('volunteers',$volunteers);
		$this->assign('idents',$idents);
		$this->display('allVolunteers.html');
	}

	/**
	 * loads and displays single volunteer data
	 * @param $id
	 * @return void
	 */
	public function volunteer_view($id){
		$this->secure('view');
		$volunteer = $this->engine->loadVolunteers(array('id'=>$id));
		if ($volunteer){
			$this->assign_by_ref('volunteer',$volunteer[0]);
			$this->assign_by_ref('notices',$this->engine->loadNotices($volunteer[0]->id));
		}
		/*
	    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	    header("Cache-Control: post-check=0, pre-check=0", false);
	    header("Pragma: no-cache");*/
		$this->display('volunteer_account.html');
	}

	/*
	 * shows notice
	 * @param $data
	 * @return unknown_type
	 */
	public function notice_view($data){
		$this->secure('view');
		$this->assign_by_ref('notice', $this->engine->loadNotice($data['nid']));
		$this->assign('ajax',$data['ajax']);
		$this->display('ajax/volunteer_notice_view.html');
	}

	/*
	 * validates notice
	 * @param $data
	 * @return array
	 */
	private function validate_notice(&$data){
		$this->secure('notices');
		$correct=true;
		switch ($data['notice']['type_of']){
			case 'spotkanie':
				$rank_coma = explode(',',$data['volunteer']['rank']);
				$rank_dot = explode('.',$data['volunteer']['rank']);
				if (isset($rank_coma[1]) || isset($rank_dot[1]) || !is_numeric($data['volunteer']['rank']) || (int)$data['volunteer']['rank']<1 || (int)$data['volunteer']['rank']>5 ){
					$correct = false;
					$error_fields['volunteer']['rank']=true;
				}
				unset($data['notice']['amount']);
				unset($data['notice']['valuables']);
				unset($data['notice']['ident_nr']);
				unset($data['notice']['final_nr']);
				break;
			case 'rozliczenie' :
				if ($data['notice']['amount']==0 || trim($data['notice']['amount']=='' || !is_numeric($data['notice']['amount']))){
					$correct = false;
					$error_fields['notice']['amount']=true;
				}
				if ($data['notice']['final_nr']==0 || !is_numeric($data['notice']['final_nr'])){
					$correct = false;
					$error_fields['notice']['final_nr']=true;
				}
				unset ($data['volunteer']['rank']);
				unset ($data['notice']['m_date']);
				unset ($data['notice']['m_presence']);
				unset($data['notice']['ident_nr']);
				break;
			case 'numer identyfikatora':
				if (!is_numeric($data['notice']['ident_nr'])){
					$correct=false;
					$error_fields['notice']['ident_nr']=true;
				}
				if ($data['notice']['final_nr']==0 || !is_numeric($data['notice']['final_nr'])){
					$correct = false;
					$error_fields['notice']['final_nr']=true;
				}
				unset ($data['volunteer']['rank']);
				unset ($data['notice']['m_date']);
				unset ($data['notice']['m_presence']);
				break;

			case 'policja' 	:
			case 'nagroda' 	:
			case 'inne'			:
				unset($data['notice']['amount']);
				unset($data['notice']['valuables']);
				unset($data['notice']['ident_nr']);
				unset($data['notice']['final_nr']);
				unset ($data['volunteer']['rank']);
				unset ($data['notice']['m_date']);
				unset ($data['notice']['m_presence']);
				if (trim($data['notice']['text_value']=='')){
					$correct = false;
					$error_fields['notice']['text_value']=true;
				}
				break;
			}
		return array('correct' => $correct, 'error_fields'=>$error_fields);
	}

	/*
	 * updates notice
	 * @param $data
	 * @return unknown_type
	 */
	public function update_notice_form($data){
		$this->secure('notices');
		if ($data['ajax']==1)
			$this->assign('ajax','1');
		if ($data['nid'])
			$notice = $this->engine->loadNotice($data['nid']);
		elseif ($data['notice']){
			$notice = new notice($this->engine, $data['notice']);
			$notice->data = date("Y-m-d H:i:s");
			$notice->changed_flag = 'created';
		}
		else{
			$notice = new notice($this->engine, array('final_nr'=>config::finalNr()));
		}
		$this->assign_by_ref('notice', $notice);
		if ($data['vid']){
			$volunteer = $this->engine->loadVolunteers(array('id'=>$data['vid']));
			$this->assign_by_ref('volunteer',$volunteer[0]);
		}

		if ($data['notice']){
			$validate = $this->validate_notice($data);
			$this->assign('ajax',$data['ajax']);
			$volunteer[0]->rank = $data['volunteer']['rank'];
			if ($data['nid'])
				foreach ($data['notice'] as $key => $val)
					$notice->$key = $val;
			if (!$validate['correct']){
				$this->assign('error_fields',$validate['error_fields']);
				$notice->changed_flag = 'loaded'; //trick to prevent save incorrect data
				$volunteer[0]->changed_flag = 'loaded'; //trick to prevent save incorrect data
				$this->display('ajax/notice_form.html');
				return 0;
			}
			else{
				$this->assign('message','Zdarzenie zmieniono');
				$this->display('ajax/volunteer_notice_view.html');
				return 0;
			}
		}
		$this->display('ajax/notice_form.html');
	}

	public function change_user_data_form($data) {
		$this->secure('edit');
		$volunteer = $this->engine->loadVolunteers(array('id'=>$data['id']));
		$values = $volunteer[0]->get();
		$values['token'] = sha1($values['id'].$this->user->id);
		$this->assign_by_ref('volunteer',$volunteer[0]);
		$this->assign_by_ref('values',$values);
		$this->display('change_user_data_form.html');
	}

	public function update_volunteer_data($data) {
		$this->secure('edit');
		$required = array(
								'name',
								'surname',
								'email',
								'h_city',
								'h_zip',
								'h_street',
								'h_building',
								//'h_loc',
								'school_name',
								//'school_street',
								//'school_building',
								//'school_loc',
								//'school_city',
								//'school_zip',
								'birth_date',
								'PESEL',
								'phone',
								'doc_type',
								'doc_id',
								'active',
								'token',
								);
		$allowed_doc_type = array(
												'legitymacja szkolna',
												'legitymacja studencka',
												'dowód osobisty',
												'paszport',
												'karta stałego pobytu',
												'prawo jazdy',
												'książeczka wojskowa',
												'inne'
												);
		$allowed_type = array (
													'nie dotyczy'=>1,
													'ppatrol'=>1,
													'sztab'=>1,
													'zaufany'=>1,
													'czarna lista'=>1,
													'zakwalifikowany na finał'=>1,
													'dane w systemie fundacyjnym (zakwalifikowany na finał)' => 1,
													);
		$correct = true;
		if ($data['active']!=1) $data['active']=0;
		
		foreach ($data as $key => &$val){
			$data[$key] = trim($val);
		}
		unset($key);// ale php jest zjebane....
		unset($val);

		foreach ($required as $val){
			if (!isset($data[$val])||$data[$val]==''){
				$correct = false;
				$error_fields[$val] = true;
			}
		}

		if (!isset($allowed_type[$data['type']])){
			$correct = false;
			$error_fields['type'] = true;
		}

		if (!validator::check_pesel($data['PESEL'])){
			$error_fields['PESEL'] = true;
			$correct = false;
		}

		if (!validator::check_phone($data['phone'])){
			$error_fields['phone'] = true;
			$correct = false;
		}

		$date_for_checking = explode('-',$data['birth_date']);
		if (!checkdate((int)$date_for_checking[1], (int)$date_for_checking[2], (int)$date_for_checking[0])){
			$error_fields['birth_date'] = true;
			$correct = false;
		}
		//parents phone number only for underage people
		if (!$error_fields['birth_date']){
			if ((int)$date_for_checking[0] > (int)date('Y',strtotime('-18 years'))){
				if (!validator::check_phone($data['p_phone'])){
					$error_fields['p_phone'] = true;
					$correct = false;
				}
			}
		}

		if (!validator::check_onlyalphabetical($data['name'])){
			$error_fields['name'] = true;
			$correct = false;
		}

		if (!validator::check_email($data['email'])){
			$error_fields['email'] = true;
			$correct = false;
		}

		if (!validator::check_onlyalphabetical($data['surname'])){
			$error_fields['surname'] = true;
			$correct = false;
		}
		
		if (!validator::check_zip($data['h_zip'])){
			$error_fields['h_zip'] = true;
			$correct = false;
		}

		if (!empty($data['school_zip']) && !validator::check_zip($data['school_zip'])){
			$error_fields['school_zip'] = true;
			$correct = false;
		}

		if (array_search($data['doc_type'], $allowed_doc_type)===false){
			$error_fields['doc_type'] = true;
			$correct = false;
		}

		//check if user with this data already exist

		if ($correct) { //not to kill database
			$ar = $this->engine->check_if_object_exists($data, 'volunteer');
			unset ($val);
			unset ($key);
			$errors = array(
											'login' => 'login',
											'PESEL' => 'numer PESEL',
											'email' => 'adres e-mail',
											);
			foreach ($ar as $key => $val) {
				if ($val != $data['id']){
					$correct= false;
					$error_fields[$key] = true;
					$this->assign($key.'_unique_error','<p class="error">Ten '.$errors[$key].' już istnieje w naszej bazie i jest przypisany do innego wolontariusza.</p>');
				}
			}
		}
		//secure check
		#echo ('<pre>');
		#var_dump(new volunteer($this->engine,$data));die();
		$volunteer = $this->engine->loadVolunteers(array('id'=>$data['id']));
		$volunteerObj =  $volunteer;
		$volunteer = $volunteer[0]->get();
		$token = sha1($volunteer['id'].$this->user->id);
		if ($token !== $data['token'] ){
			$this->display('security_alert.html');
			session_destroy();
			die;
		}
			
		if ($correct){ //here you can always edit
			$photo = $this->save_photo(sha1($data['PESEL']).'.jpg');
			if ($photo['saved'] && $_FILES['fields']['name']['photo']){
				$data['photo'] = sha1($data['PESEL']).'.jpg';
			}else if($_FILES['fields']['name']['photo']){
				$correct= false;
				$error_fields['photo'] = true;
				$this->assign('file_error', $photo['error']);
			}
		}
		if($correct){
			//create new volunteer
			unset($key);
			unset($val);
			foreach ($required as $key => $val)
				if (!array_key_exists($val, $data))
					unset ($data[$val]);
			$data['changed_flag'] = 'updated';
				if (!$data['ACL']){
			 		$ACL = $this->engine->loadVolunteers(array('id'=> $data['id']), array('ACL','id'));
			 		$data['ACL'] = $ACL[0]->ACL;
				}
			if ($this->user->ACL_check('admin')){
				if($data['active']!=1){
					$data['active']==0;
				}
			}

			new volunteer($this->engine,$data);
			HTTP::redirect('?action=volunteer_view&id='.$data['id']);
		}else{
			$this->assign_by_ref('error_fields',$error_fields);
			$this->assign_by_ref('volunteer',$volunteerObj[0]);
			$this->assign('update_error','Nie wszystkie pola formularza zostały wypełnione prawidłowo. Popraw błędy i spróbuj jeszcze raz.');
			$this->display('change_user_data_form.html');
		}
	}

	/**
	 * list all meetings
	 * @return void
	 */
	public function meetings(){
		$this->secure('admin');
		$meetings = $this->engine->loadMeetings(null, null);
		$next_day = date('Y/m/d', strtotime('+ 1 day'));
		//echo ('<pre>');
		//var_dump($meetings[0]->persons_limit); die;
		$this->assign_by_ref('next_day',$next_day);
		$this->assign_by_ref('meetings',$meetings);
		$this->display('meetings.html');
	}
}
?>