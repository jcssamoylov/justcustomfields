<?php

namespace jcf\core;

class Autoloader
{

	public function __construct()
	{
		spl_autoload_register(array( $this, 'autoload' ));
	}

	/**
	 * Autoload files by classes
	 * @param string $file
	 */
	public function autoload( $file )
	{
		$file = str_replace('\\', '/', $file);

		// project-specific namespace prefix
		$prefix = 'jcf\\';

		$len = strlen($prefix);
		// get the relative class name
		if ( substr($file, 0, 3) . '\\' == $prefix ) {
			$relative_class = substr($file, $len);
		}
		else {
			$relative_class = $file;
		}

		$path = JCF_ROOT;
		$filepath = JCF_ROOT . '/' . str_replace('\\', '/', $relative_class) . '.php';

		if ( file_exists($filepath) ) {
			require_once($filepath);
		}
		else {
			$flag = true;
			$this->recursiveAutoload($relative_class, $path, &$flag);
		}
	}

	/**
	 * Recursive autoload
	 * @param string $file
	 * @param string $path
	 * @param boolean $flag
	 */
	public function recursiveAutoload( $file, $path, $flag )
	{
		if ( FALSE !== ($handle = opendir($path)) && $flag ) {
			while ( FAlSE !== ($dir = readdir($handle)) && $flag ) {
				if ( strpos($dir, '.') === FALSE ) {
					$path2 = $path . '/' . $dir;
					$filepath = $path2 . '/' . $file . '.php';

					if ( file_exists($filepath) ) {
						$flag = FALSE;
						require_once($filepath);
						break;
					}
					$this->recursiveAutoload($file, $path2, &$flag);
				}
			}
			closedir($handle);
		}
	}

}

new Autoloader();
