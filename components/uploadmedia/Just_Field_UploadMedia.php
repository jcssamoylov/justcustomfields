<?php
namespace jcf\components\uploadmedia;
use jcf\models;

/**
 *	Upload media field
 */
class Just_Field_UploadMedia extends models\Just_Field{
	
	public static $compatibility = '3.3+';

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_uploadmedia' );
		parent::__construct('uploadmedia', __('Upload Media', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field()
	{
		$del_image = WP_PLUGIN_URL . '/just-custom-fields/components/uploadmedia/assets/jcf-delimage.png';
		$noimage = $image = WP_PLUGIN_URL . '/just-custom-fields/components/uploadmedia/assets/jcf-noimage100x77.jpg';
		$upload_text = __('Upload', \jcf\JustCustomFields::TEXTDOMAIN);
		$delete_class = ' jcf-hide';
		$upload_type = $this->instance['type'];
		$value = '#';
		$img_title = $img_descr = '';
		
		if ( empty($this->entry) ) $this->entry = array('0' => '');
		// add null element for etalon copy
		$entries = array( '00' => '' ) + (array)$this->entry;
		
		include(JCF_ROOT . '/components/uploadmedia/views/field.tpl.php');
		return true;
	}
	
	/**
	 *	save field on post edit form
	 */
	public function save( $_values )
	{
		$values = array();
		if ( empty($_values) ) return $values;
	
		// get autoresize property.
		$autoresize = '';
		if ( !empty($this->instance['autoresize']) ) {
			$autoresize = explode('x', $this->instance['autoresize']);
			if ( count($autoresize) != 2 ) $autoresize = '';
		}
	
		// remove etalon element
		if ( isset($_values['00']) ) 
			unset($_values['00']);
		
		foreach ( $_values as $key => $params ) {
			if ( !empty($params['delete']) ) continue;

			if ( !empty($params['uploaded_file']) && $params['uploaded_file'] != '#' ) {
				$value = $params['uploaded_file'];
				$file = array(
					'image' => $value,
					'title' => $params['alt_title'],
					'description' => $params['alt_descr'],
				);

				if ( !empty($autoresize) ) {
					// wordpress resize
					$imagepath = ABSPATH . str_replace( get_bloginfo('home').'/', '', $value );
					$thumbpath = image_resize($imagepath, $autoresize[0], $autoresize[1]);
					// get link
					if ( is_string($thumbpath) && $thumbpath != '' ) {
						@chmod($thumbpath, 0777);
						$value = get_bloginfo('home').'/' . str_replace(ABSPATH, '', $thumbpath);
						$file['image'] = $value;
					}
				}
				$values[$key] = $file;
			}
		}
		$values = array_values($values);
		return $values;
	}

	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] 			= strip_tags($new_instance['title']);
		$instance['type'] 			= strip_tags($new_instance['type']);
		$instance['autoresize'] 	= strip_tags($new_instance['autoresize']);
		$instance['description'] 	= strip_tags($new_instance['description']);
		$instance['alt_title'] 		= strip_tags(@$new_instance['alt_title']);
		$instance['alt_descr'] 		= strip_tags(@$new_instance['alt_descr']);
		return $instance;
	}

	/**
	 *	custom get_field functions to add one more deep level
	 */
	protected function getFieldIdL2( $field, $number )
	{
		return $this->get_field_id( $number . '-' . $field );
	}

	protected function getFieldNameL2( $field, $number )
	{
		return $this->get_field_name( $number . '][' . $field );
	}
	
	/**
	 *	add custom scripts
	 */
	public function addJs()
	{
		wp_register_script(
				'jcf_uploadmedia',
				WP_PLUGIN_URL.'/just-custom-fields/components/uploadmedia/assets/uploadmedia.js',
				array('jquery','media-upload','thickbox')
			);
		wp_enqueue_script('jcf_uploadmedia');

		// add text domain if not registered with another component
		global $wp_scripts;
		if ( empty($wp_scripts->registered['jcf_fields_group']) && empty($wp_scripts->registered['jcf_related_content']) ) {
			wp_localize_script( 'jcf_uploadmedia', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}
	
	public function addCss()
	{
		wp_register_style('jcf_uploadmedia',
				WP_PLUGIN_URL.'/just-custom-fields/components/uploadmedia/assets/uploadmedia.css',
				array('thickbox'));
		wp_enqueue_style('jcf_uploadmedia');
	}

	/**
	 *	print fields values from shortcode
	 */
	public function shortcode_value($args)
	{
		if (empty($this->entry) ) return '';

		$html = '';

		foreach ( $this->entry as $key => $value ) {
			$html .= '<div class="jcf-item jcf-item-i'.$key.'">';
			$html .= '<div class="jcf-item-image">';
			$html .= '	<img src="' . esc_attr($value['image']) . '" ' . (!empty($value['title']) ? 'alt="' . esc_attr($value['title']) . '" ' : '') . ' />';
			$html .= '</div>';

			if ( !empty($value['title']) )
				$html .= '<span class="jcf-item-title">' . esc_html($value['title']) . '</span>';

			if ( !empty($value['description']) )
				$html .= '<div class="jcf-item-description">' . esc_html($value['description']) . '</div>';

			$html .= '</div>';
		}
		return  $args['before_value'] . $html . $args['after_value'];
	}

	/**
	*	function to get link to the thumbnail script
	*/
	private function getThumbPath( $image, $size = '100x77' )
	{
		$cachedir = WP_CONTENT_DIR . '/uploads/jcfupload';
		$new_size = explode('x', $size);
		// check file extension
		$filetype = wp_check_filetype($image);
		
		if ( empty($filetype['ext']) ) return '#';

		$ext = $filetype['ext'];
		// check if thumb already exists:
		$hash = md5($image.$new_size[0].'x'.$new_size[1]);
		$thumbfile = $cachedir . '/' . $hash . '.' . $ext;
		if ( is_file($thumbfile) ) {
			return get_bloginfo('url') . '/wp-content/uploads/jcfupload/' . basename($thumbfile);
		}
		else {
			return get_bloginfo('url') . '/wp-content/plugins/just-custom-fields/components/uploadmedia/thump.php?image='.rawurlencode($image).'&amp;size='.$size;
		}
	}

}
?>