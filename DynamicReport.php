<?php

/* $Id: OrderSummaryReport.php 4572 2011-05-23 10:14:06Z stan$*/
$title = _('Dynamic Report');
include('includes/session.inc');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
/* Re-Load CSS and Javascript file*/
echo '<link href="' . $rootpath . '/css/custom/dynamic_reports.css" rel="stylesheet" type="text/css" media="screen" charset="utf-8" />';
echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
echo '<script type="text/javascript" src="'.$rootpath.'/javascripts/jquery-1.7.2.js"></script>';
echo '<script type="text/javascript" src="'.$rootpath.'/javascripts/startstop-slider.js"></script>';
/* End of Re-Load */

echo '	<div id="page-wrap">
	
		<div id="slider">

			<div id="mover">
		
				<div id="slide-1" class="slide">
                                <iframe seamless="seamless"  scrolling="no"  src="'.$rootpath.'/OrderSummaryReport.php?notop=1"></iframe>
				</div>
				<div class="slide">
				<iframe seamless="seamless" scrolling="no" src="'.$rootpath.'/reports/Awaiting_Invoice_Report" ></iframe>
	                        </div>
				<div class="slide">
                                <iframe seamless="seamless"  scrolling="no" src="'.$rootpath.'/reports/Open_Invoices_Report"></iframe>
				</div>
                                <div class="slide">
                                <iframe seamless="seamless" scrolling="no" src="'.$rootpath.'/reports/Credit_Hold_Report"></iframe>	
				</div>
                                <div class="slide">
                                <iframe seamless="seamless" scrolling="no" src="'.$rootpath.'/reports/Release_Stock_Report"></iframe>
				</div>
                                <div class="slide">
                                <iframe seamless="seamless" scrolling="no" src="'.$rootpath.'/reports/Dispatch_Stock_Report"></iframe>
				</div>
                                <div class="slide">
                                <iframe seamless="seamless" scrolling="no" src="'.$rootpath.'/reports/Back_Orders_Report"></iframe>	
				</div>
                                <div class="slide">
                                <iframe  seamless="seamless" scrolling="no" src="'.$rootpath.'/reports/Delivery_Issues_Report"></iframe>
				</div>
                                <div class="slide">
                                <iframe seamless="seamless" scrolling="no" src="'.$rootpath.'/DailySalesInquiry.php?notop=1"></iframe>
				</div>
                                                        
			
			</div>
		
		</div>
		
	</div>';

?>