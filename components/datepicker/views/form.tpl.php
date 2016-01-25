<?php
//Defaults
$instance = wp_parse_args( (array) $field_obj->instance, array( 'title' => '' ) );

$title = esc_attr( $instance['title'] );
$show_monthes = !empty($instance['show_monthes'])? ' checked="checked" ' : '';
$date_format =  !empty($instance['date_format']) ? $instance['date_format'] : 'yy-mm-dd' ;
?>
<p><label for="<?php echo $field_obj->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $field_obj->getFieldId('title'); ?>" name="<?php echo $field_obj->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><label for="<?php echo $field_obj->getFieldId('show_monthes'); ?>"><input class="checkbox" id="<?php echo $field_obj->getFieldId('show_monthes'); ?>" name="<?php echo $field_obj->getFieldName('show_monthes'); ?>" type="checkbox" value="1" <?php echo $show_monthes; ?> /> <?php _e('Show month/year select boxes', \jcf\JustCustomFields::TEXTDOMAIN); ?></label></p>
<p><label for="<?php echo $field_obj->getFieldId('date_format'); ?>"><?php _e('Date format:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
	<input class="widefat" id="<?php echo $field_obj->getFieldId('date_format'); ?>"
		   name="<?php echo $field_obj->getFieldName('date_format'); ?>" type="text"
		   value="<?php echo $date_format; ?>" /><br />
	<small><?php _e('Example:', \jcf\JustCustomFields::TEXTDOMAIN);?> yy-mm-dd <a href="http://api.jqueryui.com/datepicker/#option-dateFormat" target="_blank"><?php _e('look more about date formats', \jcf\JustCustomFields::TEXTDOMAIN);?></a></small>
</p>
		