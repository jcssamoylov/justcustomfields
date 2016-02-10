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
require_once( JCF_ROOT.'/core/Autoload.php' );
require_once( JCF_ROOT.'/functions/helpers.php' );

class JustCustomFields {

	const TEXTDOMAIN = 'just-custom-fields';
	const VERSION = '2.300';

	/**
	 * Refers to a single instance of this class. 
	 */
	protected static $_instance = null;
	protected static $_pluginName = 'just_custom_fields';
	protected static $_pluginTitle;

	protected $_fields;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Singleton A single instance of this class.
	 */
	public static function getInstance()
	{
		if ( null === static::$_instance ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * Alias for creating object of *Singleton* pattern
	 * 
	 * @return Singleton A single instance of this class.
	 */
	public static function run()
	{
		return static::getInstance();
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
		$this->registerField( '\\jcf\\components\\inputtext\\Just_Field_InputText', true );
		$this->registerField( '\\jcf\\components\\select\\Just_Field_Select', true );
		$this->registerField( '\\jcf\\components\\selectmultiple\\Just_Field_SelectMultiple', true );
		$this->registerField( '\\jcf\\components\\checkbox\\Just_Field_Checkbox', true );
		$this->registerField( '\\jcf\\components\\textarea\\Just_Field_Textarea', true );
		$this->registerField( '\\jcf\\components\\datepicker\\Just_Field_DatePicker', true );
		$this->registerField( '\\jcf\\components\\simplemedia\\Just_Field_SimpleMedia', true );
		$this->registerField( '\\jcf\\components\\table\\Just_Field_Table', true );
		$this->registerField( '\\jcf\\components\\collection\\Just_Field_Collection' );
		$this->registerField( '\\jcf\\components\\relatedcontent\\Just_Field_RelatedContent' );
		
		// deprecated
		$this->registerField( '\\jcf\\components\\uploadmedia\\Just_Field_UploadMedia' );
		$this->registerField( '\\jcf\\components\\fieldsgroup\\Just_Field_FieldsGroup' );
		
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
	public static function getPluginName()
	{
		return self::$_pluginName;
	}

	/**
	 * Getting plugin's title
	 * @return string
	 */
	public static function getPluginTitle()
	{
		return self::$_pluginTitle;
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct()
	{
		static::$_pluginTitle = __('Just Custom Fields', static::TEXTDOMAIN);
		$this->initControllers();
		$this->initFields();
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 * 
	 * @return void
	 */
	private function __clone()
	{
	}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup()
	{
	}
}

JustCustomFields::run();

?>