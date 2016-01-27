<?php
namespace jcf\components\textarea;
use jcf\models;
/**
 * Textarea field type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class Just_Field_Textarea extends models\Just_Field{
	
	public function __construct() 
	{
		$field_ops = array( 'classname' => 'field_textarea' );
		parent::__construct('textarea', __('Textarea', JCF_TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field() 
	{
		?>
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
		<?php
	}

	/**
	 * draw form for edit field
	 */
	public function form()
	{
		//Defaults
		$instance = wp_parse_args( (array) $this->instance, array( 'title' => '', 'description' => '' ) );
		$title = esc_attr( $instance['title'] );
		$description = esc_html($instance['description']);
		$checked = !empty($instance['editor']) ? ' checked="checked" ' : '';
		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->getFieldId('editor'); ?>"><input class="checkbox" id="<?php echo $this->getFieldId('editor'); ?>" name="<?php echo $this->getFieldName('editor'); ?>" type="checkbox" value="1" <?php echo $checked; ?> /> <?php _e('Use Editor for this textarea:', JCF_TEXTDOMAIN); ?></label></p>
		<p><label for="<?php echo $this->getFieldId('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->getFieldName('description'); ?>" id="<?php echo $this->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}

	/**
	 *	save field on post edit form
	 */
	public function save( $values ) 
	{
		global $wp_version;
		$values = isset($values['val']) ? $values['val'] : '' ;
		
		if ($this->instance['editor']) {
			if ($wp_version <= 3.2) {
				$values = nl2br(wpautop($values));
			}
			else {
				$values = wpautop($values);
			}
		}
		return $values;
	}

	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance ) 
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['editor'] = (int)@$new_instance['editor'];
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}
	
	/**
	 *	load custom script for tiny MCE for editors
	 */
	public function customTinyMCE()
	{
		global $jcf_flag_tiny_mce;
		
		if ( !empty($jcf_flag_tiny_mce) || ! user_can_richedit() )
			return;
		
		// just use standard tinyMCE for our textarea class
		// rewrite toolbar: remove "more" button, add "html" button
		?>
		<script type="text/javascript"><!--
			tinyMCEPreInit.mceInit.editor_selector = 'mceEditor';
			tinyMCEPreInit.mceInit.theme_advanced_buttons1 = 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,spellchecker,fullscreen,wp_adv,|,code';
			tinyMCE.init(tinyMCEPreInit.mceInit);
		--></script>
		
		<?php
		$jcf_flag_tiny_mce = true;
	}
}
?>