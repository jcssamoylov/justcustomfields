<?php

namespace jcf;
use jcf\controllers;

class JustCustomFields {

	const JCF_TEXTDOMAIN = 'just-custom-fields';
	protected $plugin_name;
	protected $version;
	protected $plugin_title;
	
	public function __construct() {

		$this->plugin_name = 'just_custom_fields';
		$this->version = '2.300';
		$this->plugin_title = __('Just Custom Fields', self::JCF_TEXTDOMAIN);
		
		new Autoloader();

		if(!empty($_GET['page'])){
			add_action('admin_print_styles', array($this, 'add_styles'));
			add_action('admin_print_scripts', array($this, 'add_scripts'));
		}

		$this->init_controllers();
	}

	public function init_controllers(){
		if(!empty($_GET['page'])) {
			new controllers\AdminController($this->plugin_name, $this->plugin_title, $this->version);
			//new controllers\FieldsetController($source_settings);
			new controllers\SettingsController($this->plugin_name, $this->plugin_title, $this->version);
			//new controllers\ImportExportController();
		}
		else {
			//new controllers\PostTypeController();
		}
	}
	
	public function add_scripts() {
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

	public function add_styles() {
		wp_register_style($this->plugin_name, WP_PLUGIN_URL.'/just-custom-fields/assets/styles.css');
		wp_enqueue_style($this->plugin_name); 
	}
}

