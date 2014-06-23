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
 * 
 * Enter description here ...
 * @author unrza249
 *
 */
class HtmlGallery {

	/*
	 * Template objects
	 */
	private $T_targetPage;
	private $T_targetPageCSS;
	private $T_table;
	private $T_table_row;

	/*
	 * Row data
	 */
	private $preview;
	private $scalable;
	private $fixed;
	private $highresPath;
	private $title;
	private $desc;
	private $keywords;

	/*
	 * Temporary variables
	 */
	private $lastLetter;
	private $localCount;

	/**
	 * Default constructor
	 */
	function __construct() {

                global $dir;
                $tplPath = basename($dir);

		/*
		 * Load templates for HTML output
		 */
		//debug("Loading templates for '".$tplPath."'...");
		$this->T_targetPage = Template::newFromName($tplPath . "/gallery.html");
		$this->T_targetPageCSS = Template::newFromName($tplPath . "/gallery.css");
		$this->T_table = Template::newFromName($tplPath . "/Table.html");
		$this->T_table_row = Template::newFromName($tplPath . "/TableRow.html");


		/*
		 * Build CSS
		 */
		global $css_widthLimit,  $css_heightLimit;
		$this->T_targetPageCSS->insert('max_width', $css_widthLimit);
		$this->T_targetPageCSS->insert('max_height', $css_heightLimit);

		/*
		 * Init variables
		 */
		$this->preview = "NA";
		$this->scalable = "NA";
		$this->fixed = "NA";
		$this->highresPath = "NA";

		$this->lastLetter = '';
		$this->localCount = 0;
	}

	function __destruct() {
		debug("HtmlGallery->__destruct();");
		$this->desc = null;
		$this->fixed = null;
		$this->highresPath = null;
		$this->keywords = null;
		$this->lastLetter = null;
		$this->localCount = null;
		$this->preview = null;
		$this->scalable = null;
		$this->lastLetter = null;
		$this->localCount = null;
		$this->title = null;
		
		$this->T_table = null;
		$this->T_table_row = null;
		$this->T_targetPage = null;
		$this->T_targetPageCSS = null;
	}
	
	function destroy() {
		$this->T_table->destroy();
		$this->T_table_row->destroy();
		$this->T_targetPage->destroy();
		$this->T_targetPageCSS->destroy();
	}
	
	
	/**
	 * Load metadata
	 * @param Array $metadata
	 */
	private function loadMetadata($metadata) {
		//debug("Loading metadata...");
		$this->title = Template::escape($metadata['title']);
		$this->desc = Template::escape($metadata['desc']);
		$this->keywords = Template::escape($metadata['keywords']);
	}

	/**
	 * Build preview html
	 * @param Array $previewIcons
	 */
	private function buildPreview($previewIcons) {
		//debug("Building preview...");
		global $web;

		$t_preview = "NA";
		$t_preview_arr = array();
		foreach ($previewIcons as $previewIcon) {
			$t_preview_arr[] = 	"<img src=\"".$web.'/'.$previewIcon->getPath()."\" alt=\"".$previewIcon->getRealSize()."\" title=\"".$previewIcon->getRealSize()."\" />";
			//$t_preview_arr[] = 	"<img width=\"".split('x',$previewIcon->getRealSize())[0]."\" height=\"".split('x',$previewIcon->getRealSize())[1]."\" class=\"lazy\" src=\"../images/blank.gif\" data-src=\"".$web.'/'.$previewIcon->getPath()."\" alt=\"".$previewIcon->getRealSize()."\" title=\"".$previewIcon->getRealSize()."\" />";
		}
		$t_preview = implode("\n", $t_preview_arr);

		$this->preview = $t_preview;
	}

	/**
	 * Build scalable html
	 * @param Array $scalableIcons
	 */
	private function buildScalable($scalableIcons) {
		//debug("Building scalable...");
		global $web;

		$t_scalable = "NA";
		$t_scalable_arr = array();
		foreach($scalableIcons as $scalable) {
			$t_scalable_arr[] = "<a href=\"".$web.'/'.$scalable->getPath()."\">".$scalable->getIntendedSize()."&nbsp;(".$scalable->getExtension().")</a>";
		}
		$t_scalable = implode("<br/>\n", $t_scalable_arr);

		$this->scalable = $t_scalable;
	}

	/**
	 * Build fixed size html
	 * @param Array $fixedIcons
	 */
	private function buildFixedSizeLinks($fixedIcons) {
		//debug("Building fixed...");
		global $web, $lightbox_size;

		$t_fixed = "NA";
		$t_highres_path = "NA";
		$t_fixed_arr = array();
		foreach($fixedIcons as $fixed) {
			$t_fixed_title = $this->title . " (" . $fixed->getRealSize() . ")";
			$t_fixed_arr[] = "<a href=\"".$web.'/'.$fixed->getPath()."\">".$fixed->getRealSize()."&nbsp;(".$fixed->getExtension().")</a>";
			if ($fixed->getRealSize() == $lightbox_size) {
				$t_highres_path = $web.'/'.$fixed->getPath();
			}
		}
		$t_fixed = implode("<br/>\n", $t_fixed_arr);

		$this->fixed = $t_fixed;
		$this->highresPath = $t_highres_path;
	}


	/**
	 * Render a complete row in the gallery table with
	 * the data given so far.
	 * @param IconContainer $iconContainer
	 */
	private function renderRow($iconContainer) {
		//debug("Rendering row...");
		$this->T_table_row->insert('high_res', $this->highresPath);
		$this->T_table_row->insert('preview', $this->preview);
		$this->T_table_row->insert('scalable', $this->scalable);
		$this->T_table_row->insert('fixed', $this->fixed);
		$this->T_table_row->insert('title', $this->title);
		$this->T_table_row->insert('description', Template::htmlLinks($this->desc, 'nowrap'));
		$this->T_table_row->insert('keywords', $this->keywords);

		// alphabetical anchors
		$currentLetter = strtoupper(substr($iconContainer->getBasename(),0,1));
		if ($this->lastLetter != $currentLetter && $this->lastLetter != '') {
			// we got a new letter, do necessary stuff
			$this->localCount = 0;
			$this->T_targetPage->insertMore('anchor_links', '<a href="#'.$this->lastLetter.'">'.$this->lastLetter.'</a>&nbsp;');
			$this->T_table->insert('anchor_name', $this->lastLetter);

			$this->T_table->insert('table_rows', '');
			$this->T_targetPage->insertMore('gallery_table', $this->T_table->getHTML());
			$this->T_table->reset();
		}
		$this->lastLetter = $currentLetter;

		// effects (style stuff)
		$this->T_table_row->insert('even_odd', ((++$this->localCount)%2 == 0)?'even':'odd');

		// per-icon anchor
		$this->T_table_row->insert('icon_anchor', str_replace(' ', '-', $this->title));


		// add new row to table
		$this->T_table->insertMore('table_rows', $this->T_table_row->getHTML());
		$this->T_table_row->reset();
	}

	/**
	 * Finialize the gallery table
	 */
	private function finalize() {
		//debug("Finalizing...");
		$this->T_targetPage->insert('anchor_links', '<a href="#'.$this->lastLetter.'">'.strtoupper($this->lastLetter).'</a>');
		$this->T_table->insert('anchor_name', $this->lastLetter);
		$this->T_table->insert('table_rows', '');
		$this->T_targetPage->insert('gallery_table', $this->T_table->getHTML());
		$this->T_targetPage->insert('GENERATION_DATE', date("d.m.Y"));
	}

	/**
	 * Write the generated gallery html and css to the generated/ 
	 * directory.
	 */
	private function writeToFile() {
		status("Writing HTML to target file...");
		$this->T_targetPage->toFile();
		$this->T_targetPageCSS->toFile();
		status("OK\n");
	}

	/**
	 * Build a complete row in the gallery table with the data specified.
	 *  
	 * @param IconContainer $iconContainer
	 * @param Array $metadata
	 * @param Array $previewIcons
	 * @param Array $scalableIcons
	 * @param Array $fixedIcons
	 */
	public function processRow($iconContainer, $metadata, $previewIcons, $scalableIcons, $fixedIcons) {
		// prepare
		$this->loadMetadata($metadata);
		$this->buildPreview($previewIcons);
		$this->buildScalable($scalableIcons);
		$this->buildFixedSizeLinks($fixedIcons);

		// render
		$this->renderRow($iconContainer);
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
