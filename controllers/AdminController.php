<?php

namespace jcf\controllers;
use jcf\models;

class AdminController {


	public function __construct($plugin_name, $plugin_title, $version) {

		add_action('admin_menu', array($this, 'admin_menu') );
	}
	
	public function admin_menu(){
		add_options_page($this->plugin_title, $this->plugin_title, 'manage_options', $this->plugin_name, array($this, 'index') );
	}

	public function index() {
		$post_types = jcf_get_post_types( 'object' );
		$tabs = 'fields';

		// load template
		$tpl_params = array(
			'tabs' => $tabs,
			'post_types' => $post_types
		);
		$this->render( 'admin_page.tpl.php', $tpl_params );
	}

	protected function _render($template, $params) {
		if( !empty($params) ){
			foreach($params as $key => $value){
				$$key = $value;
			}
		}
		include( JCF_ROOT . '/views/' . $template );
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

