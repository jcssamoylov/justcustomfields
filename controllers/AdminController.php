<?php

namespace jcf\controllers;
use jcf\models;
use jcf\core;

class AdminController extends core\Controller {

	/**
	 * Init all wp-actions
	 */
	public function __construct()
	{
		parent::__construct();
		add_action('admin_menu', array($this, 'adminMenu') );

		if ( !( isset($_GET['post']) && isset($_GET['action']) ) ) {
			add_action('admin_print_scripts', array($this, 'addScripts'));
		}

		add_action('admin_print_styles', array($this, 'addStyles'));
		add_action('admin_print_scripts', array($this, 'addCollectionJs'));
		add_action('admin_head' , array($this , 'addMediaUploaderJs'));
	}

	/**
	 * Init menu item and index page for plugin
	 */
	public function adminMenu()
	{
		$page_title = \jcf\JustCustomFields::getPluginTitle();
		$page_slug = \jcf\JustCustomFields::getPluginName();

		add_options_page( $page_title, $page_title, 'manage_options', $page_slug, array($this, 'actionIndex') );
	}

	/**
	 * Render index page
	 */
	public function actionIndex()
	{
		$post_types = jcf_get_post_types( 'object' );
		$model = new models\Fieldset();
		$count_fields = $model->getFieldsCounter();

		// load template
		$this->_render( 'admin/admin_page', array(
			'tab' => 'fields',
			'post_types' => $post_types,
			'count_fields' => $count_fields
		));
	}

	/**
	 *	Include scripts
	 */
	public function addScripts()
	{
		$slug = \jcf\JustCustomFields::getPluginName();
		wp_register_script(
			$slug,
			WP_PLUGIN_URL.'/just-custom-fields/assets/just_custom_fields.js',
			array('jquery', 'json2', 'jquery-form', 'jquery-ui-sortable')
		);
		wp_enqueue_script($slug);
		wp_enqueue_script('jquery-ui-autocomplete');

		// add text domain
		wp_localize_script( $slug, 'jcf_textdomain', jcf_get_language_strings() );
 	}

	/**
	 * Include styles
	 */
	public function addStyles()
	{
		$slug = \jcf\JustCustomFields::getPluginName();
		wp_register_style($slug, WP_PLUGIN_URL.'/just-custom-fields/assets/styles.css');
		wp_enqueue_style($slug);
 	}
	
	/**
	 *	Add collection script
	 */
	public function addCollectionJs()
	{
		wp_register_script(
				'jcf_collections',
				WP_PLUGIN_URL.'/just-custom-fields/components/collection/assets/collection.js',
				array('jquery')
			);
		wp_enqueue_script('jcf_collections');
	}

	/**
	 *	this add js script to the Upload Media wordpress popup
	 */
	public function addMediaUploaderJs()
	{
		global $pagenow;

		if ($pagenow != 'media-upload.php' || empty($_GET ['jcf_media']))
			return;

		// Gets the right label depending on the caller widget
		switch ($_GET ['type']) {
			case 'image': $button_label = __('Select Picture', \jcf\JustCustomFields::TEXTDOMAIN); break;
			case 'file': $button_label = __('Select File', \jcf\JustCustomFields::TEXTDOMAIN); break;
			default: $button_label = __('Insert into Post', \jcf\JustCustomFields::TEXTDOMAIN); break;
		}

		// Overrides the label when displaying the media uploader panels
		?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('#media-items').bind('DOMSubtreeModified' , function(){
						jQuery('td.savesend input[type="submit"]').val("<?php echo $button_label; ?>");
					});
				});
			</script>
		<?php
	}
	
}

