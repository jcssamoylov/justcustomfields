<?php

namespace jcf\models;

class Settings {
	
	const JCF_CONF_MS_NETWORK = 'network';
	const JCF_CONF_MS_SITE = 'site';
	const JCF_CONF_SOURCE_DB = 'database';
	const JCF_CONF_SOURCE_FS_THEME = 'fs_theme';
	const JCF_CONF_SOURCE_FS_GLOBAL = 'fs_global';
	
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
			$notice[] = array('error', __('<strong>Settings storage update FAILED!</strong>. Your MultiSite Settings do not allow to set global storage in FileSystem', JCF_TEXTDOMAIN));
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
}
