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

/**
 * Wrapper class containing a set of icon objects that represent different
 * versions/scales of the same icon.
 *
 * @author unrza249
 *
 */
class IconContainer {


	/**
	 * A unique identifier for this icon assembled from
	 * the icon context and the basename without extension.
	 * @var string
	 */
	private $identifier;

	/**
	 * The file basename of all files belonging to the icons
	 * stored in this container.
	 * @var string
	 */
	private $basename;

	/**
	 *
	 * @var string
	 */
	private $context;

	/**
	 * A two-dimensional array of all icon objects that are a member
	 * of this collection.
	 * The icons are grouped by size.
	 *
	 * @var array[][]
	 */
	private $icons;

	/**
	 * A one-dimensional array of all fixed size icons in this container.
	 *
	 * @var array[]
	 */
	private $fixedSizeIcons;

	/**
	 * A one-dimensional array of all scalable size icons in this container.
	 *
	 * @var array[]
	 */
	private $scalableIcons;

	/**
	 * Reference to the icon filesystem object containing this icon container.
	 *
	 * @var IconFilesystem
	 */
	private $iconFs;

	function __construct($iconFs) {
		$this->iconFs = $iconFs;

		$this->icons = array();
		$this->scalableIcons = array();
		$this->fixedSizeIcons = array();
	}

	function __destruct() {
		//debug("IconContainer->__destruct();");
		$this->icons = null;
		$this->scalableIcons = null;
		$this->fixedSizeIcons = null;
	}

	/**
	 * Remove all external references to other objects
	 * and delegate to destroy methods of subordinate
	 * objects.
	 */

	function destroy() {
		$this->iconFs = null;

		foreach ($this->icons as $iconSize) {
			foreach ($iconSize as $icon) $icon->destroy();
		}
		foreach ($this->scalableIcons as $icon) $icon->destroy();
		foreach ($this->fixedSizeIcons as $icon) $icon->destroy();
	}

	/**
	 * Sort the stored icon sizes via natsort.
	 */
	function sortSizes() {
		$orderedIcons = array();

		$orderedKeys = array_keys($this->icons);
		natsort($orderedKeys);

		foreach($orderedKeys as $key) {
			$orderedIcons[$key] = $this->icons[$key];
		}

		$this->icons = $orderedIcons;
	}


	function add($icon) {
		// set/check this containers unique icon identifier
		if ($this->identifier == '') {
			$this->identifier = $icon->getUniqueID();
		}
		else {
			// sanity checks
			if ($this->identifier != $icon->getUniqueID()) {
				error("Identifier mismatch when trying to add an icon to '{$this->identifier}'.");
				exit(50);
			}
		}

		// add the icon
		$realSize = $icon->getRealSize();
		if (!isset($this->icons[$realSize])) {
			$this->icons[$realSize] = array();
		}

		// sanity check
		foreach($this->icons[$realSize] as $test_icon) {
			if ($test_icon->getIntendedSize() == $icon->getIntendedSize()) {
				errorInternal("Tried to add '{$icon->getUniqueID()}', but icon '{$test_icon->getUniqueID()}' is already present. Skipping.");
				return;
			}
		}

		$this->basename = $icon->getBasename();
		$this->context = $icon->getContext();

		$this->icons[$realSize][] = $icon;
		if ($realSize == "scalable") {
			$this->scalableIcons[] = $icon;
		}
		else {
			$this->fixedSizeIcons[] = $icon;
		}
	}



	/**
	 * Find a scalable best suited for the requested size string. This implementation returns the
	 * next bigger scalable icon object.
	 *
	 * @param string $size
	 * The requested icon size string, e.g. "16x16"
	 * @return Icon
	 * The icon object of the best matching scalable for the requested size
	 */
	function getMatchingScalable($size) {
		$sizeArr = explode('x', $size);
		$returnSizeArr = array(PHP_INT_MAX, PHP_INT_MAX);
		$return = NULL;

		$scalables = $this->getIconRealSize('scalable');

		if (count($scalables) > 1) {
			// so we got a multi-detail scalable
			foreach($scalables as $scalable) {
				$intendedSize = $scalable->getIntendedSize();
				if ($intendedSize == 'scalable') $intendedSize = PHP_INT_MAX."x".PHP_INT_MAX; // Hack for highest detail scalable
					
				$intendedSizeArr = explode('x', $intendedSize);

				if (($sizeArr[0] <= $intendedSizeArr[0]) && ($returnSizeArr[0] >= $intendedSizeArr[0])) {
					$returnSizeArr = $intendedSizeArr;
					$return = $scalable;
				}
			}
		}
		else {
			// single-detail scalable
			$return = $scalables[0];
		}

		return $return;
	}


	/**
	 * For 'scalable' size an array with more than one element may be returned.
	 * For all fixed sizes one can assume that the returned array always has
	 * only one element.
	 * @param $size
	 * @return array
	 */
	function getIconRealSize($size) {
		if ($this->hasIconRealSize($size)) {
			return $this->icons[$size];
		}
		else {
			return array();
		}
	}

	function hasIconRealSize($size) {
		if (isset($this->icons[$size])) {
			return true;
		}
		else {
			return false;
		}
	}

	function getAvailableRealSizes() {
		return array_keys($this->icons);
	}


	function getScalableIcons() {
		// same as return $this->getIconRealSize('scalable');
		return $this->scalableIcons;

	}

	function getFixedSizeIcons() {
		return $this->fixedSizeIcons;
	}

	function getUniqueID() {
		return $this->identifier;
	}

	function getBasename() {
		return $this->basename;
	}

	function getContext() {
		return $this->context;
	}

	function getMasterScalable() {
		foreach($this->scalableIcons as $scalable) {
			if ($scalable->getRealSize() == $scalable->getIntendedSize()) {
				return $scalable;
			}
		}
		errorInternal("No master scalable found.");
		return null;
	}

	function getSlaveScalables() {
		$slaveScalables = array();
		foreach($this->scalableIcons as $scalable) {
			if ($scalable->getRealSize() != $scalable->getIntendedSize()) {
				$slaveScalables[] = $scalable;
			}
		}
		return $slaveScalables;
	}


}

?>