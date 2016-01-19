<?php 
//Defaults
$instance = wp_parse_args( (array) $field_obj->instance, array( 'title' => '', 'fields' => '', 'description' => '' ) );
$title = esc_attr( $instance['title'] );
$fields = esc_html( $instance['fields'] );
$description = esc_html($instance['description']);
?>
<div class="error"><?php _e('This field is <b>deprecated</b>. Please use Collection instead.', \jcf\JustCustomFields::TEXTDOMAIN); ?></div>

<p><label for="<?php echo $field_obj->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
	<input class="widefat" id="<?php echo $field_obj->getFieldId('title'); ?>" name="<?php echo $field_obj->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><label for="<?php echo $field_obj->getFieldId('fields'); ?>"><?php _e('Fields:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
	<textarea name="<?php echo $field_obj->getFieldName('fields'); ?>" id="<?php echo $field_obj->getFieldId('fields'); ?>" cols="20" rows="4" class="widefat"><?php echo $fields; ?></textarea>
	<br/><small><?php _e('Format: %fieldname|%fieldtitle<br/><i>Example: price|Product Price', \jcf\JustCustomFields::TEXTDOMAIN); ?></i></small></p>
<p><label for="<?php echo $field_obj->getFieldId('description'); ?>"><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
	<textarea name="<?php echo $field_obj->getFieldName('description'); ?>" id="<?php echo $field_obj->getFieldId('description'); ?>" cols="20" rows="2" class="widefat"><?php echo $description; ?></textarea></p>
		