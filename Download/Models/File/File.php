<?php

require_once AMD_PATH . '/Download/Models/File/Abstract.php';

class Download_Model_File_File extends Download_Model_File_Abstract
{

	protected 	$_path,
				$_file,
				$_width		    = null,
				$_height 	    = null,
				$_crop		    = false,
				$_refpoint 	    = null,
				$_mask		    = false,
				$_blur          = 0,
				$_forceRatio    = false;

	/**
	 * Construct File
	 *
	 * @param int $id
	 * @param int|null $width
	 * @param int|null $height
	 * @param bool $crop
	 * @param string|null $refpoint
	 * @param bool $mask
	 * @throws InvalidArgumentException
	 */
	public function __construct($id, $width=null, $height=null, $crop=false, $refpoint=null, $mask=false, $blur=0, $forceRatio=false)
	{
		$this->_file        = get_post($id);

		if (!$this->_file || $this->_file->post_type != 'attachment')
			throw new InvalidArgumentException('No file found');

		$this->_path        = get_attached_file($this->_file->ID, true);
		$this->_width		= (int) $width;
		$this->_height		= (int) $height;
		$this->_crop		= (bool) $crop;
		$this->_refpoint	= (string) $refpoint;
		$this->_mask		= (bool) $mask;
		$this->_blur		= (int) $blur;
		$this->_forceRatio  = (bool) $forceRatio;
	}

	/**
	 * Getter file
	 * @return stdClass
	 */
	public function getFile()
	{
		return $this->_file;
	}

	/**
	 * Get file contents from path
	 * @return string
	 */
	public function __toString()
	{
		if (($this->_width!==0 || $this->_height!==0 || $this->_blur!==0)
				&& in_array($this->_file->post_mime_type, array('image/png','image/jpeg','image/gif'))
		) {
			$path       = ($basePath = dirname($this->_path) . '/' . $this->_file->ID . '/') .
							sha1('w' . $this->_width . 'h' . $this->_height . 'c' . $this->_crop . 'r' . $this->_refpoint . 'm' . $this->_mask . 'b' . $this->_blur . 'fr' . $this->_forceRatio);

			/**
			 * Load from cache folder
			 */
			if (file_exists($path) && is_readable($path)) {
				return file_get_contents($path);
			} else {
				// Get some extra memory
				ini_set('memory_limit', '256M');

				// Resize
				//if ($this->_width || $this->_height || $this->_blur) {

					require_once AMD_PATH . '/PHPThumb/ThumbLib.inc.php';

					$image = PhpThumbFactory::create($this->_path, array('interlace'=>true));

					$dimensions = $image->getCurrentDimensions();
					if ((null===$this->_width || 0===$this->_width) && (null===$this->_height || 0===$this->_height)) {
						$width	= $dimensions['width'];
						$height	= $dimensions['height'];
					}
					elseif (null===$this->_width || 0===$this->_width) {
						$width	= $dimensions['width'];
						$height	= $this->_height;
					}
					elseif (null===$this->_height || 0===$this->_height) {
						$width	= $this->_width;
						$height	= $dimensions['height'];
					}
					else {
						$width	= $this->_width;
						$height	= $this->_height;
					}

					// Make sure there has to be resizing done
					if ($width < $dimensions['width'] || $height < $dimensions['height'] || $this->_blur>0 || $this->_forceRatio)
					{

						// Ratio
						/**
						 * $height / $width * $dimensions['width'] = $new_height
						 * $width / $height * $dimensions['height'] = $new_width
						 *
						 */
						// Crop
						if ($this->_crop) {

							if (false==$this->_mask) {

								if ($this->_forceRatio && ($dimensions['height']<$height || $dimensions['width']<$width)) {
									if ($height / $width * $dimensions['width'] <= $dimensions['height']) {
										$height = $height / $width * $dimensions['width'];
										$width  = $dimensions['width'];
									}
									else {
										$width  = $width / $height * $dimensions['height'];
										$height = $dimensions['height'];
									}
								}
								else {
									$scalex = ( $height / $dimensions['height'] );

									$new_width = round($dimensions['width'] * $scalex);
									$new_height = round($dimensions['height'] * $scalex);

									if ( $new_width < $width || $new_height < $height )
									{
										$image->resize($width, $dimensions['height']);
									}
									else
									{
										$image->resize($dimensions['width'], $height);
									}
								}

		                        $dimensions = $image->getCurrentDimensions();
							}

							if ($this->_refpoint) {
								$width	= ($dimensions['width'] < $width) ? $dimensions['width'] : $width;
								$height = ($dimensions['height'] < $height) ? $dimensions['height'] : $height;
								switch ($this->_refpoint) {
									case 'n':
										$startX = intval(($dimensions['width'] - $width) / 2);
										$startY = 0;
										break;
									case 'ne':
										$startX = $dimensions['width'];
										$startY = 0;
										break;
									case 'e':
										$startX = $dimensions['width'];
										$startY = intval(($dimensions['height'] - $height) / 2);
										break;
									case 'se':
										$startX = $dimensions['width'];
										$startY = $dimensions['height'];
										break;
									case 's':
										$startX = intval(($dimensions['width'] - $width) / 2);
										$startY = $dimensions['height'];
										break;
									case 'sw':
										$startX = 0;
										$startY = $dimensions['height'];
										break;
									case 'w':
										$startX = 0;
										$startY = intval(($dimensions['height'] - $height) / 2);
										break;
									case 'nw':
									default:
										$startX = 0;
										$startY = 0;
										break;
								}
								$image->crop($startX, $startY, $width, $height);
							}
							else {
								$image->cropFromCenter($width, $height);
							}
						}
						elseif ($width < $dimensions['width'] || $height < $dimensions['height']) {
							$image->resize($width, $height);
						}

						// Blur
						if ($this->_blur) {
							$image->filterGaussianBlur($this->_blur);
						}

						$image = $image->getImageAsString();

						// Create dir if it doesn't exist
						if (!is_dir($basePath))
							mkdir($basePath);

						// Remove old cache files when it is getting to much (safety)
						$files  = array();
						$dh     = opendir($basePath);

						if ($dh)
							while (false !== ($filename = readdir($dh))) {
								if ($filename=='.' || $filename=='..') continue;
								$files[] = $filename;
							}

						while(count($files)>=10) {
							@ unlink( $basePath . array_shift($files) );
						}

						file_put_contents($path, $image);

						return $image;
					}
				//}
			}
		}
		
		return file_get_contents($this->_path);
	}

}