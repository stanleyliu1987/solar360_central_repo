
<?php
//     <document_root>/MyWebSite/EnquireConsignmentDetails.php
//     Invoked by ConsignmentDetails.php
//     Star Track Express
//     25 May 2011
//     Version 4.3
  require_once('eServices.php');					// Import the Star Track Express PHP API - do not modify this file
  require_once('CustomerConnect.php');				// Import ConnectDetails class - customer to modify this class as required
  include_once('config.php');		// Import ConnectDetails class - customer to modify this class as required
?>

<?php

$dbconnection = mysql_connect("localhost", $dbuser, $dbpassword);
if (!$dbconnection) {
  die("Not connected : " . mysql_error());
}
mysql_select_db($database, $dbconnection) or die( "Unable to select database");
$paymentdate='';


// Select Consignment Note to update which is not null and already delivered
$UpdateConsignmentSql= "Select * from purchorders where delivery_options=1";

$UpdateConsignmentSqlResult = mysql_query($UpdateConsignmentSql);

while($UCrow = mysql_fetch_array($UpdateConsignmentSqlResult))
  {

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
                    'consignmentId' => explode(" ", $UCrow['consignment_id'])	// Allow for multiple consignments
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
        
       
        /* Calculate the date interval between payment date and delivery date */
        if($paymentdate!=0){
        $intervaldays = round((strtotime($deldatevalue)-strtotime($paymentdate))/86400)-1;
        }
        else{
        $intervaldays = 0;   
        }
        
/* Save Consignment Details into Purchase Order Table */

$UpdateConsignmentSql= "UPDATE purchorders
					SET consignment_id='".$consignmentId[$i]."' ,
                                            delivery_status='".$consignmentStatusDescription[$i]."',
                                            del_est_tag='".$deltag."',
                                            del_est_date='".$deldatevalue."',
                                            days='".$intervaldays."'
					WHERE ref_number = '".$UCrow['ref_number']."'";
 
 $updateConDetailResult = mysql_query($UpdateConsignmentSql);
 if( $updateConDetailResult ==1){
 echo $consignmentId[$i].' status: '.$consignmentStatusDescription[$i].' update successfully! <br>';
 }
  }


 }

 mysql_close($dbconnection);

?>


