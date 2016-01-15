<?php
/*
Plugin Name: Just Custom Fields for Wordpress
Plugin URI: http://justcoded.com/just-labs/just-custom-fields-for-wordpress-plugin/
Description: This plugin add custom fields for standard and custom post types in WordPress.
Tags: custom, fields, custom fields, meta, post meta, object meta, editor
Author: Alexander Prokopenko
Author URI: http://justcoded.com/
Version: 2.3
Donate link: http://justcoded.com/just-labs/just-custom-fields-for-wordpress-plugin/
*/

namespace jcf;

define('JCF_ROOT', dirname(__FILE__));
require_once( JCF_ROOT.'/core/Autoloader.php' );
require_once( JCF_ROOT.'/functions/helpers.php' );

if( !function_exists('pa') ) {
	function pa($mixed, $stop = false) {
		$ar = debug_backtrace(); $key = pathinfo($ar[0]['file']); $key = $key['basename'].':'.$ar[0]['line'];
		$print = array($key => $mixed); echo( '<pre>'.htmlentities(print_r($print,1)).'</pre>' );
		if($stop == 1) exit();
	}
}

class JustCustomFields {

	const JCF_TEXTDOMAIN = 'just-custom-fields';
	protected $plugin_name;
	protected $version;
	protected $plugin_title;
	
	public function __construct() {

		$this->plugin_name = 'just_custom_fields';
		$this->version = '2.300';
		$this->plugin_title = __('Just Custom Fields', self::JCF_TEXTDOMAIN);
		
		if ( !empty($_GET['page']) ) {
			add_action('admin_print_styles', array($this, 'add_styles'));
			add_action('admin_print_scripts', array($this, 'add_scripts'));
		}

		$this->init_controllers();
	}

	public function init_controllers()
	{
		if ( !is_admin() ) return;
		
 		new controllers\AdminController();
		//new controllers\FieldsetController($source_settings);
		new controllers\SettingsController();
		//new controllers\ImportExportController();
	}
	
}

new JustCustomFields();

?>