<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
	<div class="form-field">
		<label><?php echo $this->instance['title']; ?>:</label>
		<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
			<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
		</div>

		<?php if ( !empty($this->instance['editor']) ) : // check editor
			// WP 3.3+ >> we have new cool function to make wysiwyg field
			 if ( function_exists('wp_editor') ) :
				ob_start();
				/**
				 * @todo have bug with switching editor/text after ajax field loading, now disabled this functionality
				 * @author Kirill Samojlenko
				 */
				wp_editor($this->entry, $this->getFieldId('val'), array(
					'textarea_name' => $this->getFieldName('val'),
					'textarea_rows' => 5,
					'media_buttons' => true,
					'wpautop' => true,
					'quicktags' => false,
					'tinymce' => array(
						'theme_advanced_buttons1' => 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,spellchecker,fullscreen,wp_adv',
					),
				));
				echo ob_get_clean();

				if ( defined('DOING_AJAX') && DOING_AJAX ) :
				?>
					<script type="text/javascript">
						jQuery(document).ready(function(){
							tinymce.execCommand( 'mceRemoveEditor', false, '<?php echo $this->getFieldId('val'); ?>' );
							tinymce.execCommand( 'mceAddEditor', false, '<?php echo $this->getFieldId('val'); ?>' );
						})
					</script>
				<?php endif; ?>

			<?php else :
				add_action( 'admin_print_footer_scripts', array(&$this, 'customTinyMCE'), 9999 );
				$entry = wpautop($this->entry);
				$entry = esc_html($entry);
				?>
				<textarea class="mceEditor" name="<?php echo $this->getFieldName('val'); ?>" id="<?php echo $this->getFieldId('val'); ?>" rows="5" cols="50"><?php echo $entry?></textarea>
			<?php endif; ?>

		<?php else: // no editor - print textarea ?>
			<?php $entry = esc_html($this->entry); 	?>
			<textarea name="<?php echo $this->getFieldName('val'); ?>" id="<?php echo $this->getFieldId('val'); ?>" rows="5" cols="50"><?php echo $entry?></textarea>
		<?php endif; ?>

		<?php if( !empty($this->instance['description']) ) :?>
			<p class="description"><?php echo $this->instance['description']; ?></p>
		<?php endif; ?>
	</div>
</div>
