<?php 
//Defaults
$instance = wp_parse_args( (array) $field_obj->instance, array( 'title' => '', 'post_type' => 'page', 'input_type' => 'autocomplete',
		'description' => __('Start typing entry Title to see the list.', \jcf\JustCustomFields::TEXTDOMAIN) ) );

$title = esc_attr( $instance['title'] );
$description = esc_html($instance['description']);

$post_types = jcf_get_post_types( 'object' );

?>
<p><label for="<?php echo $field_obj->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
	<input class="widefat" id="<?php echo $field_obj->getFieldId('title'); ?>" name="<?php echo $field_obj->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

<p><label for="<?php echo $field_obj->getFieldId('post_type'); ?>"><?php _e('Post type:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
	<select name="<?php echo $field_obj->getFieldName('post_type'); ?>" id="<?php echo $field_obj->getFieldId('post_type'); ?>">
		<option value="any" <?php selected('any', $instance['post_type']); ?>><?php _e('All', \jcf\JustCustomFields::TEXTDOMAIN); ?></option>
		<?php foreach($post_types as $pt_id => $pt) : ?>
		<option value="<?php echo $pt_id; ?>" <?php selected($pt_id, $instance['post_type']); ?>><?php echo $pt->label; ?></option>
		<?php endforeach; ?>
	</select>
</p>

<p><label for="<?php echo $field_obj->getFieldId('input_type'); ?>"><?php _e('Input type:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
	<select name="<?php echo $field_obj->getFieldName('input_type'); ?>" id="<?php echo $field_obj->getFieldId('input_type'); ?>">
		<option value="autocomplete" <?php selected('autocomplete', $instance['input_type']); ?>><?php _e('Autocomplete', \jcf\JustCustomFields::TEXTDOMAIN); ?></option>
		<option value="select" <?php selected('select', $instance['input_type']); ?>><?php _e('Dropdown list', \jcf\JustCustomFields::TEXTDOMAIN); ?></option>
	</select>
</p>

<p><label for="<?php echo $field_obj->getFieldId('description'); ?>"><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
	<textarea name="<?php echo $field_obj->getFieldName('description'); ?>" id="<?php echo $field_obj->getFieldId('description'); ?>" cols="20" rows="2" class="widefat"><?php echo $description; ?></textarea></p>
		