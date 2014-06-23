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

if (!extension_loaded('php-gtk')) {
	if (!dl("php_gtk2.so")) {
	    die("Please install and load the php-gtk2 module in your php.ini\r\nFor further instructions on where to get and how to install php-gtk2 see http://www.geek-blog.de/php-gtk-und-wo-man-es-herbekommt.html");
	}
}

require_once("includes/functions.php");
require_once("classes/IconInspector.php");
require_once("gtk/handlers.php");



/*
 * Configuration defaults
 */
require_once("includes/config.php");


// command line options
handleCliOptions($argc, $argv);

// FIXME The automatic garbage collector somehow screws up php-gtk...
// disable automatic garbage collection
if (function_exists("gc_disable")) {
	gc_disable();
}

// welcome
$gtk_version = "GTK-" . $version;
printWelcomeMessage($gtk_version);

/*
 * Init glade
 */
// get glade instance
$glade = new GladeXML('gtk/ginspect.glade', 'mainWindow');

// let glade autoconnect the signals
$glade->signal_autoconnect();


/*
 * Set defaults
 */
// windows
$mainWindow = $glade->get_widget('mainWindow');

// run button
$buttonRun = $glade->get_widget('buttonRun');

// chancel button
$buttonCancel = $glade->get_widget('buttonCancel');

// auto generate
$checkAutoGenerate = $glade->get_widget('checkAutoGenerate');
$checkAutoGenerate->set_property('active', $auto_generate_missing);

// build html
$checkBuildHtml = $glade->get_widget('checkBuildHtml');
$checkBuildHtml->set_property('active', $build_html);

/*
// checkCheckImageDimensions
$checkCheckImageDimensions = $glade->get_widget('checkCheckImageDimensions');
$checkCheckImageDimensions->set_property('active', $check_imageDimensions);
*/

// checkOverviewImageGenerate
$checkOverviewImageGenerate = $glade->get_widget('checkOverviewImageGenerate');
$checkOverviewImageGenerate->set_property('active', $overview_generate);

/*
// checkAutofixMetaTitle
$checkAutofixMetaTitle = $glade->get_widget('checkAutofixMetaTitle');
$checkAutofixMetaTitle->set_property('active', $autofix_meta_title);
*/

// icon path
$textIconPath = $glade->get_widget('textIconPath');
$textIconPath->set_property('text', $dir);

$chooserIconPath = $glade->get_widget('chooserIconPath');
$chooserIconPath->set_current_folder($dir);

// base url
$textBaseUrl = $glade->get_widget('textBaseUrl');
$textBaseUrl->set_property('text', $web);

// progress bar
$progressbar = $glade->get_widget('progressbar');

// status text
$labelStatus = $glade->get_widget('labelStatus');

/*
 * Start main loop
 */
Gtk::main();



?>
