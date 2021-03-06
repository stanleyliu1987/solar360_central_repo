<?php

/* $Id: SelectSalesOrder.php 4714 2011-10-06 09:16:44Z daintree $*/

include('includes/session.inc');
$title = _('Search Outstanding Sales Orders');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['OrderStartDate'])){
$OrderStartDate=$_POST['OrderStartDate'];
}
if(isset($_POST['OrderEndDate'])){
$OrderEndDate=$_POST['OrderEndDate'];
}
if (isset($_POST['PlacePO'])){ /*user hit button to place PO for selected orders */

	/*Note the button would not have been displayed if the user had no authority 
	 * to create purchase orders */
	$OrdersToPlacePOFor = '';
	for ($i=1;$i<count($_POST);$i++){
		if (isset($_POST['PlacePO_' . $i])) { //checkboxes only set if they are checked
			if ($OrdersToPlacePOFor==''){
				$OrdersToPlacePOFor .= " salesorders.orderno='" . $_POST['OrderNo_PO_'.$i] . "'";
			} else {
				$OrdersToPlacePOFor .= " OR salesorders.orderno='" . $_POST['OrderNo_PO_'.$i] . "'";
			}
		}
	}
	if (mb_strlen($OrdersToPlacePOFor)==''){
		prnMsg(_('There were no sales orders checked to place purchase orders for. No purchase orders will be created.'),'info');
	} else {
   /*  Now build SQL of items to purchase with purchasing data and preferred suppliers - sorted by preferred supplier */
		$sql = "SELECT purchdata.supplierno,
						purchdata.stockid,
						purchdata.price,
						purchdata.suppliers_partno,
						purchdata.supplierdescription,
						purchdata.conversionfactor,
						purchdata.leadtime,
						purchdata.suppliersuom,
						stockmaster.kgs,
						stockmaster.volume,
						stockcategory.stockact,
						SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS orderqty,
                                                salesorders.deladd1,
                                                salesorders.deladd2,
                                                salesorders.deladd3,
                                                salesorders.deladd4,
                                                salesorders.deladd5,
                                                salesorders.deladd6,
                                                salesorders.contactphone,
                                                salesorders.shipvia,
                                                salesorders.orderno,
                                                debtorsmaster.name,
                                                salesorderdetails.narrative
				FROM purchdata INNER JOIN salesorderdetails ON
				purchdata.stockid = salesorderdetails.stkcode
                                INNER JOIN salesorders ON
                                salesorders.orderno=salesorderdetails.orderno
                                INNER JOIN debtorsmaster ON
                                debtorsmaster.debtorno=salesorders.debtorno
				INNER JOIN stockmaster  ON
				purchdata.stockid = stockmaster.stockid
				INNER JOIN stockcategory ON
				stockmaster.categoryid = stockcategory.categoryid
				WHERE purchdata.preferred=1
				AND purchdata.effectivefrom <='" . Date('Y-m-d') . "'
                                AND stockmaster.mbflag<>'A'
				AND (" . $OrdersToPlacePOFor . ")
				GROUP BY purchdata.supplierno,
					purchdata.stockid,
					purchdata.price,
					purchdata.suppliers_partno,
					purchdata.supplierdescription,
					purchdata.conversionfactor,
					purchdata.leadtime,
					purchdata.suppliersuom,
					stockmaster.kgs,
					stockmaster.volume,
					stockcategory.stockact
				ORDER BY purchdata.supplierno,
					 purchdata.stockid";
					 
		$ErrMsg = _('Unable to retrieve the items on the selected orders for creating purchase orders for');
		$ItemResult = DB_query($sql,$db,$ErrMsg);
		
		$ItemArray = array();
		
		while ($myrow = DB_fetch_array($ItemResult)){
			$ItemArray[$myrow['stockid']] = $myrow;
		}

		/* Now figure out if there are any components of Assembly items that  need to be ordered too */
		$sql = "SELECT purchdata.supplierno,
						purchdata.stockid,
						purchdata.price,
						purchdata.suppliers_partno,
						purchdata.supplierdescription,
						purchdata.conversionfactor,
						purchdata.leadtime,
						purchdata.suppliersuom,
						stockmaster.kgs,
						stockmaster.volume,
						stockcategory.stockact,
						SUM(bom.quantity *(salesorderdetails.quantity-salesorderdetails.qtyinvoiced)) AS orderqty,
                                                salesorders.deladd1,
                                                salesorders.deladd2,
                                                salesorders.deladd3,
                                                salesorders.deladd4,
                                                salesorders.deladd5,
                                                salesorders.deladd6,
                                                salesorders.contactphone,
                                                salesorders.shipvia,
                                                salesorders.orderno,
                                                debtorsmaster.name,
                                                salesorderdetails.narrative
				FROM purchdata INNER JOIN bom 
				ON purchdata.stockid=bom.component
				INNER JOIN salesorderdetails ON
				bom.parent=salesorderdetails.stkcode
                                INNER JOIN salesorders ON
                                salesorders.orderno=salesorderdetails.orderno
                                INNER JOIN debtorsmaster ON
                                debtorsmaster.debtorno=salesorders.debtorno
				INNER JOIN stockmaster ON
				purchdata.stockid = stockmaster.stockid
				INNER JOIN stockmaster AS stockmaster2 
				ON stockmaster2.stockid=salesorderdetails.stkcode
				INNER JOIN stockcategory ON
				stockmaster.categoryid = stockcategory.categoryid
				WHERE purchdata.preferred=1 
				AND stockmaster2.mbflag='A'
				AND bom.loccode ='" . $_SESSION['UserStockLocation'] . "'
				AND purchdata.effectivefrom <='" . Date('Y-m-d') . "'
				AND bom.effectiveafter <='" . Date('Y-m-d') . "' 
				AND bom.effectiveto > '" . Date('Y-m-d') . "'
				AND (" . $OrdersToPlacePOFor . ")
				GROUP BY purchdata.supplierno,
					purchdata.stockid,
					purchdata.price,
					purchdata.suppliers_partno,
					purchdata.supplierdescription,
					purchdata.conversionfactor,
					purchdata.leadtime,
					purchdata.suppliersuom,
					stockmaster.kgs,
					stockmaster.volume,
					stockcategory.stockact
				ORDER BY purchdata.supplierno,
					 purchdata.stockid";
		$ErrMsg = _('Unable to retrieve the items on the selected orders for creating purchase orders for');
		$ItemResult = DB_query($sql,$db,$ErrMsg);

	
		/* add any assembly item components from salesorders to the ItemArray */
		while ($myrow = DB_fetch_array($ItemResult)){
            if (isset($ItemArray[$myrow['stockid']])){
              /* if the item is already in the ItemArray then just add the quantity to the existing item */
               $ItemArray[$myrow['stockid']]['orderqty'] += $myrow['orderqty'];
            } else { /*it is not already in the ItemArray so add it */
            	$ItemArray[$myrow['stockid']] = $myrow;
           	}
		}


        /* We need the items to order to be in supplier order so that only a single order is created for a supplier - so need to sort the multi-dimensional array to
         ensure it is listed by supplier sequence. To use array_multisort we need to get arrays of supplier with the same keys as the main array of rows
         So to get the supplier into a separate array with the same keys as the main ItemArray
         */
        foreach ($ItemArray as $key => $row) {
            $SupplierArray[$key]  = $row['supplierno'];  //to make the Supplier array with the keys of the $ItemArray
        }

        /* Use array_multisort to Sort the ItemArray with supplierno ascending
        Add $ItemArray as the last parameter, to sort by the common key
        */
        array_multisort($SupplierArray, SORT_ASC, $ItemArray);

		if (count($ItemArray)==0){
			prnMsg(_('There might be no supplier purchasing data set up for any items on the selected sales order(s). No purchase orders have been created'),'warn');
		} else {
			/*Now get the default delivery address details from the users default stock location */
			$sql = "SELECT locationname,
							deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6,
							tel,
							contact
						FROM locations
						WHERE loccode = '" .$_SESSION['UserStockLocation']  . "'";
			$ErrMsg = _('The delivery address for the order could not be obtained from the user default stock location');
			$DelAddResult = DB_query($sql, $db,$ErrMsg);
			$DelAddRow = DB_fetch_array($DelAddResult);

			$SupplierID = '';

			if (IsEmailAddress($_SESSION['UserEmail'])){
				$UserDetails  = ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName']. '</a>';
			} else {
				$UserDetails  = ' ' . $_SESSION['UsersRealName'] . ' ';
			}

			foreach ($ItemArray as $ItemRow) {

				if ($SupplierID != $ItemRow['supplierno']){
				/* This order item is purchased from a different supplier so need to finish off the authorisation of the previous order and start a new order */

					if ($SupplierID !='' AND $_SESSION['AutoAuthorisePO']==1) {
						/* if an order is/has been created already and the supplier of this item has changed - so need to finish off the order */
						//if the user has authority to authorise the PO then it should be created as authorised
						$AuthSQL ="SELECT authlevel
					 				FROM purchorderauth
								    WHERE userid='".$_SESSION['UserID']."'
									AND currabrev='".$SuppRow['currcode']."'";

						$AuthResult=DB_query($AuthSQL,$db);
						$AuthRow=DB_fetch_array($AuthResult);
						if ($AuthRow['authlevel']=''){
							$AuthRow['authlevel'] = 0;
						}

						if (DB_num_rows($AuthResult) > 0 AND $AuthRow['authlevel'] > $Order_Value) { //user has authority to authrorise as well as create the order
							$StatusComment = date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created and Authorised by') . ' ' . $UserDetails . ' - '._('Auto created from sales orders') .'<br />';
							$ErrMsg = _('Could not update purchase order status to Authorised');
							$Debug = _('The SQL that failed was');
							$result = DB_query("UPDATE purchorders SET allowprint=1,
												   status='Authorised',
												   stat_comment='" . $StatusComment . "'
												WHERE orderno='" . $PO_OrderNo . "'",
												$db,$ErrMsg,$DbgMsg,true);
						} else { // no authority to authorise this order
							if (DB_num_rows($AuthResult) ==0){
								$AuthMessage = _('Your authority to approve purchase orders in') . ' ' .$SuppRow['currcode'] . ' ' . _('has not yet been set up') . '<br />';
							} else {
								$AuthMessage = _('You can only authorise up to') . ' ' . $SuppRow['currcode'] . ' ' . $AuthRow['authlevel'] . '.<br />';
							}

							prnMsg( _('You do not have permission to authorise this purchase order').'.<br />'. _('This order is for').' '.
							$SuppRow['currcode'] . ' '. $Order_Value .'. '.
							$AuthMessage . _('If you think this is a mistake please contact the systems administrator') . '<br />'.
							_('The order has been created with a status of pending and will require authorisation'), 'warn');
						}
					} //end of authorisation status settings

					if ($SupplierID !=''){ //then we have just added a purchase order
						echo '<p />';
						prnMsg(_('Purchase Order') . ' ' . $PO_OrderNo . ' ' . _('on') . ' ' . $SupplierID . ' ' . _('has been created'),'success');
						DB_Txn_Commit($db);
					}

                    /*Starting a new purchase order with a different supplier */
					$result = DB_Txn_Begin($db);
					
					$PO_OrderNo =  GetNextTransNo(18, $db); //get the next PO number

					$SupplierID = $ItemRow['supplierno'];
					$Order_Value =0;
					/*Now get all the required details for the supplier */
					$sql = "SELECT address1,
        							address2,
        							address3,
        							address4,
        							address5,
        							address6,
        							telephone,
        							paymentterms,
        							currcode,
        							rate
					        FROM suppliers INNER JOIN currencies
						    ON suppliers.currcode = currencies.currabrev
						    WHERE supplierid='" . $SupplierID . "'";

					$ErrMsg = _('Could not get the supplier information for the order');
					$SuppResult = DB_query($sql, $db, $ErrMsg);
					$SuppRow = DB_fetch_array($SuppResult);

					$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created by') . ' ' . $UserDetails . ' - '._('Auto created from sales orders') .'<br />';
					/*Insert to purchase order header record */
					$sql = "INSERT INTO purchorders ( orderno,
                									  supplierno,
                									  orddate,
                									  rate,
                									  initiator,
                									  intostocklocation,
                									  deladd1,
                									  deladd2,
                									  deladd3,
                									  deladd4,
                									  deladd5,
                									  deladd6,
                									  tel,
                                                                                          contact,
                									  suppdeladdress1,
                									  suppdeladdress2,
                									  suppdeladdress3,
                									  suppdeladdress4,
                									  suppdeladdress5,
                									  suppdeladdress6,
                									  supptel,
                									  version,
                									  revised,
                									  deliveryby,
                									  status,
                									  stat_comment,
                									  deliverydate,
                									  paymentterms,
                									  allowprint,
                                                                                          ref_salesorder,
                                                                                          ref_number)
                									VALUES(	'" . $PO_OrderNo . "',
                										'" . $SupplierID . "',
                										'" . Date('Y-m-d') . "',
                										'" . $SuppRow['rate'] . "',
                										'" . $_SESSION['UsersRealName'] . "',
                										'" . $_SESSION['UserStockLocation'] . "',
                										'" . $ItemRow['deladd1'] . "',
                										'" . $ItemRow['deladd2'] . "',
                										'" . $ItemRow['deladd3'] . "',
                										'" . $ItemRow['deladd4'] . "',
                										'" . $ItemRow['deladd5'] . "',
                										'" . $ItemRow['deladd6'] . "',
                										'" . $ItemRow['contactphone'] . "',
                                                                                                '" . $ItemRow['name'] . "',
                										'" . $SuppRow['address1'] . "',
                										'" . $SuppRow['address2'] . "',
                										'" . $SuppRow['address3'] . "',
                										'" . $SuppRow['address4'] . "',
                										'" . $SuppRow['address5'] . "',
                										'" . $SuppRow['address6'] . "',
                										'" . $SuppRow['telephone'] . "',
                										'1.0',
                										'" . Date('Y-m-d') . "',
                										'" . $_SESSION['Default_Shipper'] . "',
                										'Pending',
                										'" . $StatusComment . "',
                										'" . Date('Y-m-d') . "',
                										'" . $SuppRow['paymentterms'] . "',
                										0,
                                                                                                '". $ItemRow['orderno']."',
                                                                                                '". 'W'.$ItemRow['orderno'].$SupplierID.$PO_OrderNo ."'  )";

					$ErrMsg =  _('The purchase order header record could not be inserted into the database because');
					$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				} //end if it's a new supplier and PO to create

				/*reminder we are in a loop of the total of each item to place a purchase order for based on a selection of sales orders */
				$DeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',$ItemRow['leadtime']);
				$sql = "INSERT INTO purchorderdetails ( orderno,
                    									itemcode,
                    									deliverydate,
                    									itemdescription,
                    									glcode,
                    									unitprice,
                    									quantityord,
                    									suppliersunit,
                    									suppliers_partno,
                    									conversionfactor )
                                         VALUES ('" . $PO_OrderNo . "',
                    						     '" . $ItemRow['stockid'] . "',
                    						     '" . FormatDateForSQL($DeliveryDate) . "',
                    						     '" . $ItemRow['supplierdescription']  . "',
                    						     '" . $ItemRow['stockact'] . "',
                    						     '" . $ItemRow['price'] . "',
                    						     '" . $ItemRow['orderqty'] . "',
                    						     '" . $ItemRow['suppliersuom'] . "',
                    						     '" . $ItemRow['suppliers_partno'] . "',
                    						     '" . $ItemRow['conversionfactor']  . "')";
				$ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
				$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');

				$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				$Order_Value  += ($ItemRow['price']*$ItemRow['orderqty']);
			} /* end of the loop round the items on the sales order  that we wish to place purchase orders for */


			/* The last line to be purchase ordered was reach so there will be an order which is not yet completed in progress now to completed it */
		
			if ($SupplierID !='' AND $_SESSION['AutoAuthorisePO']==1) {
				//if the user has authority to authorise the PO then it should be created as authorised
				$AuthSQL ="SELECT authlevel
							FROM purchorderauth
							WHERE userid='".$_SESSION['UserID']."'
							AND currabrev='".$SuppRow['currcode']."'";
		
				$AuthResult=DB_query($AuthSQL,$db);
				$AuthRow=DB_fetch_array($AuthResult);
				if ($AuthRow['authlevel']=''){
		                    $AuthRow['authlevel'] = 0;
				}
		
				if (DB_num_rows($AuthResult) > 0 AND $AuthRow['authlevel'] > $Order_Value) { //user has authority to authrorise as well as create the order
					$StatusComment = date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created and Authorised by') . $UserDetails . ' - '._('Auto created from sales orders') .'<br />';
					$ErrMsg = _('Could not update purchase order status to Authorised');
					$Debug = _('The SQL that failed was');
					$result = DB_query("UPDATE purchorders SET allowprint=1,
															status='Authorised',
															stat_comment='" . $StatusComment . "'
									                      WHERE orderno='" . $PO_OrderNo . "'",
												$db,$ErrMsg,$DbgMsg,true);
				} else { // no authority to authorise this order
					if (DB_num_rows($AuthResult) ==0){
						$AuthMessage = _('Your authority to approve purchase orders in') . ' ' .$SuppRow['currcode'] . ' ' . _('has not yet been set up') . '<br />';
					} else {
						$AuthMessage = _('You can only authorise up to').' '.$SuppRow['currcode'].' '.$AuthRow['authlevel'].'.<br />';
					}
		
					prnMsg( _('You do not have permission to authorise this purchase order').'.<br />'. _('This order is for').' '. $SuppRow['currcode'] . ' '. $Order_Value .'. '. $AuthMessage . _('If you think this is a mistake please contact the systems administrator') . '<br />'. _('The order has been created with a status of pending and will require authorisation'), 'warn');
				}
			} //end of authorisation status settings

			if ($SupplierID !=''){ //then we have just added a purchase order irrespective of autoauthorise status
				echo '<p>';
				prnMsg(_('Purchase Order') . ' ' . $PO_OrderNo . ' ' . _('on') . ' ' . $SupplierID . ' ' . _('has been created'),'success');
				DB_Txn_Commit($db);
			}
			$result = DB_query("UPDATE salesorders SET poplaced=1 WHERE " . $OrdersToPlacePOFor,$db);
		}/*There were items that had purchasing data set up to create POs for */
	} /* there were sales orders checked to place POs for */
}/*end of purchase order creation code */
/* ******************************************************************************************* */

/*To the sales order selection form */

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('Sales') . '" alt="" />' . ' ' . _('Outstanding Sales Orders') . '</p> ';

echo '<form action="' . $_SERVER['PHP_SELF'] .'" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


if (isset($_POST['ResetPart'])){
     unset($_REQUEST['SelectedStockItem']);
     unset($OrderStartDate);
     unset($OrderEndDate);
}

echo '<p><div class="centre">';

if (isset($_REQUEST['OrderNumber']) AND $_REQUEST['OrderNumber']!='') {
	$_REQUEST['OrderNumber'] = trim($_REQUEST['OrderNumber']);
	if (!is_numeric($_REQUEST['OrderNumber'])){
		echo '<br /><b>' . _('The Order Number entered MUST be numeric') . '</b><br />';
		unset ($_REQUEST['OrderNumber']);
		include('includes/footer.inc');
		exit;
	} else {
		echo _('Order Number') . ' - ' . $_REQUEST['OrderNumber'];
	}
} else {
	if (isset($_REQUEST['SelectedCustomer'])) {
		echo _('For customer') . ': ' . $_REQUEST['SelectedCustomer'] . ' ' . _('and') . ' ';
		echo '<input type="hidden" name="SelectedCustomer" value="' . $_REQUEST['SelectedCustomer'] . '" />';
	}
	if (isset($_REQUEST['SelectedStockItem'])) {
		 echo _('for the part') . ': ' . $_REQUEST['SelectedStockItem'] . ' ' . _('and') . ' <input type="hidden" name="SelectedStockItem" value="' . $_REQUEST['SelectedStockItem'] . '" />';
	}
}

if (isset($_POST['SearchParts'])){
     unset($OrderStartDate);
     unset($OrderEndDate);
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units
			FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid=locstock.stockid
			WHERE stockmaster.description " . LIKE . " '" . $SearchString . "'
			AND stockmaster.categoryid='" . $_POST['StockCat']. "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (isset($_POST['StockCode'])){
		$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid=locstock.stockid
				WHERE stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
				ORDER BY stockmaster.stockid";

	 } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid=locstock.stockid
				WHERE stockmaster.categoryid='" . $_POST['StockCat'] ."'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
				ORDER BY stockmaster.stockid";
	 }

	$ErrMsg =  _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

}

if (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
}

if (!isset($StockID)) {

     /* Not appropriate really to restrict search by date since may miss older
     ouststanding orders
	$OrdersAfterDate = Date('d/m/Y',Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
     */

	if (!isset($_REQUEST['OrderNumber']) or $_REQUEST['OrderNumber']==''){

		echo '<table class="selection">
			<tr>
				<td>' . _('Order number') . ': </td>
				<td><input type="text" name="OrderNumber" maxlength="8" size="9" /></td>
				<td>' . _('From Stock Location') . ':</td>
				<td><select name="StockLocation"> ';

		$sql = "SELECT loccode, locationname FROM locations";

		$resultStkLocs = DB_query($sql,$db);

		while ($myrow=DB_fetch_array($resultStkLocs)){
			if (isset($_POST['StockLocation'])){
				if ($myrow['loccode'] == $_POST['StockLocation']){
				     echo '<option selected value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
				} else {
				     echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname']. '</option>';
				}
			} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
				 echo '<option selected value="' . $myrow['loccode'] . '">' . $myrow['locationname']. '</option>';
			} else {
				 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname']. '</option>';
			}
		}

		echo '</select></td>
			<td><select name="Quotations">';

		if ($_GET['Quotations']=='Quotes_Only'){
			$_POST['Quotations']='Quotes_Only';
		}

		if ($_POST['Quotations']=='Quotes_Only'){
			echo '<option selected value="Quotes_Only">' . _('Quotations Only') . '</option>';
			echo '<option value="Orders_Only">' . _('Orders Only')  . '</option>';
		} else {
			echo '<option selected value="Orders_Only">' . _('Orders Only') . '</option>';
			echo '<option value="Quotes_Only">' . _('Quotations Only') . '</option>';
		}

		echo '</select> </td></tr>
                 <tr><td><font size="1">' . _('Order Start Date') . ':</font></td>
                <td><input type="text" maxlength="10" size="15" name="OrderStartDate" value="'.$OrderStartDate.'" id="datepicker" alt="'.$_SESSION['DefaultDateFormat'].'" class="date"></td>
		<td><font size=1>' . _('Order End Date') . ':</font></td>
		<td><input type="text" maxlength="10" size="15" name="OrderEndDate"  value="'.$OrderEndDate.'" id="datepicker" alt="'.$_SESSION['DefaultDateFormat'].'" class="date"></td>
				<td><input type=submit name="SearchOrders" value="' . _('Search') . '"></td>
				<td><a href="' . $rootpath . '/SelectOrderItems.php?NewOrder=Yes">' . _('Add Sales Order') . '</a></td>
			</tr>
			</table>';
	}

	$SQL="SELECT categoryid,
			categorydescription
		FROM stockcategory
		ORDER BY categorydescription";

	$result1 = DB_query($SQL,$db);

	echo '<br />
		<table class="selection">
		<tr>
			<th colspan="6"><font size="3" color="navy">' . _('To search for sales orders for a specific part use the part selection facilities below') . '</th>
		</tr>
		<tr>
      		<td><font size="1">' . _('Select a stock category') . ':</font>
      			<select name="StockCat">';

	while ($myrow1 = DB_fetch_array($result1)) {
		echo '<option value="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	}

	echo '</select></td>
			<td><font size=1>' . _('Enter text extract(s) in the description') . ':</font></td>
			<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
		</tr>
      	<tr>
			<td></td>
      		<td><font size="3"><b>' . _('OR') . ' </b></font><font size="1">' . _('Enter extract of the Stock Code') . '</b>:</font></td>
      		<td><input type="text" name="StockCode" size="15" maxlength="18" /></td>
      	</tr>
      </table>';
	echo '<br />
			<input type="submit" name="SearchParts" value="' . _('Search Parts Now') . '" />
			<input type="submit" name="ResetPart" value="' . _('Show All') . '" />
		</div>
		<br />';

if (isset($StockItemsResult) 
	AND DB_num_rows($StockItemsResult)>0) {

	echo '<table cellpadding="2" colspan="7" class="selection">';
	$TableHeader = '<tr>
						<th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('On Hand') . '</th>
						<th>' . _('Units') . '</th>
					</tr>';
	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		printf('<td><input type="submit" name="SelectedStockItem" value="%s" /></td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>',
				$myrow['stockid'],
				$myrow['description'],
				number_format($myrow['qoh'],$myrow['decimalplaces']),
				$myrow['units']);

		$j++;
		if ($j == 12){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
//end if stock search results to show
  else {

	//figure out the SQL required from the inputs available
	if (isset($_POST['Quotations']) AND $_POST['Quotations']=='Orders_Only'){
		$Quotations = 0;
	} else {
		$Quotations =1;
	}
	if(!isset($_POST['StockLocation'])) {
		$_POST['StockLocation'] = '';
	}

        if($OrderStartDate!='' and $OrderEndDate==''){ 
           $OrderRangeCriteria="AND salesorders.orddate  >='".FormatDateForSQL($OrderStartDate)."' ";
        }
        elseif($OrderStartDate=='' and $OrderEndDate!=''){ 
           $OrderRangeCriteria="AND salesorders.orddate  <='".FormatDateForSQL($OrderEndDate)."' "; 
        }
        elseif($OrderStartDate!='' and $OrderEndDate!=''){ 
           $OrderRangeCriteria="AND salesorders.orddate  between '".FormatDateForSQL($OrderStartDate)."' and '".FormatDateForSQL($OrderEndDate)."' ";
        } 
        else{ 
           $OrderRangeCriteria=' ';  
        }



	if (isset($_REQUEST['OrderNumber']) 
		AND $_REQUEST['OrderNumber'] !='') {
			$SQL = "SELECT salesorders.orderno,
                                       salesorders.debtorno,
                                       salesorders.branchcode,
					debtorsmaster.name,
					custbranch.brname,
                                        custbranch.email,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip,
					salesorders.poplaced,
                                        salesorders.freightcost,
					SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
				FROM salesorders INNER JOIN salesorderdetails 
					ON salesorders.orderno = salesorderdetails.orderno
					INNER JOIN debtorsmaster 
					ON salesorders.debtorno = debtorsmaster.debtorno
					INNER JOIN custbranch 
					ON debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
				WHERE salesorderdetails.completed=0
				AND salesorders.orderno=". $_REQUEST['OrderNumber'] ."
				AND salesorders.quotation =" .$Quotations . "
                                ".$OrderRangeCriteria."    
				GROUP BY salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip,
					salesorders.poplaced
				ORDER BY salesorders.orderno";
	} else {
	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

		if (isset($_REQUEST['SelectedCustomer'])) {

			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT salesorders.orderno,
                                               salesorders.debtorno,
                                               salesorders.branchcode,
						debtorsmaster.name,
						custbranch.brname,
                                                custbranch.email,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto,
						salesorders.printedpackingslip,
						salesorders.poplaced,
                                                salesorders.freightcost,
						salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate AS ordervalue
					FROM salesorders INNER JOIN salesorderdetails 
						ON salesorders.orderno = salesorderdetails.orderno
						INNER JOIN debtorsmaster 
						ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN custbranch 
						ON debtorsmaster.debtorno = custbranch.debtorno
						AND salesorders.branchcode = custbranch.branchcode
						INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
					WHERE salesorderdetails.completed=0
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] ."'
					AND salesorders.debtorno='" . $_REQUEST['SelectedCustomer'] ."'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
                                            ".$OrderRangeCriteria." 
					ORDER BY salesorders.orderno";


			} else {
				$SQL = "SELECT salesorders.orderno,
                                               salesorders.debtorno,
                                               salesorders.branchcode,
						debtorsmaster.name,
						custbranch.brname,
                                                custbranch.email,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.printedpackingslip,
						salesorders.poplaced,
						salesorders.deliverydate,
                                                salesorders.freightcost,
						SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
					FROM salesorders INNER JOIN salesorderdetails 
						ON salesorders.orderno = salesorderdetails.orderno
						INNER JOIN debtorsmaster 
						ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN custbranch 
						ON debtorsmaster.debtorno = custbranch.debtorno
						AND salesorders.branchcode = custbranch.branchcode
						INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
					WHERE  salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.completed=0
					AND salesorders.debtorno='" . $_REQUEST['SelectedCustomer'] . "'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
                                            ".$OrderRangeCriteria." 
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						salesorders.debtorno,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.poplaced
					ORDER BY salesorders.orderno";

			}
		} else { //no customer selected
			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT salesorders.orderno,
                                               salesorders.debtorno,
                                               salesorders.branchcode,
						debtorsmaster.name,
						custbranch.brname,
                                                custbranch.email,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
					  	salesorders.printedpackingslip,
					  	salesorders.poplaced,
						salesorders.deliverydate,
                                                salesorders.freightcost,
						SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
					FROM salesorders INNER JOIN salesorderdetails 
						ON salesorders.orderno = salesorderdetails.orderno
						INNER JOIN debtorsmaster 
						ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN custbranch 
						ON debtorsmaster.debtorno = custbranch.debtorno
						AND salesorders.branchcode = custbranch.branchcode
						INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
					WHERE salesorderdetails.completed=0
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] . "'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
                                            ".$OrderRangeCriteria." 
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.poplaced,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orderno";
			} else {
				$SQL = "SELECT salesorders.orderno,
                                               salesorders.debtorno,
                                               salesorders.branchcode,                         
						debtorsmaster.name,
						custbranch.brname,
                                                custbranch.email,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip,
						salesorders.poplaced,
                                                salesorders.freightcost,
						SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
					FROM salesorders INNER JOIN salesorderdetails 
						ON salesorders.orderno = salesorderdetails.orderno
						INNER JOIN debtorsmaster 
						ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN custbranch 
						ON debtorsmaster.debtorno = custbranch.debtorno
						AND salesorders.branchcode = custbranch.branchcode
						INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
					WHERE salesorderdetails.completed=0
					AND salesorders.quotation =" .$Quotations . "
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
                                            ".$OrderRangeCriteria." 
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip,
						salesorders.poplaced
					ORDER BY salesorders.orderno";
			}

		} //end selected customer
	} //end not order number selected

	$ErrMsg = _('No orders or quotations were returned by the SQL because');
	$SalesOrdersResult = DB_query($SQL,$db,$ErrMsg);

	/*show a table of the orders returned by the SQL */
	if (DB_num_rows($SalesOrdersResult)>0) {
		
		/* Get users authority to place POs */
		$AuthSQL="SELECT cancreate
					FROM purchorderauth
					WHERE userid='". $_SESSION['UserID'] . "'";
						
		/*we don't know what currency these orders might be in but if no authority at all then don't show option*/
		$AuthResult=DB_query($AuthSQL,$db);
		$AuthRow=DB_fetch_array($AuthResult);

		echo '<table cellpadding="2" colspan="7" width="95%" class="selection">';

		if (isset($_POST['Quotations']) AND $_POST['Quotations']=='Orders_Only'){
			$tableheader = '<tr>
								<th>' . _('Modify') . '</th>
								<th>' . _('Invoice') . '</th>
                                                                <th>' . _('Email Proforma Invoice') . '</th>
                                                                <th>' . _('Print Proforma Invoice') . '</th>
								<th>' . _('Email Send Date') . '</th>
								<th>' . _('Customer') . '</th>
								<th>' . _('Branch') . '</th>
								<th>' . _('Cust Order') . ' #</th>
								<th>' . _('Order Date') . '</th>
								<th>' . _('Req Del Date') . '</th>
								<th>' . _('Delivery To') . '</th>
								<th>' . _('Order Total') . '<br />' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>';
								
			if ($AuthRow['cancreate']==0){ //If cancreate==0 then this means the user can create orders hmmm!!
				$tableheader .= '<th>' . _('Place PO') . '</th></tr>';
			} else {
				$tableheader .= '</tr>';
			}
		} else {  /* displaying only quotations */
			$tableheader = '<tr>
								<th>' . _('Modify') . '</th>
								<th>' . _('Print Quote') . '</th>
								<th>' . _('Customer') . '</th>
								<th>' . _('Branch') . '</th>
								<th>' . _('Cust Ref') . ' #</th>
								<th>' . _('Quote Date') . '</th>
								<th>' . _('Req Del Date') . '</th>
								<th>' . _('Delivery To') . '</th>
								<th>' . _('Quote Total') .  '<br />' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
							</tr>';
		}

		echo $tableheader;

		$i = 1;
                $j = 1;
		$k=0; //row colour counter
		$OrdersTotal =0;
		
		while ($myrow=DB_fetch_array($SalesOrdersResult)) {


			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			$ModifyPage = $rootpath . '/SelectOrderItems.php?ModifyOrderNumber=' . $myrow['orderno'];
			$Confirm_Invoice = $rootpath . '/ConfirmDispatch_Invoice.php?OrderNumber=' .$myrow['orderno'];
	
			if ($_SESSION['PackNoteFormat']==1){ /*Laser printed A4 default */
				$PrintDispatchNote = $rootpath . '/PrintCustOrder_generic.php?TransNo=' . $myrow['orderno'];
			} else { /*pre-printed stationery default */
				$PrintDispatchNote = $rootpath . '/PrintCustOrder.php?TransNo=' . $myrow['orderno'];
			}
			$PrintQuotation = $rootpath . '/PDFQuotation.php?QuotationNo=' . $myrow['orderno'];
                        $PrintProformaInv = $rootpath . '/PDFProformaInv.php?ProformaInvNo=' . $myrow['orderno'];
			$FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
			$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
                        /* Hard code the GST rate to 10% 30102015*/
                        $TotalOrderValue=((($myrow['ordervalue']+$myrow['freightcost']))/10)+$myrow['freightcost']+$myrow['ordervalue'];
			$FormatedOrderValue = number_format($TotalOrderValue,$_SESSION['CompanyRecord']['decimalplaces']);
	
			if ($myrow['printedpackingslip']==0) {
			  $PrintText = _('Print');
			} else {
			  $PrintText = _('Reprint');
			}
                        
                        /* 23012015 Retrieve Send Email Status */
                        $CheckEmailSQL="Select senddate from emailauditlog where sendstatus=1 and ordernumber='".$myrow['orderno']."' and emailtemplateid in (5,6,7) order by senddate desc limit 1";
                        $EmailResult=DB_query($CheckEmailSQL,$db);
                        $EmailSendTime = '';
                        if (DB_num_rows($EmailResult)>0){
                            $EmailRow = DB_fetch_array($EmailResult);
                            $EmailSendTime=$EmailRow['senddate'];
                        }

                        /* Email and Print Proforma Invoice link 21012015*/
	                $EmailProInvLink= $rootpath.'/EmailClientPInv.php?CustEmail='.$myrow['email'].'&SalesOrderNo='.$myrow['orderno'].'&debtorno='.$myrow['debtorno'].'&branchcode='.$myrow['branchcode']; 
			if ($_POST['Quotations']=='Orders_Only'){
	
			 /*Check authority to create POs if user has authority then show the check boxes to select sales orders to place POs for otherwise don't provide this option */
				if ($AuthRow['cancreate']==0 AND $myrow['poplaced']==0){ //cancreate==0 if the user can create POs and not already placed
				printf('<td><a href="%s">%s</a></td>
        				<td><a href="%s">' . _('Invoice') . '</a></td>
                                        <td><a href="%s" target="_blank">' . _('Email') . '<img src="'.$rootpath.'/css/'.$theme.'/images/email.gif" title="' . _('Click to email the Client Proforma Invoice') . '"></a></td>
                                        <td><a href="%s" target="_blank">Print  <img src="' .$rootpath.'/css/'.$theme.'/images/pdf.png" title="' . _('Click for PDF') . '"></a></td>    
                                        <td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td>%s</td>
        				<td class="number">%s</td>
        				<td><input type="checkbox" name="PlacePO_%s" value /><input type="hidden" name="OrderNo_PO_%s" value="%s" /></td>
        				</tr>',
        				$ModifyPage,
        				$myrow['orderno'],
        				$Confirm_Invoice,
                                        $EmailProInvLink,
                                        $PrintProformaInv,
                                        $EmailSendTime,
        				//$PrintDispatchNote,
        				$myrow['name'],
        				$myrow['brname'],
        				$myrow['customerref'],
        				$FormatedOrderDate,
        				$FormatedDelDate,
        				html_entity_decode($myrow['deliverto'],ENT_QUOTES,'UTF-8'),
        				$FormatedOrderValue,
                        $i,
                        $i,
                        $myrow['orderno']);
				} else {  /*User is not authorised to create POs so don't even show the option */
					printf('<td><a href="%s">%s</a></td>
							<td><a href="%s">' . _('Invoice') . '</a></td>
                                                        <td><a href="%s" target="_blank">' . _('Email') . '<img src="'.$rootpath.'/css/'.$theme.'/images/email.gif" title="' . _('Click to email the Client Proforma Invoice') . '"></a></td>
                                                        <td><a href="%s" target="_blank">' . _('Print') . '<img src="' .$rootpath.'/css/'.$theme.'/images/pdf.png" title="' . _('Click for PDF') . '"></a></td>        
						        <td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td class="number">%s</td>
							</tr>',
							$ModifyPage,
							$myrow['orderno'],
							$Confirm_Invoice,
                                                        $EmailProInvLink,
                                                        $PrintProformaInv,
                                                        $EmailSendTime,
							//$PrintDispatchNote,
							$myrow['name'],
							$myrow['brname'],
							$myrow['customerref'],
							$FormatedOrderDate,
							$FormatedDelDate,
							html_entity_decode($myrow['deliverto'],ENT_QUOTES,'UTF-8'),
							$FormatedOrderValue);
				}
	
			} else { /*must be quotes only */
				printf('<td><a href="%s">%s</a></td>
						<td><a href="%s">' . $PrintText . '</a></td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>',
						$ModifyPage,
						$myrow['orderno'],
						$PrintQuotation,
						$myrow['name'],
						$myrow['brname'],
						$myrow['customerref'],
						$FormatedOrderDate,
						$FormatedDelDate,
						html_entity_decode($myrow['deliverto'],ENT_QUOTES,'UTF-8'),
						$FormatedOrderValue);
			}
			$i++;
			$j++;
                          
			$OrdersTotal += ((($myrow['ordervalue']+$myrow['freightcost']))/10)+$myrow['freightcost']+$myrow['ordervalue'];
			if ($j == 12){
				$j=1;
				echo $tableheader;
			}
		//end of page full new headings if
		}//end while loop through orders to display
		if ($_POST['Quotations']=='Orders_Only' 
			AND $AuthRow['cancreate']==0){ //cancreate==0 means can create POs
			
			echo '<tr><td colspan="8"><td><td colspan="4" class="number"><input type="submit" name="PlacePO" value="' . _('Place') . "\n" . _('PO') . '" onclick="return confirm(\'' . _('This will create purchase orders for all the items on the checked sales orders above, based on the preferred supplier purchasing data held in the system. Are You Absolutely Sure?') . '\');"></td</tr>';
		}
		echo '<tr><td colspan="9" class="number">';
		if ($_POST['Quotations']=='Orders_Only'){
			echo '<b>' . _('Total Order(s) Value in');
		} else {
			echo '<b>' . _('Total Quotation(s) Value in');
		}
		echo ' ' . $_SESSION['CompanyRecord']['currencydefault'] . ' :</b></td>
			<td class="number"><b>' . number_format($OrdersTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
			</tr>
			</table>';
	} //end if there are some orders to show  
}

echo '</form>';

} //end StockID already selected

include('includes/footer.inc');
?>