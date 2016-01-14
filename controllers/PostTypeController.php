<?php

namespace jcf\controllers;
use jcf\models;

class PostTypeController {
	
	private $_dataLayer;
	private $version;
	private $plugin_name;
	private $settings;
	private $_model;
	private $_shortcodes;

	public function __construct($plugin_name, $version, $settings)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings = $settings;
		
		$layer_type = $this->settings->source == JCF_CONF_SOURCE_DB ? 'DB' : 'Files';
		$layer_factory = new models\DataLayerFactory();
		$this->_dataLayer = $layer_factory->create($layer_type, $source_settings);
		
		$this->_model = new models\PostType($this->_dataLayer);
		$this->_shortcodes = new models\Shortcodes($this->_dataLayer);

		add_action('admin_print_styles', array($this, 'enqueue_styles'));
		add_action('admin_print_scripts', array($this, 'enqueue_scripts'));
		
	}

	/**
	 *	add custom scripts to post edit page
	 */
	public function enqueue_scripts(){

		wp_register_script(
				'jcf_edit_post',
				WP_PLUGIN_URL.'/just-custom-fields/assets/edit_post.js',
				array('jquery')
			);
		wp_enqueue_script('jcf_edit_post');

		do_action('jcf_admin_edit_post_scripts');
	}

	/**
	 *	add custom styles to post edit page
	 */
	public function enqueue_styles(){
		wp_register_style('jcf_edit_post', WP_PLUGIN_URL.'/just-custom-fields/assets/edit_post.css');
		wp_enqueue_style('jcf_edit_post');
		
		do_action('jcf_admin_edit_post_styles');
	}
}

