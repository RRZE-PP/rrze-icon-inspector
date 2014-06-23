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

if (!class_exists('SimpleXMLElement')) {
	die('Please recompile PHP with the simplexml extension enabled.');
}

require_once("classes/IconFilesystem.php");
require_once("classes/Template.php");
require_once("classes/SvgReader.php");
require_once("classes/LabledIconImage.php");
require_once("classes/OverviewImage.php");
require_once("classes/HtmlGallery.php");
require_once("classes/HtmlImageMap.php");

require_once("gtk/functions.php");

/**
 *
 * The IconInspector main class
 * @author unrza249
 *
 */
class IconInspector {

	/**
	 *
	 * @var IconFilesystem
	 */
	private $iconFs;

	/**
	 *
	 * @param $iconFs
	 * @return unknown_type
	 */
	function __construct($iconFs) {
		status("\nIconInspector\n-------------\n");
		status("Current mem: " . memory_get_usage(true)/1000 . " Kb\n");
		status("Peak mem: " . memory_get_peak_usage(true)/1000 . " Kb\n");
		status("Limit: " . ((ini_get("memory_limit") == "-1")?"none":(ini_get("memory_limit")/1000) . "Kb") . "\n");

		$this->iconFs = $iconFs;
	}

	function __destruct() {
		debug("IconInspector->__destruct();");
		$this->iconFs = null;
	}

	/**
	 * Remove all external references to other objects
	 * and delegate to destroy methods of subordinate
	 * objects.
	 */
	function destroy() {
		$this->iconFs->destroy();
	}


	/**
	 *
	 * @param $iconDir
	 * @return IconInspector
	 */
	static function newFromIconDir($iconDir, $gtk = false) {
		status("Reading icon filesystem...");
		if ($gtk) {
			updateProgressBar("Reading icon filesystem...", 0);
		}
		$insp = new IconInspector(new IconFilesystem($iconDir));
		return $insp;
	}
	
	/**
	 *
	 * @param $iconDir
	 * @return IconInspector
	 */
	static function newFromIconDir_gtk($iconDir) {
		return IconInspector::newFromIconDir($iconDir, true);
	}

	/**
	 * The main processing loop
	 *
	 * @param $gtk
	 * 		Set to true if running from within the GTK loop
	 */
	function process($gtk = false) {

		/*
		 * Flags
		 */
		global $cancelProcessing;

		/*
		 * Options
		 */
		global $dir;
		global $web;
		global $preview_size;

		/*
		 * Configuration switches
		 */
		global $build_html;
		global $auto_generate_missing;
		global $overview_generate;





		/*
		 * I. ICON CONTAINER LOOP
		 */
		status("Processing icon data...\n");
		saveCursorPosition();

		$iconContainers = $this->iconFs->getIconContainers();
		$numIcons = count($iconContainers);
		$i = 0;
		foreach($iconContainers as $iconID => $iconContainer) {

			/*
			 * 1. LOGISTICS
			 */
			// progress-meter
			$frac = (++$i/$numIcons);
			$progress = $frac * 100;
			printAtSavedCursorPosition(sprintf("%.0f%s [%s]                                   ", $progress, '%', $iconID));

			// GTK stuff
			if ($gtk) {
				updateProgressBar("Processing..." . $iconID, $frac);
			}

			// check if chancel was requested
			if ($cancelProcessing) {
				$cancelProcessing = false;
				status("Chancel operation requested...\n");

				// some cleaning up
				unset($htmlGallery);
				unset($overviewImage);

				return;
			}


			/*
			 * 2. DATA PREPARATION (only once per icon!)
			 */
			$scalableIcons = $this->processScalableIcons($iconContainer);
			$previewIcons = $this->processPreviewIcons($iconContainer, $preview_size);
			$fixedIcons = $this->processFixedIcons($iconContainer, $auto_generate_missing);

			$metadata = $this->processMetadata($iconContainer, $scalableIcons);


			/*
			 * 3. CHECKS
			 */
			$this->doGenericChecks($iconContainer);
				
				
				
			/*
			 * 4. FUNCTIONS
			 */

			// 4a. BUILD HTML (if $build_html enabled)
			if ($build_html) {
				if (!isset($htmlGallery)) {
					$htmlGallery = new HtmlGallery();
				}
				$htmlGallery->processRow($iconContainer, $metadata, $previewIcons, $scalableIcons, $fixedIcons);
			}

			// 4b. GENERATE OVERVIEW IMAGE (if $overview_generate enabled)
			if ($overview_generate) {
				if (!isset($overviewImage)) {
					$overviewImage = new OverviewImage();
				}
				$imageResource = $overviewImage->generateAndAdd($iconContainer, $metadata);
				
				if (!isset($htmlImageMap)) {
					$htmlImageMap = new HtmlImageMap();
				}
				$htmlImageMap->add($imageResource, $metadata['title']);
			}


		} // end IconContainer loop
		status("\nOK");


		/*
		 * II. FINISH OPERATIONS
		 */

		// 4aa. BUILD HTML (if $build_html enabled)
		if ($build_html) {
			$htmlGallery->output();
			$htmlGallery->destroy();
			$htmlGallery = null;
		}


		// 4bb. GENERATE OVERVIEW IMAGE (if $overview_generate enabled)
		if ($overview_generate) {
			$overviewImage->writeToPNG();
			
			$htmlImageMap->generate($overviewImage);
			$htmlImageMap->output();

			$htmlImageMap->destroy();
			$htmlImageMap = null;
			
			$overviewImage->destroy();
			$overviewImage = null;
		}

	}


	function process_gtk() {
		$this->process(true);
	}


	/**
	 * Compiles an array of easily accessible meta fields for the given icon.
	 * If available the data is retrieved from an SVG and as far as the respective
	 * options are enabled false metadata fields in the SVG are fixed and the private
	 * data is removed.
	 *
	 * @param IconContainer $cntr
	 * @param Array $scalableIcons
	 * @return array Array with some easily accessible meta fields for the given icon
	 */
	public function processMetadata($cntr, $scalableIcons) {
		$t_title = $t_desc = $t_keywords = $titlefiedBasename = '';

		foreach ($scalableIcons as $icon) {
			// extract data from the filename as fallback/reference
			$titlefiedBasename = str_replace(array('-','_'),  ' ', $cntr->getBasename());

			// if one of the scalables is an SVG, we can extract metadata
			if ($icon->getExtension() == 'svg') {


				/*
				 * 1. Read SVG
				 */
				$svg = new SvgReader($this->iconFs->buildPath($icon), $icon);


				/*
				 * 2. Apply functions
				 */

				// filter private data contained in the XML
				$svg->removePrivateData();

				// check and fix (if enabled) emtpy title fields
				if ($svg->getMetaTitle() == '') {
					global $autofix_meta_title;
					if ($autofix_meta_title) {
						notice($icon, "Fixed empty 'title' field to be '" . $titlefiedBasename . "'.");
						$svg->setMetaTitle($titlefiedBasename);
					}
					else {
						warning($icon, "Empty 'title' field.");
					}

				}
				else {
					if ($titlefiedBasename != $svg->getMetaTitle()) {
						global $autofix_meta_title;
						if ($autofix_meta_title) {
							notice($icon, "Fixed differing 'title' field to be '" . $titlefiedBasename . "'.");
							$svg->setMetaTitle($titlefiedBasename);
						}
						else {
							warning($icon, "Differing 'title' field in file '{$icon->getPath()}': '{$svg->getMetaTitle()}'");
						}
					}
				}

				// check and fix (if enabled) emtpy meta keywords fields
				if ($svg->getMetaKeywords() == '') {
					warning($icon, "Empty 'keywords' field.");
				}



				/*
				 * 3. Gather metadata for later
				 */

				if ($svg->getMetaTitle() != '') {
					if ($t_title == '') {
						$t_title =  $svg->getMetaTitle();
					}
				}

				if ($svg->getMetaDesc() != '') {
					if ($t_desc == '') {
						$t_desc = $svg->getMetaDesc();
					}
				}

				if ($svg->getMetaKeywords() != '') {
					if ($t_keywords == '') {
						$t_keywords = $svg->getMetaKeywords();
					}
				}



				/*
				 * 4. Write changed XML to disk if any changes were made
				 */
				$svg->writeIfDirty();
			}
		}

		/*
		 * Prepare and return the metadata array for further use
		 * by other modules
		 */

		// fallback
		if ($t_title == '')	$t_title = $titlefiedBasename;
		if ($t_desc == '') $t_desc = $cntr->getContext();
		if ($t_keywords == '') $t_keywords = $titlefiedBasename;

		// build the result array
		$meta = array(
			'title' 	=> $t_title,
			'desc'		=> $t_desc,
			'keywords' 	=> $t_keywords,
		);

		return $meta;
	}


	public function processPreviewIcons($iconContainer, $previewSize) {
		$previewIcons = array();

		// single scalable located within the 'scalable' directory
		$fixed = $iconContainer->getIconRealSize($previewSize);
		count($fixed)?$previewIcons[$previewSize] = $fixed[0]:''; // only add if fixed not empty

		return $previewIcons;
	}


	function processScalableIcons($iconContainer) {
		$scalableIcons = $iconContainer->getIconRealSize('scalable');
		return $scalableIcons;
	}


	function processFixedIcons($iconContainer, $autogenerateMissing = false) {
		global $inkscape_executable;
		$fixedIcons = array();

		// find out about missing sizes
		$missingSizes = array_diff($this->iconFs->getSizeList(), $iconContainer->getAvailableRealSizes());
		$availableSizes = $iconContainer->getAvailableRealSizes();
		if (count($missingSizes)) {
			// try to auto-generate the missing sizes
			if (!$autogenerateMissing || in_array('scalable', $missingSizes)) {
				warning($iconContainer, "Available sizes: " . implode(', ', $availableSizes) . " -- Missing sizes: " . implode(', ', $missingSizes));
			}
			else {
				// ok, work the magic...
				foreach($missingSizes as $missingSize) {
					$scale = explode('x', $missingSize);
					$target = $this->iconFs->getRoot() . '/' . $missingSize . '/' . $iconContainer->getUniqueID() . '.png';

					$scalable = $iconContainer->getMatchingScalable($missingSize);
					if ($scalable->getExtension() != 'svg') {
						warning($iconContainer, "Missing sizes: " . implode(', ', $missingSizes));
						continue;
					}

					debug("Trying to auto-generate missing size: " . $missingSize);
					$source = $this->iconFs->buildPath($scalable);
					$cmd = $inkscape_executable . " --without-gui --export-area-drawing --export-width=".$scale[0]." --export-height=".$scale[1]." --export-png=".$target." {$source} 2>inkscape.log";
					debug("Executing: {$cmd}");
					$exitCode = runShellCommand($cmd);
					if ($exitCode != 0) {
						error("Failed to auto-generate missing size: " . $missingSize);
					}
					else {
						notice($iconContainer, "Auto-generated missing size: " . $missingSize);
					}
				}
			}
		}
		/*
		 foreach($iconContainer->getAvailableRealSizes() as $realSize) {
			if ($realSize != 'scalable') {
			$temp = $iconContainer->getIconRealSize($realSize);
			$fixedIcons[] = $temp[0];
			}
			}
			*/
		$fixedIcons = $iconContainer->getFixedSizeIcons();
		return $fixedIcons;
	}


	/**
	 * Does some checks, applicable to all icon container objects.
	 *
	 * @param IconContainer $iconContainer
	 * The icon container object to be checked
	 */
	function doGenericChecks($iconContainer) {
		/*
		 * Check if a master/highest detail scalable exists
		 */
		$scalables = $iconContainer->getScalableIcons();
		$found_master_scalable = false;
		foreach($scalables as $scalable) {
			$intendedSize = $scalable->getIntendedSize();
			if ($intendedSize == 'scalable') {
				$found_master_scalable = true;
			}
		}
		if (!$found_master_scalable) {
			warning($iconContainer, "Did not find a master/highest detail scalable.");
		}



		/*
		 * Check for quadratic image dimensions
		 */
		global $check_imageDimensions;
		if ($check_imageDimensions) {
			$nonScalables = $iconContainer->getFixedSizeIcons();
			foreach ($nonScalables as $nonScalable) {
				if ($nonScalable->getImageHeight() != $nonScalable->getImageWidth()) {
					warning($nonScalable, "Non quadratic image dimensions in file '".$nonScalable->getPath()."': " . $nonScalable->getImageWidth() . "x" . $nonScalable->getImageHeight());
				}
			}
		}



		/*
		 * Check for possibly duplicate icons
		 */
		$iconContainers = $this->iconFs->getIconContainers();
		foreach($iconContainers as $cntr) {
			if ($iconContainer === $cntr) break;
			if ($iconContainer->getBasename() == $cntr->getBasename()) {
				warning($iconContainer, "Possibly a duplicate of '" . $cntr->getUniqueID() . "'.");
			}
		}


		// check if metadata matches across detail levels
		// TODO

	}


}

?>
