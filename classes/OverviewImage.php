<?php
/*
 * RRZE Icon Inspector, developed as a part of the RRZE icon set.
 * Copyright 2008, RRZE, and individual contributors
 * as indicated by the @author tags. See the copyright.txt file in the
 * distribution for a full listing of individual contributors. This
 * product includes software developed by the Apache Software Foundation
 * http://www.apache.org/
 *
 * This is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; either version 2.1 of
 * the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this software; if not, write to the Free
 * Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA
 * 02110-1301 USA, or see the FSF site: http://www.fsf.org.
 */

if (!extension_loaded('gd')) {
	die('Please recompile PHP with the gd extension enabled.');
}

require_once("classes/Image.php");
require_once("classes/LabledIconImage.php");


/**
 *
 * @author unrza249
 *
 */
class OverviewImage {


	/**
	 * The width of the output image.
	 *
	 * @var int
	 */
	private $width;


	/**
	 * The height of the output image.
	 *
	 * @var int
	 */
	private $height;

	/**
	 * The output image resource.
	 *
	 * @var ressource
	 */
	private $img;


	/**
	 * The TTF font file to use for the rendering of the label text.
	 *
	 * @var string
	 */
	private $fontFile;

	/**
	 * The font size to use for the rendering of the label text.
	 *
	 * @var int
	 */
	private $fontSize;

	/**
	 * The font angle to use for the rendering of the label text.
	 *
	 * @var int
	 */
	private $fontAngle;

	/**
	 * The font color to use for the rendering of the label text.
	 * The color is encoded as an array with its' elements representing the RGB colors
	 * between 0 and 255.
	 *
	 * @var array
	 */
	private $fontColor;

	/**
	 * The padding space between the icon image and the label text in pixels.
	 *
	 * @var int
	 */
	private $textPadding;

	/**
	 * Was the output image already rendered?
	 *
	 * @var boolean
	 */
	private $isRendered = false;

	/**
	 * Array of source image resources to be assembled to an overview image.
	 *
	 * @var array
	 */
	private $srcImages;

	/**
	 * Maximal width of the overview image. This is used
	 * to calculate how many source images are in one row.
	 *
	 * @var int
	 */
	private $maxWidth;


	/**
	 * The width of a single source image.
	 *
	 * @var int
	 */
	private $singleImageWidth;

	/**
	 * The height of a single source image.
	 *
	 * @var int
	 */
	private $singleImageHeight;


	/**
	 * Number of images in one row.
	 *
	 * @var int
	 */
	private $numColumns;

	/**
	 * Padding between images, horizontally and vertically.
	 *
	 * @var int
	 */
	private $imagePadding;

	
	private $imageCoords;
	
	/**
	 *
	 * @param $srcImages array
	 */
	function __construct($srcImages = null) {
		global $overview_fontSize, $overview_fontColor, $overview_fontFile, $overview_textPadding, $overview_maxWidth, $overview_iconSize, $overview_imagePadding;

		$this->fontAngle = 0; // not configurable -> not needed
		if ($srcImages == null) {
			$this->srcImages = array();
		}
		else {
			$this->singleImageWidth = imagesx($srcImages[0]);
			$this->singleImageHeight = imagesy($srcImages[0]);
		}

		$this->imagePadding = $overview_imagePadding; // not configurable

		$this->setFontColor($overview_fontColor);
		$this->setFontSize($overview_fontSize);
		$this->setFontFile($overview_fontFile);
		$this->setPadding($overview_textPadding);
		$this->setMaxWidth($overview_maxWidth);
		
		$this->imageCoords = array();
	}

	function __destruct() {
		debug("OverviewImage->__destruct();");
	}

	function destroy() {
		
	}
	
	function getImageCoords($image) {
		return $this->imageCoords[$image];
	}
	
	

	/**
	 *
	 * @param $imageResource
	 */
	function add($imageResource) {
		$newWidth = imagesx($imageResource);
		if ($this->singleImageWidth < $newWidth) {
			$this->singleImageWidth = $newWidth;
		}

		$newHeight = imagesy($imageResource);
		if ($this->singleImageHeight < $newHeight) {
			$this->singleImageHeight = $newHeight;
		}

		$this->srcImages[] = $imageResource;
	}

	private function updateDimensions() {
		// calculate number of images in one row
		$this->numColumns = floor($this->maxWidth / $this->singleImageWidth);

		// calc dimensions
		$this->width = ($this->numColumns * $this->singleImageWidth) + (($this->numColumns - 1) * $this->imagePadding);
		$this->numRows = ceil(count($this->srcImages) / $this->numColumns);
		$this->height =  ($this->numRows * $this->singleImageHeight) + (($this->numRows - 1) * $this->imagePadding);
	}

	function setMaxWidth($maxWidth) {
		$this->maxWidth = $maxWidth;
	}

	function setFontFile($path) {
		$this->fontFile = $path;
	}

	function setFontColorRGB($red, $green, $blue) {
		$this->fontColor = array($red, $green, $blue);
	}

	function setFontColor($rgbArray) {
		$this->fontColor = $rgbArray;
	}


	function setFontSize($fontSize) {
		$this->fontSize = $fontSize;
	}

	function setPadding($pixels) {
		$this->textPadding = $pixels;
	}

	/**
	 * Assembles the image consisting of the given Icon and its' label text written below.
	 */
	function render() {
		/*
		 * calculate the current image dimensions
		 */
		$this->updateDimensions();

		/*
		 * create/load images
		 */
		$this->img = Image::create($this->width, $this->height);

		/*
		 * rendering
		 */
		// copy the loaded icon images
		$i = 0;
		foreach ($this->srcImages as $srcImage) {
			$i++;
			$col = (($i - 1) % $this->numColumns);
			$row = (ceil($i / $this->numColumns) - 1);

			$srcWidth = imagesx($srcImage);
			$srcHeight = imagesy($srcImage);

			
			$posX = $col * ($this->singleImageWidth + $this->imagePadding) + (($this->singleImageWidth - $srcWidth) / 2);
			$posY = $row * ($this->singleImageHeight + $this->imagePadding);

			imagecopy(
			$this->img,
			$srcImage,
			$posX,
			$posY,
			0,
			0,
			$srcWidth,
			$srcHeight
			);
			
			$this->imageCoords[$srcImage] = array($posX, $posY, $posX + $srcWidth, $posY + $srcHeight);
		}

		/*
		 * Remember rendering the image
		 */
		$this->isRendered = true;
	}

	
	/**
	 * Returns the rendered image resource of the output image for
	 * further processing.
	 *
	 * @return resource
	 */
	function getResource() {
		if (!$this->isRendered) {
			$this->render();
		}
		return $this->img;
	}

	function getSourceImages() {
		return $this->srcImages;
	}
	
	
	
	/**
	 * Write the previously rendered picture to the given output file.<br/>
	 * <b> If the file already exists it will be overwritten!</b>
	 *
	 * @param $file
	 */
	function writeToPNG($file = null) {
                global $dir;
                $tplPath = basename($dir);

		if (!$this->isRendered) {
			$this->render();
		}

		if ($file == null) {
			$file = "generated/".$tplPath."/overview.png";
		}

		imagepng($this->img, $file);
	}


	/**
	 * Write the previously rendered picture to the given output file.<br/>
	 * <b> If the file already exists it will be overwritten!</b>
	 *
	 * @param $file
	 */
	function writeToJPEG($file = null) {
                global $dir;
                $tplPath = basename($dir);
		
                if (!$this->isRendered) {
			$this->render();
		}

		if ($file == null) {
			$file = "generated/".$tplPath."/overview.jpeg";
		}

		imagejpeg($this->img, $file);
	}


	/**
	 * Generate a new LabledIconImage from the given data and add
	 * it to this OverviewImage instance
	 * @param IconContainer $iconContainer
	 * @param Array $metadata
	 */
	function generateAndAdd($iconContainer, $metadata) {
		global $overview_iconSize;
		$sizes = $iconContainer->getAvailableRealSizes();
		if (!in_array($overview_iconSize, $sizes)) {
			warning($iconContainer, "Missing size " . $overview_iconSize . " -- Skipping for overview image generation.");
			return;
		}
		
		$image = new LabledIconImage($iconContainer);
		$image->setLabel($metadata['title']);
		$imageResource = $image->getResource();
		$this->add($imageResource);
		
		return $imageResource;
	}
}

?>
