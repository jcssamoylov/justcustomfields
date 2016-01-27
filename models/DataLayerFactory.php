<?php

namespace jcf\models;
use jcf\models;

class DataLayerFactory {
	
	protected $_id;
	
	public function __construct(){
		$source = models\Settings::getDataSourceType();
		$this->_id = $source == models\Settings::CONF_SOURCE_DB ? 'DB' : 'Files';
	}
	
	public function create($id = FALSE){
		$class_name = '\\jcf\\models\\' . (!empty($id) ? $id : $this->_id) . 'DataLayer';
		return new $class_name();
	}
}

