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

/**
 *
 * @author unrza249
 *
 */
class LabledIconImage {


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
	 * The icon image resource.
	 *
	 * @var ressource
	 */
	private $imgIcon;

	/**
	 * The label text string.
	 *
	 * @var string
	 */
	private $labelText;

	/**
	 * Dimensions of the lable text.
	 * @var Array
	 */
	private $textBox;

	/**
	 * The icon object to be rendered.
	 *
	 * @var Icon
	 */
	private $icon;


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
	 *
	 * @var boolean
	 */
	private $drawBox = false;

	/**
	 *
	 * @var int
	 */
	private $boxPadding;

	/**
	 *
	 * @var int
	 */
	private $boxRadius;

	/**
	 * The color to use for the rendering of the bounding box.
	 * The color is encoded as an array with its' elements representing the RGB colors
	 * between 0 and 255.
	 *
	 * @var array
	 */
	private $boxColor;

	/**
	 * The color to use for the rendering of the shading of the bounding box.
	 * The color is encoded as an array with its' elements representing the RGB colors
	 * between 0 and 255.
	 *
	 * @var array
	 */
	private $boxShadeColor;

	/**
	 * Calculated width of the bounding box
	 *
	 * @var int
	 */
	private $boxWidth;

	/**
	 * Calculated height of the bounding box
	 *
	 * @var int
	 */
	private $boxHeight;

	/**
	 * Was the output image already rendered?
	 *
	 * @var boolean
	 */
	private $isRendered = false;

	/**
	 *
	 * @param $icon Icon
	 */
	function __construct($iconContainer = null) {
		if ($iconContainer != null) {
			$this->setIconContainer($iconContainer);
		}


		global $overview_boundingBoxShadeColor, $overview_boundingBoxColor, $overview_boundingBoxRadius, $overview_boundingBox,$overview_boundingBoxPadding, $overview_fontSize, $overview_fontColor, $overview_fontFile, $overview_textPadding;
		$this->fontAngle = 0; // not configurable -> not needed
		$this->setFontColor($overview_fontColor);
		$this->setFontSize($overview_fontSize);
		$this->setFontFile($overview_fontFile);
		$this->setTextPadding($overview_textPadding);

		$this->setBoxPadding($overview_boundingBoxPadding);
		$this->drawBox($overview_boundingBox);
		$this->setBoxRadius($overview_boundingBoxRadius);
		$this->setBoxColor($overview_boundingBoxColor);
		$this->setBoxShadeColor($overview_boundingBoxShadeColor);
	}

	function __destruct() {
		//debug("LabledIconImage->__destruct();");
	}

	function destroy() {
	}


	function setIcon($icon) {
		$this->icon = $icon;

		// set default label text
		$this->setLabel($icon->getBasename());
	}

	function setIconContainer($iconContainer) {
		global $overview_iconSize;
		$iconArr = $iconContainer->getIconRealSize($overview_iconSize);
		$this->setIcon($iconArr[0]);
	}

	function setLabel($label) {
		$this->labelText = $label;
	}

	function drawBox($draw) {
		$this->drawBox = $draw;
	}

	function setBoxRadius($rad) {
		$this->boxRadius = $rad;
	}

	function setBoxColor($array) {
		$this->boxColor = $array;
	}

	function setBoxShadeColor($array) {
		$this->boxShadeColor = $array;
	}

	private function updateDimensions() {
		// set dimensions to the icon's width and height
		$this->width = $this->icon->getImageWidth();
		$this->height = $this->icon->getImageHeight();


		// add size for the bounding box around th icon image
		if ($this->drawBox) {
			$this->width += 2 * $this->boxPadding;
			$this->height += 2 * $this->boxPadding;

			$this->boxWidth = $this->width;
			$this->boxHeight = $this->height;
		}

		global $overview_label;
		if ($overview_label) {
			// update image dimension for the included label text
			$this->textBox = Image::calculateTextBox($this->labelText, $this->fontFile, $this->fontSize, $this->fontAngle);
			$this->width = max(array($this->width, $this->textBox["width"]));
			$this->height += $this->textBox["height"] + $this->textPadding;
		}
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

	function setTextPadding($pixels) {
		$this->textPadding = $pixels;
	}

	function setBoxPadding($pixels) {
		$this->boxPadding = $pixels;
	}

	/**
	 * Assembles the image consisting of the given Icon and its' label text written below.
	 */
	function render() {
		/*
		 * calculate the current image dimensions
		 */
		$this->updateDimensions();
		$xOffset = 0;
		$yOffset = 0;


		/*
		 * create/load images
		 */
		$this->img = Image::create($this->width, $this->height);
		$this->imgIcon = Image::loadFromIcon($this->icon);

		/*
		 * colors
		 */
		// configured text color
		$textColor = imagecolorallocate($this->img, $this->fontColor[0], $this->fontColor[1], $this->fontColor[2]);



		/*
		 * rendering
		 */
		if ($this->drawBox) {
			// shaded box color
			$boxColor = imagecolorallocate($this->img, $this->boxColor[0],$this->boxColor[1],$this->boxColor[2]);
			$boxShadeColor = imagecolorallocate($this->img, $this->boxShadeColor[0],$this->boxShadeColor[1],$this->boxShadeColor[2]);
			// draw shaded box
			Image::drawRectangleWithRoundedCorners(
			$this->img,
			($this->width/2 - $this->boxWidth/2), 0,
			($this->width/2 + $this->boxWidth/2) - 1, $this->boxHeight,
			$this->boxRadius,
			$boxColor,
			$boxShadeColor
			);

			$xOffset = $this->boxPadding;
			$yOffset = $this->boxPadding;
		}
			
		// copy the loaded icon image
		imagecopy(
		$this->img,
		$this->imgIcon,
		($this->width/2 - $this->icon->getRealWidth()/2), $yOffset,
		0, 0,
		$this->icon->getImageWidth(), $this->icon->getImageHeight()
		);

		global $overview_label;
		if ($overview_label) {
			// write the given label text below the icon image
			imagettftext(
			$this->img,
			$this->fontSize,
			$this->fontAngle,
			$this->textBox["left"] + ($this->width / 2) - ($this->textBox["width"] / 2),
			$this->height - $this->textBox["bottom"],
			$textColor,
			$this->fontFile,
			$this->labelText
			);
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


	/**
	 * Write the previously rendered picture to the given output file.<br/>
	 * <b> If the file already exists it will be overwritten!</b>
	 *
	 * @param $file
	 * @return unknown_type
	 */
	function writeToPNG($file = null) {
		if (!$this->isRendered) {
			$this->render();
		}

		if ($file == null) {
			$file = $this->icon->getBasename() . ".png";
		}

		imagepng($this->img, $file);
	}

}

?>