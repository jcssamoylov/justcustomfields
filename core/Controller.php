<?php

namespace jcf\core;

/**
 *	Main controller
 */
class Controller 
{
	public function __construct(){}
	/*
	 *	Function for render views
	 */
	protected function _render($template, $params = array())
	{
		extract($params);
  		include( JCF_ROOT . '/views/' . $template . '.tpl.php' );
	}

	/*
	 *	Function for render views by AJAX
	 */
	protected function _renderAjax($data, $format, $params = array())
	{
		if ( $format == 'json' ) {
			$responce = json_encode($data);
			header( "Content-Type: application/json; charset=" . get_bloginfo('charset') );
		}
		else {
			header( "Content-Type: text/html; charset=" . get_bloginfo('charset') );
			ob_start();
			$this->_render($data, $params);
			$responce = ob_get_clean();
		}
		echo $responce;
		exit();
	}
}
