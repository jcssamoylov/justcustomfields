<?php

namespace JCF\models;
use JCF\interfaces\FieldSettings;

class FilesDataLayer implements FieldSettings {

	public function get_fields($source_settings = false, $id = '') {
		$option_name = $this->getFieldName();

		$jcf_settings = $this->findAll($source_settings);
		$post_type =  str_replace('jcf_fields-', '', $option_name);
		$field_settings = $jcf_settings['field_settings'][$post_type];

		if(!empty($id)){
			return @$field_settings[$id];
		}

		return $field_settings;
	}
	
	public function update_fields(){}

	public function get_fieldsets($source_settings = false , $id = false){

		$jcf_settings = $this->getAllData($source_settings);
		$post_type = jcf_get_post_type();
		if(isset($jcf_settings['fieldsets'][$post_type])){
			$fieldsets = $jcf_settings['fieldsets'][$post_type];
		} else $fieldsets = array();

		if(!empty($id)){
			return @$fieldsets[$id];
		}

		return $fieldsets;
	}

	public function update_fieldsets(){}
	
	// option name in wp-options table
	public function getFieldName(){
		$post_type = jcf_get_post_type();
		return 'jcf_fields-'.$post_type;
	}
	
	public function getConfigFilePath( $source_settings ){

		if( !empty($source_settings) && ($source_settings == JCF_CONF_SOURCE_FS_THEME || $source_settings == JCF_CONF_SOURCE_FS_GLOBAL) ){
			return ($source_settings == JCF_CONF_SOURCE_FS_THEME)? get_stylesheet_directory() . '/jcf-settings/jcf_settings.json' : get_home_path() . 'wp-content/jcf-settings/jcf_settings.json';
		}
		return false;
	}
	
	public function cloneDBSettings($file){
		$db_factory = new DataLayerFactory();
		$db_source = $db_factory->create('\JCF\models\DBDataLayer');
		$all_fields = $db_source->getAllData();

		$dir = dirname($file);
		if( 
			// check that dir exists (if not try to create) and is writable
			 ( (!is_dir($dir) && !wp_mkdir_p($dir)) || !is_writable($dir) )
			// try to write settings
			|| !$saved = $this->saveAll($all_fields, $file)
		)
		{
			// if fail - print error
			$notice = array('error', sprintf( __('<strong>Settings storage update FAILED!</strong>. Please check that directory exists and writable: %s', JCF_TEXTDOMAIN), dirname($dir) ));
		}
		else{
			// we have another notification after this func called
			//$msg = array('notice', __('<strong>Fields settings</strong> successfully copied!', JCF_TEXTDOMAIN));
		}
		unset($db_source);
		$output = array(
			'saved' => $saved,
			'notice' => $notice
		);
		return $output;
	}
	
	public function saveAll($data, $file){
		$data = jcf_format_json(json_encode($data));
		$dir = dirname($file);

		// trying to create dir
		if( (!is_dir($dir) && ! wp_mkdir_p($dir)) || !is_writable($dir) ){
			return false;
		}

		if( !empty($dir) ){
			$content = $data . "\r\n";
			if( $fp = fopen($file, 'w') ){
				fwrite($fp, $content);
				fclose($fp);
				jcf_set_chmod($file);
				return true;
			}
		}

		return false;
	}
	
	/**
	 * return number of registered fields and fieldsets for specific post type
	 * @param string $post_type
	 * @return int
	 */
	public function countFields($post_type, $source_settings){
		$jcf_settings = $this->getAllData($source_settings);
		if(isset($jcf_settings['fieldsets'][$post_type])){
			$fieldsets = $jcf_settings['fieldsets'][$post_type];
		} else {
			$fieldsets = array();
		}
		
		if(!empty($fieldsets)){
			$count['fieldsets'] = count($fieldsets);
			$count['fields'] = 0;
			foreach($fieldsets as $fieldset){
				if(!empty($fieldset['fields'])){
					$count['fields'] += count($fieldset['fields']);
				}
			}
		}
		else{
			$count = array('fieldsets' => 0, 'fields' => 0);
		}
		return $count;
	}
	
	public function getAllData($source_settings){
		$filename = $this->getConfigFilePath($source_settings);
		if (file_exists($filename)) {
			return $this->getDataFromFile($filename);
		}
		else{
			return false;
		}
	}
	
	/**
	*	Get settings from file
	*	@param string $uploadfile File name
	*	@return array Array with fields settings from file
	*/
	function getDataFromFile($uploadfile = false){
		if(!$uploadfile)
			$uploadfile = $this->getConfigFilePath($source_settings);

		if (file_exists($uploadfile)) {
			$content = file_get_contents($uploadfile);
			$data = json_decode($content, true);
			return $data;
		}
		else{
			return false;
		}
	}
}
