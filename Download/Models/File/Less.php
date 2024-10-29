<?php

require_once AMD_PATH . '/Download/Models/File/Abstract.php';
require_once AMD_PATH . '/Lessphp/lessc.inc.php';

class Download_Model_File_Less extends Download_Model_File_Abstract
{

	const 		EXTENSION		= 'less',
				CONTENT_TYPE	= 'text/css';

	protected 	$_path;

	/**
	 * Construct Style File
	 * @param string $path
	 * @throws Exception
	 */
	public function __construct($path)
	{
		if (file_exists($path) && is_readable($path))
			$this->_path = $path;
		else
			throw new Exception('File (' . $path . ') does not exist');
	}

	/**
	 * Get file contents from path
	 * @return string
	 */
	public function __toString()
	{
		$less = new lessc;
		// No caching in none production environments
		if (defined('APPLICATION_ENV') && APPLICATION_ENV!='production') {
			return $less->compileFile($this->_path);
		}

		$less->checkedCompile($this->_path, ABSPATH . "wp-content/uploads/output-less.css");

		return file_get_contents(ABSPATH . "wp-content/uploads/output-less.css");
	}

}