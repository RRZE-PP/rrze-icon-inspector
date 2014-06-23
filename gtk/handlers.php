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

require_once("gtk/functions.php");


function on_mainWindow_destroy() {
	Gtk::main_quit();
}


function on_checkAutoGenerate_toggled() {
	global $auto_generate_missing;
	global $checkAutoGenerate;
	$auto_generate_missing = $checkAutoGenerate->get_property('active');
	debug("auto-generate-missing = " . ($auto_generate_missing?'on':'off'));
}

function on_checkBuildHtml_toggled() {
	global $build_html;
	global $checkBuildHtml;
	$build_html = $checkBuildHtml->get_property('active');
	debug("html = ". ($build_html?'on':'off'));
}
/*
function on_checkCheckImageDimensions_toggled() {
	global $checkCheckImageDimensions;
	global $check_imageDimensions;
	$check_imageDimensions = $checkCheckImageDimensions->get_property('active');
	debug("check-image-dimensions = " . ($check_imageDimensions?'on':'off'));
}
*/
function on_checkOverviewImageGenerate_toggled() {
	global $checkOverviewImageGenerate;
	global $overview_generate;
	$overview_generate = $checkOverviewImageGenerate->get_property('active');
	debug("overview-generate = " . ($overview_generate?'on':'off'));
}

/*
function on_checkAutofixMetaTitle_toggled() {
	global $checkAutofixMetaTitle;
	global $autofix_meta_title;
	$autofix_meta_title = $checkAutofixMetaTitle->get_property('active');
	debug("auto-fix-meta-title = " . ($autofix_meta_title?'on':'off'));
}
*/

function on_textIconPath_focus_out_event() {
	global $dir;
	global $textIconPath;
	$dir = $textIconPath->get_property('text');
	debug("dir = " . $dir);
}

function on_chooserIconPath_current_folder_changed() {
	global $dir, $chooserIconPath;
	$dir = $chooserIconPath->get_current_folder();
	debug("dir = " . $dir);

	global $textIconPath;
	$textIconPath->set_property('text', $dir);
}


function on_textBaseUrl_focus_out_event() {
	global $web;
	global $textBaseUrl;
	$web = $textBaseUrl->get_property('text');
	debug("web-url = " . $web);
}



function on_buttonCancel_clicked() {
	global $cancelProcessing;
	$cancelProcessing = true;
	debug("CHANCEL PRESSED!");
}

function on_buttonRun_clicked() {
	debug("RUN PRESSED!");

	global $dir;
	global $labelStatus;

	sensitiveOnRun();

	// seperator
	fwrite(STDERR, "==============================================================\n");
	fwrite(STDERR, "   " . date("d.m.Y - H:i:s") . "\n");
	fwrite(STDERR, "==============================================================\n");
	
	/*
	$labelStatus->set_text('Clearing log...');
	debug('Clearing errors.log.');
	$fp = fopen('errors.log', 'w');
	fclose($fp);
	*/
	
	$labelStatus->set_text('Processing...');

	$inspector = IconInspector::newFromIconDir_gtk($dir);
	$inspector->process_gtk();
	$inspector->destroy(); // trigger total destruction
	$inspector = null; // free mem
	
	status("\n");

	sensitiveAfterRun();

	// display log messages, if any
	$text = file_get_contents('errors.log');
	if ($text != '') {
		$labelStatus->set_text('See errors.log file.');

		// load the logWindow
		$layout_logWindow = new GladeXML('gtk/ginspect.glade', 'logWindow');
		$logWindow = $layout_logWindow->get_widget('logWindow');
		$textviewLog = $layout_logWindow->get_widget('textviewLog');

		// fill the textView
		$buffer = new GtkTextBuffer();
		$textviewLog->set_buffer($buffer);
		$buffer->set_text($text);

		// show the window
		$logWindow->set_visible(true);
	}
	else {
		$labelStatus->set_text('No errors.');
	}
}





?>