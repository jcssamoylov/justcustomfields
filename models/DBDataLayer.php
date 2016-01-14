<?php

namespace jcf\models;
use jcf\interfaces;

class DBDataLayer implements interfaces\FieldSettings {
	
	private $source_settings;
	
	public function __construct()
	{
		$this->source_settings = \jcf\models\Settings::getDataSourceType();
	}
	
	public function get_fields($post_type, $id = FALSE){
		$option_name = $this->getFieldName($post_type);
		$field_settings = $this->get_options($option_name);

		if(!empty($id)){
			return @$field_settings[$id];
		}

		return $field_settings;
	}

	/**
	 *	set fields in wp-options
	 */
	public function update_fields( $post_type, $key, $values = array(), $fieldset_id = '', $collection_id = ''){
		$option_name = $this->getFieldName($post_type);

		$field_settings = $this->get_options($option_name);
		if( $values === NULL && ( isset($field_settings[$key]) || isset($field_settings[$collection_id]['fields'][$key]) ) ){
			if(!empty($collection_id)){
				unset($field_settings[$collection_id]['fields'][$key]);
			}
			else{
				unset($field_settings[$key]);
				
			}
		}

		if( !empty($values) ){
			$field_settings[$key] = $values;
		}
		$this->update_options($option_name, $field_settings);
	}

	public function get_fieldsets($post_type, $id = FALSE){
		$option_name = $this->getFieldsetName($post_type);
		$fieldsets = $this->get_options($option_name);
		if(!empty($id)){
			return @$fieldsets[$id];
		}

		return $fieldsets;
	}

	public function update_fieldsets($post_type, $key, $values = array()){
		$option_name = $this->getFieldsetName($post_type);
		$fieldsets = $this->get_options($option_name);
		if( $values === NULL && isset($fieldsets[$key]) ){
			unset($fieldsets[$key]);
		}

		if( !empty($values) ){
			$fielset_values = $this->applyVisibilitySettings($values, $fieldsets[$key]);
			$fieldsets[$key] = $fielset_values;
		}

		$this->update_options($option_name, $fieldsets);
	}
	
	/**
	 * update order fieldsets
	 * @param array $keys Fieldsets keys
	 */
	public function sort_fieldsets($post_type, $keys = array()){
		$option_name = $this->getFieldsetName($post_type);
		$new_fieldsets = array();

		$fieldsets = $this->get_options($option_name);
		foreach($keys as $key){
			$new_fieldsets[$key] = $fieldsets[$key];
			unset($fieldsets[$key]);
		}
		$this->update_options($option_name, $new_fieldsets);
	}
	
	// option name in wp-options table
	public function getFieldName($post_type){
		return 'jcf_fields-' . $post_type;
	}

	/**
	 * return db fieldset name
	 * @return string
	 */
	public function getFieldsetName($post_type){
		return 'jcf_fieldsets-' . $post_type;
	}
	
	public function getAllData(){
		$post_types = jcf_get_post_types();
		$settings = array();
		$fieldsets = array();
		$field_settings = array();
		$field_options = array();
		
		foreach($post_types as $key => $value){
			$fieldsets[$key] = $this->get_fieldsets($key);
			$field_settings[$key] = $this->get_fields($key);
		}

		$settings = array(
			'post_types' => $post_types,
			'fieldsets' => $fieldsets,
			'field_settings' => $field_settings,
		);
		return $settings;
	}
	
	public function get_options($key){
		$settings = new Settings();
		$multisite_settings = $settings->getNetworkMode();
		return $multisite_settings == JCF_CONF_MS_NETWORK ? get_site_option($key, array()) : get_option($key, array());
	}
	
	/**
	 *	Update options with wp-options
	 *	@param string $key Option name
	 *	@param array $value Values with option name
	 *	@return bollean
	 */
	public function update_options($key, $value){
		$settings = new Settings();
		$multisite_settings = $settings->getNetworkMode();
		$multisite_settings == JCF_CONF_MS_NETWORK ? update_site_option($key, $value) : update_option($key, $value);
		return true;
	}
	
	/**
	 * return number of registered fields and fieldsets for specific post type
	 * @param string $post_type
	 * @return int
	 */
	public function countFields($post_type, $source = false){
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
	
	/**
	 * apply viibility settings for fieldsets
	 * @param array $values Visibility values
	 * @return array Fieldsets visibility values
	 */
	public function applyVisibilitySettings($values, $fieldset){
		$visibility_rules = array();
		if(!empty($values['rules'])) {
			if(!empty($values['rules']['remove'])){
				$key_rule = $values['rules']['remove'];
				unset($fieldset['visibility_rules'][$key_rule-1]);
				sort($fieldset['visibility_rules']);
			}
			elseif(!empty($values['rules']['update'])){
				$key_rule = $values['rules']['update'];
				$fieldset['visibility_rules'][$key_rule-1] = $values['rules']['data'];
			}
			else{
				$fieldset['visibility_rules'][] = $values['rules'];
			}
		}
		else {
			return $values;
		}
		return $fieldset;
	}
}

