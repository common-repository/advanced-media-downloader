<?php

class Download_Model_Parse
{

	public function __construct() {}
	
	/**
	 * Get to string from Download_Model_File_Abstract
	 * @param Download_Model_File_Abstract $file
	 * @return string
	 */
	public function parseFile(Download_Model_File_Abstract $file)
	{
		return (string) $file;
	}
	
	/**
	 * Get to string from multiple Download_Model_File_Abstract
	 * @param array $files
	 * @return string
	 */
	public function parseFiles(array $files)
	{
		$return = '';
		
		foreach ($files as $file) {
			$return .= $this->parseFile($file) . "\n\n"; 
		}
		
		return $return;
	}

}