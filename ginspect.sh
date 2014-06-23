#!/bin/bash
#
# This is the start script for the gnome icon inspector using
# the php-gtk extension.

cd `dirname $0`

php ginspect.php $@ 2>errors.log
