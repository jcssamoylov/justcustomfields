<?php
// get registered post types
function jcf_get_post_types( $format = 'single' ){
	
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
function jcf_get_language_strings(){
	global $wp_version;

	$strings = array(
		'hi' => __('Hello there', JCF_TEXTDOMAIN),
		'edit' => __('Edit', JCF_TEXTDOMAIN),
		'delete' => __('Delete', JCF_TEXTDOMAIN),
		'confirm_field_delete' => __('Are you sure you want to delete selected field?', JCF_TEXTDOMAIN),
		'confirm_fieldset_delete' => __("Are you sure you want to delete the fieldset?\nAll fields will be also deleted!", JCF_TEXTDOMAIN),
		'update_image' => __('Update Image', JCF_TEXTDOMAIN),
		'update_file' => __('Update File', JCF_TEXTDOMAIN),
		'yes' => __('Yes', JCF_TEXTDOMAIN),
		'no' => __('No', JCF_TEXTDOMAIN),
		'no_term' => __('The term doesn\'t exist', JCF_TEXTDOMAIN),
		'no_templates' => __('The template doesn\'t exist', JCF_TEXTDOMAIN),
		'slug' => __('Slug', JCF_TEXTDOMAIN),
		'type' => __('Type', JCF_TEXTDOMAIN),
		'enabled' => __('Enabled', JCF_TEXTDOMAIN),

		'wp_version' => $wp_version,
	);
	$strings = apply_filters('jcf_localize_script_strings', $strings);
	return $strings;
}

/**
 * print response (encode to json if needed) callback
 */
function jcf_ajax_response( $resp, $format = 'json' ){
	if( $format == 'json' ){
		$resp = json_encode($resp);
		header( "Content-Type: application/json; charset=" . get_bloginfo('charset') );
	}
	else{
		header( "Content-Type: text/html; charset=" . get_bloginfo('charset') );
	}
	echo $resp;
	exit();
}

/**
 *	Json formater
 *	@param string $json Data of settings for fields
 *	@return string Return formated json string with settings for fields
 */
function jcf_format_json($json){
	$tabcount = 0;
	$result = '';
	$inquote = false;
	$ignorenext = false;
	$tab = "\t";
	$newline = "\n";

	for( $i = 0; $i < strlen($json); $i++ ){
		$char = $json[$i];
		if( $ignorenext ){
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
					if( $json[$i + 1] != ' ' ){
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
	if( @chmod( $filename, $dir_perms ) ){
		return true;
	}
	else{
		return false;
	}
}

// print image with loader
function print_loader_img(){
	return '<img class="ajax-feedback " alt="" title="" src="' . get_bloginfo('wpurl') . '/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">';
}