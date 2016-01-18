<?php

namespace jcf\models;
use jcf\core;

class Settings extends core\Model {
	
	const JCF_CONF_MS_NETWORK = 'network';
	const JCF_CONF_MS_SITE = 'site';
	const JCF_CONF_SOURCE_DB = 'database';
	const JCF_CONF_SOURCE_FS_THEME = 'fs_theme';
	const JCF_CONF_SOURCE_FS_GLOBAL = 'fs_global';

	protected $_layer;
	
	public function __construct(){
		parent::__construct();
		$layer_factory = new DataLayerFactory();
		$this->_layer = $layer_factory->create();
	}
	
	public static function getDataSourceType() {
		return get_site_option('jcf_read_settings', self::JCF_CONF_SOURCE_DB);
	}

	public static function getNetworkMode() {
		if( MULTISITE && $network = get_site_option('jcf_multisite_setting') )
		{
			return $network;
		}
		return self::JCF_CONF_MS_SITE;
	}

	public function save()
	{
		if ( !empty($this->_request) ) {
			$source = $this->_request['jcf_read_settings'];
			$network = $this->_request['jcf_multisite_setting'];

			if ( MULTISITE ) {
				$this->_updateNetworkMode($network);
			}
			$this->_updateDataSource($source, $network);
		}
		return false;
	}

	protected function _updateDataSource($new_value, $new_network) {
		$current_value = self::getDataSourceType();
		$saved = FALSE;

		if ( MULTISITE && ($new_network != self::JCF_CONF_MS_NETWORK && $new_value == self::JCF_CONF_SOURCE_FS_GLOBAL) ) {
			$error = __('<strong>Settings storage update FAILED!</strong>. Your MultiSite Settings do not allow to set global storage in FileSystem', \jcf\JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
		}
		else {
			$saved = update_site_option('jcf_read_settings', $new_value);

			if ( $saved ) {
				$message = __('<strong>Settings storage</strong> configurations has been updated.', \jcf\JustCustomFields::TEXTDOMAIN);
				$this->addMessage($message);
			}
		}
		return $saved;
	}

	protected function _updateNetworkMode($new_value) {
		$current_value = self::getNetworkMode();
		$new_value = trim($new_value);

		if ( $current_value ) {
			$saved = update_site_option( 'jcf_multisite_setting', $new_value );

			if ( $saved ) {
				$message = __('<strong>MultiSite settings</strong> has been updated.', \jcf\JustCustomFields::TEXTDOMAIN);
				$this->addMessage($message);
			}
		}
		return $saved;
	}
}
