<?php

namespace jcf\core;

/**
 *	Main Model
 */
class Model
{
	protected $_errors;
 	protected $_messages;
	protected $_request;
 	
 	public function __construct(){}
 	
	/**
	 * Set errors
	 * @param string $error
	 */
 	public function addError($error) {
 		$this->_errors[] = $error;
 		
 		add_action('jcf_print_admin_notice', array($this, 'printMessages') );
 	}

	/**
	 * Set messages
	 * @param string $message
	 */
 	public function addMessage($message)
 	{
		$this->_messages[] = $message;

 		add_action('jcf_print_admin_notice', array($this, 'printMessages') );
 	}

	/**
	 * Render notices 
	 * @param array $args
	 * @return html
	 */
 	public function printMessages($args = array()) 
	{
		if( empty($this->_messages) && empty($this->_errors) ) return;

		global $wp_version;
		include( JCF_ROOT . '/views/notices.tpl.php');
 	}

	/**
	 * Set request params
	 * @param array $params
	 * @return boolean
	 */
	public function load($params)
	{
		if ( !empty($params) ) {
			$this->_setAttributes($params);
			return true;
		}
		return false;
	}
	
	protected function _setAttributes($params)
	{
		foreach ( $params as $key => $value) {		
			property_exists(get_class($this), $key) && $this->$key = strip_tags(trim($value));
		}
	}
}

