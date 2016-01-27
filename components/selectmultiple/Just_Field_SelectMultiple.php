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
	public function field() 
	{
		if( !is_array($this->entry) )
			$this->entry = array();
		// prepare options array
		$values = $this->parsedSelectOptions($this->instance);
		?>
			<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
				<div class="form-field">
					<label><?php echo $this->instance['title']; ?>:</label>
					<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
						<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
					</div>
					<div class="select_multiple_field">
					<select name="<?php echo $this->getFieldName('val'); ?>[]" id="<?php echo $this->getFieldId('val'); ?>" multiple="multiple" style="height:200px; width:47%;">
						<?php foreach( $values as $key => $val ): ?>
							<option value="<?php echo esc_attr($val); ?>" <?php echo selected(true, in_array($val, $this->entry), false); ?>><?php echo esc_html(ucfirst($key)); ?></option>
						<?php endforeach; ?>
					</select>
					</div>
					<?php if( $this->instance['description'] != '' ) : ?>
						<p class="description"><?php echo $this->instance['description']; ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php
	}

	/**
	 * draw form for edit field
	 */
	public function form()
	{
		//Defaults
		$instance = wp_parse_args( (array) $this->instance, array( 'title' => '', 'description' => '', 'settings' => '' ) );

		$title = esc_attr( $instance['title'] );
		$settings = esc_attr( $instance['settings'] );
		$description = esc_html($instance['description']);
		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->getFieldId('settings'); ?>"><?php _e('Settings:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
		<textarea class="widefat" id="<?php echo $this->getFieldId('settings'); ?>" name="<?php echo $this->getFieldName('settings'); ?>" ><?php echo $settings; ?></textarea>
		<br/><small><?php _e('Parameters like (you can use just "label" if "id" is the same):<br>label1|id1<br>label2|id2<br>label3', \jcf\JustCustomFields::TEXTDOMAIN); ?></small></p>
		<p><label for="<?php echo $this->getFieldId('description'); ?>"><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <textarea name="<?php echo $this->getFieldName('description'); ?>" id="<?php echo $this->getFieldId('description'); ?>" cols="20" rows="4" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
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