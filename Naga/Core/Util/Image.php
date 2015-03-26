<?php

namespace Naga\Core\Util;

use Naga\Core\FileSystem\File;

/**
 * Basic class for image resizing and cropping. BETA VERSION.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Util
 */
class Image extends File
{
	// filter consts
	const FilterGrayscale = IMG_FILTER_GRAYSCALE;

	/**
	 * @var object original width and height
	 */
	protected $_originalSize;
	/**
	 * @var object original aspect ratio
	 */
	protected $_originalRatio;
	/**
	 * @var array basic filters
	 */
	protected $_basicFilters = array();

	/**
	 * Saves a cropped image. Currently supports only jpeg images.
	 *
	 * @param string $fileName
	 * @param int $width
	 * @param int $height
	 * @param int $cropX
	 * @param int $cropY
	 * @param int $cropW
	 * @param int $cropH
	 * @return bool
	 * @throws \Exception
	 */
	public function saveCropped($fileName, $width, $height, $cropX = null, $cropY = null,
								$cropW = null, $cropH = null)
	{
		$this->initImage();
		$fileName = $fileName . $this->getExtensionFromMimeType($this->mimeType());

		$original = imagecreatefromjpeg($this->realPath());
		$buffer = imagecreatetruecolor($width, $height);

		if (is_null($cropW) || is_null($cropH))
			$cropSizes = $this->getCropSizeByAspectRatio((object)array('width' => $width, 'height' => $height));
		else
			$cropSizes = (object)array('width' => $cropW, 'height' => $cropH);

		if (is_null($cropX) || is_null($cropY))
			$cropPositions = $this->getCropPositions($cropSizes);
		else
			$cropPositions = (object)array('left' => $cropX, 'top' => $cropY);

		imagecopyresampled(
			$buffer,
			$original,
			0, 0,
			$cropPositions->left,
			$cropPositions->top,
			$width,
			$height,
			$cropSizes->width,
			$cropSizes->height
		);

		// applying filters
		foreach ($this->_basicFilters as $filter)
			imagefilter($buffer, $filter);

		$img = imagejpeg($buffer, $fileName, 90);
		if ($img)
			chmod($fileName, 0777);

		return $img;
	}

	/**
	 * Adds a basic filter that'll be applied to the image when saving.
	 *
	 * @param int $filter see class Filter consts
	 * @return $this
	 */
	public function addBasicFilter($filter)
	{
		$this->_basicFilters[$filter] = $filter;

	    return $this;
	}

	/**
	 * Removes a basic filter.
	 *
	 * @param int $filter see class Filter consts
	 * @return $this
	 */
	public function removeBasicFilter($filter)
	{
		if (isset($this->_basicFilters[$filter]))
			unset($this->_basicFilters[$filter]);

		return $this;
	}

	/**
	 * Initializes the image for processing. (getting image size & aspect ratio)
	 *
	 * @throws \Exception
	 */
	public function initImage()
	{
		$sizes = getimagesize($this->realPath());
		if ($sizes === false)
			throw new \Exception('Could not get image width and height.');

		$this->_originalSize = (object)array(
			'width' => $sizes[0],
			'height' => $sizes[1]
		);
		$this->_originalRatio = $this->_originalSize->width / $this->_originalSize->height;
	}

	/**
	 * Gets the extension from mime type. If there is no match, .jpg will be returned.
	 *
	 * @param string $mimeType
	 * @return string
	 */
	public function getExtensionFromMimeType($mimeType)
	{
		if ($mimeType == 'image/jpeg')
			return '.jpg';
		else if ($mimeType == 'image/png')
			return '.png';

		return '.jpg';
	}

	/**
	 * Gets the image's mime type.
	 *
	 * @return string
	 */
	public function mimeType()
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $this->realPath());
		finfo_close($finfo);

		return $mime;
	}

	/**
	 * Gets resized dimensions of the given width and height (originalSizes). You should set keepAspectRatio
	 * to false if both new width and new height specified.
	 *
	 * @param object $newSize
	 * @param bool $keepAspectRatio should we keep aspect ratio? (returns instantly with the new sizes if set to false)
	 * @return object
	 */
	public function getResizedDimensions($newSize, $keepAspectRatio = true)
	{
		// get aspect ratio of original image
		$originalAspectRatio = $this->_originalSize->width / $this->_originalSize->height;
		// default new aspect ratio is equal to original aspect ratio
		$newAspectRatio = $originalAspectRatio;

		// if we have new height
		if ($newSize->height)
			// get aspect ratio of the new image
			$newAspectRatio = $newSize->width / $newSize->height;

		// if keepAspectRatio is false, we won't resize anything, we just returning the new sizes
		if (!$keepAspectRatio && $newSize->width <= $this->_originalSize->width)
			return $newSize;

		// if keepAspectRatio is false, and newSize is bigger than originalSize we return original sizes
		if (!$keepAspectRatio && $newSize->width > $this->_originalSize->width)
			return $this->_originalSize;

		// if new width bigger than original width, we use original width (we need this for picture viewing, we display
		// the image in it's original size, with proper aspect ratio)
		if ($this->_originalSize->width < $newSize->width)
		{
			$newSize->width = $this->_originalSize->width;
			$newSize->height = $this->_originalSize->width / $newAspectRatio;
			return $newSize;
		}

		// if original width bigger than original height
		if ($this->_originalSize->width > $this->_originalSize->height)
			$newSize->height = floor($this->_originalSize->height * ($newSize->width / $this->_originalSize->width));
		// if original height bigger than original width
		else if ($this->_originalSize->height > $this->_originalSize->width)
			$newSize->height = floor(($this->_originalSize->height / $this->_originalSize->width) * $newSize->width);
		// if equal
		else
			$newSize->height = $newSize->width;

		return $newSize;
	}

	/**
	 * Gets the crop sizes we'll crop off from the original image.
	 *
	 * @param $newSize
	 * @return \stdClass
	 */
	public function getCropSizeByAspectRatio($newSize)
	{
		// get aspect ratio of original image
		$originalAspectRatio = $this->_originalSize->width / $this->_originalSize->height;
		// get aspect ratio of the new image
		$newAspectRatio = $newSize->width / $newSize->height;
		// we need this for multiple processing (cause if the out ratio is 1:1, it modifies this)
		// php doesn't copy the FUCKING OBJECT so we use clone. :@
		$originalSizes = clone $this->_originalSize;

		// if new height is 0, we calculate with the same aspect ratio as the original, but we set the new height
		// for the new width
		if (!$newSize->height)
		{
			$newAspectRatio = $originalAspectRatio;
			$newSize->height = $newSize->width / $newAspectRatio;
		}

		// if the new width equals the new height (1:1 ratio)
		if ($newSize->width == $newSize->height)
		{
			// if original width bigger than original height, we set width to height
			if ($originalSizes->width > $originalSizes->height)
				$originalSizes->width = $originalSizes->height;
			// if original height bigger than original width, we set height to width
			else if ($originalSizes->height > $originalSizes->width)
				$originalSizes->height = $originalSizes->width;

			return $originalSizes;
		}

		// if the new aspect ratio equals original aspect ratio, we set crop size to the original width and height
		if ($newAspectRatio == $originalAspectRatio)
		{
			$newSize->width = $originalSizes->width;
			$newSize->height = $originalSizes->height;
			return $newSize;
		}

		// if new width > original width we are setting new width to original width
		if ($newSize->width > $originalSizes->width)
			$newSize->width = $originalSizes->width;

		// calculate height with the new aspect ratio
		$newSize->height = $newSize->width / $newAspectRatio;

		// getting the width ratio of the new image and original image
		$widthRatio = $originalSizes->width / $newSize->width;
		// getting the height ratio of the new image and original image
		$heightRatio = $originalSizes->height / $newSize->height;

		// we will return these
		$width = $newSize->width;
		$height = $newSize->height;
		$newAspectRatio = $newSize->width / $newSize->height;

		// if width ratio is bigger than height ratio
		if ($widthRatio > $heightRatio)
		{
				$height = $originalSizes->height;
				$width = $height * $newAspectRatio;
		}
		// if height ratio is bigger than width ratio
		else if ($heightRatio > $widthRatio)
		{
			$width = $originalSizes->width;
			$height = $width / $newAspectRatio;
		}

		return (object)array('width' => floor($width), 'height' => floor($height));
	}

	/**
	 * Gets centered crop positions.
	 *
	 * @param object $cropSize
	 * @return object
	 */
	public function getCropPositions($cropSize)
	{
		$positions = (object)array('left' => 0, 'top' => 0);

		if ($this->_originalSize->width == $cropSize->width && $this->_originalSize->height == $cropSize->height)
			return $positions;

		$positions->left = $this->_originalSize->width - ($this->_originalSize->width / 2) - ($cropSize->width / 2);
		$positions->top = $this->_originalSize->height - ($this->_originalSize->height / 2) - ($cropSize->height / 2);

		return $positions;
	}

	/**
	 * Gets original size. (width, height)
	 *
	 * @return object
	 */
	public function originalSize()
	{
		return $this->_originalSize;
	}
}