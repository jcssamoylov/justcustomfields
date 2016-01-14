<?php

namespace jcf\models;
use jcf\interfaces;

class FilesDataLayer implements interfaces\FieldSettings {
	
	private $source_settings;
	
	public function __construct($source_settings)
	{
		$this->source_settings = $source_settings;
	}
	
	public function get_fields($post_type, $id = FALSE) {
		$all_fields = $this->getAllData();
		$field_settings = $all_fields['field_settings'][$post_type];

		if(!empty($id)){
			return @$field_settings[$id];
		}
		return $field_settings;
	}
	
	/**
	 *	set fields in wp-options
	 */
	public function update_fields( $post_type, $key, $values = array(), $fieldset_id = '', $collection_id = ''){

		$all_fields = $this->getAllData();
		$fieldset = $all_fields['fieldsets'][$post_type][$fieldset_id];
		if( isset($all_fields['field_settings']) && isset($all_fields['field_settings'][$post_type]) ){
			$field_settings = $all_fields['field_settings'][$post_type];				
		} else $field_settings = array();

		if( $values === NULL && ( isset($field_settings[$key]) || isset($field_settings[$collection_id]['fields'][$key]) ) ){
			if(!empty($collection_id)){
				unset($field_settings[$collection_id]['fields'][$key]);
			}
			else{
				unset($field_settings[$key]);
			}
			unset($fieldset['fields'][$key]);
			
		}

		if( !empty($values) ){
			$fieldset['fields'][$key] = $values['enabled'];
			$field_settings[$key] = $values;
		}

		$all_fields['fieldsets'][$post_type][$fieldset_id] = $fieldset;
		$all_fields['field_settings'][$post_type] = $field_settings;
		$this->saveAll($all_fields);

	}

	public function get_fieldsets($post_type, $id = FALSE){

		$all_fields = $this->getAllData();

		if(isset($all_fields['fieldsets'][$post_type])){
			$fieldsets = $all_fields['fieldsets'][$post_type];
		} else $fieldsets = array();

		if(!empty($id)){
			return @$fieldsets[$id];
		}

		return $fieldsets;
	}

	/**
	 * update one fieldset settings
	 * @param string $key	fieldset id
	 * @param array $values		fieldset settings
	 */
	public function update_fieldsets($post_type, $key, $values = array() ){
		$all_fields = $this->getAllData();

		if( $values === NULL && isset($all_fields['fieldsets'][$post_type][$key]) ){
			unset($all_fields['fieldsets'][$post_type][$key]);
		}
		if( !empty($values) ){
			$fielset_values = $this->applyVisibilitySettings($values, $all_fields['fieldsets'][$post_type][$key]);
			$all_fields['fieldsets'][$post_type][$key] = $fielset_values;
		}
		$this->saveAll($all_fields);
	}

	/**
	 * update order fieldsets
	 * @param array $keys Fieldsets keys
	 */
	public function sort_fieldsets($post_type, $keys = array()){
		$new_fieldsets = array();
		
		$all_fields = $this->getAllData();

		foreach($keys as $key){
			$new_fieldsets[$key] = $all_fields['fieldsets'][$post_type][$key];
			unset($all_fields['fieldsets'][$post_type][$key]);
		}
		$all_fields['fieldsets'][$post_type] = $new_fieldsets;
		$this->saveAll($all_fields);
	}
	
	public function getConfigFilePath( $source_settings ){

		if( !empty($source_settings) && ($source_settings == JCF_CONF_SOURCE_FS_THEME || $source_settings == JCF_CONF_SOURCE_FS_GLOBAL) ){
			return ($source_settings == JCF_CONF_SOURCE_FS_THEME)? get_stylesheet_directory() . '/jcf-settings/jcf_settings.json' : get_home_path() . 'wp-content/jcf-settings/jcf_settings.json';
		}
		return false;
	}
	
	public function cloneDBSettings($file){
		$layer_factory = new DataLayerFactory();
		$db_layer = $layer_factory->create('DB', $this->source_settings);
		$all_fields = $db_layer->getAllData();

		$dir = dirname($file);

		if( 
			// check that dir exists (if not try to create) and is writable
			 ( (!is_dir($dir) && !wp_mkdir_p($dir)) || !is_writable($dir) )
			// try to write settings
			|| !$saved = $this->saveAll($all_fields, $file)
		)
		{
			// if fail - print error
			$notice = array('error', sprintf( __('<strong>Settings storage update FAILED!</strong>. Please check that directory exists and writable: %s', JCF_TEXTDOMAIN), dirname($dir) ));
		}
		else{
			// we have another notification after this func called
			//$msg = array('notice', __('<strong>Fields settings</strong> successfully copied!', JCF_TEXTDOMAIN));
		}
		unset($db_layer);
		$output = array(
			'saved' => $saved,
			'notice' => $notice
		);
		return $output;
	}
	
	public function saveAll($data, $file = false){
		if(!$file){
			$file = $this->getConfigFilePath($this->source_settings);
		}
		$data = jcf_format_json(json_encode($data));
		$dir = dirname($file);

		// trying to create dir
		if( (!is_dir($dir) && ! wp_mkdir_p($dir)) || !is_writable($dir) ){
			return false;
		}

		if( !empty($dir) ){
			$content = $data . "\r\n";
			if( $fp = fopen($file, 'w') ){
				fwrite($fp, $content);
				fclose($fp);
				jcf_set_chmod($file);
				return true;
			}
		}

		return false;
	}
	
	public function getAllData(){
		$filename = $this->getConfigFilePath($this->source_settings);

		if (file_exists($filename)) {
			return $this->getDataFromFile($filename);
		}
		else{
			return false;
		}
	}
	
	/**
	*	Get settings from file
	*	@param string $uploadfile File name
	*	@return array Array with fields settings from file
	*/
	function getDataFromFile($uploadfile = false){
		if(!$uploadfile)
			$uploadfile = $this->getConfigFilePath($this->source_settings);

		if (file_exists($uploadfile)) {
			$content = file_get_contents($uploadfile);
			$data = json_decode($content, true);
			return $data;
		}
		else{
			return false;
		}
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
