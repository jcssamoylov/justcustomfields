<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;

class ImportExportController extends core\Controller
{

	public function __construct()
	{
		parent::__construct();
		add_action('admin_menu', array( $this, 'initRoutes' ));
		add_action('wp_ajax_jcf_export_fields', array( $this, 'ajaxExport' ));
		add_action('wp_ajax_jcf_export_fields_form', array( $this, 'ajaxExportForm' ));
		add_action('wp_ajax_jcf_import_fields', array( $this, 'ajaxImportForm' ));
	}

	/**
	 * Init routes for import/export page
	 */
	public function initRoutes()
	{
		$page_title = __('Import/Export', \jcf\JustCustomFields::TEXTDOMAIN);
		add_submenu_page(null, $page_title, $page_title, 'manage_options', 'jcf_import_export', array( $this, 'actionIndex' ));
	}

	/**
	 * Render import/export page
	 */
	public function actionIndex()
	{
		$model = new models\ImportExport();
		$model->load($_POST) && $model->import();

		//load template
		return $this->_render('import_export/index', array( 'tab' => 'import_export' ));
	}

	public function ajaxImportForm()
	{
		$model = new models\ImportExport();

		if ( !($model->load($_POST) && $all_fields = $model->getImportFields()) ) {
			$this->_renderAjax(array( 'status' => !empty($all_fields), 'error' => $model->getErrors() ), 'json');
		}

		// load template
		$this->_renderAjax('import_export/import', 'html', $all_fields);
	}

	/**
	 * Ajax export fields
	 */
	public function ajaxExport()
	{
		$model = new models\ImportExport();

		if ( !($model->load($_POST) && $data = json_encode($model->export_data)) ) {
			$this->_renderAjax(null, 'json', array( 'status' => !empty($data), 'error' => $model->getErrors() ));
		}

		$filename = 'jcf_export' . date('Ymd-his') . '.json';
		header("Content-Disposition: attachment;filename=" . $filename);
		header("Content-Transfer-Encoding: binary ");
		$this->_renderAjax(null, 'json', $data);
	}

	/**
	 * Ajax render form with fields for export 
	 */
	public function ajaxExportForm()
	{
		$fieldsets_model = new models\Fieldset();
		$fields_model = new models\Field();
		$fieldsets = $fieldsets_model->findAll();
		$fields = $fields_model->findAll();

		$data = array(
			'field_settings' => $fields,
			'fieldsets' => $fieldsets
		);
		$data['post_types'] = !empty($fieldsets['post_types']) ? $fieldsets['post_types'] : jcf_get_post_types();

		// load template
		$this->_renderAjax('import_export/export', 'html', $data);
	}

}
