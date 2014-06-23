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

function sensitiveOnRun() {
	global $buttonCancel;
	$buttonCancel->set_property('sensitive', true);

	global $buttonRun;
	$buttonRun->set_property('sensitive', false);

	global $checkAutoGenerate;
	$checkAutoGenerate->set_property('sensitive', false);

	global $checkBuildHtml;
	$checkBuildHtml->set_property('sensitive', false);

	global $textIconPath;
	$textIconPath->set_property('sensitive', false);

	global $chooserIconPath;
	$chooserIconPath->set_property('sensitive', false);

	global $textBaseUrl;
	$textBaseUrl->set_property('sensitive', false);

	/*
	global $checkCheckImageDimensions;
	$checkCheckImageDimensions->set_property('sensitive', false);
	*/
	
	global $checkOverviewImageGenerate;
	$checkOverviewImageGenerate->set_property('sensitive', false);

	/*
	global $checkAutofixMetaTitle;
	$checkAutofixMetaTitle->set_property('sensitive', false);
	*/
}

function sensitiveAfterRun() {
	global $buttonCancel;
	$buttonCancel->set_property('sensitive', false);

	global $buttonRun;
	$buttonRun->set_property('sensitive', true);

	global $checkAutoGenerate;
	$checkAutoGenerate->set_property('sensitive', true);

	global $checkBuildHtml;
	$checkBuildHtml->set_property('sensitive', true);

	global $textIconPath;
	$textIconPath->set_property('sensitive', true);

	global $chooserIconPath;
	$chooserIconPath->set_property('sensitive', true);

	global $textBaseUrl;
	$textBaseUrl->set_property('sensitive', true);

	/*
	global $checkCheckImageDimensions;
	$checkCheckImageDimensions->set_property('sensitive', true);
	*/
	
	global $checkOverviewImageGenerate;
	$checkOverviewImageGenerate->set_property('sensitive', true);
	
	/*
	global $checkAutofixMetaTitle;
	$checkAutofixMetaTitle->set_property('sensitive', true);
	*/
}

function updateProgressBar($text, $frac) {
	global $progressbar, $labelStatus;
	$progressbar->set_fraction($frac);
	$labelStatus->set_text($text);
	while (Gtk::events_pending()) {Gtk::main_iteration();}
}

?>