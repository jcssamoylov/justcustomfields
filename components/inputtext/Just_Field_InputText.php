<?php
namespace jcf\components\inputtext;
use jcf\models;

class Just_Field_InputText extends models\Just_Field{
	
	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_inputtext' );
		parent::__construct('inputtext', __('Input Text', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field()
	{
		include(JCF_ROOT . '/components/inputtext/views/field.tpl.php');
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
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}

}
