<?php

namespace jcf\controllers;
use jcf\models;

class PostTypeController {

	public function __construct()
	{
		if ( !isset($_GET['page']) ) {
			add_action('admin_print_scripts', array($this, 'addScripts'));
		}
		add_action('admin_print_styles', array($this, 'addStyles'));
		add_action('add_meta_boxes', array($this, 'renderFields'), 10, 1); 
		add_action('save_post', array($this, 'saveFields'), 10, 2);
		add_shortcode('jcf-value',  array($this, 'setShortcodeValue'));
	}

	public function renderFields( $post_type = '' )
	{
		$model = new models\PostType();
		$model->renderCustomFields($post_type);
	}

	public function saveFields($post_ID = 0, $post = null)
	{
		$model = new models\PostType();
		$model->saveCustomFields($post_ID, $post);
	}

	public function setShortcodeValue()
	{
		$model = new models\Shortcodes();
		$shortcode_data = $model->setFieldValue();
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

