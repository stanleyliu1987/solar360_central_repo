<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php
//     <document_root>/MyWebSite/EnquireCost.php
//     Invoked by CostCalculation.php
//     Star Track Express
//     25 May 2011
//     Version 4.3

  require_once("eServices.php");					// Import the Star Track Express PHP API - do not modify this file
  require_once("CustomerConnect.php");				// Import ConnectDetails class - customer to modify this class as required
?>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>EnquireCost</title>
</head>

<body>
<h1>Results</h1>

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
					'senderLocation' => array(
											'suburb' => $_POST['suburbSender'],
											'postCode' => $_POST['postCodeSender'],
											'state' => strtoupper($_POST['stateSender'])		// Must be upper case
											 ),
					'receiverLocation' => array(
												'suburb' => $_POST['suburbReceiver'],
												'postCode' => $_POST['postCodeReceiver'],
												'state' => strtoupper($_POST['stateReceiver'])	// Must be upper case
											   ),
					'serviceCode' => strtoupper($_POST['serviceCode']),							// Must be upper case
					'noOfItems' => $_POST['noOfItems'],
					'weight' => $_POST['weight'],
					'volume' => $_POST['volume'],
					'includeRiskWarranty' => $_POST['includeRiskWarranty']
					);

// Risk Warranty Value can be present only if it is non-null, otherwise a fault occurs

$riskWarrantyValue = $_POST['riskWarrantyValue'];
if ($riskWarrantyValue != "")
{
	$parameters += array('riskWarrantyValue' => $riskWarrantyValue);							// Append Risk Warranty Value
}

// NOTE: Applications *must* validate all parameters passed (data type, mandatory fields non-null), as described in Readme.pdf,
//       or alerts are generated at Star Track Express. Validation is omitted in this sample for reasons of clarity.

$request = array('parameters' => $parameters);

// Invoke Star Track Express eServices
try
{
	$oC = new STEeService();
//	$oC = new startrackexpress\eservices\STEeService();	// *** If PHP V5.3 or later, uncomment this line and remove the line above ***

$response = $oC->invokeWebService($connection,'calculateCost', $request);	
																						// $response is as per Response Schema
																						// described in eServices - Usage Guide.xls.
																						// Returned value is a stdClass object.
																						// Faults to be handled as appropriate.<br />

$totalCostExGST = $response->cost + $response->fuelSurcharge + $response->riskWarrantyCharge;
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

?>

<table width="600" border="1">
  <tr>
    <th scope="row" align="left">Standard Cost&nbsp;</th>
    <td><?php echo "$" . number_format($response->cost, 2);?>&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">GST&nbsp;</th>
    <td><?php echo "$" . number_format($response->gstCharge, 2);?>&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">FuelSurcharge&nbsp;</th>
    <td><?php echo "$" . number_format($response->fuelSurcharge, 2);?>&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">RiskWarrantyCharge&nbsp;</th>
    <td><?php echo "$" . number_format($response->riskWarrantyCharge, 2);?>&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">RiskWarrantyValue&nbsp;</th>
    <td><?php echo "$" . number_format($response->riskWarrantyValue, 2);?>&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">Total Cost ex GST&nbsp;</th>
    <td><?php echo "$" . number_format($totalCostExGST, 2);?>&nbsp;</td>
  </tr>

</table>

</body>
</html>