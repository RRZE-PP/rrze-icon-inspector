#!/bin/bash
#
# This is the icon inspector start script you should always use to
# run the icon inspector from the command line.  

cd `dirname $0`

php inspect.php $@ 2>errors.log
