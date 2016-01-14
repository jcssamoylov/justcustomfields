<?php

namespace jcf\controllers;
use jcf\models;

class FieldsetController {
	protected $_dataLayer;
	protected $_model;

	public function __construct($source_settings){
		$layer_type = $source_settings == JCF_CONF_SOURCE_DB ? 'DB' : 'Files';
		$layer_factory = new models\DataLayerFactory();

		$this->_dataLayer = $layer_factory->create($layer_type, $source_settings);
		$this->_model = new models\Fieldset();

		add_action('wp_ajax_jcf_add_fieldset', array($this, 'ajaxCreate'));
		add_action('wp_ajax_jcf_delete_fieldset', array($this, 'ajaxDelete'));
		add_action('wp_ajax_jcf_change_fieldset', array($this, 'ajaxChange'));
		add_action('wp_ajax_jcf_update_fieldset', array($this, 'ajaxUpdate'));
		add_action('wp_ajax_jcf_order_fieldsets', array($this, 'ajaxSort'));
		
		//Visibility options
		add_action('wp_ajax_jcf_get_rule_options', array($this, 'ajaxGetRuleOptions'));
		add_action('wp_ajax_jcf_get_taxonomy_terms', array($this, 'ajaGetTaxonomyTerms'));
		add_action('wp_ajax_jcf_save_visibility_rules', array($this, 'ajaxSaveVisibilityRules'));
		add_action('wp_ajax_jcf_add_visibility_rules_form', array($this, 'ajaxAddVisibilityRulesForm'));
		add_action('wp_ajax_jcf_delete_visibility_rule', array($this, 'ajaxDeleteVisibilityRule'));
		add_action('wp_ajax_jcf_visibility_autocomplete', array($this->_model, 'ajaxVisibilityAutocomplete'));
	}

	public function findByPostType($post_type){
		return $fieldsets = $this->_dataLayer->get_fieldsets($post_type);
	}
	
	/**
	 *  add fieldset form process callback
	 */
	public function ajaxCreate(){
		$title = strip_tags(trim($_POST['title']));
		$post_type = strip_tags(trim($_POST['post_type']));

		if( empty($title) ){
			jcf_ajax_response( array('status' => "0", 'error'=>__('Title field is required.', JCF_TEXTDOMAIN)) );
		}
		
		$slug = preg_replace('/[^a-z0-9\-\_\s]/i', '', $title);
		$trimed_slug = trim($slug);

		if( $trimed_slug == '' ){
			$slug = 'jcf-fieldset-'.rand(0,10000);
		}
		else{
			$slug = sanitize_title( $title );
		}

		$fieldsets = $this->_dataLayer->get_fieldsets($post_type);

		// check exists
		if( isset($fieldsets[$slug]) ){
			jcf_ajax_response( array('status' => "0", 'error'=>__('Such fieldset already exists.', JCF_TEXTDOMAIN)) );
		}

		// create fiedlset
		$fieldset = array(
			'id' => $slug,
			'title' => $title,
			'fields' => array(),
		);
		$this->_dataLayer->update_fieldsets($post_type, $slug, $fieldset);

		jcf_ajax_response( array('status' => "1" ) ); 
	}
	
	/**
	 *  delete fieldset link process callback
	 */
	public function ajaxDelete(){
		$post_type = strip_tags(trim($_POST['post_type']));
		$f_id = $_POST['fieldset_id'];

		if( empty($f_id) ){
			jcf_ajax_response( array('status' => "0", 'error'=>__('Wrong params passed.', JCF_TEXTDOMAIN)) );
		}

		$this->_dataLayer->update_fieldsets($post_type, $f_id, NULL);

		jcf_ajax_response( array('status' => "1") );
	}
	
	/**
	 * change fieldset link process callback
	 */
	public function ajaxChange(){
		$f_id = $_POST['fieldset_id'];
		$post_type = strip_tags(trim($_POST['post_type']));
		$fieldset = $this->_dataLayer->get_fieldsets($post_type, $f_id);

		ob_start(); ?>
		<div class="jcf_edit_fieldset">
			<h3 class="header"><?php echo __('Edit Fieldset:', JCF_TEXTDOMAIN) . ' ' . $fieldset['title']; ?></h3>
			<div class="jcf_inner_content">
				<form action="#" method="post" id="jcform_edit_fieldset">
					<fieldset>
						<input type="hidden" name="fieldset_id" value="<?php echo $fieldset['id']; ?>" />
						
						<p><label for="jcf_edit_fieldset_title"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="jcf_edit_fieldset_title" type="text" value="<?php echo esc_attr($fieldset['title']); ?>" /></p>
						
						<div class="field-control-actions">
							<h4>
								<a href="#" class="visibility_toggle" >
									<?php _e('Visibility rules', JCF_TEXTDOMAIN); ?>
									<span class="<?php echo !empty($fieldset['visibility_rules']) ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2' ?> dashicons-before"></span>
								</a>
							</h4>
							<div id="visibility" class="<?php echo !empty($fieldset['visibility_rules']) ? '' : 'hidden' ?>">
								<?php if( !empty($fieldset['visibility_rules']) ): ?>
									<?php echo $this->_model->getVisibilityRulesHtml($fieldset['visibility_rules']); ?>
								<?php else: ?>
									<?php $this->ajaxAddVisibilityRulesForm(); ?>
								<?php endif; ?>
							</div>
							<br class="clear"/>
							<div class="alignleft">
								<a href="#remove" class="field-control-remove"><?php _e('Delete', JCF_TEXTDOMAIN); ?></a> |
								<a href="#close" class="field-control-close"><?php _e('Close', JCF_TEXTDOMAIN); ?></a>
							</div>
							<div class="alignright">
								<?php echo print_loader_img(); ?>
								<input type="submit" value="<?php _e('Save', JCF_TEXTDOMAIN); ?>" class="button-primary" name="savefield">
							</div>
							<br class="clear"/>
						</div>
					</fieldset>
				</form>
			</div>
		</div>

		<?php
		$html = ob_get_clean();
		jcf_ajax_response($html, 'html');
	}
	
	/**
	 * save fieldset functions callback
	 */
	public function ajaxUpdate(){
		$f_id = $_POST['fieldset_id'];
		$post_type = strip_tags(trim($_POST['post_type']));
		$fieldset = $this->_dataLayer->get_fieldsets($post_type, $f_id);

		if(empty($fieldset)){
			jcf_ajax_response( array('status' => "0", 'error'=>__('Wrong data passed.', JCF_TEXTDOMAIN)) );
		}

		$title = strip_tags(trim($_POST['title']));
		if( empty($title) ){
			jcf_ajax_response( array('status' => "0", 'error'=>__('Title field is required.', JCF_TEXTDOMAIN)) );
		}

		$fieldset['title'] = $title;
		$this->_dataLayer->update_fieldsets($post_type, $f_id, $fieldset);
		jcf_ajax_response( array('status' => "1", 'title' => $title) );
	}
	
	/**
	 * fields order change callback
	 */
	public function ajaxSort(){
		$post_type = strip_tags(trim($_POST['post_type']));
		$order  = explode(',' ,trim($_POST['fieldsets_order'], ','));
		pa($_POST['fieldsets_order']);
		if(!empty($_POST['fieldsets_order'])){
			$this->_dataLayer->sort_fieldsets($post_type, $order);
		}

		$resp = array('status' => '1');
		jcf_ajax_response($resp, 'json');
	}

	/**
	 * get base options for visibility rules functions callback
	 */
	public function ajaxGetRuleOptions() {
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
	public function ajaGetTaxonomyTerms() {
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
	public function ajaxSaveVisibilityRules(){
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
	public function ajaxAddVisibilityRulesForm() {
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
			<legend><?php !empty($edit_rule) ? _e('Edit rule', JCF_TEXTDOMAIN) : _e('Add rule', JCF_TEXTDOMAIN)  ?></legend>

			<?php // Status for fieldset ?>
			<div class="visibility-options">
				<p><?php _e('You are about to set the visibility option for this fieldset', JCF_TEXTDOMAIN); ?></p>
				<input type="radio" name="visibility_option" id="visibility-option-hide" value="hide" <?php echo (!empty($edit_rule) ? checked( $visibility_rule['visibility_option'], 'hide' ) : 'checked' );  ?> />
				<label for="visibility-option-hide"><?php _e('Hide fieldset', JCF_TEXTDOMAIN); ?></label>
				<br class="clear"/>
				<input type="radio" name="visibility_option" id="visibility-option-show" value="show" <?php checked( $visibility_rule['visibility_option'], 'show' ); ?> />
				<label for="visibility-option-show"><?php _e('Show fieldset', JCF_TEXTDOMAIN); ?></label>
			</div>
			
			<?php // Condition fields for rule ?>
			<div class="join-condition <?php echo ( (!empty($add_rule) || $rule_id != 0) ? '' : 'hidden' ); ?>" >
				<p>
					<label for="rule-join-condition"><?php _e('Join condition with previous rules with operator:', JCF_TEXTDOMAIN); ?></label>
					<br />
					<select name="join_condition" id="rule-join-condition">
						<option value="and" <?php selected($visibility_rule['join_condition'], 'and'); ?> ><?php _e('AND', JCF_TEXTDOMAIN); ?></option>
						<option value="or" <?php selected($visibility_rule['join_condition'], 'or'); ?> ><?php _e('OR', JCF_TEXTDOMAIN); ?></option>
					</select>
				</p>
			</div>

			<?php if($post_type != 'page'): // Form for post types wich are not page ?>
				<p><?php _e('Based on', JCF_TEXTDOMAIN); ?> <strong><?php _e('Taxonomy terms', JCF_TEXTDOMAIN); ?></strong></p>
				<input type="hidden" name="based_on" value="taxonomy" />
				<?php $this->_model->getTaxonomiesHtml($taxonomies, $visibility_rule['rule_taxonomy'], $terms, $visibility_rule['rule_taxonomy_terms']); ?>
			<?php else: // Form for post type wich is page ?>
				<p>
				<label for="rule-based-on"><?php _e('Based on:', JCF_TEXTDOMAIN); ?></label><br />
				<select name="based_on" id="rule-based-on">
					<option value="" disabled="disabled" <?php echo !empty($edit_rule) ? '' : 'selected'; ?> ><?php _e('Choose option', JCF_TEXTDOMAIN); ?></option>
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
				<input type="button" class="update_rule_btn button" data-rule_id="<?php echo $_POST['rule_id'];?>" name="update_rule" value="<?php _e('Update rule', JCF_TEXTDOMAIN); ?>"/>
			<?php else: ?>
				<input type="button" class="save_rule_btn button" name="save_rule" value="<?php _e('Save rule', JCF_TEXTDOMAIN); ?>"/>
			<?php endif;?>
			<?php if( $edit_rule || $add_rule ):?>
				<input type="button" class="cancel_rule_btn button" name="cancel_rule" value="<?php _e('Cancel', JCF_TEXTDOMAIN); ?>" />
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
	public function ajaxDeleteVisibilityRule(){
		$data = $_POST;
		$post_type = $_POST['post_type'];

		$this->_dataLayer->update_fieldsets($post_type, $data['fieldset_id'], array('rules' => array('remove' => $data['rule_id'])));
		$fieldset = $this->_dataLayer->get_fieldsets($post_type, $data['fieldset_id']);
		$resp = $this->_model->getVisibilityRulesHtml($fieldset['visibility_rules']);
		jcf_ajax_response($resp, 'html');
	}

}


