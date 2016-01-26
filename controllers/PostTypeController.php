<?php

namespace jcf\controllers;
use jcf\models;
use jcf\core;

class PostTypeController extends core\Controller {

	public function __construct()
	{
		if ( !isset($_GET['page']) ) {
			add_action('admin_print_scripts', array($this, 'addScripts'));
		}
		add_action('admin_print_styles', array($this, 'addStyles'));
		add_action('add_meta_boxes', array($this, 'renderFields'), 10, 1); 
		add_action('save_post', array($this, 'saveFields'), 10, 2);
		add_action('wp_ajax_jcf_related_content_autocomplete', array($this, 'ajaxRelatedContentAutocomplete'));
		add_action('wp_ajax_jcf_collection_add_new_field_group', array($this, 'ajaxReturnCollectionFieldGroup'));
		add_shortcode('jcf-value',  array($this, 'getShortcodeValue'));
	}

	/**
	 * Get fields by post type
	 * @param string $post_type
	 */
	public function renderFields( $post_type = '' )
	{
		$model = new models\PostType();
		$model->renderCustomFields($post_type);
	}

	/**
	 * Save values of custom fields for post
	 * @param int $post_ID
	 * @param array $post
	 */
	public function saveFields($post_ID = 0, $post = null)
	{
		$model = new models\PostType();
		$model->saveCustomFields($post_ID, $post);
	}

	/**
	 * Set value of shortcode
	 */
	public function getShortcodeValue($args)
	{
		$model = new models\Shortcodes();
		return $model->getFieldValue($args);
	}

	/**
	 * return empty collection fields group
	 */
	public function ajaxReturnCollectionFieldGroup()
	{
		$model = new models\Field();
		$model->load($_POST);
		$collection = $model->groupCollectionFields();
		$this->_render('/components/collection/views/group_fields', array('collection' => $collection));
		die();
	}

	/**
	 *	Autocomplete for related content field callback
	 */
	public function ajaxRelatedContentAutocomplete()
	{
		$model = new models\Field();
		$model->load($_POST) && $model->autocompleteRelatedContentField();
	}

	/**
	 *	add custom scripts to post edit page
	 */
	public function addScripts(){

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
	public function addStyles(){
		wp_register_style('jcf_edit_post', WP_PLUGIN_URL.'/just-custom-fields/assets/edit_post.css');
		wp_enqueue_style('jcf_edit_post');
		
		do_action('jcf_admin_edit_post_styles');
	}
}

