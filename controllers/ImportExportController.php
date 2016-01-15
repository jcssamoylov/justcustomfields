<?php

namespace jcf\controllers;
use jcf\models;

class ImportExportController {
	public function __construct()
	{
		add_submenu_page(null, 'Transfer', 'Transfer', 'manage_options', 'jcf_export_import', array($this, 'transfer_page'));
	}
	
	public function import($data) {
		$_fieldsetsController = new FieldsetController($this->source);
		$_fieldController = new FieldController($this->source);

		foreach($data as $post_type_name => $post_type ){

			if(is_array($post_type) && !empty($post_type['fieldsets'])){
				foreach($post_type['fieldsets'] as $fieldset_id => $fieldset){
					$status_fieldset = $this->addImporFieldset($post_type_name, $fieldset['title'], $fieldset_id);
					if( empty($status_fieldset) ){
						$notice = array('error', 'Error! Please check <strong>import file</strong>');
						break;
					}
					else {
						$fieldset_id = $status_fieldset;
						if(!empty($fieldset['fields'])){
							$old_fields = $this->_dataLayer->get_fields($post_type_name);
							if(!empty($old_fields)){
								foreach($old_fields as $old_field_id => $old_field){
									$old_slugs[] = $old_field['slug'];
									$old_field_ids[$old_field['slug']] = $old_field_id;
								}
							}
							foreach($fieldset['fields'] as $field_id => $field){
								$id_base = preg_replace('/\-([0-9]+)/', '', $field_id);
								$slug_checking = !empty($old_slugs) ? in_array($field['slug'], $old_slugs) : false;
								if($slug_checking){
									$status_field = $this->addImportField($post_type_name, $old_field_ids[$field['slug']], $fieldset_id, $field);
								}else{
									$status_field = $this->addImportField($post_type_name, $field_id, $fieldset_id, $field);
								}
								
								if($id_base == 'collection') {
									$old_collection_fields = $this->_dataLayer->get_fields($post_type_name, $field_id);
									if(!empty($old_collection_fields['fields'])){
										foreach($old_collection_fields['fields'] as $old_collection_field_id => $old_collection_field){
											$old_collection_slugs[] = $old_collection_field['slug'];
											$old_collection_field_ids[$old_collection_field['slug']] = $old_collection_field_id;
										}
									}
									foreach($field['fields'] as $field_key => $field_values){
										$collection_field_slug_checking = !empty($old_collection_slugs) ? in_array($field_values['slug'], $old_collection_slugs) : false;
										if($collection_field_slug_checking) {
											$status_collection_field = $this->addImportField($post_type_name, $old_collection_field_ids[$field_values['slug']], $fieldset_id, $field_values, $field_id);
										}
										else{
											$status_collection_field = $this->addImportField($post_type_name, $field_key, $fieldset_id, $field_values, $field_id);
										}
									}
								}
							}
						}
					}
				}
				if( !empty($status_fieldset) ){
					if( $_POST['file_name'] ){
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
	
	/**
	 *	Add fieldset form import
	 *	@param string $title_fieldset Feildset name
	 *	@param string $slug Fieldset slug
	 *	@return string|boolean Return slug if fieldset has saved and false if not
	 */
	function addImporFieldset($post_type, $title_fieldset='', $slug = ''){
		$title = !empty($title_fieldset) ? $title_fieldset : strip_tags(trim($_POST['title']));
		if( empty($title) ){
			return false;
		}
		if( empty($slug) ) {
			$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $title);
			$slug = 'jcf-fieldset-'.rand(0,10000);
		}

		$fieldsets = $this->_dataLayer->get_fieldsets($post_type);
		if( isset($fieldsets[$slug]) ){
			return $slug;
		}

		// create fiedlset
		$fieldset = array(
			'id' => $slug,
			'title' => $title,
			'fields' => array(),
		);
		$this->_dataLayer->update_fieldsets($post_type, $slug, $fieldset);
		return $slug;
	}

	/**
	 *	Add field from import
	 *	@param string $field_id Field id
	 *	@param string $fieldset_id Fieldset id
	 *	@param array $params Attributes of field
	 *	@return array Attributes of field
	 */
	public function addImportField($post_type, $field_id, $fieldset_id, $params, $collection_id = ''){
		$field_obj = $this->_field_factory->initObject($post_type, $field_id, $fieldset_id, $collection_id);
		$id_base = preg_replace('/\-([0-9]+)/', '', $field_id);
		$field_index = $this->_field_factory->getIndex($id_base);
		if($field_obj->slug == $params['slug']){
			$resp = $field_obj->do_update($field_index, $params);
		}
		else{
			$field_obj = $this->_field_factory->initObject($post_type, $field_id, $fieldset_id, $collection_id);
			$resp = $field_obj->do_update($field_index, $params);
		}

		return $resp;
	}
	
	public function import_form(){
		if( !empty($_POST['action']) && $_POST['action'] == 'jcf_import_fields' ){
			if( !empty($_FILES['import_data']['name']) ){
				$path_info = pathinfo($_FILES['import_data']['name']);

				if( $path_info['extension'] == 'json'){
					$uploaddir = get_home_path() . "wp-content/uploads/";
					$uploadfile = $uploaddir . basename($_FILES['import_data']['name']);

					if ( is_readable($_FILES['import_data']['tmp_name']) ){
						$file_Layer = $this->layer_factory->create('Files', $this->source);
						$post_types = $file_Layer->getDataFromFile($_FILES['import_data']['tmp_name']);
						unlink($_FILES['import_data']['tmp_name']);
						if( empty($post_types) ){
							$notice[] = array('error', __('<strong>Import FAILED!</strong> File do not contain fields settings data..', JCF_TEXTDOMAIN));
						}
					}
					else{
						$notice[] = array('error', __('<strong>Import FAILED!</strong> Can\'t read uploaded file.', JCF_TEXTDOMAIN));
					}
				}
				else{
					$notice[] = array('error', __('<strong>Import FAILED!</strong> Please upload correct file format.', JCF_TEXTDOMAIN));
				}
			}
			else{
				$notice[] = array('error', __('<strong>Import FAILED!</strong> Import file is missing.', JCF_TEXTDOMAIN));
			}
		}
		//if( !empty($notice) )
			//jcf_add_admin_notice($notice[0], $notice[1]);
		
		header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
		include( JCF_ROOT . '/views/import.tpl.php' );
		exit();
	}
	
	public function export(){
		if( $_POST['export_fields'] && !empty($_POST['export_data']) ) {
			$export_data = $_POST['export_data'];
			$export_data = json_encode($export_data);
			$filename = 'jcf_export' . date('Ymd-his') . '.json';
			header('Content-Type: text/json; charset=' . get_bloginfo('charset'));
			header("Content-Disposition: attachment;filename=" . $filename);
			header("Content-Transfer-Encoding: binary ");
			echo $export_data;
			exit();
		}
	}
	
	public function form_export(){
		$jcf_settings = $this->_dataLayer->getAllData();

		$post_types = !empty($jcf_settings['post_types']) ? $jcf_settings['post_types'] : jcf_get_post_types();
		$fieldsets = $jcf_settings['fieldsets'];
		$field_settings = $jcf_settings['field_settings'];

		// load template
		header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
		include( JCF_ROOT . '/views/export.tpl.php' );
		exit();
	}
}

