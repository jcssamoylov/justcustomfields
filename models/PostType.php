<?php

namespace JCF\models;
use JCF\models\JustFieldFactory;

class PostType {
	public $_dataLayer;
	public $_fieldFactory;
	
	public function __construct($data_layer)
	{
		$this->_dataLayer = $data_layer;
		$this->_fieldFactory = new JustFieldFactory($this->_dataLayer);
	}

	/**
	 *	callback function for hook "add_meta_boxes"
	 *	call add_meta_box for each fieldset
	 */
	public function render_custom_fields( $post_type = '' ){
	
		$fieldsets = $this->_dataLayer->get_fieldsets($post_type);
		$field_settings = $this->_dataLayer->get_fields($post_type);
		if(!empty($fieldsets)){
			// remove fieldsets without fields
			foreach($fieldsets as $f_id => $fieldset){
				// check $enabled; add custom js/css for components
				foreach($fieldset['fields'] as $field_id => $enabled){
					if( !$enabled ){
						unset($fieldset['fields'][$field_id]);
						continue;
					}
					$field_obj = $this->_fieldFactory->initObject($post_type, $field_id, $fieldset['id']);
					$field_obj->do_add_js();
					$field_obj->do_add_css();
				}
				// if all fields disabled -> remove fieldset
				if( empty($fieldset['fields']) ){
					unset($fieldsets[$f_id]);
				}
			}
			if(!empty($field_obj)) unset($field_obj);

			if( empty($fieldsets) ) return false;

			$visibility_rules = array();
			foreach($fieldsets as $f_id => $fieldset){
				if(!empty($fieldset['visibility_rules'])){
					$visibility_rules[$f_id] = $fieldset['visibility_rules'];

					foreach($visibility_rules[$f_id] as $key => $rule) {
						if($rule['based_on'] == 'taxonomy'){
							$taxonomy_terms = array();
							foreach($rule['rule_taxonomy_terms'] as $term_id){
								$taxonomy_terms[] = get_term_by('id', $term_id, $rule['rule_taxonomy']);
							}
							$visibility_rules[$f_id][$key]['rule_taxonomy_terms'] = $taxonomy_terms;
						}
					}
				}

				add_meta_box('jcf_fieldset-'.$f_id, $fieldset['title'], array($this, 'get_custom_fields'), $post_type, 'advanced', 'default', array($fieldset) );
			}
			?>
			<script>
				var fieldsets_visibility_rules = <?php echo json_encode($visibility_rules);?>;
			</script>
			<?php
		}
	}

	/**
	 *	prepare and print fieldset html.
	 *	- load each field class
	 *	- print form from each class
	 */
	public function get_custom_fields( $post = NULL, $box = NULL ){
		$fieldset = $box['args'][0];

		$this->print_shortcodes_modal();

		foreach($fieldset['fields'] as $field_id => $enabled){
			if( !$enabled ) continue;
			$field_obj = $this->_fieldFactory->initObject($post->post_type, $field_id, $fieldset['id']);
			$field_obj->set_post_ID( $post->ID );

			echo '<div id="jcf_field-' . $field_id . '" class="jcf_edit_field ' . $field_obj->field_options['classname'] . '">'."\r\n";
			$args = $field_obj->field_options;
			$args['after_title'] .= '<div class="jcf-get-shortcode" rel="' . $field_obj->slug . '">'
					. '<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>'
					. '</div>'."\r\n";
			$field_obj->field( $args );

			echo "\r\n </div> \r\n";
		}
		unset($field_obj);
		
		// Use nonce for verification
		global $jcf_noncename;
		if( empty($jcf_noncename) ){
			wp_nonce_field( plugin_basename( __FILE__ ), 'justcustomfields_noncename' );
			$jcf_noncename = true;
		}
	}
	
	/**
	 *	callback function for "save_post" action
	 */
	public function save_custom_fields( $post_ID = 0, $post = null ){

		// do not save anything on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( empty($_POST['justcustomfields_noncename']) || !wp_verify_nonce( $_POST['justcustomfields_noncename'], plugin_basename( __FILE__ ) ) )
			return;
		
		// check permissions
		$permission = ('page' == $_POST['post_type'])? 'edit_page' : 'edit_post';
		if ( !current_user_can( $permission, $post_ID ) ) return;
		
		// OK, we're authenticated: we need to find and save the data
		
		// set global post type
		jcf_set_post_type( $_POST['post_type'] );

		// get fieldsets
		$fieldsets = $this->_dataLayer->get_fieldsets($_POST['post_type']);

		// create field class objects and call save function
		foreach($fieldsets as $f_id => $fieldset){
			foreach($fieldset['fields'] as $field_id => $tmp){
				$field_obj = $this->_fieldFactory->initObject($_POST['post_type'], $field_id, $fieldset['id']);
				$field_obj->set_post_ID( $post->ID );

				$field_obj->do_save();
			}
		}

		return false;
	}

	/**
	 * get modal window for getting shortcodes
	 */
	function print_shortcodes_modal(){
		?>
		<div class="jcf_shortcodes_tooltip" >
			<div class="jcf_inner_box">
				<h3 class="header"><?php _e('Usage guidelines for field ', JCF_TEXTDOMAIN); ?> 
					<span class="field-name"></span> 
					<a href="#" class="jcf_shortcodes_tooltip-close"><span class="media-modal-icon"></span></a>
				</h3>
				<div class="jcf_inner_content">
					<fieldset class="shortcode_usage">
						<legend><?php _e('Inside the Editor', JCF_TEXTDOMAIN); ?></legend>
						<span class="fieldset-description"><?php _e('To insert the value into your post editor, please copy and paste the code examples below to your editor.', JCF_TEXTDOMAIN); ?></span>
						
						<span class="jcf-relative"><input type="text" readonly="readonly" class="jcf-shortcode jcf-shortcode-value" value="" /><a href="#" class="copy-to-clipboard" title="Copy to clipboard"></a></span><br />
						<small><?php _e('optional parameters: class="myclass" id="myid" post_id="123" label="yes"', JCF_TEXTDOMAIN); ?></small><br /><br />
					</fieldset>
					<fieldset class="template_usage">
						<legend><?php _e('Inside your Templates ', JCF_TEXTDOMAIN); ?></legend>
						<span class="fieldset-description"><?php _e('To print the value or label inside your template (for example in single.php) please use the examples below:', JCF_TEXTDOMAIN); ?></span>
						
						<span class="jcf-relative"><input type="text" readonly="readonly" class="jcf-shortcode jcf-template-value" value=""/><a href="#" class="copy-to-clipboard" title="Copy to clipboard"></a></span><br />
						<small><?php _e('optional parameters: class="myclass" id="myid" post_id="123" label="yes"', JCF_TEXTDOMAIN); ?></small><br /><br />
					</fieldset>
				</div>
			</div>
		</div>
		<?php
	}
}

