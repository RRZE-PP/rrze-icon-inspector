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
 * Helper class for dealing with SVG XML stuff.
 *
 * @author unrza249
 *
 */
class SvgReader {

	var $icon;
	var $filename;

	var $xml;
	var $xml_metadata;
	//var $xml_metaTitle;

	var $arr_metadata;

	var $dirty;

	function __construct($filename, $icon) {
		$this->filename = $filename;
		$this->icon = $icon;
		$this->dirty = false;

		$this->xml = simplexml_load_file($filename);
		//$this->xml['xmlns'] = '';

		// Metadata
		$this->initMetadataXML();
		$this->buildMetadataArray();

		//var_dump($this->arr_metadata);exit;
	}


	private function initMetadataXML() {
		foreach ($this->xml->children() as $child) {

			$name = $child->getName();

			// get the metadata simpleXML element
			if ($name == 'metadata') {
				$this->xml_metadata = $child;
				continue;
			}

		}
	}

	private function buildMetadataArray() {
		$this->arr_metadata = $this->r_walkXml($this->xml_metadata);

		/*
		 if ($this->icon->getUniqueID() == "emblems/affiliation") {
			print_r($this->arr_metadata);
			}
			*/
	}

	private function r_walkXml($xmlElement) {
		$return = array();

		// get available namespace list
		$namespaces = $xmlElement->getDocNamespaces();

		// walk all namespaces
		foreach ($namespaces as $key => $ns) {

			/*
			 * VALUES
			 */

			// add non-empty values
			$t_value = trim((string)$xmlElement);
			if ($t_value != '')
			$return["value"] = $t_value;

			/*
			 * ATTRIBUTES
			 */

			// get all attributes that are a member of the current namespace
			$attributes = $xmlElement->attributes($ns);
			foreach ($attributes as $key => $value) {
				$return[$key] = (string)$value;
			}


			/*
			 * CHILD-ELEMENTS
			 */

			// walk all elements that are a member of the current namespace
			$children = $xmlElement->children($ns);
			foreach ($children as $nsXmlElement) {

				$t_name = $nsXmlElement->getName();

				/*
				 *  save some locations
				 */
				if ($xmlElement->getName() == "Work" && $t_name == 'title') {
					$this->xml_metaTitle = $nsXmlElement;
				}

				$t_result = $this->r_walkXml($nsXmlElement);
				$this->safeSetSubelement($return, $t_name, $t_result);
			}
		}

		return $return;
	}



	private function safeSetSubelement(&$array, $key, $value) {
		if (!isset($array[$key])) {
			$array[$key] = $value;
		}
		else {
			if (!isset($array[$key][0])) {
				$t_temp = $array[$key];
				$array[$key] = array();
				$array[$key][] = $t_temp;
			}
			$array[$key][] = $value;
		}
	}












	function getMetadataArray() {
		return $this->arr_metadata;
	}

	function getMetaTitle() {
		if (isset($this->arr_metadata['RDF']['Work']['title']['value'])) {
			return $this->arr_metadata['RDF']['Work']['title']['value'];
		}
		else {
			return "";
		}
	}

	function getMetaDesc() {
		if (isset($this->arr_metadata['RDF']['Work']['description']['value'])) {
			return $this->arr_metadata['RDF']['Work']['description']['value'];
		}
		else {
			return "";
		}
	}

	function getMetaKeywords() {
		$t_key_arr = array();

		$ns = $this->xml->getDocNamespaces();
		$this->xml->registerXPathNamespace('dc', $ns['dc']);
		$this->xml->registerXPathNamespace('cc', $ns['cc']);
		$this->xml->registerXPathNamespace('rdf', $ns['rdf']);
		$this->xml->registerXPathNamespace('null', $ns['']);
		$elementArr = $this->xml->xpath("//null:metadata/rdf:RDF/cc:Work/dc:subject/rdf:Bag/rdf:li");
		if (is_array($elementArr)) {
			foreach ($elementArr as $element) {
				$t_key_arr[] = $element[0];
			}
		}

			

		return implode(', ', $t_key_arr);
	}

	function removePrivateData() {
		$ns = $this->xml->getDocNamespaces();

		/*
		 * Attributes in the SVG root tag
		 * Search and remove: all attributes named 'sodipodi:absref', 'sodipodi:docbase', 'sodipodi:docname' in the root tag named 'svg'
		 */
		$attributes = $this->xml->attributes($ns['sodipodi']);

		if (isset($attributes['absref'])) {
			unset($attributes['absref']);
			notice($this->icon, "Removed attribute 'sodipodi:absref'.");
			$this->dirty = true;
		}

		if (isset($attributes['docbase'])) {
			unset($attributes['docbase']);
			notice($this->icon, "Removed attribute 'sodipodi:docbase'.");
			$this->dirty = true;
		}

		if (isset($attributes['docname'])) {
			unset($attributes['docname']);
			notice($this->icon, "Removed attribute 'sodipodi:docname'.");
			$this->dirty = true;
		}


		/*
		 * Attributes in some arbitrary tag
		 * Search and remove: all attributes named 'inkscape:export-filename' in any tags
		 */

		$this->xml->registerXPathNamespace('inkscape', $ns['inkscape']);
		$elementArr = $this->xml->xpath("//*[@inkscape:export-filename]");

		if (is_array($elementArr)) {
			foreach ($elementArr as $element) {
				$attributes = $element->attributes($ns['inkscape']);
				unset($attributes['export-filename']);
				notice($this->icon, "Removed attribute 'inkscape:export-filename'.");
				$this->dirty = true;
			}
		}
	}

	function setMetaTitle($title) {

		//$this->arr_metadata['RDF']['Work']['title']['value'] = $title;

		$titleElement = $this->xml_metaTitle;
		//print_r($titleElement);
		$titleElement[0] = $title;

		$this->dirty = true;

	}


	function writeIfDirty($filename = "") {
		if ($this->dirty === true) {
			if ($filename == "") {
				$filename = $this->filename;
			}

			$this->xml->asXML($filename);
		}
	}

}

?>