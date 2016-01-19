<?php 
//Defaults
$instance['type'] = (isset($field_obj->instance['type']))? $instance['type'] : 'file';
$instance = wp_parse_args( (array) $instance,
		array( 'title' => '', 'type' => 'file', 'autoresize' => '',
			  'description' => ''));

$title = esc_attr( $instance['title'] );
$type = $instance['type'];
$autoresize = esc_attr( $instance['autoresize'] );
$description = esc_html($instance['description']);
?>
<p><label for="<?php echo $field_obj->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $field_obj->getFieldId('title'); ?>" name="<?php echo $field_obj->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p>
	<label for="<?php echo $field_obj->getFieldId('type'); ?>"><?php _e('Type of files:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
	<select class="widefat" id="<?php echo $field_obj->getFieldId('type'); ?>" name="<?php echo $field_obj->getFieldName('type'); ?>">
		<option value="file" <?php selected('file', $type);?>><?php _e('All', \jcf\JustCustomFields::TEXTDOMAIN); ?></option>
		<option value="image" <?php selected('image', $type);?>><?php _e('Only Images', \jcf\JustCustomFields::TEXTDOMAIN); ?></option>
	</select>
</p>
<p><label for="<?php echo $field_obj->getFieldId('description'); ?>"><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $field_obj->getFieldName('description'); ?>" id="<?php echo $field_obj->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		