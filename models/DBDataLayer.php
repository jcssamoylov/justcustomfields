<?php

namespace jcf\models;
use jcf\core;

class DBDataLayer extends core\DataLayer {

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

		$post_types = jcf_get_post_types();

		foreach ( $post_types as $post_type) {
			$option_name = $this->_getFieldName($post_type->name);
			$this->_fields[$post_type->name] = $this->_getOptions($option_name);
		}
	}

	/**
	 *	Update fields in wp-options
	 */
	public function saveFieldsData( /*$post_type, $key, $values = array(), $fieldset_id = '', $collection_id = ''*/)
	{
		$option_name = $this->_getFieldName($post_type);
		$fields = $this->_getOptions($option_name);

		if ( $values === NULL && ( isset($fields[$key]) || isset($fields[$collection_id]['fields'][$key]) ) ) {
			if ( !empty($collection_id) ) {
				unset($fields[$collection_id]['fields'][$key]);
			}
			else {
				unset($fields[$key]);
			}
		}

		if ( !empty($values) ) {
			$fields[$key] = $values;
		}
		$this->_updateOptions($option_name, $fields);
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
		
		$post_types = jcf_get_post_types( 'object' );

		foreach ( $post_types as $post_type) {
			$option_name = $this->_getFieldsetName($post_type->name);
			$this->_fieldsets[$post_type->name] = $this->_getOptions($option_name);
		}
	}
	
	/**
	 * Update fiekdsets
	 * @param string $post_type
	 * @param string $key
	 * @param array $values
	 */
	public function saveFieldsetsData($post_type, $key, $values = array())
	{
		$option_name = $this->_getFieldsetName($post_type);

		if ( $values === NULL && isset($this->_fieldsets[$post_type][$key]) ) {
			unset($this->_fieldsets[$post_type][$key]);
		}

		if ( !empty($values) ) {
			$fielset_values = $this->_applyVisibilitySettings($values, $this->_fieldsets[$post_type][$key]);
			$this->_fieldsets[$post_type][$key] = $fielset_values;
		}

		$this->_updateOptions($option_name, $this->_fieldsets[$post_type]);
	}
	
	/**
	 * Sort fieldsets
	 * @param string $post_type
	 * @param array $keys Fieldsets keys
	 */
	public function sortFieldsets($post_type, $keys = array())
	{
		$option_name = $this->_getFieldsetName($post_type);
		$new_fieldsets = array();
		$fieldsets = $this->_getOptions($option_name);

		foreach ( $keys as $key ) {
			$new_fieldsets[$key] = $fieldsets[$key];
			unset($fieldsets[$key]);
		}
		$this->_updateOptions($option_name, $new_fieldsets);
	}

	/**
	 * Get all fieldsets and fields
	 * @return array/boolean
	 */
	public function getAll()
	{
		$post_types = jcf_get_post_types();
		$settings = array();
		$fieldsets = array();
		$field_settings = array();
		$field_options = array();
		
		foreach ( $post_types as $key => $value ) {
			$fieldsets[$key] = $this->getFieldsets($key);
			$field_settings[$key] = $this->getFields($key);
		}

		$settings = array(
			'post_types' => $post_types,
			'fieldsets' => $fieldsets,
			'field_settings' => $field_settings,
		);
		return $settings;
	}

	/**
	 * Option name in wp-options table
	 * @param string $post_type
	 * @return string 
	 */
	protected function _getFieldName($post_type)
	{
		return 'jcf_fields-' . $post_type;
	}

	/**
	 * return db fieldset name
	 * @param string $post_type
	 * @return string
	 */
	protected function _getFieldsetName($post_type)
	{
		return 'jcf_fieldsets-' . $post_type;
	}
	
	
	
	protected function _getOptions($key)
	{
		$multisite_settings = \jcf\models\Settings::getNetworkMode();
		return $multisite_settings == \jcf\models\Settings::CONF_MS_NETWORK ? get_site_option($key, array()) : get_option($key, array());
	}
	
	/**
	 *	Update options with wp-options
	 *	@param string $key Option name
	 *	@param array $value Values with option name
	 *	@return bollean
	 */
	protected function _updateOptions($key, $value)
	{
		$multisite_settings = \jcf\models\Settings::getNetworkMode();
		$multisite_settings == \jcf\models\Settings::CONF_MS_NETWORK ? update_site_option($key, $value) : update_option($key, $value);
		return true;
	}
	

}

