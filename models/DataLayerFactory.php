<?php

namespace jcf\models;

class DataLayerFactory {
	
	public function __construct(){}
	
	public function create($type){
		$class_name = 'jcf\\models\\' . $type . 'DataLayer';
		return new $class_name();
	}
}

