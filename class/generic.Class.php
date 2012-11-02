<?php
require_once	$_SERVER['DOCUMENT_ROOT'].'/class/Exceptions_handler_lib.php';
require_once	$_SERVER['DOCUMENT_ROOT'].'/class/phpSucksInterface.php';
//@todo implement validations for child classes
/**
 * 
 * @author jacek(at)jacoor.net
 *
 */
class genericClass{
	protected $engine;
	protected $changed_flag				= 'loaded'; //loaded is safets, options: created, loaded, updated, deleted, wiped, restored. When created object will save itself in DB

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
	 * 
	 * @param $values array of new values. Old values with names equal to keys of this table will be overwritten;
	 * @return changed_flag
	 */
	public function modify(array $values = array()){
		return $this->changed_flag = 'updated';
		//@todo complete
	}
	
	/**
	 * 
	 * @return array with object data
	 */
	public function get(){
		$ar = $this->getVariables();
		unset($ar['engine']);
		return $ar;
	}
	
	public function __destruct(){
		switch ($this->changed_flag){
			case 'updated':
					$this->engine->update($this);
				break;
			case 'created':
					$this->engine->save($this);
				break;
			case 'deleted':
					$this->engine->delete($this);
				break;
			case 'restored':
					$this->engine->restore($this);
				break;
			case 'wiped':
					$this->engine->wipe($this);
				break;			
		}
	}
	
	protected function getVariables(){
		return get_object_vars($this);
	}
}
?>