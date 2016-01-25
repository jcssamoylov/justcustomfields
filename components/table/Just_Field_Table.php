<?php
namespace jcf\components\table;
use jcf\models;

/**
 * Class for select multiple list type
 *
 * @package default
 * @author Sergey Samoylov
 */
class Just_Field_Table extends models\Just_Field{
	
	public function __construct()
	{
		$field_ops = array( 'classname' => 'field_table' );
		parent::__construct('table', __('Table', \jcf\JustCustomFields::TEXTDOMAIN), $field_ops);
	}
	
	/**
	 *	draw field on post edit form
	 *	you can use $this->instance, $this->entry
	 */
	
	public function field() 
	{
		

		if ( empty($this->entry) ) 
			$this->entry = array('0' => '');

		// add null element for etalon copy
		$entries = (array)$this->entry;

		// get fields
		$columns = $this->parseColumnsOptions();

		if ( empty($columns) ) {
			echo '<p>'.__('Wrong columns configuration. Please check widget settings.', \jcf\JustCustomFields::TEXTDOMAIN).'</p>';
		}

		$count_cols = count($columns);
		$table_head = '<thead>';
		$rows = '';
		
		foreach ( $entries as $key => $entry ) {
			if ( $key == 0 ) {
				$table_head .= '<tr ' . ($key == 0 ? 'class="table-header"' : '') . '><th class="jcf_option_column">Options</th>';
				$first_row = '<tr class="hide"><td>
						<span class="drag-handle" >' . __('move', \jcf\JustCustomFields::TEXTDOMAIN) . '</span>
						<span class="jcf_delete_row" >' . __('delete', \jcf\JustCustomFields::TEXTDOMAIN) . '</span>
					</td>';
			}

			$rows .= '<tr><td>
						<span class="drag-handle" >' . __('move', \jcf\JustCustomFields::TEXTDOMAIN) . '</span>
						<span class="jcf_delete_row" >' . __('delete', \jcf\JustCustomFields::TEXTDOMAIN) . '</span>
					</td>';

			foreach ( $columns as $col_name => $col_title ) {
				if ( $key == 0 ) {
					$table_head .= '<th>' . $col_name . '</th>';
					$first_row .= '<td><input type="text" value=""
									id="' . $this->getFieldIdL2($col_name, '00') . '"
									name="' . $this->getFieldNameL2($col_name, '00') . '"></td>';
				}
				
				$rows .= '<td><input type="text" value="' . ( !empty($entry)? esc_attr($entry[$col_name]) : '' ) . '"
					id="' . $this->getFieldIdL2($col_name, $key) . '"
					name="' . $this->getFieldNameL2($col_name, $key) . '">
				</td>';
			}

			if ( $key == 0 ) {
				$table_head .= '</tr></thead>';
				$first_row .= '</tr>';
			}
			$rows .= '</tr>';
		}

		include(JCF_ROOT . '/components/table/views/field.tpl.php'); 
		return true;
	}
	
	/**
	 *	save field on post edit form
	 */
	public function save( $_values )
	{
		$values = array();
		if (empty($_values) ) return $values;

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
		$instance['columns'] = strip_tags($new_instance['columns']);
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
	
	public function addJs()
	{
		global $wp_version;

		if ( $wp_version <= 3.2 ) {
			// ui core
			wp_register_script(
				'jcf-jquery-ui-core',
				WP_PLUGIN_URL.'/just-custom-fields/assets/jquery-ui.min.js',
				array('jquery')
			);
			wp_enqueue_script('jcf-jquery-ui-core');
			wp_register_script(
				'jcf_table',
				WP_PLUGIN_URL.'/just-custom-fields/components/table/table.js',
				array('jcf-jquery-ui-core')
			);
			wp_enqueue_script('jcf_table');
		}
		else {
			wp_register_script(
				'jcf_table',
				WP_PLUGIN_URL.'/just-custom-fields/components/table/table.js',
				array('jquery')
			);
			wp_enqueue_script('jcf_table');
		}

		// add text domain if not registered with another component
		global $wp_scripts;
		wp_localize_script( 'jcf_table', 'jcf_textdomain', jcf_get_language_strings() );
	}
	
	public function addCss()
	{
		wp_register_style('jcf_table', WP_PLUGIN_URL.'/just-custom-fields/components/table/table.css');
		wp_enqueue_style('jcf_table');
	}

	/**
	 * parse columns from settings
	 * @return array
	 */
	protected function parseColumnsOptions(){
		$columns = array();
		$_columns = explode("\n", $this->instance['columns']);
		foreach ( $_columns as $line ) {
			$line = trim($line);
			if ( strpos($line, '|') !== FALSE ) {
				$col_name = explode('|', $line);
				$columns[ $col_name[0] ] = $col_name[1];
			}
			elseif ( !empty($line) ) {
				$columns[$line] = $line;
			}
		}
		return $columns;
	}

	/**
	 *	print fields values from shortcode
	 */
	public function shortcode_value($args)
	{
		$columns = $this->parseColumnsOptions();
		if ( empty($columns) || empty($this->entry) ) return '';

		$count_cols = count($columns);
		$thead_columns = '';
		$html = $rows = '';
		foreach ( $this->entry as $key => $entry ) {
			$rows .= '<tr class="jcf-table-row jcf-table-row-i' . $key . '">';

			foreach ( $columns as $col_name => $col_title ) {
				if ( $key == 0 ) {
					$thead_columns .= '<th class="jcf-table-cell jcf-table-cell-' . esc_attr($col_name) . '">' . esc_html($col_title) . '</th>';
				}
				$rows .= '<td class="jcf-table-cell jcf-table-cell-' . esc_attr($col_name) . '">' . esc_html($entry[$col_name]) . '</td>';
			}
			$rows .= '</tr>';
		}
		$html .= '<table class="jcf-table">';
		$html .= '<thead><tr>' . $thead_columns .'</tr></thead>';
		$html .= $rows;
		$html .= '</table>';
		return  $args['before_value'] . $html . $args['after_value'];
	}

}