<?php

namespace JCF\models;
use JCF\interfaces\FieldSettings;

class DBDataLayer implements FieldSettings {

	public function get_fields($id = ''){
		$option_name = $this->getFieldName();
		$field_settings = $this->get_options($option_name);

		if(!empty($id)){
			return @$field_settings[$id];
		}

		return $field_settings;
	}

	public function update_fields(){}

	public function get_fieldsets($id = false){
		$option_name = $this->getFieldsetName();
		$fieldsets = $this->get_options($option_name);
		if(!empty($id)){
			return @$fieldsets[$id];
		}

		return $fieldsets;
	}
	public function update_fieldsets(){}
	
	// option name in wp-options table
	public function getFieldName(){
		$post_type = jcf_get_post_type();
		return 'jcf_fields-' . $post_type;
	}

	/**
	 * return db fieldset name
	 * @return string
	 */
	public function getFieldsetName(){
		$post_type = jcf_get_post_type();
		return 'jcf_fieldsets-' . $post_type;
	}
	
	public function getAllData(){
		$post_types = jcf_get_post_types();
		$settings = array();
		$fieldsets = array();
		$field_settings = array();
		$field_options = array();
		foreach($post_types as $key => $value){
			jcf_set_post_type($key);
			$fieldsets[$key] = $this->get_fieldsets();
			$field_settings[$key] = $this->get_fields();
		}

		$settings = array(
			'post_types' => $post_types,
			'fieldsets' => $fieldsets,
			'field_settings' => $field_settings,
		);
		return $settings;
	}
	
	public function get_options($key){
		$jcf_multisite_settings = \JCF\models\Settings::getNetworkMode();
		return $jcf_multisite_settings == 'network' ? get_site_option($key, array()) : get_option($key, array());
	}
	
	/**
	 * return number of registered fields and fieldsets for specific post type
	 * @param string $post_type
	 * @return int
	 */
	public function countFields($post_type){
		$fieldsets = $this->get_options('jcf_fieldsets-'.$post_type);

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

