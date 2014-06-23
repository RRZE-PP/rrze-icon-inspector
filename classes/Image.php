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

/**
 * Convenient helper class providing functionality for handling images in PHP.
 *
 * @author unrza249
 *
 */
class Image {

	/**
	 * Calculates the dimensions of the textbox around the given string.
	 *
	 * @param string $text
	 * @param string $fontFile
	 * @param int $fontSize
	 * @param int $fontAngle
	 * @return array
	 */
	static function calculateTextBox($text, $fontFile, $fontSize, $fontAngle) {
		$rect = imagettfbbox($fontSize, $fontAngle, $fontFile, $text);

		$minX = min(array($rect[0],$rect[2],$rect[4],$rect[6]));
		$maxX = max(array($rect[0],$rect[2],$rect[4],$rect[6]));
		$minY = min(array($rect[1],$rect[3],$rect[5],$rect[7]));
		$maxY = max(array($rect[1],$rect[3],$rect[5],$rect[7]));

		//if (abs($minY) > 0)  echo "MINY:" . abs($minY) . "::" .  (($maxY - $minY)) . "::" . $maxY . "                                                 ";

		return array(
    		"left"   => abs($minX),
			"right"  => $maxX,
    		"top"    => abs($minY),
			"bottom" => $maxY,
    		"width"  => $maxX - $minX,
    		"height" => $maxY - $minY,
			"box"    => $rect
		);

	}


	/**
	 * Create a new truecolor image resource with real transparency enabled.
	 *
	 * @param int $width
	 * @param  int $height
	 * @return resource
	 */
	static function create($width, $height) {
		//echo "width: " . $width . "  height: " . $height;
		$image = imagecreatetruecolor($width, $height);

		imagealphablending($image, true);
		imagesavealpha($image, true);

		// transparent background color
		$bgColor = imagecolorallocatealpha($image, 255, 255, 255, 127);
		imagefill($image, 0, 0, $bgColor);

		return $image;
	}

	/**
	 * Load an image from a PNG file.
	 *
	 * @param string $path
	 * @return resource
	 */
	static function loadFromPNG($path) {
		$image = imagecreatefrompng($path);
		imagealphablending($image, true);
		imagesavealpha($image, true);
		/*
		 $imageWidth = imagesx($image);
		 $imageHeight = imagesy($image);

		 $newImage = imagecreatetruecolor($imageWidth, $imageHeight);
		 imagealphablending($newImage, false);
		 imagesavealpha($newImage, true);

		 // transparent background color
		 $bgColor = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
		 imagefill($newImage, 0, 0, $bgColor);

		 imagecopy(
		 $newImage,
		 $image,
		 0, 0,
		 0, 0,
		 $imageWidth, $imageHeight
		 );

		 return $newImage;
		 */

		//imagepng($image, basename($path) . '_test.png');

		return $image;
	}

	/**
	 * Load an image from the PNG file that belongs to the given Icon object.
	 *
	 * @param Icon $icon
	 * @return resource
	 */
	static function loadFromIcon($icon) {
		global $dir;
		//echo "Loading " . $dir . "/" . $icon->getPath();
		$image = Image::loadFromPNG($dir . "/" . $icon->getPath());
		return $image;
	}

	/**
	 * Draws a box with shades and rounded edges.
	 *
	 * @param resource $im
	 * @param int $x1
	 * @param int $y1
	 * @param int $x2
	 * @param int $y2
	 * @param int $radius
	 * @param int $color
	 */
	static function drawRectangleWithRoundedCorners(&$im, $x1, $y1, $x2, $y2, $radius, $color, $shadeColor = null)	{
		imagealphablending($im, false);

		if ($shadeColor == null) {
			$rgbArr = imagecolorsforindex($im, $color);
			$shadeColor = imagecolorallocate($im, $rgbArr["red"] * 1.5, $rgbArr["green"] * 1.5, $rgbArr["blue"] * 1.5);
		}

		// Draw circled corners
		ImageFilledEllipse($im, $x1+$radius, $y1+$radius, $radius*2, $radius*2, $shadeColor);
		ImageFilledEllipse($im, $x2-$radius, $y1+$radius, $radius*2, $radius*2, $shadeColor);
		ImageFilledEllipse($im, $x1+$radius, $y2-$radius, $radius*2, $radius*2, $shadeColor);
		ImageFilledEllipse($im, $x2-$radius, $y2-$radius, $radius*2, $radius*2, $shadeColor);

		// Draw rectangle without corners
		ImageFilledRectangle($im, $x1+$radius, $y1, $x2-$radius, $y2, $shadeColor);
		ImageFilledRectangle($im, $x1, $y1+$radius, $x2, $y2-$radius, $shadeColor);


		ImageFilledRectangle($im, $x1+$radius, $y1+$radius, $x2-$radius, $y2-$radius, $color);


		//		ImageFilledArc($im, $x1+$radius, $y1+$radius, $radius*2, $radius*2, 180, 270, $color, IMG_ARC_PIE);
		//		ImageFilledArc($im, $x2-$radius, $y1+$radius, $radius*2, $radius*2, 270, 0, $color, IMG_ARC_PIE);
		//		ImageFilledArc($im, $x1+$radius, $y2-$radius, $radius*2, $radius*2, 90, 180, $color, IMG_ARC_PIE);
		//		ImageFilledArc($im, $x2-$radius, $y2-$radius, $radius*2, $radius*2, 0, 90, $color, IMG_ARC_PIE);

		imagealphablending($im, true);
	}


}

?>