<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>
		<div class="jcf-relatedcontent-field jcf-field-container">
			<?php foreach ( $entries as $key => $entry ) : ?>
				<div class="jcf-relatedcontent-row<?php if('00' === $key) echo ' jcf-hide'; ?>">
					<div class="jcf-relatedcontent-container">
						<p>
							<span class="drag-handle" >move</span>
							<?php if( $type == 'select' ) : ?>
								<select id="<?php echo $this->getFieldIdL2('related_id', $key); ?>" 
									name="<?php echo $this->getFieldNameL2('related_id', $key); ?>">
									<option value="">&nbsp;</option>
									<?php foreach ( $options as $val => $label ) : ?>
									<option value="<?php echo $val; ?>" <?php selected($val, $entry); ?>><?php echo $label; ?></option>
									<?php endforeach; ?>
								</select>
							<?php else : // input field for autocomplete ?>
								<input type="text" value="<?php echo @$options[$entry]; ?>" 
									id="<?php echo $this->getFieldIdL2('related_title', $key); ?>" 
									name="<?php echo $this->getFieldNameL2('related_title', $key); ?>" 
									alt="<?php echo $post_type; ?>" />
								<input type="hidden" value="<?php echo $entry; ?>" 
									id="<?php echo $this->getFieldIdL2('related_id', $key); ?>" 
									name="<?php echo $this->getFieldNameL2('related_id', $key); ?>" />
							<?php endif; ?>
							<a href="#" class="jcf-btn jcf_delete"><?php _e('Delete', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
						</p>
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

		<?php if( $this->instance['description'] != '' ): ?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php endif; ?>
	</div>
</div>
