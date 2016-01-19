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

		//Fields actions
		add_action('wp_ajax_jcf_add_field', array($this, 'ajaxCreateField'));
		add_action('wp_ajax_jcf_save_field', array($this, 'ajaxSaveField'));
		add_action('wp_ajax_jcf_delete_field', array($this, 'ajaxDeleteField'));
		add_action('wp_ajax_jcf_edit_field', array($this, 'ajaxEditField'));
		add_action('wp_ajax_jcf_fields_order', array($this, 'ajaxSortFields'));
		add_action('wp_ajax_jcf_collection_order', array($this, 'ajaxCollectionFieldsOrder' ));
		add_action('wp_ajax_jcf_collection_add_new_field_group', array($this, 'ajaxReturnCollectionFieldGroup' ));
		add_action('wp_ajax_jcf_related_content_autocomplete', array($this, 'autocomplete'));

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
	 *  add field form show callback
	 */
	public function ajaxCreateField()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $field = $model->createField();

		ob_start();
		$this->_render('/views/fieldsets/field_form', array('field' => $field));
		$html = ob_get_clean();
		jcf_ajax_response($html, 'html');
	}

	/**
	 * save field from the form callback
	 */
	public function ajaxSaveField()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $resp = $model->saveField(); 

		if ( isset($resp['id_base']) && $resp['id_base'] == 'collection') {
			ob_start();
			$template_params = array(
				'collection' => $resp['instance'],
				'collection_id' => $resp['id'],
				'fieldset_id' => $resp['fieldset_id'],
				'registered_fields' => $resp['registered_fields']
			);
			$this->_render( '/components/collection/views/fields_ui', $template_params);
			$resp["collection_fields"] = ob_get_clean();
		}
		jcf_ajax_response($resp, 'json');
	}

	/**
	 * delete field processor callback
	 */
	public function ajaxDeleteField()
	{
		$model = new models\Fieldset();
		$model->load($_POST) && $model->deleteField();
	}


	/**
	 * get base options for visibility rules functions callback
	 */
	public function ajaxGetRuleOptions()
	{
		$rule = $_POST['rule'];
		$post_type = $_POST['post_type'];
		ob_start();

		if( $rule == 'page_template' ) {
			$templates = get_page_templates(); 
			$this->_model->getPageTemplatesHtml($templates);
		} 
		else { 
			$taxonomies = get_object_taxonomies( $post_type, 'objects' );
			$this->_model->getTaxonomiesHtml($taxonomies);
		} 

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
	 * add form for new rule functions callback
	 */
	public function ajaxAddVisibilityRulesForm()
	{
		$post_type = $_POST['post_type'];
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		$add_rule = !empty($_POST['add_rule']) ? $_POST['add_rule'] : false;

		if(!empty($_POST['edit_rule'])){
			$rule_id = $_POST['rule_id'] - 1;
			$fieldset_id = $_POST['fieldset_id'];
			$fieldset = $this->_dataLayer->get_fieldsets($post_type, $fieldset_id);
			$edit_rule = $_POST['edit_rule']; 
			$visibility_rule = $fieldset['visibility_rules'][$rule_id];
			if($visibility_rule['based_on'] == 'taxonomy'){
				$terms = get_terms($visibility_rule['rule_taxonomy'], array('hide_empty' => false));
			}
			else{
				$templates = get_page_templates();
			}
		}

		ob_start();
		?>
		<fieldset id="fieldset_visibility_rules">
			<legend><?php !empty($edit_rule) ? _e('Edit rule', TEXTDOMAIN) : _e('Add rule', TEXTDOMAIN)  ?></legend>

			<?php // Status for fieldset ?>
			<div class="visibility-options">
				<p><?php _e('You are about to set the visibility option for this fieldset', TEXTDOMAIN); ?></p>
				<input type="radio" name="visibility_option" id="visibility-option-hide" value="hide" <?php echo (!empty($edit_rule) ? checked( $visibility_rule['visibility_option'], 'hide' ) : 'checked' );  ?> />
				<label for="visibility-option-hide"><?php _e('Hide fieldset', TEXTDOMAIN); ?></label>
				<br class="clear"/>
				<input type="radio" name="visibility_option" id="visibility-option-show" value="show" <?php checked( $visibility_rule['visibility_option'], 'show' ); ?> />
				<label for="visibility-option-show"><?php _e('Show fieldset', TEXTDOMAIN); ?></label>
			</div>
			
			<?php // Condition fields for rule ?>
			<div class="join-condition <?php echo ( (!empty($add_rule) || $rule_id != 0) ? '' : 'hidden' ); ?>" >
				<p>
					<label for="rule-join-condition"><?php _e('Join condition with previous rules with operator:', TEXTDOMAIN); ?></label>
					<br />
					<select name="join_condition" id="rule-join-condition">
						<option value="and" <?php selected($visibility_rule['join_condition'], 'and'); ?> ><?php _e('AND', TEXTDOMAIN); ?></option>
						<option value="or" <?php selected($visibility_rule['join_condition'], 'or'); ?> ><?php _e('OR', TEXTDOMAIN); ?></option>
					</select>
				</p>
			</div>

			<?php if($post_type != 'page'): // Form for post types wich are not page ?>
				<p><?php _e('Based on', TEXTDOMAIN); ?> <strong><?php _e('Taxonomy terms', TEXTDOMAIN); ?></strong></p>
				<input type="hidden" name="based_on" value="taxonomy" />
				<?php $this->_model->getTaxonomiesHtml($taxonomies, $visibility_rule['rule_taxonomy'], $terms, $visibility_rule['rule_taxonomy_terms']); ?>
			<?php else: // Form for post type wich is page ?>
				<p>
				<label for="rule-based-on"><?php _e('Based on:', TEXTDOMAIN); ?></label><br />
				<select name="based_on" id="rule-based-on">
					<option value="" disabled="disabled" <?php echo !empty($edit_rule) ? '' : 'selected'; ?> ><?php _e('Choose option', TEXTDOMAIN); ?></option>
					<option value="page_template" <?php selected( $visibility_rule['based_on'], 'page_tempalate' ); ?> >Page template</option>
					<?php if(!empty($taxonomies)):?>
						<option value="taxonomy" <?php selected( $visibility_rule['based_on'], 'taxonomy' ); ?> >Taxonomy</option>
					<?php endif; ?>	
				</select>
				</p>
				
				<div class="rules-options">
					<?php if($visibility_rule['based_on'] == 'taxonomy'): //Taxonomy options for post type page based on taxonomy ?>
						<?php $this->_model->getTaxonomiesHtml($taxonomies, $visibility_rule['rule_taxonomy'], $terms, $visibility_rule['rule_taxonomy_terms']); ?>
					<?php elseif($visibility_rule['based_on'] == 'page_template'): //Page template options ?>
						<?php $this->_model->getPageTemplatesHtml($templates, $visibility_rule['rule_templates']); ?>
					<?php endif;?>
				</div>

			<?php endif; ?>

			<?php // From buttons ?>
			<?php if( !empty($edit_rule) ): ?>
				<input type="button" class="update_rule_btn button" data-rule_id="<?php echo $_POST['rule_id'];?>" name="update_rule" value="<?php _e('Update rule', TEXTDOMAIN); ?>"/>
			<?php else: ?>
				<input type="button" class="save_rule_btn button" name="save_rule" value="<?php _e('Save rule', TEXTDOMAIN); ?>"/>
			<?php endif;?>
			<?php if( $edit_rule || $add_rule ):?>
				<input type="button" class="cancel_rule_btn button" name="cancel_rule" value="<?php _e('Cancel', TEXTDOMAIN); ?>" />
			<?php endif;?>

		</fieldset>

		<?php
		$html = ob_get_clean();
		if(!empty($add_rule) || !empty($edit_rule)){
			jcf_ajax_response($html, 'html');
		}
		else{
			echo $html;
		}
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
	
		
	public function ajaxCollectionFieldsOrder()
	{
		$field_factory = new models\JustFieldFactory();
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = $_POST['collection_id'];
		$post_type = $_POST['post_type'];
		$collection = $field_factory->initObject($post_type, $collection_id, $fieldset_id);
		$order  = trim($_POST['fields_order'], ',');

		$new_fields = explode(',', $order);
		$new_order = array();		

		if(! empty($new_fields)){
			foreach($new_fields as $field_id){
				if(isset($collection->instance['fields'][$field_id])){
					$new_order[$field_id] = $collection->instance['fields'][$field_id];					
				}
			}
		}
		$collection->instance['fields'] = $new_order;
		$this->_layer->updateFields($post_type, $collection_id, $collection->instance, $fieldset_id);
		
		$resp = array('status' => '1');
		jcf_ajax_response($resp, 'json');
	}
	
	/**
	 * return empty collection fields group
	 */
	public function ajaxReturnCollectionFieldGroup()
	{
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = $_POST['collection_id'];
		$collection = $this->_fieldFactory->initObject($this->postType, $collection_id, $fieldset_id);
		self::$currentCollectionFieldKey = $_POST['group_id'];
		?>
		<div class="collection_field_group">
			<h3>
				<span class="dashicons dashicons-editor-justify"></span>
				<span class="collection_group_title">
				<?php echo $collection->instance['title'].' Item'; ?>
				</span>
				<span class="dashicons dashicons-trash"></span>

			</h3>
			<div class="collection_field_group_entry">
				<?php					
					foreach ( $collection->instance['fields'] as $field_id => $field ) {
						echo '<div class="collection_field_border jcf_collection_'.(intval($field['field_width'])?$field['field_width']:'100').'">';
						$field_obj = $this->_fieldFactory->initObject($this->postType, $field_id, $collection->fieldsetId, $collection->id);
						$field_obj->setSlug($field['slug']);
						$field_obj->instance = $field;
						$field_obj->isPostEdit = true;
						$field_obj->field($field_obj->field_options);
						echo '</div>';
					}
				?>
				<div class="clr"></div>
			</div>
		</div>
		<?php die();
	}
	
	/**
	 *	autocomplete
	 */
	public static function autocomplete(){
		$term = $_POST['term'];
		if(empty($term)) die('');
		
		$post_type = $_POST['post_types'];
		
		$post_types = jcf_get_post_types('object');

		if( $post_type != 'any' ){
			$post_type_where = " post_type = '$post_type' ";
		}
		else{
			// get all post types
			$post_type_where = "( post_type = '" . implode("' OR post_type = '", array_keys($post_types)) . "' )";
		}
		
		global $wpdb;
		$query = "SELECT ID, post_title, post_status, post_type
			FROM $wpdb->posts
			WHERE $post_type_where AND (post_status = 'publish' OR post_status = 'draft') AND post_title LIKE '%$term%'
			ORDER BY post_title";
		$posts = $wpdb->get_results($query);
		
		$response = array();
		foreach($posts as $p){
			$draft = ( $p->post_status == 'draft' )? ' (DRAFT)' : '';
			$type_label = ( $post_type != 'any' )? '' : ' / '.$post_types[$p->post_type]->labels->singular_name;
			$response[] = array(
				'id' => $p->ID,
				'label' => $p->post_title . $draft . $type_label,
				'value' => $p->post_title . $draft . $type_label,
				'type' => $p->post_type,
				'status' => $p->post_status
			);
		}
		
		$json = json_encode($response);
		
		header( "Content-Type: application/json" );
		echo $json;
		exit();
	}
}


