<?php

namespace jcf\controllers;
use jcf\models;
use jcf\core;

class FieldController extends core\Controller {

	public function __construct()
	{
		//Fields actions
		add_action('wp_ajax_jcf_add_field', array($this, 'ajaxChangeField'));
		add_action('wp_ajax_jcf_save_field', array($this, 'ajaxSaveField'));
		add_action('wp_ajax_jcf_delete_field', array($this, 'ajaxDeleteField'));
		add_action('wp_ajax_jcf_edit_field', array($this, 'ajaxChangeField'));
		add_action('wp_ajax_jcf_fields_order', array($this, 'ajaxSortFields'));
		add_action('wp_ajax_jcf_collection_order', array($this, 'ajaxCollectionSortFields' ));
	}

	/**
	 *  add field form show callback
	 */
	public function ajaxChangeField()
	{
		$model = new models\Field();
		$model->load($_POST) && $field = $model->initField();

		ob_start();
		$this->_render('/views/fieldsets/field_form', array('field' => $field));
		$html = ob_get_clean();
		jcf_ajax_response($html, 'html');
	}

	/**
	 * save field from the form callback
	 */
	public function ajaxSaveField()
	{
		$model = new models\Field();
		$model->load($_POST) && $resp = $model->saveField(); 
		if ( isset($resp['id_base']) && $resp['id_base'] == 'collection') {
			ob_start();
			$template_params = array(
				'collection' => $resp['instance'],
				'collection_id' => $resp['id'],
				'fieldset_id' => $resp['fieldset_id'],
				'registered_fields' => $resp['registered_fields']
			);
			$this->_render( '/components/collection/views/fields_ui', $template_params);
			$resp["collection_fields"] = ob_get_clean();
		}
		jcf_ajax_response($resp, 'json');
	}

	/**
	 * delete field processor callback
	 */
	public function ajaxDeleteField()
	{
		$model = new models\Field();
		$model->load($_POST) && $model->deleteField();
	}

	/**
	 * fields sort change callback
	 */
	public function ajaxSortFields()
	{
		$model = new models\Field();
		$model->load($_POST) && $model->sortFields();
	}

	/**
	 * sort collection fields callback
	 */
	public function ajaxCollectionSortFields()
	{
		$model = new models\Field();
		$model->load($_POST) && $model->sortCollectionFields();
	}
}