<?php
namespace jcf\components\datepicker;
use jcf\models;

class Just_Field_DatePicker extends models\Just_Field {
	
	public static $compatibility = '3.3+';

	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_datepicker' );
		parent::__construct('datepicker', __('Date Picker', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
	}

	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	public function field() 
	{
		?>
			<div id="jcf_field-<?php echo $this->id; ?>" class="jcf_edit_field <?php echo $this->fieldOptions['classname']; ?>">
				<div class="form-field">
					<label><?php echo $this->instance['title']; ?>:</label>
					<div class="jcf-get-shortcode" rel="<?php echo $this->slug; ?>">
						<span class="dashicons dashicons-editor-help wp-ui-text-highlight"></span>
					</div>
					<div>
					<input id="<?php echo $this->getFieldId('val'); ?>" name="<?php echo $this->getFieldName('val'); ?>" type="text" value="<?php echo esc_attr($this->entry); ?>" size="20" style="width:25%;" />
					</div>

					<script type="text/javascript"><!--
						jQuery(document).ready(function(){
							jQuery("#<?php echo $this->getFieldId('val'); ?>").datepicker({
								dateFormat: "<?php echo !empty($this->instance['date_format']) ? $this->instance['date_format'] : 'yy-mm-dd'; ?>"
								<?php if(!empty($this->instance['show_monthes'])) echo ', changeMonth: true, changeYear: true'; ?>
							});
						});
					--></script>
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
		$instance = wp_parse_args( (array) $this->instance, array( 'title' => '' ) );

		$title = esc_attr( $instance['title'] );
		$show_monthes = !empty($instance['show_monthes'])? ' checked="checked" ' : '';
		$date_format =  !empty($instance['date_format']) ? $instance['date_format'] : 'yy-mm-dd' ;
		?>
		<p><label for="<?php echo $this->getFieldId('title'); ?>"><?php _e('Title:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label> <input class="widefat" id="<?php echo $this->getFieldId('title'); ?>" name="<?php echo $this->getFieldName('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->getFieldId('show_monthes'); ?>"><input class="checkbox" id="<?php echo $this->getFieldId('show_monthes'); ?>" name="<?php echo $this->getFieldName('show_monthes'); ?>" type="checkbox" value="1" <?php echo $show_monthes; ?> /> <?php _e('Show month/year select boxes', \jcf\JustCustomFields::TEXTDOMAIN); ?></label></p>
		<p><label for="<?php echo $this->getFieldId('date_format'); ?>"><?php _e('Date format:', \jcf\JustCustomFields::TEXTDOMAIN); ?></label>
			<input class="widefat" id="<?php echo $this->getFieldId('date_format'); ?>"
				   name="<?php echo $this->getFieldName('date_format'); ?>" type="text"
				   value="<?php echo $date_format; ?>" /><br />
			<small><?php _e('Example:', \jcf\JustCustomFields::TEXTDOMAIN);?> yy-mm-dd <a href="http://api.jqueryui.com/datepicker/#option-dateFormat" target="_blank"><?php _e('look more about date formats', \jcf\JustCustomFields::TEXTDOMAIN);?></a></small>
		</p>
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
		$instance['show_monthes'] = (int)@$new_instance['show_monthes'];
		$instance['date_format'] = @$new_instance['date_format'];
		return $instance;
	}

	public function addJs()
	{
		/**
		 * WP version 3.0 and above have datepicker ui-core;
		 */
		wp_enqueue_script('jquery-ui-datepicker');
	}

	public function addCss()
	{
		wp_register_style('jcf_ui_datepicker', WP_PLUGIN_URL.'/just-custom-fields/components/datepicker/ui-theme-smoothness/jquery-ui-1.8.13.custom.css');
		wp_enqueue_style('jcf_ui_datepicker');
	}

}
?>