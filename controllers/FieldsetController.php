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
		add_action('wp_ajax_jcf_get_rule_options', array($this, 'ajaxGetRuleOptions'));
		add_action('wp_ajax_jcf_get_taxonomy_terms', array($this, 'ajaGetTaxonomyTerms'));
		add_action('wp_ajax_jcf_save_visibility_rules', array($this, 'ajaxSaveVisibilityRules'));
		add_action('wp_ajax_jcf_add_visibility_rules_form', array($this, 'ajaxGetVisibilityRulesForm'));
		add_action('wp_ajax_jcf_delete_visibility_rule', array($this, 'ajaxDeleteVisibilityRule'));
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
		$tab = 'fields';
		$model = new models\Fieldset();

		$name_post_type = $_GET['pt'];
		$data = $model->findByPostType($name_post_type);
		$post_types = jcf_get_post_types( 'object' );

		// load template
		$template_params = array(
			'tab' => $tab,
			'post_type' => $post_types[$name_post_type],
			'fieldsets' => $data['fieldsets'],
			'field_settings' => $data['fields'],
			'collections' => $data['collections'],
			'registered_fields' => $data['registered_fields']
		);
		$this->_render( 'fieldsets/fields_ui', $template_params );
	}

	/**
	 *  Add fieldset form process callback
	 */
	public function ajaxCreate()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $result = $model->create();

		$this->_renderAjax($result, 'json');
	}
	
	/**
	 *  Delete fieldset link process callback
	 */
	public function ajaxDelete()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $result = $model->delete();

		$this->_renderAjax($result, 'json');
	}
	
	/**
	 * Change fieldset link process callback
	 */
	public function ajaxGetForm()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $fieldset = $model->findById($model->post_type, $model->fieldset_id);
		$visibility_form_data = $model->getVisibilityRulesForm();

		$this->_renderAjax('fieldsets/change_fieldset', 'html', array('fieldset' => $fieldset, 'visibility_form_data' => $visibility_form_data));
	}
	
	/**
	 * Save fieldset functions callback
	 */
	public function ajaxUpdate()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $result = $model->update();
		
		$this->_renderAjax($result, 'json');
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
	public function ajaxGetVisibilityRulesForm()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $form_data = $model->getVisibilityRulesForm();

		if ( !empty($model->add_rule) || !empty($model->edit_rule) ) {
			$this->_renderAjax('fieldsets/visibility_form', 'html', $form_data);
		}
		else {
			$this->_render('fieldsets/visibility_form', $form_data);
		}
	}

	/**
	 * get base options for visibility rules functions callback
	 */
	public function ajaxGetRuleOptions()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $rules = $model->getVisibilityRules();
		$template = ( $rules['type'] == 'page_template' ) ? 'templates_list' : 'taxonomies_list';
		$data = ( $rules['type'] == 'page_template' ) ? array('templates' => $rules['data']) : array('taxonomies' => $rules['data']);

		$this->_renderAjax('fieldsets/' . $template, 'html', $data);
	}

	
	/**
	 * Get taxonomy terms options functions callback
	 */
	public function ajaGetTaxonomyTerms() 
	{
		$taxonomy = $_POST['taxonomy'];
		$current_term = array();
		$terms = get_terms($taxonomy, array('hide_empty' => false));

		$this->_renderAjax('fieldsets/terms_list', 'html', array(
			'terms' => $terms, 
			'taxonomy' => $taxonomy, 
			'current_term' => $current_term
		));
	}
	
	/**
	 * Save rules for visibility functions callback
	 */
	public function ajaxSaveVisibilityRules()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $visibility_rules = $model->saveVisibilityRules();

		$this->_renderAjax('fieldsets/visibility_rules', 'html', array('visibility_rules' => $visibility_rules));
	}

	/**
	 * Delete rule for visibility functions callback
	 */
	public function ajaxDeleteVisibilityRule()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $visibility_rules = $model->deleteVisibilityRules();

		$this->_renderAjax('fieldsets/visibility_rules', 'html', array('visibility_rules' => $visibility_rules));
	}

	/**
	 * Autocomplete for input for taxonomy terms in visibility form
	 */
	public function ajaxVisibilityAutocomplete()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $result = $model->getVisibilityAutocompleteData();

		$this->_renderAjax($result, 'json');
	}
}


