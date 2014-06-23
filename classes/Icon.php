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
 * Wrapper class representing a single icon file.
 *
 * @author unrza249
 *
 */
class Icon {

	/**
	 * This array contains all extensions that should be treated
	 * as 'scalable'. Files with one of the specified extensions
	 * will be assigned the size 'scalable' regardless of their position
	 * within the directory structure.
	 * This is useful for multiple detail levels of one icon.
	 * @var array
	 */
	private static $SCALE_EXT = array('svg', 'xcf', 'xcf.bz2');


	/**
	 * This is the original path the icon object was created with.
	 * It is only stored for debugging reasons and internal use.
	 * @var string
	 */
	private $path;

	/**
	 * This is the real size of the icon.
	 * Either the value is 'scalable' or '16x16', ...
	 * @var string
	 */
	private $realSize;

	/**
	 * idx[0] => width
	 * idx[1] => height
	 * @var Array
	 */
	private $realSizeArr;

	/**
	 * This is the prefixed directory and also the (intended) icon size.
	 * For most icons this will be the same as realSize - except
	 * for multiple detail level scalables.
	 * @var string
	 */
	private $intendedSize;

	/**
	 * idx[0] => width
	 * idx[1] => height
	 * @var Array
	 */
	private $intendedSizeArr;

	/**
	 * The actual width of the image file for fixed size images (e.g. PNG).
	 *
	 * @var int
	 */
	private $imgWidth;


	/**
	 * The actual height of the image file for fixed size images (e.g. PNG).
	 *
	 * @var int
	 */
	private $imgHeight;

	/**
	 * This is the directory context of the icon.
	 * @var string
	 */
	private $context;

	/**
	 * The icon file's basename without extension.
	 * @var string
	 */
	private $basename;

	/**
	 * The icon file's extension.
	 * @var string
	 */
	private $extension;


	/**
	 * Reference to the icon filesystem object containing this icon.
	 *
	 * @var IconFilesystem
	 */
	private $iconFs;


	/**
	 *
	 * @param $path
	 * @return unknown_type
	 */
	function __construct($iconFs, $path) {
		$this->iconFs = $iconFs;

		$this->path = $path;
		$this->extractPathElements($path);
	}

	function __destruct() {
		//debug("Icon->__destruct();");
		$this->basename = null;
		$this->context = null;
		$this->extension = null;
		$this->imgHeight = null;
		$this->imgWidth = null;
		$this->intendedSize = null;
		$this->intendedSizeArr = null;
		$this->path = null;
		$this->realSize = null;
		$this->realSizeArr = null;
	}

	/**
	 * Remove all external references to other objects
	 * and delegate to destroy methods of subordinate
	 * objects.
	 */
	function destroy() {
		$this->iconFs = null;
	}


	private function extractImageDimensions() {
		$size = getimagesize($this->iconFs->buildPath($this));
		$this->imgWidth = $size[0];
		$this->imgHeight = $size[1];
	}

	/**
	 *
	 * @param $path
	 * @return unknown_type
	 */
	private function extractPathElements($path) {
		$this->intendedSize = substr($path, 0, strpos($path, '/'));


		/*
		 * If the extension points to a scalable file we mark it as such,
		 * for all other files the size is defined through its position in the
		 * directory structure - namely the prefix.
		 */
		$scaleExt = $this->extractScalableExtension($path);
		if ($scaleExt == '') {
			$this->extension = substr($path, strrpos($path,'.') + 1);

			$this->realSize = $this->intendedSize;
			$this->realSizeArr = explode("x", $this->realSize);

			$this->intendedSizeArr = explode("x", $this->intendedSize);
		}
		else {
			$this->extension = $scaleExt;

			$this->realSize = 'scalable';
			$this->realSizeArr = null;

			$this->intendedSizeArr = null;
		}


		$this->basename = basename($path, '.' . $this->extension);


		$this->context = substr($path, strlen($this->intendedSize) + 1, strrpos($path, '/') - strlen($this->intendedSize) - 1);
	}

	private function extractScalableExtension($path) {
		$return = '';

		foreach(Icon::$SCALE_EXT as $ext) {
			$t_end = substr($path, strlen($path) - strlen($ext));
			if ($ext == $t_end)	$return = $ext;
		}

		return $return;
	}

	/**
	 *
	 * @return string
	 * The context of the icon, e.g. actions or emblems
	 */
	function getContext() {
		return $this->context;
	}

	/**
	 *
	 * @return string
	 * The icon file's basename without extension
	 */
	function getBasename() {
		return $this->basename;
	}

	/**
	 *
	 * @return string
	 * The icon's file extension
	 */
	function getExtension() {
		return $this->extension;
	}

	/**
	 *
	 * @return string
	 *
	 */
	function getRealSize() {
		return $this->realSize;
	}

	/**
	 * Returns the icon width
	 * @return String
	 */
	function getRealWidth() {
		if (is_array($this->realSizeArr)) {
			return $this->realSizeArr[0];
		}
		else {
			return $this->realSize; // scalable
		}
	}

	/**
	 * Returns the icon height
	 * @return String
	 */
	function getRealHeight() {
		if (is_array($this->realSizeArr)) {
			return $this->realSizeArr[1];
		}
		else {
			return $this->realSize; // scalable
		}
	}

	/**
	 * Returns the icon width
	 * @return String
	 */
	function getIntendedWidth() {
		if (is_array($this->intendedSizeArr)) {
			return $this->intendedSizeArr[0];
		}
		else {
			return $this->intendedSize; // scalable
		}
	}

	/**
	 * Returns the icon height
	 * @return String
	 */
	function getIntendedHeight() {
		if (is_array($this->intendedSizeArr)) {
			return $this->intendedSizeArr[1];
		}
		else {
			return $this->intendedSize; // scalable
		}
	}

	/**
	 *
	 * @return string
	 * The size this icon was intended for according to its' placement in the
	 * directory structure, e.g. 16x16 if it is placed within the 16x16/ subdirectory
	 */
	function getIntendedSize() {
		return $this->intendedSize;
	}

	/**
	 * Returns the image width of the represented fixed size image (e.g. PNG).
	 * <b>Returns null for scalables</b>
	 * <i>The image dimensions are extracted on the first call to this function
	 * or getImageHeight(). All subsequent calls use the cached values.</i>
	 *
	 * @return int
	 */
	function getImageWidth() {
		if ($this->imgWidth == null && $this->getRealSize() != "scalable") {
			$this->extractImageDimensions();
		}
		return $this->imgWidth;
	}

	/**
	 * Returns the image height of the represented fixed size image (e.g. PNG)
	 * <b>Returns null for scalables</b>
	 * <i>The image dimensions are extracted on the first call to this function
	 * or getImageWidth(). All subsequent calls use the cached values.</i>
	 *
	 * @return int
	 */
	function getImageHeight() {
		if ($this->imgHeight == null && $this->getRealSize() != "scalable") {
			$this->extractImageDimensions();
		}
		return $this->imgHeight;
	}


	/**
	 *
	 * @return string
	 * A talking unique identifier for this icon.
	 * This does not distinguish between different scales of the same icon.
	 */
	function getUniqueID() {
		return $this->getContext() . '/' . $this->getBasename();
	}

	/**
	 *
	 * @return string
	 * The icon's full filename including the extension
	 */
	function getFilename() {
		return $this->getBasename() . '.' . $this->getExtension();
	}

	/**
	 *
	 * @return string
	 * The icon's full path including scaling size, context and filename
	 */
	function getPath() {
		return $this->getIntendedSize() . '/' . $this->getContext() . '/' . $this->getFilename();
	}

}

?>