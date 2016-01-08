<?php

namespace JCF\controllers;
use JCF\models\DataLayerFactory;

class FieldsetController {
	protected $_dataLayer;

	public function __construct($source_settings){
		$layer_type = $source_settings == JCF_CONF_SOURCE_DB ? 'DB' : 'Files';
		$layer_factory = new DataLayerFactory();

		$this->_dataLayer = $layer_factory->create($layer_type, $source_settings);

		add_action('wp_ajax_jcf_add_fieldset', array($this, 'ajaxCreate'));
		add_action('wp_ajax_jcf_delete_fieldset', array($this, 'ajaxDelete'));
		add_action('wp_ajax_jcf_change_fieldset', array($this, 'ajaxChange'));
		add_action('wp_ajax_jcf_update_fieldset', array($this, 'ajaxUpdate'));
		add_action('wp_ajax_jcf_order_fieldsets', array($this, 'ajaxSort'));
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
									<?php //echo jcf_get_visibility_rules_html($fieldset['visibility_rules']); ?>
								<?php else: ?>
									<?php //jcf_ajax_add_visibility_rules_form(); ?>
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
	 * Get table with added visibility rules
	 * @param array $visibility_rules
	 * @return html
	 */
	public function getVisibilityRulesHtml($visibility_rules){
		ob_start(); 
		?>

			<div class="rules">
				<?php if(!empty($visibility_rules)): ?>
				<table class="wp-list-table widefat fixed fieldset-visibility-rules">
					<thead>
						<tr>
							<th style="width: 10%;">№</th>
							<th><?php _e('Rule', JCF_TEXTDOMAIN); ?></th>
							<th style="width: 20%;"><?php _e('Options', JCF_TEXTDOMAIN); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php	foreach($visibility_rules as $key => $rule): ?>
						<?php	
							$rule_text = '';
							$rule_text .= ucfirst($rule['visibility_option']);
							$rule_text .= ' when ';
							if($rule['based_on'] == 'taxonomy'){
								$term_text = '';
								if(!empty($rule['rule_taxonomy_terms'])){
									foreach($rule['rule_taxonomy_terms'] as $key_term => $term) {
										$term_obj = get_term_by('id', $term, $rule['rule_taxonomy']);
										$term_text .= ($key_term != 0 ? ', ' . $term_obj->name : $term_obj->name);
									}
								}
								$tax = get_taxonomy($rule['rule_taxonomy']);
								$rule_text .=  '<strong>'.$tax->labels->singular_name.'</strong>';
								$rule_text .=  ' in ';
								$rule_text .= '<strong>' . $term_text . '</strong>';
							}
							else{
								$templates = get_page_templates();
								$tpl_text = '';
								foreach($rule['rule_templates'] as $key_tpl => $template) {
									$tpl_name = array_search($template, $templates);
									$tpl_text .= ($key_tpl != 0 ? ', ' . $tpl_name : $tpl_name);
								}
								$rule_text .= '<strong>'.ucfirst(str_replace('_', ' ', $rule['based_on'] )).'</strong>';
								$rule_text .=  ' in ';
								$rule_text .= '<strong>'.$tpl_text.'<strong>';
							}
						?>
						
						<tr class="visibility_rule_<?php echo $key+1; ?>">
							<td><?php echo ($key+1); ?></td>
							<td>
								<?php if($key != 0):?>
									<strong><?php echo strtoupper($rule['join_condition']); ?></strong><br/>
								<?php endif;?>
								<?php echo $rule_text; ?>
							</td>
							<td>
								<a href="#" class="dashicons-before dashicons-edit edit-rule" data-rule_id="<?php echo $key+1; ?>"></a>
								<a href="#" class="dashicons-before dashicons-no remove-rule" data-rule_id="<?php echo $key+1; ?>"></a><?php ?>
							</td>
						</tr>
					<?php	endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
				<p><input type="button" class="add_rule_btn button" name="add_rule" value="<?php _e('Add rule', JCF_TEXTDOMAIN); ?>"/></p>
			</div>

		<?php 
		$rules = ob_get_clean(); 
		return $rules;
	}
}


