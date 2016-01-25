<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>
		<input type="text" name="<?php echo $this->getFieldName('val'); ?>" id="<?php echo $this->getFieldId('val'); ?>" value="<?php echo esc_attr($this->entry); ?>"/>
		<?php if ( $this->instance['description'] != '' ) : ?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php endif; ?>
	</div>
</div>
