<?php
namespace jcf\components\fieldsgroup;
use jcf\models;

/**
 *	Fields group field.
 *	allow you to add "table" of fields
 */
class Just_Field_FieldsGroup extends models\Just_Field{
	
	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_fieldsgroup' );
		parent::__construct('fieldsgroup', __('Fields Group', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field() 
	{
		extract( $args );
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;

		$del_image = WP_PLUGIN_URL . '/just-custom-fields/components/uploadmedia/assets/jcf-delimage.png';
		$delete_class = ' jcf-hide';
		
		if ( empty($this->entry) ) $this->entry = array('0' => '');

		// add null element for etalon copy
		$entries = array( '00' => '' ) + (array)$this->entry;

		// get fields
		$fields = $this->parseFieldsOptions();
		?>
		<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
			<div class="form-field">
				<label><?php echo $this->instance['title']; ?>:</label>
				<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
					<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
				</div>

				<?php if ( empty($fields) ) : ?>
					<p><?php echo __('Wrong fields configuration. Please check widget settings.', \jcf\JustCustomFields::TEXTDOMAIN); ?></p>
				<?php else : ?>
				<div class="jcf-fieldsgroup-field jcf-field-container">
					<?php foreach( $entries as $key => $entry ) : ?>
					<div class="jcf-fieldsgroup-row<?php if('00' === $key) echo ' jcf-hide'; ?>">
						<span class="drag-handle" >move</span>
						<div class="jcf-fieldsgroup-container">
							<?php foreach ( $fields as $field_name => $field_title ) : 
								$field_value = esc_attr(@$entry[$field_name]);
							?>
								<p><?php echo $field_title ?>: <br/>
									<input type="text" value="<?php echo $field_value; ?>" 
										id="<?php echo $this->getFieldIdL2($field_name, $key); ?>" 
										name="<?php echo $this->getFieldNameL2($field_name, $key); ?>">
								</p>
							<?php endforeach; ?>
							<a href="#" class="jcf-btn jcf_delete"><?php _e('Delete', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
						</div>
						<div class="jcf-delete-layer">
							<img src="<?php echo $del_image; ?>" alt="" />
							<input type="hidden" id="<?php echo $this->getFieldIdL2('__delete__', $key); ?>" name="<?php echo $this->getFieldNameL2('__delete__', $key); ?>" value="" />
							<a href="#" class="jcf-btn jcf_cancel"><?php _e('Cancel', \jcf\JustCustomFields::TEXTDOMAIN); ?></a><br/>
						</div>
					</div>
					<?php endforeach; ?>
					<a href="#" class="jcf-btn jcf_add_more"><?php _e('+ Add another', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
				</div>
				<?php endif; ?>

				<?php if( !empty($this->instance['description']) ) : ?>
					<p class="description"><?php echo $this->instance['description']; ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return true;
	}

	/**
	 * draw form for edit field
	 */
	public function form()
	{
		//Defaults
		$instance = wp_parse_args( (array) $this->instance, array( 'title' => '', 'fields' => '', 'description' => '' ) );
		$title = esc_attr( $instance['title'] );
		$fields = esc_html( $instance['fields'] );
		$description = esc_html($instance['description']);
		?>
		<div class="error"><?php _e('This field is <b>deprecated</b>. Please use Collection instead.', \jcf\JustCustomFields::TEXTDOMAIN); ?></div>

		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
			<input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->getFieldId('fields'); ?>"><?php _e('Fields:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
			<textarea name="<?php echo $this->getFieldName('fields'); ?>" id="<?php echo $this->getFieldId('fields'); ?>" cols="20" rows="4" class="widefat"><?php echo $fields; ?></textarea>
			<br/><small><?php _e('Format: %fieldname|%fieldtitle<br/><i>Example: price|Product Price', \jcf\JustCustomFields::TEXTDOMAIN); ?></i></small></p>
		<p><label for="<?php echo $this->getFieldId('description'); ?>"><?php _e('Description:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> 
			<textarea name="<?php echo $this->getFieldName('description'); ?>" id="<?php echo $this->getFieldId('description'); ?>" cols="20" rows="2" class="widefat"><?php echo $description; ?></textarea></p>
		<?php
	}

	/**
	 *	save field on post edit form
	 */
	public function save( $_values )
	{
		$values = array();
		if ( empty($_values) ) return $values;
	
		// remove etalon element
		if ( isset($_values['00']) ) 
			unset($_values['00']);
		
		// fill values
		foreach ( $_values as $key => $params ) {
			if ( !is_array($params) || !empty($params['__delete__']) ) {
				continue;
			}

			unset($params['__delete__']);
			$values[$key] = $params;
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
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['fields'] = strip_tags($new_instance['fields']);
		$instance['description'] = strip_tags($new_instance['description']);
		return $instance;
	}

	/**
	 *	custom get_field functions to add one more deep level
	 */
	protected function getFieldIdL2( $field, $number )
	{
		return $this->getFieldId( $number . '-' . $field );
	}

	protected function getFieldNameL2( $field, $number )
	{
		return $this->getFieldName( $number . '][' . $field );
	}
	
	/**
	 *	add custom scripts
	 */
	public function addJs()
	{
		wp_register_script(
				'jcf_fields_group',
				WP_PLUGIN_URL.'/just-custom-fields/components/fieldsgroup/fields-group.js',
				array('jquery')
			);
		wp_enqueue_script('jcf_fields_group');

		// add text domain if not registered with another component
		global $wp_scripts;
		if ( empty($wp_scripts->registered['jcf_related_content']) && empty($wp_scripts->registered['jcf_uploadmedia']) ) {
			wp_localize_script( 'jcf_fields_group', 'jcf_textdomain', jcf_get_language_strings() );
		}
	}
	
	public function addCss()
	{
		wp_register_style('jcf_fields_group', WP_PLUGIN_URL.'/just-custom-fields/components/fieldsgroup/fields-group.css');
		wp_enqueue_style('jcf_fields_group');
	}
	
	/**
	 * Parse fields settings
	 * @return array
	 */
	protected function parseFieldsOptions()
	{
		$fields = array();
		$_fields = explode("\n", $this->instance['fields']);

		foreach ( $_fields as $line ) {
			$line = trim($line);
			$field = explode('|', $line);

			if ( count($field) == 2 ) {
				$fields[ $field[0] ] = $field[1];
			}
		}
		return $fields;
	}

	/**
	 *	print fields values from shortcode
	 */
	public function shortcodeValue($args)
	{
		$fields = $this->parseFieldsOptions();
		if ( empty($this->entry) || empty($fields) ) return '';

		$html = '';

		foreach ( $this->entry as $key_entry => $entry ) {
			$html .= '<div class="jcf-item jcf-item-i' . $key_entry . '">';
			foreach ( $entry as $key => $value ) {
				$title = $fields[$key];
				$html .= '<div class="jcf-cell jcf-cell-' . $key . '"><span class="jcf-cell-label">' . esc_html($title) . '</span><span class="jcf-cell-value">' . esc_html($value) . '</span></div>';
			}
			$html .= '</div>';
		}
		return  $args['before_value'] . $html . $args['after_value'];
	}
}
?>