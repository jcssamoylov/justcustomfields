<?php
namespace JCF\components\collection;
use JCF\models\Just_Field;

/**
 * Class for Collection type
 *
 * @package default
 * @author Kirill samojlenko
 */
class Just_Field_Collection extends Just_Field{
	
	public static $compatibility = "4.0+";
	
	public static $current_collection_field_key = 0;

	public static $field_width = array(
		'100' => '100%',
		'75' => '75%',
		'50' => '50%',
		'33' => '33%',
		'25' => '25%',
	);
	public $_collection_field_factory;

	public function __construct($data_layer, $post_type = NULL){
		$this->_collection_field_factory = new \JCF\models\JustFieldFactory($this->_dataLayer, TRUE);
		$this->_collection_field_factory->register( 'Just_Field_Input' );
		$this->_collection_field_factory->register( 'Just_Field_Select' );
		$this->_collection_field_factory->register( 'Just_Field_SelectMultiple' );
		$this->_collection_field_factory->register( 'Just_Field_Checkbox' );
		$this->_collection_field_factory->register( 'Just_Field_Textarea' );
		$this->_collection_field_factory->register( 'Just_Field_DatePicker' );
		$this->_collection_field_factory->register( 'Just_Field_Simple_Media' );
		$this->_collection_field_factory->register( 'Just_Field_Table' );

		$field_ops = array( 'classname' => 'field_collection' );
		parent::__construct($data_layer, $post_type, 'collection', __('Collection', JCF_TEXTDOMAIN), $field_ops);
		
		if( !empty($_GET['page']) && $_GET['page'] == 'just_custom_fields' ){
			//add_action('admin_print_styles', 'jcf_admin_add_styles');
			add_action('admin_print_scripts', array($this, 'add_collection_js') );
		}
		add_action('wp_ajax_jcf_collection_order', array($this, 'ajax_collection_fields_order' ));
		add_action('wp_ajax_jcf_collection_add_new_field_group', array($this, 'ajax_return_collection_field_group' ));
		
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field( $args ) {
		extract( $args );
		
		self::$current_collection_field_key = 0;
		if(empty($this->entry)) $this->entry = array('0' => '');
		$entries = (array)$this->entry;
		echo $before_widget;
		echo $before_title . $this->instance['title'] . $after_title;
		
		if( empty($this->instance['fields']) ) {
			echo '<p class="error">Collection element has no fields registered. Please check component settings</p>';
			echo $after_widget;
			return;
		}
?>
		<div class="collection_fields">
<?php
			foreach($entries as $key => $fields){
?>
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
						<a href="#" class="collection_undo_remove_group"><?php _e('UNDO',JCF_TEXTDOMAIN); ?></a>
						<span class="dashicons dashicons-trash"></span>
						
					</h3>
					<div class="collection_field_group_entry">
<?php					
						foreach($this->instance['fields'] as $field_id => $field){
							echo '<div class="collection_field_border jcf_collection_'.(intval($field['field_width'])?$field['field_width']:'100').'">';
							$field_obj = $this->_collection_field_factory->initObject($this->post_type, $field_id, $this->fieldset_id, $this->id);
							$field_obj->set_slug($field['slug']);
							if(isset($fields[$field['slug']])){
								$field_obj->entry = $fields[$field['slug']];
							}
							$field_obj->instance = $field;
							$field_obj->is_post_edit = true;
							$field_obj->field($field_obj->field_options);
							echo '</div>';
						}
?>

						<div class="clr"></div>
					</div>
				</div>
<?php
				self::$current_collection_field_key = self::$current_collection_field_key + 1;
			}
?>
			<div class="clr"></div>
			<input type="button" value="<?php echo sprintf(__('Add %s Item', JCF_TEXTDOMAIN),$this->instance['title']); ?>" 
				   class="button button-large jcf_add_more_collection"
				   data-collection_id="<?php echo $this->id; ?>"
				   data-fieldset_id="<?php echo $this->fieldset_id; ?>"
				   name="jcf_add_more_collection">
			<div class="clr"></div>
		</div>
<?php
		echo $after_widget;
	}
	
	/**
	 * return empty collection fields group
	 */
	public function ajax_return_collection_field_group(){
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = $_POST['collection_id'];
		$collection = $this->_collection_field_factory->initObject($this->post_type, $collection_id, $fieldset_id);
		self::$current_collection_field_key = $_POST['group_id'];
	?>
			<div class="collection_field_group">
				<h3>
					<span class="dashicons dashicons-editor-justify"></span>
					<span class="collection_group_title">
					<?php echo $collection->instance['title'].' Item'; ?>
					</span>
					<span class="dashicons dashicons-trash"></span>

				</h3>
				<div class="collection_field_group_entry">
<?php					
					foreach($collection->instance['fields'] as $field_id => $field){
						echo '<div class="collection_field_border jcf_collection_'.(intval($field['field_width'])?$field['field_width']:'100').'">';
						$field_obj = $this->_collection_field_factory->initObject($this->post_type, $field_id, $collection->fieldset_id, $collection->id);
						$field_obj->set_slug($field['slug']);
						$field_obj->instance = $field;
						$field_obj->is_post_edit = true;
						$field_obj->field($field_obj->field_options);
						echo '</div>';
					}
?>
					<div class="clr"></div>
				</div>
			</div>
<?php
		die();
	}
	/**
	 *	save field on post edit form
	 */
	function save( $_values ){
		$values = array();
		foreach($_values as $_value){
			$item = array();
			foreach($this->instance['fields'] as $field_id => $field){
				$field_obj = $this->_collection_field_factory->initObject($this->post_type, $field_id, $this->fieldset_id, $this->id);
				if(isset($_value[$field_id])){
					$item[$field['slug']] = $field_obj->save($_value[$field_id]);
				} else {
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
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['custom_row'] = true;
		return $instance;
	}
	
	/**
	 *	print settings form for field
	 */	
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'description' => '' ) );
		$description = esc_html($instance['description']);
		$title = esc_attr( $instance['title'] );
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', JCF_TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<?php
	}
	
	/**
	 *	add script for collection and custom scripts and styles from collection fields
	 */
	public function add_js(){
		
		wp_register_script(
			'jcf_collection_post_edit',
			WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection_post_edit.js',
			array('jquery')
		);
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script('jcf_collection_post_edit');
		foreach($this->instance['fields'] as $field_id => $field){
			$field_obj = $this->_collection_field_factory->initObject($this->post_type, $field_id, $this->fieldset_id, $this->id);
			if(  method_exists($field_obj, 'add_js')) $field_obj->add_js();
			if(  method_exists($field_obj, 'add_css')) $field_obj->add_css();
		}
	}
	
	/**
	 *	add custom  styles from collection
	 */
	
	public function add_css(){
		wp_register_style('jcf_collection',
				WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection.css',
				array('thickbox'));
		wp_enqueue_style('jcf_collection');
	}
	
	/**
	 *	add custom scripts for jcf fildset edit page
	 */
	public function add_collection_js(){
		wp_register_script(
				'jcf_collections',
				WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection.js',
				array('jquery')
			);
		wp_enqueue_script('jcf_collections');

	}
	
	/**
	 * Get nice name for width attribute
	 * 
	 * @param string $width_key
	 * @return string|null
	 */
	public static function get_width_alias( $width_key ) {
		if ( isset(self::$field_width[$width_key]) ){
			return self::$field_width[$width_key];
		}
		
		return null;
	}
	
	/**
	 * create custom table on jcf settings fields
	 */
	
	public function settings_row($post_type, $collection_id, $fieldset_id)
	{
		$collection = $this->_dataLayer->get_fields($post_type, $collection_id);
		$registered_fields = $this->_collection_field_factory->get_registered_fields();
		include( JCF_ROOT . '/components/collection/templates/fields_ui.tpl.php' );
	}
	
	/**
	 * delete field from collection
	 */
	/**
	 * delete field from collection
	 */
	public function delete_field($field_id)
	{
		// remove from fields array
		$this->_dataLayer->update_fields($this->post_type, $field_id, NULL, $this->fieldset_id, $this->id);
				
	}
	
	public function ajax_collection_fields_order(){
		$field_factory = new \JCF\models\JustFieldFactory($this->_dataLayer);
		$fieldset_id = $_POST['fieldset_id'];
		$collection_id = $_POST['collection_id'];
		$post_type = $_POST['post_type'];
		$collection = $field_factory->initObject($post_type, $collection_id, $fieldset_id);
		$order  = trim($_POST['fields_order'], ',');

		$new_fields = explode(',', $order);
		$new_order = array();		

		if(! empty($new_fields)){
			foreach($new_fields as $field_id){
				if(isset($collection->instance['fields'][$field_id])){
					$new_order[$field_id] = $collection->instance['fields'][$field_id];					
				}
			}
		}
		$collection->instance['fields'] = $new_order;
		$this->_dataLayer->update_fields($post_type, $collection_id, $collection->instance, $fieldset_id);
		
		$resp = array('status' => '1');
		jcf_ajax_response($resp, 'json');
	}
	
}