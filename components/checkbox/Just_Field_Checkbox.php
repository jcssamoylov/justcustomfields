<?php
namespace jcf\components\checkbox;
use jcf\models;

/**
 * Class for group of checkboxes
 *
 * @package default
 * @author Alexander Prokopenko
 */
class Just_Field_Checkbox extends models\Just_Field{
	
	public function __construct() {
		$field_ops = array('classname' => 'field_checkbox' );
		parent::__construct('checkbox', __('Checkbox', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field() 
	{		
		// prepare options array
		$values = $this->parsedSelectOptions($this->instance);
		
		if ( empty($values) ) {
			echo '<p>'.__('Please check settings. Values are empty', \jcf\JustCustomFields::TEXTDOMAIN).'</p>';
			return false;
		}

		$single_checkbox = (count($values) == 1) ? true : false;
		include(JCF_ROOT . '/components/checkbox/views/field.tpl.php');
	}
	
	/**
	 *	save field on post edit form
	 */
	public function save( $values ) 
	{
		$values = isset($values['val']) ? $values['val'] : '' ;
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance ) 
	{
		$instance = $old_instance;
		$instance['title'] 			= strip_tags($new_instance['title']);
		$instance['settings'] 		= strip_tags($new_instance['settings']);
		$instance['description'] 	= strip_tags($new_instance['description']);
		return $instance;
	}

	/**
	 * prepare list of options
	 * 
	 * @param array $instance	current instance
	 */
	protected function parsedSelectOptions($instance)
	{
		$values = array();
		$v = explode("\n", $instance['settings']);

		foreach ( $v as $val ) {
			$val = trim($val);

			if ( strpos($val, '|') !== FALSE ) {
				$a = explode('|', $val);
				$values[$a[0]] = $a[1];
			}
			elseif ( !empty($val) ) {
				$values[$val] = $val;
			}
		}
		return $values;
	}
	
	/**
	 *	print fields values from shortcode
	 */
	public function shortcodeValue($args)
	{
		$options = $this->parsedSelectOptions($this->instance);
		$options = array_flip($options);

		if ( empty($this->entry) ) return '';

		$html = '<ul class="jcf-list">';
		foreach ( $this->entry as $value ) {
			$key = preg_replace('/\s+/', '-', $value);
			$key = preg_replace('/[^0-9a-z\-\_]/i', '', $key);
			if ( isset($options[$value]) ) {
				$value = $options[$value];
			}
			$html .= "<li class=\"jcf-item jcf-item-$key\">$value</li>\r\n";
		}
		$html .= '</ul>';

		return  $args['before_value'] . $html . $args['after_value'];
	}

}
?>