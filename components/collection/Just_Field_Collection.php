<?php
namespace jcf\components\collection;
use jcf\models;

/**
 * Class for Collection type
 *
 * @package default
 * @author Kirill samojlenko
 */
class Just_Field_Collection extends models\Just_Field{
	
	public static $compatibility = "4.0+";
	
	public static $currentCollectionFieldKey = 0;

	public static $fieldWidth = array(
		'100' => '100%',
		'75' => '75%',
		'50' => '50%',
		'33' => '33%',
		'25' => '25%',
	);
	public $_fieldFactory;

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_collection' );
		parent::__construct('collection', __('Collection', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
		
		$this->_fieldFactory = new models\JustFieldFactory(TRUE);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field() 
	{
		self::$currentCollectionFieldKey = 0;

		if ( empty($this->entry) ) $this->entry = array('0' => '');

		$entries = (array)$this->entry;
		include(JCF_ROOT . '/components/collection/views/field.tpl.php');
	}

	/**
	 *	save field on post edit form
	 */
	function save( $_values )
	{
		$values = array();

		foreach ( $_values as $_value ) {
			$item = array();

			foreach ( $this->instance['fields'] as $field_id => $field ) {
				$field_obj = $this->_fieldFactory->initObject($this->postType, $field_id, $this->fieldsetId, $this->id);

				if ( isset($_value[$field_id]) ) {
					$item[$field['slug']] = $field_obj->save($_value[$field_id]);
				}
				else {
					$item[$field['slug']] = $field_obj->save(array('val'=>''));
				}
			}
			$values[] = $item;
		}
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['custom_row'] = true;
		return $instance;
	}
	
	/**
	 *	add script for collection and custom scripts and styles from collection fields
	 */
	public function addJs()
	{
		
		wp_register_script(
			'jcf_collection_post_edit',
			WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection_post_edit.js',
			array('jquery')
		);
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script('jcf_collection_post_edit');

		foreach ( $this->instance['fields'] as $field_id => $field ) {
			$field_obj = $this->_fieldFactory->initObject($this->postType, $field_id, $this->fieldsetId, $this->id);
			if(  method_exists($field_obj, 'addJs')) $field_obj->addJs();
			if(  method_exists($field_obj, 'addCss')) $field_obj->addCss();
		}
	}
	
	/**
	 *	add custom  styles from collection
	 */
	public function addCss()
	{
		wp_register_style('jcf_collection',
				WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection.css',
				array('thickbox'));
		wp_enqueue_style('jcf_collection');
	}
	


	/**
	 * Get nice name for width attribute
	 * 
	 * @param string $width_key
	 * @return string|null
	 */
	public static function getWidthAlias( $width_key )
	{
		if ( isset(self::$fieldWidth[$width_key]) ) {
			return self::$fieldWidth[$width_key];
		}
		return null;
	}

	/**
	 * delete field from collection
	 */
	public function deleteField($field_id)
	{
		// remove from fields array
		$this->_layer->updateFields($this->postType, $field_id, NULL, $this->fieldsetId, $this->id);
				
	}

	
}