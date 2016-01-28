<?php
if( !function_exists('pa') ) {
	function pa($mixed, $stop = false) {
		$ar = debug_backtrace(); $key = pathinfo($ar[0]['file']); $key = $key['basename'].':'.$ar[0]['line'];
		$print = array($key => $mixed); echo( '<pre>'.htmlentities(print_r($print,1)).'</pre>' );
		if($stop == 1) exit();
	}
}

function jcf_get_current_screen() {
	$screen = get_current_screen();
	return $screen;
}

/**
 * get registered post types
 * @param string $format
 * @return string 
 */
function jcf_get_post_types( $format = 'single' ) {
	
	$all_post_types = get_post_types(array('show_ui' => true ), 'object');
	
	$post_types = array();
	
	foreach($all_post_types as $key=>$val){
		
		//we should exclude 'revision' and 'nav menu items'
		if($val == 'revision' || $val == 'nav_menu_item') continue;
		
		$post_types[$key] = $val;
	}
	
	return $post_types;
}

/**
 *	javascript localization
 */
function jcf_get_language_strings() {
	global $wp_version;

	$strings = array(
		'hi' => __('Hello there', \jcf\JustCustomFields::TEXTDOMAIN),
		'edit' => __('Edit', \jcf\JustCustomFields::TEXTDOMAIN),
		'delete' => __('Delete', \jcf\JustCustomFields::TEXTDOMAIN),
		'confirm_field_delete' => __('Are you sure you want to delete selected field?', \jcf\JustCustomFields::TEXTDOMAIN),
		'confirm_fieldset_delete' => __("Are you sure you want to delete the fieldset?\nAll fields will be also deleted!", \jcf\JustCustomFields::TEXTDOMAIN),
		'update_image' => __('Update Image', \jcf\JustCustomFields::TEXTDOMAIN),
		'update_file' => __('Update File', \jcf\JustCustomFields::TEXTDOMAIN),
		'yes' => __('Yes', \jcf\JustCustomFields::TEXTDOMAIN),
		'no' => __('No', \jcf\JustCustomFields::TEXTDOMAIN),
		'no_term' => __('The term doesn\'t exist', \jcf\JustCustomFields::TEXTDOMAIN),
		'no_templates' => __('The template doesn\'t exist', \jcf\JustCustomFields::TEXTDOMAIN),
		'slug' => __('Slug', \jcf\JustCustomFields::TEXTDOMAIN),
		'type' => __('Type', \jcf\JustCustomFields::TEXTDOMAIN),
		'enabled' => __('Enabled', \jcf\JustCustomFields::TEXTDOMAIN),

		'wp_version' => $wp_version,
	);
	$strings = apply_filters('jcf_localize_script_strings', $strings);
	return $strings;
}

/**
 * print response (encode to json if needed) callback
 */
//function jcf_ajax_response( $resp, $format = 'json' ){
//	if ( $format == 'json' ) {
//		$resp = json_encode($resp);
//		header( "Content-Type: application/json; charset=" . get_bloginfo('charset') );
//	}
//	else {
//		header( "Content-Type: text/html; charset=" . get_bloginfo('charset') );
//	}
//	echo $resp;
//	exit();
//}

/**
 *	Json formater
 *	@param string $json Data of settings for fields
 *	@return string Return formated json string with settings for fields
 */
function jcf_format_json($json) {
	$tabcount = 0;
	$result = '';
	$inquote = false;
	$ignorenext = false;
	$tab = "\t";
	$newline = "\n";

	for ( $i = 0; $i < strlen($json); $i++ ) {
		$char = $json[$i];
		if ( $ignorenext ) {
			$result .= $char;
			$ignorenext = false;
		}
		else {
			switch( $char ) {
				case '{':
					$tabcount++;
					$result .= $char . $newline . str_repeat($tab, $tabcount);
					break;
				case '}':
					$tabcount--;
					$result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
					break;
				case ',':
					if ( $json[$i + 1] != ' ' ) {
						$result .= $char . $newline . str_repeat($tab, $tabcount);
						break;
					}
				case '"':
					$inquote = !$inquote;
					$result .= $char;
					break;
				case '\\':
					if ($inquote) $ignorenext = true;
					$result .= $char;
					break;
				default:
					$result .= $char;
			}
		}
	}
	return $result;
}

/**
 *	Set permisiions for file
 *	@param string $dir Parent directory path
 *	@param string $filename File path
 */
function jcf_set_chmod($filename){
	$dir_perms = fileperms(dirname($filename));
	if ( @chmod( $filename, $dir_perms ) ) {
		return true;
	}
	else {
		return false;
	}
}

// print image with loader
function jcf_print_loader_img() {
	return '<img class="ajax-feedback " alt="" title="" src="' . get_bloginfo('wpurl') . '/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">';
}