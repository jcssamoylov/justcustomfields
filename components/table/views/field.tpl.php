<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>
		<?php if( !empty($columns) ) : ?>
			<div class="jcf-table jcf-field-container">
				<table class="sortable wp-list-table widefat fixed">
					<?php echo $table_head; ?>
					<?php echo $rows; ?>
					<?php echo $first_row; ?>
				</table>
				<p><a href="#" class="button button-large jcf_add_row"><?php _e('+ Add row', \jcf\JustCustomFields::TEXTDOMAIN); ?></a></p>
			</div>
		<?php endif; ?>

		<?php if( $this->instance['description'] != '' ): ?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php endif; ?>
	</div>
</div>
