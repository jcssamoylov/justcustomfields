<?php

namespace JCF\controllers;
use JCF\models\Settings;
use JCF\models\DataLayerFactory;

class SettingsController {
	public $source;
	public $network;
	protected $_model;
	public $_dbLayer;
	public $_fileLayer;

	public function __construct() {
		$this->_model = new Settings();
		$this->source = $this->_model->getDataSourceType();
		$this->network = $this->_model->getNetworkMode();
		$layerFactory = new DataLayerFactory();
		$this->_dbLayer = $layerFactory->create('\JCF\models\DBDataLayer');
		$this->_fileLayer = $layerFactory->create('\JCF\models\FilesDataLayer');

		add_action('wp_ajax_jcf_check_file', array($this, 'check_file'));
		add_action('wp_ajax_jcf_export_fields', array($this, 'export') );
		add_action('wp_ajax_jcf_export_fields_form', array($this, 'form_export') );
		add_action('wp_ajax_jcf_import_fields', array($this, 'import_form') );
	}
	
	public function update() {
		$notices = array();
		$new_source = $_POST['jcf_read_settings'];
		$new_network = $_POST['jcf_multisite_setting'];

		if( MULTISITE ){
			$update_network = $this->_model->updateNetworkMode( $new_network );
			$this->network = $update_network['value'];
			array_push($update_network['notice'], $notices);
		}
		
		$update_source = $this->_model->updateDataSource( $new_source, $new_network, $this->_fileLayer );
		$this->source = $update_source['source'];

		array_push($notices, $update_source['notice']);
		return $notices;
	}
	
	public function import($data) {
		$_fieldController = new FieldController($this->source);
		$_fieldController = new FieldController($this->source);
		foreach($data as $key => $post_type ){
			jcf_set_post_type($key);
			if(is_array($post_type) && !empty($post_type['fieldsets'])){
				foreach($post_type['fieldsets'] as $fieldset_id => $fieldset){
					$status_fieldset = jcf_import_add_fieldset($fieldset['title'], $fieldset_id);
					if( empty($status_fieldset) ){
						$notice[] = array('error' => 'Error! Please check <strong>import file</strong>');
						return false;
					}else{
						$fieldset_id = $status_fieldset;
						if(!empty($fieldset['fields'])){
							$old_fields = jcf_field_settings_get();
							if(!empty($old_fields)){
								foreach($old_fields as $old_field_id => $old_field){
									$old_slugs[] = $old_field['slug'];
									$old_field_ids[$old_field['slug']] = $old_field_id;
								}
							}
							foreach($fieldset['fields'] as $field_id => $field){
								$slug_checking = !empty($old_slugs) ? in_array($field['slug'], $old_slugs) : false;
								if($slug_checking){
									$status_field = jcf_import_add_field($old_field_ids[$field['slug']], $fieldset_id, $field);
								}else{
									$status_field = jcf_import_add_field($field_id, $fieldset_id, $field);
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
	
	public function import_form(){
		if( !empty($_POST['action']) && $_POST['action'] == 'jcf_import_fields' ){
			if( !empty($_FILES['import_data']['name']) ){
				$path_info = pathinfo($_FILES['import_data']['name']);

				if( $path_info['extension'] == 'json'){
					$uploaddir = get_home_path() . "wp-content/uploads/";
					$uploadfile = $uploaddir . basename($_FILES['import_data']['name']);

					if ( is_readable($_FILES['import_data']['tmp_name']) ){
						$post_types = $this->_fileLayer->getDataFromFile($_FILES['import_data']['tmp_name']);
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
		if( !empty($notice) )
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
		if( $this->source != JCF_CONF_SOURCE_DB ){
			$jcf_settings = $this->_fileLayer->getAllData($this->source);
		}
		else{
			$jcf_settings = $this->_dbLayer->getAllData();
		}

		$post_types = !empty($jcf_settings['post_types']) ? $jcf_settings['post_types'] : jcf_get_post_types();
		$fieldsets = $jcf_settings['fieldsets'];
		$field_settings = $jcf_settings['field_settings'];

		// load template
		header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
		include( JCF_ROOT . '/views/export.tpl.php' );
		exit();
	}

	public function check_file(){
		$settings_source = $_POST['jcf_read_settings'];

		if($settings_source == JCF_CONF_SOURCE_FS_THEME OR $settings_source == JCF_CONF_SOURCE_FS_GLOBAL){
			$file = $this->_fileLayer->getConfigFilePath($settings_source);
			
			if($settings_source == JCF_CONF_SOURCE_FS_THEME){
				$msg = __("The settings will be written to your theme folder.\nIn case you have settings there, they will be overwritten.\nPlease confirm that you want to continue.", JCF_TEXTDOMAIN);
			}
			else {
				$msg = __("The settings will be written to folder wp-conten/jcf-settings.\nIn case you have settings there, they will be overwritten.\nPlease confirm that you want to continue.", JCF_TEXTDOMAIN);
			}
			
			if( file_exists( $file ) ) {
				$resp = array('status' => '1', 'msg' => $msg);
			}
			else {
				$resp = array('status' => '1', 'file' => '1');
			}
		}
		else {
			$resp = array('status' => '1');
		}
		jcf_ajax_reponse($resp, 'json');
	}
}

