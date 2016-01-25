<?php
//Defaults
$instance = wp_parse_args( (array) $field_obj->instance, array( 'title' => '', 'description' => '', 'options' => '', 'empty_option' => '' ) );
$title = esc_attr( $instance['title'] );
$options = esc_attr( $field_obj->getInstanceSelectOptions($instance) );
$description = esc_html($instance['description']);
$empty_option = esc_attr( $instance['empty_option']);

?>
<p><label for="<?php echo $field_obj->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $field_obj->getFieldId('title'); ?>" name="<?php echo $field_obj->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><label for="<?php echo $field_obj->getFieldId('options'); ?>"><?php _e('Options:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
<textarea class="widefat" id="<?php echo $field_obj->getFieldId('options'); ?>" name="<?php echo $field_obj->getFieldName('options'); ?>" ><?php echo $options; ?></textarea>
<br/><small><?php _e('Parameters like (you can use just "label" if "id" is the same):<br>label1|id1<br>label2|id2<br>label3', \jcf\JustCustomFields::TEXTDOMAIN); ?></small></p>
<p><label for="<?php echo $field_obj->getFieldId('empty_option'); ?>"><?php _e('Empty option:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label><input class="widefat" id="<?php echo $field_obj->getFieldId('empty_option'); ?>" name="<?php echo $field_obj->getFieldName('empty_option'); ?>" placeholder="ex. Choose item from the list"" type="text" value="<?php echo $empty_option; ?>" />
<br/><small><?php _e('Leave blank to disable empty option', \jcf\JustCustomFields::TEXTDOMAIN); ?></small></p>
<p><label for="<?php echo $field_obj->getFieldId('description'); ?>"><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $field_obj->getFieldName('description'); ?>" id="<?php echo $field_obj->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>


