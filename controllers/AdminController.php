<?php

namespace jcf\controllers;
use jcf\models;
use jcf\core;

class AdminController extends core\Controller {

	/**
	 * Init all wp-actions
	 */
	public function __construct()
	{
		add_action('admin_menu', array($this, 'adminMenu') );
		add_action('admin_print_styles', array($this, 'addStyles'));
		add_action('admin_print_scripts', array($this, 'addScripts'));
	}

	/**
	 * Init menu item and index page for plugin
	 */
	public function adminMenu()
	{
		$plugin = new \jcf\JustCustomFields();
		$page_title = $plugin->getPluginTitle();
		$page_slug = $plugin->getPluginName();

		add_options_page( $page_title, $page_title, 'manage_options', $page_slug, array($this, 'index') );
	}

	/**
	 * Render index page
	 */
	public function index()
	{
		$post_types = jcf_get_post_types( 'object' );
		$tab = 'fields';
		$model = new models\Fieldset();
		$count_fields = $model->getCountFields();

		// load template
		$template_params = array(
			'tab' => $tab,
			'post_types' => $post_types,
			'count_fields' => $count_fields
		);

		$this->_render( 'admin_page', $template_params );
	}

	/**
	 *	Include scripts
	 */
	public function addScripts()
	{
		$plugin = new \jcf\JustCustomFields();
		$slug = $plugin->getPluginName();
		wp_register_script(
			$slug,
			WP_PLUGIN_URL.'/just-custom-fields/assets/just_custom_fields.js',
			array('jquery', 'json2', 'jquery-form', 'jquery-ui-sortable')
		);
		wp_enqueue_script($slug);
		wp_enqueue_script('jquery-ui-autocomplete');

		// add text domain
		wp_localize_script( $slug, 'jcf_textdomain', jcf_get_language_strings() );
 	}

	/**
	 * Include styles
	 */
	public function addStyles()
	{
		$plugin = new \jcf\JustCustomFields();
		$slug = $plugin->getPluginName();
		wp_register_style($slug, WP_PLUGIN_URL.'/just-custom-fields/assets/styles.css');
		wp_enqueue_style($slug);
 	}
}

