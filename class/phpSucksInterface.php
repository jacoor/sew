<?php
 interface PHPSucks{
	public function __set($var, $val);
	public function __get($var);
	public function modify (array $values = array());
 }
?>