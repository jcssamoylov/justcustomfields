<?php

namespace jcf\controllers;
use jcf\models;
use jcf\core;

class FieldsetController extends core\Controller {

	/**
	 * Init all wp-actions
	 */
	public function __construct()
	{
		parent::__construct();
		add_action('admin_menu', array($this, 'initRoutes') );

		//Fieldset actions
		add_action('wp_ajax_jcf_add_fieldset', array($this, 'ajaxCreate'));
		add_action('wp_ajax_jcf_delete_fieldset', array($this, 'ajaxDelete'));
		add_action('wp_ajax_jcf_change_fieldset', array($this, 'ajaxGetForm'));
		add_action('wp_ajax_jcf_update_fieldset', array($this, 'ajaxUpdate'));
		add_action('wp_ajax_jcf_order_fieldsets', array($this, 'ajaxSort'));

		//Visibility options
		add_action('wp_ajax_jcf_get_rule_options', array($this, 'ajaxGetVisibilityOptions'));
		add_action('wp_ajax_jcf_get_taxonomy_terms', array($this, 'ajaGetTaxonomyTerms'));
		add_action('wp_ajax_jcf_save_visibility_rules', array($this, 'ajaxSaveVisibility'));
		add_action('wp_ajax_jcf_add_visibility_rules_form', array($this, 'ajaxGetVisibilityForm'));
		add_action('wp_ajax_jcf_delete_visibility_rule', array($this, 'ajaxDeleteVisibility'));
		add_action('wp_ajax_jcf_visibility_autocomplete', array($this, 'ajaxVisibilityAutocomplete'));
	}

	/**
	 * Init routes for settings page with fieldsets and fields
	 */
	public function initRoutes()
	{
		$page_title = __('Fields', \jcf\JustCustomFields::TEXTDOMAIN);
		add_submenu_page(null, $page_title, $page_title, 'manage_options', 'jcf_fields', array($this, 'actionIndex'));
	}

	/**
	 * Render settings page with fieldsets and fields
	 */
	public function actionIndex()
	{
		$post_type = $_GET['pt'];
		$post_types = jcf_get_post_types( 'object' );

		$jcf = new \jcf\JustCustomFields();
		$fieldset_model = new models\Fieldset();
		$field_model = new models\Field();

		$fieldsets = $fieldset_model->findByPostType($post_type);
		$fields = $field_model->findByPostType($post_type);
		$collections = $field_model->findCollectionsByPostType($post_type);
		$collections['registered_fields'] = $jcf->getFields(true);
		$registered_fields = $jcf->getFields();

		// load template
		$template_params = array(
			'tab' => 'fields',
			'post_type' => $post_types[$post_type],
			'fieldsets' => $fieldsets,
			'field_settings' => $fields,
			'collections' => $collections,
			'registered_fields' => $registered_fields
		);
		$this->_render( 'fieldsets/fields_ui', $template_params );
	}

	/**
	 *  Add fieldset form process callback
	 */
	public function ajaxCreate()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $success = $model->create();

		$this->_renderAjax(array('status' => $success, 'error' => $model->getErrors()), 'json');
	}
	
	/**
	 *  Delete fieldset link process callback
	 */
	public function ajaxDelete()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $success = $model->delete();

		$this->_renderAjax(array('status' => $success, 'error' => $model->getErrors()), 'json');
	}
	
	/**
	 * Change fieldset link process callback
	 */
	public function ajaxGetForm()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $fieldset = $model->findById($model->fieldset_id);

		$this->_renderAjax('fieldsets/change_fieldset', 'html', array('fieldset' => $fieldset));
	}
	
	/**
	 * Save fieldset functions callback
	 */
	public function ajaxUpdate()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $success = $model->update();

		$params = array(
			'status' => $success,
			'title' => $model->title,
			'error' => $model->getErrors()
		);
		$this->_renderAjax($params, 'json');
	}
	
	/**
	 * fieldsets order change callback
	 */
	public function ajaxSort()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $result = $model->sort();

		$this->_renderAjax($result, 'json');
	}

	/**
	 * add form for new rule functions callback
	 */
	public function ajaxGetVisibilityForm()
	{
		$model = new models\FieldsetVisibility();
		$model->load($_POST) && $form_data = $model->getForm();

		if ( !empty($model->add_rule) || !empty($model->edit_rule) ) {
			$this->_renderAjax('fieldsets/visibility/form', 'html', $form_data);
		}
		else {
			$this->_render('fieldsets/visibility/form', $form_data);
		}
	}

	/**
	 * get base options for visibility rules functions callback
	 */
	public function ajaxGetVisibilityOptions()
	{
		$model = new models\FieldsetVisibility();
		$model->load($_POST) && $success = $model->getOptions();

		$template = ( $model->rule == 'page_template' ) ? 'templates_list' : 'taxonomies_list';
		$options = ( $model->rule == 'page_template' ) ? array('templates' => $success) : array('taxonomies' => $success);

		$this->_renderAjax('fieldsets/visibility/' . $template, 'html', $options);
	}

	
	/**
	 * Get taxonomy terms options functions callback
	 */
	public function ajaGetTaxonomyTerms() 
	{
		$taxonomy = $_POST['taxonomy'];
		$terms = get_terms($taxonomy, array('hide_empty' => false));

		$this->_renderAjax('fieldsets/visibility/terms_list', 'html', array(
			'terms' => $terms, 
			'taxonomy' => $taxonomy, 
			'current_term' => array()
		));
	}
	
	/**
	 * Save rules for visibility functions callback
	 */
	public function ajaxSaveVisibility()
	{
		$model = new models\FieldsetVisibility();
		$model->load($_POST) && $rules = $model->update();

		$this->_renderAjax('fieldsets/visibility/rules', 'html', array('visibility_rules' => $rules));
	}

	/**
	 * Delete rule for visibility functions callback
	 */
	public function ajaxDeleteVisibility()
	{
		$model = new models\FieldsetVisibility();
		$model->load($_POST) && $rules = $model->delete();

		$this->_renderAjax('fieldsets/visibility/rules', 'html', array('visibility_rules' => $rules));
	}

	/**
	 * Autocomplete for input for taxonomy terms in visibility form
	 */
	public function ajaxVisibilityAutocomplete()
	{
		$model = new models\FieldsetVisibility();
		$model->load($_POST) && $result = $model->autocomplete();

		$this->_renderAjax($result, 'json');
	}
}


