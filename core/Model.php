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

		$all_messages = array();

		if ( !empty($this->_messages) ) {
			foreach ( $this->_messages as $msg ) {
				$all_messages[] = array('notice', $msg);
			}
		}
		
		if ( !empty($this->_errors) ) {
			foreach ( $this->_errors as $msg ) {
				$all_messages[] = array('error', $msg);
			}
		}

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
			$this->_request = $params;
			return true;
		}
		return false;
	}
}

