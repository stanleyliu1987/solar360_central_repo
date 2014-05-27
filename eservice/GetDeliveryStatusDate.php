
<?php
//     <document_root>/MyWebSite/EnquireConsignmentDetails.php
//     Invoked by ConsignmentDetails.php
//     Star Track Express
//     25 May 2011
//     Version 4.3

  require_once('ESsession.inc');
  require_once('eServices.php');					// Import the Star Track Express PHP API - do not modify this file
  require_once('CustomerConnect.php');				// Import ConnectDetails class - customer to modify this class as required
  include_once('config.php');
?>

<?php

$dbconnection = mysql_connect("localhost", $dbuser, $dbpassword);
if (!$dbconnection) {
  die("Not connected : " . mysql_error());
}
mysql_select_db($database, $dbconnection) or die( "Unable to select database");

$porefnumber=$_GET['porefnumber'];
$paymentdate=$_GET['paymentdate'];
$delservice=$_GET['delservice'];
$delstatus=$_GET['delstatus'];
$deldate=$_GET['deldate'];
$pocomment=$_GET['pocomment'];
/* Calculate the date interval between payment date and delivery date */
list($day, $month, $year) = split('/', $deldate);
$formatdate=$year.'-'.$month.'-'.$day;
$intervaldays = round((strtotime($formatdate)-strtotime($paymentdate))/86400);   

/* 0. Update Invoice Delivery Status, 05052014 By Stan update delivery status of each invoice*/
$sqlupdateInvoiceDelStatus="UPDATE debtortrans
			    SET delivery_status='".$_GET['Invdelstatus']."' 
			    WHERE id = '".$_GET['InvoiceId']."'";
$ErrMsg =_('The invoice delivery status could not be updated');
$resultInvDelStatusUpdate=mysql_query($sqlupdateInvoiceDelStatus); 
/* End of update process */

/* 1. Tracking Star Track Service */
if($delservice == 1){
/* Update Purchase Order Table with Default Values */
 $UpdateConsignmentSql= "UPDATE purchorders
					SET consignment_id='".$_GET['consignmentId']."' ,
                                            delivery_status='".$delstatus."',
                                            del_est_tag='Del Date',
                                            del_est_date='".$deldate."',
                                            days='".$intervaldays."',
                                            delivery_options=1,
                                            comments='".$pocomment."'
					WHERE ref_number = '".$porefnumber."'";
 $updateConDetailResult = mysql_query($UpdateConsignmentSql);
// Get the parameters required for a connection to eServices
$oConnect = new ConnectDetails();
$connection = $oConnect->getConnectDetails(); 
//Create the request, as per Request Schema described in eServices - Usage Guide.xls
$parameters = array(
					'header' => array(
										'source' => 'TEAM',
										'accountNo' => '10128362',
										'userAccessKey' => $connection['userAccessKey']
									 ),	
                    'consignmentId' => explode(" ", $_GET['consignmentId'])	// Allow for multiple consignments
				);
$request = array('parameters' => $parameters);

// NOTE: Applications *must* validate all parameters passed (data type, mandatory fields non-null), as described in Readme.pdf,
//       or alerts are generated at Star Track Express. Validation is omitted in this sample for reasons of clarity.

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
        if (empty($podDateTime[$i])){
            //$deldate='Est Date: '.$etaDate[$i].substr(0, 10);
            $deldatevalue=substr($etaDate[$i],0,10);
            $deltag='Est Date';
        }
        else{
           // $deldate='Del Date: '.$podDateTime[$i].substr(0, 10); 
            $deldatevalue=substr($podDateTime[$i],0,10);
            $deltag='Del Date';
        }
        
        $delStatus='Del Status: '.$consignmentStatusDescription[$i];
            
        /* Calculate the date interval between payment date and delivery date */

        $intervaldays = round((strtotime($deldatevalue)-strtotime($paymentdate))/86400);

        
/* Save Consignment Details into Purchase Order Table */

$UpdateConsignmentSql= "UPDATE purchorders
					SET consignment_id='".$consignmentId[$i]."' ,
                                            delivery_status='".$consignmentStatusDescription[$i]."',
                                            del_est_tag='".$deltag."',
                                            del_est_date='".$deldatevalue."',
                                            days='".$intervaldays."',
                                            delivery_options=1,
                                            comments='".$pocomment."'
					WHERE ref_number = '".$porefnumber."'";
 
 $updateConDetailResult = mysql_query($UpdateConsignmentSql);
        
 echo $deldatevalue.','.$delStatus.','.$deltag.','.$intervaldays;	
}
}
/* 09042014 by Stan Tracking Other Services details except Star Track */
else{      
/* Save Consignment Details into Purchase Order Table */
$UpdateConsignmentSql= "UPDATE purchorders
					SET consignment_id='".$_GET['consignmentId']."' ,
                                            delivery_status='".$delstatus."',
                                            del_est_tag='Del Date',
                                            del_est_date='".$formatdate."',
                                            days='".$intervaldays."',
                                            delivery_options='".$delservice."',
                                            comments='".$pocomment."'
					WHERE ref_number = '".$porefnumber."'";
 
 $updateConDetailResult = mysql_query($UpdateConsignmentSql);
 $deltag='Del Date';
 echo $formatdate.','.$delstatus.','.$deltag.','.$intervaldays;
}
mysql_close($dbconnection);

?>


