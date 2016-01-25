<?php

namespace jcf\models;
use jcf\core;
use jcf\models;

class Fieldset extends core\Model {
	
	protected $_layer;

	public function __construct()
	{
		parent::__construct();
		$layer_factory = new DataLayerFactory();
		$this->_layer = $layer_factory->create();
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
	 * @return array
	 */
	public function findAll()
	{
		return $this->_layer->getAllFields();
	}

	/**
	 * Get fields and fieldsets by post_type
	 * @param string $post_type Name post type
	 * @return array
	 */
	public function findByPostType($post_type)
	{
		$field_factory = new models\JustFieldFactory();
		$registered_fields = $field_factory->getRegisteredFields();
		$fieldsets = $this->_layer->getFieldsets($post_type);
		$fields = $this->_layer->getFields($post_type);
		$collections = array();

		if ( !empty($fields) ) {
			foreach ( $fields as $field_id => $field ) {
				if ( preg_replace('/\-[0-9]+$/', '', $field_id) != 'collection' ) continue;
				$collecton = $field_factory->initObject($post_type, $field_id);
				$collections['registered_fields'] = $collecton->_fieldFactory->getRegisteredFields();
				$collections[$field_id] = $this->_layer->getFields($post_type, $field_id);
			}
		}

		$data = array(
			'fieldsets' => $fieldsets,
			'fields' => $fields,
			'collections' => $collections,
			'registered_fields' => $registered_fields
		);
		return $data;
	}

	/**
	 * Get fieldset by ID
	 */
	public function findFieldsetById($post_type, $fieldset_id)
	{
		return $this->_layer->getFieldsets($post_type, $fieldset_id);
	}

	/**
	 * Create new fieldset with $this->_request params
	 */
	public function createFieldset()
	{
		$title = strip_tags(trim($this->_request['title']));
		$post_type = strip_tags(trim($this->_request['post_type']));

		if ( empty($title) ) {
			jcf_ajax_response( array('status' => "0", 'error'=>__('Title field is required.', \jcf\JustCustomFields::TEXTDOMAIN)) );
		}
		
		$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $title);
		$trimed_slug = trim($slug);

		if ( $trimed_slug == '' ) {
			$slug = 'jcf-fieldset-'.rand(0,10000);
		}
		else {
			$slug = sanitize_title( $title );
		}

		$fieldsets = $this->_layer->getFieldsets($post_type);

		// check exists
		if ( isset($fieldsets[$slug]) ) {
			jcf_ajax_response( array('status' => "0", 'error'=>__('Such fieldset already exists.', \jcf\JustCustomFields::TEXTDOMAIN)) );
		}

		// create fiedlset
		$fieldset = array(
			'id' => $slug,
			'title' => $title,
			'fields' => array(),
		);
		$this->_layer->updateFieldsets($post_type, $slug, $fieldset);
		jcf_ajax_response( array('status' => "1" ) ); 
	}

	/**
	 * Delete fieldset with $this->_request params
	 */
	public function deleteFieldset()
	{
		$post_type = strip_tags(trim($this->_request['post_type']));
		$f_id = $this->_request['fieldset_id'];

		if ( empty($f_id) ) {
			jcf_ajax_response( array('status' => "0", 'error'=>__('Wrong params passed.', \jcf\JustCustomFields::TEXTDOMAIN)) );
		}

		$this->_layer->updateFieldsets($post_type, $f_id, NULL);
		jcf_ajax_response( array('status' => "1") );
	}

	/**
	 * Update fieldset with $this->_request params
	 */
	public function updateFieldset()
	{
		$fieldset_id = $this->_request['fieldset_id'];
		$post_type = strip_tags(trim($this->_request['post_type']));
		$fieldset = $this->_layer->getFieldsets($post_type, $fieldset_id);

		if ( empty($fieldset) ) {
			jcf_ajax_response( array('status' => "0", 'error'=>__('Wrong data passed.', \jcf\JustCustomFields::TEXTDOMAIN)) );
		}

		$title = strip_tags(trim($this->_request['title']));

		if ( empty($title) ) {
			jcf_ajax_response( array('status' => "0", 'error'=>__('Title field is required.', \jcf\JustCustomFields::TEXTDOMAIN)) );
		}

		$fieldset['title'] = $title;
		$this->_layer->updateFieldsets($post_type, $fieldset_id, $fieldset);
		jcf_ajax_response( array('status' => "1", 'title' => $title) );
	}

	/**
	 * Sort fieldsets with $this->_request params
	 */
	public function sortFieldsets()
	{
		$post_type = strip_tags(trim($this->_request['post_type']));
		$order  = explode(',' ,trim($this->_request['fieldsets_order'], ','));

		if ( !empty($this->_request['fieldsets_order']) ) {
			$this->_layer->sortFieldsets($post_type, $order);
		}

		$resp = array('status' => '1');
		jcf_ajax_response($resp, 'json');
	}

	/**
	 * Get form data for visibility rules form
	 * @return array
	 */
	public function getVisibilityRulesForm()
	{
		$output = array();
		$post_type = $this->_request['post_type'];
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		$add_rule = !empty($this->_request['add_rule']) ? $this->_request['add_rule'] : false;

		$output['post_type'] = $post_type;
		$output['taxonomies'] = $taxonomies;
		$output['add_rule'] = $add_rule;

		if ( !empty($this->_request['edit_rule'] ) ) {
			$rule_id = $this->_request['rule_id'] - 1;
			$fieldset_id = $this->_request['fieldset_id'];
			$fieldset = $this->_layer->getFieldsets($post_type, $fieldset_id);
			$edit_rule = $this->_request['edit_rule']; 
			$visibility_rule = $fieldset['visibility_rules'][$rule_id];

			if ( $visibility_rule['based_on'] == 'taxonomy' ) {
				$terms = get_terms($visibility_rule['rule_taxonomy'], array('hide_empty' => false));
				$output['terms'] = $terms;
			}
			else {
				$templates = get_page_templates();
				$output['templates'] = $templates;
			}

			$output['rule_id'] = $rule_id;
			$output['fieldset_id'] = $fieldset_id;
			$output['fieldset'] = $fieldset;
			$output['visibility_rule'] = $visibility_rule;
			$output['edit_rule'] = $edit_rule;
		}

		return $output;
	}

	/**
	 * Get visibility rules for fieldset with $this->_request
	 */
	public function getVisibilityRules()
	{
		$rule = $this->_request['rule'];
		$post_type = $this->_request['post_type'];
		if ( $rule == 'page_template' ) {
			$type = 'page_template';
			$templates = get_page_templates();
			$output = $templates;
		}
		else {
			$type = 'taxonomies';
			$taxonomies = get_object_taxonomies( $post_type, 'objects' );
			$output = $taxonomies;
		}

		return array('type' => $type, 'data' => $output);
	}

	/**
	 * Save visibility rule
	 * @return array
	 */
	public function saveVisibilityRules()
	{
		$post_type = $this->_request['post_type'];

		if ( !empty($this->_request['rule_id']) ) {
			$this->_layer->updateFieldsets($post_type, $this->_request['fieldset_id'], array('rules' => array('update' => $this->_request['rule_id'], 'data' => $this->_request['visibility_rules'])));
		}
		else {
			$this->_layer->updateFieldsets($post_type, $this->_request['fieldset_id'], array('rules' => $this->_request['visibility_rules']));
		}

		$fieldset = $this->_layer->getFieldsets($post_type, $this->_request['fieldset_id']);
		return $fieldset['visibility_rules'];
	}

	/**
	 * Delete visibility rule
	 * @return array
	 */
	public function deleteVisibilityRules()
	{
		$post_type = $this->_request['post_type'];
		$this->_layer->updateFieldsets($post_type, $this->_request['fieldset_id'], array('rules' => array('remove' => $this->_request['rule_id'])));
		$fieldset = $this->_layer->getFieldsets($post_type, $this->_request['fieldset_id']);
		return $fieldset['visibility_rules'];
	}

	/**
	 * 
	 */
	public function getVisibilityAutocompleteData()
	{
		global $wpdb;
		$taxonomy = $this->_request['taxonomy'];
		$term = $this->_request['term'];

		$query = "SELECT t.term_id, t.name
			FROM wp_terms AS t
			LEFT JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id
			WHERE t.name LIKE '%$term%' AND tt.taxonomy = '$taxonomy'";
		$terms = $wpdb->get_results($query);
		$response = array();

		foreach ( $terms as $p ) {
			$response[] = array(
				'id' => $p->term_id,
				'label' => $p->name,
				'value' => $p->name,
			);
		}
		$json = json_encode($response);

		header( "Content-Type: application/json; charset=" . get_bloginfo('charset') );
		echo $json;
		exit();
	}

	/**
	 * Export fields
	 */
	public function exportFields()
	{
		if ( $this->_request['export_fields'] && !empty($this->_request['export_data']) ) {
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

	public function importFields()
	{
		$data = $this->_request['import_data'];

		foreach ( $data as $post_type_name => $post_type ) {
			if ( is_array($post_type) && !empty($post_type['fieldsets']) ) {
				foreach ( $post_type['fieldsets'] as $fieldset_id => $fieldset ) {
					$status_fieldset = $this->_addImporFieldset($post_type_name, $fieldset['title'], $fieldset_id);

					if ( empty($status_fieldset) ) {
						$this->addError(__('Error! Please check <strong>import file</strong>', \jcf\JustCustomFields::TEXTDOMAIN));
						break;
					}
					else {
						$fieldset_id = $status_fieldset;

						if ( !empty($fieldset['fields']) ) {
							$old_fields = $this->_layer->getFields($post_type_name);

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
									$status_field = $this->_addImportField($post_type_name, $old_field_ids[$field['slug']], $fieldset_id, $field);
								}
								else {
									$status_field = $this->_addImportField($post_type_name, $field_id, $fieldset_id, $field);
								}
								
								if ( $id_base == 'collection' ) {
									$old_collection_fields = $this->_layer->getFields($post_type_name, $field_id);

									if ( !empty($old_collection_fields['fields']) ) {
										foreach ( $old_collection_fields['fields'] as $old_collection_field_id => $old_collection_field ) {
											$old_collection_slugs[] = $old_collection_field['slug'];
											$old_collection_field_ids[$old_collection_field['slug']] = $old_collection_field_id;
										}
									}
									foreach ( $field['fields'] as $field_key => $field_values ) {
										$collection_field_slug_checking = !empty($old_collection_slugs) ? in_array($field_values['slug'], $old_collection_slugs) : false;

										if ( $collection_field_slug_checking ) {
											$status_collection_field = $this->_addImportField($post_type_name, $old_collection_field_ids[$field_values['slug']], $fieldset_id, $field_values, $field_id);
										}
										else {
											$status_collection_field = $this->_addImportField($post_type_name, $field_key, $fieldset_id, $field_values, $field_id);
										}
									}
								}
							}
						}
					}
				}

				if ( !empty($status_fieldset) ) {
					if ( $this->_request['file_name'] ) {
						unlink($this->_request['file_name']);
					}
				}
			}
		}

		if ( $status_fieldset ) {
			$this->addMessage(__('<strong>Import</strong> has been completed successfully!', \jcf\JustCustomFields::TEXTDOMAIN));
		}
		else {
			$this->addError( __('<strong>Import failed!</strong> Please check that your import file has right format.', \jcf\JustCustomFields::TEXTDOMAIN));
		}	
		return $saved;
	}

	/**
	 *	Add fieldset form import
	 *	@param string $title_fieldset Feildset name
	 *	@param string $slug Fieldset slug
	 *	@return string|boolean Return slug if fieldset has saved and false if not
	 */
	protected function _addImporFieldset($post_type, $title_fieldset='', $slug = '')
	{
		$title = !empty($title_fieldset) ? $title_fieldset : strip_tags(trim($this->_request['title']));
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
		$model = new models\JustFieldFactory();
		$field_obj = $model->initObject($post_type, $field_id, $fieldset_id, $collection_id);
		$id_base = preg_replace('/\-([0-9]+)/', '', $field_id);
		$field_index = $model->getIndex($id_base);

		if ( $field_obj->slug == $params['slug'] ) {
			$resp = $field_obj->doUpdate($field_index, $params);
		}
		else {
			$field_obj = $model->initObject($post_type, $field_id, $fieldset_id, $collection_id);
			$resp = $field_obj->doUpdate($field_index, $params);
		}
		return $resp;
	}

}

