<?php 
//Defaults
$instance = wp_parse_args( (array) $field_obj->instance, array( 'title' => '', 'columns' => '', 'description' => '' ) );

$title = esc_attr( $instance['title'] );
$columns = esc_html( $instance['columns'] );
$description = esc_html($instance['description']);
?>
<p><label for="<?php echo $field_obj->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
	<input class="widefat" id="<?php echo $field_obj->getFieldId('title'); ?>" name="<?php echo $field_obj->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><label for="<?php echo $field_obj->getFieldId('fields'); ?>"><?php _e('Columns:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
	<textarea name="<?php echo $field_obj->getFieldName('columns'); ?>" id="<?php echo $field_obj->getFieldId('columns'); ?>" cols="20" rows="4" class="widefat"><?php echo $columns; ?></textarea>
	<br/><small><?php _e('Format: %colname|%coltitle<br/><i>Example: username|User name', \jcf\JustCustomFields::TEXTDOMAIN); ?></i></small></p>
<p><label for="<?php echo $field_obj->getFieldId('description'); ?>"><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
	<textarea name="<?php echo $field_obj->getFieldName('description'); ?>" id="<?php echo $field_obj->getFieldId('description'); ?>" cols="20" rows="2" class="widefat"><?php echo $description; ?></textarea></p>
		