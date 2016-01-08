<?php

namespace JCF\models;

class JustFieldFactory{
	
	public $registered_fields;
	protected $post_type;
	protected $data_layer;
	
	public function __construct($data_layer, $noregister = FALSE){
		$this->data_layer = $data_layer;
		if(!$noregister){
			$this->register( 'Just_Field_Input' );
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
	}
	/**
	 *	register field in global variable. contain info like id_base, title and class name
	 */
	public function register($class_name){

		$class_name = 'JCF\\components\\' . str_replace('just_field_','',  strtolower($class_name)).'\\' . $class_name;
		// check class exists and try to create class object to get title
		if( !class_exists($class_name) ) return false;

		//check field compatibility with WP version
		if( !$class_name::checkCompatibility($class_name::$compatibility) ) return false;
		
		$field_obj = new $class_name($this->data_layer);

		$field = array(
			'id_base' => $field_obj->id_base,
			'class_name' => $class_name,
			'title' => $field_obj->title,
		);
		
		$this->registered_fields[$field_obj->id_base] = $field;
	}

	/**
	 *	return array of registered fields (or concrete field by id_base)
	 */
	public function get_registered_fields( $id_base = '' ){

		if( !empty($id_base) ){
			return @$this->registered_fields[$id_base];
		}

		return $this->registered_fields;
	}
	
	/**
	 *	init field object
	 */
	public function initObject( $post_type, $field_mixed, $fieldset_id = '', $collection_id = ''){
		// $field_mixed can be real field id or only id_base
		$id_base = preg_replace('/\-([0-9]+)/', '', $field_mixed);
		
		$field = $this->get_registered_fields( $id_base );
		$field_obj = new $field['class_name']($this->data_layer, $post_type);

		$field_obj->set_fieldset( $fieldset_id );
		$field_obj->set_collection( $collection_id );
		$field_obj->set_id( $field_mixed );
		//if is not new field and include to cillection
		if(!$field_obj->is_new && $collection_id){
			
			$collection_obj = new \JCF\components\collection\Just_Field_Collection($this->data_layer, $post_type);
			$collection_obj->set_fieldset($fieldset_id);
			$collection_obj->set_id($collection_id);
			$field = $collection_obj->instance['fields'][$field_mixed];
			$field_obj->set_slug($field['slug']);
			$field_obj->instance = $field;
		}

		return $field_obj;
	}
	
	/**
	 * get next index for save new instance
	 * because of ability to import fields now, we can't use DB to save AI. 
	 * we will use timestamp for this
	 */
	public function getIndex( $id_base ){
		return time();
	}
}

