<?php

namespace jcf\models;
use jcf\core;
use jcf\models;

class Fieldset extends core\Model {

	protected $_dL;

	public $export_fields;
	public $export_data;
	public $action;
	public $import_data;
	public $file_name;
	public $title;
	public $post_type;
	public $fieldset_id;
	public $fieldsets_order;


	public function __construct()
	{
		parent::__construct();
		$this->_dL = core\DataLayerFactory::create();
	}

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
	public function findByPostType($post_type)
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
	public function findById($fieldset_id)
	{
		$fieldsets = $this->_dL->getFieldsets();
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
		
		$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $this->title);
		$trimed_slug = trim($slug);

		if ( $trimed_slug == '' ) {
			$slug = 'jcf-fieldset-'.rand(0,10000);
		}
		else {
			$slug = sanitize_title($this->title);
		}

		//If do import, just return new slug without save
		if ( !empty($this->import_data) ) return $slug;

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
		$this->_save($fieldsets);

		return true;
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
		$this->_save($fieldsets);

		return true;
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
		$this->_save($fieldsets);

		return true;
	}

	/**
	 * Sort fieldsets with $this->_request params
	 */
	public function sort()
	{
		$sort  = explode(',', trim($this->fieldsets_order, ','));
		$fieldsets = $this->_dL->getFieldsets();

		foreach ($sort as $key) {
			$new_fieldsets[$this->post_type][$key] = $fieldsets[$this->post_type][$key];
			unset($fieldsets[$key]);
		}
		$this->_save($fieldsets);

		return true;
	}

	/**
	 * Export fields
	 */
	public function exportFields()
	{
		if ( $this->export_fields && !empty($this->export_data) ) {
			return json_encode($this->export_data);
		}
	}

	/**
	 * Get fields for import
	 */
	public function getImportFields()
	{
		if ( !empty($this->action) && $this->action == 'jcf_import_fields' ) {
			if ( !empty($_FILES['import_data']['name']) ) {
				$path_info = pathinfo($_FILES['import_data']['name']);

				if ( $path_info['extension'] == 'json') {
					if ( is_readable($_FILES['import_data']['tmp_name']) ) {
						$file_Layer = core\DataLayerFactory::create('file');
						$data['post_types'] = json_decode($file_Layer->getDataFromFile($_FILES['import_data']['tmp_name']), true);
						unlink($_FILES['import_data']['tmp_name']);

						if ( empty($data) ) {
							$error = __('<strong>Import FAILED!</strong> File do not contain fields settings data..', \jcf\JustCustomFields::TEXTDOMAIN);
							$this->addError($error);
						}

						return $data;
					}
					else {
						$error = __('<strong>Import FAILED!</strong> Can\'t read uploaded file.', \jcf\JustCustomFields::TEXTDOMAIN);
						$this->addError($error);
					}
				}
				else {
					$error = __('<strong>Import FAILED!</strong> Please upload correct file format.', \jcf\JustCustomFields::TEXTDOMAIN);
					$this->addError($error);
				}
			}
			else {
				$error = __('<strong>Import FAILED!</strong> Import file is missing.', \jcf\JustCustomFields::TEXTDOMAIN);
				$this->addError($error);
			}
		}
	}

	public function importFields()
	{
		$data = $this->import_data;
		$old_fields = $this->_dL->getFields();
		$old_fieldsets = $this->_dL->getFieldsets();

		foreach ( $data as $pt_name => $post_type ) {
			if ( is_array($post_type) && !empty($post_type['fieldsets']) ) {
				foreach ( $post_type['fieldsets'] as $fieldset_id => $fieldset ) {
					$this->title = $fieldset['title'];
					$fieldset_id = !empty($old_fieldsets[$pt_name][$fieldset_id]) ? $fieldset_id : $this->create();
					$old_fieldsets[$pt_name][$fieldset_id]['id'] = $fieldset_id;
					$old_fieldsets[$pt_name][$fieldset_id]['title'] = $fieldset['title'];

					if ( empty($fieldset_id) ) {
						$this->addError(__('Error! Please check <strong>import file</strong>', \jcf\JustCustomFields::TEXTDOMAIN));
						break;
					}
					else {
						if ( !empty($fieldset['fields']) ) {

							if ( !empty($old_fields[$pt_name]) ) {
								foreach ( $old_fields[$pt_name] as $old_field_id => $old_field ) {
									$old_slugs[] = $old_field['slug'];
									$old_field_ids[$old_field['slug']] = $old_field_id;
								}
							}

							foreach ( $fieldset['fields'] as $field_id => $field ) {
								$id_base = preg_replace('/\-([0-9]+)/', '', $field_id);
								$slug_checking = !empty($old_slugs) ? in_array($field['slug'], $old_slugs) : false;
								$new_field_id = !$slug_checking ? $field_id : $old_field_ids[$field['slug']];
								$old_fields[$pt_name][$new_field_id] = $field;
								$old_fieldsets[$pt_name][$fieldset_id]['fields'][$new_field_id] = $field['enabled'];
								
								if ( $id_base == 'collection' ) {
									if ( !empty($old_fields[$pt_name][$new_field_id]['fields']) ) {
										foreach ( $old_fields[$pt_name][$new_field_id]['fields'] as $old_collection_field_id => $old_collection_field ) {
											$old_collection_slugs[] = $old_collection_field['slug'];
											$old_collection_field_ids[$old_collection_field['slug']] = $old_collection_field_id;
										}
									}

									if ( !empty($field['fields']) && is_array($field['fields']) ) {
										foreach ( $field['fields'] as $field_key => $field_values ) {
											$collection_field_slug_checking = !empty($old_collection_slugs) ? in_array($field_values['slug'], $old_collection_slugs) : false;
											$new_collection_field_id = !$collection_field_slug_checking ? $field_key : $old_collection_field_ids[$field_values['slug']];
											$old_fields[$pt_name][$new_field_id]['fields'][$new_collection_field_id] = $field_values;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$this->_dL->setFields($old_fields);
		$this->_dL->setFieldsets($old_fieldsets);
		$import_status = $this->_dL->saveFieldsData() && $this->_dL->saveFieldsetsData();

		if ( $import_status ) {
			$this->addMessage(__('<strong>Import</strong> has been completed successfully!', \jcf\JustCustomFields::TEXTDOMAIN));
		}
		else {
			$this->addError( __('<strong>Import failed!</strong> Please check that your import file has right format.', \jcf\JustCustomFields::TEXTDOMAIN));
		}

		return $import_status;
	}

	/**
	 * Save fieldsets
	 * @param array $fieldsets
	 */
	protected function _save($fieldsets)
	{
		$this->_dL->setFieldsets($fieldsets);
		$this->_dL->saveFieldsetsData();
	}
}

