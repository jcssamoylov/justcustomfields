<?php

namespace jcf\core;

/**
 *	Main controller
 */
class Controller 
{
	/*
	 *	Function for render views
	 */
	protected function _render($template, $params = array())
	{
		if ( !empty($params) ) {
  			foreach ($params as $key => $value) {
  				$$key = $value;
			}
  		}
		$folder = str_replace('jcf\\controllers\\', '', strtolower(str_replace('Controller', '', get_class($this))));
  		include( JCF_ROOT . '/views/' . $folder . '/' . $template . '.tpl.php' );
	}
}
