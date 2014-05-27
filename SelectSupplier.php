<?php
/* $Id: SelectSupplier.php 4551 2011-04-16 06:20:56Z daintree $*/

include ('includes/session.inc');
$title = _('Search Suppliers');
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
if (isset($_POST['Select'])) { /*User has hit the button selecting a supplier */
	$_SESSION['SupplierID'] = $_POST['Select'];
	unset($_POST['Select']);
	unset($_POST['Keywords']);
	unset($_POST['SupplierCode']);
        unset($_POST['SupplierContact']);
	unset($_POST['Search']);
	unset($_POST['Go']);
	unset($_POST['Next']);
	unset($_POST['Previous']);
}
if (!isset($_SESSION['SupplierID'])) {
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/supplier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Suppliers') . '</p>';
}
if (isset($_GET['SupplierID'])) {
	$_SESSION['SupplierID']=$_GET['SupplierID'];
}
// only get geocode information if integration is on, and supplier has been selected
if ($_SESSION['geocode_integration'] == 1 AND isset($_SESSION['SupplierID'])) {
	$sql = "SELECT * FROM geocode_param WHERE 1";
	$ErrMsg = _('An error occurred in retrieving the information');;
	$result = DB_query($sql, $db, $ErrMsg);
	$myrow = DB_fetch_array($result);
	$sql = "SELECT suppliers.supplierid,
				suppliers.lat,
				suppliers.lng,
                                suppliers.address1,
                                suppliers.address2,
                                suppliers.address3,
                                suppliers.address4,
                                suppliers.suppname
			FROM suppliers
			WHERE suppliers.supplierid = '" . $_SESSION['SupplierID'] . "'
			ORDER BY suppliers.supplierid";
	$ErrMsg = _('An error occurred in retrieving the information');
	$result2 = DB_query($sql, $db, $ErrMsg);
	$myrow2 = DB_fetch_array($result2);
	$lat = $myrow2['lat'];
	$lng = $myrow2['lng'];
        $suppliername=$myrow2['suppname'];
        $supplieraddress = $myrow2["address1"] . ", " . $myrow2["address2"] . ", " . $myrow2["address3"] . ", " . $myrow2["address4"];
	$api_key = $myrow['geocode_key'];
	$center_long = $myrow['center_long'];
	$center_lat = $myrow['center_lat'];
	$map_height = $myrow['map_height'];
	$map_width = $myrow['map_width'];
	$map_host = $myrow['map_host'];
             
       echo '  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&sensor=true"></script>
                <script type="text/javascript">
                    function load() {
                       var mapOptions = {
                       center: new google.maps.LatLng(' . $lat . ', ' . $lng . '),
                       zoom: 12,
                       mapTypeId: google.maps.MapTypeId.ROADMAP
                       };
                       var map = new google.maps.Map(document.getElementById("map"),
                       mapOptions);
                       var panoramaOptions = {
                        position: new google.maps.LatLng(' . $lat . ', ' . $lng . '),
                        pov: {
                        heading: 34,
                        pitch: 10,
                        zoom: 1
                        }
                     };
                       var panorama = new google.maps.StreetViewPanorama(document.getElementById("pano"), panoramaOptions);
                       map.setStreetView(panorama);
                       
                       var marker = new google.maps.Marker({
                       position: map.getCenter(),
                       map: map,
                       title: "Click to zoom"
                       });
                      google.maps.event.addListener(marker, "click", function() {
                      map.setZoom(16);
                      map.setCenter(marker.getPosition());
                      });


                     }
                </script>';
//	echo '<script src="http://maps.google.com/maps?file=api&v=2&key=' . $api_key . '"';
//	echo ' type="text/javascript"></script>';
//	echo ' <script type="text/javascript">';
//	echo 'function load() {
//               var WINDOW_HTML = "<b>"+ "'.$suppliername.'" + "</b> <br/>" + "'.$supplieraddress.'";
//		if (GBrowserIsCompatible()) {
//			var map = new GMap2(document.getElementById("map"));
//			map.addControl(new GSmallMapControl());
//			map.addControl(new GMapTypeControl());';
//	echo 'map.setCenter(new GLatLng(' . $lat . ', ' . $lng . '), 11);';
//	echo 'var marker = new GMarker(new GLatLng(' . $lat . ', ' . $lng . '));';
//	echo 'map.addOverlay(marker);
//			GEvent.addListener(marker, "click", function() {
//			marker.openInfoWindowHtml(WINDOW_HTML);
//			});
//			marker.openInfoWindowHtml(WINDOW_HTML);
//			}
//                         var fenwayPOV = {yaw:370.64659986187695,pitch:-20};          
//                myPano = new GStreetviewPanorama(document.getElementById("pano"));
//                myPano.setLocationAndPOV(new GLatLng(' . $lat . ', ' . $lng . '), fenwayPOV);
//                GEvent.addListener(myPano, "error", handleNoFlash);  
//                panoClient.getNearestPanorama(new GLatLng(' . $lat . ', ' . $lng . '),showPanoData);
//		}';
//        
//	echo 'function showPanoData(panoData) {
//      if (panoData.code != 200) {
//        GLog.write("showPanoData: Server rejected with code: " + panoData.code);
//        return;
//      }
//      nextPanoId = panoData.links[0].panoId;
//      var displayString = [
//        "Panorama ID: " + panoData.location.panoId,
//        "LatLng: " + panoData.location.latlng,
//        "Copyright: " + panoData.copyright,
//        "Description: " + panoData.location.description,
//        "Next Pano ID: " + panoData.links[0].panoId
//      ].join("<br/>");
//      map.openInfoWindowHtml(panoData.location.latlng, displayString);
//      
//      GLog.write("Viewer moved to" + panoData.location.latlng);
//      myPano.setLocationAndPOV(panoData.location.latlng);
//    }';             
//       
//        echo 'function handleNoFlash(errorCode) {
//                       if (errorCode == 603) {
//                       alert("Error: Flash doesnt appear to be supported by your browser");
//                       return;
//                     }
//                 }
//			</script>
	echo '<body onload="load()" onunload="GUnload()" >';
}

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']) OR isset($_GET['SearchSupName'])) {
    if(isset($_GET['SearchSupName'])){
        $_POST['Keywords']=$_GET['SearchSupName'];
    }
	if (strlen($_POST['Keywords']) > 0 AND strlen($_POST['SupplierCode']) > 0) {
		prnMsg( _('Supplier name keywords have been used in preference to the Supplier code extract entered'), 'info' );
	}
        elseif(strlen($_POST['Keywords']) > 0 AND strlen($_POST['SupplierContact']) > 0) {
		prnMsg( _('Supplier name keywords have been used in preference to the Supplier contact extract entered'), 'info' );
        }
	if ($_POST['Keywords'] == '' AND $_POST['SupplierCode'] == '' AND $_POST['SupplierContact']=='') {
		$SQL = "SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4
				FROM suppliers
				ORDER BY suppname";
	} else {
		if (strlen($_POST['Keywords']) > 0) {
			$_POST['Keywords'] = strtoupper($_POST['Keywords']);
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = "SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4
				FROM suppliers
				WHERE suppname " . LIKE . " '" . $SearchString . "'
				ORDER BY suppname";
		} elseif (strlen($_POST['SupplierCode']) > 0) {
			$_POST['SupplierCode'] = strtoupper($_POST['SupplierCode']);
			$SQL = "SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4
				FROM suppliers
				WHERE supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'
				ORDER BY supplierid";
		}
                   elseif (strlen($_POST['SupplierContact']) > 0) { 
			$_POST['SupplierContact'] = strtoupper($_POST['SupplierContact']);
			$SQL = "SELECT suppliers.supplierid,
				       suppliers.suppname,
				       suppliers.currcode,
				       suppliers.address1,
				       suppliers.address2,
				       suppliers.address3,
				       suppliers.address4
				FROM suppliers, suppliercontacts
				WHERE suppliers.supplierid=suppliercontacts.supplierid  AND
                                suppliercontacts.contact " . LIKE . " '%" . $_POST['SupplierContact'] . "%'
                                GROUP BY suppliers.supplierid   
				ORDER BY suppliers.supplierid";
		}
	} //one of keywords or SupplierCode was more than a zero length string
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) == 1) {
		$myrow = DB_fetch_row($result);
		$SingleSupplierReturned = $myrow[0];
	}
} //end of if search
if (isset($SingleSupplierReturned)) { /*there was only one supplier returned */
	$_SESSION['SupplierID'] = $SingleSupplierReturned;
	unset($_POST['Keywords']);
	unset($_POST['SupplierCode']);
        unset($_POST['SupplierContact']);
}
if (isset($_SESSION['SupplierID'])) {
	$SupplierName = '';
	$SQL = "SELECT suppliers.suppname
		FROM suppliers
		WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";
	$SupplierNameResult = DB_query($SQL, $db);
	if (DB_num_rows($SupplierNameResult) == 1) {
		$myrow = DB_fetch_row($SupplierNameResult);
		$SupplierName = $myrow[0];
	}
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/supplier.png" title="' .
				_('Supplier') . '" alt="" />' . ' ' . _('Supplier') . ' : <b>' . $_SESSION['SupplierID'] .
				' - ' . $SupplierName . '</b> ' . _('has been selected') . '.</p>';
	echo '<div class="page_help_text">' . _('Select a menu option to operate using this supplier.') . '</div>';
	echo '<br /><table width=90% colspan=2 cellpadding=4>';
	echo '<tr>
		<th width=33%>' . _('Supplier Inquiries') . '</th>
		<th width=33%>' . _('Supplier Transactions') . '</th>
		<th width=33%>' . _('Supplier Maintenance') . '</th>
		</tr>';
	echo '<tr><td valign=top class="select">'; /* Inquiry Options */
	echo '<a href="' . $rootpath . '/SupplierInquiry.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . _('Supplier Account Inquiry') . '</a>
		<br />
		<br />';
	
	echo '<br /><a href="' . $rootpath . '/PO_SelectOSPurchOrder.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '">' . _('Add / Receive / View Outstanding Purchase Orders') . '</a>';
	echo '<br /><a href="' . $rootpath . '/PO_SelectPurchOrder.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '">' . _('View All Purchase Orders') . '</a><br />';
	wikiLink('Supplier', $_SESSION['SupplierID']);
	echo '<br /><a href="' . $rootpath . '/ShiptsList.php?SupplierID=' . $_SESSION['SupplierID'] . '&SupplierName=' . urlencode($SupplierName) . '">' . _('List all open shipments for') .' '.$SupplierName. '</a>';
	echo '<br /><a href="' . $rootpath . '/Shipt_Select.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '">' . _('Search / Modify / Close Shipments') . '</a>';
	echo '<br /><a href="' . $rootpath . '/SuppPriceList.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '">' . _('Supplier Price List') . '</a>';
	echo '</td><td valign=top class="select">'; /* Supplier Transactions */
	echo '<a href="' . $rootpath . '/PO_Header.php?NewOrder=Yes&SupplierID=' . $_SESSION['SupplierID'] . '">' . _('Enter a Purchase Order for This Supplier') . '</a><br />';
	echo '<a href="' . $rootpath . '/SupplierInvoice.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . _('Enter a Suppliers Invoice') . '</a><br />';
	echo '<a href="' . $rootpath . '/SupplierCredit.php?New=true&SupplierID=' . $_SESSION['SupplierID'] . '">' . _('Enter a Suppliers Credit Note') . '</a><br />';
	echo '<a href="' . $rootpath . '/Payments.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . _('Enter a Payment to, or Receipt from the Supplier') . '</a><br />';
	echo '<br />';
	echo '<br /><a href="' . $rootpath . '/ReverseGRN.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . _('Reverse an Outstanding Goods Received Note (GRN)') . '</a>';
	echo '</td><td valign=top class="select">'; /* Supplier Maintenance */
	echo '<a href="' . $rootpath . '/Suppliers.php">' . _('Add a New Supplier') . '</a><br />';
	echo '<a href="' . $rootpath . '/Suppliers.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . _('Modify Or Delete Supplier Details') . '</a>';
	echo '<br /><a href="' . $rootpath . '/SupplierContacts.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . _('Add/Modify/Delete Supplier Contacts') . '</a>';
        echo '<br /><a href="' . $rootpath . '/SupplierWarehouse.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . _('Add/Modify/Delete Supplier Warehouses') . '</a>';
        echo '<br />';
	echo '<br /><a href="' . $rootpath . '/Shipments.php?NewShipment=Yes">' . _('Set Up A New Shipment') . '</a>';
	echo '<br /><a href="' . $rootpath . '/SuppLoginSetup.php">' . _('Supplier Login Configuration') . '</a>';
	echo '</td></tr></table>';
} else {
	// Supplier is not selected yet
	echo '<br />';
	echo '<table width=90% colspan=2 cellpadding=4>';
	echo '<tr>
		<th width=33%>' . _('Supplier Inquiries') . '</th>
		<th width=33%>' . _('Supplier Transactions') . '</th>
		<th width=33%>' . _('Supplier Maintenance') . '</th>
		</tr>';
	echo '<tr><td valign=top class="select">'; /* Inquiry Options */
	echo '</td><td valign=top class="select">'; /* Supplier Transactions */
	echo '</td><td valign=top class="select">'; /* Supplier Maintenance */
	echo '<a href="' . $rootpath . '/Suppliers.php">' . _('Add a New Supplier') . '</a><br />';
	echo '</td></tr></table>';
}
echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') .
		'" alt="" />' . ' ' . _('Search for Suppliers') . '</p>
		<table class=selection><tr><td colspan="2">' . _('Enter a partial Name') . ':</td><td>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size=20 maxlength=25>';
} else {
	echo '<input type="text" name="Keywords" size=20 maxlength=25>';
}
echo '</td><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Code') . ':</font></td><td>';
if (isset($_POST['SupplierCode'])) {
	echo '<input type="text" name="SupplierCode" value="' . $_POST['SupplierCode'] . '" size=15 maxlength=18>';
} else {
	echo '<input type="text" name="SupplierCode" size=15 maxlength=18>';
}
echo '</td></tr>';
echo '<tr><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Supplier Contact') . ':</font></td><td>';
if (isset($_POST['SupplierContact'])) {
	echo '<input type="text" name="SupplierContact" value="' . $_POST['SupplierContact'] . '" size=15 maxlength=18>';
} else {
	echo '<input type="text" name="SupplierContact" size=15 maxlength=18>';
}
echo '</td></tr></table>
    
		<br /><div class="centre"><input type="submit" name="Search" value="' . _('Search Now') . '"></div>';
//if (isset($result) AND !isset($SingleSupplierReturned)) {
if (isset($_POST['Search'])) {
	$ListCount = DB_num_rows($result);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	if ($ListPageMax > 1) {
		echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
			} else {
				echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<input type=submit name="Go" value="' . _('Go') . '">
			<input type=submit name="Previous" value="' . _('Previous') . '">
			<input type=submit name="Next" value="' . _('Next') . '">';
		echo '<p>';
	}
	echo '<input type=hidden name="Search" value="' . _('Search Now') . '">';
	echo '<br /><br />';
	echo '<br /><table cellpadding=2 colspan=7>';
	$tableheader = '<tr>
  		<th>' . _('Code') . '</th>
		<th>' . _('Supplier Name') . '</th>
		<th>' . _('Currency') . '</th>
		<th>' . _('Address 1') . '</th>
		<th>' . _('Address 2') . '</th>
		<th>' . _('Address 3') . '</th>
		<th>' . _('Address 4') . '</th>
		</tr>';
	echo $tableheader;
	$j = 1;
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;
	if (DB_num_rows($result) <> 0) {
		DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	}
	while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		echo '<td><input type=submit name="Select" value="'.$myrow['supplierid'].'" /></td>
			<td>'.$myrow['suppname'].'</td>
			<td>'.$myrow['currcode'].'</td>
			<td>'.$myrow['address1'].'</td>
			<td>'.$myrow['address2'].'</td>
			<td>'.$myrow['address3'].'</td>
			<td>'.$myrow['address4'].'</td>
			</tr>';
		$RowIndex = $RowIndex + 1;
		//end of page full new headings if
	}
	//end of while loop
	echo '</table>';
}
//end if results to show
if (isset($ListPageMax) and $ListPageMax > 1) {
	echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
	echo '<select name="PageOffset">';
	$ListPage = 1;
	while ($ListPage <= $ListPageMax) {
		if ($ListPage == $_POST['PageOffset']) {
			echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
		} else {
			echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
		}
		$ListPage++;
	}
	echo '</select>
		<input type=submit name="Go" value="' . _('Go') . '">
		<input type=submit name="Previous" value="' . _('Previous') . '">
		<input type=submit name="Next" value="' . _('Next') . '">';
	echo '<p>';
}
echo '</form>';
// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
if (isset($_SESSION['SupplierID']) and $_SESSION['SupplierID'] != '') {
	if ($_SESSION['geocode_integration'] == 1) {
		if ($lat == 0) {
			echo '<br />';
			echo '<div class="centre">' . _('Mapping is enabled, but no Mapping data to display for this Supplier.') . '</div>';
		} else {
			echo '<div class="centre"><br />';
			echo '<tr><td colspan=2>';
			echo '<table width=45% colspan=2 border=2 cellpadding=4>';
			echo '<tr><th width=33%>' . _('Supplier Mapping') . '</th></tr>';
			echo '</td><td valign=top>'; /* Mapping */
			echo '<div class="centre">' . _('Mapping is enabled, Map will display below.') . '</div>';
			echo '<div class="centre" id="map" style="width: ' . $map_width . 'px; height: ' . $map_height . 'px"></div></div><br />';
                        echo '<div class="centre" id="pano" style="width: ' . $map_width . 'px; height: ' . $map_height . 'px"></div>';  
			echo '</th></tr></table>';
		}
	}
	// Extended Info only if selected in Configuration
	if ($_SESSION['Extended_SupplierInfo'] == 1) {
		if ($_SESSION['SupplierID'] != '') {
			$sql = "SELECT suppliers.suppname,
							suppliers.lastpaid,
							suppliers.lastpaiddate,
							suppliersince
					FROM suppliers
					WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";
			$ErrMsg = _('An error occurred in retrieving the information');
			$DataResult = DB_query($sql, $db, $ErrMsg);
			$myrow = DB_fetch_array($DataResult);
			// Select some more data about the supplier
			$SQL = "SELECT sum(-ovamount) AS total FROM supptrans WHERE supplierno = '" . $_SESSION['SupplierID'] . "' and type != '20'";
			$Total1Result = DB_query($SQL, $db);
			$row = DB_fetch_array($Total1Result);
			echo '<br />';
			echo '<tr><td colspan=2>';
			echo '<table width=45% colspan=2 cellpadding=4>';
			echo '<tr><th width=33% colspan=2>' . _('Supplier Data') . '</th></tr>';
			echo '<tr><td valign=top class=select>'; /* Supplier Data */
			//echo "Distance to this Supplier: <b>TBA</b><br />";
			if ($myrow['lastpaiddate'] == 0) {
				echo _('No payments yet to this supplier.') . '</td><td valign=top class=select></td></tr>';
			} else {
				echo _('Last Paid:') . '</td><td valign=top class=select> <b>' . ConvertSQLDate($myrow['lastpaiddate']) . '</b></td></tr>';
			}
			echo '<tr><td valign=top class=select>'._('Last Paid Amount:') . '</td><td valign=top class=select>  <b>' . number_format($myrow['lastpaid'], 2) . '</b></td></tr>';
			echo '<tr><td valign=top class=select>'._('Supplier since:') . '</td><td valign=top class=select> <b>' . ConvertSQLDate($myrow['suppliersince']) . '</b></td></tr>';
			echo '<tr><td valign=top class=select>'._('Total Spend with this Supplier:') . '</td><td valign=top class=select> <b>' . number_format($row['total'], 2) . '</b></td></tr>';
			echo '</th></tr></table>';
		}
	}
}
include ('includes/footer.inc');
echo '<script>defaultControl(document.forms[0].SupplierCode);</script>';
?>