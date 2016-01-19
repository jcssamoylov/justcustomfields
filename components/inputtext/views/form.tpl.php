<?php

$instance = wp_parse_args( (array) $field_obj->instance, array( 'title' => '', 'description' => '' ) );
$description = esc_html($instance['description']);
$title = esc_attr( $instance['title'] );
?>
<p><label for="<?php echo $field_obj->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $field_obj->getFieldId('title'); ?>" name="<?php echo $field_obj->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><label for="<?php echo $field_obj->getFieldId('description'); ?>"><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $field_obj->getFieldName('description'); ?>" id="<?php echo $field_obj->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>


