<?php

namespace jcf\models;
use jcf\core;
use jcf\models;

class FieldsetVisibility extends core\Model {

	protected $_dL;
	
	public $add_rule = false;
	public $edit_rule = false;
	public $rule_id;
	public $rule;
	public $visibility_rules;
	public $taxonomy;
	public $term;
	public $fieldset_id;
	public $post_type;

	public function __construct()
	{
		parent::__construct();
		$this->_dL = core\DataLayerFactory::create();
	}

	/**
	 * Get form data for visibility rules form
	 * @return array
	 */
	public function getForm()
	{
		$output = array();
		$taxonomies = get_object_taxonomies( $this->post_type, 'objects' );

		$output['post_type'] = $this->post_type;
		$output['taxonomies'] = $taxonomies;
		$output['add_rule'] = $this->add_rule;

		if ( !empty($this->edit_rule) ) {
			$fieldsets = $this->_dL->getFieldsets();
			$edit_rule = $this->_request['edit_rule']; 
			$visibility_rule = $fieldsets[$this->post_type][$this->fieldset_id]['visibility_rules'][$this->rule_id - 1];

			if ( $visibility_rule['based_on'] == 'taxonomy' ) {
				$terms = get_terms($visibility_rule['rule_taxonomy'], array('hide_empty' => false));
				$output['terms'] = $terms;
			}
			else {
				$templates = get_page_templates();
				$output['templates'] = $templates;
			}

			$output['rule_id'] = $this->rule_id - 1;
			$output['fieldset_id'] = $this->fieldset_id;
			$output['visibility_rule'] = $visibility_rule;
			$output['edit_rule'] = $this->edit_rule;
		}

		return $output;
	}

	/**
	 * Get visibility rules for fieldset with $this->_request
	 */
	public function getOptions()
	{
		if ( $this->rule == 'page_template' ) {
			$options = get_page_templates();
		}
		else {
			$options = get_object_taxonomies( $this->post_type, 'objects' );
		}

		return $options;
	}

	/**
	 * Save visibility rule
	 * @return array
	 */
	public function update()
	{
		$fieldsets = $this->_dL->getFieldsets();
		$visibility_rules = $fieldsets[$this->post_type][$this->fieldset_id]['visibility_rules'];

		if ( !empty($this->rule_id) ) {
			$visibility_rules[$this->rule_id - 1 ] = $this->visibility_rules;
		}
		else {
			$visibility_rules[] = $this->visibility_rules;
		}
		
		$fieldsets[$this->post_type][$this->fieldset_id]['visibility_rules'] = $visibility_rules;
		$this->_save($fieldsets);

		return $visibility_rules;
	}

	/**
	 * Delete visibility rule
	 * @return array
	 */
	public function delete()
	{
		$fieldsets = $this->_dL->getFieldsets();
		unset($fieldsets[$this->post_type][$this->fieldset_id]['visibility_rules'][$this->rule_id - 1]);
		$this->_save($fieldsets);

		return $fieldsets[$this->post_type][$this->fieldset_id]['visibility_rules'];
	}

	/**
	 * Autocomplete for visibility rule
	 */
	public function autocomplete()
	{
		global $wpdb;

		$query = "SELECT t.term_id, t.name
			FROM wp_terms AS t
			LEFT JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id
			WHERE t.name LIKE '%$this->term%' AND tt.taxonomy = '$this->taxonomy'";
		$terms = $wpdb->get_results($query);
		$response = array();

		foreach ( $terms as $p ) {
			$response[] = array(
				'id' => $p->term_id,
				'label' => $p->name,
				'value' => $p->name,
				'status' => true
			);
		}

		return $response;
	}

	/**
	 * Save visibility settings
	 * @param array $fieldsets
	 */
	protected function _save($fieldsets)
	{
		$this->_dL->setFieldsets($fieldsets);
		$this->_dL->saveFieldsetsData();
	}
}

