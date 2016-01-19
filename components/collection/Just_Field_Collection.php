<?php
namespace jcf\components\collection;
use jcf\models;

/**
 * Class for Collection type
 *
 * @package default
 * @author Kirill samojlenko
 */
class Just_Field_Collection extends models\Just_Field{
	
	public static $compatibility = "4.0+";
	
	public static $currentCollectionFieldKey = 0;

	public static $fieldWidth = array(
		'100' => '100%',
		'75' => '75%',
		'50' => '50%',
		'33' => '33%',
		'25' => '25%',
	);
	public $_fieldFactory;

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_collection' );
		parent::__construct('collection', __('Collection', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
		
		$this->_fieldFactory = new models\JustFieldFactory(TRUE);
		$this->_fieldFactory->register( 'Just_Field_InputText' );
		$this->_fieldFactory->register( 'Just_Field_Select' );
		$this->_fieldFactory->register( 'Just_Field_SelectMultiple' );
		$this->_fieldFactory->register( 'Just_Field_Checkbox' );
		$this->_fieldFactory->register( 'Just_Field_Textarea' );
		$this->_fieldFactory->register( 'Just_Field_DatePicker' );
		$this->_fieldFactory->register( 'Just_Field_Simple_Media' );
		$this->_fieldFactory->register( 'Just_Field_Table' );
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field( $args ) 
	{
		extract( $args );
		self::$currentCollectionFieldKey = 0;

		if (empty($this->entry)) $this->entry = array('0' => '');

		$entries = (array)$this->entry;
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		
		if ( empty($this->instance['fields']) ) {
			echo '<p class="error">Collection element has no fields registered. Please check component settings</p>';
			echo $after_widget;
			return;
		}
		?>

		<div class="collection_fields">
		<?php foreach ( $entries as $key => $fields ) { ?>
			<div class="collection_field_group">
				<h3>
					<span class="dashicons dashicons-editor-justify"></span>
					<span class="collection_group_title">
					<?php
						$group_title = $this->instance['title'].' Item';
						foreach($this->instance['fields'] as $field_id => $field){
							if(isset($field['group_title'])){
								if(isset($fields[$field['slug']])) $group_title = $group_title.' : '.$fields[$field['slug']];
								break;
							}
						}
						echo $group_title;
					 ?>
					</span>
					<a href="#" class="collection_undo_remove_group"><?php _e('UNDO',\jcf\JustCustomFields::TEXTDOMAIN); ?></a>
					<span class="dashicons dashicons-trash"></span>

				</h3>
				<div class="collection_field_group_entry">
					<?php					
						foreach ( $this->instance['fields'] as $field_id => $field ) {
							echo '<div class="collection_field_border jcf_collection_'.(intval($field['field_width'])?$field['field_width']:'100').'">';
							$field_obj = $this->_fieldFactory->initObject($this->postType, $field_id, $this->fieldsetId, $this->id);
							$field_obj->setSlug($field['slug']);

							if ( isset($fields[$field['slug']]) ) {
								$field_obj->entry = $fields[$field['slug']];
							}

							$field_obj->instance = $field;
							$field_obj->isPostEdit = true;
							$field_obj->field($field_obj->field_options);
							echo '</div>';
						}
					?>
					<div class="clr"></div>
				</div>
			</div>
			<?php self::$currentCollectionFieldKey = self::$currentCollectionFieldKey + 1; } ?>
			<div class="clr"></div>
			<input type="button" value="<?php echo sprintf(__('Add %s Item', \jcf\JustCustomFields::TEXTDOMAIN),$this->instance['title']); ?>" 
				   class="button button-large jcf_add_more_collection"
				   data-collection_id="<?php echo $this->id; ?>"
				   data-fieldset_id="<?php echo $this->fieldsetId; ?>"
				   name="jcf_add_more_collection">
			<div class="clr"></div>
		</div>
		<?php echo $after_widget;
	}

	/**
	 *	save field on post edit form
	 */
	function save( $_values )
	{
		$values = array();

		foreach ( $_values as $_value ) {
			$item = array();

			foreach ( $this->instance['fields'] as $field_id => $field ) {
				$field_obj = $this->_fieldFactory->initObject($this->postType, $field_id, $this->fieldsetId, $this->id);

				if ( isset($_value[$field_id]) ) {
					$item[$field['slug']] = $field_obj->save($_value[$field_id]);
				}
				else {
					$item[$field['slug']] = $field_obj->save(array('val'=>''));
				}
			}
			$values[] = $item;
		}
		return $values;
	}
	
	/**
	 *	update instance (settings) for current field
	 */
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['custom_row'] = true;
		return $instance;
	}
	
	/**
	 *	add script for collection and custom scripts and styles from collection fields
	 */
	public function addJs()
	{
		
		wp_register_script(
			'jcf_collection_post_edit',
			WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection_post_edit.js',
			array('jquery')
		);
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script('jcf_collection_post_edit');

		foreach ( $this->instance['fields'] as $field_id => $field ) {
			$field_obj = $this->_fieldFactory->initObject($this->postType, $field_id, $this->fieldsetId, $this->id);
			if(  method_exists($field_obj, 'addJs')) $field_obj->addJs();
			if(  method_exists($field_obj, 'addCss')) $field_obj->addCss();
		}
	}
	
	/**
	 *	add custom  styles from collection
	 */
	public function addCss()
	{
		wp_register_style('jcf_collection',
				WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection.css',
				array('thickbox'));
		wp_enqueue_style('jcf_collection');
	}
	


	/**
	 * Get nice name for width attribute
	 * 
	 * @param string $width_key
	 * @return string|null
	 */
	public static function getWidthAlias( $width_key )
	{
		if ( isset(self::$fieldWidth[$width_key]) ) {
			return self::$fieldWidth[$width_key];
		}
		return null;
	}

	/**
	 * delete field from collection
	 */
	public function deleteField($field_id)
	{
		// remove from fields array
		$this->_layer->updateFields($this->postType, $field_id, NULL, $this->fieldsetId, $this->id);
				
	}

	
}