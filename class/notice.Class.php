<?php
require_once	$_SERVER['DOCUMENT_ROOT'].'/class/generic.Class.php';

/**
 *
 * @author jacek(at)jacoor.net
 *
 */

final class notice extends genericClass implements PHPSucks{
	private $id;
	private $vid;
	private $data;
	private $mid;
	private $m_date;
	private $m_presence;//zmienione w templatach na brak zdjęcia
	private $amount;
	private $valuables;
	private $text_value;
	private $author;
	private $type_of; //rozliczenie, spotkania, policja, nagroda, inne;
	private $ident_nr;
	private $final_nr;
	private $deleted = 0;

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
	 * (non-PHPdoc) PHP sucks, so this is workaround
	 * @see class/genericClass#getVariables()
	 */
	protected function getVariables(){
		return get_object_vars($this);
	}

}
?>