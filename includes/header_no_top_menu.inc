<?php
/* $Id: header.inc 4565 2011-05-13 10:50:42Z daintree $ */

	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $rootpath
	//  $title - should be defined in the page this file is included with

	$StrictXHTML=False;

	if (!headers_sent()){
		if ($StrictXHTML) {
			header('Content-type: application/xhtml+xml; charset=utf-8');
		} else {
			header('Content-type: text/html; charset=utf-8');
		}
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath.'/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath.'/favicon.ico" />';
	if ($StrictXHTML) {
		echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
	} else {
		echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
	}
	echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
        echo '<link href="' . $rootpath . '/css/custom/default.css" rel="stylesheet" type="text/css" />';
        echo '<link href="' . $rootpath . '/css/sorttable/tablesortstyle.css" rel="stylesheet" type="text/css" />';
        echo '<link href="' . $rootpath . '/css/custom/dynamic_reports.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />';

       // echo '<link rel="stylesheet" href="' . $rootpath . '/wysiwyg/docs/style.css" type="text/css">';
        echo '<script type="text/javascript" src="'.$rootpath.'/javascripts/jquery-1.7.2.js"></script>';
        echo '<script type="text/javascript" src="'.$rootpath.'/javascripts/startstop-slider.js"></script>';

        echo '<script type="text/javascript" src="'.$rootpath.'/javascripts/jquery.tablesorter.js"></script>';
        echo '<script type="text/javascript" src="'.$rootpath.'/javascripts/jquery.tablesorter.pager.js"></script>';
                
        echo '<script type="text/javascript" src = "'.$rootpath.'/javascripts/MiscFunctions.js"></script>';
        echo '<script type="text/javascript" src = "'.$rootpath.'/javascripts/MiscFunctions_JQ.js"></script>';  
	echo '<script type="text/javascript" src="'.$rootpath.'/wysiwyg/scripts/wysiwyg.js"></script>
	      <script type="text/javascript" src="'.$rootpath.'/wysiwyg/scripts/wysiwyg-settings.js"></script>
	      <script type="text/javascript">
	      WYSIWYG.attach("EmailMessage", full); </script>';
        
	echo '<script type="text/javascript">
	     $(function() { 
		  $("#SortableTable")
			.tablesorter({widthFixed: true, widgets: ["zebra"]})
			.tablesorterPager({container: $("#pager")});
	                });
	      </script>';
        
	echo '</head>';

	echo '<body>';


?>