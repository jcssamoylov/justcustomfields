<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>
		
		<?php if ( empty($this->instance['fields']) ) : ?>
			<p class="error">Collection element has no fields registered. Please check component settings</p>
		<?php else: ?>
			<div class="collection_fields">
			<?php foreach ( $entries as $key => $fields ) : ?>
				<div class="collection_field_group">
					<h3>
						<span class="dashicons dashicons-editor-justify"></span>
						<span class="collection_group_title">
						<?php
							$group_title = $this->instance['title'] . ' Item';
							foreach ( $this->instance['fields'] as $field_id => $field) {
								if ( isset($field['group_title']) ) {
									if ( isset($fields[$field['slug']]) ) $group_title = $group_title.' : '.$fields[$field['slug']];
									break;
								}
							}
							echo $group_title;
						 ?>
						</span>
						<a href="#" class="collection_undo_remove_group"><?php _e('UNDO', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
						<span class="dashicons dashicons-trash"></span>
					</h3>
					<div class="collection_field_group_entry">
						<?php foreach ( $this->instance['fields'] as $field_id => $field ) : ?>
							<div class="collection_field_border jcf_collection_<?php echo (intval($field['field_width']) ? $field['field_width'] : '100'); ?>">
								<?php 
									$field_obj = $this->_fieldFactory->initObject($this->postType, $field_id, $this->fieldsetId, $this->id);
									$field_obj->setSlug($field['slug']);

									if ( isset($fields[$field['slug']]) ) {
										$field_obj->entry = $fields[$field['slug']];
									}

									$field_obj->instance = $field;
									$field_obj->isPostEdit = true;
									$field_obj->field();
								?>
							</div>
						<?php endforeach; ?>
						<div class="clr"></div>
					</div>
				</div>
				<?php 
					self::$currentCollectionFieldKey = self::$currentCollectionFieldKey + 1; 
					endforeach; 
				?>
				<div class="clr"></div>
				<input type="button" value="<?php echo sprintf(__('Add %s Item', \jcf\JustCustomFields::TEXTDOMAIN), $this->instance['title']); ?>" 
					   class="button button-large jcf_add_more_collection"
					   data-collection_id="<?php echo $this->id; ?>"
					   data-fieldset_id="<?php echo $this->fieldsetId; ?>"
					   name="jcf_add_more_collection">
				<div class="clr"></div>
			</div>
		<?php endif; ?>
	</div>
</div>
