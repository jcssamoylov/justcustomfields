<?php

namespace jcf\models;
use jcf\core;

class Settings extends core\Model {
	
	const CONF_MS_NETWORK = 'network';
	const CONF_MS_SITE = 'site';
	const CONF_SOURCE_DB = 'database';
	const CONF_SOURCE_FS_THEME = 'fs_theme';
	const CONF_SOURCE_FS_GLOBAL = 'fs_global';

	protected $_layer;
	
	public $source;
	public $network;
	
	/**
	 * Init constructor 
	 */
	public function __construct()
	{
		parent::__construct();
		$layer_factory = new DataLayerFactory();
		$this->_layer = $layer_factory->create();
	}

	/**
	 * Get source settings
	 * @return string
	 */
	public static function getDataSourceType() 
	{
		return get_site_option('jcf_read_settings', self::CONF_SOURCE_DB);
	}

	/**
	 * Get network settings
	 * @return string
	 */
	public static function getNetworkMode() 
	{
		if( MULTISITE && $network = get_site_option('jcf_multisite_setting') ) {
			return $network;
		}
		return self::CONF_MS_SITE;
	}

	/**
	 * Save settings
	 * @return boolean
	 */
	public function save()
	{
		if ( MULTISITE ) {
			$this->_updateNetworkMode();
		}
		return $this->_updateDataSource();
	}

	/**
	 * Update source data
	 * @return boolean
	 */
	protected function _updateDataSource() 
	{
		$current_value = self::getDataSourceType();
		$saved = FALSE;

		if ( MULTISITE && ($this->network != self::CONF_MS_NETWORK && $this->source == self::CONF_SOURCE_FS_GLOBAL) ) {
			$error = __('<strong>Settings storage update FAILED!</strong>. Your MultiSite Settings do not allow to set global storage in FileSystem', \jcf\JustCustomFields::TEXTDOMAIN);
			$this->addError($error);
		}
		else {
			$saved = update_site_option('jcf_read_settings', $this->source);

			if ( $saved ) {
				$message = __('<strong>Settings storage</strong> configurations has been updated.', \jcf\JustCustomFields::TEXTDOMAIN);
				$this->addMessage($message);
			}
		}

		return $saved;
	}

	/**
	 * Update network data
	 * @return boolean
	 */
	protected function _updateNetworkMode() 
	{
		$current_value = self::getNetworkMode();

		if ( $current_value ) {
			$saved = update_site_option( 'jcf_multisite_setting', $this->network );

			if ( $saved ) {
				$message = __('<strong>MultiSite settings</strong> has been updated.', \jcf\JustCustomFields::TEXTDOMAIN);
				$this->addMessage($message);
			}
		}

		return $saved;
	}
}
