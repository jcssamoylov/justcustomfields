<?php
namespace jcf\components\selectmultiple;
use jcf\models;

/**
 * Class for select multiple list type
 *
 * @package default
 * @author Alexander Prokopenko
 */
class Just_Field_SelectMultiple extends models\Just_Field{
	
	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_selectmultiple' );
		parent::__construct('selectmultiple', __('Select Multiple', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field( $args ) 
	{
		extract( $args );
		
		if( !is_array($this->entry) )
			$this->entry = array();

		// prepare options array
		$values = $this->parsedSelectOptions($this->instance);
			
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		echo '<div class="select_multiple_field">';
		echo '<select name="'.$this->getFieldName('val').'[]" id="'.$this->getFieldId('val').'" multiple="multiple" style="height:200px; width:47%;">';
			foreach( $values as $key => $val ) {
				echo '<option value="'.esc_attr($val).'" '.selected(true, in_array($val, $this->entry), false).'>'.esc_html(ucfirst($key)).'</option>' . "\n";
			}
		echo '</select>' . "\n";
		echo '</div>';
		if( $this->instance['description'] != '' )
			echo '<p class="description">' . $this->instance['description'] . '</p>';
		echo $after_widget;

	}
	
	/**
	 *	save field on post edit form
	 */
	public function save( $values )
	{
		$values = $values['val'];
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['settings'] = strip_tags($new_instance['settings']);
		$instance['description'] = strip_tags($new_instance['description']);
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
		foreach($v as $val){
			$val = trim($val);
			if(strpos($val, '|') !== FALSE ){
				$a = explode('|', $val);
				$values[$a[0]] = $a[1];
			}
			elseif(!empty($val)){
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
		
		if( empty($this->entry) ) return '';
		
		$html = '<ul class="jcf-list">';
		foreach($this->entry as $value){
			$key = preg_replace('/\s+/', '-', $value);
			$key = preg_replace('/[^0-9a-z\-\_]/i', '', $key);
			if(isset($options[$value])){
				$value = $options[$value];
			}
			$html .= "<li class=\"jcf-item jcf-item-$key\">$value</li>\r\n";
		}
		$html .= '</ul>';
		
		return  $args['before_value'] . $html . $args['after_value'];
	}

}
?>