<?php
/* $Revision: 1.25 $ */
/* $Id: OrderDetails.php 4304 2010-12-22 16:05:30Z tim_schofield $*/

//$PageSecurity = 2;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/** update Stock Code in Temporary ERP table **/

/** End update Stock Code in Temporary ERP table **/

if(isset($_GET['OrderNumber'])){
 $OrderNumber=(int)$_GET['OrderNumber'];
}
elseif(isset($_POST['OrderNumber'])){
 $OrderNumber=(int)$_POST['OrderNumber'];   
}

if ($OrderNumber) {
	$title = _('Reviewing Sales Order Number') . ' ' . $OrderNumber;
} else {
	include('includes/header.inc');
	echo '<br><br><br>';
	prnMsg(_('This page must be called with a sales order number to review') . '.<br>' . _('i.e.') . ' http://????/WebsiteOrderDetails.php?OrderNumber=<i>xyz</i><br>' . _('Click on back') . '.','error');
	include('includes/footer.inc');
	exit;
}

include('includes/header.inc');


if(isset($_POST['UpdateCustomer'])){
    
    if(isset($_POST['customername']) and $_POST['customername'] !=''){
        $customername=$_POST['customername'];
    }
    if(isset($_POST['branchname']) and $_POST['branchname'] !=''){
        $branchname=$_POST['branchname'];
    }
    
    $SearchCustString = '%' .  str_replace("  "," ",$customername) . '%';
    $SearchBranString = '%' .  str_replace("  "," ",$branchname) . '%';
    /** 1. Based on new customer name and branch name to retrieve the customer details **/
    $SQLCheckCustomer= "SELECT  debtorsmaster.name,
                                custbranch.brname,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind
			        FROM custbranch
				LEFT JOIN debtorsmaster
				ON custbranch.debtorno=debtorsmaster.debtorno
				WHERE debtorsmaster.name ".LIKE."'".$SearchCustString."' or 
                                      custbranch.brname ".LIKE."'".$SearchBranString."'"; 

    $ErrMsg = _('The Customer cannot be retrieved because');
    $CustomerResult = db_query($SQLCheckCustomer,$db,$ErrMsg);
 
    /** 2. Replace the current customer with new customer and update import_salesorder table **/
    if (isset($CustomerResult) and DB_num_rows($CustomerResult)>0) {
        $myrow = DB_fetch_row($CustomerResult);
	$ImportSOSQL = "update import_salesorders set
                                                debtorno='".DB_escape_string($myrow[0])."',
                                                branchcode='".DB_escape_string($myrow[1])."',
                                                deliverto='". DB_escape_string($myrow[1])."',
                                                deladd1='". DB_escape_string($myrow[2])."',
	                                        deladd2='". DB_escape_string($myrow[3])."',
	                                        deladd3='". DB_escape_string($myrow[4])."',
		                                deladd4='". DB_escape_string($myrow[5])."',
		                                deladd5='". DB_escape_string($myrow[6])."',
		                                deladd6='". DB_escape_string($myrow[7])."',
                                                contactphone='". $myrow[8]."',
		                                contactemail='". $myrow[9]."',
		                                fromstkloc='". $myrow[10]."', 						
		                                shipvia='". $myrow[11]."', 						
                                                deliverblind='". $myrow[12]."'
                                                where  orderno='".$OrderNumber."'";
        
        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The Cusotomer Info record could not be updated for the disposal because');
	$DbgMsg = '<br />' ._('The following SQL to update the customer info record was used');
	$Result = DB_query($ImportSOSQL,$db,$ErrMsg,$DbgMsg,true);
             
    }
    else{
        prnMsg(_('Customer you entered is not existed! please try again') );
    }
  
}

if(isset($_POST['UpdateStockCode'])){
    
    	for ($i=0;$i<$_POST['OrderLineItemsTotal'];$i++){
		if (isset($_POST['stockCode_' . $i]) and isset($_POST['stockLineno_' . $i])) { //checkboxes only set if they are checked
                    
		 $stockSellPriceSQL = "SELECT price FROM prices
				                      WHERE stockid ='".$_POST['stockCode_' . $i] . "'"; 
                 $StockSellPriceResult = DB_query($stockSellPriceSQL,$db); 
                 $StockSellPriceList=DB_fetch_array($StockSellPriceResult); 
                 
                 $SelectOptionCodeSQL = "SELECT import_csv_salesorders.optioncode FROM import_csv_salesorders,import_salesorderdetails
				                       WHERE import_salesorderdetails.orderno='". $OrderNumber . "'
                                                             and import_salesorderdetails.orderlineno='".$_POST['stockCode_' . $i] . "'
                                                             and import_salesorderdetails.ref_csvorderno=import_csv_salesorders.id
				                       ORDER BY import_csv_salesorders.Number"; 
                 $SelectOptionCodeResult = DB_query($SelectOptionCodeSQL,$db); 
                 $SelectOptionCodeList=DB_fetch_array($SelectOptionCodeResult); 
             
               if(!empty($SelectOptionCodeList['optioncode'])){
                $SqlUpdateStockCode="Update import_salesorderdetails,import_csv_salesorders set import_salesorderdetails.stkcode='". $_POST['stockCode_' . $i]."',
                                                    import_csv_salesorders.optioncode='". $_POST['stockCode_' . $i]."',
                                                    import_salesorderdetails.unitprice='".$StockSellPriceList['price']."'
                                                    where orderlineno='". $_POST['stockLineno_' . $i]."' and orderno='".$OrderNumber."' and
                                                    import_salesorderdetails.stkcode = import_csv_salesorders.optioncode AND 
                                                    import_salesorderdetails.orderno = import_csv_salesorders.Number "; 
               }
               else{
                 $SqlUpdateStockCode="Update import_salesorderdetails,import_csv_salesorders set import_salesorderdetails.stkcode='". $_POST['stockCode_' . $i]."',
                                                    import_csv_salesorders.code='". $_POST['stockCode_' . $i]."',
                                                    import_salesorderdetails.unitprice='".$StockSellPriceList['price']."'
                                                    where orderlineno='". $_POST['stockLineno_' . $i]."' and orderno='".$OrderNumber."' and
                                                    import_salesorderdetails.stkcode = import_csv_salesorders.code AND 
                                                    import_salesorderdetails.orderno = import_csv_salesorders.Number ";   
               }
               
                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock Code cannot be updated');
	        $DbgMsg = _('The following SQL to update the Stock Code');
	        $UpdateStockCodeResult = DB_query($SqlUpdateStockCode,$db,$ErrMsg,$DbgMsg,true);
		}
	}
         
}

if(isset($_POST['GoPreviousPage'])){
  	echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/PlaceSalesOrderWeb.php">';
}

$OrderHeaderSQL = "SELECT
			import_salesorders.debtorno,
			import_salesorders.branchcode,
			import_salesorders.customerref,
			import_salesorders.comments,
			import_salesorders.orddate,
			import_salesorders.ordertype,
			import_salesorders.shipvia,
			import_salesorders.deliverto,
			import_salesorders.deladd1,
			import_salesorders.deladd2,
			import_salesorders.deladd3,
			import_salesorders.deladd4,
			import_salesorders.deladd5,
			import_salesorders.deladd6,
			import_salesorders.contactphone,
			import_salesorders.contactemail,
			import_salesorders.freightcost,
			import_salesorders.deliverydate,
			import_salesorders.fromstkloc
		FROM import_salesorders
		WHERE import_salesorders.orderno = '" . $OrderNumber. "'";

$ErrMsg =  _('The order cannot be retrieved because');
$DbgMsg = _('The SQL that failed to get the order header was');
$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db, $ErrMsg, $DbgMsg);


if (DB_num_rows($GetOrdHdrResult)==1) {
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' .
		_('Order Details') . '" alt="" />' . ' ' . $title . '</p>';

	$myrow = DB_fetch_array($GetOrdHdrResult); 
	echo '<table class=selection>';
	echo '<tr><th colspan=4><font color=blue>'._('Order Header Details For Order No').' '.$OrderNumber.'</font></th></tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Customer Name') . ':</th>
		<td class="OddTableRows"><input type="text" name="customername" value="' . $myrow['debtorno'] . '" size=50 /></td>
		<th style="text-align: left">' . _('Branch Name') . ':</th>
		<td class="OddTableRows"><input type="text" name="branchname" value="' . $myrow['branchcode'] . '" size=50/></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Customer Reference') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['customerref'] . '</font></td>
		<th style="text-align: left">' . _('Deliver To') . ':</th><td><font>' . $myrow['deliverto'] . '</td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Ordered On') . ':</th>
		<td class="OddTableRows"><font>' . ConvertSQLDate($myrow['orddate']) . '</font></td>
		<th style="text-align: left">' . _('Delivery Address 1') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd1'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Requested Delivery') . ':</th>
		<td class="OddTableRows"><font>' . ConvertSQLDate($myrow['deliverydate']) . '</font></td>
		<th style="text-align: left">' . _('Delivery Address 2') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd2'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left"h>' . _('Order Currency') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['currcode'] . '</font></td>
		<th style="text-align: left">' . _('Delivery Address 3') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd3'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Deliver From Location') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['fromstkloc'] . '</font></td>
		<th style="text-align: left">' . _('Delivery Address 4') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd4'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Telephone') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['contactphone'] . '</font></td>
		<th style="text-align: left">' . _('Delivery Address 5') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd5'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Email') . ':</th>
		<td class="OddTableRows"><font><a href="mailto:' . $myrow['contactemail'] . '">' . $myrow['contactemail'] . '</a></font></td>
		<th style="text-align: left">' . _('Delivery Address 6') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['deladd6'] . '</font></td>
	</tr>';
	echo '<tr>
		<th style="text-align: left">' . _('Freight Cost') . ':</th>
		<td class="OddTableRows"><font>' . $myrow['freightcost'] . '</font></td>
	</tr>';
	echo '<tr><th style="text-align: left">'._('Comments'). ': ';
	echo '</th><td colspan=3>'.$myrow['comments'] . '</td></tr>';
        echo '<tr><td colspan=8 class=number><input type=submit  Value="' . _('Update Customer') . '"  name="UpdateCustomer"></td></tr>';
	echo '</table>';
}

/*Now get the line items */

	$LineItemsSQL = "SELECT
				import_salesorderdetails.stkcode,
				import_salesorderdetails.unitprice,
				import_salesorderdetails.quantity,
				import_salesorderdetails.discountpercent,
				import_salesorderdetails.actualdispatchdate,
				import_salesorderdetails.qtyinvoiced,
                                import_salesorderdetails.narrative,
                                stockmaster.description,
                                import_salesorderdetails.orderlineno
			FROM import_salesorderdetails, stockmaster
			WHERE import_salesorderdetails.stkcode=stockmaster.stockid AND
                              import_salesorderdetails.orderno ='" . $OrderNumber . "'
                        ORDER BY import_salesorderdetails.orderlineno";

	$ErrMsg =  _('The line items of the order cannot be retrieved because');
	$DbgMsg =  _('The SQL used to retrieve the line items, that failed was');
	$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg, $DbgMsg);
        
       $LineItemsSQLCompare = "SELECT
				*
			FROM import_salesorderdetails, import_csv_salesorders
			WHERE import_salesorderdetails.ref_csvorderno = import_csv_salesorders.id AND 
                              import_salesorderdetails.orderno ='" . $OrderNumber . "'
                        ORDER BY import_salesorderdetails.orderlineno";
       $LineItemsCompareResult = db_query($LineItemsSQLCompare,$db, $ErrMsg, $DbgMsg);
        
        if(db_num_rows($LineItemsResult)!=db_num_rows($LineItemsCompareResult)){
    	 $LineItemsSQL = "SELECT
				import_salesorderdetails.stkcode,
				import_salesorderdetails.unitprice,
				import_salesorderdetails.quantity,
				import_salesorderdetails.discountpercent,
				import_salesorderdetails.actualdispatchdate,
				import_salesorderdetails.qtyinvoiced,
                                import_salesorderdetails.narrative,
                                import_csv_salesorders.productname,
                                import_salesorderdetails.orderlineno
			FROM import_salesorderdetails, import_csv_salesorders
			WHERE import_salesorderdetails.ref_csvorderno = import_csv_salesorders.id AND 
                              import_salesorderdetails.orderno ='" . $OrderNumber . "'
                        ORDER BY import_salesorderdetails.orderlineno";

	$ErrMsg =  _('The line items of the order cannot be retrieved because');
	$DbgMsg =  _('The SQL used to retrieve the line items, that failed was');
	$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg, $DbgMsg);
    }
	if (db_num_rows($LineItemsResult)>0) {

		$OrderTotal = 0;

                echo '<input type="hidden" name="OrderLineItemsTotal" value="' . db_num_rows($LineItemsResult) . '" />';
		echo '<br><table cellpadding=2 colspan=9 class=selection>';
		echo '<tr><th colspan=9><font color=blue>'._('Order Line Details For Order No').' '.$OrderNumber.'</font></th></tr>';
		echo '<tr>
			<th>' . _('Item Code') . '</th>
			<th>' . _('Item Description') . '</th>
                        <th>' . _('Narrative') . '</th>
			<th>' . _('Quantity') . '</th>
			<th>' . _('Price') . '</th>
			<th>' . _('Discount') . '</th>
			<th>' . _('Total') . '</th>
			<th>' . _('Qty Del') . '</th>
			<th>' . _('Last Del') . '</th>
			</tr>';
		$k=0;
                $i=0;
		while ($myrow=db_fetch_array($LineItemsResult)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			if ($myrow['qtyinvoiced']>0){
				$DisplayActualDeliveryDate = ConvertSQLDate($myrow['actualdispatchdate']);
			} else {
		  		$DisplayActualDeliveryDate = _('N/A');
			}

                        $ProductDesc=empty($myrow['productname'])?$myrow['description']:$myrow['productname'];
			echo 	'<td><input type="text" name="stockCode_' . $i . '" size=16 maxlength=16 value="'.$myrow['stkcode'].'"></td>
				<td>' . $ProductDesc . '</td>
                                <td>' . $myrow['narrative'] . '</td>    
				<td class=number>' . $myrow['quantity'] . '</td>
				<td class=number>' . number_format($myrow['unitprice'],2) . '</td>
				<td class=number>' . number_format(($myrow['discountpercent'] * 100),2) . '%' . '</td>
				<td class=number>' . number_format($myrow['quantity'] * $myrow['unitprice'] * (1 - $myrow['discountpercent']),2) . '</td>
				<td class=number>' . number_format($myrow['qtyinvoiced'],2) . '</td>
				<td>' . $DisplayActualDeliveryDate . '</td>
			</tr>';
                        echo '<input type="hidden" name="stockLineno_' . $i . '" value="'.$myrow['orderlineno'].'">';

			$OrderTotal = $OrderTotal + $myrow['quantity'] * $myrow['unitprice'] * (1 - $myrow['discountpercent']);
                        $i++;

		}
		$DisplayTotal = number_format($OrderTotal,2);
           echo '<tr>
			<td colspan=5 class=number><b>' . _('TOTAL Excl Tax/Freight') . '</b></td>
			<td colspan=2 class=number>' . $DisplayTotal . '</td>
			</tr>';
		
echo '<tr><td colspan=8 class=number><input type=submit  Value="' . _('Update-Stockcode') . '"  name="UpdateStockCode"></td>
    <td  class=number><input type=submit  Value="' . _('Go Back') . '"  name="GoPreviousPage"></td></tr></table>';
echo '<input type="hidden" name="OrderNumber" value="' . $OrderNumber . '" />';
echo '</form>';
	}

include('includes/footer.inc');
?>