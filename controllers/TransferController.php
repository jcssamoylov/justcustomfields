<?php

namespace jcf\controllers;
use jcf\models;

class TransferController {
	public function __construct()
	{
		add_submenu_page(null, 'Transfer', 'Transfer', 'manage_options', 'jcf_transfer', array($this, 'transfer_page'));
	}
}

