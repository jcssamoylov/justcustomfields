<?php

namespace jcf\models;
use jcf\core;
use jcf\models;

class Field extends core\Model {

	protected $_layer;
	
	public $post_type;
	public $field_id;
	public $field_type;
	public $fieldset_id;
	public $collection_id;
	public $fields_order;

	public function __construct()
	{
		parent::__construct();
		$layer_factory = new DataLayerFactory();
		$this->_layer = $layer_factory->create();
	}

	/**
	 * Save new field
	 */
	public function save()
	{
		$field_obj = models\JustFieldFactory::create($this);
		$field_index = models\JustFieldFactory::createFieldIndex($field_obj->idBase);

		return $field_obj->doUpdate($field_index);
	}

	/**
	 * Delete field with $this->_request params
	 */
	public function delete()
	{
		$field_obj = models\JustFieldFactory::create($this);
		$field_obj->doDelete();

		return array('status' => '1');
	}

	/**
	 * Sort fields with $this->_request params
	 */
	public function sort()
	{
		$order  = trim($this->fields_order, ',');
		$fieldset = $this->_layer->getFieldsets($this->post_type, $this->fieldset_id);
		$new_fields = explode(',', $order);
		$fieldset['fields'] = array();

		foreach ( $new_fields as $field_id ) {
			$fieldset['fields'][$field_id] = $field_id;
		}

		$this->_layer->updateFieldsets($this->post_type, $this->fieldset_id, $fieldset);

		return array('status' => '1');
	}

	/**
	 * Sort sollection fields with $this->_request params
	 */
	public function sortCollection()
	{
		$this->field_id = $this->collection_id;
		$this->collection_id = false;
		$collection = models\JustFieldFactory::create($this);
		$order  = trim($this->fields_order, ',');
		$new_fields = explode(',', $order);
		$new_order = array();		

		if (! empty($new_fields) ) {
			foreach ( $new_fields as $field_id ) {
				if ( isset($collection->instance['fields'][$field_id]) ) {
					$new_order[$field_id] = $collection->instance['fields'][$field_id];					
				}
			}
		}

		$collection->instance['fields'] = $new_order;
		$this->_layer->updateFields($this->post_type, $this->field_id, $collection->instance, $this->fieldset_id);

		return array('status' => '1');
	}

	/**
	 * Collection fields group with $this->_request params
	 */
	public function groupCollectionFields()
	{
		$post_type = $this->_request['post_type'];
		$fieldset_id = $this->_request['fieldset_id'];
		$collection_id = $this->_request['collection_id'];

		$field_factory = new models\JustFieldFactory();
		$collection = $field_factory->initObject($post_type, $collection_id, $fieldset_id);
		\jcf\components\collection\Just_Field_Collection::$currentCollectionFieldKey = $this->_request['group_id'];

		foreach ( $collection->instance['fields'] as $field_id => $field ) {
			$field_obj = $field_factory->initObject($post_type, $field_id, $collection->fieldsetId, $collection->id);
			$field_obj->setSlug($field['slug']);
			$field_obj->instance = $field;
			$field_obj->isPostEdit = true;
			ob_start();
			$field_obj->field();
			$field['field'] = ob_get_clean();
			$collection->instance['fields'][$field_id] = $field;
		}

		return $collection;
	}

	/**
	 * Autocomplete for related content
	 */
	public function autocompleteRelatedContentField()
	{
		$term = $this->_request['term'];
		if ( empty($term) ) die('');
		
		$post_type = $this->_request['post_types'];
		$post_types = jcf_get_post_types('object');

		if ( $post_type != 'any' ) {
			$post_type_where = " post_type = '$post_type' ";
		}
		else {
			// get all post types
			$post_type_where = "( post_type = '" . implode("' OR post_type = '", array_keys($post_types)) . "' )";
		}
		
		global $wpdb;
		$query = "SELECT ID, post_title, post_status, post_type
			FROM $wpdb->posts
			WHERE $post_type_where AND (post_status = 'publish' OR post_status = 'draft') AND post_title LIKE '%$term%'
			ORDER BY post_title";
		$posts = $wpdb->get_results($query);
		
		$response = array();
		foreach ( $posts as $p ) {
			$draft = ( $p->post_status == 'draft' ) ? ' (DRAFT)' : '';
			$type_label = ( $post_type != 'any' ) ? '' : ' / ' . $post_types[$p->post_type]->labels->singular_name;
			$response[] = array(
				'id' => $p->ID,
				'label' => $p->post_title . $draft . $type_label,
				'value' => $p->post_title . $draft . $type_label,
				'type' => $p->post_type,
				'status' => $p->post_status
			);
		}
		$json = json_encode($response);
		header( "Content-Type: application/json" );
		echo $json;
		exit();
	}
	
}

