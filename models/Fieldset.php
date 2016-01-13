<?php

namespace JCF\models;

class Fieldset {
	
	public function __construct(){}
	
	/**
	 * Get html for templates options for adding visibility rules
	 * @param array $templates
	 * @param array $current
	 */
	public function getPageTemplatesHtml($templates, $current = array()) {
		ob_start();
		?>
		<?php if( !empty($templates) ): ?>
			<div class="templates-options">
				<p>
					<p><?php _e('Choose templates:', JCF_TEXTDOMAIN); ?></p>
					<ul class="visibility-list-items">
					<?php $i=1; foreach( $templates as $name => $slug ): ?>
						<li>
							<input type="checkbox" name="rule_templates" value="<?php echo $slug; ?>" id="rule_taxonomy_term_<?php echo $i; ?>"
								<?php checked(in_array($slug, $current), true ); ?>/>
							<label for="rule_taxonomy_term_<?php echo $i; ?>"><?php echo $name; ?></label>
						</li>
					<?php $i++; endforeach; ?>
					</ul>
					<br class="clear">
				</p>
			</div>
		<?php else:?>
			<p><?php _e('No available templates', JCF_TEXTDOMAIN); ?></p>
		<?php endif;

		$html = ob_get_clean();
		echo $html;
	}
	
	/**
	 * Get html for taxonomies options for adding visibility rules
	 * @param array $taxonomies
	 * @param array $current
	 * @param array $terms
	 * @param array $current_term
	 */
	public function getTaxonomiesHtml( $taxonomies = array(), $current_tax = array(), $terms = array(), $current_term = array() ) {
		ob_start();
		?>
		<?php if( !empty($taxonomies) ): ?>
			<div class="taxonomy-options">
				<p>
					<label for="rule-taxonomy"><?php _e('Choose taxonomy:', JCF_TEXTDOMAIN); ?></label>
					<br class="clear"/>
					<select name="rule_taxonomy" id="rule-taxonomy">
						<option value="" disabled="disabled" <?php selected(empty($current_tax)); ?> ><?php _e('Choose taxonomy', JCF_TEXTDOMAIN); ?></option>
						<?php foreach( $taxonomies as $slug => $taxonomy ): ?>
							<?php if($slug != 'post_format'):?>
								<option value="<?php echo $slug; ?>" <?php selected($current_tax, $slug); ?> ><?php echo $taxonomy->labels->singular_name; ?></option>
							<?php endif;?>
						<?php	endforeach; ?>
					</select>
				</p>
				<div class="taxonomy-terms-options">
					<?php if(!empty($terms)) :?>
						<?php $this->getTaxonomyTermsHtml($terms, $current_term); ?>
					<?php endif;?>
				</div>
			</div>
		<?php else: ?>
			<p><?php _e('No available taxonomies', JCF_TEXTDOMAIN); ?></p>
		<?php endif;

		$html = ob_get_clean();
		echo $html;
	}
	
	/**
	 * Get html for taxonomy terms options for adding visibility rules
	 * @param array $terms
	 * @param array $current_term
	 */
	public function getTaxonomyTermsHtml($terms, $current_term = array(), $taxonomy = '') {
		$taxonomy = get_taxonomy($terms[0]->taxonomy);
		ob_start();
		?>
		<?php if( !empty($terms) ): ?>
			<p>
				<p><?php _e('Choose ' . $taxonomy->labels->name .  ':', JCF_TEXTDOMAIN); ?></p>
				<?php if( count($terms) <= 20 ) : ?>
					<ul class="visibility-list-items">
					<?php $i=1; foreach( $terms as $term ): ?>
						<li>
							<input type="checkbox" name="rule_taxonomy_terms" value="<?php echo $term->term_id; ?>"
								<?php checked(in_array($term->term_id, $current_term), true ); ?>
								   id="rule_taxonomy_term_<?php echo $term->term_id; ?>" />
							<label for="rule_taxonomy_term_<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
						</li>
					<?php $i++; endforeach; ?>
					</ul>
				<?php else: ?>
					<p>
						<input type="text" id="new-term" name="newterm" class="newterm form-input-tip" size="16" autocomplete="on" value="">
						<input type="button" class="button termadd" value="Add">
					</p>
					<ul class="visibility-list-items">
					<?php if(!empty($current_term)) : ?>
						<?php $i=1; foreach( $terms as $term ): ?>
							<?php if(in_array($term->term_id, $current_term)) :?>
								<li>
									<input type="checkbox" name="rule_taxonomy_terms" value="<?php echo $term->term_id; ?>"
										<?php checked(true); ?>
										   id="rule_taxonomy_term_<?php echo $term->term_id; ?>" />
									<label for="rule_taxonomy_term_<?php echo $term->term_id; ?>"><?php echo $term->name; ?></label>
								</li>
							<?php endif; ?>
						<?php $i++; endforeach; ?>
					<?php endif;?>
					</ul>
				<?php endif; ?>
				<br class="clear">
			</p>
		<?php else: ?>
			<p><?php _e('No available terms', JCF_TEXTDOMAIN); ?></p>
		<?php endif;
		$html = ob_get_clean();
		echo $html;
	}
	
	/**
	 * Get table with added visibility rules
	 * @param array $visibility_rules
	 * @return html
	 */
	function getVisibilityRulesHtml($visibility_rules){
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
	
	// autocomplete for input
	public function ajaxVisibilityAutocomplete(){
		global $wpdb;
		$taxonomy = $_POST['taxonomy'];
		$term = $_POST['term'];
		$query = "SELECT t.term_id, t.name
			FROM wp_terms AS t
			LEFT JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id
			WHERE t.name LIKE '%$term%' AND tt.taxonomy = '$taxonomy'";
		$terms = $wpdb->get_results($query);

		$response = array();
		foreach($terms as $p){
			$response[] = array(
				'id' => $p->term_id,
				'label' => $p->name,
				'value' => $p->name,
			);
		}
		$json = json_encode($response);
		
		header( "Content-Type: application/json; charset=" . get_bloginfo('charset') );
		echo $json;
		exit();
	}
}

