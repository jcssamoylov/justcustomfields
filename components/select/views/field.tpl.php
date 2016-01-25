<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>
		<div class="select-field">
		<select name="<?php echo $this->getFieldName('val'); ?>" id="<?php echo $this->getFieldId('val'); ?>" style="width: 47%;">
			<option value="<?php echo esc_attr($this->instance['empty_option']); ?>" <?php echo selected($this->instance['empty_option'], $this->entry, false); ?>><?php echo esc_attr($this->instance['empty_option']); ?></option>
			<?php  foreach( (array)$values as $key => $val ) : ?>
				<option value="<?php echo esc_attr($val); ?>" <?php echo selected($val, $this->entry, false); ?>><?php echo esc_html(ucfirst($key)); ?></option>
			<?php endforeach; ?>
		</select>
		</div>
		<?php if( !empty($this->instance['description']) ) : ?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php endif; ?>
	</div>
</div>
