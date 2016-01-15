<?php

namespace jcf\controllers;
use jcf\models;

class SettingsController {
	
	protected $model;

	public function __construct() {
		add_action('admin_menu', array($this, 'admin_menu') );
		$this->model = new models\Settings();
	}

	public function admin_menu(){
		add_submenu_page(null, 'Settings', 'Settings', 'manage_options', 'jcf_settings', array($this, 'settings_page'));
	}
	
	public function settings_page(){

		if( !empty($_POST['jcf_update_settings']) ) {
			$this->update();
		}
		
		$network = models\Settings::getNetworkMode();
		$source = models\Settings::getDataSourceType();
		$tabs = 'settings';

		// load template
		$tpl_params = array(
			'tabs' => $tabs,
			'network' => $network,
			'source' => $source
		);
		$this->render( 'settings_page.tpl.php', $tpl_params );
	}
	
	public function update() {

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

