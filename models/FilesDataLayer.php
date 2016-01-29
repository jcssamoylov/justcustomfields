<?php

namespace jcf\models;
use jcf\core;

class FilesDataLayer extends core\DataLayer {

	/**
	 * Set $this->_fields property
	 * @param array $fields
	 */
	public function setFields($fields = null) 
	{
		if ( !is_null($fields) ) {
			$this->_fields = $fields;
			return;
		}

		$data = $this->getDataFromFile();
		$this->_fields = $data['field_settings'];
	}

	/**
	 *	Update fields
	 */
	public function saveFieldsData(/*$post_type, $key, $values = array(), $fieldset_id = '', $collection_id = ''*/)
	{
		$all_fields = $this->getAll();
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
	public function setFieldsets($fieldsets = null)
	{
		if ( !is_null($fieldsets) ) {
			$this->_fieldsets = $fieldsets;
			return;
		}

		$data = $this->getDataFromFile();
		$this->_fieldsets = $data['fieldsets'];
	}

	/**
	 * update one fieldset settings
	 * @param string $key	fieldset id
	 * @param array $values		fieldset settings
	 */
	public function saveFieldsetsData($post_type, $key, $values = array())
	{
		$all_fields = $this->getAll();

		if ( $values === NULL && isset($all_fields['fieldsets'][$post_type][$key]) ) {
			unset($all_fields['fieldsets'][$post_type][$key]);
		}
		if ( !empty($values) ) {
			$fieldset_values = $this->_applyVisibilitySettings($values, $all_fields['fieldsets'][$post_type][$key]);
			$all_fields['fieldsets'][$post_type][$key] = $fieldset_values;
		}
		$this->_save($all_fields);
	}

	/**
	 * Sort fieldsets
	 * @param string $post_type
	 * @param array $keys Fieldsets keys
	 */
	public function sortFieldsets($post_type, $keys = array())
	{
		$new_fieldsets = array();
		
		$all_fields = $this->getAll();

		foreach($keys as $key){
			$new_fieldsets[$key] = $all_fields['fieldsets'][$post_type][$key];
			unset($all_fields['fieldsets'][$post_type][$key]);
		}
		$all_fields['fieldsets'][$post_type] = $new_fieldsets;
		$this->_save($all_fields);
	}

	/**
	 * Get all fieldsets and fields
	 * @return array/boolean
	 */
	public function getAll()
	{
		$source = \jcf\models\Settings::getDataSourceType();
		$filename = $this->_getConfigFilePath($source);

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
	public function getDataFromFile($file = false)
	{
		$source = \jcf\models\Settings::getDataSourceType();

		if ( !$file )
			$file = $this->_getConfigFilePath($source);

		if ( file_exists($file) ) {
			$content = file_get_contents($file);
			$data = json_decode($content, true);
			return $data;
		}

		return false;
	}

	/**
	 * Get path to file with fields and fieldsets
	 * @param string $source_settings
	 * @return string/boolean
	 */
	protected function _getConfigFilePath($source_settings)
	{
		if( !empty($source_settings) && ($source_settings == \jcf\models\Settings::CONF_SOURCE_FS_THEME || $source_settings == \jcf\models\Settings::CONF_SOURCE_FS_GLOBAL) ) {
			return ($source_settings == \jcf\models\Settings::CONF_SOURCE_FS_THEME)? get_stylesheet_directory() . '/jcf-settings/jcf_settings.json' : get_home_path() . 'wp-content/jcf-settings/jcf_settings.json';
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
		$source = \jcf\models\Settings::getDataSourceType();
		if ( !$file ) {
			$file = $this->_getConfigFilePath($source);
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
}
