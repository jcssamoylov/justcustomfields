<?php

namespace jcf\controllers;
use jcf\models;
use jcf\core;

class FieldsetController extends core\Controller {

	public function __construct()
	{
		add_action('admin_menu', array($this, 'initRoutes') );

		//Fieldset actions
		add_action('wp_ajax_jcf_add_fieldset', array($this, 'ajaxCreateFieldset'));
		add_action('wp_ajax_jcf_delete_fieldset', array($this, 'ajaxDeleteFieldset'));
		add_action('wp_ajax_jcf_change_fieldset', array($this, 'ajaxChangeFieldset'));
		add_action('wp_ajax_jcf_update_fieldset', array($this, 'ajaxUpdateFieldset'));
		add_action('wp_ajax_jcf_order_fieldsets', array($this, 'ajaxSortFieldsets'));

		//Visibility options
		add_action('wp_ajax_jcf_get_rule_options', array($this, 'ajaxGetRuleOptions'));
		add_action('wp_ajax_jcf_get_taxonomy_terms', array($this, 'ajaGetTaxonomyTerms'));
		add_action('wp_ajax_jcf_save_visibility_rules', array($this, 'ajaxSaveVisibilityRules'));
		add_action('wp_ajax_jcf_add_visibility_rules_form', array($this, 'ajaxAddVisibilityRulesForm'));
		add_action('wp_ajax_jcf_delete_visibility_rule', array($this, 'ajaxDeleteVisibilityRule'));
		add_action('wp_ajax_jcf_visibility_autocomplete', array($this, 'ajaxVisibilityAutocomplete'));
	}

	/**
	 * Init routes for settings page
	 */
	public function initRoutes()
	{
		$page_title = __('Fields', \jcf\JustCustomFields::TEXTDOMAIN);
		$page_slug = 'jcf_fields';
		add_submenu_page(null, $page_title, $page_title, 'manage_options', $page_slug, array($this, 'initPage'));
	}

	/**
	 * Render settings page
	 */
	public function initPage()
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
		$this->_render( '/views/fieldsets/fields_ui', $template_params );
	}

	/**
	 *  add fieldset form process callback
	 */
	public function ajaxCreateFieldset()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $model->createFieldset();
	}
	
	/**
	 *  delete fieldset link process callback
	 */
	public function ajaxDeleteFieldset()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $model->deleteFieldset();
	}
	
	/**
	 * change fieldset link process callback
	 */
	public function ajaxChangeFieldset()
	{
		$model = new models\Fieldset();
		$model->load($_POST);
		$fieldset_id = $_POST['fieldset_id'];
		$post_type = strip_tags(trim($_POST['post_type']));
		$fieldset = $model->findFieldsetById($post_type, $fieldset_id);
		$visibility_form_data = $model->getVisibilityRulesForm();

		ob_start(); 
		$this->_render('/views/fieldsets/change_fieldset', array('fieldset' => $fieldset, 'visibility_form_data' => $visibility_form_data));
		$html = ob_get_clean();
		jcf_ajax_response($html, 'html');
	}
	
	/**
	 * save fieldset functions callback
	 */
	public function ajaxUpdateFieldset()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $model->updateFieldset();
	}
	
	/**
	 * fieldsets order change callback
	 */
	public function ajaxSortFieldsets()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $model->sortFieldsets();
	}

	/**
	 * add form for new rule functions callback
	 */
	public function ajaxAddVisibilityRulesForm()
	{
		$model = new models\Fieldset();
		$model->load($_POST);
		$form_data = $model->getVisibilityRulesForm();

		if ( !empty($_POST['add_rule']) || !empty($_POST['edit_rule']) ) {
			ob_start();
			$this->_render('/views/fieldsets/visibility_form', $form_data);
			$html = ob_get_clean();
			jcf_ajax_response($html, 'html');
		}
		else {
			$this->_render('/views/fieldsets/visibility_form', $form_data);
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
		
		ob_start();
		$this->_render('/views/fieldsets/' . $template, array('templates' => $rules['data']));
		$html = ob_get_clean();
		jcf_ajax_response($html, 'html');
	}

	/**
	 * Get taxonomy terms options functions callback
	 */
	public function ajaGetTaxonomyTerms() {
		$taxonomy = $_POST['taxonomy'];
		$current_term = array();
		$terms = get_terms($taxonomy, array('hide_empty' => false));
		ob_start();
		$this->_render('/views/fieldsets/terms_list', array('terms' => $terms, 'taxonomy' => $taxonomy, 'current_term' => $current_term));
		$html = ob_get_clean();
		jcf_ajax_response($html, 'html');
	}
	
	/**
	 * Save rules for visibility functions callback
	 */
	public function ajaxSaveVisibilityRules()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $visibility_rules = $model->saveVisibilityRules();
		
		ob_start();
		$this->_render('/views/fieldsets/visibility_rules', array('visibility_rules' => $visibility_rules));
		$resp = ob_get_clean();
		jcf_ajax_response($resp, 'html');
	}

	/**
	 * Delete rule for visibility functions callback
	 */
	public function ajaxDeleteVisibilityRule()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $visibility_rules = $model->deleteVisibilityRules();
		
		ob_start();
		$this->_render('/views/fieldsets/visibility_rules', array('visibility_rules' => $visibility_rules));
		$resp = ob_get_clean();
		jcf_ajax_response($resp, 'html');
	}

	/**
	 * Autocomplete for input for taxonomy terms in visibility form
	 * @global type $wpdb
	 */
	public function ajaxVisibilityAutocomplete(){
		$model = new models\Fieldset();
		$model->load($_POST) && $model->getVisibilityAutocompleteData();
	}
}


