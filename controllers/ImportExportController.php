<?php

namespace jcf\controllers;
use jcf\models;
use jcf\core;

class ImportExportController extends core\Controller {

	public function __construct()
	{
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
		$page_slug = 'jcf_import_export';
		add_submenu_page(null, $page_title, $page_title, 'manage_options', $page_slug, array($this, 'initPage'));
	}

	/**
	 * Render import/export page
	 */
	public function initPage()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $model->importFields();
		$tab = 'import_export';
		$this->_render('import_export');
	}

	public function ajaxImportForm()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $all_fields = $model->getImportFields();

		header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
		$this->_render('import', $all_fields);
		exit();
	}

	/**
	 * Ajax export fields
	 */
	public function ajaxExport()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $model->exportFields();
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
		header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
		$this->_render('export', $all_fields);
		exit();
	}
}

