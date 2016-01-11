<?php

namespace JCF\controllers;
use JCF\models\Setings;
use JCF\models\DataLayerFactory;
use JCF\controllers\FieldController;

class AdminController {
	public $notices = array();
	private $version;
	private $plugin_name;
	private $settings;
	private $_fieldsetsController;
	private $_fieldsController;

	public function __construct($plugin_name, $version, $settings){
		$this->version = $version;
		$this->plugin_name = $plugin_name;
		$this->settings = $settings;
		
		$this->_fieldsetsController = new FieldsetController($this->settings->source);
		$this->_fieldsController = new FieldController($this->settings->source);
		
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

		// save import
		if( !empty($_POST['save_import']) ) {
			$import = $this->settings->import( $_POST['import_data'] );
			if($import['saved']){
				$this->add_notice('notice', __('<strong>Import</strong> has been completed successfully!', JCF_TEXTDOMAIN));
			}
			else{
				
				$notices = array(array('error', __('<strong>Import failed!</strong> Please check that your import file has right format.', JCF_TEXTDOMAIN)));
				$notices[] = $import['notice'];	
			}			
		}

		if( !empty($_POST['jcf_update_settings']) ) {
			$notices = $this->settings->update();
		}

		// add notices 
		if( !empty($notices) ){
			foreach($notices as $notice){
				if(!empty($notice)) $this->add_notice($notice[0], $notice[1]);
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
		$fieldsets = $this->_fieldsetsController->findByPostType($post_type->name);
		$field_settings = $this->_fieldsController->findByPostType($post_type->name);	
		$layer_type = $this->settings->source == JCF_CONF_SOURCE_DB ? 'DB' : 'Files';
		$layer_factory = new DataLayerFactory();
		$data_layer = $layer_factory->create($layer_type, $this->settings->source);
		
		$tabs = 'fields';

		// load template
		$tpl_params = array(
			'tabs' => $tabs,
			'fieldsets' => $fieldsets,
			'post_type' => $post_type,
			'registered_fields' => $this->_fieldsController->registered_fields,
			'field_settings' => $field_settings,
			'data_layer' => $data_layer
		);
		$this->render( JCF_ROOT . '/views/fields_ui.tpl.php', $tpl_params );
	}
	
	public function get_collection_settings_row($post_type, $fieldset_id, $field_id){
		$collection = $this->_fieldsController->_field_factory->initObject($post_type, 'collection', $fieldset_id, $field_id);
		$collection->settings_row($post_type, $field_id, $fieldset_id);
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
		$this->notices[] = array($type, $message);
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
		if( !empty($params) ){
			foreach($params as $key => $value){
				$$key = $value;
			}
		}
		include( $path );
	}
}

