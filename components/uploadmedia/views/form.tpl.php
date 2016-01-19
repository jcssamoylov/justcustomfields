<?php 
//Defaults
$instance = wp_parse_args( (array) $field_obj->instance,
		array( 'title' => '', 'type' => 'file', 'autoresize' => '',
			  'description' => __('Press "Upload" button, upload file or select in the library. Then choose Link "None" and "Full size" and press "Select File".', \jcf\JustCustomFields::TEXTDOMAIN) ) );

$title = esc_attr( $instance['title'] );
$type = $instance['type'];
$autoresize = esc_attr( $instance['autoresize'] );
$description = esc_html($instance['description']);
?>
<div class="error"><?php _e('This field is <b>deprecated</b>. Please use Simple Media instead. In case you need multiple images you can use Collection of Simple Media', \jcf\JustCustomFields::TEXTDOMAIN); ?></div>

<p><label for="<?php echo $field_obj->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $field_obj->getFieldId('title'); ?>" name="<?php echo $field_obj->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p>
	<label for="<?php echo $field_obj->getFieldId('type'); ?>"><?php _e('Type of files:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
	<select class="widefat" id="<?php echo $field_obj->getFieldId('type'); ?>" name="<?php echo $field_obj->getFieldName('type'); ?>">
		<option value="file" <?php selected('file', $type);?>><?php _e('All', \jcf\JustCustomFields::TEXTDOMAIN); ?></option>
		<option value="image" <?php selected('image', $type);?>><?php _e('Only Images', \jcf\JustCustomFields::TEXTDOMAIN); ?></option>
	</select>
</p>
<p>
	<label for="<?php echo $field_obj->getFieldId('autoresize'); ?>"><?php _e('Auto resize', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
	<input id="<?php echo $field_obj->getFieldId('autoresize'); ?>" name="<?php echo $field_obj->getFieldName('autoresize'); ?>" type="text" value="<?php echo $autoresize; ?>" />
	<br/><small><?php _e('Set dimensions to autoresize (in px).<br/><i>Example: 200x160', \jcf\JustCustomFields::TEXTDOMAIN); ?></i></small>
</p>

<p><label for="<?php echo $field_obj->getFieldId('alt_title'); ?>"><input type="checkbox" id="<?php echo $field_obj->getFieldId('alt_title'); ?>" name="<?php echo $field_obj->getFieldName('alt_title'); ?>" <?php if(!empty($instance['alt_title'])) echo 'checked="checked"'; ?> /> <?php _e('Enable alternative text', \jcf\JustCustomFields::TEXTDOMAIN); ?></label></p>
<p><label for="<?php echo $field_obj->getFieldId('alt_descr'); ?>"><input type="checkbox" id="<?php echo $field_obj->getFieldId('alt_descr'); ?>" name="<?php echo $field_obj->getFieldName('alt_descr'); ?>" <?php if(!empty($instance['alt_descr'])) echo 'checked="checked"'; ?> /> <?php _e('Enable alternative description', \jcf\JustCustomFields::TEXTDOMAIN); ?></label></p>

<p><label for="<?php echo $field_obj->getFieldId('description'); ?>"><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $field_obj->getFieldName('description'); ?>" id="<?php echo $field_obj->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		