<?php

namespace jcf\models;

class Fieldset {
	
	protected $layer;

	public function __construct(){

		$source = \jcf\models\Settings::getDataSourceType();
		$this->layer = new DataLayerFactory();
		$this->layer = $this->layer->create('DB', $source);
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

