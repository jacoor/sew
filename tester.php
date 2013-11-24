Plik testowy.
<br />
<?php/*
require_once 'class/engine.Class.php';
require_once 'class/volunteer.Class.php';
require_once 'class/meeting.Class.php';
require_once 'class/notice.Class.php';
require_once 'class/sqlManager.Class.php';

$engine =  EngineClass::GetInstance();

$m = new meeting($engine);
var_dump($m->get());
$v = new volunteer($engine,array(
	'name' =>'me'
)); //odrzuca - pewnie przez brak wymaganych danych
//$v->ACL['superadmin'] = 1; - nie da rady tak użyć metody magicznej 
//var_dump($v);/$ACL = $v->ACL;
//$ACL['admin'] = 1;
//var_dump($ACL);
//$v->ACL = $ACL; //ale przecież nie będzie sytuacji w której obiekt podczas jednej iteracji jest tworzony i modyfikowany
//$v->name = 'ja';
var_dump($v->name);
var_dump($v->ACL_check('admin'));
echo "<h2>wolontarius</h2>";
var_dump($v);

echo '<h3> test notice</h3>';
$n = new notice($engine, array(
	'vid' =>'1'
));
var_dump($n->get());
var_dump($n->vid);
var_dump($n);

echo '<h3>zakomentowany test sqlManagiera</h3>';

//$sql = sqlManager::GetInstance();
//var_dump($sql);



//var_dump($sql->insertRow('notices',array('id'=>500, 'vid'=>1)));

//var_dump($sql->insertInto('notices', array('id' =>1, 'vid'=>1,'type_of'=>'rozliczenie', 'author'=>'jacoor'), 'iiss'));
 * 
 */
?>
