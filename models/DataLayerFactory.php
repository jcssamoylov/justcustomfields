<?php

namespace jcf\models;
use jcf\models;

class DataLayerFactory {
	
	protected $id;
	
	public function __construct(){
		$source = models\Settings::getDataSourceType();
		$this->id = $source == models\Settings::JCF_CONF_SOURCE_DB ? 'DB' : 'Files';
	}
	
	public function create($id = FALSE){
		$class_name = '\\jcf\\models\\' . (!empty($id) ? $id : $this->id) . 'DataLayer';
		return new $class_name();
	}
}

