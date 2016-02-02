<?php

namespace jcf\models;
use jcf\core;

class Field extends core\Model {

	protected $_dL;
	
	public $post_type;
	public $post_types;
	public $field_id;
	public $field_type;
	public $fieldset_id;
	public $collection_id;
	public $fields_order;
	public $group_id;
	public $term;

	public function __construct()
	{
		parent::__construct();
		$this->_dL = core\DataLayerFactory::create();
	}

	/**
	 * Get all fields
	 * @return array
	 */
	public function findAll()
	{
		return $this->_dL->getFields();
	}

	/**
	 * Find fields by post_type
	 * @param string $post_type
	 * @return array
	 */
	public function findByPostType($post_type)
	{
		$fields = $this->_dL->getFields();
		return $fields[$post_type];
	}

	/**
	 * Find collection by post_type
	 * @param string $post_type
	 * @return array
	 */
	public function findCollectionsByPostType($post_type)
	{
		$fields = $this->_dL->getFields();
		$collections = array();

		if ( !empty($fields) ) {
			foreach ( $fields[$post_type] as $field_id => $field ) {
				if ( !empty($field['fields']) )
					$collections[$field_id] =  $field;
			}
		}

		return $collections;
	}

	/**
	 * Save new field
	 */
	public function save($import = null)
	{
		$field_obj = core\JustFieldFactory::create($this);
		$field_index = core\JustFieldFactory::createFieldIndex($field_obj->idBase);

		return $field_obj->doUpdate($field_index, $import);
	}

	/**
	 * Delete field with $this->_request params
	 */
	public function delete()
	{
		$field_obj = core\JustFieldFactory::create($this);
		$field_obj->doDelete();

		return true;
	}

	/**
	 * Sort fields with $this->_request params
	 */
	public function sort()
	{
		$order  = trim($this->fields_order, ',');
		$fieldsets = $this->_dL->getFieldsets();
		$new_fields = explode(',', $order);
		$fieldsets[$this->post_type][$this->fieldset_id]['fields'] = array();

		foreach ( $new_fields as $field_id ) {
			$fieldsets[$this->post_type][$this->fieldset_id]['fields'][$field_id] = $field_id;
		}

		$this->_dL->setFieldsets($fieldsets);
		$this->_dL->saveFieldsetsData();
		return true;
	}

	/**
	 * Sort sollection fields with $this->_request params
	 */
	public function sortCollection()
	{
		$fields = $this->_dL->getFields();
		$order  = trim($this->fields_order, ',');
		$new_sort = explode(',', $order);
		$new_fields = array();

		if ( !empty($new_sort) ) {
			foreach ( $new_sort as $field_id ) {
				if ( isset($fields[$this->post_type][$this->collection_id]['fields'][$field_id]) ) {
					$new_fields[$field_id] = $fields[$this->post_type][$this->collection_id]['fields'][$field_id];					
				}
			}
		}

		$fields[$this->post_type][$this->collection_id]['fields'] = $new_fields;

		$this->_dL->setFields($fields);
		$this->_dL->saveFieldsData();
		return true;
	}

	/**
	 * Collection fields group with $this->_request params
	 */
	public function groupCollectionFields()
	{
		$this->field_id = $this->collection_id;
		$this->collection_id = false;
		$collection = core\JustFieldFactory::create($this);
		\jcf\components\collection\Just_Field_Collection::$currentCollectionFieldKey = $this->group_id;

		foreach ( $collection->instance['fields'] as $field_id => $field ) {
			$this->field_id = $field_id;
			$this->collection_id = $collection->id;
			$this->fieldset_id = $collection->fieldsetId;
			$field_obj = core\JustFieldFactory::create($this);
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
	public function autocompleteRelatedContent()
	{
		if ( empty($this->term) ) die('');
		
		$post_type = $this->post_types;
		$post_types = jcf_get_post_types('object');

		if ( $post_type != 'any' ) {
			$post_type_where = " post_type = '$this->post_types' ";
		}
		else {
			// get all post types
			$post_type_where = "( post_type = '" . implode("' OR post_type = '", array_keys($post_types)) . "' )";
		}
		
		global $wpdb;
		$query = "SELECT ID, post_title, post_status, post_type
			FROM $wpdb->posts
			WHERE $post_type_where AND (post_status = 'publish' OR post_status = 'draft') AND post_title LIKE '%$this->term%'
			ORDER BY post_title";
		$posts = $wpdb->get_results($query);

		$response = array();
		foreach ( $posts as $p ) {
			$draft = ( $p->post_status == 'draft' ) ? ' (DRAFT)' : '';
			$type_label = ( $this->post_types != 'any' ) ? '' : ' / ' . $post_types[$p->post_type]->labels->singular_name;
			$response[] = array(
				'id' => $p->ID,
				'label' => $p->post_title . $draft . $type_label,
				'value' => $p->post_title . $draft . $type_label,
				'type' => $p->post_type,
				'status' => $p->post_status
			);
		}

		return $response;
	}
	
}

