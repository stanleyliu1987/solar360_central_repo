<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php
//     <document_root>/MyWebSite/EnquireConsignmentReferences.php
//     Invoked by ConsignmentReferences.php
//     Star Track Express
//     25 May 2011
//     Version 4.3

  require_once("eServices.php");					// Import the Star Track Express PHP API - do not modify this file
  require_once("CustomerConnect.php");				// Import ConnectDetails class - customer to modify this class as required
?>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Enquire Consignment References</title>
</head>

<body>
<h2>Selected Response Items</h2>

<?php

// Get the parameters required for a connection to eServices
$oConnect = new ConnectDetails();
$connection = $oConnect->getConnectDetails();

//Create the request, as per Request Schema described in eServices - Usage Guide.xls

$parameters = array(
					'header' => array(
										'source' => 'TEAM',
										'accountNo' => $_POST['accountNo'],
										'userAccessKey' => $connection['userAccessKey']
									 ),	
                    'consignmentId' => explode(" ", $_POST['consignmentId'])	// Allow for multiple consignments
				);

// NOTE: Applications *must* validate all parameters passed (data type, mandatory fields non-null), as described in Readme.pdf,
//       or alerts are generated at Star Track Express. Validation is omitted in this sample for reasons of clarity.

$request = array('parameters' => $parameters);

// Invoke Star Track Express eServices

try
{
	$oC = new STEeService();
//	$oC = new startrackexpress\eservices\STEeService();	// *** If PHP V5.3 or later, uncomment this line and remove the line above ***

$response = $oC->invokeWebService($connection,'getConsignmentDetails', $request);		// $response is as per Response Schema
																						// described in eServices - Usage Guide.xls.
																						// Returned value is a stdClass object.
																						// Faults to be handled as appropriate.
}
catch (SoapFault $e)
{
	echo "<p>" . $e->detail->fault->fs_severity . "<br />";	
	echo $e->faultstring . "<br />";
	echo $e->detail->fault->fs_message . "<br />";
	echo $e->detail->fault->fs_category . "<br />";
	echo $e->faultcode . "<br />" . "</p>";
	exit($e->detail->fault->fs_timestamp);
	//	Or if there is a higher caller:    throw new SoapFault($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);	
}

$consignments = $response->consignment;
$consignmentCount = count($consignments);

// Loop through consignments
for ($i = 0; $i < $consignmentCount; $i++)
{
	$consignment = $consignments[$i];		// The i-th consignment	

	// Retrieve items of interest from the response
	
	// Consignment ID:
	$consignmentId[$i] = $consignment->id;
	// Service Description:
	$serviceDescription[$i] = $oC->serviceDescription($consignment->serviceCode);
	// Despatch Date:
	$despatchDate[$i] = $consignment->despatchDate;
	// ETA Date:
	$etaDate[$i] = $consignment->etaDate;
	// Despatch Depot:
	$despatchDepotDescription[$i] = $oC->locationDescription($consignment->despatchDepot);
	// Delivery Depot:
	$deliveryDepotDescription[$i] = $oC->locationDescription($consignment->deliveryDepot);
	// Consignment Status:
	$consignmentStatus = $consignment->status;
	$consignmentStatusDescription[$i] = $oC->statusDescription($consignmentStatus, 'consignment', 'full');
	// Most Recently Seen Place and Time:
	$trackingEvents = $consignment->trackingEvents;		// Tracking events
	$trackingEvent = $trackingEvents[0];  				// Most recent tracking event
	if (!is_null($trackingEvent))
	{
		$scanningDepotDescription[$i] = $oC->locationDescription($trackingEvent->scanningDepot); 
		$mostRecentScanDateTime[$i] = $trackingEvent->eventDateTime;
	}
	else
	{
		$scanningDepotDescription[$i] = ""; 
		$mostRecentScanDateTime[$i] = "";		
	}
			
	// Sender Name:
	$senderName[$i] = $consignment->sender->name[0] . ', ' . $consignment->sender->name[1];
	//Sender Address:
	$address = $consignment->sender->contactDetails[0]->address;
	$addressLine = $address->addressLine;
	$senderAddress[$i] = $addressLine[0] . ' ' . $addressLine[1] . ' ' .$addressLine[2] . ' ' . $address->suburbOrLocation . ' ' . $address->state . ' ' . $address->postCode;
	$senderCountry = $address->country;
	if ($senderCountry != "")
	{
		$senderAddress[$i] .= ' ' . $senderCountry;
	}
	//Sender Phone:
	$senderPhone[$i] = $consignment->sender->contactDetails[0]->phoneNumber;
	
	// Receiver Name:
	$receiverName[$i] = $consignment->receiver->name[0] . ', ' . $consignment->receiver->name[1];
	// Receiver Address:
	$address = $consignment->receiver->contactDetails[0]->address;
	$addressLine = $address->addressLine;
	$receiverAddress[$i] = $addressLine[0] . ' ' . $addressLine[1] . ' ' .$addressLine[2] . ' ' . $address->suburbOrLocation . ' ' .$address->state . ' ' . $address->postCode;
	$receiverCountry = $address->country;
	if ($receiverCountry != "")
	{
		$receiverAddress[$i] .= ' ' . $receiverCountry;
	}
	//Receiver Phone:
	$receiverPhone[$i] = $consignment->receiver->contactDetails[0]->phoneNumber;
	// Receiver Mobile:
	$receiverMobile[$i] = $consignment->receiver->contactDetails[0]->mobileNumber;

	
	// ********** ADDED CODE BEGINS HERE ****************
	
	// Consignment References:
	
	DEFINE("MAX_REFERENCES", 6);									// Maximum references per consignment - Sender's Ref + 5 User Refs

	// Default values to empty string, in case reference(s) absent
	$template = "\$reference*Name[\$i] = \"\"; \$reference*Value[\$i] = \"\";";	// Template for eval -- see HTML table
	for ($k = 0; $k < MAX_REFERENCES; $k++)
	{
		eval(str_replace("*", $k, $template));						// Execute the code
	}
	
	// Process any references
	$consignmentReferences = $consignment->references;				// The references for the consignment, if any
	if (!is_null($consignmentReferences))							// If at least one reference
	{
		$refCount = count($consignmentReferences);					// The number of references
		$template = "\$reference*Name[\$i] = \"\$refName\"; \$reference*Value[\$i] = \"\$refValue\";";	// Template for eval -- see HTML table
		for ($k = 0; $k < $refCount; $k++)
		{
			$consignmentReference = $consignmentReferences[$k];		// The next reference
			$refName = $consignmentReference->name;					// Name of the reference
			$refValue = $consignmentReference->value;				// Value of the reference
			eval(str_replace("*", $k, $template));					// Execute the code
		}
	}

	// ********** ADDED CODE ENDS HERE ****************

	// Special Instructions:
	$specialInstructions[$i] = $consignment->specialInstructions[0] . ' ' . $consignment->specialInstructions[1];
	// Total Weight
	$totalWeight[$i] = $consignment->totalWeight.'kg';

	// Proof of Delivery
	// Note: When delivery has been completed, getConsignmentDetails provides a POD signature image if the recipient 
	// signed on-screen. However currently a proportion of PODs are signed manually, not on-screen, and in that case
	// getConsignmentDetails cannot provide the signature image. Manual signature occurs primarily with agents. 
	// A complete POD solution for B2B use is not yet available.
	
	// Proof-of-Delivery Signatory:
	$signatory = $consignment->podSignatoryName;
	if (is_null($signatory))
	{
		$podSignatory[$i] = "";
	}
	else
	{
		$podSignatory[$i] = $oC->substituteAnyPODSignatoryCode($signatory);	// Usually $signatory is a real person's name but
																			// it can be a defined code. If it is a code 
																			//(e.g. *LAI), substitute a corresponding descriptive 
																			// string (e.g. LEFT AS INSTRUCTED).
	}
	
	// Proof-of-Delivery Signature:	
	$podSignatureBase64 = $consignment->podSignature;
	if (is_null($podSignatureBase64))					// Is there an electronic signature?
	{
		$podSignature[$i] = "";							// No
	}
	else
	{
		$podSignature[$i] = '<img src="data:image/png;base64,' . $podSignatureBase64 . '" alt="POD Signature" width="300" height="100">';
	}
	
	//Proof-of-Delivery Date:
	$podDateTime[$i] = "";
	
	$images = $consignment->image;
	$imageCount = count($images);				// Image may be POD or an attachment
	for ($k = 0; $k < $imageCount; $k++)
	{
		$image = $images[$k];
		if ($image->isPOD)
		{
			$podDateTime[$i] = $image->creationDateTime;
			break;
		}
	}

	// Carrier's Quality Control:
	$qualityControl[$i] = $oC->qualityControlDescription($consignment->publishedQCCode);
}
											  
$html = new DynamicHtml();		// Utility class for dynamic html
?>

<table width="600" border="1">

  <tr>
    <th scope="row" align="left">Consignment Id&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $consignmentId);?>
  </tr>
 
  <tr>
    <th scope="row" align="left">Service&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $serviceDescription);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Despatch Date&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $despatchDate);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Expected Delivery Date&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $etaDate);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Carrier Despatch Depot&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $despatchDepotDescription);?>
  </tr>

  <tr>
    <th scope="row" align="left">Carrier Delivery Depot&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $deliveryDepotDescription);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Consignment Status&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $consignmentStatusDescription);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Most Recently Seen&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $scanningDepotDescription);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Date and Time Seen&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $mostRecentScanDateTime);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Sender&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $senderName);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Sender Address&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $senderAddress);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Sender Phone&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $senderPhone);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Receiver&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $receiverName);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Receiver Address&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $receiverAddress);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Receiver Phone&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $receiverPhone);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Receiver Mobile&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $receiverMobile);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Special Instructions&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $specialInstructions);?>
  </tr>
  
<tr>
    <th scope="row" align="left">Total Weight&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $totalWeight);?>
  </tr>

<tr>
    <th scope="row" align="left">Proof of Delivery Signatory&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $podSignatory);?>
  </tr>
  
  <tr>
    <th scope="row" align="left">Proof of Delivery Signature&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $podSignature);?>
  </tr>

  <tr>
    <th scope="row" align="left">Proof of Delivery Date&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $podDateTime);?>
  </tr>

  <tr>
    <th scope="row" align="left">Carrier's Quality Control&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $qualityControl);?>
  </tr>

  <tr>
    <th scope="row" align="left">Reference0 Name&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference0Name);?>
  </tr>
  <tr>
    <th scope="row" align="left">Reference0 Value&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference0Value);?>
  </tr>  
  <tr>
    <th scope="row" align="left">Reference1 Name&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference1Name);?>
  </tr>
  <tr>
    <th scope="row" align="left">Reference1 Value&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference1Value);?>
  </tr>
  <tr>
    <th scope="row" align="left">Reference2 Name&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference2Name);?>
  </tr>
  <tr>
    <th scope="row" align="left">Reference2 Value&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference2Value);?>
  </tr>
  <tr>
    <th scope="row" align="left">Reference3 Name&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference3Name);?>
  </tr>
  <tr>
    <th scope="row" align="left">Reference3 Value&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference3Value);?>
  </tr>

  <tr>
    <th scope="row" align="left">Reference4 Name&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference4Name);?>
  </tr>
  <tr>
    <th scope="row" align="left">Reference4 Value&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference4Value);?>
  </tr>

  <tr>
    <th scope="row" align="left">Reference5 Name&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference5Name);?>
  </tr>
  <tr>
    <th scope="row" align="left">Reference5 Value&nbsp;</th>
    <?php $html->addColumns($consignmentCount, $reference5Value);?>
  </tr>
</table>

<?php
	   echo "<p>&nbsp;</p><p>For information here is a dump of the entire response: </p>";
	   var_dump($response);
?>

</body>
</html>