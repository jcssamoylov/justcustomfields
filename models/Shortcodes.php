<?php

namespace jcf\models;
use jcf\core;

class Shortcodes extends core\Model {
	
	protected $_layer;
	
	public function __construct()
	{
		parent::__construct();
		$layer_factory = new DataLayerFactory();
		$this->_layer = $layer_factory->create();
	}

	/**
	 *	Do shortcode
	 *	@param array $args Attributes from shortcode
	 *	@return string Field content
	 */
	protected function _initShortcode($args)
	{
		extract( shortcode_atts( array(
			'field' => '',
			'post_id' => '',
		), $args ) );

		//get post id
		$post_id = !empty($args['post_id']) ? $args['post_id'] : get_the_ID();
		//get post type
		$post_type = get_post_type($post_id);
		//get field settings
		$field_settings = $this->_layer->getFields($post_type);
		//get field id
		foreach ( $field_settings as $key_field => $field ) {
			if ( strcmp($args['field'], $field['slug']) === 0 ) {
				$field_id = $key_field;
				break;
			}
		}
		// init field object and do shortcode
		if ( $field_id ) {
			$factory = new JustFieldFactory();
			$field_obj = $factory->initObject($post_type, $field_id);
			$field_obj->setPostID( $post_id );
			
			unset($args['field']);
			return $field_obj->doShortcode($args);
		}
		else {
			return false;
		}
	}

	/**
	 *	Shortcode [jcf-value]
	 *	@param array $args Attributes from shortcode
	 *	@return string Field content
	 */
	public function getFieldValue($args)
	{
		if ( !empty($args['field']) ) {
			return $this->_initShortcode($args);
		}
		else {
			return _e('Error! "field" parameter is missing', \jcf\JustCustomFields::TEXTDOMAIN);
		}
	}

}

