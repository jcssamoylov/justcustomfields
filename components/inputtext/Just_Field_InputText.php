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
	public function field( $args )
	{
		extract( $args );
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		echo '<input type="text" name="'.$this->getFieldName('val').'" id="'.$this->getFieldId('val').'" value="'.esc_attr($this->entry).'"/> ';
		
		if ( $this->instance['description'] != '' )
			echo '<p class="description">' . $this->instance['description'] . '</p>';

		echo $after_widget;
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
