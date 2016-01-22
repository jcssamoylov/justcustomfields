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
		<?php 
			$this->_render('/views/fieldsets/taxonomies_list', array(
				'taxonomies' => $taxonomies,
				'visibility_rule' => $visibility_rule,
				'terms' => $terms
			)); 
		?>
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
