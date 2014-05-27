<?php

/* $Id: geocode_genxml_customers.php 4443 2010-12-23 15:30:30Z tim_schofield $*/
//$PageSecurity = 3;


include ('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
$title = _('Geocode Generate XML');
function parseToXML($htmlStr)
{
$xmlStr=str_replace('<','&lt;',$htmlStr);
$xmlStr=str_replace('>','&gt;',$xmlStr);
$xmlStr=str_replace('"','&quot;',$xmlStr);
$xmlStr=str_replace("'",'&#39;',$xmlStr);
$xmlStr=str_replace("&",'&amp;',$xmlStr);
return $xmlStr;
}
if(isset($_GET['custcode']) and $_GET['custcode']!=''){
  $sql = "SELECT * FROM custbranch WHERE debtorno='".$_GET['custcode']."' order by brname";  
}
else{
    if(isset($_GET['custstate']) and $_GET['custstate']!=''){
  $sql = "SELECT * FROM custbranch WHERE braddress3='".$_GET['custstate']."' order by brname"; 
    }
    else{
  $sql = "SELECT * FROM custbranch WHERE 1 order by brname";      
    }
}

$ErrMsg = _('An error occurred in retrieving the information');;
$result = DB_query($sql, $db, $ErrMsg);


header("Content-type: text/xml");

// Start XML file, echo parent node
echo '<markers>';

// Iterate through the rows, printing XML nodes for each
while ($myrow = DB_fetch_array($result)){
  // ADD TO XML DOCUMENT NODE
  echo '<marker ';
  echo 'name="' . parseToXML($myrow['brname']) . '" ';
  echo 'address="' . parseToXML($myrow["braddress1"] . ", " . $myrow["braddress2"] . ", " . $myrow["braddress3"] . ", " . $myrow["braddress4"]) . '" ';
  echo 'branchcontact="'. parseToXML($myrow['contactname']) . '" ';
  echo 'phoneno="'. parseToXML($myrow['phoneno']) . '" ';
  echo 'custcode="'. parseToXML($myrow['debtorno']) . '" ';
  echo 'custstate="'. parseToXML($myrow['braddress3']) . '" ';
  echo 'lat="' . $myrow['lat'] . '" ';
  echo 'lng="' . $myrow['lng'] . '" ';
  echo 'type="' . $myrow['type'] . '" ';
  echo '/>';
}

// End XML file
echo '</markers>';

?>