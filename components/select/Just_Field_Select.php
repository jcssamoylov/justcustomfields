<?php
namespace jcf\components\select;
use jcf\models;
/**
 * Class for select list type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class Just_Field_Select extends models\Just_Field{
	
	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_select' );
		parent::__construct('select', __('Select', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
	}

	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field()
	{
		$values = $this->parsedSelectOptions($this->instance);
		include(JCF_ROOT . '/components/select/views/field.tpl.php');
	}

	/**
	 *	save field on post edit form
	 */
	public function save( $values )
	{
		$values = $values['val'];
		
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['options'] = strip_tags($new_instance['options']);
		$instance['description'] = strip_tags($new_instance['description']);
		$instance['empty_option'] = strip_tags($new_instance['empty_option']);
		return $instance;
	}
	
	/**
	 * get current options settings based on plugin version
	 * 
	 * @param array $instance	current instance
	 */
	public function getInstanceSelectOptions( $instance )
	{
		// from version 1.4 key for storing select options changed to match it's meaning
		if ( $this->getInstanceVersion($instance) < 1.4 && empty($instance['options']) && !empty($instance['settings']) ) {
			return $instance['settings'];
		}
		else {
			return $instance['options'];
		}
	}
	
	/**
	 * prepare list of options
	 * 
	 * @param array $instance	current instance
	 */
	public function parsedSelectOptions($instance)
	{
		$values = array();
		$settings = $this->getInstanceSelectOptions($instance);
		
		$v = explode("\n", $settings);
		foreach ( $v as $val ) {
			$val = trim($val);
			if ( strpos($val, '|') !== FALSE ) {
				$a = explode('|', $val);
				$values[$a[0]] = $a[1];
			}
			elseif ( !empty($val) ) {
				$values[$val] = $val;
			}
		}

		return $values;
	}

	/**
	 * print field values inside the shortcode
	 * 
	 * @params array $args	shortcode args
	 */
	public function shortcodeValue( $args )
	{
		$options = $this->parsedSelectOptions($this->instance);
		$options = array_flip($options);
		$value = $this->entry;

		if ( isset($options[$this->entry]) ) {
			$value = $options[$this->entry];
		}
		return  $args['before_value'] . $value . $args['after_value'];
	}
}
