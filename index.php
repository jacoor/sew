<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/class/displayManager.Class.php');
//session_start();
//session_destroy(); die;
$smarty = new displayManager();
$action = $_REQUEST['action'];

if ($action != 'register' && $action != 'login' && $action != 'activate' && !$smarty->engine->session->isLoggedIn()){
		$smarty->default_action();
		die;
	}
	
switch ($action){
	case 'login':
		$smarty->login($_REQUEST);
		break;

	case 'register':
		$smarty->register($_REQUEST['fields']);
		break;
		
	case 'activate':
		$smarty->activate($_REQUEST);
		break;
		
	case 'm_date':
		$smarty->ajax_m_date();
		break;
			
	case 'm_time':
		$smarty->ajax_m_time($_REQUEST);
		break;		
		
	case 'add_volunteer_to_meeting':
		$smarty->add_volunteer_to_meeting($_REQUEST);
		break;

	case 'logout':
		$smarty->logout();
		break;	

	case 'volunteer_list':
		$smarty->volunteer_list();
		break;	

	case 'volunteer_view':
		$smarty->volunteer_view($_REQUEST['id']);
		break;
		
	case 'notice_view':
		$smarty->notice_view($_REQUEST);
		break;	

	case 'update_notice_form':
		$smarty->update_notice_form($_REQUEST);
		break;	

	case 'notice_create':
		$smarty->update_notice_form($_REQUEST);
		break;
		
	case 'change_user_data':
		$smarty->change_user_data_form($_REQUEST);
		break;

	case 'update_volunteer_data':
		$smarty->update_volunteer_data($_REQUEST['fields']);
		break;
		
	case 'meetings':
		$smarty->meetings();
		break;
	default: $smarty->default_action();
}

?>
 