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
class Template {

	private $name;
	private $filename;
	
	private $html_in;
	private $html_out;

	
	private $autoHtmlLinks = false;

	
	function __construct() {
	}
	
	function __destruct() {
		//debug("Template->__destruct();");
		$this->name = null;
		$this->filename = null;
		$this->html_in = null;
		$this->html_out = null;
	}
	
	function destroy() {
	}

	static function newFromFile($path) {
		$tpl = new Template();
		$tpl->filename = substr($path, strrpos($path, '/', $path) + 1);
		$tpl->name = basename($path, substr($path, 0, strrpos($path,'.')-1));
		$tpl->html_in = file_get_contents($path);
		$tpl->html_out = $tpl->html_in;
		return $tpl;
	}

	static function newFromName($name) {
		$tpl = new Template();
		$tpl->filename = $name;
		$tpl->name = $name;
		$tpl->html_in = file_get_contents('templates/' . $name);
		$tpl->html_out = $tpl->html_in;
		return $tpl;
	}
	
	function reset() {
		$this->html_out = $this->html_in;
	}

	function insert($variable, $value) {
		if ($this->autoHtmlLinks) {
			$value = Template::htmlLinks($value);
		}
		
		$variable = "__" . strtoupper($variable) . "__";
		$this->html_out = str_replace($variable, $value, $this->html_out);
	}

	static function htmlLinks($string, $class="") {
		$search = "/([a-zA-Z]+:\/\/[a-zA-Z0-9?&%.;:\/=+_\-]+\/([a-zA-Z0-9._\-]+))/";
		if ($class == '') {
			$replace = "<a href=\"$1\">$2</a>";
		}
		else {
			$replace = "<a class=\"".$class."\" href=\"$1\">$2</a>";
		}
		return preg_replace($search, $replace, $string);
	}
	
	static function escape($string) {
		return htmlspecialchars($string);
	}
	
	function insertMore($variable, $value) {
		$this->insert($variable, $value . "__" . strtoupper($variable) . "__");
	}

	function setAutoHtmlLinks($flag) {
		$this->autoHtmlLinks = $flag;
	}


	function getHTML() {
		return $this->html_out;
	}

	function __toString() {
		return $this->getHTML();
	}

	function toFile($filename = "") {
		if ($filename == "") {
			$filename = $this->filename;
		}
		file_put_contents('generated/' . $filename, $this->getHTML());
	}

}

?>