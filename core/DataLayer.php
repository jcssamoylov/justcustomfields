<?php

namespace jcf\core;

/**
 * General class for all data layers
 */
abstract class DataLayer
{
	protected $_fields;
	protected $_fieldsets;
	
	public function __construct()
	{
		$this->setFields();
		$this->setFieldsets();
	}
	
	public function getFields()
	{
		return $this->_fields;
	}
	// TODO: other general functions
	
	abstract public function setFields( $fields = null );
	abstract public function saveFieldsData();
	
	abstract public function setFieldsets( $fieldset = null );
	abstract public function saveFieldsetsData();
	
	/**
	 * apply viibility settings for fieldsets
	 * @param array $values Visibility values
	 * @return array Fieldsets visibility values
	 */
	protected function _applyVisibilitySettings($values, $fieldset)
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
