<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/configs/config.php';
require_once("Exceptions_handler_lib.php");

/**
 * sql manager class
 * @author jacek(at)jacoor.net
 *
 */
final class sqlManager extends mysqli { //don't suppose that i have to extend this
	private static $instance;
	
	public static function GetInstance()
	{
		if ( !isset(self::$instance))
		{
			self::$instance = new sqlManager();
		}
		return self::$instance;
	}

	/**
	 * private constructor
	 *
	 */
private function __construct(){		
	//this is singletone :-)
	self::I_hate_fucking_magic_quotes(); //clear magic quotes	
	try{
		@parent::__construct(config::hostname(),config::username(),config::pass(), config::dbname(), config::port());
		
		if (mysqli_connect_errno()!==0) 
			throw new Exception(mysqli_connect_error(), mysqli_connect_errno());
		
		$result=@$this->query("SET NAMES 'utf8'");
		$result=@$this->set_charset("utf8");
		if ($result===FALSE) 
			{
			$error = $this->error;
			$errcode = $this->errno;
			$this->close();	
			throw new Exception($error, $errcode);
			}			
		}
		catch (Exception $dberror)
		{
			throw new MySQLIConnectException($dberror->getMessage(),$dberror->getCode());
		}
	}

	/**
	 * fetch rows with desired criteria
	 * @param $tableName
	 * @param $search associative array with data to search (key => val)
	 * @param $get indexed array of columns name to get , if null all are fetched
	 * @param $order order of records to return (withoud 'order by' part)
	 * @return associative of fetched row(s)
	 * @todo implement limit
	 */
	public function getRows ($tableName, $search = null, $get = null, $order = null) {
		if ($tableName===null)
			trigger_error("Niepoprawna wartość tableName", E_USER_ERROR);
		if (is_array($search)){		
			$where = "WHERE ";
	
			foreach ($search as $key => $val){
				$where = $where . addslashes($key) .' = \''.addslashes($val).'\' AND ';  
			}
		}
		if ($get === null) 
			$get = ' * ';
		else{
			foreach ($get as $key => &$val)
				$get[$key] = addslashes($val);
			$get  = implode(array_values($get),',');
		} 
		
		if ($order != null ) $order = "ORDER BY ".addslashes($order);
		
		$where = substr($where,0,-5);//remove last char
		$query  = "
							SELECT $get FROM `$tableName`
							$where 
							$order
						;";
		$res = $this->query($query);
		if (!$this->error){
			while ($row = $res->fetch_assoc())
				$result[] = $row;
		}	
	else trigger_error("Bład zapytania mysqli" .$this->error , E_USER_ERROR); 
	return ($result);
	}
	 
	/**
	 * inserts single row to table
	 * @param $tableName
	 * @param $fields array of fields to be inserted $key => $val
	 * @return boolean 
	 * @exception throws some exception on mysqli error
	 */
	public function insertRow ($tableName, array $fields){
		if ($tableName === null) trigger_error("Niepoprawna wartość tableName", E_USER_ERROR);
		if (!is_array($fields)) trigger_error("Niepoprawna wartość fields", E_USER_ERROR);
			$cols = implode(',', array_keys($fields));
		foreach ($fields as $key => &$val){
			$fields[$key] = addslashes($val);
		}
		$vals = implode($fields,'\',\'');
		$vals = '\''.$vals.'\'';
		$query  = "
							INSERT INTO `$tableName`
							($cols) 
							VALUES($vals);";
		
		$this->query($query);
		if ($this->error)
			trigger_error("Bład zapytania mysqli" .$this->error , E_USER_ERROR);
	return $this->insert_id;
	}
	
	/**
	 * fetch rows with desired criteria
	 * @param $tableName
	 * @param $update associative array with data to update (key => val)
	 * @param $search associative array with data to search (key => val)
	 * @return mysqli_affected_rows
	 */
	public function updateRow ($tableName, array $update, array $search) {
		if ($tableName===null) trigger_error("Niepoprawna wartość tableName", E_USER_ERROR);
		if (!is_array($search)) trigger_error("Niepoprawna wartość search", E_USER_ERROR);
		if (!is_array($update)) trigger_error("Niepoprawna wartość update", E_USER_ERROR);	
			
		$set = "SET ";	
		foreach ($update as $key => $val)
			$set = $set . addslashes($key) ." = '".addslashes($val)."', ";
		
		$set = substr($set,0,-2);//remove last char
		
		$where = "WHERE ";	
		foreach ($search as $key => $val)
			$where = $where . addslashes($key) ." = '".addslashes($val)."' AND ";  
	
		$where = substr($where,0,-5);//remove last char  
	
		$query  = "
							UPDATE `$tableName` 
							$set 
							$where
						;";	

		$this->query($query);
	
		if ($this->error)
			trigger_error("Bład zapytania mysqli" .$this->error , E_USER_ERROR);
		return $this->affected_rows;
	}	

	/**
	 * deletes rows with desired criteria. Does not allow to delete all.
	 * @param $tableName
	 * @param $search associative array with data to search (key => val) 
	 * @return myqli_affected_rows
	 * @exception MysqliQueryException
	 */
	public function deleteRows ($tableName, $search) {
		if ($tableName===null) trigger_error("Niepoprawna wartość tableName", E_USER_ERROR);
		if (!is_array($search)) trigger_error("Niepoprawna wartość search", E_USER_ERROR);
		$where = "WHERE ";
	
		foreach ($search as $key => $val){
			$where = $where . addslashes($key) .' = '.addslashes($val).' AND ';  
		}
	
		$where = substr($where,0,-5);//remove last char
		$query  = "
							DELETE FROM `$tableName`
							$where
						;";	
			$this->query($query);
		if ($this->error)
			trigger_error("Bład zapytania mysqli" .$this->error , E_USER_ERROR);
	return $this->affected_rows;
	}

	/**
	 * I really hate magic_quotes_gpc - this stuff reverts changes of magic quotes
	 * @return unknown_type
	 */
	private static function I_hate_fucking_magic_quotes(){
		if (ini_get ('magic_quotes_sybase') != false){
			die('Yo! I will not work untill you disable magic_quotes_sybase in your php.ini');
		}
		if (get_magic_quotes_gpc()){
			array_walk_recursive($_REQUEST, 'sqlManager::clearslashes');
			array_walk_recursive($_POST, 'sqlManager::clearslashes');
			array_walk_recursive($_GET, 'sqlManager::clearslashes');
			array_walk_recursive($_COOKIE, 'sqlManager::clearslashes');
		}
	} 
	/**
	* clears slashes from magic quotes
	* @param $input
	* @return modified $input
	*/
	private static function clearslashes(&$input){
   	$input = stripslashes($input);
  	return true;
 	}
} 
?>