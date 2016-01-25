<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>
		<div class="checkboxes-set">
		<div class="checkbox-row">
			<?php foreach( (array)$values as $key => $val ) : ?>
				<?php 
					if ( $single_checkbox ) {
						$checked = ($val == $this->entry) ? true : false;
					}
					else {
						$checked = in_array($val, (array)$this->entry);
					}
				?>
				<label><input type="checkbox" name="<?php echo $this->getFieldName('val') . ($single_checkbox ? '' : '[]'); ?>" id="<?php echo $this->getFieldId('val'); ?>" value="<?php echo esc_attr($val); ?>" <?php echo checked(true, $checked, false); ?>/> <?php echo $key; ?></label>
			<?php endforeach; ?>
		</div>
		</div>
		
		<?php if( !empty($this->instance['description']) ) : ?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php endif; ?>
	</div>
</div>
