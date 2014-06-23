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

require_once("classes/Icon.php");
require_once("classes/IconContainer.php");

/**
 * Abstraction for the base directory containing the icon files.
 *
 * @author unrza249
 *
 */
class IconFilesystem {

	/**
	 * This is used for filtering the files that should be managed by the
	 * IconFilesystem class.
	 * @var array
	 */
	private static $PICTURE_EXT = array('png', 'gif', 'jpg', 'jpeg', 'tif', 'bmp', 'xcf', 'xcf.bz2', 'svg');

	/**
	 * The root directory of this icon filesystem object
	 * @var string
	 */
	private $root;

	/**
	 * List of all icon container objects in this filesystem
	 * @var Icon[]
	 */
	private $iconContainers;

	/**
	 * A complete list of all read files with full path
	 *
	 * @var array
	 */
	private $filelist;


	/**
	 * This is determined in runtime based on the available subdirectories, e.g. 16x16, 32x32, ...
	 *
	 * @var array
	 */
	private $sizelist;


	/**
	 *
	 * @param $root
	 * @return unknown_type
	 */
	function __construct($root) {
		$this->root = $root;
		$this->filelist = array();

		$this->initFileStructure();
		$this->initDirStructure();

		$this->sort();
		status("OK\n");
	}

	function __destruct() {
		debug("IconFilesystem->__destruct();");
		$this->root = null;
		$this->filelist = null;
		$this->sizelist = null;
		$this->iconContainers = null;
	}

	/**
	 * Remove all external references to other objects
	 * and delegate to destroy methods of subordinate
	 * objects.
	 */

	function destroy() {
		foreach($this->iconContainers as $container) $container->destroy();
	}

	/**
	 *
	 * @return unknown_type
	 */
	private function initFileStructure() {
		// get a complete list of all files with full path
		$this->filelist = getFiles($this->root);

		// compile a list of Icon objects
		$this->iconContainers = array();
		foreach ($this->filelist as $path_to_file) {
			// filter extensions
			if (!$this->allowedExtension($path_to_file)) continue;

			// cut off the root path prefixing each and every icon
			$path = substr($path_to_file, strlen($this->root) + 1);

			// check for misplaced files
			if (!preg_match("/^(([1-9][0-9]+x[1-9][0-9]+)|(scalable))(\/[^\/]+)+(\/[^\.]+\..+)$/", $path)) {
				error("Found possibly misplaced file '{$path}'.");
				continue;
			}

			// ok, create the icon object
			$t_icon = new Icon($this, $path);


			// add the icon to the icon container
			if (!isset($this->iconContainers[$t_icon->getUniqueID()])) {
				$this->iconContainers[$t_icon->getUniqueID()] = new IconContainer($this);
			}
			$this->iconContainers[$t_icon->getUniqueID()]->add($t_icon);
		}
	}

	private function initDirStructure() {
		$this->sizelist = getDirectories($this->getRoot());
		natsort($this->sizelist);
	}


	private function allowedExtension($path) {
		// only accept pictures
		$allowed = false;

		// this is faster and gets most cases
		$ext = substr($path, strrpos($path,'.')+1);
		if (in_array($ext, IconFilesystem::$PICTURE_EXT)) $allowed = true;

		// this is more accurate, but slower
		if (!$allowed) {
			foreach(IconFilesystem::$PICTURE_EXT as $ext) {
				$t_end = substr($path, strlen($path) - strlen($ext));
				if ($ext == $t_end)	$allowed = true;
			}
		}
			
		if (!$allowed) {
			$shortPath = substr($path, strlen($this->getRoot()) + 1);
		}

		return $allowed;
	}

	private function sortIcons() {
		$temp = array();
		$orderedIcons = array();

		foreach($this->iconContainers as $key => $iconContainer) {
			$temp[$key] = $iconContainer->getBasename();
		}
		asort($temp);

		$orderedKeys = array_keys($temp);
		foreach($orderedKeys as $key) {
			$orderedIcons[$key] = $this->iconContainers[$key];
		}

		$this->iconContainers = $orderedIcons;
	}

	/**
	 * Sort the icons alphabethically based on their filenames and the sizes via natsort.
	 */
	function sort() {
		$this->sortIcons();

		foreach ($this->getIconContainers() as $iconContainer) {
			$iconContainer->sortSizes();
		}
	}

	function getRoot() {
		return $this->root;
	}


	function buildPath($icon) {
		return $this->getRoot() . '/' . $icon->getPath();
	}


	function getIconContainers() {
		return $this->iconContainers;
	}

	function getIconContainer($iconID) {
		if (isset($this->iconContainers[$iconID])) {
			return $this->iconContainers[$iconID];
		}
		else {
			error("No icon found in array with ID '{$iconID}'.");
			return array();
		}
	}

	function getIconIDs() {
		return array_keys($this->iconContainers);
	}

	function getSizeList() {
		return $this->sizelist;
	}

}

?>