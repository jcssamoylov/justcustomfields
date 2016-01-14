<?php

namespace JCF\models;
use JCF\models\JustFieldFactory;

class Shortcodes {
	
	public $_dataLayer;
	public $_fieldFactory;
	
	public function __construct($data_layer){

		$this->_dataLayer = $data_layer;
		$this->_fieldFactory = new JustFieldFactory($this->_dataLayer);

		add_shortcode( 'jcf-value',  array($this, 'set_field_value') );
	}

	/**
	 *	Do shortcode
	 *	@param array $args Attributes from shortcode
	 *	@return string Field content
	 */
	public function init_shortcode($args){
		extract( shortcode_atts( array(
			'field' => '',
			'post_id' => '',
		), $args ) );

		//get post id
		$post_id = !empty($args['post_id']) ? $args['post_id'] : get_the_ID();
		//get post type
		$post_type = get_post_type($post_id);
		//get field settings
		$field_settings = $this->_dataLayer->get_fields($post_type);
		//get field id
		foreach($field_settings as $key_field => $field){
			if( strcmp($args['field'], $field['slug']) === 0 ){
				$field_id = $key_field;
				break;
			}
		}
		// init field object and do shortcode
		if( $field_id ){
			$field_obj = $this->_fieldFactory->initObject($post_type, $field_id);
			$field_obj->set_post_ID( $post_id );

			unset($args['field']);
			return $field_obj->do_shortcode($args);
		}
		else{
			return false;
		}
	}

	/**
	 *	Shortcode [jcf-value]
	 *	@param array $args Attributes from shortcode
	 *	@return string Field content
	 */
	public function set_field_value($args){
		if( !empty($args['field']) ){
			return $this->init_shortcode($args);
		}else{
			return _e('Error! "field" parameter is missing', JCF_TEXTDOMAIN);
		}
	}

}

