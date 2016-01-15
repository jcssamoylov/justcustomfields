<?php

namespace jcf\models;
use jcf\models;

class Fieldset extends models\MainModel  {
	
	protected $layer;

	public function __construct(){
		parent::__construct();
		$layer_factory = new DataLayerFactory();
		$this->layer = $layer_factory->create();
	}

	/**
	 * return number of registered fields and fieldsets for specific post type
	 * @param string $post_type
	 * @return int
	 */
	public function countFields($post_type){
		$all_settings = $this->layer->getAllData();
		if(isset($all_settings['fieldsets'][$post_type])){
			$fieldsets = $all_settings['fieldsets'][$post_type];
		} else {
			$fieldsets = array();
		}
		
		if(!empty($fieldsets)){
			$count['fieldsets'] = count($fieldsets);
			$count['fields'] = 0;
			foreach($fieldsets as $fieldset){
				if(!empty($fieldset['fields'])){
					$count['fields'] += count($fieldset['fields']);
				}
			}
		}
		else{
			$count = array('fieldsets' => 0, 'fields' => 0);
		}
		return $count;
	}
}

