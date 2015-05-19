
RRZE Icon Inspector
-------------------


0. Quick usage
--------------
After downloading the Icon Inspecor adjust the settings in the includes/config.php
file to your needs. The file is well commented and should (hopefully) be self 
explanatory.

Always start the icon inspector via the included inspect.sh script. This
will redirect all error/debug output to the errors.log file for later 
evaluation.

After the script run has completed you can open the errors.log file in your
favorite text editor to see what problems were found, if any.



1. Introduction
---------------
The Regional Computing Centre Erlangen (RRZE) provides and maintains an icon set
especially created for IT topics. The set is free to use under the popular
creative commons licence (see https://github.com/RRZE-PP/rrze-icon-set).

Out of this project arose the need for a tablerized overview of all available
icons in the set. 
The icon set is created using inkscape and its scalable SVG format. The 
metadata included in these SVG images can be easily maintained by the icon 
designers and provides an easy way to store additional information about a 
specific icon.

The RRZE Icon Inspector can extract this information directly from the base
SVG files and use it to create a verbose and clear compendium of all icons
within the set (see gallery at https://github.com/RRZE-PP/rrze-icon-set).

Additionally it also checks the icon structure for errors like missing fixed
size icons, misplaced files, possibly duplicate icons and so on.


2. Requirements
---------------
The RRZE Icon Inspector is completely written in PHP. It was developed using
Eclipse PDT.
The target operating system to run the RRZE Icon Inspector is LINUX.

Requirements are 
	php-5.2.10 and
	inkscape (only if you need auto creation of missing fixed scale icons)
compiled with (at least) the 
	cli,
	gd, 
	simplexml and 
	pcre 
flags.

Note that these requirements were pulled out of my development environment and
are not verified nor aim to be complete. 

To be clear: It may be that other PHP extensions are also needed and the script
may also run with a different (lower) version of PHP.



3. Features
-----------

3.1 Icon overview
-----------------

3.1.1 Website gallery
---------------------
In order to generate the HTML icon overview the RRZE Icon Inspector uses a
very simple template system. 
Basically the templates are stored in the templates/ directory and the output
is put in the generated/ directory.
All the assembling takes place in the rrze_icon_inspector.php file. 

The generated HTML gallery page is using the Lightbox2 
(http://www.huddletogether.com/projects/lightbox2/) script to show some fancy
high-res previews of the icons in a nice overlay.

3.1.2 Overview image
--------------------
Additionally a PNG overview image containing all icons can be created. Every
icon within the image is subtitled with its' meta title element from the SVG.

The image generation can be configured in several ways like
	- icon size
	- padding between the icons
	- padding between text and icon
	- font / font size / font color
	- shaded icon bounding box
	- maximal width of the overview image (height is automatically calculated)


3.2 Conformity checks
---------------------
Besides the overview generation the RRZE Icon Inspector also checks all gathered 
data against the RRZE Icon Set requirements and collects a list of deviations 
from these requirements within the errors.log file.

Some of the requirements currently beeing checked are
	- if all required fixed scale icons are available
	- if there are fixed scale icons without a scalable SVG
	- if all SVGs have a 'title' and 'keywords' field set in their metadata
	- if the 'title' field "correlates" with the icon filename (see 3.6 for further info)
	- if all files are correctly placed within the folder structure
	- if there are two or more icons with the same basename, which may be duplicates
	
	
3.3 Auto-creation of fixed size icons
------------------------------------- 
If you activate it in the config.php file and have Inkscape installed, the 
RRZE Icon Inspector can also auto-generate all missing fixed size icons for you.
The target format will be PNG. The auto-generation action will be logged to the
errors.log file as a note. 
	

3.4 Multiple detail levels support
----------------------------------
Usually icon creaters design their icons scaled to full screen and with much
love for details. These details may disappear or worse make the icon 
undecipherable for the user when scaled down. 
Especially with very small icon sizes like 16x16 the need for a less detailed
icon version grew, to ensure the icon's message can still be communicated to
the user.

Therefore the RRZE Icon Inspector also supports mulitple detail versions of the
same icon. To use this feature just store the scalable SVG of each detail level
within the fixed size directory of its intended minimum size instead of storing 
it in the scalable/ directory and the icon inspector will automatically
recognize it.
Additionally it is suggested to store a master/highest detail scalable in the 
scalables/ directory.

Here are some examples to clarify the above.

Single detail example:
 		tango/
 			scalable/
 				actions/
 					action-undo.svg		(<-- one scalable for all sizes)
 				categories/
	 			...
 			16x16/
 				actions/
 					action-undo.png
 				categories/
	 			...
 			32x32/
 				actions/
 					action-undo.png
 				categories/
	 			...
 			...
 

Multi detail example:
 		tango/
 			scalable/
 				actions/
 					action-undo.svg		(<-- master/highest detail scalable)
 				categories/
	 			...
 			16x16/
 				actions/
 					action-undo.svg		(<-- scalable for 16x16 and below)
 					action-undo.png
 				categories/
	 			...
 			32x32/
 				actions/
 					action-undo.svg		(<-- scalable for 32x32 and below)
 					action-undo.png
 				categories/
	 			...
 			...


3.5 Privacy protection
----------------------
Inspired by Sebastian Pipping's svgstrip script (see also "Privacy concern with 
export-filename and sodipodi:absref") the Icon Inspector removes certain attributes from 
Inkscape's SVG files which contain some information about the local system which may be 
considered a privacy concern.

The attributes beeing removed are
    - sodipodi:absref from the svg tag
    - sodipodi:docname from the svg tag
    - sodipodi:docbase from the svg tag
    - inkscape:export-filename from all tags

 
3.6 Automatic metadata generation
---------------------------------
As the first feature in the line of automatic metadata generation, the SVG's title element
can now be automatically fixed.
If activated in the config.php file the title element will be set to the SVG's filename
without extension and all '_' and '-' replaced with whitespace.

Example: account-delete.svg will get the title set to "account delete"


4. Notes
--------
The RRZE Icon Inspector has only been real-world tested with SVG as the source
scalable format and PNG as the target fixed-size format. 

However it SHOULD work with others, too. 

Currently the RRZE Icon Inspector recognises the following file extensions as
pictures. Files with deviating extensions will be filtered and NOT be processed
at all.
	'png', 
	'gif', 
	'jpg', 
	'jpeg', 
	'tif', 
	'bmp', 
	'xcf', 
	'xcf.bz2', 
	'svg'
	
Additionally files with the following extensions are considered 'scalables' and
treated as such internally.
	'svg', 
	'xcf', 
	'xcf.bz2' 

Hidden files and directories -- those prefixed with a dot '.' -- are also 
ignored.
