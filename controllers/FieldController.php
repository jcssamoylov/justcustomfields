<?php

namespace jcf\controllers;
use jcf\models;
use jcf\core;

class FieldController extends core\Controller {

	/**
	 * Init all wp-actions
	 */
	public function __construct()
	{
		parent::__construct();
		//Fields actions
		add_action('wp_ajax_jcf_add_field', array($this, 'ajaxEdit'));
		add_action('wp_ajax_jcf_save_field', array($this, 'ajaxSave'));
		add_action('wp_ajax_jcf_delete_field', array($this, 'ajaxDelete'));
		add_action('wp_ajax_jcf_edit_field', array($this, 'ajaxEdit'));
		add_action('wp_ajax_jcf_fields_order', array($this, 'ajaxSort'));
		add_action('wp_ajax_jcf_collection_order', array($this, 'ajaxCollectionSort' ));
	}

	/**
	 *  Get field form show callback
	 */
	public function ajaxEdit()
	{
		$model = new models\Field();
		$model->load($_POST) && $field = models\JustFieldFactory::create($model);

		$this->_renderAjax('fieldsets/field_form', 'html', array('field' => $field));
	}

	/**
	 * save field from the form callback
	 */
	public function ajaxSave()
	{
		$model = new models\Field();
		$model->load($_POST) && $result = $model->save(); 
	
		if ( isset($result['id_base']) && $result['id_base'] == 'collection') {
			ob_start();
			$collection = $result['instance'];
			$collection_id = $result['id'];
			$fieldset_id = $result['fieldset_id'];
			$registered_fields = $result['registered_fields'];

			include( JCF_ROOT . '/components/collection/views/fields_ui');
			$result["collection_fields"] = ob_get_clean();
		}

		$this->_renderAjax($result, 'json');
	}

	/**
	 * delete field processor callback
	 */
	public function ajaxDelete()
	{
		$model = new models\Field();
		$model->load($_POST) && $result = $model->delete();

		$this->_renderAjax($result, 'json');
	}

	/**
	 * fields sort change callback
	 */
	public function ajaxSort()
	{
		$model = new models\Field();
		$model->load($_POST) && $result = $model->sort();
		
		$this->_renderAjax($result, 'json');
	}

	/**
	 * sort collection fields callback
	 */
	public function ajaxCollectionSort()
	{
		$model = new models\Field();
		$model->load($_POST) && $result = $model->sortCollection();
		
		$this->_renderAjax($result, 'json');
	}
}