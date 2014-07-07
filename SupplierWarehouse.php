<?php

/* $Id: CustomerBranches.php 4591 2011-06-09 10:33:38Z daintree $*/

include('includes/session.inc');
$title = _('Supplier Warehouses');
include('includes/header.inc');

if (isset($_GET['SupplierID'])) {
	$SuppCode = strtoupper($_GET['SupplierID']);
} else if (isset($_POST['SupplierID'])){ 
	$SuppCode = strtoupper($_POST['SupplierID']);
}

if (!isset($SuppCode)) {
	prnMsg(_('This page must be called with the supplier code of the supplier for whom you wish to edit the warehouse for').'.
		<br />'._('When the pages is called from within the system this will always be the case').' <br />'.
			_('Select a supplier first then select the link to add/edit/delete warehouse'),'warn');
	include('includes/footer.inc');
	exit;
}


if (isset($_GET['SelectedWarehouse'])){
	$SelectedWarehouse = strtoupper($_GET['SelectedWarehouse']);
} else if (isset($_POST['SelectedWarehouse'])){
	$SelectedWarehouse = strtoupper($_POST['SelectedWarehouse']);
}

if (isset($Errors)) {
	unset($Errors);
}

	//initialise no input errors assumed initially before we test
$Errors = array();
$InputError = 0;

if (isset($_POST['submit'])) {

	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['WarehouseCode'] = strtoupper($_POST['WarehouseCode']);

	if (ContainsIllegalCharacters($_POST['WarehouseCode']) OR strstr($_POST['WarehouseCode'],' ')) {
		$InputError = 1;
		prnMsg(_('The Warehouse code cannot contain any of the following characters')." -  & \'",'error');
		$Errors[$i] = 'WarehouseCode';
		$i++;
	}
	if (strlen($_POST['WarehouseCode'])==0) {
		$InputError = 1;
		prnMsg(_('The Warehouse code must be at least one character long'),'error');
		$Errors[$i] = 'WarehouseCode';
		$i++;
	}
	if (!isset($latitude)) {
		$latitude=0.0;
		$longitude=0.0;
	}
//	if ($_SESSION['geocode_integration']==1 ){
//		// Get the lat/long from our geocoding host
//		$sql = "SELECT * FROM geocode_param WHERE 1";
//		$ErrMsg = _('An error occurred in retrieving the information');
//		$resultgeo = DB_query($sql, $db, $ErrMsg);
//		$row = DB_fetch_array($resultgeo);
//		$api_key = $row['geocode_key'];
//		$map_host = $row['map_host'];
//		define('MAPS_HOST', $map_host);
//		define('KEY', $api_key);
//		if ($map_host=="") {
//		// check that some sane values are setup already in geocode tables, if not skip the geocoding but add the record anyway.
//			echo '<div class="warn">' . _('Warning - Geocode Integration is enabled, but no hosts are setup.  Go to Geocode Setup') . '</div>';
//                } else {
//			$address = $_POST['BrAddress1'] . ", " . $_POST['BrAddress2'] . ", " . $_POST['BrAddress3'] . ", " . $_POST['BrAddress4'];
//			$base_url = 'http://' . MAPS_HOST . '/maps/geo?output=xml&key=' . KEY;
//			$request_url = $base_url . '&q=' . urlencode($address);
//			$xml = simplexml_load_string(utf8_encode(file_get_contents($request_url))) or die("url not loading");
//			$coordinates = $xml->Response->Placemark->Point->coordinates;
//			$coordinatesSplit = explode(",", $coordinates);
//			// Format: Longitude, Latitude, Altitude
//			$latitude = $coordinatesSplit[1];
//			$longitude = $coordinatesSplit[0];
//			
//			$status = $xml->Response->Status->code;
//			if (strcmp($status, '200') == 0) {
//				// Successful geocode
//			    	$geocode_pending = false;
//				$coordinates = $xml->Response->Placemark->Point->coordinates;
//				$coordinatesSplit = explode(",", $coordinates);
//				// Format: Longitude, Latitude, Altitude
//				$latitude = $coordinatesSplit[1];
//				$longitude = $coordinatesSplit[0];
//			} else {
//				// failure to geocode
//				$geocode_pending = false;
//				echo '<div class="page_help_text"><b>' . _('Geocode Notice') . ':</b> ' . _('Address') . ': ' . $address . ' ' . _('failed to geocode');
//				echo _('Received status') . ' ' . $status . '</div>';
//			}
//		}
//	}
	if (isset($SelectedWarehouse) AND $InputError !=1) {

		/*SelectedBranch could also exist if submit had not been clicked this code would not run in this case cos submit is false of course see the 	delete code below*/

		$sql = "UPDATE supplierwarehouse SET warehousecode = '" . $_POST['WarehouseCode'] . "',
						     warehousename = '" . $_POST['WhName'] . "',
						     whaddress1 = '" . $_POST['WhAddress1'] . "',
						     whaddress2 = '" . $_POST['WhAddress2'] . "',
						     whaddress3 = '" . $_POST['WhAddress3'] . "',
						     whaddress4 = '" . $_POST['WhAddress4'] . "',
						     whaddress5 = '" . $_POST['WhAddress5'] . "',
						     whaddress6 = '" . $_POST['WhAddress6'] . "',
						     phoneno = '" . $_POST['PhoneNo'] . "',
						     faxno ='" . $_POST['FaxNo'] . "',
						     contactname='" . $_POST['ContactName'] . "',
						     email= '" . $_POST['Email'] . "',
					             comment ='" . $_POST['Comment'] . "',
                                                     alt1email= '".  $_POST['Alt1Email'] . "',  
                                                     alt2email= '".  $_POST['Alt2Email'] . "',  
                                                     alt3email= '".  $_POST['Alt3Email'] . "'    
			WHERE warehousecode = '".$SelectedWarehouse."' AND supplierid='".$SuppCode."'";

		$msg = $_POST['WhName'] . ' '._('warehouse has been updated.');

	} else if ($InputError !=1) {

	/*Selected branch is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Customer Branches form */

        $sql = "INSERT INTO supplierwarehouse  (warehousecode,
						supplierid,
                                                warehousename,
                                                whaddress1,
                                                whaddress2,
                                                whaddress3,
                                                whaddress4,
                                                whaddress5,
                                                whaddress6,
                                                phoneno,
                                                faxno,
                                                contactname,
                                                email,
                                                comment,
                                                lat,
                                                lng,
                                                alt1email,
                                                alt2email,
                                                alt3email
						)
				VALUES ('" . $_POST['WarehouseCode'] . "',
					'" . $SuppCode . "',
					'" . $_POST['WhName'] . "',
					'" . $_POST['WhAddress1'] . "',
					'" . $_POST['WhAddress2'] . "',
					'" . $_POST['WhAddress3'] . "',
					'" . $_POST['WhAddress4'] . "',
					'" . $_POST['WhAddress5'] . "',
					'" . $_POST['WhAddress6'] . "',
					'" . $_POST['PhoneNo'] . "',
					'" . $_POST['FaxNo'] . "',
					'" . $_POST['ContactName'] . "',
					'" . $_POST['Email'] . "',
                                        '" . $_POST['Comment'] . "',
					'" . $latitude . "',
					'" . $longitude . "',
                                        '" . $_POST['Alt1Email'] . "',
                                        '" . $_POST['Alt2Email'] . "',
                                        '" . $_POST['Alt3Email'] . "'   
					)";
	}
	echo '<br />';
	$msg = _('Supplier warehouse').'<b> ' . $_POST['BranchCode'] . ': ' . $_POST['BrName'] . ' </b>'._('has been added, add another branch, or return to the') . ' <a href="index.php">' . _('Main Menu') . '</a>';

	//run the SQL from either of the above possibilites

	$ErrMsg = _('The warehouse record could not be inserted or updated because');
	if ($InputError==0) {
		$result = DB_query($sql,$db, $ErrMsg);
	}

	if (DB_error_no($db) ==0 and $InputError==0) {
		prnMsg($msg,'success');
		unset($_POST['WarehouseCode']);
		unset($_POST['WhName']);
		unset($_POST['WhAddress1']);
		unset($_POST['WhAddress2']);
		unset($_POST['WhAddress3']);
		unset($_POST['WhAddress4']);
		unset($_POST['WhAddress5']);
		unset($_POST['WhAddress6']);
		unset($_POST['PhoneNo']);
		unset($_POST['FaxNo']);
		unset($_POST['ContactName']);
		unset($_POST['Email']);
		unset($_POST['Comment']);
                unset($_POST['Alt1Email']);
                unset($_POST['Alt2Email']);
                unset($_POST['Alt3Email']);
		unset($SelectedWarehouse);
    }
} else if (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

	$sql= "SELECT COUNT(*) FROM purchorders WHERE supwarehouseno='".$SelectedWarehouse."' AND supplierno = '".$SuppCode."'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this warehouse because PO transactions have been created to this warehouse') . '<br />' .
			 _('There are').' ' . $myrow[0] . ' '._('PO transactions with this Warehouse Code'),'error');

	} else {
		$sql="DELETE FROM supplierwarehouse WHERE warehousecode='" . $SelectedWarehouse . "' AND supplierid='" . $SuppCode . "'";
		      $ErrMsg = _('The branch record could not be deleted') . ' - ' . _('the SQL server returned the following message');
    		$result = DB_query($sql,$db,$ErrMsg);
		if (DB_error_no($db)==0){
		   prnMsg(_('Warehouse Deleted'),'success');
		
	} //end ifs to test if the branch can be deleted

      }
}
if (!isset($SelectedWarehouse)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedBranch will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of branches will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT warehousecode,
                       supplierid,
                       warehousename,
                       whaddress1,
                       whaddress2,
                       whaddress3,
                       whaddress4,
                       phoneno,
                       faxno,
                       contactname,
                       email,
                       comment,
                       alt1email,
                       alt2email,
                       alt3email
                       FROM  supplierwarehouse where supplierid='".$SuppCode."'";

	$result = DB_query($sql,$db);
        if (DB_num_rows($result)!=0) {
		echo '<p Class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Customer') .
			'" alt="" />' . ' ' . _('Branches defined for'). ' '. $SuppCode . ' - ' . $myrow[0] . '</p>';
		echo '<table class=selection>';
		
		echo '<tr><th>'._('Code').'</th>
				<th>'._('Name').'</th>
				<th>'._('Branch Contact').'</th>
                                <th>'._('Street').'</th>
                                <th>'._('Suburb/City').'</th>
                                <th>'._('State').'</th>
                                <th>'._('Post Code').'</th>    
				<th>'._('Phone No').'</th>
				<th>'._('Fax No').'</th>
				<th>'._('Email').'</th></tr>';

		$k=0;
		while ($myrow = DB_fetch_array($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
                                <td>%s</td>
                                <td>%s</td>
				<td><a href="Mailto:%s">%s</a></td>
				<td><a href="%s?SupplierID=%s&SelectedWarehouse=%s">%s</td>
				<td><a href="%s?SupplierID=%s&SelectedWarehouse=%s&delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this warehouse?') . '");\'>%s</td></tr>',
				$myrow['warehousecode'],
				$myrow['warehousename'],
				$myrow['contactname'],
				$myrow['whaddress1'],
				$myrow['whaddress2'],
				$myrow['whaddress3'],
				$myrow['whaddress4'],
				$myrow['phoneno'],
				$myrow['faxno'],
				$myrow['email'],
                                $myrow['email'],
				$_SERVER['PHP_SELF'],
				$SuppCode,
				urlencode($myrow['warehousecode']),
				_('Edit'),
				$_SERVER['PHP_SELF'],
				$SuppCode,
				urlencode($myrow['warehousecode']),
				_('Delete Warehouse'));
			


				$TotalWarehouse++; 


		} 
		//END WHILE LIST LOOP
		echo '</table><br /><table class=selection><tr><td><div class="centre">';
	
		echo '<b>'.$TotalWarehouse. '</b> ' . _('Total Branches') . '</div></td></tr></table>';
	} else {
		$sql = "SELECT  suppname,
				address1,
				address2,
				address3,
				address4,
				address5,
				address6
			FROM suppliers
			WHERE supplierid = '".$SuppCode."'";

		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		echo '<div class="page_help_text">'._('No Warehouse are defined for').' - '.$myrow[0]. '. ' . _('Please add a Warehouse now.') .'</div>';
		$_POST['WarehouseCode'] = substr($SuppCode,0,10);
		$_POST['WhName'] = $myrow[0];
		$_POST['WhAddress1'] = $myrow[1];
		$_POST['WhAddress2'] = $myrow[2];
		$_POST['WhAddress3'] = $myrow[3];
		$_POST['WhAddress4'] = $myrow[4];
		$_POST['WhAddress5'] = $myrow[5];
		$_POST['WhAddress6'] = $myrow[6];
		unset($myrow);
	}
}

if (!isset($_GET['delete'])) {
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .'">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedWarehouse)) {
		//editing an existing branch

		$sql = "SELECT warehousecode,
                               supplierid,
                               warehousename,
                               whaddress1,
                               whaddress2,
                               whaddress3,
                               whaddress4,
                               whaddress5,
                               whaddress6,
                               phoneno,
                               faxno,
                               contactname,
                               email,
                               comment,
                               alt1email,
                               alt2email,
                               alt3email
			FROM supplierwarehouse
			WHERE warehousecode='".$SelectedWarehouse."'
			AND supplierid='".$SuppCode."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		if ($InputError==0) {
			$_POST['WarehouseCode'] = $myrow['warehousecode'];
                        $_POST['SupplierId'] = $myrow['supplierid'];
			$_POST['WhName']  = $myrow['warehousename'];
			$_POST['WhAddress1']  = $myrow['whaddress1'];
			$_POST['WhAddress2']  = $myrow['whaddress2'];
			$_POST['WhAddress3']  = $myrow['whaddress3'];
			$_POST['WhAddress4']  = $myrow['whaddress4'];
			$_POST['WhAddress5']  = $myrow['whaddress5'];
			$_POST['WhAddress6']  = $myrow['whaddress6'];
			$_POST['ContactName'] = $myrow['contactname'];
			$_POST['PhoneNo'] =$myrow['phoneno'];
			$_POST['FaxNo'] =$myrow['faxno'];
			$_POST['Email'] =$myrow['email'];
                        $_POST['Comment']=$myrow['comment'];
                        $_POST['Alt1Email'] =$myrow['alt1email'];
                        $_POST['Alt2Email'] =$myrow['alt2email'];
                        $_POST['Alt3Email'] =$myrow['alt3email'];
		
		}

		echo '<input type=hidden name="SelectedWarehouse" value="' . $SelectedWarehouse . '" />';
		echo '<input type=hidden name="WarehouseCode" value="' . $_POST['WarehouseCode'] . '" />';
		
		echo '<p Class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Customer') .
			'" alt="">' . ' ' . _('Change Details for Branch'). ' '. $SelectedWarehouse . '</p>';
		if (isset($SelectedWarehouse)) {
			echo '<div class="centre"><a href=' . $_SERVER['PHP_SELF'] . '?SupplierID=' . $SuppCode. '>' . _('Show all warehouses defined for'). ' '. $SuppCode . '</a></div>';
		}
		echo '<br><table class="selection">';
		echo '<tr><th colspan=2><div class="centre"><b>'._('Change Branch').'</b></th></tr>';
		echo '<tr><td>'._('Warehouse Code').':</td><td>';

		echo $_POST['WarehouseCode'] . '</td></tr>';

	} else { //end of if $SelectedWarehouse only do the else when a new record is being entered

	/* SETUP ANY $_GET VALUES THAT ARE PASSED.  This really is just used coming from the Customers.php when a new customer is created.
			Maybe should only do this when that page is the referrer?
	*/
            	if (isset($_GET['WarehouseCode'])){
			$sql="SELECT  suppname,
				      address1,
				      address2,
				      address3,
				      address4,
				      address5,
				      address6
			      FROM suppliers
			      WHERE supplierid ='".$_GET['WarehouseCode']."'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
			$_POST['WarehouseCode'] = $_GET['BranchCode'];
			$_POST['WhName']     = $myrow['suppname'];
		 	$_POST['WhAddress1'] = $myrow['addrsss1'];
        	        $_POST['WhAddress2'] = $myrow['addrsss2'];
			$_POST['WhAddress3'] = $myrow['addrsss3'];
		 	$_POST['WhAddress4'] = $myrow['addrsss4'];
        	        $_POST['WhAddress5'] = $myrow['addrsss5'];
			$_POST['WhAddress6'] = $myrow['addrsss6'];
		}
	
		if (!isset($_POST['WarehouseCode'])) {
			$_POST['WarehouseCode']='';
		}
		echo '<p Class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Customer') . '" alt="">' . ' ' . _('Add a Warehouse').'</p>';
		echo '<table class=selection>
				<tr>
					<td>'._('Warehouse Code'). ':</td>
					<td><input ' .(in_array('WarehouseCode',$Errors) ?  'class="inputerror"' : '' ) . ' tabindex=1 type="text" name="WarehouseCode" size=12 maxlength=10 value="' . $_POST['WarehouseCode'] . '"></td>
				</tr>';
	
	}



	echo '<input type=hidden name="SupplierID" value="'. $SuppCode . '" />';

	echo '<tr>
			<td>'._('Warehouse Name').':</td>';
	if (!isset($_POST['WhName'])) {$_POST['WhName']='';}
	echo '<td><input tabindex=2 type="text" name="WhName" size=41 maxlength=40 value="'. $_POST['WhName'].'"></td>
		</tr>';
	echo '<tr>
			<td>'._('Warehouse Contact').':</td>';
	if (!isset($_POST['ContactName'])) {$_POST['ContactName']='';}
	echo '<td><input tabindex=3 type="text" name="ContactName" size=41 maxlength=40 value="'. $_POST['ContactName'].'"></td>
		</tr>';
	echo '<tr><td>'._('Street Address 1 (Street)').':</td>';
	if (!isset($_POST['WhAddress1'])) {$_POST['WhAddress1']='';}
	echo '<td><input tabindex=4 type="text" name="WhAddress1" size=41 maxlength=40 value="'. $_POST['WhAddress1'].'"></td></tr>';
	echo '<tr><td>'._('Street Address 2 (Suburb/City)').':</td>';
	if (!isset($_POST['WhAddress2'])) {$_POST['WhAddress2']='';}
	echo '<td><input tabindex=5 type="text" name="WhAddress2" size=41 maxlength=40 value="'. $_POST['WhAddress2'].'"></td></tr>';
	echo '<tr><td>'._('Street Address 3 (State)').':</td>';
	if (!isset($_POST['WhAddress3'])) {$_POST['WhAddress3']='';}
	echo '<td><input tabindex=6 type="text" name="WhAddress3" size=41 maxlength=40 value="'. $_POST['WhAddress3'].'"></td></tr>';
	echo '<tr><td>'._('Street Address 4 (Postal Code)').':</td>';
	if (!isset($_POST['WhAddress4'])) {$_POST['WhAddress4']='';}
	echo '<td><input tabindex=7 type="text" name="WhAddress4" size=31 maxlength=40 value="'. $_POST['WhAddress4'].'"></td></tr>';
	echo '<tr><td>'._('Street Address 5').':</td>';
	if (!isset($_POST['WhAddress5'])) {$_POST['WhAddress5']='';}
	echo '<td><input tabindex=8 type="text" name="WhAddress5" size=21 maxlength=20 value="'. $_POST['WhAddress5'].'"></td></tr>';
	echo '<tr><td>'._('Street Address 6').':</td>';
	if (!isset($_POST['WhAddress6'])) {$_POST['WhAddress6']='';}
	echo '<td><input tabindex=9 type="text" name="WhAddress6" size=16 maxlength=15 value="'. $_POST['WhAddress6'].'"></td></tr>';
        echo '<tr><td>'._('Phone Number').':</td>';
	if (!isset($_POST['PhoneNo'])) {$_POST['PhoneNo']='';}
	echo '<td><input tabindex=16 type="Text" name="PhoneNo" size=22 maxlength=20 value="'. $_POST['PhoneNo'].'"></td></tr>';
	echo '<tr><td>'._('Fax Number').':</td>';
	if (!isset($_POST['FaxNo'])) {$_POST['FaxNo']='';}
	echo '<td><input tabindex=17 type="Text" name="FaxNo" size=22 maxlength=20 value="'. $_POST['FaxNo'].'"></td></tr>';

	if (!isset($_POST['Email'])) {$_POST['Email']='';}
	echo '<tr><td>'.(($_POST['Email']) ? '<a href="Mailto:'.$_POST['Email'].'">'._('Email').':</a>' : _('Email').':').'</td>';
      //only display email link if there is an email address
	echo '<td><input tabindex=18 type="text" name="Email" size=56 maxlength=55 value="'. $_POST['Email'].'"></td></tr>';
        
        if (!isset($_POST['Alt1Email'])) {$_POST['Alt1Email']='';}
	echo '<tr><td>'.(($_POST['Alt1Email']) ? '<a href="Mailto:'.$_POST['Alt1Email'].'">'._('Alternate Email Address1').':</a>' : _('Alternate Email Address1').':').'</td>';
      //only display email link if there is an email address
	echo '<td><input tabindex=18 type="text" name="Alt1Email" size=56 maxlength=55 value="'. $_POST['Alt1Email'].'"></td></tr>';
        
        if (!isset($_POST['Alt2Email'])) {$_POST['Alt2Email']='';}
	echo '<tr><td>'.(($_POST['Alt2Email']) ? '<a href="Mailto:'.$_POST['Alt2Email'].'">'._('Alternate Email Address2').':</a>' : _('Alternate Email Address2').':').'</td>';
      //only display email link if there is an email address
	echo '<td><input tabindex=18 type="text" name="Alt2Email" size=56 maxlength=55 value="'. $_POST['Alt2Email'].'"></td></tr>';
        
        if (!isset($_POST['Alt3Email'])) {$_POST['Alt3Email']='';}
	echo '<tr><td>'.(($_POST['Alt3Email']) ? '<a href="Mailto:'.$_POST['Alt3Email'].'">'._('Alternate Email Address3').':</a>' : _('Alternate Email Address3').':').'</td>';
      //only display email link if there is an email address
	echo '<td><input tabindex=18 type="text" name="Alt3Email" size=56 maxlength=55 value="'. $_POST['Alt3Email'].'"></td></tr>';
        
	echo '<tr><td>'._('Comment').':</td>';
	if (!isset($_POST['Comment'])) {$_POST['Comment']='';}
	echo '<td><input tabindex=10 type="text" name="Comment" size=56 value="'. $_POST['Comment'].'"></td></tr>';
	
	echo '</table>';
	echo '<br /><div class="centre"><input tabindex=28 type="submit" name="submit" value="' . _('Enter Warehouse') . '"></div>';
	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>