<?php

namespace JCF\controllers;
use JCF\models\DataLayerFactory;
use JCF\models\Fieldset;

class FieldsetController {
	protected $_dataLayer;
	protected $_model;

	public function __construct($source_settings){
		$layer_type = $source_settings == JCF_CONF_SOURCE_DB ? '\JCF\models\DBDataLayer' : '\JCF\models\FilesDataLayer';
		$layer_factory = new DataLayerFactory();
		$this->_dataLayer = $layer_factory->create($layer_type);
		
		$this->_model = new Fieldset();
	}
	
	public function findAll(){
		return $fieldsets = $this->_dataLayer->get_fieldsets();
	}

}


