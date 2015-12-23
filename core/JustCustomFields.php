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
		$this->define_admin_hooks();
		$this->define_public_hooks();
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
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}
	/**
	 * Register all of the hooks related to the public-facing functionality
	 */
	private function define_public_hooks() {
		$plugin_public = new PostTypeController( $this->get_plugin_name(), $this->get_version(), $this->get_settings() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}
}

