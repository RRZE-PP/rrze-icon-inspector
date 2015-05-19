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


// This is only changed by the developer :)
$version = "0.5.6";

/*
 * CONFIGURATION
 */




/*
 * Icon set source path
 * 
 * This variable must point to the source directory of the icon set to
 * process. That is the folder containing something like the following 
 * directory structure:
 * 
 * 		tango/
 * 			scalable/
 * 				actions/
 * 				categories/
 *	 			...
 * 			16x16/
 * 				actions/
 * 				categories/
 *	 			...
 * 			32x32/
 * 				...
 * 			...
 * 
 * In the above example you would have to specifiy the full path to the tango/
 * directory like this "/full/path/to/tango".
 */

// RRZE ICON SET
$dir = '../rrze-icon-set/tango';

// TANGO ART LIBRE
//$dir = '../cvs/tango-art-libre';




/*
 * Web target path
 * 
 * This variable must point to the target web address to link to when building
 * online links for the processed icons.
 * The specified address is used as the base address. The relative paths to the
 * icons are added as found in the offline structure from the $dir variable.
 * 
 * In short: The online directory structure has to match the offline structure
 * from the $dir variable, or the generated links won't match.  
 */

// RRZE ICON SET
$web = 'https://github.com/RRZE-PP/rrze-icon-set/tree/master/tango';

// TANGO ART LIBRE
//$web = 'http://tango.freedesktop.org/static/cvs/tango-art-libre';
//$web = 'http://ftp.uni-erlangen.de/pub/rrze/tango/tango-art-libre';



/*
 * Build HTML overview
 * 
 * If set the HTML overview will be build using the templates in the
 * templates/ directory and outputted to the generated/ directory.
 */
$build_html = true;



/*
 * Auto-creation of fixed size icons
 * 
 * Uses an installed inkscape to automatically generate missing 
 * fixed-size icons.
 */
$auto_generate_missing = false;
$inkscape_executable = "inkscape";
$inkscape_executableWin = "C:/Program Files/inkscape/inkscape.exe";
$inkscape_executableMac = "/Applications/Inkscape.app/Contents/Resources/bin/inkscape";



/*
 * Preview size
 * 
 * This variable has two functions.
 * 
 * ONE: 
 * If only one scalable/detail level is available the fixed size icon with 
 * the specified size is used for the preview image in the overview table.
 * 
 * TWO:
 * If multiple detail levels are available the scalables of each detail
 * level will be located within the directory of their intended size
 * (e.g. 16x16/, 32x32/, ...) instead of the scalables/ directory. 
 * Therefore the fixed size icons located in the directory of each scalable 
 * will be used as the preview images. 
 * To eliminate problems with big images (e.g. if there's a high detail 
 * version for 256x256 and above), in this second case this variable limits the 
 * size of the preview image to the size specified using CSS.
 * 
 * In short: No preview image will be bigger than specified here.
 */
$preview_size = "32x32";
//$lightbox_size = "150x150";
$lightbox_size = "720x720";



/*
 * Check image dimensions
 * 
 * TODO documentation
 * 
 */
$check_imageDimensions = true;



/*
 * Icon overview image generation
 * 
 * TODO documentation
 * 
 */
// general settings
$overview_generate = true;
$overview_iconSize = "48x48";
$overview_maxWidth = 750;
$overview_imagePadding = 10;

// text and font settings
$overview_label = false;
$overview_fontSize = 10;
$overview_fontColor = array(0,0,0);
$overview_fontFile = "/usr/share/fonts/truetype/msttcorefonts/Verdana.ttf";
//$overview_fontFile = "/usr/share/fonts/truetype/ttf-bitstream-vera/Vera.ttf";
$overview_fontFileWin = "C:/Windows/Fonts/verdana.ttf";
$overview_fontFileMac = "/usr/X11/lib/X11/fonts/TTF/Vera.ttf";
$overview_textPadding = 5;

// bounding box with shade and rounded corners for every icon
$overview_boundingBox = false;
$overview_boundingBoxRadius = 3;
$overview_boundingBoxPadding = 5;
$overview_boundingBoxColor = array(200,200,200);
$overview_boundingBoxShadeColor = array(65,65,65);

// imagemap
$overview_imagemap_template = "overview.html";
$overview_galleryUrl = "gallery.html";



/*
 * Autofix title field in metadata
 * 
 * If activated all title fields in the SVG metadata will be set to the default value
 * composed of the icon file's basename with all '_' and '-' replaced by whitespaces.
 * If set to false, a warning will be issued in case the title field set deviates from
 * the default as described above.
 */
$autofix_meta_title = true;


/*
 * Debugging
 * 
 * Be extra verbose in the error.log file.
 */
$debug = false;



/* END CONFIGURATION */

require("includes/os_fixups.php");

?>
