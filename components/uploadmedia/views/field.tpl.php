<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>

		<div class="jcf-upload-field jcf-upload-type-<?php echo $upload_type; ?> jcf-field-container">
			<?php foreach($entries as $key => $entry) : 
				if ( !empty($entry) ) {
					$value = esc_attr( $entry['image'] );
					$image = $this->getThumbPath( $entry['image'] );
					$upload_text = ($upload_type == 'image')? __('Update image', \jcf\JustCustomFields::TEXTDOMAIN) : __('Update file', \jcf\JustCustomFields::TEXTDOMAIN);
					$delete_class = '';
	
					$img_title = esc_attr( @$entry['title'] );
					$img_descr = esc_attr( @$entry['description'] );
				}
			?>
			<div class="jcf-upload-row<?php if ( '00' === $key ) echo ' jcf-hide'; ?>">
				<div class="jcf-upload-container">
					<span class="drag-handle" >move</span>
					<?php if ( $upload_type == 'image' ) : ?>
						<div class="jcf-upload-image">
							<a href="<?php echo $value; ?>" class="jcf-btn" target="_blank"><img src="<?php echo $image; ?>" height="77" alt="" /></a>
						</div>
					<?php endif; ?>
					<div class="jcf-upload-file-info">
						<input type="hidden"
							   id="<?php echo $this->getFieldIdL2('uploaded_file', $key); ?>"
								name="<?php echo $this->getFieldNameL2('uploaded_file', $key); ?>"
								value="<?php echo $value; ?>" />
						<p class="<?php echo $delete_class; ?>"><a href="<?php echo $value; ?>" target="_blank"><?php echo basename($value); ?></a></p>
						
						<?php if ( $this->instance['alt_title'] ) : ?>
							<p><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?> <br/>
								<input type="text" value="<?php echo $img_title; ?>" 
									id="<?php echo $this->getFieldIdL2('alt_title', $key); ?>" 
									name="<?php echo $this->getFieldNameL2('alt_title', $key); ?>"></p>
						<?php endif; ?>

						<?php if ( $this->instance['alt_descr'] ) : ?>
							<p><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?> <br/>
								<textarea cols="95" row="3"
									id="<?php echo $this->getFieldIdL2('alt_descr', $key); ?>" 
									name="<?php echo $this->getFieldNameL2('alt_descr', $key); ?>"
									><?php echo $img_descr; ?></textarea></p>
						<?php endif; ?>

						<a href="media-upload.php?jcf_media=true&amp;type=<?php echo $upload_type; ?>&amp;TB_iframe=true" class="jcf-btn jcf_upload"
								rel="<?php echo $this->getFieldIdL2('uploaded_file', $key); ?>"><?php echo $upload_text; ?></a>
						<a href="#" class="jcf-btn jcf_delete<?php echo $delete_class; ?>"><?php _e('Delete', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
					</div>
				</div>
				<div class="jcf-delete-layer">
					<img src="<?php echo $del_image; ?>" alt="" />
					<input type="hidden" id="<?php echo $this->getFieldIdL2('delete', $key); ?>" name="<?php echo $this->getFieldNameL2('delete', $key); ?>" value="" />
					<a href="#" class="jcf-btn jcf_cancel"><?php _e('Cancel', \jcf\JustCustomFields::TEXTDOMAIN); ?></a><br/>
				</div>
			</div>
			<?php endforeach; ?>
			<a href="#" class="jcf-btn jcf_add_more"><?php if($upload_type == 'image') _e('+ Add another image', \jcf\JustCustomFields::TEXTDOMAIN); else _e('+ Add another file', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
		</div>

		<?php if( !empty($this->instance['description']) ) : ?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php endif; ?>
	</div>
</div>
