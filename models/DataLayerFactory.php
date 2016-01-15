<?php

namespace jcf\models;
use jcf\models;

class DataLayerFactory {
	
	protected $id;
	
	public function __construct(){
		$source = models\Settings::getDataSourceType();
		$this->id = $source == models\Settings::JCF_CONF_SOURCE_DB ? 'DB' : 'Files';
	}
	
	public function create(){
		$class_name = '\\jcf\\models\\' . $this->id . 'DataLayer';
		return new $class_name();
	}
}

