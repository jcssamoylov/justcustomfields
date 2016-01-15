<?php

namespace jcf\core;

class Model {
	
	protected $_errors;
	protected $_messages;
	
	public function __construct(){
		//add_action('jcf_print_admin_notice', array($this, 'print_notice') );
	}
	
	public function addError($error) {
		$this->_errors[] = array($type, $message);
		
		add_action('jcf_print_admin_notice', array($this, 'printMessages') );
	}
	
	public function addMessage($message)
	{
		add_action('jcf_print_admin_notice', array($this, 'printMessages') );
	}

	public function print_notice($args = array()) {

		if( empty($this->notices) ) return;
		foreach($this->notices as $msg)
		{
			include( JCF_ROOT . '/views/notices.tpl.php');
		}
	}

}

