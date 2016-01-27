<?php
namespace jcf\components\inputtext;
use jcf\models;

class Just_Field_InputText extends models\Just_Field{
	
	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_inputtext' );
		parent::__construct('inputtext', __('Input Text', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
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
				<input type="text" name="<?php echo $this->getFieldName('val'); ?>" id="<?php echo $this->getFieldId('val'); ?>" value="<?php echo esc_attr($this->entry); ?>"/>
				<?php if ( $this->instance['description'] != '' ) : ?>
					<p class="description"><?php echo $this->instance['description']; ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * draw form for 
	 */
	public function form()
	{
		$instance = wp_parse_args( (array) $this->instance, array( 'title' => '', 'description' => '' ) );
		$description = esc_html($instance['description']);
		$title = esc_attr( $instance['title'] );

		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->getFieldId('description'); ?>"><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->getFieldName('description'); ?>" id="<?php echo $this->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}
	
	/**
	 *	save field on post edit form
	 */
	public function save( $values )
	{
		$values = $values['val'];
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}

}
