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
  		include( JCF_ROOT . $template . '.tpl.php' );
	}
}
