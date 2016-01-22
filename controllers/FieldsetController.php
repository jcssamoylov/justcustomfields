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
		$fieldset_id = $_POST['fieldset_id'];
		$post_type = strip_tags(trim($_POST['post_type']));
		$fieldset = $model->findFieldsetById($post_type, $fieldset_id);

		ob_start(); 
		$this->_render('/views/fieldsets/change_fieldset', array('fieldset' => $fieldset));
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
		
		ob_start();
		$this->_render('/view/fieldsets/visibility_form');
		$html = ob_get_clean();
		if ( !empty($add_rule) || !empty($edit_rule) ) {
			jcf_ajax_response($html, 'html');
		}
		else {
			$this->_render('/view/fieldsets/visibility_form');
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
	public function ajaGetTaxonomyTerms()
	{
		$taxonomy = $_POST['taxonomy'];
		$terms = get_terms($taxonomy, array('hide_empty' => false));
		ob_start();
		$this->_model->getTaxonomyTermsHtml($terms);
		$html = ob_get_clean();
		jcf_ajax_response($html, 'html');
	}
	
	/**
	 * Save rules for visibility functions callback
	 */
	public function ajaxSaveVisibilityRules()
	{
		$data = $_POST;
		$post_type = $_POST['post_type'];
		if(!empty($data['rule_id'])){
			$this->_dataLayer->update_fieldsets($post_type, $data['fieldset_id'], array('rules' => array('update' => $data['rule_id'], 'data' => $data['visibility_rules'])));
		}
		else{
			$this->_dataLayer->update_fieldsets($post_type, $data['fieldset_id'], array('rules' => $data['visibility_rules']));
		}
		$fieldset = $this->_dataLayer->get_fieldsets($post_type, $data['fieldset_id']);
		$resp = $this->_model->getVisibilityRulesHtml($fieldset['visibility_rules']);
		jcf_ajax_response($resp, 'html');
	}

	/**
	 * Delete rule for visibility functions callback
	 */
	public function ajaxDeleteVisibilityRule()
	{
		$data = $_POST;
		$post_type = $_POST['post_type'];

		$this->_dataLayer->update_fieldsets($post_type, $data['fieldset_id'], array('rules' => array('remove' => $data['rule_id'])));
		$fieldset = $this->_dataLayer->get_fieldsets($post_type, $data['fieldset_id']);
		$resp = $this->_model->getVisibilityRulesHtml($fieldset['visibility_rules']);
		jcf_ajax_response($resp, 'html');
	}
}


