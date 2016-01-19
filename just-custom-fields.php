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

	const TEXTDOMAIN = 'just-custom-fields';

	protected $_pluginName = 'just_custom_fields';
	protected $_version = '2.300';
	protected $_pluginTitle;

	protected static $_instance = NULL;
	
	public function __construct()
	{
		$this->_pluginTitle = __('Just Custom Fields', self::TEXTDOMAIN);

		if ( self::$_instance !== NULL ) {
			return self::$_instance;
		}

		$this->initControllers();
		static::$_instance = $this;
	}

	/**
	 * Init all controllers
	 */
	public function initControllers()
	{
		if ( !is_admin() ) return;

 		new controllers\AdminController();
		new controllers\SettingsController();
		new controllers\ImportExportController();
		new controllers\FieldsetController();
	}

	/**
	 * Getting plugin's name
	 * @return string
	 */
	public function getPluginName()
	{
		return $this->_pluginName;
	}

	/**
	 * Getting plugin's title
	 * @return string
	 */
	public function getPluginTitle()
	{
		return $this->_pluginTitle;
	}

	/**
	 * Getting plugin's version
	 * @return string
	 */
	public function getVersion()
	{
		return $this->_version;
	}

	/**
	 * Close method clone for object
	 */
	protected function __clone(){}
}

new JustCustomFields();

?>