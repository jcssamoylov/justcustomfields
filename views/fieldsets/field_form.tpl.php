<?php $op = ($field->idBase == $field->id) ? __('Add', \jcf\JustCustomFields::TEXTDOMAIN) : __('Edit', \jcf\JustCustomFields::TEXTDOMAIN); ?>
		<div class="jcf_edit_field">
			<h3 class="header"><?php echo $op . ' ' . $field->title; ?></h3>
			<div class="jcf_inner_content">
				<form action="#" method="post" id="<?php echo ( $field->isCollectionField() ? 'jcform_edit_collection_field':'jcform_edit_field');?>">
					<fieldset>
						<input type="hidden" name="field_id" value="<?php echo $field->id; ?>" />
						<input type="hidden" name="field_number" value="<?php echo $field->number; ?>" />
						<input type="hidden" name="field_id_base" value="<?php echo $field->idBase; ?>" />
						<input type="hidden" name="fieldset_id" value="<?php echo $field->fieldsetId; ?>" />
						<?php if( $field->isCollectionField() ) : ?>
							<input type="hidden" name="collection_id" value="<?php echo $field->collectionId; ?>" />
						<?php
							endif;
							$this->_render('/components/' . $field->idBase . '/views/form', array('field_obj' => $field));
							// need to add slug field too
							$slug = esc_attr($field->slug);
						?>
						<p>
							<label for="<?php echo $field->getFieldId('slug'); ?>"><?php _e('Slug:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
							<input class="widefat" id="<?php echo $field->getFieldId('slug'); ?>" name="<?php echo $field->getFieldName('slug'); ?>" type="text" value="<?php echo $slug; ?>" />
							<br/><small><?php _e('Machine name, will be used for postmeta field name. (should start from underscore)', \jcf\JustCustomFields::TEXTDOMAIN); ?></small>
						</p>
						<?php
							// enabled field
							if( $field->isNew ){
								$field->instance['enabled'] = 1;
							}
						?>
						<p>
							<label for="<?php echo $field->getFieldId('enabled'); ?>">
								<input class="checkbox" type="checkbox" 
										id="<?php echo $field->getFieldId('enabled'); ?>"
										name="<?php echo $field->getFieldName('enabled'); ?>"
										value="1" <?php checked(true, @$field->instance['enabled']); ?> />
								<?php _e('Enabled', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
						</p>
						<?php if($field->isCollectionField()) : ?>
							<?php if($field->idBase == 'inputtext') : ?>
								<p>
									<label for="<?php echo $field->getFieldId('group_title'); ?>">
										<input class="checkbox" type="checkbox" 
											id="<?php echo $field->getFieldId('group_title'); ?>"
											name="<?php echo $field->getFieldName('group_title'); ?>"
											value="1" <?php checked(true, @$field->instance['group_title']); ?> />
										<?php _e('Use this field as collection item title?', \jcf\JustCustomFields::TEXTDOMAIN); ?>
									</label>
								</p>
						
							<?php endif; ?>
							<p>
								<label for="<?php echo $field->getFieldId('field_width'); ?>"><?php _e('Select Field Width', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
								<select class="widefat" 
										id="<?php echo $field->getFieldId('field_width'); ?>"
										name="<?php echo $field->getFieldName('field_width'); ?>">
									<?php foreach( \jcf\components\collection\Just_Field_Collection::$fieldWidth as $key => $width) : ?>
										<option value="<?php echo $key; ?>"<?php echo (@$field->instance['field_width']==$key?' selected':''); ?>>
											<?php echo $width; ?></option>
									<?php endforeach; ?>
								</select> 
									
							</p>
						<?php endif; ?>
						<div class="field-control-actions">
							<div class="alignleft">
								<?php if( $op != __('Add', \jcf\JustCustomFields::TEXTDOMAIN) ) : ?>
								<a href="#remove" class="field-control-remove"><?php _e('Delete', \jcf\JustCustomFields::TEXTDOMAIN); ?></a> |
								<?php endif; ?>
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