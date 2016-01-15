<?php

namespace jcf\models;
use jcf\models;

class Settings extends core\Model {
	
	const JCF_CONF_MS_NETWORK = 'network';
	const JCF_CONF_MS_SITE = 'site';
	const JCF_CONF_SOURCE_DB = 'database';
	const JCF_CONF_SOURCE_FS_THEME = 'fs_theme';
	const JCF_CONF_SOURCE_FS_GLOBAL = 'fs_global';
	
	protected $dataLayer;
	
	public $dataSource;
	public $networkMode;
	
	public function __construct(){
		parent::__construct();
		
		//$this->layer = new DataLayerFactory();
		$this->dataSource = self::getDataSourceType();
		$this->networkMode = self::getNetworkMode();
	}
	
	public static function getDataSourceType() {
		return get_site_option('jcf_read_settings', self::JCF_CONF_SOURCE_DB);
	}

	public static function getNetworkMode() {
		if( MULTISITE && $multisite_setting = get_site_option('jcf_multisite_setting') )
		{
			return $multisite_setting;
		}
		return self::JCF_CONF_MS_SITE;
	}

	public function updateDataSource($new_value, $new_network, $file_layer) {
		$current_value = self::getDataSourceType();

		if( MULTISITE && ($new_network != self::JCF_CONF_MS_NETWORK && $new_value == self::JCF_CONF_SOURCE_FS_GLOBAL) ) {
			$notice[] = array('error', __('<strong>Settings storage update FAILED!</strong>. Your MultiSite Settings do not allow to set global storage in FileSystem', \jcf\JustCustomFields::JCF_TEXTDOMAIN));
			$output = array(
				'source' => $current_value,
				'notice' => $notice
			);
		}
		else {
			if( !empty($current_value) ){
				// if need to copy settings from db to FS
				if( !empty($_POST['jcf_keep_settings']) ){
					if( in_array($new_value, array(self::JCF_CONF_SOURCE_FS_GLOBAL, self::JCF_CONF_SOURCE_FS_THEME)) ){
						$file = $file_layer->getConfigFilePath( $new_value );
						$clone_settings = $file_layer->cloneDBSettings($file);
						$notice[] = $clone_settings['notice'];
						if( !empty($clone_settings['saved']) ){
							$notice[] = array('notice', __('<strong>Database settings has been imported</strong> to file system.', \jcf\JustCustomFields::JCF_TEXTDOMAIN));
							$saved = update_site_option('jcf_read_settings', $new_value);
						}
						else {
							$notice[] = array('error', __('<strong>Database settings import to file system FAILED!</strong>. Revert settings storage to Database.', \jcf\JustCustomFields::JCF_TEXTDOMAIN));
						}
					}
				}
				else{
					$saved = update_site_option('jcf_read_settings', $new_value);
				}
			}
			else{
				$saved = add_site_option('jcf_read_settings', $new_value);
			}

			if( $saved )
				$notice[] = array('notice', __('<strong>Settings storage</strong> configurations has been updated.', \jcf\JustCustomFields::JCF_TEXTDOMAIN));

			$output = array(
				'source' => $saved ? $new_value : $current_value,
				'notice' => $notice
			);
		}
		return $output;
	}

	public function updateNetworkMode( $new_value ) {
		$current_value = self::getNetworkMode();
		$new_value = trim($new_value);

		if( $current_value ){
			$saved = update_site_option( 'jcf_multisite_setting', $new_value );
		}
		else{
			$saved = add_site_option( 'jcf_multisite_setting', $new_value );
		}
		
		if( $saved ){
			$notice = array('notice', __('<strong>MultiSite settings</strong> has been updated.', \jcf\JustCustomFields::JCF_TEXTDOMAIN));
		}
		$output = array('value' => $new_value, 'notice' => $notice);
		return $output;
	}
	/*
	public function check_file(){
		$settings_source = $_POST['jcf_read_settings'];

		if($settings_source == self::JCF_CONF_SOURCE_FS_THEME OR $settings_source == self::JCF_CONF_SOURCE_FS_GLOBAL){
			$file_Layer = $this->layer->create('Files', $settings_source);
			$file = $file_Layer->getConfigFilePath($settings_source);
			

			if($settings_source == self::JCF_CONF_SOURCE_FS_THEME){
				$msg = __("The settings will be written to your theme folder.\nIn case you have settings there, they will be overwritten.\nPlease confirm that you want to continue.", \jcf\JustCustomFields::JCF_TEXTDOMAIN);
			}
			else {
				$msg = __("The settings will be written to folder wp-conten/jcf-settings.\nIn case you have settings there, they will be overwritten.\nPlease confirm that you want to continue.", \jcf\JustCustomFields::JCF_TEXTDOMAIN);
			}

			if( file_exists( $file ) ) {
				$resp = array('status' => '1', 'msg' => $msg);
			}
			else {
				$resp = array('status' => '1', 'file' => '1');
			}
		}
		else {
			$resp = array('status' => '1');
		}
		jcf_ajax_response($resp, 'json');
	}
	 * 
	 */
}
