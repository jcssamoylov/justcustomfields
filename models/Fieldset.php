<?php

namespace jcf\models;
use jcf\core;
use jcf\models;

class Fieldset extends core\Model  {
	
	protected $_layer;

	public function __construct()
	{
		parent::__construct();
		$layer_factory = new DataLayerFactory();
		$this->_layer = $layer_factory->create();
		$this->_fieldFactory = new models\JustFieldFactory();
	}

	/**
	 * return number of registered fields and fieldsets for specific post type
	 * @param string $post_type
	 * @return int
	 */
	public function getCountFields()
	{
		$all_settings = $this->_layer->getAllFields();
		$fieldsets = array();

		if ( isset($all_settings['fieldsets']) ) {
			$fieldsets = $all_settings['fieldsets'];
		}
		
		if ( !empty($fieldsets) ) {
			foreach ( $fieldsets as $post_type => $values ) {
				$count[$post_type]['fieldsets'] = count($values);
				$count[$post_type]['fields'] = 0;
				foreach ( $values as $fieldset ) {
					if ( !empty($fieldset['fields']) ) {
						$count[$post_type]['fields'] += count($fieldset['fields']);
					}
				}
			}
		}
		else {
			$post_types = jcf_get_post_types();
			foreach ( $post_types as $key => $post_type ) {
				$count[$post_type->name]['fieldsets'] = 0;
				$count[$post_type->name]['fields'] = 0;
			}
		}
		return $count;
	}

	/**
	 * Get all fields and fieldsets
	 */
	public function findAll()
	{
		return $this->_layer->getAllFields();
	}

	/**
	 * Export fields
	 */
	public function exportFields()
	{
		if( $this->_request['export_fields'] && !empty($this->_request['export_data']) ) {
			$export_data = $this->_request['export_data'];
			$export_data = json_encode($export_data);
			$filename = 'jcf_export' . date('Ymd-his') . '.json';
			header('Content-Type: text/json; charset=' . get_bloginfo('charset'));
			header("Content-Disposition: attachment;filename=" . $filename);
			header("Content-Transfer-Encoding: binary ");
			echo $export_data;
			exit();
		}
	}
	
	/**
	 * Get fields for import
	 */
	public function getImportFields()
	{
		if ( !empty($this->_request['action']) && $this->_request['action'] == 'jcf_import_fields' ) {
			if ( !empty($_FILES['import_data']['name']) ) {
				$path_info = pathinfo($_FILES['import_data']['name']);

				if( $path_info['extension'] == 'json'){
					$uploaddir = get_home_path() . "wp-content/uploads/";
					$uploadfile = $uploaddir . basename($_FILES['import_data']['name']);

					if ( is_readable($_FILES['import_data']['tmp_name']) ) {
						$layer_factory = new models\DataLayerFactory();
						$file_Layer = $layer_factory->create('Files');
						$all_fields['post_types'] = $file_Layer->getDataFromFile($_FILES['import_data']['tmp_name']);
						unlink($_FILES['import_data']['tmp_name']);
						if ( empty($all_fields) ) {
							$error = __('<strong>Import FAILED!</strong> File do not contain fields settings data..', \jcf\JustCustomFields::TEXTDOMAIN);
							$this->addError($error);
						}
						return $all_fields;
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
	
	/**
	 *	Add fieldset form import
	 *	@param string $title_fieldset Feildset name
	 *	@param string $slug Fieldset slug
	 *	@return string|boolean Return slug if fieldset has saved and false if not
	 */
	public function _addImporFieldset($post_type, $title_fieldset='', $slug = '')
	{
		$title = !empty($title_fieldset) ? $title_fieldset : strip_tags(trim($_POST['title']));
		if ( empty($title) ) {
			return false;
		}

		if ( empty($slug) ) {
			$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $title);
			$slug = 'jcf-fieldset-'.rand(0,10000);
		}

		$fieldsets = $this->_layer->getFieldsets($post_type);
		if ( isset($fieldsets[$slug]) ) {
			return $slug;
		}

		// create fiedlset
		$fieldset = array(
			'id' => $slug,
			'title' => $title,
			'fields' => array(),
		);
		$this->_layer->updateFieldsets($post_type, $slug, $fieldset);
		return $slug;
	}

	/**
	 *	Add field from import
	 *	@param string $field_id Field id
	 *	@param string $fieldset_id Fieldset id
	 *	@param array $params Attributes of field
	 *	@return array Attributes of field
	 */
	protected function _addImportField($post_type, $field_id, $fieldset_id, $params, $collection_id = '')
	{
		$field_obj = $this->_fieldFactory->initObject($post_type, $field_id, $fieldset_id, $collection_id);
		$id_base = preg_replace('/\-([0-9]+)/', '', $field_id);
		$field_index = $this->_fieldFactory->getIndex($id_base);

		if ( $field_obj->slug == $params['slug'] ) {
			$resp = $field_obj->do_update($field_index, $params);
		}
		else {
			$field_obj = $this->_fieldFactory->initObject($post_type, $field_id, $fieldset_id, $collection_id);
			$resp = $field_obj->do_update($field_index, $params);
		}
		return $resp;
	}
	
	
	public function importFields($data)
	{
		$data = $this->_request['import_data'];

		foreach ( $data as $post_type_name => $post_type ) {
			if ( is_array($post_type) && !empty($post_type['fieldsets']) ) {
				foreach ( $post_type['fieldsets'] as $fieldset_id => $fieldset ) {
					$status_fieldset = $this->addImporFieldset($post_type_name, $fieldset['title'], $fieldset_id);

					if ( empty($status_fieldset) ) {
						$notice = array('error', 'Error! Please check <strong>import file</strong>');
						break;
					}
					else {
						$fieldset_id = $status_fieldset;

						if ( !empty($fieldset['fields']) ) {
							$old_fields = $this->_dataLayer->get_fields($post_type_name);

							if ( !empty($old_fields) ) {
								foreach ( $old_fields as $old_field_id => $old_field ) {
									$old_slugs[] = $old_field['slug'];
									$old_field_ids[$old_field['slug']] = $old_field_id;
								}
							}
							foreach ( $fieldset['fields'] as $field_id => $field ) {
								$id_base = preg_replace('/\-([0-9]+)/', '', $field_id);
								$slug_checking = !empty($old_slugs) ? in_array($field['slug'], $old_slugs) : false;

								if ( $slug_checking ) {
									$status_field = $this->addImportField($post_type_name, $old_field_ids[$field['slug']], $fieldset_id, $field);
								}
								else {
									$status_field = $this->addImportField($post_type_name, $field_id, $fieldset_id, $field);
								}
								
								if ( $id_base == 'collection' ) {
									$old_collection_fields = $this->_dataLayer->get_fields($post_type_name, $field_id);

									if ( !empty($old_collection_fields['fields']) ) {
										foreach ( $old_collection_fields['fields'] as $old_collection_field_id => $old_collection_field ) {
											$old_collection_slugs[] = $old_collection_field['slug'];
											$old_collection_field_ids[$old_collection_field['slug']] = $old_collection_field_id;
										}
									}
									foreach ( $field['fields'] as $field_key => $field_values ) {
										$collection_field_slug_checking = !empty($old_collection_slugs) ? in_array($field_values['slug'], $old_collection_slugs) : false;

										if ( $collection_field_slug_checking ) {
											$status_collection_field = $this->addImportField($post_type_name, $old_collection_field_ids[$field_values['slug']], $fieldset_id, $field_values, $field_id);
										}
										else {
											$status_collection_field = $this->addImportField($post_type_name, $field_key, $fieldset_id, $field_values, $field_id);
										}
									}
								}
							}
						}
					}
				}

				if ( !empty($status_fieldset) ) {
					if ( $_POST['file_name'] ) {
						unlink($_POST['file_name']);
					}
				}
			}
		}

		$output = array(
			'saved' => $status_fieldset,
			'notice' => $notice
		);
		return $output;
	}
}

