<?php

namespace jcf\controllers;
use jcf\models;
use jcf\core;

class ImportExportController extends core\Controller {

	public function __construct()
	{
		parent::__construct();
		add_action('admin_menu', array($this, 'initRoutes'));
		add_action('wp_ajax_jcf_export_fields', array($this, 'ajaxExport'));
		add_action('wp_ajax_jcf_export_fields_form', array($this, 'ajaxExportForm'));
		add_action('wp_ajax_jcf_import_fields', array($this, 'ajaxImportForm'));
	}
	
	/**
	 * Init routes for import/export page
	 */
	public function initRoutes()
	{
		$page_title = __('Import/Export', \jcf\JustCustomFields::TEXTDOMAIN);
		add_submenu_page(null, $page_title, $page_title, 'manage_options', 'jcf_import_export', array($this, 'actionIndex'));
	}

	/**
	 * Render import/export page
	 */
	public function actionIndex()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $model->importFields();

		//load template
		$this->_render('import_export/import_export' , array('tab' => 'import_export'));
	}

	public function ajaxImportForm()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $all_fields = $model->getImportFields();
		
		// load template
		$this->_renderAjax('import_export/import', 'html', $all_fields);
	}

	/**
	 * Ajax export fields
	 */
	public function ajaxExport()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $data = $model->exportFields();

		$filename = 'jcf_export' . date('Ymd-his') . '.json';
		header('Content-Type: text/json; charset=' . get_bloginfo('charset'));
		header("Content-Disposition: attachment;filename=" . $filename);
		header("Content-Transfer-Encoding: binary ");
		echo $data;
		exit();
	}

	/**
	 * Ajax render form with fields for export 
	 */
	public function ajaxExportForm()
	{
		$model = new models\Fieldset();
		$all_fields = $model->findAll();
		$all_fields['post_types'] = !empty($all_fields['post_types']) ? $all_fields['post_types'] : jcf_get_post_types();

		// load template
		$this->_renderAjax('import_export/export', 'html', $all_fields);
	}
}

