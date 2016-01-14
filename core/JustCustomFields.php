<?php

namespace jcf;
use jcf\controllers;

class JustCustomFields {

	const JCF_TEXTDOMAIN = 'just-custom-fields';
	protected $plugin_name;
	protected $version;
	protected $plugin_title;
	
	public function __construct() {
		add_action('admin_menu', array($this, 'admin_menu') );

		$this->plugin_name = 'just_custom_fields';
		$this->version = '2.300';
		$this->plugin_title = __('Just Custom Fields', self::JCF_TEXTDOMAIN);
		
		new Autoloader();

		if(!empty($_GET['page'])){
			add_action('admin_print_styles', array($this, 'add_styles'));
			add_action('admin_print_scripts', array($this, 'add_scripts'));
		}
	}

	public function admin_menu(){
		add_options_page($this->plugin_title, $this->plugin_title, 'manage_options', $this->plugin_name, array($this, 'admin_page') );		
		add_submenu_page(null, 'Fields', 'Fields', 'manage_options', 'jcf_fields', array($this, 'fields_page'));
		add_submenu_page(null, 'Settings', 'Settings', 'manage_options', 'jcf_settings', array($this, 'settings_page'));
		add_submenu_page(null, 'Transfer', 'Transfer', 'manage_options', 'jcf_transfer', array($this, 'transfer_page'));
	}

	public function admin_page(){
		$admin_page = new controllers\AdminController($this->plugin_name);
		$admin_page->run();
	}
	
	public function fields_page(){
		$field_page = new controllers\FieldsetController($source_settings);
	}
	
	public function settings_page(){
		$setting_page = new controllers\SettingsController();
	}

	public function transfer_page(){
		$transfer_page = new controllers\TransferController();
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

