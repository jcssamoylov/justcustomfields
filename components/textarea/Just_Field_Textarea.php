<?php
namespace jcf\components\textarea;
use jcf\models;
/**
 * Textarea field type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class Just_Field_Textarea extends models\Just_Field{
	
	public function __construct() 
	{
		$field_ops = array( 'classname' => 'field_textarea' );
		parent::__construct('textarea', __('Textarea', JCF_TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field() 
	{
		include(JCF_ROOT . '/components/textarea/views/field.tpl.php');
	}
	
	/**
	 *	save field on post edit form
	 */
	public function save( $values ) 
	{
		global $wp_version;
		$values = isset($values['val']) ? $values['val'] : '' ;
		
		if ($this->instance['editor']) {
			if ($wp_version <= 3.2) {
				$values = nl2br(wpautop($values));
			}
			else {
				$values = wpautop($values);
			}
		}
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance ) 
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['editor'] = (int)@$new_instance['editor'];
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}
	
	/**
	 *	load custom script for tiny MCE for editors
	 */
	public function customTinyMCE()
	{
		global $jcf_flag_tiny_mce;
		
		if ( !empty($jcf_flag_tiny_mce) || ! user_can_richedit() )
			return;
		
		// just use standard tinyMCE for our textarea class
		// rewrite toolbar: remove "more" button, add "html" button
		?>
		<script type="text/javascript"><!--
			tinyMCEPreInit.mceInit.editor_selector = 'mceEditor';
			tinyMCEPreInit.mceInit.theme_advanced_buttons1 = 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,spellchecker,fullscreen,wp_adv,|,code';
			tinyMCE.init(tinyMCEPreInit.mceInit);
		--></script>
		
		<?php
		$jcf_flag_tiny_mce = true;
	}
}
?>