<?php
namespace jcf\components\datepicker;
use jcf\models;

class Just_Field_DatePicker extends models\Just_Field {
	
	public static $compatibility = '3.3+';

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_datepicker' );
		parent::__construct('datepicker', __('Date Picker', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
	}

	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field() 
	{
		include(JCF_ROOT . '/components/datepicker/views/field.tpl.php');
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
		$instance['show_monthes'] = (int)@$new_instance['show_monthes'];
		$instance['date_format'] = @$new_instance['date_format'];
		return $instance;
	}

	public function addJs()
	{
		/**
		 * WP version 3.0 and above have datepicker ui-core;
		 */
		wp_enqueue_script('jquery-ui-datepicker');
	}

	public function addCss()
	{
		wp_register_style('jcf_ui_datepicker', WP_PLUGIN_URL.'/just-custom-fields/components/datepicker/ui-theme-smoothness/jquery-ui-1.8.13.custom.css');
		wp_enqueue_style('jcf_ui_datepicker');
	}

}
?>