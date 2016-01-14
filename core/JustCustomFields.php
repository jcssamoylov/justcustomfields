<?php

namespace JCF;
use JCF\JustCustomLoader;
use JCF\controllers\AdminController;
use JCF\controllers\SettingsController;
use JCF\controllers\PostTypeController;

class JustCustomFields {
	
	protected $loader;
	protected $plugin_name;
	protected $version;
	protected $settings;

	
	public function __construct() {
		$this->plugin_name = 'just_custom_fields';
		$this->version = '2.300';
		$this->settings = new SettingsController();
		$this->load_dependencies();
		if(is_admin() && !empty($_GET['page']) && $_GET['page'] == 'just_custom_fields'){
			$this->define_admin_hooks();
		}

		if(!$_GET['page']){
			$this->define_public_hooks();
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		$this->loader = new JustCustomLoader();
	}

	public function get_plugin_name() {
		
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}

	public function get_loader() {
		return $this->loader;
	}
	
	public function get_settings() {
		return $this->settings;
	}
	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new AdminController( $this->get_plugin_name(), $this->get_version(), $this->get_settings() );
	}
	/**
	 * Register all of the hooks related to the public-facing functionality
	 */
	private function define_public_hooks() {
		$plugin_public = new PostTypeController( $this->get_plugin_name(), $this->get_version(), $this->get_settings() );
	}
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}
}

