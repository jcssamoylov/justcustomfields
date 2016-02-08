<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;

class FieldController extends core\Controller
{

	/**
	 * Init all wp-actions
	 */
	public function __construct()
	{
		parent::__construct();
		//Fields actions
		add_action('wp_ajax_jcf_add_field', array( $this, 'ajaxEdit' ));
		add_action('wp_ajax_jcf_save_field', array( $this, 'ajaxSave' ));
		add_action('wp_ajax_jcf_delete_field', array( $this, 'ajaxDelete' ));
		add_action('wp_ajax_jcf_edit_field', array( $this, 'ajaxEdit' ));
		add_action('wp_ajax_jcf_fields_order', array( $this, 'ajaxSort' ));
		add_action('wp_ajax_jcf_collection_order', array( $this, 'ajaxCollectionSort' ));
	}

	/**
	 *  Get field form show callback
	 */
	public function ajaxEdit()
	{
		$model = new models\Field();

		if ( !($model->load($_POST) && $field = core\JustFieldFactory::create($model)) ) {
			$this->_renderAjax(null, 'json', array( 'status' => !empty($field), 'error' => $model->getErrors() ));
		}

		$this->_renderAjax('fields/form', 'html', array( 'field' => $field ));
	}

	/**
	 * save field from the form callback
	 */
	public function ajaxSave()
	{
		$model = new models\Field();

		if ( !($model->load($_POST) && $success = $model->save()) ) {
			$this->_renderAjax(null, 'json', array( 'status' => !empty($field), 'error' => $model->getErrors() ));
		}

		if ( isset($success['id_base']) && $success['id_base'] == 'collection' ) {
			$jcf = new \jcf\JustCustomFields();
			$registered_fields = $jcf->getFields(true);

			ob_start();
			$template_params = array(
				'collection' => $success['instance'],
				'collection_id' => $success['id'],
				'fieldset_id' => $success['fieldset_id'],
				'registered_fields' => $registered_fields
			);

			$this->_render('fields/collection', $template_params);
			$success["collection_fields"] = ob_get_clean();
		}

		$this->_renderAjax(null, 'json', $success);
	}

	/**
	 * delete field processor callback
	 */
	public function ajaxDelete()
	{
		$model = new models\Field();
		$model->load($_POST) && $success = $model->delete();

		$this->_renderAjax(null, 'json', array( 'status' => !empty($success), 'error' => $model->getErrors() ));
	}

	/**
	 * fields sort change callback
	 */
	public function ajaxSort()
	{
		$model = new models\Field();
		$model->load($_POST) && $success = $model->sort();

		$this->_renderAjax(null, 'json', array( 'status' => !empty($success), 'error' => $model->getErrors() ));
	}

	/**
	 * sort collection fields callback
	 */
	public function ajaxCollectionSort()
	{
		$model = new models\Field();
		$model->load($_POST) && $success = $model->sortCollection();

		$this->_renderAjax(null, 'json', array( 'status' => !empty($success), 'error' => $model->getErrors() ));
	}

}
