<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>

		<?php if ( empty($fields) ) : ?>
			<p><?php echo __('Wrong fields configuration. Please check widget settings.', \jcf\JustCustomFields::TEXTDOMAIN); ?></p>
		<?php else : ?>
		<div class="jcf-fieldsgroup-field jcf-field-container">
			<?php foreach( $entries as $key => $entry ) : ?>
			<div class="jcf-fieldsgroup-row<?php if('00' === $key) echo ' jcf-hide'; ?>">
				<span class="drag-handle" >move</span>
				<div class="jcf-fieldsgroup-container">
					<?php foreach ( $fields as $field_name => $field_title ) : 
						$field_value = esc_attr(@$entry[$field_name]);
					?>
						<p><?php echo $field_title ?>: <br/>
							<input type="text" value="<?php echo $field_value; ?>" 
								id="<?php echo $this->getFieldIdL2($field_name, $key); ?>" 
								name="<?php echo $this->getFieldNameL2($field_name, $key); ?>">
						</p>
					<?php endforeach; ?>
					<a href="#" class="jcf-btn jcf_delete"><?php _e('Delete', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
				</div>
				<div class="jcf-delete-layer">
					<img src="<?php echo $del_image; ?>" alt="" />
					<input type="hidden" id="<?php echo $this->getFieldIdL2('__delete__', $key); ?>" name="<?php echo $this->getFieldNameL2('__delete__', $key); ?>" value="" />
					<a href="#" class="jcf-btn jcf_cancel"><?php _e('Cancel', \jcf\JustCustomFields::TEXTDOMAIN); ?></a><br/>
				</div>
			</div>
			<?php endforeach; ?>
			<a href="#" class="jcf-btn jcf_add_more"><?php _e('+ Add another', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
		</div>
		<?php endif; ?>

		<?php if( !empty($this->instance['description']) ) : ?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php endif; ?>
	</div>
</div>
		