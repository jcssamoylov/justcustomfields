<?php

namespace jcf\controllers;
use jcf\models;

class AdminController {

	protected $plugin_name;
	protected $plugin_title;
	protected $version;
	protected $model;

	public function __construct($plugin_name, $plugin_title, $version) {

		add_action('admin_menu', array($this, 'admin_menu') );
		
		$this->plugin_name = $plugin_name;
		$this->plugin_title = $plugin_title;
		$this->version = $version;
		$this->model = new models\Fieldset();
	}
	
	public function admin_menu(){
		add_options_page($this->plugin_title, $this->plugin_title, 'manage_options', $this->plugin_name, array($this, 'init_page') );
	}

	public function init_page() {
		$post_types = jcf_get_post_types( 'object' );
		$tabs = 'fields';

		// load template
		$tpl_params = array(
			'tabs' => $tabs,
			'post_types' => $post_types
		);
		$this->render( 'admin_page.tpl.php', $tpl_params );
	}

	protected function render($template, $params) {
		if( !empty($params) ){
			foreach($params as $key => $value){
				$$key = $value;
			}
		}
		include( JCF_ROOT . '/views/' . $template );
	}
}

