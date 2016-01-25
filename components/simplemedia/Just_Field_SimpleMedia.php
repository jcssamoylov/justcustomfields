<?php
namespace jcf\components\simplemedia;
use jcf\models;

/**
 *	Simple Upload media field
 */
class Just_Field_SimpleMedia extends models\Just_Field
{
	public static $compatibility = "4.0+";


	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_simplemedia' );
		parent::__construct('simplemedia', __('Simple Media', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
			
	}

	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field()
	{	
		$del_image = WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/jcf-delimage.png';
		$noimage = $image = WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/jcf-noimage100x77.jpg';
		$delete_class = ' jcf-hide';
		$upload_type = $this->instance['type'];
		$upload_text = ($upload_type == 'image') ? __('Select image', \jcf\JustCustomFields::TEXTDOMAIN) : __('Select file', \jcf\JustCustomFields::TEXTDOMAIN);
		$value = $link = '#';
		
		if ( empty($this->entry) ) 
			$this->entry = 0;
		
		include(JCF_ROOT . '/components/simplemedia/views/field.tpl.php');
		
		return true;
	}
	
	/**
	 *	save field on post edit form
	 */
	public function save( $_values )
	{
		$value = 0;
		if ( empty($_values) ) return $value;
		if ( isset($_values['uploaded_file']) && intval($_values['uploaded_file']) ) return $_values['uploaded_file'];
		return $value;
	}
		
	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] 			= strip_tags($new_instance['title']);
		$instance['type'] 			= strip_tags($new_instance['type']);
		$instance['description'] 	= strip_tags($new_instance['description']);
		return $instance;
	}
	
	/**
	 *	add custom scripts
	 */
	public function addJs(){
		global $pagenow, $wp_version, $post_ID;
		// only load on select pages 
		if ( ! in_array( $pagenow, array( 'post-new.php', 'post.php', 'media-upload-popup' ) ) ) return;
		wp_enqueue_media( array( 'post' => ( $post_ID ? $post_ID : null ) ) );
		wp_enqueue_script( "jcf-simpleupload-modal", WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/simplemedia-modal.js', array( 'jquery', 'media-models') );				

		// add text domain if not registered with another component
		global $wp_scripts;
		if ( empty($wp_scripts->registered['jcf_fields_group']) && empty($wp_scripts->registered['jcf_related_content']) ) {
			wp_localize_script( 'jcf_simplemedia', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}
	
	public function addCss(){
		wp_register_style('jcf_simplemedia',
				WP_PLUGIN_URL.'/just-custom-fields/components/simplemedia/assets/simplemedia.css',
				array('thickbox'));
		wp_enqueue_style('jcf_simplemedia');
	}
	
	
}
?>
