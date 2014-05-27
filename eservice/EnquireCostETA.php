

<?php
//     <document_root>/MyWebSite/EnquireCostETA.php
//     Invoked by CostETACalculation.php
//     Star Track Express
//     25 May 2011
//     Version 4.3
  require_once('ESsession.inc');
  require_once("eServices.php");					// Import the Star Track Express PHP API - do not modify this file
  require_once("CustomerConnect.php");				// Import ConnectDetails class - customer to modify this class as required
  require_once("header.inc");
?>

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
					'includeRiskWarranty' => $_POST['includeRiskWarranty'],
					'choice' => array(
									  'despatchDate' => $_POST['despatchDate']
									  )
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

$response = $oC->invokeWebService($connection,'calculateCostAndEstimatedTime', $request);	
																						// $response is as per Response Schema
																						// described in eServices - Usage Guide.xls.
																						// Returned value is a stdClass object.
																						// Faults to be handled as appropriate.

$totalCostExGST = $response->cost + $response->fuelSurcharge + $response->riskWarrantyCharge;
}
catch (SoapFault $e)
{
	echo "<p>" . $e->detail->fault->fs_severity . "<br />";	
	echo $e->faultstring . "<br />";
	echo $e->detail->fault->fs_message . "<br />";
        echo "<input type=button value=Re-calculate onclick=Recalculate()>";
	exit();
        
	//	Or if there is a higher caller:    throw new SoapFault($e->faultcode, $e->faultstring, $e->faultactor, $e->detail, $e->_name, $e->headerfault);	
}

?>
<form id= "form2" action="CostETACalculation.php" method="post">
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
    <th scope="row" align="left">Estimated Time of Arrival&nbsp;</th>
    <td><?php echo $response->eta;?>&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">Total Cost ex GST&nbsp;</th>
    <td><?php echo "$" . number_format($totalCostExGST, 2);?>&nbsp;</td>
  </tr>

</table><br/><br/>
<div align="center">
<input type="submit" value="Re-calculate"/>
<input type="button" value="Get Freight Cost" onclick="ReturnBack(<?php echo $totalCostExGST;?>,<?php echo $_SESSION['freightcosttxt'];?>)"/>
</div>
</form>
</body>
<?php
require_once("footer.inc");
?>