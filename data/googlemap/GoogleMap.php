<?php
require("config.php");

// Opens a connection to a MySQL server
$connection = mysql_connect("localhost", $dbuser, $dbpassword);
if (!$connection) {
  die("Not connected : " . mysql_error());
}

// Set the active MySQL database
$db_selected = mysql_select_db($database, $connection);
if (!$db_selected) {
  die("Can\'t use db : " . mysql_error());
}

// Select all the rows in the custbranch table
$query = "SELECT * FROM custbranch where 1";
$result = mysql_query($query);
if (!$result) {
  die("Invalid query: " . mysql_error());
}

// Select all the rows in the supplier table
$query2 = "SELECT * FROM suppliers where 1";
$result2 = mysql_query($query2);
if (!$result2) {
  die("Invalid query: " . mysql_error());
}

// Initialize delay in geocode speed
$delay = 3000000;
$base_url = "http://" . MAPS_HOST . "/maps/api/geocode/xml?address=";

// Iterate through the rows, geocoding each address
while ($row = @mysql_fetch_assoc($result)) {
  $geocode_pending = true;

  while ($geocode_pending) {
    $address = urlencode($row["braddress1"] . "," . $row["braddress2"] . "," . $row["braddress3"] . "," . $row["braddress4"]);
    $id = $row["branchcode"];
    $debtorno =$row["debtorno"];
    $request_url = $base_url . $address . ',&sensor=true';
   
    echo '<br \>' . _('Customer Code: ') . $id;


    $xml = simplexml_load_string(utf8_encode(file_get_contents($request_url))) or die("url not loading");

    $status = $xml->status; 

    if (strcmp($status, "OK") == 0) {
      // Successful geocode
      $geocode_pending = false;
      // Format: Longitude, Latitude, Altitude
      $lat = $xml->result->geometry->location->lat;
      $lng = $xml->result->geometry->location->lng;

	  $query = sprintf("UPDATE custbranch " .
	         " SET lat = '%s', lng = '%s' " .
	         " WHERE branchcode = '%s' " .
	         " AND debtorno = '%s' LIMIT 1;",
	         mysql_real_escape_string($lat),
	         mysql_real_escape_string($lng),
	         mysql_real_escape_string($id),
	         mysql_real_escape_string($debtorno));
	  $update_result = mysql_query($query);
	  if ($update_result) {
      echo '<br />'. 'Address: ' . $address . ' updated to geocode.';
      echo '<br />'. 'Received status ' . $status . '<br />';
	  }
	} else {
	  // failure to geocode
      $geocode_pending = false;
      echo '<br />' . 'Address: ' . $address . _('failed to geocode.');
      echo 'Received status ' . $status . '<br />';
	}
	usleep($delay);
  }
}

// Iterate through the rows, geocoding each address
while ($row2 = @mysql_fetch_assoc($result2)) {
  $geocode_pending = true;

  while ($geocode_pending) {
    $address = urlencode($row2["address1"] . "," . $row2["address2"] . "," . $row2["address3"] . "," . $row2["address4"]);
    $id = $row2["supplierid"];
    $request_url = $base_url . $address . ',&sensor=true';

    echo '<p>' . _('Supplier Code: ') . $id;
    
    $xml = simplexml_load_string(utf8_encode(file_get_contents($request_url))) or die("url not loading");

    $status = $xml->status; 

	 if (strcmp($status, "OK") == 0) {
      // Successful geocode
      $geocode_pending = false;
      // Format: Longitude, Latitude, Altitude
      $lat = $xml->result->geometry->location->lat;
      $lng = $xml->result->geometry->location->lng;

          $query = sprintf("UPDATE suppliers " .
             " SET lat = '%s', lng = '%s' " .
             " WHERE supplierid = '%s' LIMIT 1;",
             mysql_real_escape_string($lat),
             mysql_real_escape_string($lng),
             mysql_real_escape_string($id));
          
	  $update_result = mysql_query($query);
	  if ($update_result) {
      echo '<br />' . 'Address: ' . $address . ' updated to geocode.';
      echo '<br />' . 'Received status ' . $status . '<br />';
	  }
	} else {
      // failure to geocode
      $geocode_pending = false;
      echo '<br />' . 'Address: ' . $address . ' failed to geocode.';
      echo '<br />' . 'Received status ' . $status . '<br />';
	}
	usleep($delay);
  }
}
?>