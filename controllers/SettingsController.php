<?php

namespace jcf\controllers;
use jcf\models;
use jcf\core;

class SettingsController extends core\Controller {

	/**
	 * Init all wp-actions
	 */
	public function __construct() 
	{
		add_action('admin_menu', array($this, 'initRoutes') );
	}

	/**
	 * Init routes for settings page
	 */
	public function initRoutes()
	{
		$page_title = __('Settings', \jcf\JustCustomFields::TEXTDOMAIN);
		$page_slug = 'jcf_settings';
		add_submenu_page(null, $page_title, $page_title, 'manage_options', $page_slug, array($this, 'initPage'));
	}

	/**
	 * Render settings page
	 */
	public function initPage()
	{
		$tab = 'settings';
		$model = new models\Settings();
		$model->load($_POST) && $model->save();

		// load template
		$template_params = array(
			'tab' => $tab,
			'model' => $model
		);
		$this->_render( '/views/settings/settings_page', $template_params );
	}
}

