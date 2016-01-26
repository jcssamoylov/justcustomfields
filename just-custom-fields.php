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
	const VERSION = '2.300';

	protected $_pluginName = 'just_custom_fields';
	protected $_pluginTitle;
	protected $_fields;

	protected static $_instance = NULL;
	
	public function __construct()
	{
		if ( self::$_instance !== NULL ) {
			return self::$_instance;
		}
		static::$_instance = $this;

		$this->_pluginTitle = __('Just Custom Fields', self::TEXTDOMAIN);
		$this->initControllers();
		$this->initFields();
	}

	/**
	 * Init all controllers
	 */
	public function initControllers()
	{
		new controllers\PostTypeController();

		if ( !is_admin() ) return;

 		new controllers\AdminController();
		new controllers\SettingsController();
		new controllers\ImportExportController();
		new controllers\FieldsetController();
		new controllers\FieldController();
	}
	
	public function initFields()
	{
		$this->registerField( '\\jcf\\components\\Just_Field_InputText', true );
		$this->registerField( '\\jcf\\components\\Just_Field_Select', true );
		$this->registerField( '\\jcf\\components\\Just_Field_SelectMultiple', true );
		$this->registerField( '\\jcf\\components\\Just_Field_Checkbox', true );
		$this->registerField( '\\jcf\\components\\Just_Field_Textarea', true );
		$this->registerField( '\\jcf\\components\\Just_Field_DatePicker', true );
		$this->registerField( '\\jcf\\components\\Just_Field_SimpleMedia', true );
		$this->registerField( '\\jcf\\components\\Just_Field_Table', true );
		$this->registerField( '\\jcf\\components\\Just_Field_Collection' );
		$this->registerField( '\\jcf\\components\\Just_Field_RelatedContent' );
		
		// deprecated
		$this->registerField( '\\jcf\\components\\Just_Field_UploadMedia' );
		$this->registerField( '\\jcf\\components\\Just_Field_FieldsGroup' );
		
		/**
		 *	to add more fields with your custom plugin:
		 *	- add_action  'jcf_register_fields'
		 *	- include your components files
		 *	- run 
		 *  $jcf = new JustCustomFields();
		 *  $jcf->registerField('namespace\className', $collection_field = true|false);
		 */
		do_action( 'jcf_register_fields' );
	}
	
	public function registerField( $class_name, $collection_field = false )
	{
		if ( !$class_name::checkCompatibility($class_name::$compatibility) ) return false;

		$field_obj = new $class_name();

		$field = array(
			'id_base' => $field_obj->idBase,
			'class' => $class_name,
			'title' => $field_obj->title,
			'collection_field' => $collection_field,
		);
		$this->_fields[$field_obj->idBase] = $field;
	}
	
	/**
	 *	return array of registered fields
	 */
	public function getFields( $collection_only = false )
	{
		if ( ! $collection_only )
			return $this->_fields;
		
		// filter by collection availability
		$collection_fields = array();
		foreach ($this->_fields as $f) {
			if ( !$f['collection_field'] ) continue;
			$collection_fields[] = $f;
		}
		
		return $collection_fields;
	}
	
	/**
	 * Field info (title, id_base, class)
	 * @param string $id_base
	 * @return array
	 */
	public function getFieldInfo($id_base)
	{
		if ( !empty($this->_fields[$id_base]) ) {
			return $this->_fields[$id_base];
		}
		return null;
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
	 * Close method clone for object
	 */
	protected function __clone(){}
}

new JustCustomFields();

?>