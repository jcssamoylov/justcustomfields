<?php

namespace jcf\controllers;

use jcf\models;
use jcf\core;

class PostTypeController extends core\Controller
{

	/**
	 * Init all wp-actions
	 */
	public function __construct()
	{
		parent::__construct();

		if ( isset($_GET['post']) && isset($_GET['action']) && $_GET['action'] == 'edit' ) {
			add_action('admin_print_scripts', array( $this, 'addScripts' ));
		}

		add_action('admin_print_styles', array( $this, 'addStyles' ));
		add_action('add_meta_boxes', array( $this, 'actionRender' ), 10, 1);
		add_action('save_post', array( $this, 'actionSave' ), 10, 2);

		add_shortcode('jcf-value', array( $this, 'actionGetShortcodeValue' ));
	}

	/**
	 * Get fields by post type
	 * @param string $post_type
	 */
	public function actionRender( $post_type = '' )
	{
		$visibility_rules = array();
		$model = new models\Fieldset();
		$fieldsets = $model->findByPostType($post_type);
		$field_model = new models\Field();
		$visibility_model = new models\FieldsetVisibility();
		$visibility_rules = $visibility_model->findByPostType($post_type);

		if ( !empty($fieldsets) ) {
			// remove fieldsets without fields
			foreach ( $fieldsets as $f_id => $fieldset ) {
				// if all fields disabled -> remove fieldset
				if ( empty($fieldset['fields']) )
					continue;

				foreach ($fieldset['fields'] as $field_id => $enabled) {
					if ( !$enabled )
						continue;

					$params = array(
						'post_type' => $post_type,
						'field_id' => $field_id,
						'fieldset_id' => $fieldset['id']
					);
					$field_model->load($params) && $field_obj = core\JustFieldFactory::create($field_model);
					$field_obj->doAddJs();
					$field_obj->doAddCss();
				}
				add_meta_box('jcf_fieldset-' . $f_id, $fieldset['title'], array( $this, 'renderCustomField' ), $post_type, 'advanced', 'default', array( $fieldset ));
			}
			?>
			<script>
				var fieldsets_visibility_rules = <?php echo json_encode($visibility_rules); ?>;
			</script>
			<?php
		}

		return false;
	}

	/**
	 * 	prepare and print fieldset html.
	 * 	- load each field class
	 * 	- print form from each class
	 */
	public function renderCustomField( $post = NULL, $box = NULL )
	{
		$model = new models\Field();
		$fieldset = $box['args'][0];
		$this->_render('shortcodes/modal');

		foreach ( $fieldset['fields'] as $field_id => $enabled ) {
			if ( !$enabled )
				continue;

			$params = array(
				'post_type' => $post->post_type,
				'field_id' => $field_id,
				'fieldset_id' => $fieldset['id'],
			);
			$model->load($params) && $field_obj = core\JustFieldFactory::create($model);
			$field_obj->setPostID($post->ID);
			$field_obj->field();
		}
		unset($field_obj);

		// Use nonce for verification
		global $jcf_noncename;

		if ( empty($jcf_noncename) ) {
			wp_nonce_field(plugin_basename(__FILE__), 'justcustomfields_noncename');
			$jcf_noncename = true;
		}
	}
	
	/**
	 * Save values of custom fields for post
	 * @param int $post_ID
	 * @param array $post
	 */
	public function actionSave( $post_ID = 0, $post = null )
	{
		$fieldsets_model = new models\Fieldset();
		$field_model = new models\Field();
		$field_model->load($_POST);
		// do not save anything on autosave
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return;

		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( empty($_POST['justcustomfields_noncename']) || !wp_verify_nonce($_POST['justcustomfields_noncename'], plugin_basename(__FILE__)) )
			return;

		// check permissions
		$permission = ('page' == $field_model->post_type) ? 'edit_page' : 'edit_post';
		if ( !current_user_can($permission, $post_ID) )
			return;

		// OK, we're authenticated: we need to find and save the data
		// get fieldsets

		$fieldsets = $fieldsets_model->findByPostType($field_model->post_type);

		// create field class objects and call save function
		foreach ( $fieldsets as $f_id => $fieldset ) {
			$field_model->fieldset_id = $fieldset['id'];

			foreach ( $fieldset['fields'] as $field_id => $tmp ) {
				$field_model->field_id = $field_id;
				$field_obj = core\JustFieldFactory::create($field_model);
				$field_obj->setPostID($post->ID);
				$field_obj->doSave();
			}
		}

		return false;
	}

	/**
	 * Set value of shortcode
	 */
	public function actionGetShortcodeValue( $args )
	{
		$model = new models\Shortcodes();
		return $model->getFieldValue($args);
	}

	/**
	 * 	add custom scripts to post edit page
	 */
	public function addScripts()
	{
		wp_register_script(
				'jcf_edit_post', WP_PLUGIN_URL . '/just-custom-fields/assets/edit_post.js', array( 'jquery' )
		);
		wp_enqueue_script('jcf_edit_post');

		do_action('jcf_admin_edit_post_scripts');
	}

	/**
	 * 	add custom styles to post edit page
	 */
	public function addStyles()
	{
		wp_register_style('jcf_edit_post', WP_PLUGIN_URL . '/just-custom-fields/assets/edit_post.css');
		wp_enqueue_style('jcf_edit_post');

		do_action('jcf_admin_edit_post_styles');
	}
}
