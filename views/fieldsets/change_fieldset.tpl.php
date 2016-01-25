<div class="jcf_edit_fieldset">
			<h3 class="header"><?php echo __('Edit Fieldset:', \jcf\JustCustomFields::TEXTDOMAIN) . ' ' . $fieldset['title']; ?></h3>
			<div class="jcf_inner_content">
				<form action="#" method="post" id="jcform_edit_fieldset">
					<fieldset>
						<input type="hidden" name="fieldset_id" value="<?php echo $fieldset['id']; ?>" />
						
						<p><label for="jcf_edit_fieldset_title"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="jcf_edit_fieldset_title" type="text" value="<?php echo esc_attr($fieldset['title']); ?>" /></p>
						
						<div class="field-control-actions">
							<h4>
								<a href="#" class="visibility_toggle" >
									<?php _e('Visibility rules', \jcf\JustCustomFields::TEXTDOMAIN); ?>
									<span class="<?php echo !empty($fieldset['visibility_rules']) ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2' ?> dashicons-before"></span>
								</a>
							</h4>
							<div id="visibility" class="<?php echo !empty($fieldset['visibility_rules']) ? '' : 'hidden' ?>">
								<?php if( !empty($fieldset['visibility_rules']) ): ?>
									<?php $this->_render('/views/fieldsets/visibility_rules', array('visibility_rules' => $fieldset['visibility_rules'])); ?>
								<?php else: ?>
									<?php $this->_render('/views/fieldsets/visibility_form', $visibility_form_data); ?>
								<?php endif; ?>
							</div>
							<br class="clear"/>
							<div class="alignleft">
								<a href="#remove" class="field-control-remove"><?php _e('Delete', \jcf\JustCustomFields::TEXTDOMAIN); ?></a> |
								<a href="#close" class="field-control-close"><?php _e('Close', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
							</div>
							<div class="alignright">
								<?php echo jcf_print_loader_img(); ?>
								<input type="submit" value="<?php _e('Save', \jcf\JustCustomFields::TEXTDOMAIN); ?>" class="button-primary" name="savefield">
							</div>
							<br class="clear"/>
						</div>
					</fieldset>
				</form>
			</div>
		</div>

