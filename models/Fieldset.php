<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

class Fieldset extends core\Model
{
	public $title;
	public $post_type;
	public $fieldset_id;
	public $fieldsets_order;

	/**
	 * Return number of registered fields and fieldsets for specific post type
	 * @return array
	 */
	public function getFieldsCounter()
	{
		$fields = $this->_dL->getFields();
		$fieldsets = $this->_dL->getFieldsets();
		$post_types = jcf_get_post_types();

		foreach ( $post_types as $key => $post_type ) {
			$count[$post_type->name]['fieldsets'] = count($fieldsets[$post_type->name]);
			$count[$post_type->name]['fields'] = count($fields[$post_type->name]);
		}

		return $count;
	}

	/**
	 * Get fields and fieldsets by post_type
	 * @param string $post_type Name post type
	 * @return array
	 */
	public function findByPostType( $post_type )
	{
		$fieldsets = $this->_dL->getFieldsets();
		return $fieldsets[$post_type];
	}

	/**
	 * Get all fieldsets
	 * @return array
	 */
	public function findAll()
	{
		return $this->_dL->getFieldsets();
	}

	/**
	 * Get fieldset by ID
	 */
	public function findById( $fieldset_id )
	{
		$fieldsets = $this->_dL->getFieldsets();
		if ( empty($fieldsets[$this->post_type][$fieldset_id]) ) {
			$this->addError(__('Fieldset not found', \jcf\JustCustomFields::TEXTDOMAIN));
			return false;
		}
		return $fieldsets[$this->post_type][$fieldset_id];
	}

	/**
	 * Create new fieldset with $this->_request params
	 */
	public function create()
	{
		if ( empty($this->title) && empty($this->import_data) ) {
			$this->addError(__('Title field is required.', \jcf\JustCustomFields::TEXTDOMAIN));
			return false;
		}

		$slug = $this->createSlug();

		$fieldsets = $this->_dL->getFieldsets();

		// check exists
		if ( isset($fieldsets[$this->post_type][$slug]) ) {
			$this->addError(__('Such fieldset already exists.', \jcf\JustCustomFields::TEXTDOMAIN));
			return false;
		}

		$fieldsets[$this->post_type][$slug] = array(
			'id' => $slug,
			'title' => $this->title,
			'fields' => array()
		);

		return $this->_save($fieldsets);
	}

	/**
	 * Delete fieldset with $this->_request params
	 */
	public function delete()
	{
		if ( empty($this->fieldset_id) ) {
			$this->addError(__('Wrong params passed.', \jcf\JustCustomFields::TEXTDOMAIN));
			return false;
		}

		$fieldsets = $this->_dL->getFieldsets();
		unset($fieldsets[$this->post_type][$this->fieldset_id]);

		return $this->_save($fieldsets);
	}

	/**
	 * Update fieldset with $this->_request params
	 */
	public function update()
	{
		$fieldsets = $this->_dL->getFieldsets();

		if ( empty($fieldsets[$this->post_type][$this->fieldset_id]) ) {
			$this->addError(__('Wrong data passed.', \jcf\JustCustomFields::TEXTDOMAIN));
			return false;
		}

		if ( empty($this->title) ) {
			$this->addError(__('Title field is required.', \jcf\JustCustomFields::TEXTDOMAIN));
			return false;
		}

		$fieldsets[$this->post_type][$this->fieldset_id]['title'] = $this->title;

		return $this->_save($fieldsets);
	}

	/**
	 * Sort fieldsets with $this->_request params
	 */
	public function sort()
	{
		$sort = explode(',', trim($this->fieldsets_order, ','));
		$fieldsets = $this->_dL->getFieldsets();

		foreach ( $sort as $key ) {
			$new_fieldsets[$this->post_type][$key] = $fieldsets[$this->post_type][$key];
			unset($fieldsets[$key]);
		}

		if ( !$this->_save($fieldsets) ) {
			$this->addError(__('Sorting isn\'t changed.', \jcf\JustCustomFields::TEXTDOMAIN));
			return false;
		}

		return true;
	}

	/**
	 * Create slug for new fieldset
	 * @return string
	 */
	public function createSlug()
	{
		$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $this->title);
		$trimed_slug = trim($slug);

		if ( $trimed_slug == '' ) {
			$slug = 'jcf-fieldset-' . rand(0, 10000);
		}
		else {
			$slug = sanitize_title($this->title);
		}
		return $slug;
	}

	/**
	 * Save fieldsets
	 * @param array $fieldsets
	 */
	protected function _save( $fieldsets )
	{
		$this->_dL->setFieldsets($fieldsets);
		$save = $this->_dL->saveFieldsetsData();
		return !empty($save);
	}

}
