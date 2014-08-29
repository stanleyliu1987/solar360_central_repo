<?php
/* $Id: header.inc 4565 2011-05-13 10:50:42Z daintree $ */

	// Titles and screen header
	//  $rootpath
	//  $title - should be defined in the page this file is included with
               clearBrowserCache();
	
		header('Content-type: text/html; charset=utf-8');
	        $meta='<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
	
function clearBrowserCache() {
    header("Pragma: no-cache");
    header("Cache: no-cache");
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
}
	
?>

        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

	<html xmlns="http://www.w3.org/1999/xhtml"><head><title>Online Order Track</title>
	<link rel="shortcut icon" href="<?php echo $rootpath;?>/image/favicon.ico" />
	<link rel="icon" href="<?php echo $rootpath;?>/image/favicon.ico" />
        <?php echo $meta;?>
        <link href="<?php echo $rootpath;?>/css/default_OT.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="<?php echo $rootpath;?>/javascripts/jquery-1.7.2.js"></script>
	<script type="text/javascript" src = "<?php echo $rootpath;?>/javascripts/MiscFunctions_OT.js"></script>
        <script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-21466662-2']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

         </script>
	</head>
   <body>
