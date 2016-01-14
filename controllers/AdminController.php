<?php

namespace jcf\controllers;
use jcf\models;

class AdminController {

	protected $plugin_name;
	protected $model;

	public function __construct($plugin_name) {

		$this->plugin_name = $plugin_name;
		$this->model = new models\Fieldset();

	}

	public function run() {
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

