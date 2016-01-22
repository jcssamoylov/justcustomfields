<?php

namespace jcf\models;
use jcf\core;

class JustFieldFactory extends core\Model {
	
	protected $_registeredFields;
	
	public function __construct($collection = FALSE)
	{
		if ( !$collection ) {
			$this->register( 'Just_Field_InputText' );
			$this->register( 'Just_Field_Select' );
			$this->register( 'Just_Field_SelectMultiple' );
			$this->register( 'Just_Field_Checkbox' );
			$this->register( 'Just_Field_Textarea' );
			$this->register( 'Just_Field_DatePicker' );
			$this->register( 'Just_Field_SimpleMedia' );
			$this->register( 'Just_Field_Table' );
			$this->register( 'Just_Field_Collection' );
			$this->register( 'Just_Field_RelatedContent' );
			$this->register( 'Just_Field_UploadMedia' );
			$this->register( 'Just_Field_FieldsGroup' );
		}
		else {
			$this->register( 'Just_Field_InputText' );
			$this->register( 'Just_Field_Select' );
			$this->register( 'Just_Field_SelectMultiple' );
			$this->register( 'Just_Field_Checkbox' );
			$this->register( 'Just_Field_Textarea' );
			$this->register( 'Just_Field_DatePicker' );
			$this->register( 'Just_Field_Simple_Media' );
			$this->register( 'Just_Field_Table' );
		}
	}

	/**
	 *	register field in global variable. contain info like id_base, title and class name
	 */
	public function register($class_name)
	{
		$class_name = 'jcf\\components\\' . str_replace('just_field_','',  strtolower($class_name)).'\\' . $class_name;
		// check class exists and try to create class object to get title
		if ( !class_exists($class_name) ) return false; 

		//check field compatibility with WP version
		if ( !$class_name::checkCompatibility($class_name::$compatibility) ) return false;

		$field_obj = new $class_name();

		$field = array(
			'id_base' => $field_obj->idBase,
			'class_name' => $class_name,
			'title' => $field_obj->title,
		);
		$this->_registeredFields[$field_obj->idBase] = $field;
	}

	/**
	 *	return array of registered fields (or concrete field by id_base)
	 */
	public function getRegisteredFields($id_base = '')
	{
		if ( !empty($id_base) ) {
			return @$this->_registeredFields[$id_base];
		}
		return $this->_registeredFields;
	}
	
	/**
	 *	init field object
	 */
	public function initObject($post_type, $field_mixed, $fieldset_id = '', $collection_id = '')
	{
		// $field_mixed can be real field id or only id_base
		$id_base = preg_replace('/\-([0-9]+)/', '', $field_mixed);
		$field = $this->getRegisteredFields( $id_base );

		$field_obj = new $field['class_name']();
		$field_obj->setPostType($post_type);
		$field_obj->setFieldset( $fieldset_id );
		$field_obj->setCollection( $collection_id );
		$field_obj->setId( $field_mixed );

		//if is not new field and include to cillection
		if ( !$field_obj->is_new && $collection_id ) {
			$collection_obj = new \jcf\components\collection\Just_Field_Collection();
			$collection_obj->setPostType($post_type);
			$collection_obj->setFieldset($fieldset_id);
			$collection_obj->setId($collection_id);
			$field = $collection_obj->instance['fields'][$field_mixed];
			$field_obj->setSlug($field['slug']);
			$field_obj->instance = $field;
		}
		return $field_obj;
	}
	
	/**
	 * get next index for save new instance
	 * because of ability to import fields now, we can't use DB to save AI. 
	 * we will use timestamp for this
	 */
	public function getIndex( $id_base )
	{
		return time();
	}
}

