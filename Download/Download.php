<?php

class Amd_Download
{
	const FILES_SEPARATOR = ' ';

	protected $_args = array();
	
	public function __construct($type, array $args)
	{
		if (empty($type) || empty($args))
			return $this->nonexisting();

		header('Etag: "' . md5($_SERVER['REQUEST_URI']) .'"');
		header('Cache-Control: public');
		header('Pragma: public');
		header('Expires: ' . date(DATE_RFC822,strtotime(" 7 day")));
		
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			// if the browser has a cached version of this file, send 304
			header('Last-Modified: ' . $_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
			die;
		}

		$this->_args = $args;

		$types = array(
			'js'			=> 'javascript',
			'javascript'	=> 'javascript',
			'css'			=> 'style',
			'style'			=> 'style',
			'less'			=> 'less',
			'file'			=> 'files'
		);

		if (array_key_exists($type, $types)) {
			$goto = '_' . $types[$type];
			try {
				$this->$goto();
			} catch (Exception $err) {
				return $this->nonexisting();
			}
		}
		else
			return $this->nonexisting();
	}
	
	/**
	 * Download style sheet
	 */
	protected function _style()
	{
		if (!$files = implode('/', $this->_args))
			die;

		require_once AMD_PATH . '/Download/Models/File/Style.php';

		$files = explode(self::FILES_SEPARATOR, $files);

		$output = array();
		
		foreach ($files as $file)
		{
			$path = TEMPLATEPATH . '/css/' . $file . '.' . Download_Model_File_Style::EXTENSION;
			$output[] = new Download_Model_File_Style($path);
		}

		/**
		 * Send content type headers
		 */
		header('Content-Type: ' . Download_Model_File_Style::CONTENT_TYPE);
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');

		require_once AMD_PATH . '/Download/Models/Parse.php';

		$parser = new Download_Model_Parse();
		echo $parser->parseFiles($output);
		
		die;
	}
	
	/**
	 * Download javascript
	 */
	protected function _javascript()
	{
		if (!$files = implode('/', $this->_args))
			die;

		require_once AMD_PATH . '/Download/Models/File/Javascript.php';

		$files = explode(self::FILES_SEPARATOR, $files);
		
		$output = array();
		
		foreach ($files as $file)
		{
			$path = TEMPLATEPATH . '/js/' . $file . '.' . Download_Model_File_Javascript::EXTENSION;
			$output[] = new Download_Model_File_Javascript($path);
		}

		/**
		 * Send content type headers
		 */
		header('Content-Type: ' . Download_Model_File_Javascript::CONTENT_TYPE);
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');

		require_once AMD_PATH . '/Download/Models/Parse.php';

		$parser = new Download_Model_Parse();
		echo $parser->parseFiles($output);
		
		die;
	}

	/**
	 * Download and parse less files
	 */
	protected function _less()
	{
		if (!$files = implode('/', $this->_args))
			die;

		require_once AMD_PATH . '/Download/Models/File/Less.php';

		$files = explode(self::FILES_SEPARATOR, $files);

		$output = array();

		foreach ($files as $file)
		{
			$path = TEMPLATEPATH . '/less/' . $file . '.' . Download_Model_File_Less::EXTENSION;
			$output[] = new Download_Model_File_Less($path);
		}

		/**
		 * Send content type headers
		 */
		header('Content-Type: ' . Download_Model_File_Less::CONTENT_TYPE);
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');

		require_once AMD_PATH . '/Download/Models/Parse.php';

		$parser = new Download_Model_Parse();
		echo $parser->parseFiles($output);

		die;
	}

	/**
	 * Download files / uploads
	 */
	protected function _files()
	{
		// Not a file
		if (empty($this->_args) || count($this->_args)!=1)
			return $this->nonexisting();

		list($id) = explode('.', array_shift($this->_args));

		$width		= !empty($_GET['width'])?(int)$_GET['width']:null;
		$height		= !empty($_GET['height'])?(int)$_GET['height']:null;
		$crop		= !empty($_GET['crop'])?(bool)$_GET['crop']:false;
		$refpoint	= !empty($_GET['refpoint']) && in_array($_GET['refpoint'], array('nw','n','ne','e','se','s','sw','w'))?$_GET['refpoint']:null;
		$mask		= !empty($_GET['mask'])?(bool)$_GET['mask']:null;
		$blur		= !empty($_GET['blur'])?(int)$_GET['blur']:0;
		$forceRatio = !empty($_GET['forceratio'])?(bool)$_GET['forceratio']:false;
		$save		= !empty($_GET['save'])?(bool)$_GET['save']:false;

		require_once AMD_PATH . '/Download/Models/File/File.php';
		require_once AMD_PATH . '/Download/Models/Parse.php';

		$file = new Download_Model_File_File($id, $width, $height, $crop, $refpoint, $mask, $blur, $forceRatio);

		/**
		 * Send content type headers
		 */
		header('Content-type: ' . $file->getFile()->post_mime_type);
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
		header('Content-Disposition: ' . (in_array($file->getFile()->post_mime_type, array('image/png','image/jpeg','image/gif'))&&false===$save?'inline':'attachment') . '; filename="' . $file->getFile()->post_name . strrchr($file->getFile()->guid,'.') . '"');

		$parser = new Download_Model_Parse();
		echo $parser->parseFile($file);

		die;
	}
	
	protected function nonexisting()
	{
		global $wp_query;
		header("HTTP/1.0 404 Not Found - Archive Empty");
		$wp_query->set_404();
		require TEMPLATEPATH.'/404.php';
		die;
	}
	
}