<?php

namespace jcf\models;

class MainModel {
	
	public $notices;
	
	public function __construct(){
		add_action('jcf_print_admin_notice', array($this, 'print_notice') );
	}
	
	public function add_notice($type, $message) {
		$this->notices[] = array($type, $message);
	}

	public function print_notice($args = array()) {

		if( empty($this->notices) ) return;
		foreach($this->notices as $msg)
		{
			include( JCF_ROOT . '/views/notices.tpl.php');
		}
	}

}

