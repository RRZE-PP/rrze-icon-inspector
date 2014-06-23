#!/bin/bash
#
# Convenience script matching only for my environment!
# Don't use this. But if you want you can cook up your own script
# based on this one.

cd `dirname $0`

#
# RRZE Icon Set
#

# Check and generate and copy to webdir
#sh inspect.sh --verbose --auto-generate-missing --icon-dir=../../../svn/rrze-icon-set/trunk/tango && cp generated/gallery.css ../../../svn/rrze-icon-set/trunk/website/css/ && cp generated/gallery.html ../../../svn/rrze-icon-set/trunk/website/ && cat errors.log

# Check
#sh inspect.sh --verbose --icon-dir=../../../svn/rrze-icon-set/trunk/tango && cat errors.log
#sh ginspect.sh --verbose --icon-dir=../../../svn/rrze-icon-set/trunk/tango && cat errors.log

# Check only
#sh inspect.sh --verbose --no-html --icon-dir=../../..//svn/rrze-icon-set/trunk/tango/ && cat errors.log
#sh xinspect.sh --verbose --no-html --icon-dir=../../../svn/rrze-icon-set/trunk/tango
sh ginspect.sh --verbose --icon-dir=../../../svn/rrze-icon-set/tango



#
# Tango Art Libre
#

#sh ginspect.sh --verbose --no-html --icon-dir=../../cvs/tango-art-libre 
#sh ginspect.sh --no-html --icon-dir=../../cvs/tango-art-libre 

