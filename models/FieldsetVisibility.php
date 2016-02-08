<?php

namespace jcf\models;

use jcf\core;
use jcf\models;

class FieldsetVisibility extends core\Model
{
	public $add_rule = false;
	public $edit_rule = false;
	public $rule_id;
	public $rule;
	public $visibility_rules;
	public $taxonomy;
	public $term;
	public $fieldset_id;
	public $post_type;

	/**
	 * Get visibility rules by post type
	 * @param string $post_type
	 */
	public function findByPostType($post_type) 
	{
		$fieldsets = $this->_dL->getFieldsets();

		if ( !empty($fieldsets[$post_type]) ) {
			foreach ($fieldsets[$post_type] as $f_id => $fieldset) {
				if ( !empty($fieldset['visibility_rules']) ) {
					$visibility_rules[$f_id] = $fieldset['visibility_rules'];

					foreach ( $visibility_rules[$f_id] as $key => $rule ) {
						if ( $rule['based_on'] == 'taxonomy' ) {
							$taxonomy_terms = array();

							foreach ( $rule['rule_taxonomy_terms'] as $term_id ) {
								$taxonomy_terms[] = get_term_by('id', $term_id, $rule['rule_taxonomy']);
							}

							$visibility_rules[$f_id][$key]['rule_taxonomy_terms'] = $taxonomy_terms;
						}
					}
				}
			}
		}

		return $visibility_rules;
	}

	/**
	 * Get form data for visibility rules form
	 * @return array
	 */
	public function getForm()
	{
		$output = array();
		$taxonomies = get_object_taxonomies($this->post_type, 'objects');

		$output['post_type'] = $this->post_type;
		$output['taxonomies'] = $taxonomies;
		$output['add_rule'] = $this->add_rule;

		if ( !empty($this->edit_rule) ) {
			$fieldsets = $this->_dL->getFieldsets();
			$visibility_rule = $fieldsets[$this->post_type][$this->fieldset_id]['visibility_rules'][$this->rule_id - 1];

			if ( empty($visibility_rule) ) {
				$this->addError(__('Visibility rule not found.', \jcf\JustCustomFields::TEXTDOMAIN));
				return false;
			}

			if ( $visibility_rule['based_on'] == 'taxonomy' ) {
				$terms = get_terms($visibility_rule['rule_taxonomy'], array( 'hide_empty' => false ));
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
			$options = get_object_taxonomies($this->post_type, 'objects');
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
			$visibility_rules[$this->rule_id - 1] = $this->visibility_rules;
		}
		else {
			$visibility_rules[] = $this->visibility_rules;
		}

		$fieldsets[$this->post_type][$this->fieldset_id]['visibility_rules'] = $visibility_rules;

		if ( !$this->_save($fieldsets) ) {
			$this->addError(__('Visibility rule not updated.', \jcf\JustCustomFields::TEXTDOMAIN));
			return false;
		}

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
		sort($fieldsets[$this->post_type][$this->fieldset_id]['visibility_rules']);

		if ( !$this->_save($fieldsets) ) {
			$this->addError(__('Visibility rule not deleted.', \jcf\JustCustomFields::TEXTDOMAIN));
			return false;
		}

		$rules = $fieldsets[$this->post_type][$this->fieldset_id]['visibility_rules'];
		return !empty($rules) ? $rules : true;
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
	protected function _save( $fieldsets )
	{
		$this->_dL->setFieldsets($fieldsets);
		$save = $this->_dL->saveFieldsetsData();
		return !empty($save);
	}

}
