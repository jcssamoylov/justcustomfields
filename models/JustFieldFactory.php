<?php

namespace jcf\models;
use jcf\core;

class JustFieldFactory {
	
	public static function create( \jcf\models\Field $field )
	{
		// $field_mixed can be real field id or only id_base
		$field_mixed = !empty($field->field_id) ? $field->field_id : $field->field_type;
		$id_base = preg_replace('/\-([0-9]+)/', '', $field_mixed);

		$jcf = new \jcf\JustCustomFields();
		$field_info = $jcf->getFieldInfo($id_base);
		
		$model = new $field_info['class']();
		$model->setPostType($field->post_type);
		$model->setFieldset($field->fieldset_id);
		$model->setCollection($field->collection_id);
		$model->setId($field_mixed);
		
		if ( !$model->is_new && $field->collection_id ) {
			$collection = new \jcf\components\collection\Just_Field_Collection();
			$collection->setPostType($field->post_type);
			$collection->setFieldset($field->fieldset_id);
			$collection->setId($field->collection_id);
			
			$field_instance = $collection->instance['fields'][$field_mixed];
			$model->setSlug($field_instance['slug']);
			$model->instance = $field_instance;
		}

		return $model;
	}
	
	/**
	 *	init field object
	 /
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
	public static function createFieldIndex( $id_base )
	{
		return time();
	}
}

