<?php
require_once	$_SERVER['DOCUMENT_ROOT'].'/class/generic.Class.php';
/**
 * meeting.class
 * @author jacek(at)jacoor.net
 *
 */
final class meeting extends genericClass implements PHPSucks{
	private $id;
	private $date;
	private $time;
	private $persons_limit 		=	30 ;
	private $r_amount = 0;
	private $deleted = 0;
	private $active = 0;
	private $place = '';
	protected $engine;
	
	/**
	 * 
	 * @param $engine instance of engineClass
	 * @param $data array of data
	 * @return void
	 */
	public function __construct(EngineClass $engine,array $data = array()){ //remember about the flag
		$this->engine = $engine;
		foreach ($data as $var => $val){
			if (array_key_exists($var,$this->getVariables())){
				$this->$var = $val;
			}
				else throw new InvalidVariableNameException($var);
			}
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