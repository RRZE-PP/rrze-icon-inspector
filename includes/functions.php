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

define('ESC', 27);
$PADDING = 70;


function error($msg) {
	fwrite(STDERR, $msg . "\n");
}

function message($msg) {
	fwrite(STDOUT, $msg . "\n");
}

function errorInternal($msg) {
	error("INTERNAL ERROR: " . $msg);
}

function warning($icon,$msg) {
	global $PADDING;
	error(str_pad("WARNING:        [" . $icon->getUniqueID() . "]", $PADDING)  . $msg);
}

function notice($icon,$msg) {
	global $PADDING;
	error(str_pad("NOTICE:         [" . $icon->getUniqueID() . "]", $PADDING)  . $msg);
}

function debug($msg) {
	global $debug;
	if ($debug) {
		message("DEBUG: " . $msg);
	}
}

function status($msg) {
	fwrite(STDOUT, $msg);
}

function runShellCommand($cmd) {
	//$shellOutput = shell_exec($cmd . " > /dev/null; echo $?;");
	$shellOutput = shell_exec($cmd);
	$exitCode = trim($shellOutput);
	return $exitCode;
}

/**
 * Save cursor position
 */
function saveCursorPosition() {

	printf( "%c7", ESC );

}

/**
 * Print the given string value at the previously saved position.
 * @param String $string
 *
 */
function printAtSavedCursorPosition($string) {
	printf("%c8%s", ESC, $string);
}

/**
 *
 * @param $directory
 * @param $extension
 * @param $strip_extension
 * @param $short
 * @return unknown_type
 */
function getFiles($directory, $extension = "") {
	$prefix_len = strlen($directory);

	if (!is_dir($directory)) {
		error("Cannot find directory '{$directory}'.");
		die("Cannot find directory '{$directory}'.\n");
	}

	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {

			// skip hidden files and directories
			if ($file[0] == '.')
			continue;

			// skip CVS directories
			if ($file == 'CVS')
			continue;

			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
					$array_items = array_merge($array_items, getFiles($directory. "/" . $file, $extension));
				}
				else {
					$ext = substr($file,strrpos($file, '.'));
					if(!$extension || ( ".".$extension == $ext))
					{
						$array_items[] = $directory . '/' . $file;
					}
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
}


/**
 *
 * @param $directory
 * @return unknown_type
 */
function getDirectories($directory) {

	if (!is_dir($directory)) {
		error("Cannot find directory '{$directory}'.");
		die("Cannot find directory '{$directory}'.\n");
	}

	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {

			// skip hidden files and directories
			if ($file[0] == '.')
			continue;

			// skip CVS directories
			if ($file == 'CVS')
			continue;

			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
					$array_items[] = $file;
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
}


function handleCliOptions($argc, $argv) {
	if ($argc > 1) {
		for($i = 1; $i < $argc; $i++) {
			$parts = explode('=',$argv[$i]);
			$switch = $parts[0];
			if (isset($parts[1])) $value = $parts[1];

			switch ($switch) {
				case '--auto-generate-missing':
				case '-g':
					global $auto_generate_missing;
					$auto_generate_missing = true;
					break;

				case '--icon-dir':
				case '-d':
					global $dir;
					$dir = $value;
					break;

				case '--web-url':
				case '-u':
					global $web;
					$web = $value;
					break;

				case '--no-html':
				case '-n':
					global $build_html;
					$build_html = false;
					break;

				case '--no-check-image-dimensions':
				case '-i':
					global $check_imageDimensions;
					$check_imageDimensions = false;
					break;

				case '--no-overview-generate':
				case '-o':
					global $overview_generate;
					$overview_generate = false;
					break;

				case '--no-autofix-meta-title':
				case '-t':
					global $autofix_meta_title;
					$autofix_meta_title = false;
					break;

				case '--verbose':
				case '-v':
					global $debug;
					$debug = true;
					break;

				case '--help':
				case '-h':
					//status("Usage: php {$argv[0]} [OPTIONS]\n\n");
					status("Usage: sh inspect.sh [OPTIONS]\n\n");
					status("-h\t--help\t\t\t\tDisplay this help page\n");
					status("-g\t--auto-generate-missing\t\tUse Inkscape to generate missing icon sizes\n");
					status("-d=DIR\t--icon-dir=DIR\t\t\tThe directory of the icon set to inspect\n");
					status("-u=URL\t--web-url=URL\t\t\tThe base web URL for icon links\n");
					status("-n\t--no-html\t\t\tDo not generate a HTML overview\n");
					status("-i\t--no-check-image-dimensions\tDo not check for quadratic image dimensions\n");
					status("-o\t--no-overview-generate\t\tDo not generate an overview image\n");
					status("-t\t--no-autofix-meta-title\t\tDo not automatically set the title SVG meta field\n");
					status("-v\t--verbose\t\t\tEnable debugging output\n");
					exit(0);
					break;

				default:
					// do nothing
					die("Unknown command line option '$switch'.");
			}
		}
	}


	// some autoconfiguration stuff
	global $preview_size;
	global $css_widthLimit, $css_heightLimit;
	$sizeLimit = explode('x',$preview_size);
	$css_widthLimit = $sizeLimit[0];
	$css_heightLimit = $sizeLimit[1];
}

function printWelcomeMessage($version) {
	global $debug;

	$msg = "The RRZE Icon Inspector " . "(" . $version . ")";
	status($msg . "\n");
	status(str_pad("",strlen($msg), "-")."\n");


	// status messages
	if ($debug) {
		global $dir;
		global $web;
		global $build_html;
		global $auto_generate_missing, $inkscape_executable;
		global $check_imageDimensions;
		global $overview_generate;
		global $autofix_meta_title;

		status("icon-dir = {$dir}\n");
		status("web-url = {$web}\n");
		status("build-html = " . ($build_html?'on':'off') . "\n");
		status("auto-generate-missing = " . ($auto_generate_missing?'on ('.$inkscape_executable.')':'off') . "\n");
		status("check-image-dimensions = " . ($check_imageDimensions?'on':'off') . "\n");
		status("overview-generate = " . ($overview_generate?'on':'off') . "\n");
		status("auto-fix-meta-title = " . ($autofix_meta_title?'on':'off') . "\n");
	}
}

?>