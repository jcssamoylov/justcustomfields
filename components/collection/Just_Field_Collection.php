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

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_collection' );
		parent::__construct('collection', __('Collection', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field() 
	{
		$params = array(
			'post_type' => $this->postType,
			'fieldset_id' => $this->fieldsetId,
			'collection_id' => $this->id,
		);
		$field_model = new models\Field();
		$field_model->load($params);

		self::$currentCollectionFieldKey = 0;

		if ( empty($this->entry) ) $this->entry = array('0' => '');

		$entries = (array)$this->entry;
		?>
		<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
			<div class="form-field">
				<label><?php echo $this->instance['title']; ?>:</label>
				<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
					<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
				</div>

				<?php if ( empty($this->instance['fields']) ) : ?>
					<p class="error">Collection element has no fields registered. Please check component settings</p>
				<?php else: ?>
					<div class="collection_fields">
					<?php foreach ( $entries as $key => $fields ) : ?>
						<div class="collection_field_group">
							<h3>
								<span class="dashicons dashicons-editor-justify"></span>
								<span class="collection_group_title">
								<?php
									$group_title = $this->instance['title'] . ' Item';
									foreach ( $this->instance['fields'] as $field_id => $field) {
										if ( isset($field['group_title']) ) {
											if ( isset($fields[$field['slug']]) ) $group_title = $group_title.' : '.$fields[$field['slug']];
											break;
										}
									}
									echo $group_title;
								 ?>
								</span>
								<a href="#" class="collection_undo_remove_group"><?php _e('UNDO', \jcf\JustCustomFields::TEXTDOMAIN); ?></a>
								<span class="dashicons dashicons-trash"></span>
							</h3>
							<div class="collection_field_group_entry">
								<?php foreach ( $this->instance['fields'] as $field_id => $field ) : ?>
									<div class="collection_field_border jcf_collection_<?php echo (intval($field['field_width']) ? $field['field_width'] : '100'); ?>">
										<?php 
											$field_model->field_id = $field_id;
											$field_obj = models\JustFieldFactory::create($field_model);

											if ( isset($fields[$field['slug']]) ) {
												$field_obj->entry = $fields[$field['slug']];
											}

											$field_obj->isPostEdit = true;
											$field_obj->field();
										?>
									</div>
								<?php endforeach; ?>
								<div class="clr"></div>
							</div>
						</div>
						<?php 
							self::$currentCollectionFieldKey = self::$currentCollectionFieldKey + 1; 
							endforeach; 
						?>
						<div class="clr"></div>
						<input type="button" value="<?php echo sprintf(__('Add %s Item', \jcf\JustCustomFields::TEXTDOMAIN), $this->instance['title']); ?>" 
							   class="button button-large jcf_add_more_collection"
							   data-collection_id="<?php echo $this->id; ?>"
							   data-fieldset_id="<?php echo $this->fieldsetId; ?>"
							   name="jcf_add_more_collection">
						<div class="clr"></div>
					</div>
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
		$instance = wp_parse_args( (array) $this->instance, array( 'title' => '', 'description' => '' ) );
		$description = esc_html($instance['description']);
		$title = esc_attr( $instance['title'] );
		?>
			<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<?php
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
				$params = array(
					'post_type' => $this->postType,
					'field_id' => $field_id,
					'fieldset_id' => $this->fieldsetId,
					'collection_id' => $this->id,
				);
				$field_model = new models\Field();
				$field_model->load($params) && $field_obj = models\JustFieldFactory::create($field_model);

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
			$params = array(
				'post_type' => $this->postType,
				'field_id' => $field_id,
				'fieldset_id' => $this->fieldsetId,
				'collection_id' => $this->id,
			);
			$field_model = new models\Field();
			$field_model->load($params) && $field_obj = models\JustFieldFactory::create($field_model);

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
	 * print fields values from shortcode
	 * 
	 * @param array $args	shortcode args
	 */
	public function shortcodeValue($args)
	{
		foreach ( $this->entry as $key => $entry_values ) {
			foreach ( $entry_values as $field_slug => $field_value ) {
				$field_mixed = str_replace('__', '-', str_replace('_field_', '', $field_slug));
				$params = array(
					'post_type' => $this->postType,
					'field_type' => $field_mixed,
					'fieldset_id' => '',
					'collection_id' => $this->id,
				);
				$field_model = new models\Field();
				$field_model->load($params) && $field_obj = models\JustFieldFactory::create($field_model);

				$field_obj->setPostID( $this->postID, $key );
				$shortcode_value .= $field_obj->doShortcode($args); 
				unset($field_obj);
			}
		}
		return  $args['before_value'] . $shortcode_value . $args['after_value'];
	}
}