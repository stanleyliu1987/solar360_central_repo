<?php
// ReferenceDataDownloadFromCommandLine.php
//  
// Downloads JSON Reference Data from command line
// See also ReferenceDataDownloadWeb.php

// Sample command line invocation (Windows):
//      C:\Temp> c:\xampp\PHP\php.exe ReferenceDataDownloadFromCommandLine.php

// Requires the following files to be in the current directory:
//		ReferenceDataDownloadFromCommandLine.php
//		eServices.php
//		WSSecurity.php
//		CustomerConnect.php

// If no PHP server is available, PHP can be installed on a local PC for daily download purposes

//	 Customer can edit $files to remove definitions of unneeded files

//     Star Track Express
//     25 May 2011
//     Version 4.3

  require_once("eServices.php");					// Import the Star Track Express PHP API - do not modify this file
  require_once("CustomerConnect.php");				// Import ConnectDetails class - customer to modify this class as required
  
// ****** SELECT DATA TO BE DOWNLOADED: MODIFY AS REQUIRED  ******
//                                      ------------------

define("GET_DEPOTS", true);				// Depots
define("GET_LOCATIONS", true);			// Locations and Nearest Depots
define("GET_QCCODES", true);			// QC Codes
define("GET_SERVICECODES", true);		// Service Codes
define("GET_FASTSERVICECODES", true);	// Fast Service Codes

// ******                   ******                          ******


echo "\nThe following files have been written : \n\n";

// Define the files to be created and their construction details

$files = array(	"Depots.json" =>								// Filename
						array( 	"operation" => "getDepots",		// eServices operation
					  			"name" => "depot",				// Top-level XML element in response
								"keyvalue" => "depotCode",		// Second -level XML element (key)
								"data0" => "depotName",			// Second-level XML element (value)
								"data1" => "",
								"data2" => "",
								"body" => "",					// SOAP Request XML (following common header)
								"enabled" => GET_DEPOTS			// Create this file? 
					 		 ),
				"Locations.json" =>
						array( 	"operation" => "getLocations",
					  			"name" => "location",
								"keyvalue" => "suburb",
								"data0" => "postCode",
								"data1" => "state",
								"data2" => "nearestDepotCode",
								"body" => 
											array("locationDetails" => 
												  						array("locationStandard" => "TEAM")
												  ),
								"enabled" => GET_LOCATIONS
					 		 ),

					"QCCodes.json" =>
						array( 	"operation" => "getQCCodes",
					  			"name" => "qcCodes",
								"keyvalue" => "qcCode",
								"data0" => "qcDescription",
								"data1" => "",
								"data2" => "",
								"body" => "",
								"enabled" => GET_QCCODES
					 		 ),
					"ServiceCodes.json" =>
						array( 	"operation" => "getServiceCodes",
					  			"name" => "codes",
								"keyvalue" => "serviceCode",
								"data0" => "serviceDescription",
								"data1" => "",
								"data2" => "",
								"body" => "",
								"enabled" => GET_SERVICECODES
					 		 ),
					"FastServiceCodes.json" =>
						array( 	"operation" => "getServiceCodes",
					  			"name" => "codes",
								"keyvalue" => "serviceCode",
								"data0" => "fastServiceCode",
								"data1" => "",
								"data2" => "",
								"body" => "",
								"enabled" => GET_FASTSERVICECODES
					 		 )
				);				

// Get the parameters required for a connection to eServices
$oConnect = new ConnectDetails();
$connection = $oConnect->getConnectDetails();

//Create the request, as per Request Schema described in eServices - Usage Guide.xls

$parameters = array(
					'header' => array(
										'source' => 'TEAM',
										'userAccessKey' => $connection['userAccessKey']
									 )

				   );

// Iterate through the files

foreach ($files as $fileName => $spec)
{
	if ($spec['enabled'])								// If user wants this file
	{
		$body = $spec['body'];
		if ($body != "")								// If non-null SOAP body, append it to header
		{
			$parameters = array_merge($parameters, $body);
		}
		$request = array('parameters' => $parameters);
		$fileSpec = $destinationDirectory . $fileName;
		$fileHandle = fopen($fileSpec, 'w') or die("Can't open file $fileSpec for writing");
		$success = writeData($fileHandle, $destinationDirectory, $fileName, $connection, $request, $spec); 	// Write the file contents 
		if ($success)
		{
			echo $fileName . "\n";
		}
		fclose($fileHandle) or die("Can't close file $fileSpec after writing");
	}
}

	

echo "\nDownload Completed.\n\nReminder: Files may now have to be copied to where they are needed.\n";


function writeData($fileHandle, $destinationDirectory, $fileName ,$connection, $request, $spec)
{
// Writes the contents of a file, retrieving the data via eServices
// Input parameters:
//    $fileHandle = file to be written to
//	  $destinationDirectory = directory to be written into
//	  $fileName = name of file to write
//    $connection = eServices connection details
//	  $request = As required by eServices
//    $spec = array containing operation, name, keyvalue and data
// Output parameter:
//    $success = true if no error

	$fileSpec = $destinationDirectory . $fileName;

	// Invoke Star Track Express eServices

	try
	{
		$oC = new STEeService();
	//	$oC = new startrackexpress\eservices\STEeService();	// *** If PHP V5.3 or later, uncomment this line and remove the line above ***
		$response = $oC->invokeWebService($connection, $spec['operation'], $request);	// $response is as per Response Schema
																						// described in eServices - Usage Guide.xls.
																						// Returned value is a stdClass object.
																						// Faults to be handled as appropriate.
	}
	catch (SoapFault $e)
	{
		echo "\n" . $e->detail->fault->fs_severity ."\n";	
		echo $e->faultstring . "\n";
		echo $e->detail->fault->fs_message . "\n";
		echo $e->detail->fault->fs_category . "\n";
		echo $e->faultcode . "\n\n";
		exit($e->detail->fault->fs_timestamp);
	//	Or if there is a higher caller:    throw new SoapFault($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);	
	}
	// Set $items to array of top-level elements, for example 'depot' or 'location'
	eval("\$items = \$response->" . $spec['name'] . ";");
	
	// Step through top-level elements, extracting key and value(s)
	$pairList = array();
	foreach($items as $item)
	{
		eval("\$keyValue = \$item->" . $spec['keyvalue'] . ";");
		eval("\$data0 = \$item->" . $spec['data0'] . ";");
		

		if ($spec['data2'] == "")	// If only one value is associated (usual case)
		{
			$pairList += array($keyValue => $data0);
		}
		else	// Locations.json is the only case
		{		
			eval("\$data1 = \$item->" . $spec['data1'] . ";");			// State
			eval("\$data2 = \$item->" . $spec['data2'] . ";");			// Postcode
			$data1 = $oC->stateAbbreviation($data1);					// Convert State code (e.g. 2) to State abbreviation (e.g. NSW)
			$pairList += array($keyValue => array(
												  $data0,				// Postcode
												  $data1,				// State
												  $data2				// Depot nearest to postcode
												  )
							   );
		}
	}
	// Sort the array	
	ksort($pairList);
	
	// If Locations, special treatment is required to avoid a second web service call.
	// Two files must be written, Locations.json and NearestDepot.json
	
	if ($fileName == 'Locations.json')
	{
		$pairList = extractNearestDepot($pairList, $destinationDirectory);	// Write NearestDepot.json and
																			// remove nearest depots from $pairList for compactness
	}

// Write in JSON format
	fwrite($fileHandle, json_encode($pairList)) or die("Can't write file $fileSpec");
	return true;
}

function extractNearestDepot($pairList, $destinationDirectory)
// Writes NearestDepot.json and then
// removes nearest depots from $pairList for compactness
{
	// Create array of postcodes to nearest depot, with duplicates
	$nearestDepotDuplicates = array();
	foreach ($pairList as $suburb => $params)
	{
		$nearestDepotDuplicates += array($params[0] => $params[2]);		// Append  (postcode => nearest depot) 
	}
	ksort($nearestDepotDuplicates);
	
	// Remove duplicates
	$nearestDepots = array();
	$previousPostCode = "";
	foreach ($nearestDepotDuplicates as $postCode => $depot)
	{
		if ($postCode != $previousPostCode)
		{
			$nearestDepots += array($postCode => $depot);
		}
	}

	// Write NearestDepots.json
	$fName = "NearestDepots.json";
	$fSpec = $destinationDirectory . $fName;
	$fHandle = fopen($fSpec, 'w') or die("Can't open file $fSpec for writing");
	fwrite($fHandle, json_encode($nearestDepots)) or die("Can't write file $fSpec");
	fclose($fHandle) or die("Can't close file $fSpec after writing");
	echo  $fName . "\n";
	
	// Strip nearest depots from $pairList
	$pList = array();
	foreach ($pairList as $suburb => $params)
	{
		$pList += array(
					   $suburb => array($params[0], $params[1])					// Append (Post Code, State
					   );
	}
	return $pList;
}

?>
