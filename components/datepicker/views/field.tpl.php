<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>
		<div>
		<input id="<?php echo $this->getFieldId('val'); ?>" name="<?php echo $this->getFieldName('val'); ?>" type="text" value="<?php echo esc_attr($this->entry); ?>" size="20" style="width:25%;" />
		</div>

		<script type="text/javascript"><!--
			jQuery(document).ready(function(){
				jQuery("#<?php echo $this->getFieldId('val'); ?>").datepicker({
					dateFormat: "<?php echo !empty($this->instance['date_format']) ? $this->instance['date_format'] : 'yy-mm-dd'; ?>"
					<?php if(!empty($this->instance['show_monthes'])) echo ', changeMonth: true, changeYear: true'; ?>
				});
			});
		--></script>
	</div>
</div>
