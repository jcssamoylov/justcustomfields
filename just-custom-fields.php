<?php
/*
Plugin Name: Just Custom Fields for Wordpress
Plugin URI: http://justcoded.com/just-labs/just-custom-fields-for-wordpress-plugin/
Description: This plugin add custom fields for standard and custom post types in WordPress.
Tags: custom, fields, custom fields, meta, post meta, object meta, editor
Author: Alexander Prokopenko
Author URI: http://justcoded.com/
Version: 2.3
Donate link: http://justcoded.com/just-labs/just-custom-fields-for-wordpress-plugin/
*/

define('JCF_ROOT', dirname(__FILE__));
define('JCF_TEXTDOMAIN', 'just-custom-fields');
define('JCF_CONF_MS_NETWORK', 'network');
define('JCF_CONF_MS_SITE', 'site');
define('JCF_CONF_SOURCE_DB', 'database');
define('JCF_CONF_SOURCE_FS_THEME', 'fs_theme');
define('JCF_CONF_SOURCE_FS_GLOBAL', 'fs_global');

require_once( JCF_ROOT.'/core/Autoloader.php');
require_once( JCF_ROOT.'/functions/helper.php' );

if(!function_exists('pa')) {
	function pa($mixed, $stop = false) {
		$ar = debug_backtrace(); $key = pathinfo($ar[0]['file']); $key = $key['basename'].':'.$ar[0]['line'];
		$print = array($key => $mixed); echo( '<pre>'.htmlentities(print_r($print,1)).'</pre>' );
		if($stop == 1) exit();
	}
}

function run_plugin_name() {
    
    spl_autoload_register('JCF\Autoloader::autoload');

    $plugin = new JCF\JustCustomFields();
    $plugin->run();
}

run_plugin_name();


?>