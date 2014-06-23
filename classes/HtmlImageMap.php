<?php
/*
 * RRZE Icon Inspector, developed as a part of the RRZE icon set.
 * Copyright 2011, RRZE, and individual contributors
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
 *
 * Enter description here ...
 * @author unrza249
 *
 */
class HtmlImageMap {

	/*
	 * Source image definition
	 */
	private $overviewImage;

	private $imageLinks;
	
	/*
	 * Template objects
	 */
	private $T_targetPage;

	/*
	 * Generated area defintions
	 */
	private $areaDefs;

	/**
	 * Default constructor
	 */
	function __construct() {
                global $dir;
                $tplPath = basename($dir);

		/*
		 * Load templates for HTML output
		 */
		//debug("Loading templates...");
		global $overview_imagemap_template;
		$this->T_targetPage = Template::newFromName($tplPath."/".$overview_imagemap_template);
		$this->imageLinks = array();
	}

	function __destruct() {
		debug("HtmlGallery->__destruct();");
		$this->T_targetPage = null;
	}

	function destroy() {
		$this->T_targetPage->destroy();
	}

	
	public function add($icon, $link) {
		$this->imageLinks[$icon] = $link;
	}

	public function generate($bigPicture) {
		
		/*
		 * <area shape="rect" coords="9,372,66,397" href="http://en.wikipedia.org/" />
		 */
		foreach ($bigPicture->getSourceImages() as $srcImage) {
			$coords = $bigPicture->getImageCoords($srcImage);
			
			global $overview_galleryUrl;
			$url = $overview_galleryUrl . "#".str_replace(' ', '-', $this->imageLinks[$srcImage]);
			
			$this->areaDefs .= "<area shape=\"rect\" coords=\"{$coords[0]},{$coords[1]},{$coords[2]},{$coords[3]}\" href=\"{$url}\" title=\"{$this->imageLinks[$srcImage]}\"/>\n";
		}

	}


	/**
	 * Finialize the gallery table
	 */
	private function finalize() {
		//debug("Finalizing...");
		$this->T_targetPage->insert('AREA_DEFINITIONS', $this->areaDefs);
		$this->T_targetPage->insert('GENERATION_DATE', date("d.m.Y"));
	}

	/**
	 * Write the generated gallery html and css to the generated/
	 * directory.
	 */
	private function writeToFile() {
		status("Writing HTML to target file...");
		$this->T_targetPage->toFile();
		status("OK\n");
	}

	/**
	 * Output the gallery to the generated/ directory
	 */
	public function output() {
		$this->finalize();
		$this->writeToFile();
	}
}


?>
