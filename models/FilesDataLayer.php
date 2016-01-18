<?php

namespace jcf\models;
use jcf\interfaces;

class FilesDataLayer implements interfaces\FieldSettings {

	/**
	 * Get fields by post type and id if isset
	 * @param string $post_type
	 * @param string $id
	 * @return array
	 */
	public function getFields($post_type, $id = FALSE) 
	{
		$all_fields = $this->getAllFields();
		$fields = $all_fields['field_settings'][$post_type];

		if ( !empty($id) ) {
			return $fields[$id];
		}
		return $fields;
	}
	
	/**
	 *	Update fields
	 */
	public function updateFields($post_type, $key, $values = array(), $fieldset_id = '', $collection_id = '')
	{
		$all_fields = $this->getAllFields();
		$fieldset = $all_fields['fieldsets'][$post_type][$fieldset_id];
		$fields = array();

		if ( isset($all_fields['field_settings']) && isset($all_fields['field_settings'][$post_type]) ) {
			$fields = $all_fields['field_settings'][$post_type];				
		}

		if ( $values === NULL && (isset($fields[$key]) || isset($fields[$collection_id]['fields'][$key])) ) {
			if ( !empty($collection_id) ) {
				unset($fields[$collection_id]['fields'][$key]);
			}
			else {
				unset($fields[$key]);
			}
			unset($fieldset['fields'][$key]);
		}

		if ( !empty($values) ) {
			$fieldset['fields'][$key] = $values['enabled'];
			$fields[$key] = $values;
		}

		$all_fields['fieldsets'][$post_type][$fieldset_id] = $fieldset;
		$all_fields['field_settings'][$post_type] = $fields;
		$this->_save($all_fields);
	}
	
	/**
	 * Get Fieldsets by post type and id if isset
	 * @param string $post_type
	 * @param string $id
	 * @return array
	 */
	public function getFieldsets($post_type, $id = FALSE)
	{
		$all_fields = $this->getAllFields();
		$fieldsets = array();

		if ( isset($all_fields['fieldsets'][$post_type]) ) {
			$fieldsets = $all_fields['fieldsets'][$post_type];
		}

		if ( !empty($id) ) {
			return @$fieldsets[$id];
		}
		return $fieldsets;
	}

	/**
	 * update one fieldset settings
	 * @param string $key	fieldset id
	 * @param array $values		fieldset settings
	 */
	public function updateFieldsets($post_type, $key, $values = array() )
	{
		$all_fields = $this->getAllFields();

		if ( $values === NULL && isset($all_fields['fieldsets'][$post_type][$key]) ) {
			unset($all_fields['fieldsets'][$post_type][$key]);
		}
		if ( !empty($values) ) {
			$fieldset_values = $this->applyVisibilitySettings($values, $all_fields['fieldsets'][$post_type][$key]);
			$all_fields['fieldsets'][$post_type][$key] = $fieldset_values;
		}
		$this->_save($all_fields);
	}

	/**
	 * update order fieldsets
	 * @param array $keys Fieldsets keys
	 */
	public function sorFieldsets($post_type, $keys = array())
	{
		$new_fieldsets = array();
		
		$all_fields = $this->getAllFields();

		foreach($keys as $key){
			$new_fieldsets[$key] = $all_fields['fieldsets'][$post_type][$key];
			unset($all_fields['fieldsets'][$post_type][$key]);
		}
		$all_fields['fieldsets'][$post_type] = $new_fieldsets;
		$this->_save($all_fields);
	}
	
	/**
	 * Get path to file with fields and fieldsets
	 * @param string $source_settings
	 * @return string/boolean
	 */
	public function getConfigFilePath($source_settings)
	{
		if( !empty($source_settings) && ($source_settings == JCF_CONF_SOURCE_FS_THEME || $source_settings == JCF_CONF_SOURCE_FS_GLOBAL) ) {
			return ($source_settings == JCF_CONF_SOURCE_FS_THEME)? get_stylesheet_directory() . '/jcf-settings/jcf_settings.json' : get_home_path() . 'wp-content/jcf-settings/jcf_settings.json';
		}
		return false;
	}
	
	/**
	 * Save all field and fieldsets
	 * @param array $data
	 * @param string $file
	 * @return boolean
	 */
	protected function _save($data, $file = false)
	{
		if ( !$file ) {
			$file = $this->getConfigFilePath($this->_source);
		}
		$data = jcf_format_json(json_encode($data));
		$dir = dirname($file);

		// trying to create dir
		if ( (!is_dir($dir) && ! wp_mkdir_p($dir)) || !is_writable($dir) ) {
			return false;
		}

		if ( !empty($dir) ) {
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
	
	/**
	 * Get all fieldsets and fields
	 * @return array/boolean
	 */
	public function getAllFields()
	{
		$source = \jcf\models\Settings::getDataSourceType();
		$filename = $this->getConfigFilePath($source);

		if ( file_exists($filename) ) {
			return $this->getDataFromFile($filename);
		}
		return false;
	}
	
	/**
	*	Get fields and fieldsets from file
	*	@param string $uploadfile File name
	*	@return boolean/array Array with fields settings from file
	*/
	function getDataFromFile($file = false)
	{
		if ( !$file )
			$file = $this->getConfigFilePath($this->_source);

		if ( file_exists($file) ) {
			$content = file_get_contents($file);
			$data = json_decode($content, true);
			return $data;
		}
		return false;
	}
	
	/**
	 * Apply viibility settings for fieldsets
	 * @param array $values Visibility values
	 * @return array Fieldsets visibility values
	 */
	public function applyVisibilitySettings($values, $fieldset)
	{
		$visibility_rules = array();

		if ( !empty($values['rules']) ) {
			if ( !empty($values['rules']['remove']) ) {
				$key_rule = $values['rules']['remove'];
				unset($fieldset['visibility_rules'][$key_rule-1]);
				sort($fieldset['visibility_rules']);
			}
			elseif ( !empty($values['rules']['update']) ) {
				$key_rule = $values['rules']['update'];
				$fieldset['visibility_rules'][$key_rule-1] = $values['rules']['data'];
			}
			else {
				$fieldset['visibility_rules'][] = $values['rules'];
			}
		}
		else {
			return $values;
		}
		return $fieldset;
	}
}
