<?php

/* $Id: SelectCustomer.php 4553 2011-04-18 00:09:23Z daintree $*/

include ('includes/session.inc');
$title = _('Search Customers');
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
if (isset($_GET['Select'])) {
	$_SESSION['CustomerID'] = $_GET['Select'];
}
if (!isset($_SESSION['CustomerID'])) { //initialise if not already done
	$_SESSION['CustomerID'] = "";
}
if (isset($_GET['Area'])) {
	$_POST['Area']=$_GET['Area'];
	$_POST['Search']='Search';
	$_POST['Keywords']='';
	$_POST['CustCode']='';
	$_POST['CustPhone']='';
	$_POST['CustAdd']='';
	$_POST['CustType']='';
        $_POST['CustBranchContact']='';
}
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Customers') . '</p>';
if (!isset($_SESSION['CustomerType'])) { //initialise if not already done
	$_SESSION['CustomerType'] = "";
}
// only run geocode if integration is turned on and customer has been selected
if ($_SESSION['geocode_integration'] == 1 AND $_SESSION['CustomerID'] != "") {
	$sql = "SELECT * FROM geocode_param WHERE 1";
	$ErrMsg = _('An error occurred in retrieving the information');
	$result = DB_query($sql, $db, $ErrMsg);
	$myrow = DB_fetch_array($result);
	$sql = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.lat,
					custbranch.lng,
                                        custbranch.braddress1,
                                        custbranch.braddress2,
                                        custbranch.braddress3,
                                        custbranch.braddress4
				FROM debtorsmaster LEFT JOIN custbranch
					ON debtorsmaster.debtorno = custbranch.debtorno
				WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'
				ORDER BY debtorsmaster.debtorno";
	$ErrMsg = _('An error occurred in retrieving the information');
	$result2 = DB_query($sql, $db, $ErrMsg);
	$myrow2 = DB_fetch_array($result2);
	$lat = $myrow2['lat'];
	$lng = $myrow2['lng'];
        $customername=$myrow2['name'];
        $customeraddress = $myrow2["braddress1"] . ", " . $myrow2["braddress2"] . ", " . $myrow2["braddress3"] . ", " . $myrow2["braddress4"];
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
//                var WINDOW_HTML = "<b>"+ "'.$customername.'" + "</b> <br/>" + "'.$customeraddress.'";
//            
//		if (GBrowserIsCompatible()) {
//			var map = new GMap2(document.getElementById("map"));
//		map.addControl(new GSmallMapControl());
//		map.addControl(new GMapTypeControl());';
//	echo 'map.setCenter(new GLatLng(' . $lat . ', ' . $lng . '), 11);';
//	echo 'var marker = new GMarker(new GLatLng(' . $lat . ', ' . $lng . '));';
//	echo 'map.addOverlay(marker);
//		GEvent.addListener(marker, "click", function() {
//		marker.openInfoWindowHtml(WINDOW_HTML);
//		});
//		marker.openInfoWindowHtml(WINDOW_HTML);
//		}
//                var fenwayPOV = {yaw:370.64659986187695,pitch:-20};          
//                myPano = new GStreetviewPanorama(document.getElementById("pano"));
//                myPano.setLocationAndPOV(new GLatLng(' . $lat . ', ' . $lng . '), fenwayPOV);
//                GEvent.addListener(myPano, "error", handleNoFlash);  
//                panoClient.getNearestPanorama(new GLatLng(' . $lat . ', ' . $lng . '),showPanoData);
//		}';
//               
//       echo 'function showPanoData(panoData) {
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
//       echo 'function handleNoFlash(errorCode) {
//                       if (errorCode == 603) {
//                       alert("Error: Flash doesnt appear to be supported by your browser");
//                       return;
//                     }
//                 }
//		</script>';
	echo '<body onload="load()" onunload="GUnload()">';
}
unset($result);
$msg = "";
if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_POST['Search']) OR isset($_POST['CSV']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']) OR isset($_GET['CustCode']) ) {
	if (isset($_POST['Search'])) {
		$_POST['PageOffset'] = 1;
	}
	if (strlen($_POST['Keywords'])>0) {
		$msg = _('Search Result: Customer Name has been used in search') . '<br />';
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
	} elseif (strlen($_POST['CustCode'])>0) {
		$msg = _('Search Result: Customer Code has been used in search') . '<br />';
	} elseif (strlen($_GET['CustCode'])>0) {
		$msg = _('Search Result: Customer Code has been used in search') . '<br />';
                $_POST['CustCode']=$_GET['CustCode'];
	} 
          elseif (strlen($_POST['CustPhone'])>0) {
		$msg = _('Search Result: Customer Phone has been used in search') . '<br />';
	}elseif (strlen($_POST['CustAdd'])>0) {
		$msg = _('Search Result: Customer Address has been used in search') . '<br />';
	}elseif (strlen($_POST['CustBranchContact'])>0) {
		$msg = _('Search Result: Customer Branch Contact has been used in search') . '<br />';
	} elseif (isset($_POST['CustType']) AND $_POST['CustType']!='ALL') {
		$msg = _('Search Result: Customer Type has been used in search') . '<br />';
	} elseif (isset($_POST['Area']) AND $_POST['Area']!='ALL') {
		$msg = _('Search Result: Customer branch area has been used in search') . '<br />';
	} elseif (isset($_POST['SalesTypes']) AND $_POST['SalesTypes']!='ALL') {
		$msg = _('Search Result: Customer sales type has been used in search') . '<br />';
	}
	if (($_POST['Keywords'] == '') AND ($_POST['CustCode'] == '') AND ($_POST['CustPhone'] == '') AND ($_POST['CustType'] == 'ALL') AND ($_POST['Area'] == 'ALL') AND ($_POST['SalesTypes'] == 'ALL') AND ($_POST['CustAdd'] == '') AND ($_POST['CustBranchContact'] == '') ) {
		//no criteria set then default to all customers
		$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
			ON debtorsmaster.debtorno = custbranch.debtorno
			INNER JOIN debtortype
			ON debtorsmaster.typeid = debtortype.typeid";
	} else {
		if (strlen($_POST['Keywords']) > 0) {
			//using the customer name
			$_POST['Keywords'] = strtoupper(trim($_POST['Keywords']));
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = "SELECT debtorsmaster.debtorno,
						debtorsmaster.name,
						debtorsmaster.address1,
						debtorsmaster.address2,
						debtorsmaster.address3,
						debtorsmaster.address4,
						custbranch.branchcode,
						custbranch.brname,
						custbranch.contactname,
						debtortype.typename,
						custbranch.phoneno,
						custbranch.faxno
					FROM debtorsmaster LEFT JOIN custbranch
						ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
					WHERE debtorsmaster.name " . LIKE . " '" . $SearchString . "'
					AND debtorsmaster.typeid = debtortype.typeid";
		} elseif (strlen($_POST['CustCode']) > 0) {
			$_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));
			$SQL = "SELECT debtorsmaster.debtorno,
						debtorsmaster.name,
						debtorsmaster.address1,
						debtorsmaster.address2,
						debtorsmaster.address3,
						debtorsmaster.address4,
						custbranch.branchcode,
						custbranch.brname,
						custbranch.contactname,
						debtortype.typename,
						custbranch.phoneno,
						custbranch.faxno
					FROM debtorsmaster LEFT JOIN custbranch
						ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
					WHERE debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
					AND debtorsmaster.typeid = debtortype.typeid";
		} elseif (strlen($_POST['CustPhone']) > 0) {
			$SQL = "SELECT debtorsmaster.debtorno,
						debtorsmaster.name,
						debtorsmaster.address1,
						debtorsmaster.address2,
						debtorsmaster.address3,
						debtorsmaster.address4,
						custbranch.branchcode,
						custbranch.brname,
						custbranch.contactname,
						debtortype.typename,
						custbranch.phoneno,
						custbranch.faxno
					FROM debtorsmaster LEFT JOIN custbranch
						ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
					WHERE custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
					AND debtorsmaster.typeid = debtortype.typeid";
			// Added an option to search by address. I tried having it search address1, address2, address3, and address4, but my knowledge of MYSQL is limited.  This will work okay if you select the CSV Format then you can search though the address1 field. I would like to extend this to all 4 address fields. Gilles Deacur

		} elseif (strlen($_POST['CustAdd']) > 0) {
			$SQL = "SELECT debtorsmaster.debtorno,
							debtorsmaster.name,
							debtorsmaster.address1,
							debtorsmaster.address2,
							debtorsmaster.address3,
							debtorsmaster.address4,
							custbranch.branchcode,
							custbranch.brname,
							custbranch.contactname,
							debtortype.typename,
							custbranch.phoneno,
							custbranch.faxno
						FROM debtorsmaster LEFT JOIN custbranch
							ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
						WHERE debtorsmaster.address1 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address2 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address3 "  . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address4 "  . LIKE . " '%" . $_POST['CustAdd'] . "%'
						AND debtorsmaster.typeid = debtortype.typeid";
			// End added search feature. Gilles Deacur

		} elseif (strlen($_POST['CustBranchContact']) > 0) {
			$SearchBranchContactString = '%' . str_replace(' ', '%', $_POST['CustBranchContact']) . '%';
			$SQL = "SELECT debtorsmaster.debtorno,
						debtorsmaster.name,
						debtorsmaster.address1,
						debtorsmaster.address2,
						debtorsmaster.address3,
						debtorsmaster.address4,
						custbranch.branchcode,
						custbranch.brname,
						custbranch.contactname,
						debtortype.typename,
						custbranch.phoneno,
						custbranch.faxno
					FROM debtorsmaster LEFT JOIN custbranch
						ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
					WHERE custbranch.contactname " . LIKE . " '" . $SearchBranchContactString . "'
					AND debtorsmaster.typeid = debtortype.typeid
                                        Group By debtorsmaster.debtorno";
			// End added search feature. Gilles Deacur

		}elseif (strlen($_POST['CustType']) > 0 AND $_POST['CustType']!='ALL') {
			$SQL = "SELECT debtorsmaster.debtorno,
								debtorsmaster.name,
								debtorsmaster.address1,
								debtorsmaster.address2,
								debtorsmaster.address3,
								debtorsmaster.address4,
								custbranch.branchcode,
								custbranch.brname,
								custbranch.contactname,
								debtortype.typename,
								custbranch.phoneno,
								custbranch.faxno
						FROM debtorsmaster LEFT JOIN custbranch
								ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
						WHERE debtorsmaster.typeid LIKE debtortype.typeid
						AND debtortype.typename = '" . $_POST['CustType'] . "'";
		} elseif (strlen($_POST['Area']) > 0 AND $_POST['Area']!='ALL') {
			$SQL = "SELECT debtorsmaster.debtorno,
								debtorsmaster.name,
								debtorsmaster.address1,
								debtorsmaster.address2,
								debtorsmaster.address3,
								debtorsmaster.address4,
								custbranch.branchcode,
								custbranch.brname,
								custbranch.contactname,
								debtortype.typename,
								custbranch.phoneno,
								custbranch.faxno
						FROM debtorsmaster LEFT JOIN custbranch
								ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
						WHERE debtorsmaster.typeid LIKE debtortype.typeid
						AND custbranch.area = '" . $_POST['Area'] . "'";
		} elseif (strlen($_POST['SalesTypes']) > 0 AND $_POST['SalesTypes']!='ALL') { echo $_POST['SalesTypes'];
			$SQL = "SELECT debtorsmaster.debtorno,
								debtorsmaster.name,
								debtorsmaster.address1,
								debtorsmaster.address2,
								debtorsmaster.address3,
								debtorsmaster.address4,
								custbranch.branchcode,
								custbranch.brname,
								custbranch.contactname,
								debtortype.typename,
								custbranch.phoneno,
								custbranch.faxno
						FROM debtorsmaster LEFT JOIN custbranch
								ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
						WHERE debtorsmaster.typeid LIKE debtortype.typeid
						AND debtorsmaster.salestype = '" . $_POST['SalesTypes'] . "'";
		}
	} //one of keywords or custcode or custphone was more than a zero length string
	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL.= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$SQL.= " ORDER BY debtorsmaster.name";
	$ErrMsg = _('The searched customer records requested cannot be retrieved because');
	
	
	$result = DB_query($SQL, $db, $ErrMsg);
	if (DB_num_rows($result) == 1) {
		$myrow = DB_fetch_array($result);
		$_POST['Select'] = $myrow['debtorno'];
		unset($result);
	} elseif (DB_num_rows($result) == 0) {
		prnMsg(_('No customer records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
		echo '<br />';
	}
} //end of if search
if (!isset($_POST['Select'])) {
	$_POST['Select'] = "";
}
$Debtor=explode(' ', $_POST['Select']);
if ($_POST['Select'] != '' OR ($_SESSION['CustomerID'] != '' AND !isset($_POST['Keywords']) AND !isset($_POST['CustCode']) AND !isset($_POST['CustType']) AND !isset($_POST['CustPhone']))) {
	if ($_POST['Select'] != '') {
		$SQL = "SELECT brname, phoneno FROM custbranch WHERE debtorno='" . $Debtor[0] . "'";
		$_SESSION['CustomerID'] = $Debtor[0];
                echo "<meta http-equiv='refresh' content='0;URL=SelectCustomer.php'>"; 
	} else {
		$SQL = "SELECT debtorsmaster.name, 
						custbranch.phoneno 
				FROM debtorsmaster, custbranch 
				WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "' 
				AND debtorsmaster.debtorno = custbranch.debtorno";
	}
	$ErrMsg = _('The customer name requested cannot be retrieved because');
	$result = DB_query($SQL, $db, $ErrMsg);
	if ($myrow = DB_fetch_row($result)) {
		$CustomerName = $myrow[0];
		$phone = $myrow[1];
	}
	unset($result);
	// Adding customer encoding. Not needed for general use. This is not a recommended upgrade submission. Gilles Deacur
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Customer') . ' : ' . $_SESSION['CustomerID'] . ' - ' . $CustomerName . ' - ' . $phone . _(' has been selected') . '</p>';
	echo '<div class="page_help_text">' . _('Select a menu option to operate using this customer') . '.</div><br />';
	$_POST['Select'] = NULL;
	echo '<table cellpadding=4 width=90% class=selection><tr><th width=33%>' . _('Customer Inquiries') . '</th>
			<th width=33%>' . _('Customer Transactions') . '</th>
			<th width=33%>' . _('Customer Maintenance') . '</th></tr>';
	echo '<tr><td valign=top class="select">';
	/* Customer Inquiry Options */
	echo '<a href="' . $rootpath . '/CustomerInquiry.php?CustomerID=' . $_SESSION['CustomerID'] . '">' . _('Customer Transaction Inquiries') . '</a><br />';
	echo '<a href="' . $rootpath . '/Customers.php?DebtorNo=' . $_SESSION['CustomerID'] . '&Modify=No">' . _('View Customer Details') . '</a><br />';
	echo '<a href="' . $rootpath . '/PrintCustStatements.php?FromCust=' . $_SESSION['CustomerID'] . '&ToCust=' . $_SESSION['CustomerID'] . '&PrintPDF=Yes">' . _('Print Customer Statement') . '</a><br />';
	echo '<a href="' . $rootpath . '/SelectCompletedOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Order Inquiries') . '</a><br />';
	wikiLink('Customer', $_SESSION['CustomerID']);
	echo '</td><td valign=top class="select">';
	echo '<a href="' . $rootpath . '/SelectSalesOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Modify Outstanding Sales Orders') . '</a><br />';
	echo '<a href="' . $rootpath . '/CustomerAllocations.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Allocate Receipts or Credit Notes') . '</a><br />';
	echo '</td><td valign=top class=select>';
	echo '<a href="' . $rootpath . '/Customers.php?">' . _('Add a New Customer') . '</a><br />';
	echo '<a href="' . $rootpath . '/Customers.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Modify Customer Details') . '</a><br />';
	echo '<a href="' . $rootpath . '/CustomerBranches.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add/Modify/Delete Customer Branches') . '</a><br />';
	echo '<a href="' . $rootpath . '/SelectProduct.php">' . _('Special Customer Prices') . '</a><br />';
	echo '<a href="' . $rootpath . '/CustEDISetup.php">' . _('Customer EDI Configuration') . '</a><br />';
	echo '<a href="' . $rootpath . '/CustLoginSetup.php">' . _('Customer Login Configuration') . '</a>';
	echo '</td>';
	echo '</tr></table><br />';
} else {
	echo '<table width=90%><tr><th width=33%>' . _('Customer Inquiries') . '</th>
			<th width=33%>' . _('Customer Transactions') . '</th>
			<th width=33%>' . _('Customer Maintenance') . '</th></tr>';
	echo '<tr><td class="select">';
	echo '</td><td class="select">';
	echo '</td><td class="select">';
	if (!isset($_SESSION['SalesmanLogin']) or $_SESSION['SalesmanLogin'] == '') {
		echo '<a href="' . $rootpath . '/Customers.php?">' . _('Add a New Customer') . '</a><br />';
	}
	echo '</td></tr></table>';
}
echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (strlen($msg)>1){
	prnMsg($msg,'info');
}
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Customers').'</p>';
echo '<table cellpadding="3" colspan="4" class="selection">';
echo '<tr><td colspan="2">' . _('Enter a partial Name') . ':</td><td>';
if (isset($_POST['Keywords'])) {
	echo '<input type="Text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25">';
} else {
	echo '<input type="Text" name="Keywords" size="20" maxlength="25">';
}
echo '</td><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Code') . ':</td><td>';
if (isset($_POST['CustCode'])) {
	echo '<input type="Text" name="CustCode" value="' . $_POST['CustCode'] . '" size="15" maxlength="18">';
} else {
	echo '<input type="Text" name="CustCode" size="15" maxlength="18">';
}
echo '</td></tr><tr><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Phone Number') . ':</td><td>';
if (isset($_POST['CustPhone'])) {
	echo '<input type="Text" name="CustPhone" value="' . $_POST['CustPhone'] . '" size="15" maxlength="18">';
} else {
	echo '<input type="Text" name="CustPhone" size="15" maxlength="18">';
}
echo '</td>';
echo '<td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter part of the Address') . ':</td><td>';
if (isset($_POST['CustAdd'])) {
	echo '<input type="Text" name="CustAdd" value="' . $_POST['CustAdd'] . '" size=20 maxlength=25>';
} else {
	echo '<input type="Text" name="CustAdd" size=20 maxlength=25>';
}
echo '</td></tr>';
echo '<tr><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Branch Contact') . ':</td><td>';
if (isset($_POST['CustBranchContact'])) {
	echo '<input type="Text" name="CustBranchContact" value="' . $_POST['CustBranchContact'] . '" size="20" maxlength="25">';
} else {
	echo '<input type="Text" name="CustBranchContact" size="20" maxlength="25">';
}
echo '</td><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Choose a Type') . ':</td><td>';
if (isset($_POST['CustType'])) {
	// Show Customer Type drop down list
	$result2 = DB_query('SELECT typeid, typename FROM debtortype', $db);
	// Error if no customer types setup
	if (DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="CustomerTypes.php?" target="_parent">Setup Types</a>';
		echo '<tr><td colspan=2>' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
	} else {
		// If OK show select box with option selected
		echo '<select name="CustType">';
		echo '<option value="ALL">' . _('Any') . '</option>';
		while ($myrow = DB_fetch_array($result2)) {
			if ($_POST['CustType'] == $myrow['typename']) {
				echo '<option selected value="' . $myrow['typename'] . '">' . $myrow['typename']  . '</option>';
			} else {
				echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename']  . '</option>';
			}
		} //end while loop
		DB_data_seek($result2, 0);
		echo '</select></td>';
	}
} else {
	// No option selected yet, so show Customer Type drop down list
	$result2 = DB_query('SELECT typeid, typename FROM debtortype', $db);
	// Error if no customer types setup
	if (DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="CustomerTypes.php?" target="_parent">Setup Types</a>';
		echo '<tr><td colspan=2>' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
	} else {
		// if OK show select box with available options to choose
		echo '<select name="CustType">';
		echo '<option value="ALL">' . _('Any'). '</option>';
		while ($myrow = DB_fetch_array($result2)) {
			echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
		} //end while loop
		DB_data_seek($result2, 0);
		echo '</select></td></tr>';
	}
}



/* Option to select a sales type */
echo '<tr><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Choose an Sales Type') . ':</td><td>';
$result2 = DB_query('SELECT typeabbrev, sales_type FROM salestypes order by typeabbrev', $db);
// Error if no sales areas setup
if (DB_num_rows($result2) == 0) {
	$DataError = 1;
	echo '<a href="SalesTypes.php?" target="_parent">' . _('Setup Types') . '</a>';
	echo '<tr><td colspan=2>' . prnMsg(_('No Sales Types defined'), 'error') . '</td>';
} else {
	// if OK show select box with available options to choose
	echo '<select name="SalesTypes">';
	echo '<option value="ALL">' . _('Any') . '</option>';
	while ($myrow = DB_fetch_array($result2)) {
		if (isset($_POST['SalesTypes']) and $_POST['SalesTypes']==$myrow['typeabbrev']) {
			echo '<option selected value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
		} else {
			echo '<option value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
		}
	} //end while loop
	DB_data_seek($result2, 0);
	echo '</select></td>';
}
/* Option to select a sales area */
echo '<td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Choose an Area') . ':</td><td>';
$result2 = DB_query('SELECT areacode, areadescription FROM areas', $db);
// Error if no sales areas setup
if (DB_num_rows($result2) == 0) {
	$DataError = 1;
	echo '<a href="Areas.php?" target="_parent">' . _('Setup Types') . '</a>';
	echo '<tr><td colspan=2>' . prnMsg(_('No Sales Areas defined'), 'error') . '</td></tr>';
} else {
	// if OK show select box with available options to choose
	echo '<select name="Area">';
	echo '<option value="ALL">' . _('Any') . '</option>';
	while ($myrow = DB_fetch_array($result2)) {
		if (isset($_POST['Area']) and $_POST['Area']==$myrow['areacode']) {
			echo '<option selected value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		}
	} //end while loop
	DB_data_seek($result2, 0);
	echo '</select></td></tr>';
}

echo '</table><br />';
echo '<div class="centre"><input type=submit name="Search" value="' . _('Search Now') . '"><input type=submit name="CSV" value="' . _('CSV Format') . '"></div>';
if (isset($_SESSION['SalesmanLogin']) and $_SESSION['SalesmanLogin'] != '') {
	prnMsg(_('Your account enables you to see only customers allocated to you'), 'warn', _('Note: Sales-person Login'));
}
if (isset($result)) {
	unset($_SESSION['CustomerID']);
	$ListCount = DB_num_rows($result);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (!isset($_POST['CSV'])) {
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
		echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
		if ($ListPageMax > 1) {
			echo '<p><div class=centre>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset1">';
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
				<input type=submit name="Go1" value="' . _('Go') . '">
				<input type=submit name="Previous" value="' . _('Previous') . '">
				<input type=submit name="Next" value="' . _('Next') . '">';
			echo '</div>';
		}
		echo '<br /><table cellpadding=2 colspan=7 class=selection>';
		$TableHeader = '<tr>
				<th>' . _('Code') . '</th>
				<th>' . _('Customer Name') . '</th>
				<th>' . _('Branch') . '</th>
				<th>' . _('Contact') . '</th>
				<th>' . _('Type') . '</th>
				<th>' . _('Phone') . '</th>
				<th>' . _('Fax') . '</th>
			</tr>';
		echo $TableHeader;
		$j = 1;
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
	}
	if (DB_num_rows($result) <> 0) {
		if (isset($_POST['CSV'])) {
			$FileName = $_SESSION['reports_dir'] . '/Customer_Listing_' . Date('Y-m-d') . '.csv';
			echo '<br /><p class="page_title_text"><a href="' . $FileName . '">' . _('Click to view the csv Search Result') . '</p>';
			$fp = fopen($FileName, 'w');
			while ($myrow2 = DB_fetch_array($result)) {
				fwrite($fp, $myrow2['debtorno'] . ',' . str_replace(',', '', $myrow2['name']) . ',' . str_replace(',', '', $myrow2['address1']) . ',' . str_replace(',', '', $myrow2['address2']) . ',' . str_replace(',', '', $myrow2['address3']) . ',' . str_replace(',', '', $myrow2['address4']) . ',' . str_replace(',', '', $myrow2['contactname']) . ',' . str_replace(',', '', $myrow2['typename']) . ',' . $myrow2['phoneno'] . ',' . $myrow2['faxno'] . "\n");
			}
			echo '</div>';
		}
		if (!isset($_POST['CSV'])) {
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
			echo '<td><font size=1><input type=submit name="Select" value="' . $myrow['debtorno'].' '.$myrow['branchcode'] . '"></font></td>
				<td><font size=1>' . $myrow['name'] . '</font></td>
				<td><font size=1>' . $myrow['brname'] . '</font></td>
				<td><font size=1>' . $myrow['contactname'] . '</font></td>
				<td><font size=1>' . $myrow['typename'] . '</font></td>
				<td><font size=1>' . $myrow['phoneno'] . '</font></td>
				<td><font size=1>' . $myrow['faxno'] . '</font></td></tr>';
			$j++;
			if ($j == 11 AND ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $TableHeader;
			}
			$RowIndex++;
			//end of page full new headings if

		}
		//end of while loop
		echo '</table>';
	}
}
//end if results to show
if (!isset($_POST['CSV'])) {
	if (isset($ListPageMax) and $ListPageMax > 1) {
		echo '<p><div class=centre>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset2">';
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
			<input type=submit name="Go2" value="' . _('Go') . '">
			<input type=submit name="Previous" value="' . _('Previous') . '">
			<input type=submit name="Next" value="' . _('Next') . '">';
	}
	//end if results to show
	echo '</div></form>';
}
// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
if (isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != "") {
	if ($_SESSION['geocode_integration'] == 1) {
		echo '<br />';
		if ($lat == 0) {
			echo '<div class="centre">' . _('Mapping is enabled, but no Mapping data to display for this Customer.') . '</div>';
		} else {
			echo '<tr><td colspan=2>';
			echo '<table width=45% colspan=2 cellpadding=4>';
			echo '<tr><th width=33%>' . _('Customer Mapping') . '</th></tr>';
			echo '</td><td valign="top">'; /* Mapping */
			echo '<div class="centre">' . _('Mapping is enabled, Map will display below.') . '</div>';
			echo '<div align="center" id="map" style="width: ' . $map_width . 'px; height: ' . $map_height . 'px"></div><br />';
                        echo '<div class="centre" id="pano" style="width: ' . $map_width . 'px; height: ' . $map_height . 'px"></div>';   
			echo '</th></tr></table>';
		}
	}
	// Extended Customer Info only if selected in Configuration
	if ($_SESSION['Extended_CustomerInfo'] == 1) {
		if ($_SESSION['CustomerID'] != "") {
			$sql = "SELECT debtortype.typeid, debtortype.typename
						FROM debtorsmaster, debtortype
					WHERE debtorsmaster.typeid = debtortype.typeid
					AND debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";
			$ErrMsg = _('An error occurred in retrieving the information');
			$result = DB_query($sql, $db, $ErrMsg);
			$myrow = DB_fetch_array($result);
			$CustomerType = $myrow['typeid'];
			$CustomerTypeName = $myrow['typename'];
			// Customer Data
			echo '<br />';
			// Select some basic data about the Customer
			$SQL = "SELECT debtorsmaster.clientsince,
						(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.clientsince))) as customersincedays,
						(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.lastpaiddate))) as lastpaiddays,
						debtorsmaster.paymentterms, debtorsmaster.lastpaid, debtorsmaster.lastpaiddate
					FROM debtorsmaster
					WHERE debtorsmaster.debtorno ='" . $_SESSION['CustomerID'] . "'";
			$DataResult = DB_query($SQL, $db);
			$myrow = DB_fetch_array($DataResult);
			// Select some more data about the customer
			$SQL = "SELECT sum(ovamount+ovgst) as total 
					FROM debtortrans 
					WHERE debtorno = '" . $_SESSION['CustomerID'] . "' 
					AND type !=12";
			$Total1Result = DB_query($SQL, $db);
			$row = DB_fetch_array($Total1Result);
			echo '<tr><td colspan=2>';
			echo '<table width=45% colspan=2 cellpadding=4>';
			echo '<tr><th width=33% colspan=3>' . _('Customer Data') . '</th></tr>';
			echo '<tr><td valign=top class=select>'; /* Customer Data */
			if ($myrow['lastpaiddate'] == 0) {
				echo _('No receipts from this customer.') . '</td><td class=select></td><td class=select></td></tr>';
			} else {
				echo _('Last Paid Date:') . '</td><td class=select> <b>' . ConvertSQLDate($myrow['lastpaiddate']) . '</b> </td><td class=select>' . $myrow['lastpaiddays'] . ' ' . _('days') . '</td></tr>';
			}
			echo '<tr><td class=select>' . _('Last Paid Amount (inc tax):') . '</td><td class=select> <b>' . number_format($myrow['lastpaid'], 2) . '</b></td><td class=select></td></tr>';
			echo '<tr><td class=select>' . _('Customer since:') . '</td><td class=select> <b>' . ConvertSQLDate($myrow['clientsince']) . '</b> </td><td class=select>' . $myrow['customersincedays'] . ' ' . _('days') . '</td></tr>';
			if ($row['total'] == 0) {
				echo '<tr><td class=select>' . _('No Spend from this Customer.') . '</b></td><td class=select></td><td class=select></td></tr>';
			} else {
				echo '<tr><td class=select>' . _('Total Spend from this Customer (inc tax):') . ' </td><td class=select><b>' . number_format($row['total'], 2) . '</b></td><td class=select></td></tr>';
			}
			echo '<tr><td class=select>' . _('Customer Type:') . ' </td><td class=select><b>' . $CustomerTypeName . '</b></td><td class=select></td></tr>';
			echo '</th></tr></table>';
		}
		// Customer Contacts
		echo '<tr><td colspan=2>';
		$sql = "SELECT * FROM custcontacts 
				WHERE debtorno='" . $_SESSION['CustomerID'] . "' 
				ORDER BY contid";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) <> 0) {
			echo '<table width=45%>';
			echo '<br /><th colspan=7><img src="' . $rootpath . '/css/' . $theme . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt="">' . ' ' . _('Customer Contacts') . '</th>';
			echo '<tr>
					<th>' . _('Name') . '</th>
					<th>' . _('Role') . '</th>
					<th>' . _('Phone Number') . '</th>
					<th>' . _('Notes') . '</th>
					<th>' . _('Edit') . '</th>
					<th>' . _('Delete') . '</th>
					<th> <a href="AddCustomerContacts.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add New Contact') . '</a> </th>
				</tr>';
			$k = 0; //row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $myrow[2] . '</td>
					<td>' . $myrow[3] . '</td>
					<td>' . $myrow[4] . '</td>
					<td>' . $myrow[5] . '</td>
					<td><a href="AddCustomerContacts.php?Id=' . $myrow[0] . '&DebtorNo=' . $myrow[1] . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerContacts.php?Id=' . $myrow[0] . '&DebtorNo=' . $myrow[1] . '&delete=1">' . _('Delete') . '</a></td>
					</tr>';
			} //END WHILE LIST LOOP
			echo '</table>';
		} else {
			if ($_SESSION['CustomerID'] != "") {
				echo '<br /><div class="centre"><img src="' . $rootpath . '/css/' . $theme . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt=""><a href="AddCustomerContacts.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Contact') . '</a></div>';
			}
		}
		// Customer Notes
		echo '<tr><td colspan=2>';
		$sql = "SELECT * FROM custnotes 
				WHERE debtorno='" . $_SESSION['CustomerID'] . "' 
				ORDER BY date DESC";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $rootpath . '/css/' . $theme . '/images/note_add.png" title="' . _('Customer Notes') . '" alt="">' . ' ' . _('Customer Notes') . '</div><br />';
			echo '<table width=45%>';
			echo '<tr>
					<th>' . _('date') . '</th>
					<th>' . _('note') . '</th>
					<th>' . _('hyperlink') . '</th>
					<th>' . _('priority') . '</th>
					<th>' . _('Edit') . '</th>
					<th>' . _('Delete') . '</th>
					<th> <a href="AddCustomerNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Note') . '</a> </th>
				</tr>';
			$k = 0; //row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $myrow[4] . '</td>
					<td>' . $myrow[3] . '</td>
					<td>' . $myrow[2] . '</td>
					<td>' . $myrow[5] . '</td>
					<td><a href="AddCustomerNotes.php?Id=' . $myrow[0] . '&DebtorNo=' . $myrow[1] . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerNotes.php?Id=' . $myrow[0] . '&DebtorNo=' . $myrow[1] . '&delete=1">' . _('Delete') . '</a></td>
					</tr>';
			} //END WHILE LIST LOOP
			echo '</table>';
		} else {
			if ($_SESSION['CustomerID'] != "") {
				echo '<br /><div class="centre"><img src="' . $rootpath . '/css/' . $theme . '/images/note_add.png" title="' . _('Customer Notes') . '" alt=""><a href="AddCustomerNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Note for this Customer') . '</a></div>';
			}
		}
		// Custome Type Notes
		echo '<tr><td colspan=2>';
		$sql = "SELECT * FROM debtortypenotes where typeid='" . $CustomerType . "' 
				ORDER BY date DESC";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $rootpath . '/css/' . $theme . '/images/folder_add.png" title="' . _('Customer Type (Group) Notes') . '" alt="">' . ' ' . _('Customer Type (Group) Notes for:' . '<b> ' . $CustomerTypeName . '</b>') . '</div><br />';
			echo '<table width=45%>';
			echo '<tr>
			 	<th>' . _('date') . '</th>
			  	<th>' . _('note') . '</th>
			   	<th>' . _('file link / reference / URL') . '</th>
			   	<th>' . _('priority') . '</th>
			   	<th>' . _('Edit') . '</th>
			   	<th>' . _('Delete') . '</th>
			   	<th><a href="AddCustomerTypeNotes.php?DebtorType=' . $CustomerType . '">' . _('Add New Group Note') . '</a></th></tr>';
			$k = 0; //row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $myrow[4] . '</td>
					<td>' . $myrow[3] . '</td>
					<td>' . $myrow[2] . '</td>
					<td>' . $myrow[5] . '</td>
					<td><a href="AddCustomerTypeNotes.php?Id=' . $myrow[0] . '&DebtorType=' . $myrow[1] . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerTypeNotes.php?Id=' . $myrow[0] . '&DebtorType=' . $myrow[1] . '&delete=1">' . _('Delete') . '</a></td>
					</tr>';
			} //END WHILE LIST LOOP
			echo '</table>';
		} else {
			if ($_SESSION['CustomerID'] != "") {
				echo '<br /><div class="centre"><img src="' . $rootpath . '/css/' . $theme . '/images/folder_add.png" title="' . _('Customer Group Notes') . '" alt=""><a href="AddCustomerTypeNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Group Note') . '</a></div><br />';
			}
		}
	}
}
echo '<script>defaultControl(document.forms[0].CustCode);</script>';
include ('includes/footer.inc');
?>