<?php
// FreightLabelDetails.php
//  
// Sample command line application to generate freight label details using downloaded JSON Reference Files

// Sample command line invocation (Windows):
// C:\Temp> c:\xampp\PHP\php.exe FreightLabelDetails.php

// Requires the following files to be in the same directory:
//		eServices.php
//		WSSecurity.php
//		CustomerConnect.php
//      	Downloaded JSON reference files

// If no production PHP server is available, PHP can be installed on a local PC

//     Star Track Express
//     26 May 2011
//     Version 4.3

  require_once("eServices.php");					// Import the Star Track Express PHP API - do not modify this file
  require_once("CustomerConnect.php");				// Import ConnectDetails class - customer to modify this class as required

// Get receiver details

echo "\nFreightLabelDetails -- Command-Line Application -- \n";

echo "\nReceiver Postcode: ";						// For example 2000
$postcode = trim(fgets(STDIN)); 
echo "\nService Code: ";							// for example EXP
$serviceCode = trim(fgets(STDIN)); 

// Get depot details
$o = new STEeService;
$nearestDepotCode = $o->nearestDepot($postcode);
$nearestDepotDescription = $o->locationDescription($nearestDepotCode);

echo "\nNearest depot to receiver is $nearestDepotDescription ($nearestDepotCode) \n";

// Get destination sortation code

$sortationCode = $o->destinationSortationCode($postcode, $serviceCode);

echo "\nDestination Sortation code is $sortationCode \n";

?>
