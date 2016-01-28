<?php

namespace jcf\models;
use jcf\core;

class PostType extends core\Model {

	protected $_layer;
	
	public function __construct()
	{
		parent::__construct();
		$layer_factory = new DataLayerFactory();
		$this->_layer = $layer_factory->create();
	}

	/**
	 *	callback function for hook "add_meta_boxes"
	 *	call add_meta_box for each fieldset
	 */
	public function renderCustomFields( $post_type = '' )
	{
		$field_model = new Field();
		$fieldsets = $this->_layer->getFieldsets($post_type);
		$field_settings = $this->_layer->getFields($post_type);

		if ( !empty($fieldsets) ) {
			// remove fieldsets without fields
			foreach ( $fieldsets as $f_id => $fieldset ) {
				// check $enabled; add custom js/css for components
				foreach ( $fieldset['fields'] as $field_id => $enabled ) {
					if ( !$enabled ) {
						unset($fieldset['fields'][$field_id]);
						continue;
					}

					$params = array(
						'post_type' => $post_type,
						'field_id' => $field_id,
						'fieldset_id' => $f_id
					);
					$field_model->load($params) && $field_obj = JustFieldFactory::create($field_model);
					$field_obj->doAddJs();
					$field_obj->doAddCss();
				}
				// if all fields disabled -> remove fieldset
				if ( empty($fieldset['fields']) ) {
					unset($fieldsets[$f_id]);
				}
			}
			if (!empty($field_obj) ) unset($field_obj);

			if ( empty($fieldsets) ) return false;

			$visibility_rules = array();

			foreach ( $fieldsets as $f_id => $fieldset ) {
				if ( !empty($fieldset['visibility_rules']) ) {
					$visibility_rules[$f_id] = $fieldset['visibility_rules'];

					foreach ( $visibility_rules[$f_id] as $key => $rule ) {
						if( $rule['based_on'] == 'taxonomy' ) {
							$taxonomy_terms = array();
							foreach ( $rule['rule_taxonomy_terms'] as $term_id ) {
								$taxonomy_terms[] = get_term_by('id', $term_id, $rule['rule_taxonomy']);
							}
							$visibility_rules[$f_id][$key]['rule_taxonomy_terms'] = $taxonomy_terms;
						}
					}
				}
				add_meta_box('jcf_fieldset-'.$f_id, $fieldset['title'], array($this, 'getCustomFields'), $post_type, 'advanced', 'default', array($fieldset) );
			}
			?>
			<script>
				var fieldsets_visibility_rules = <?php echo json_encode($visibility_rules); ?>;
			</script>
			<?php
		}
	}

	/**
	 *	prepare and print fieldset html.
	 *	- load each field class
	 *	- print form from each class
	 */
	public function getCustomFields( $post = NULL, $box = NULL )
	{
		$field_model = new Field();
		$fieldset = $box['args'][0];
		include(JCF_ROOT . '/views/shortcodes/modal.tpl.php');

		foreach ( $fieldset['fields'] as $field_id => $enabled ) {
			if( !$enabled ) continue;

			$params = array(
				'post_type' => $post->post_type,
				'field_id' => $field_id,
				'fieldset_id' => $fieldset['id']
			);
			$field_model->load($params) && $field_obj = JustFieldFactory::create($field_model);
			$field_obj->setPostID( $post->ID );
			$field_obj->field();
		}
		unset($field_obj);
		
		// Use nonce for verification
		global $jcf_noncename;

		if ( empty($jcf_noncename) ) {
			wp_nonce_field( plugin_basename( __FILE__ ), 'justcustomfields_noncename' );
			$jcf_noncename = true;
		}
	}
	
	/**
	 *	callback function for "save_post" action
	 */
	public function saveCustomFields( $post_ID = 0, $post = null )
	{
		$field_model = new Field();
		$field_model->load($_POST);
		// do not save anything on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( empty($_POST['justcustomfields_noncename']) || !wp_verify_nonce( $_POST['justcustomfields_noncename'], plugin_basename( __FILE__ ) ) )
			return;
		
		// check permissions
		$permission = ('page' == $field_model->post_type)? 'edit_page' : 'edit_post';
		if ( !current_user_can( $permission, $post_ID ) ) return;
		
		// OK, we're authenticated: we need to find and save the data

		// get fieldsets
		$fieldsets = $this->_layer->getFieldsets($field_model->post_type);

		// create field class objects and call save function
		foreach ( $fieldsets as $f_id => $fieldset ) {
			$field_model->fieldset_id = $fieldset['id'];

			foreach( $fieldset['fields'] as $field_id => $tmp ) {
				$field_model->field_id = $field_id;
				$field_obj = JustFieldFactory::create($field_model);
				$field_obj->setPostID( $post->ID );
				$field_obj->doSave();
			}
		}

		return false;
	}
}

