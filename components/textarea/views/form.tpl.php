<?php
//Defaults
$instance = wp_parse_args( (array) $field_obj->instance, array( 'title' => '', 'description' => '' ) );
$title = esc_attr( $instance['title'] );
$description = esc_html($instance['description']);
$checked = !empty($instance['editor']) ? ' checked="checked" ' : '';
?>
<p><label for="<?php echo $field_obj->getFieldId('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $field_obj->getFieldId('title'); ?>" name="<?php echo $field_obj->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><label for="<?php echo $field_obj->getFieldId('editor'); ?>"><input class="checkbox" id="<?php echo $field_obj->getFieldId('editor'); ?>" name="<?php echo $field_obj->getFieldName('editor'); ?>" type="checkbox" value="1" <?php echo $checked; ?> /> <?php _e('Use Editor for this textarea:', JCF_TEXTDOMAIN); ?></label></p>
<p><label for="<?php echo $field_obj->getFieldId('description'); ?>"><?php _e('Description:', JCF_TEXTDOMAIN); ?></label> <textarea name="<?php echo $field_obj->getFieldName('description'); ?>" id="<?php echo $field_obj->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		