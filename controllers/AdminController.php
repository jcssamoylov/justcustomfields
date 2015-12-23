<?php

namespace JCF\controllers;
use JCF\models\Setings;
use JCF\controllers\FieldController;

class AdminController {
	public $notices = array();
	protected $version;
	protected $plugin_name;
	protected $settings;

	public function __construct($plugin_name, $version, $settings){
		$this->version = $version;
		$this->plugin_name = $plugin_name;
		$this->settings = $settings;

		add_action('admin_menu', array($this, 'admin_menu') );
		add_action('jcf_print_admin_notice', array($this, 'print_notice') );
	}
	
	public function admin_menu(){
		add_options_page(__('Just Custom Fields', JCF_TEXTDOMAIN), __('Just Custom Fields', JCF_TEXTDOMAIN), 'manage_options', 'just_custom_fields', array($this, 'settings_page') );
	}
	
	public function settings_page(){
		$post_types = jcf_get_post_types( 'object' );
		$tabs = !isset($_GET['tab']) ? 'fields' : $_GET['tab'];

		// edit page
		if( !empty($_GET['pt']) && isset($post_types[ $_GET['pt'] ]) ){
			$this->fields_page( $post_types[ $_GET['pt'] ] );
			return;
		}

		if( !empty($_POST['save_import']) ) {
			$import = $this->settings->import( $_POST['import_data'] );
			
			$notices = $import['saved'] ? 
					array('notice', __('<strong>Import</strong> has been completed successfully!', JCF_TEXTDOMAIN)) : 
					array('error', __('<strong>Import failed!</strong> Please check that your import file has right format.', JCF_TEXTDOMAIN));
			array_push($notices, $import['notice']);
			foreach($notices as $notice){
				$this->add_notice($notice[0], $notice[1]);
			}
		}

		if( !empty($_POST['jcf_update_settings']) ) {
			$notices = $this->settings->update();
			foreach($notices as $notice){
				$this->add_notice($notice[0], $notice[1]);
			}
			
		}
		
		// load template
		$tpl_params = array(
			'tabs' => $tabs,
			'post_types' => $post_types
		);
		$this->render( JCF_ROOT . '/views/settings_page.tpl.php', $tpl_params );
	}
	
	public function fields_page($post_type){
		jcf_set_post_type( $post_type->name );
		$_fieldsetsController = new FieldsetController($this->settings->source);
		$_fieldsController = new FieldController($this->settings->source);

		$fieldsets = $_fieldsetsController->findAll();
		$field_settings = $_fieldsController->findAll($post_type->name);	

		$tabs = 'fields';

		// load template
		// load template
		$tpl_params = array(
			'tabs' => $tabs,
			'post_types' => $post_types,
			'fieldsets' => $fieldsets,
			'field_settings' => $field_settings
		);
		$this->render( JCF_ROOT . '/views/fields_ui.tpl.php', $params );
	}
	
	public function enqueue_scripts(){
		wp_register_script(
			$this->plugin_name,
			WP_PLUGIN_URL.'/just-custom-fields/assets/just_custom_fields.js',
			array('jquery', 'json2', 'jquery-form', 'jquery-ui-sortable')
		);
		wp_enqueue_script($this->plugin_name);
		wp_enqueue_script('jquery-ui-autocomplete');

		// add text domain
		wp_localize_script( $this->plugin_name, 'jcf_textdomain', jcf_get_language_strings() );
	}
	
	public function enqueue_styles(){
		wp_register_style($this->plugin_name, WP_PLUGIN_URL.'/just-custom-fields/assets/styles.css');
		wp_enqueue_style($this->plugin_name); 
	}
	
	public function add_notice($type, $message){
		$this->notices += array($type, $message);
	}
	
	public function print_notice($args = array()){

		if( empty($this->notices) ) return;
		
		foreach($this->notices as $msg)
		{
			if(!empty($msg)){
				$tpl_params = array('msg' => $msg);
				$this->render(JCF_ROOT . '/views/notices.tpl.php', $tpl_params);
			}
		}
	}
	
	protected function render($path, $params){

		foreach($params as $key => $value){
			$$key = $value;
		}

		include( $path );
	}
}

