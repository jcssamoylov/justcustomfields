<?php

namespace jcf\controllers;
use jcf\models;

class SettingsController {


	public function __construct() {
		add_action('admin_menu', array($this, 'initRoutes') );
		add_action('wp_ajax_jcf_check_file', array($this, 'check_file'));
	}

	public function initRoutes(){
		add_submenu_page(null, 'Settings', 'Settings', 'manage_options', 'jcf_settings', array($this, 'settings_page'));
	}
	
	public function settings_page(){
		
		$model = new Settings();
		
		$model->load($_POST) && $model->save();
		
		$tab = 'settings';

		// load template
		$this->render( 'settings_page', array(
			'tab' => $tab,
			'model' => $model,
		));
	}
	
	protected function _update() {

		$new_source = $_POST['jcf_read_settings'];
		$new_network = $_POST['jcf_multisite_setting'];

		if( MULTISITE ){
			$update_network = $this->model->updateNetworkMode( $new_network );
		}

		$update_source = $this->model->updateDataSource( $new_source, $new_network);
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

