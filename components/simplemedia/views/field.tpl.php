<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>
		<div class="jcf-simple-field jcf-simple-type-<?php echo $upload_type; ?> ">
		<?php if ( !empty($this->entry) ) {
				$value = esc_attr( $this->entry );
				$link = wp_get_attachment_url($this->entry);
				$upload_text = ($upload_type == 'image')? __('Update image', \jcf\JustCustomFields::TEXTDOMAIN) : __('Update file', \jcf\JustCustomFields::TEXTDOMAIN);
				$delete_class = '';	
			}
		?>
			<div class="jcf-simple-row">
				<div class="jcf-simple-container">
					<?php if( $upload_type == 'image' ) : ?>
						<div class="jcf-simple-image">
							<a href="<?php echo $link; ?>" class="" target="_blank"><img src="<?php echo ((!empty($link) && $link!='#')? $link : $noimage); ?>" height="77" alt="" /></a>
						</div>
					<?php endif; ?>
					<div class="jcf-simple-file-info">
						<input type="hidden" name="<?php echo $this->getFieldName('simplemedia'); ?>" id="<?php echo $this->getFieldId('simplemedia'); ?>" value="true">
						<input type="hidden"
							   id="<?php echo $this->getFieldId('uploaded_file'); ?>"
								name="<?php echo $this->getFieldName('uploaded_file'); ?>"
								value="<?php echo $value; ?>" />
						<p class="<?php echo $delete_class; ?>"><a href="<?php echo $link; ?>" target="_blank"><?php echo basename($link); ?></a></p>
							<a href="#"  id="simplemedia-<?php echo $this->getFieldId('uploaded_file'); ?>" class="button button-large "
							   data-selected_id="<?php echo $this->getFieldId('uploaded_file'); ?>" 
							   data-uploader_title="<?php echo $upload_text; ?>" 
							   data-media_type="<?php echo ($upload_type == 'image'?$upload_type:''); ?>"
							   data-uploader_button_text="<?php echo $upload_text; ?>"><?php echo $upload_text; ?></a>
								<script type="text/javascript">
									//create modal upload pop-up to select Media Files
									jQuery(document).ready(function(){
										var mm_<?php echo $this->getFieldId('uploaded_file', '_'); ?> = new JcfMediaModal({
											calling_selector : "#simplemedia-<?php echo $this->getFieldId('uploaded_file'); ?>",
											cb : function(attachment){
												JcfSimpleMedia.selectMedia(attachment, 
													"<?php echo $this->getFieldId('uploaded_file'); ?>", "<?php echo (( $upload_type == 'image' )?'image':'all');?>"
												);
											}
										});
									});
								</script>
						<a href="#" class="button button-large jcf_simple_delete<?php echo $delete_class; ?>" data-field_id="<?php echo $this->getFieldId('uploaded_file'); ?>"><?php _e('Delete', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
					</div>
				</div>
				<div class="jcf-delete-layer">
					<img src="<?php echo $del_image; ?>" alt="" />
					<a href="#" class="button button-large jcf_simple_cancel" data-field_id="<?php echo $this->getFieldId('uploaded_file'); ?>"><?php _e('Undo delete', \jcf\JustCustomFields::TEXTDOMAIN); ?></a><br/>
				</div>
			</div>
		</div>

		<?php if( $this->instance['description'] != '' ) : ?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php endif; ?>
		
	</div>
</div>
