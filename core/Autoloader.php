<?php

namespace JCF;

class Autoloader
{
	const debug = 1;
	public function __construct(){}

	public static function autoload($file)
	{
		$file = str_replace('\\', '/', $file);

		// project-specific namespace prefix
		$prefix = 'JCF\\';

		$len = strlen($prefix);
		// get the relative class name
		if(substr($file, 0, 3).'\\' == $prefix ){
			$relative_class = substr($file, $len);
		}
		else {
			$relative_class = $file;
		}

		$path = JCF_ROOT;
		$filepath = JCF_ROOT .'/'.  str_replace('\\', '/', $relative_class) . '.php';

		if (file_exists($filepath))
		{
			require_once($filepath);
		}
		else
		{ 
			$flag = true;
			Autoloader::recursive_autoload($relative_class, $path, &$flag);
		}
	}

	public static function recursive_autoload($file, $path, $flag)
	{
	  if (FALSE !== ($handle = opendir($path)) && $flag)
	  {
		while (FAlSE !== ($dir = readdir($handle)) && $flag)
		{

		  if (strpos($dir, '.') === FALSE)
		  {
			$path2 = $path .'/' . $dir;
			$filepath = $path2 . '/' . $file . '.php';
			
			if (file_exists($filepath))
			{
			  $flag = FALSE;
			  require_once($filepath);
			  break;
			}
			Autoloader::recursive_autoload($file, $path2, &$flag); 
		  }
		}
		closedir($handle);
	  }
	}

  }


