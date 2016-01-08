<?php

namespace JCF\models;

class DataLayerFactory {
	
	public function __construct(){}
	
	public function create($type, $source_settings){
		$class_name = '\\JCF\\models\\' . $type . 'DataLayer';
		return new $class_name($source_settings);
	}
}

