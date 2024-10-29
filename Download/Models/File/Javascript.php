<?php

require_once AMD_PATH . '/Download/Models/File/Abstract.php';

class Download_Model_File_Javascript extends Download_Model_File_Abstract
{

	const 		EXTENSION		= 'js',
				CONTENT_TYPE	= 'text/javascript';
	
	protected 	$_path;
	
	/**
	 * Construct Javascript File
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
		return file_get_contents($this->_path);
	}

}