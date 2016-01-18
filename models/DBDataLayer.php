<?php

namespace jcf\models;
use jcf\interfaces;

class DBDataLayer implements interfaces\FieldSettings {
	
	/**
	 * Get fields by post type and id if isset
	 * @param string $post_type
	 * @param string $id
	 * @return array
	 */
	public function getFields($post_type, $id = FALSE)
	{
		$option_name = $this->getFieldName($post_type);
		$fields = $this->getOptions($option_name);

		if ( !empty($id) ) {
			return $fields[$id];
		}
		return $fields;
	}

	/**
	 *	Update fields in wp-options
	 */
	public function updateFields( $post_type, $key, $values = array(), $fieldset_id = '', $collection_id = '')
	{
		$option_name = $this->getFieldName($post_type);
		$fields = $this->getOptions($option_name);

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
		$this->updateOptions($option_name, $fields);
	}
	
	/**
	 * Get Fieldsets by post type and id if isset
	 * @param string $post_type
	 * @param string $id
	 * @return array
	 */
	public function getFieldsets($post_type, $id = FALSE)
	{
		$option_name = $this->getFieldsetName($post_type);
		$fieldsets = $this->getOptions($option_name);

		if ( !empty($id) ) {
			return @$fieldsets[$id];
		}
		return $fieldsets;
	}
	
	/**
	 * Update fiekdsets
	 * @param string $post_type
	 * @param string $key
	 * @param array $values
	 */
	public function updateFieldsets($post_type, $key, $values = array())
	{
		$option_name = $this->getFieldsetName($post_type);
		$fieldsets = $this->getOptions($option_name);

		if ( $values === NULL && isset($fieldsets[$key]) ) {
			unset($fieldsets[$key]);
		}

		if( !empty($values) ){
			$fielset_values = $this->applyVisibilitySettings($values, $fieldsets[$key]);
			$fieldsets[$key] = $fielset_values;
		}
		$this->updateOptions($option_name, $fieldsets);
	}
	
	/**
	 * update order fieldsets
	 * @param array $keys Fieldsets keys
	 */
	public function sortFieldsets($post_type, $keys = array())
	{
		$option_name = $this->getFieldsetName($post_type);
		$new_fieldsets = array();
		$fieldsets = $this->getOptions($option_name);

		foreach ( $keys as $key ) {
			$new_fieldsets[$key] = $fieldsets[$key];
			unset($fieldsets[$key]);
		}
		$this->updateOptions($option_name, $new_fieldsets);
	}
	
	// option name in wp-options table
	public function getFieldName($post_type)
	{
		return 'jcf_fields-' . $post_type;
	}

	/**
	 * return db fieldset name
	 * @return string
	 */
	public function getFieldsetName($post_type)
	{
		return 'jcf_fieldsets-' . $post_type;
	}
	
	/**
	 * Get all fieldsets and fields
	 * @return array/boolean
	 */
	public function getAllFields()
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
	
	public function getOptions($key)
	{
		$multisite_settings = \jcf\models\Settings::getNetworkMode();
		return $multisite_settings == \jcf\models\Settings::JCF_CONF_MS_NETWORK ? get_site_option($key, array()) : get_option($key, array());
	}
	
	/**
	 *	Update options with wp-options
	 *	@param string $key Option name
	 *	@param array $value Values with option name
	 *	@return bollean
	 */
	public function updateOptions($key, $value)
	{
		$multisite_settings = \jcf\models\Settings::getNetworkMode();
		$multisite_settings == \jcf\models\Settings::JCF_CONF_MS_NETWORK ? update_site_option($key, $value) : update_option($key, $value);
		return true;
	}
	
	/**
	 * apply viibility settings for fieldsets
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

