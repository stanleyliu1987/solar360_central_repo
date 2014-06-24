<?php

//s
/* $Id: SelectOrderItems.php 4571 2011-05-16 10:46:50Z daintree $ */

include('includes/DefineInvCartClass.php');

/* Session started in session.inc for password checking and authorisation level check
  config.php is in turn included in session.inc */

include('includes/session.inc');

if (isset($_GET['ModifyOrderNumber'])) {
    $title = _('Modifying Order') . ' ' . $_GET['ModifyOrderNumber'];
} else {
    $title = _('Select Order Items');
}

include('includes/header.inc');
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');
echo '<a href="' . $rootpath . '/OrderManagement.php">' . _('Back to Search Invoice') . '</a> ';


if (isset($_POST['order_items'])) {
    foreach ($_POST as $key => $value) {
        if (strstr($key, 'itm')) {
            $NewItem_array[substr($key, 3)] = trim($value);
        }
    }
}

if (isset($_GET['NewItem'])) {
    $NewItem = trim($_GET['NewItem']);
}


if (empty($_GET['identifier'])) {
    /* unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
    $identifier = date('U');
} else {
    $identifier = $_GET['identifier'];
}
/* Jump to freight cost calculation page */
if (isset($_POST['freightcost'])) {
    $_SESSION['Items' . $identifier]->FreightCost = $FreightTotal;
    echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/CostETACalculation.php?' . SID . 'identifier=' . $identifier . '&pre=' . _('InvoiceModification') . '">';
    prnMsg(_('You should automatically be forwarded to the entry of the cost estimation page') . '. ' . _('if this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' .
            '<a href="' . $rootpath . '/CostETACalculation.php?' . SID . 'identifier=' . $identifier . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
    exit;
}

if (isset($_GET['ModifyOrderNumber'])
        AND $_GET['ModifyOrderNumber'] != '') {

    /* The delivery check screen is where the details of the order are either updated or inserted depending on the value of ExistingOrder */

    if (isset($_SESSION['Items' . $identifier])) {
        unset($_SESSION['Items' . $identifier]->LineItems);
        unset($_SESSION['Items' . $identifier]);
    }
    $_SESSION['ExistingOrder'] = $_GET['ModifyOrderNumber'];
    $_SESSION['RequireCustomerSelection'] = 0;
    $_SESSION['Items' . $identifier] = new cart;

    /* read in all the guff from the selected order into the Items cart  */
    $file = "Invoice_Modification.php?identifier=" . $identifier;
    $OrderHeaderSQL = "SELECT salesorders.debtorno,
 				  debtorsmaster.name,
				  salesorders.branchcode,
				  salesorders.customerref,
				  salesorders.comments,
				  salesorders.orddate,
				  salesorders.ordertype,
				  salestypes.sales_type,
				  salesorders.shipvia,
				  salesorders.deliverto,
				  salesorders.deladd1,
				  salesorders.deladd2,
				  salesorders.deladd3,
				  salesorders.deladd4,
				  salesorders.deladd5,
				  salesorders.deladd6,
				  salesorders.contactphone,
				  salesorders.contactemail,
				  salesorders.freightcost,
				  salesorders.deliverydate,
				  debtorsmaster.currcode,
				  currencies.decimalplaces,
				  paymentterms.terms,
				  salesorders.fromstkloc,
				  salesorders.printedpackingslip,
				  salesorders.datepackingslipprinted,
				  salesorders.quotation,
				  salesorders.deliverblind,
				  debtorsmaster.customerpoline,
				  locations.locationname,
                                  locations.taxprovinceid,
				  custbranch.estdeliverydays,
				  custbranch.salesman,
                                  custbranch.taxgroupid,
                                  salesorders.orderno,
                                  debtortrans.rate
				FROM salesorders 
				INNER JOIN debtorsmaster 
				ON salesorders.debtorno = debtorsmaster.debtorno
				 INNER JOIN salestypes 
				 ON salesorders.ordertype=salestypes.typeabbrev
				 INNER JOIN custbranch 
				 ON salesorders.debtorno = custbranch.debtorno 
				 AND salesorders.branchcode = custbranch.branchcode
				 INNER JOIN paymentterms
				 ON debtorsmaster.paymentterms=paymentterms.termsindicator 
				 INNER JOIN locations  
				 ON locations.loccode=salesorders.fromstkloc 
				 INNER JOIN currencies
				 ON debtorsmaster.currcode=currencies.currabrev 
                                 INNER JOIN debtortrans
                                 ON debtortrans.order_= salesorders.orderno
                                AND salesorders.orderno<>''
				WHERE debtortrans.transno = '" . $_SESSION['ExistingOrder'] . "'";

    $ErrMsg = _('The order cannot be retrieved because');
    $GetOrdHdrResult = DB_query($OrderHeaderSQL, $db, $ErrMsg);

    if (DB_num_rows($GetOrdHdrResult) == 1) {

        $myrow = DB_fetch_array($GetOrdHdrResult);
        if ($_SESSION['SalesmanLogin'] != '' AND $_SESSION['SalesmanLogin'] != $myrow['salesman']) {
            prnMsg(_('Your account is set up to see only a specific salespersons orders. You are not authorised to modify this order'), 'error');
            include('includes/footer.inc');
            exit;
        }
        $_SESSION['CurrencyRate'] = $myrow['rate'];
        $_SESSION['salesorderNum'] = $myrow['orderno'];
        $_SESSION['Items' . $identifier]->IsModified = $_GET['ismodified'];
        $_SESSION['Items' . $identifier]->ModebtrId = $_GET['ModebtrId'];
        $_SESSION['Items' . $identifier]->OrderNo = $_SESSION['ExistingOrder'];
        $_SESSION['Items' . $identifier]->DebtorNo = $myrow['debtorno'];
        /* CustomerID defined in header.inc */
        $_SESSION['Items' . $identifier]->Branch = $myrow['branchcode'];
        $_SESSION['Items' . $identifier]->CustomerName = $myrow['name'];
        $_SESSION['Items' . $identifier]->CustRef = $myrow['customerref'];
        $_SESSION['Items' . $identifier]->Comments = stripcslashes($myrow['comments']);
        $_SESSION['Items' . $identifier]->PaymentTerms = $myrow['terms'];
        $_SESSION['Items' . $identifier]->DefaultSalesType = $myrow['ordertype'];
        $_SESSION['Items' . $identifier]->SalesTypeName = $myrow['sales_type'];
        $_SESSION['Items' . $identifier]->DefaultCurrency = $myrow['currcode'];
        $_SESSION['Items' . $identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];
        $_SESSION['Items' . $identifier]->ShipVia = $myrow['shipvia'];
        $BestShipper = $myrow['shipvia'];
        $_SESSION['Items' . $identifier]->DeliverTo = $myrow['deliverto'];
        $_SESSION['Items' . $identifier]->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
        $_SESSION['Items' . $identifier]->DelAdd1 = $myrow['deladd1'];
        $_SESSION['Items' . $identifier]->DelAdd2 = $myrow['deladd2'];
        $_SESSION['Items' . $identifier]->DelAdd3 = $myrow['deladd3'];
        $_SESSION['Items' . $identifier]->DelAdd4 = $myrow['deladd4'];
        $_SESSION['Items' . $identifier]->DelAdd5 = $myrow['deladd5'];
        $_SESSION['Items' . $identifier]->DelAdd6 = $myrow['deladd6'];
        $_SESSION['Items' . $identifier]->PhoneNo = $myrow['contactphone'];
        $_SESSION['Items' . $identifier]->Email = $myrow['contactemail'];
        $_SESSION['Items' . $identifier]->Location = $myrow['fromstkloc'];
        $_SESSION['Items' . $identifier]->LocationName = $myrow['locationname'];
        $_SESSION['Items' . $identifier]->Quotation = $myrow['quotation'];
        $_SESSION['Items' . $identifier]->FreightCost = $myrow['freightcost'];
        $_SESSION['Items' . $identifier]->Orig_OrderDate = $myrow['orddate'];
        $_SESSION['PrintedPackingSlip'] = $myrow['printedpackingslip'];
        $_SESSION['DatePackingSlipPrinted'] = $myrow['datepackingslipprinted'];
        $_SESSION['Items' . $identifier]->DeliverBlind = $myrow['deliverblind'];
        $_SESSION['Items' . $identifier]->DefaultPOLine = $myrow['customerpoline'];
        $_SESSION['Items' . $identifier]->DeliveryDays = $myrow['estdeliverydays'];
        $_SESSION['Items']->TaxGroup = $myrow['taxgroupid'];
        $_SESSION['Items']->DispatchTaxProvince = $myrow['taxprovinceid'];

        //Get The exchange rate used for GPPercent calculations on adding or amending items
        if ($_SESSION['Items' . $identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']) {
            $ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items' . $identifier]->DefaultCurrency . "'", $db);
            if (DB_num_rows($ExRateResult) > 0) {
                $ExRateRow = DB_fetch_row($ExRateResult);
                $ExRate = $ExRateRow[0];
            } else {
                $ExRate = 1;
            }
        } else {
            $ExRate = 1;
        }

        /* need to look up customer name from debtors master then populate the line items array with the sales order details records */

        $LineItemsSQL = "SELECT  stockmoves.stkmoveno,
                                                stockmoves.stockid,
						stockmaster.description,
                                                stockmaster.volume,
				                stockmaster.kgs,
						-stockmoves.qty as quantity,
						stockmoves.discountpercent,
						stockmoves.price,
                                                stockmoves.discountpercent,
                                                stockmoves.standardcost,
						stockmoves.narrative,
						stockmaster.controlled,
						stockmaster.units,
                                                stockmaster.mbflag,
						stockmoves.stkmoveno,
                                                stockmaster.taxcatid,
                                                salesorderdetails.itemdue,
                                                freightcostevaluation.freightamount,
                                                freightcostevaluation.height,
                                                freightcostevaluation.width,
                                                freightcostevaluation.length,
                                                freightcostevaluation.cube,
                                                freightcostevaluation.weight,
                                                freightcostevaluation.chargeweight,
                                                freightcostevaluation.shipper,
                                                freightcostevaluation.servicetype,
                                                freightcostevaluation.prefsupplier,
                                                freightcostevaluation.suppwarehouse,
                                                freightcostevaluation.comment
					FROM stockmoves,
					     stockmaster,
                                             salesorderdetails,
                                             debtortrans,
                                             freightcostevaluation
                               
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=10
					AND stockmoves.transno='" . $_SESSION['ExistingOrder'] . "'
					AND stockmoves.show_on_inv_crds=1
                                        AND stockmoves.transno=debtortrans.transno
                                        AND debtortrans.order_=salesorderdetails.orderno
                                        AND salesorderdetails.stkcode=stockmoves.stockid
                                        AND freightcostevaluation.salesorder =salesorderdetails.orderno 
                                        AND freightcostevaluation.itemcode= salesorderdetails.stkcode 
                                        AND freightcostevaluation.linenumber= salesorderdetails.orderlineno 
                                       ";


        $ErrMsg = _('The line items of the order cannot be retrieved because');
        $LineItemsResult = db_query($LineItemsSQL, $db, $ErrMsg);
        if (db_num_rows($LineItemsResult) > 0) {

            while ($myrow = db_fetch_array($LineItemsResult)) {
                $LineNumber = $_SESSION['Items' . $identifier]->LineCounter;

                $_SESSION['Items' . $identifier]->add_to_cart($myrow['stockid'], $myrow['quantity'], $myrow['description'], $myrow['price'], $myrow['discountpercent'], $myrow['stkmoveno'], $myrow['volume'], $myrow['kgs'], '', $myrow['mbflag'], '', '', '', '', '', '', $myrow['narrative'], 'No', -1, $myrow['taxcatid'], '', ConvertSQLDate($myrow['itemdue']), '', $myrow['standardcost'], '', '', '', $myrow['freightamount'], '', $_SESSION['Items' . $identifier]->DebtorNo, $_SESSION['ExistingOrder'], $myrow['height'], $myrow['width'], $myrow['length'], $myrow['cube'], $myrow['weight'], $myrow['chargeweight'], $myrow['shipper'], $myrow['servicetype'], $myrow['prefsupplier'], $myrow['suppwarehouse'], $myrow['comment']);

                $_SESSION['Items' . $identifier]->GetExistingTaxes($LineNumber, $myrow['stkmoveno']);
                /* Just populating with existing order - no DBUpdates */
            }
            $LastLineNo = $myrow['orderlineno'];
        } /* line items from sales order details */
        $_SESSION['Items' . $identifier]->LineCounter = $LastLineNo + 1;
    } //end of checks on returned data set
}

if (isset($_POST['CancelOrder'])) {
    $OK_to_delete = 1; //assume this in the first instance

    /*
     *  Validate the Order Can or Can't be Cancelled
     */
    $OK_to_delete = CheckAllPOStatusCompleted($_SESSION['ExistingOrder'], $db);


    if ($OK_to_delete == 1) {
        /*
         *  Cancel the Invoice, Return the Stock back
         */
        $SQL = DB_Txn_Begin($db);

        if ($_SESSION['ExistingOrder'] != 0) {

            /* Update invoice details to null, and invoice tax to 0 */
            $SQL = "UPDATE debtortrans, debtortranstaxes SET debtortranstaxes.taxamount=0,
                                       debtortrans.mod_flag=2,
                                       debtortrans.order_stages=5,
                                       debtortrans.settled=0,
                                       debtortrans.ovamount =0,
                                       debtortrans.ovgst =0,
                                       debtortrans.ovfreight=0,
                                       debtortrans.alloc=0
                                       WHERE 
                                       debtortrans.id= debtortranstaxes.debtortransid AND                                   
                                       debtortrans.transno ='" . $_SESSION['ExistingOrder'] . "' and debtortrans.type=10";
            $ErrMsg = _('The invoice detail lines could not be deleted because');
            $DelResult = DB_query($SQL, $db, $ErrMsg, true);

            foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {

                $SQL = "UPDATE locstock SET quantity=quantity-(SELECT sum(qty) FROM stockmoves 
                          WHERE type=10 and transno= '" . $_SESSION['Items' . $identifier]->OrderNo . "'
                          and  stockid='" . $OrderLine->StockID . "') WHERE loccode=001 and stockid='" . $OrderLine->StockID . "'";

                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('CANNOT UPDATE AVAILABLE STOCK in Locstock table') . '';
                $DbgMsg = _('The following SQL to update the invoiced was used');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                $SQL = "UPDATE stockmoves SET qty=0 WHERE type=10 and transno= '" . $_SESSION['Items' . $identifier]->OrderNo . "'
                          and  stockid='" . $OrderLine->StockID . "'";

                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('CANNOT UPDATE AVAILABLE Qty in Stockmoves table') . '';
                $DbgMsg = _('The following SQL to update the invoiced was used');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                $SQL = "UPDATE salesorderdetails SET qtyinvoiced=0 ,
                                                              quantity=0 
                                                              WHERE  orderno= '" . ConvertTranToInv($_SESSION['Items' . $identifier]->OrderNo, $db, $rootpath, $file) . "'
                          and  stkcode='" . $OrderLine->StockID . "'";

                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('CANNOT UPDATE AVAILABLE Qty in salesorderdetails table') . '';
                $DbgMsg = _('The following SQL to update the salesorderdetails was used');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                
                /*08042014 by Stan update freightcostevaluation table */
                $SQL = "UPDATE freightcostevaluation SET quantity=0 ,
                                                         height=0,
                                                         width=0,
                                                         length=0,
                                                         cube=0,
                                                         weight=0,
                                                         chargeweight=0,
                                                         freightamount=0
                        WHERE salesorder= '" . ConvertTranToInv($_SESSION['Items' . $identifier]->OrderNo, $db, $rootpath, $file) . "'
                        and  itemcode='" . $OrderLine->StockID . "'";

                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('CANNOT UPDATE datat in Freightcostevaluation table') . '';
                $DbgMsg = _('The following SQL to update the freightcostevaluation was used');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);                
                /* End of updating process*/

                /* Delete records in salesanalysis table */

                $SalesAnalyTime = GetPeriod(Date($_SESSION['DefaultDateFormat'], CalcEarliestDispatchDate()), $db);

                $SQL = "SELECT COUNT(*),
						salesanalysis.stockid,
						salesanalysis.stkcategory,
						salesanalysis.cust,
						salesanalysis.custbranch,
						salesanalysis.area,
						salesanalysis.periodno,
						salesanalysis.typeabbrev,
						salesanalysis.salesperson
					FROM salesanalysis,
						custbranch,
						stockmaster
					WHERE salesanalysis.stkcategory=stockmaster.categoryid
					AND salesanalysis.stockid=stockmaster.stockid
					AND salesanalysis.cust=custbranch.debtorno
					AND salesanalysis.custbranch=custbranch.branchcode
					AND salesanalysis.area=custbranch.area
					AND salesanalysis.salesperson=custbranch.salesman
					AND salesanalysis.typeabbrev ='" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'
					AND salesanalysis.periodno='" . $SalesAnalyTime . "'
					AND salesanalysis.cust " . LIKE . " '" . $_SESSION['Items' . $identifier]->DebtorNo . "'
					AND salesanalysis.custbranch " . LIKE . " '" . $_SESSION['Items' . $identifier]->Branch . "'
					AND salesanalysis.stockid " . LIKE . " '" . $OrderLine->StockID . "'
					AND salesanalysis.budgetoractual=1
					GROUP BY salesanalysis.stockid,
						salesanalysis.stkcategory,
						salesanalysis.cust,
						salesanalysis.custbranch,
						salesanalysis.area,
						salesanalysis.periodno,
						salesanalysis.typeabbrev,
						salesanalysis.salesperson";

                $ErrMsg = _('The count of existing Sales analysis records could not run because');
                $DbgMsg = '<br />' . _('SQL to count the no of sales analysis records');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                $myrow = DB_fetch_row($Result);

                if ($myrow[0] > 0) { /* Update the existing record that already exists */

                    $SQL = "DELETE from salesanalysis   
			               WHERE salesanalysis.area='" . $myrow[5] . "'
					     AND salesanalysis.salesperson='" . $myrow[8] . "'
				             AND typeabbrev ='" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'
					     AND periodno = '" . $SalesAnalyTime . "'
					     AND cust " . LIKE . " '" . $_SESSION['Items' . $identifier]->DebtorNo . "'
					     AND custbranch " . LIKE . " '" . $_SESSION['Items' . $identifier]->Branch . "'
					     AND stockid " . LIKE . " '" . $OrderLine->StockID . "'
					     AND salesanalysis.stkcategory ='" . $myrow[2] . "'
					     AND budgetoractual=1";

                    $ErrMsg = _('Sales analysis record could not be deleted because');
                    $DbgMsg = _('The following SQL to insert the sales analysis record was used');
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                }
            }

            /* If custmoer has been allocated, re-allocate the amount */
            $InvoiceIDResult = DB_query("SELECT id FROM debtortrans WHERE debtortrans.transno ='" . $_SESSION['ExistingOrder'] . "' and debtortrans.type=10", $db);
            $myrow = DB_fetch_row($InvoiceIDResult);
            $invoiceid = $myrow[0];
            
            $ReceiptResult = DB_query("SELECT transid_allocfrom, amt FROM custallocns WHERE custallocns.transid_allocto ='" . $invoiceid . "'", $db);
            while ($receipt = DB_fetch_array($ReceiptResult, $db)) {
                
           /* update receipt allocation amount */
            $SQL="update debtortrans set alloc=0 where id=".$receipt['transid_allocfrom'];
            $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The receipt allocation amount cannot be updated');
            $DbgMsg = _('The following SQL to update receipt allocation amount');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            
           /* update custallocns amount */
            $SQL="update custallocns set amt=0 where transid_allocfrom=".$receipt['transid_allocfrom']." and transid_allocto=".$invoiceid;  
            $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The custallocns table cannot be updated');
            $DbgMsg = _('The following SQL to update custallocns amount');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            }
            /* Upadte amounts in GL table to be 0, type is invoice */
            $SQL = "Update gltrans set amount=0   
	                       WHERE type=10 and typeno= '" . $_SESSION['Items' . $identifier]->OrderNo . "'";

            $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The invoice GL cannot be updated');
            $DbgMsg = _('The following SQL to update Invoice GL was used');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

            /**
             * Update the Related PO status to Cancelled
             */
            $_SESSION['PO' . $identifier]->StatusComments = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Cancelled') . ' ' . _('by ') . $_SESSION['UsersRealName'] . '<br />';
            $SQL = "UPDATE purchorders SET status='Cancelled',
							stat_comment=CONCAT('" . $_SESSION['PO' . $identifier]->StatusComments . "',stat_comment),
							allowprint=0
					WHERE purchorders.ref_salesorder ='" . ConvertTranToInv($_SESSION['ExistingOrder'], $db, $rootpath, $file) . "'";

            $ErrMsg = _('The order status could not be updated to cancelled because');
            $DbgMsg = _('The following SQL to update the purchase order was used');
            $UpdateToCancellResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            $_SESSION['ExistingOrder'] = 0;
        }
        $SQL = DB_Txn_Commit($db);

        unset($_SESSION['Items' . $identifier]->LineItems);
        $_SESSION['Items' . $identifier]->ItemsOrdered = 0;
        unset($_SESSION['Items' . $identifier]);
        $_SESSION['Items' . $identifier] = new cart;

        if (in_array(2, $_SESSION['AllowedPageSecurityTokens'])) {
            $_SESSION['RequireCustomerSelection'] = 1;
        } else {
            $_SESSION['RequireCustomerSelection'] = 0;
        }
        echo '<br /><br />';
        prnMsg(_('This Invoice has been cancelled as requested'), 'success');
        include('includes/footer.inc');
        exit;
    } else {
        /*
         *  Invoice Cannot be Cancelled, display the Error Message
         */
        prnMsg('One or More PO in that Invoice is already completed, You Cannt do the Cancel Action', 'error');
    }
} else { /* Not cancelling the order */

    echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/inventory.png" title="' . _('Order') . '" alt="" />' . ' ';


    echo _('Invoice for') . ' ';

    echo '<b>Customer Name: ' . $_SESSION['Items' . $identifier]->CustomerName . '&nbsp;&nbsp;';
    echo 'Branch Name: ' . $_SESSION['Items' . $identifier]->CustomerName . '</b></p>';

    echo '<div class="page_help_text">' . '<b>' . _('') . '</b><br />' . _('Deliver To') . ':<b> ' . $_SESSION['Items' . $identifier]->DeliverTo;
    echo '</b>&nbsp;' . _('From Location') . ':<b> ' . $_SESSION['Items' . $identifier]->LocationName;
    echo '</b><br />' . _('Sales Type') . '/' . _('Price List') . ':<b> ' . $_SESSION['Items' . $identifier]->SalesTypeName;
    echo '</b><br />' . _('Terms') . ':<b> ' . $_SESSION['Items' . $identifier]->PaymentTerms;
    echo '</b><br />* Completed PO cannot further modify its item details';
    echo '</b></div>';
}
$msg = '';
if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev'])) {


    if (isset($_POST['Keywords']) AND strlen($_POST['Keywords']) > 0) {
        //insert wildcard characters in spaces
        $_POST['Keywords'] = strtoupper($_POST['Keywords']);
        $SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
//Modified tomorrow
        if ($_POST['StockCat'] == 'All') {

            $SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster,
								stockcategory
						WHERE stockmaster.categoryid=stockcategory.categoryid
						AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
						AND stockmaster.mbflag <>'G'
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
						AND stockmaster.discontinued=0
                                                AND stockmaster.stockid not in (SELECT  
                                        stockmoves.stockid
					FROM stockmoves
	                        	WHERE
					stockmoves.type=10
					AND stockmoves.transno=" . $_SESSION['Items' . $identifier]->OrderNo . "
					AND stockmoves.show_on_inv_crds=1)
						ORDER BY stockmaster.stockid";
        } else {

            $SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster, stockcategory
						WHERE  stockmaster.categoryid=stockcategory.categoryid
						AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
                                                AND stockmaster.stockid not in (SELECT  
                                        stockmoves.stockid
					FROM stockmoves
	                        	WHERE
					stockmoves.type=10
					AND stockmoves.transno=" . $_SESSION['Items' . $identifier]->OrderNo . "
					AND stockmoves.show_on_inv_crds=1)
						ORDER BY stockmaster.stockid";
        }
    } elseif (strlen($_POST['StockCode']) > 0) {

        $_POST['StockCode'] = strtoupper($_POST['StockCode']);
        $SearchString = '%' . $_POST['StockCode'] . '%';

        if ($_POST['StockCat'] == 'All') {


            $SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster, stockcategory
						WHERE stockmaster.categoryid=stockcategory.categoryid
						AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
						AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
                                                AND stockmaster.stockid not in (SELECT  
                                        stockmoves.stockid
					FROM stockmoves
	                        	WHERE
					stockmoves.type=10
					AND stockmoves.transno=" . $_SESSION['Items' . $identifier]->OrderNo . "
					AND stockmoves.show_on_inv_crds=1)
						ORDER BY stockmaster.stockid";
        } else {


            $SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster, stockcategory
						WHERE stockmaster.categoryid=stockcategory.categoryid
						AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
						AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
                                                AND stockmaster.stockid not in (SELECT  
                                        stockmoves.stockid
					FROM stockmoves
	                        	WHERE
					stockmoves.type=10
					AND stockmoves.transno=" . $_SESSION['Items' . $identifier]->OrderNo . "
					AND stockmoves.show_on_inv_crds=1)
						ORDER BY stockmaster.stockid";
        }
    } else {
        if ($_POST['StockCat'] == 'All') {

            $SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster, stockcategory
						WHERE  stockmaster.categoryid=stockcategory.categoryid
						AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
                                                AND stockmaster.stockid not in (SELECT  
                                        stockmoves.stockid
					FROM stockmoves
	                        	WHERE
					stockmoves.type=10
					AND stockmoves.transno=" . $_SESSION['Items' . $identifier]->OrderNo . "
					AND stockmoves.show_on_inv_crds=1)
					ORDER BY stockmaster.stockid";
        } else {


            $SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster, stockcategory
						WHERE stockmaster.categoryid=stockcategory.categoryid
						AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
                                                AND stockmaster.stockid not in (SELECT  
                                        stockmoves.stockid
					FROM stockmoves
	                        	WHERE
					stockmoves.type=10
					AND stockmoves.transno=" . $_SESSION['Items' . $identifier]->OrderNo . "
					AND stockmoves.show_on_inv_crds=1)
						ORDER BY stockmaster.stockid";
        }
    }

    if (isset($_POST['Next'])) {
        $Offset = $_POST['nextlist'];
    }
    if (isset($_POST['Prev'])) {
        $Offset = $_POST['previous'];
    }
    if (!isset($Offset) or $Offset < 0) {
        $Offset = 0;
    }
    $SQL = $SQL . " LIMIT " . $_SESSION['DefaultDisplayRecordsMax'] . " OFFSET " . number_format($_SESSION['DefaultDisplayRecordsMax'] * $Offset);

    $ErrMsg = _('There is a problem selecting the part records to display because');
    $DbgMsg = _('The SQL used to get the part selection was');
    $SearchResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

    if (DB_num_rows($SearchResult) == 0) {
        prnMsg(_('There are no products available meeting the criteria specified'), 'info');
    }
    if (DB_num_rows($SearchResult) == 1) {
        $myrow = DB_fetch_array($SearchResult);
        $NewItem = $myrow['stockid'];
        DB_data_seek($SearchResult, 0);
    }
    if (DB_num_rows($SearchResult) < $_SESSION['DisplayRecordsMax']) {
        $Offset = 0;
    }
} //end of if search
#Always do the stuff below if not looking for a customerid

echo '<form action="' . $_SERVER['PHP_SELF'] . '?identifier=' . $identifier . '" name="SelectParts" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

//Get The exchange rate used for GPPercent calculations on adding or amending items
if ($_SESSION['Items' . $identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']) {
    $ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items' . $identifier]->DefaultCurrency . "'", $db);
    if (DB_num_rows($ExRateResult) > 0) {
        $ExRateRow = DB_fetch_row($ExRateResult);
        $ExRate = $ExRateRow[0];
    } else {
        $ExRate = 1;
    }
} else {
    $ExRate = 1;
}

/* Process Quick Entry */
/* If enter is pressed on the quick entry screen, the default button may be Recalculate */
if (isset($_POST['order_items'])
        OR isset($_POST['Recalculate'])) {

    /* get the item details from the database and hold them in the cart object */

    /* Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
    $Discount = 0;

    $i = 1;
    while ($i <= $_SESSION['QuickEntries'] and isset($_POST['part_' . $i]) and $_POST['part_' . $i] != '') {
        $QuickEntryCode = 'part_' . $i;
        $QuickEntryQty = 'qty_' . $i;
        $QuickEntryPOLine = 'poline_' . $i;
        $QuickEntryItemDue = 'itemdue_' . $i;

        $i++;

        if (isset($_POST[$QuickEntryCode])) {
            $NewItem = strtoupper($_POST[$QuickEntryCode]);
        }
        if (isset($_POST[$QuickEntryQty])) {
            $NewItemQty = $_POST[$QuickEntryQty];
        }
        if (isset($_POST[$QuickEntryItemDue])) {
            $NewItemDue = $_POST[$QuickEntryItemDue];
        } else {
            $NewItemDue = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $identifier]->DeliveryDays);
        }
        if (isset($_POST[$QuickEntryPOLine])) {
            $NewPOLine = $_POST[$QuickEntryPOLine];
        } else {
            $NewPOLine = 0;
        }

        if (!isset($NewItem)) {
            unset($NewItem);
            break; /* break out of the loop if nothing in the quick entry fields */
        }

        if (!Is_Date($NewItemDue)) {
            prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $NewItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'warn');
            //Attempt to default the due date to something sensible?
            $NewItemDue = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $identifier]->DeliveryDays);
        }
        /* Now figure out if the item is a kit set - the field MBFlag='K' */
        $sql = "SELECT stockmaster.mbflag
							FROM stockmaster
							WHERE stockmaster.stockid='" . $NewItem . "'";

        $ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
        $DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
        $KitResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);


        if (DB_num_rows($KitResult) == 0) {
            prnMsg(_('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database and has not been added to the order'), 'warn');
        } elseif ($myrow = DB_fetch_array($KitResult)) {
            if ($myrow['mbflag'] == 'K') { /* It is a kit set item */
                $sql = "SELECT bom.component,
							bom.quantity
							FROM bom
							WHERE bom.parent='" . $NewItem . "'";
                //		AND bom.effectiveto > '" . Date('Y-m-d') . "'
                //		AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

                $ErrMsg = _('Could not retrieve kitset components from the database because') . ' ';
                $KitResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

                $ParentQty = $NewItemQty;
                while ($KitParts = DB_fetch_array($KitResult, $db)) {
                    $NewItem = $KitParts['component'];
                    $NewItemQty = $KitParts['quantity'] * $ParentQty;
                    $NewPOLine = 0;
                    include('includes/SelectInvItems_IntoCart.inc');
                }
            } elseif ($myrow['mbflag'] == 'G') {
                prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
            } else { /* Its not a kit set item */
                include('includes/SelectInvItems_IntoCart.inc');
            }
        }
    }
    unset($NewItem);
} /* end of if quick entry */

/* Now do non-quick entry delete/edits/adds */

if ((isset($_SESSION['Items' . $identifier])) OR isset($NewItem)) {

    if (isset($_GET['Delete'])) {
        //page called attempting to delete a line - GET['Delete'] = the line number to delete
        $QuantityAlreadyDelivered = $_SESSION['Items' . $identifier]->Some_Already_Delivered($_GET['Delete']);
        if ($QuantityAlreadyDelivered == 0) {
            $_SESSION['Items' . $identifier]->remove_from_cart($_GET['Delete'], 'Yes');  /* Do update DB */
            //  echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/Invoice_Modification.php?&ModifyOrderNumber=' .$_SESSION['ExistingOrder']. '">';
        } else {
            $_SESSION['Items' . $identifier]->LineItems[$_GET['Delete']]->Quantity = $QuantityAlreadyDelivered;
        }
    }

    foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {

        if (isset($_POST['Quantity_' . $OrderLine->LineNumber])) {

            $Quantity = $_POST['Quantity_' . $OrderLine->LineNumber];

            if (ABS($OrderLine->Price - $_POST['Price_' . $OrderLine->LineNumber]) > 0.01) {
                $Price = $_POST['Price_' . $OrderLine->LineNumber];
                $_POST['GPPercent_' . $OrderLine->LineNumber] = (($Price * (1 - ($_POST['Discount_' . $OrderLine->LineNumber] / 100))) - $OrderLine->StandardCost * $ExRate) / ($Price * (1 - $_POST['Discount_' . $OrderLine->LineNumber]) / 100);
            } elseif (ABS($OrderLine->GPPercent - $_POST['GPPercent_' . $OrderLine->LineNumber]) >= 0.001) {
                //then do a recalculation of the price at this new GP Percentage
                $Price = ($OrderLine->StandardCost * $ExRate) / (1 - (($_POST['GPPercent_' . $OrderLine->LineNumber] + $_POST['Discount_' . $OrderLine->LineNumber]) / 100));
            } else {
                $Price = $_POST['Price_' . $OrderLine->LineNumber];
            }
            $DiscountPercentage = $_POST['Discount_' . $OrderLine->LineNumber];
            if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
                $Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
            } else {
                $Narrative = '';
            }

            if (!isset($OrderLine->DiscountPercent)) {
                $OrderLine->DiscountPercent = 0;
            }

            if (!Is_Date($_POST['ItemDue_' . $OrderLine->LineNumber])) {
                prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $ItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'warn');
                //Attempt to default the due date to something sensible?
                $_POST['ItemDue_' . $OrderLine->LineNumber] = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $identifier]->DeliveryDays);
            }
            if ($Quantity < 0 OR $Price < 0 OR $DiscountPercentage > 100 OR $DiscountPercentage < 0) {
                prnMsg(_('The item could not be updated because you are attempting to set the quantity ordered to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'), 'warn');
            } elseif ($_SESSION['Items' . $identifier]->Some_Already_Delivered($OrderLine->LineNumber) != 0 AND $_SESSION['Items' . $identifier]->LineItems[$OrderLine->LineNumber]->Price != $Price) {
                prnMsg(_('The item you attempting to modify the price for has already had some quantity invoiced at the old price the items unit price cannot be modified retrospectively'), 'warn');
            } elseif ($_SESSION['Items' . $identifier]->Some_Already_Delivered($OrderLine->LineNumber) != 0 AND $_SESSION['Items' . $identifier]->LineItems[$OrderLine->LineNumber]->DiscountPercent != ($DiscountPercentage / 100)) {

                prnMsg(_('The item you attempting to modify has had some quantity invoiced at the old discount percent the items discount cannot be modified retrospectively'), 'warn');
            } elseif ($_SESSION['Items' . $identifier]->LineItems[$OrderLine->LineNumber]->QtyInv > $Quantity) {
                prnMsg(_('You are attempting to make the quantity ordered a quantity less than has already been invoiced') . '. ' . _('The quantity delivered and invoiced cannot be modified retrospectively'), 'warn');
            } elseif ($OrderLine->Quantity != $Quantity
                    OR $OrderLine->Price != $Price
                    OR ABS($OrderLine->DiscountPercent - $DiscountPercentage / 100) > 0.001
                    OR $OrderLine->Narrative != $Narrative
                    OR $OrderLine->ItemDue != $_POST['ItemDue_' . $OrderLine->LineNumber]
                    OR $OrderLine->POLine != $_POST['POLine_' . $OrderLine->LineNumber]) {

                $_SESSION['Items' . $identifier]->update_cart_item($OrderLine->LineNumber, $Quantity, $Price, ($DiscountPercentage / 100), $Narrative, 'No', /* Update DB */ $_POST['ItemDue_' . $OrderLine->LineNumber], $_POST['POLine_' . $OrderLine->LineNumber], $_POST['GPPercent_' . $OrderLine->LineNumber]);
            }
        } //page not called from itself - POST variables not set
    }
}
if (isset($_POST['DeliveryDetails'])) {

    include('includes/UpdateInvoiceDetails.inc');
}


if (isset($NewItem)) {
    /* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
    /* Now figure out if the item is a kit set - the field MBFlag='K' */
    $sql = "SELECT stockmaster.mbflag
		   		FROM stockmaster
				WHERE stockmaster.stockid='" . $NewItem . "'";

    $ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');

    $KitResult = DB_query($sql, $db, $ErrMsg);

    $NewItemQty = 1; /* By Default */
    $Discount = 0; /* By default - can change later or discount category override */

    if ($myrow = DB_fetch_array($KitResult)) {
        if ($myrow['mbflag'] == 'K') { /* It is a kit set item */
            $sql = "SELECT bom.component,
							bom.quantity
						FROM bom
						WHERE bom.parent='" . $NewItem . "'";
            //	AND bom.effectiveto > '" . Date('Y-m-d') . "'
            //	AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

            $ErrMsg = _('Could not retrieve kitset components from the database because');
            $KitResult = DB_query($sql, $db, $ErrMsg);

            $ParentQty = $NewItemQty;
            while ($KitParts = DB_fetch_array($KitResult, $db)) {
                $NewItem = $KitParts['component'];
                $NewItemQty = $KitParts['quantity'] * $ParentQty;
                $NewPOLine = 0;
                $NewItemDue = date($_SESSION['DefaultDateFormat']);
                include('includes/SelectInvItems_IntoCart.inc');
            }
        } else { /* Its not a kit set item */
            $NewItemDue = date($_SESSION['DefaultDateFormat']);
            $NewPOLine = 0;

            include('includes/SelectInvItems_IntoCart.inc');
        }
    } /* end of if its a new item */
} /* end of if its a new item */

if (isset($NewItem_array) AND isset($_POST['order_items'])) {
    /* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
    /* Now figure out if the item is a kit set - the field MBFlag='K' */

    foreach ($NewItem_array as $NewItem => $NewItemQty) {
        if ($NewItemQty > 0) {
            $sql = "SELECT stockmaster.mbflag
									FROM stockmaster
									WHERE stockmaster.stockid='" . $NewItem . "'";

            $ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');

            $KitResult = DB_query($sql, $db, $ErrMsg);

            //$NewItemQty = 1; /*By Default */
            $Discount = 0; /* By default - can change later or discount category override */


            if ($myrow = DB_fetch_array($KitResult)) {
                if ($myrow['mbflag'] == 'K') { /* It is a kit set item */
                    $sql = "SELECT bom.component,
														bom.quantity
											FROM bom
											WHERE bom.parent='" . $NewItem . "'";


                    $ErrMsg = _('Could not retrieve kitset components from the database because');
                    $KitResult = DB_query($sql, $db, $ErrMsg);

                    $ParentQty = $NewItemQty;
                    while ($KitParts = DB_fetch_array($KitResult, $db)) {

                        $NewItem = $KitParts['component'];
                        $NewItemQty = $KitParts['quantity'] * $ParentQty;
                        $NewItemDue = date($_SESSION['DefaultDateFormat']);
                        $NewPOLine = 0;
                        include('includes/SelectInvItems_IntoCart.inc');
                    }
                } else { /* Its not a kit set item */
                    $NewItemDue = date($_SESSION['DefaultDateFormat']);
                    $NewPOLine = 0;
                    include('includes/SelectInvItems_IntoCart.inc');
                }
            } /* end of if its a new item */
        } /* end of if its a new item */
    }
}

/* Run through each line of the order and work out the appropriate discount from the discount matrix */
$DiscCatsDone = array();
$counter = 0;
foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {

    if ($OrderLine->DiscCat != "" AND !in_array($OrderLine->DiscCat, $DiscCatsDone)) {
        $DiscCatsDone[$counter] = $OrderLine->DiscCat;
        $QuantityOfDiscCat = 0;

        foreach ($_SESSION['Items' . $identifier]->LineItems as $StkItems_2) {
            /* add up total quantity of all lines of this DiscCat */
            if ($StkItems_2->DiscCat == $OrderLine->DiscCat) {
                $QuantityOfDiscCat += $StkItems_2->Quantity;
            }
        }
        $result = DB_query("SELECT MAX(discountrate) AS discount
								FROM discountmatrix
								WHERE salestype='" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'
								AND discountcategory ='" . $OrderLine->DiscCat . "'
								AND quantitybreak <" . $QuantityOfDiscCat, $db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0] != 0) { /* need to update the lines affected */
            foreach ($_SESSION['Items' . $identifier]->LineItems as $StkItems_2) {
                /* add up total quantity of all lines of this DiscCat */
                if ($StkItems_2->DiscCat == $OrderLine->DiscCat AND $StkItems_2->DiscountPercent == 0) {
                    $_SESSION['Items' . $identifier]->LineItems[$StkItems_2->LineNumber]->DiscountPercent = $myrow[0];
                }
            }
        }
    }
} /* end of discount matrix lookup code */

if (count($_SESSION['Items' . $identifier]->LineItems) > 0) { /* only show order lines if there are any */

    /* This is where the order as selected should be displayed  reflecting any deletions or insertions */

    echo '<br />
				<table width="90%" cellpadding="2" colspan="7">
				<tr bgcolor=#800000>';
    if ($_SESSION['Items' . $identifier]->DefaultPOLine == 1) {
        echo '<th>' . _('PO Line') . '</th>';
    }
    echo '<div class="page_help_text">' . _('Quantity (required).  Price (required).  Discount (optional).  Due Date (optional). Tax (optional). Freight (optional)') . '</div><br />';
    echo '<th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Price') . '</th>';

    if (in_array(2, $_SESSION['AllowedPageSecurityTokens'])) {
        echo '<th>' . _('Discount %') . '</th>';
    }


    echo '
		       <th>' . _('Total Amount') . '</th>
                       <th>' . _('Due Date') . '</th>
                       <th>' . _('Tax Rate%') . '</th>  
                       <th>' . _('PO Status') . '</th>     
		      </tr>';

    $_SESSION['Items' . $identifier]->newtotalwithGST = 0;
    $_SESSION['Items' . $identifier]->totalVolume = 0;
    $_SESSION['Items' . $identifier]->totalWeight = 0;
    $k = 0;  //row colour counter
    foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {

        /* Check PO Completed or not, if completed then make the item line non-editable */
        $POStatus = TrackPOStatus(ConvertTranToInv($_SESSION['Items' . $identifier]->OrderNo, $db, $rootpath, $file), $OrderLine->StockID, $db);

        $LineDueDate = ConvertSQLDate($OrderLine->ItemDue);

        if ($POStatus == 'Completed') {
            $editoption = "readonly";
            $duedateText = '<input type=text  alt="' . $_SESSION['DefaultDateFormat'] . '" name="ItemDue_' . $OrderLine->LineNumber . '" size=10 maxlength=10 value=' . ConvertSQLDate($LineDueDate) . ' ' . $editoption . '>';
        } else {
            $editoption = '';
            $duedateText = '<input type=text class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ItemDue_' . $OrderLine->LineNumber . '" size=10 maxlength=10 value=' . ConvertSQLDate($LineDueDate) . '>';
        }

        $LineTotalWithoutDis += $OrderLine->Quantity * $OrderLine->Price;
        $LineTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
        $DisplayLineTotal = number_format($LineTotal, $_SESSION['Items' . $identifier]->CurrDecimalPlaces);
        $DisplayDiscount = number_format(($OrderLine->DiscountPercent * 100), 2);
        $QtyOrdered = $OrderLine->Quantity;
        $QtyRemain = $QtyOrdered - $OrderLine->QtyInv;

        if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag == 'B' OR $OrderLine->MBflag == 'M')) {
            /* There is a stock deficiency in the stock location selected */
            $RowStarter = '<tr bgcolor="#EEAABB">'; //rows show red where stock deficiency
        } elseif ($k == 1) {
            $RowStarter = '<tr class="OddTableRows">';
            $k = 0;
        } else {
            $RowStarter = '<tr class="EvenTableRows">';
            $k = 1;
        }

        echo $RowStarter;
        if ($_SESSION['Items' . $identifier]->DefaultPOLine == 1) { //show the input field only if required
            echo '<td><input tabindex=1 type=text name="POLine_' . $OrderLine->LineNumber . '" size=20 maxlength=20 value=' . $OrderLine->POLine . '></td>';
        } else {
            echo '<input type="hidden" name="POLine_' . $OrderLine->LineNumber . '" value="">';
        }

        echo '<td><a target="_blank" href="' . $rootpath . '/StockStatus.php?identifier=' . $identifier . '&StockID=' . $OrderLine->StockID . '&DebtorNo=' . $_SESSION['Items' . $identifier]->DebtorNo . '">' . $OrderLine->StockID . '</a></td>
				<td>' . $OrderLine->ItemDescription . '</td>';

        echo '<td><input class="number" tabindex=2 type=text name="Quantity_' . $OrderLine->LineNumber . '" size=6 maxlength=6 value=' . $OrderLine->Quantity . ' ' . $editoption . '>';
        if ($QtyRemain != $QtyOrdered) {
            echo '<br />' . $OrderLine->QtyInv . ' of ' . $OrderLine->Quantity . ' invoiced';
        }
        echo '</td>';


        if (in_array(2, $_SESSION['AllowedPageSecurityTokens'])) {
            /* OK to display with discount if it is an internal user with appropriate permissions */
            echo '<td><input class="number" type=text size=12 maxlength=12 name="Price_' . $OrderLine->LineNumber . '" size=16 maxlength=16 value=' . $OrderLine->Price . ' ' . $editoption . '>
                                          <input class="number" type=hidden name="GPPercent_' . $OrderLine->LineNumber . '" size=3 maxlength=40 value=' . $OrderLine->GPPercent . '></td>                            
			              <td><input class="number" type=text size=6 maxlength=6 name="Discount_' . $OrderLine->LineNumber . '" size=5 maxlength=4 value=' . ($OrderLine->DiscountPercent * 100) . ' ' . $editoption . '></td>';
        } else {
            echo '<td class=number>' . $OrderLine->Price . '</td><td></td>';
            echo '<input type=hidden name="Price_' . $OrderLine->LineNumber . '" value=' . $OrderLine->Price . '>
			              <td><input class="number" type=text size=6 maxlength=6 name="Discount_' . $OrderLine->LineNumber . '" size=5 maxlength=4 value=' . ($OrderLine->DiscountPercent * 100) . ' ' . $editoption . '></td>';
        }
        if ($_SESSION['Items' . $identifier]->Some_Already_Delivered($OrderLine->LineNumber)) {
            $RemTxt = _('Clear Remaining');
        } else {
            $RemTxt = _('Delete');
        }
        echo '<td class=number>' . $DisplayLineTotal . '</td>';


//			if (empty($OrderLine->ItemDue)){
//                   
//				$LineDueDate = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
//				$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemDue= $LineDueDate;
//				
//			}

        echo '<td>' . $duedateText . '</td>';
        //echo '<td><input type=checkbox class="NoIncTax"  name="NoIncTax_' . $OrderLine->LineNumber . '" value=1 checked="yes"></td>';
        //echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?identifier=' . $identifier . '&Delete=' . $OrderLine->Stockmoveid. '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">' . $RemTxt . '</a></td></tr>';
        if (count($_SESSION['Items' . $identifier]->LineItems[$OrderLine->LineNumber]->Taxes) > 0 &&
                isset($_SESSION['Items' . $identifier]->LineItems[$OrderLine->LineNumber])) {
            foreach ($_SESSION['Items' . $identifier]->LineItems[$OrderLine->LineNumber]->Taxes AS $Tax) {

                $DisplayTaxRate = $Tax->TaxRate * 100;
                echo '<td><input type=text class="taxRate"  name="TaxRate_' . $OrderLine->LineNumber . '" size=10
                                               maxlength=10 value=' . $DisplayTaxRate . ' readonly="readonly"></td>';

                $i++;
            }
            $_SESSION['Items' . $identifier]->newtotalwithGST +=$LineTotal * (1 + $Tax->TaxRate);
        } else {

            $rate = $_SESSION['Items' . $identifier]->GetNewItemTaxes($OrderLine->TaxCategory, $_SESSION['Items']->TaxGroup
                    , $_SESSION['Items']->DispatchTaxProvince);
            $taxrate = $rate * 100;

            echo '<td><input type=text class="taxRate"  name="TaxRate_' . $OrderLine->LineNumber . '" size=10
                                               maxlength=10 value=' . $taxrate . ' readonly="readonly"></td>';

            $_SESSION['Items' . $identifier]->newtotalwithGST +=$LineTotal * (1 + $rate);
        }
        echo '<td class=number>' . $POStatus . '</td>';
        echo '<tr><td colspan=10>' . _('Narrative') . ':<textarea ' . $editoption . ' name="Narrative_' . $OrderLine->LineNumber . '" cols="100%" rows="1">' . stripslashes(AddCarriageReturns($OrderLine->Narrative)) . '</textarea><br /></td></tr>';
        $_SESSION['Items' . $identifier]->totalwithoutdis = $LineTotalWithoutDis;
    } /* end of loop around items */

    $sqlTran = "SELECT debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.alloc 
				FROM debtortrans
				WHERE debtortrans.type=10
				AND debtortrans.transno='" . $_SESSION['ExistingOrder'] . "'";

    $resultTR = DB_query($sqlTran, $db);

    if (DB_num_rows($resultTR) == 1) {
        $myrowTR = DB_fetch_array($resultTR);
        $freGST = 10;
        $ovFreight = $myrowTR['ovfreight'];
        $tempAP = $myrowTR['alloc'];
    } else {
        $freGST = $_POST['FreGST'];
        $ovFreight = $_POST['Freight'];
        $tempAP = $_POST['tempAP'];
    }

    /* Overwrite new calculated total freight */
    if (isset($_SESSION['TotalFreight']) and $_SESSION['TotalFreight'] != '') {
        $ovFreight = $_SESSION['TotalFreight'];
    }

    $DisplayTotal = number_format(( $_SESSION['Items' . $identifier]->newtotalwithGST) + ($ovFreight * (1 + $freGST / 100)) - $tempAP, $_SESSION['Items' . $identifier]->CurrDecimalPlaces);

    unset($_SESSION['Items' . $identifier]->newtotalwithGST);

    if (in_array(2, $_SESSION['AllowedPageSecurityTokens'])) {
        $ColSpanNumber = 3;
    } else {
        $ColSpanNumber = 1;
    }


    echo '<tr class="EvenTableRows">
                      <td><b>' . _('Comment: ') . '</b><textarea name=Comments cols=40 rows=2>' . $_SESSION['Items' . $identifier]->Comments . '</textarea></td>
                      <td><b><input type=submit name=freightcost value="Estimate Freight"/></b><input class="number" type=text size=20 maxlength=12 name="Freight"  size=16 maxlength=16 value=' . $ovFreight . '></td> 
                      <td class="number"><b>' . _('Fre Tax %') . '</b></td>
                      <td><input class="number" type=text size=12 maxlength=12 name="FreGST" size=16 maxlength=16 value=' . $freGST . '></td>
                      <td class="number"><b>' . _('Already Paid %') . '</b></td>
                      <td class="number">' . $tempAP . '
                      <input type="hidden" name="tempAP" value=' . $tempAP . '></input> </td> 
                      <td class="number" colspan="1" ><b>' . _('Balance') . '</b></td>
		      <td colspan="' . $ColSpanNumber . '" class=number>' . $DisplayTotal . '</td></tr><table><p><p>';


    echo '<table  class=selection>
     <tr><td><b>' . _('Customer Code') . ':<b></td><td><input type=text value="' . $_SESSION['Items' . $identifier]->DebtorNo . '" name=debtorno> <b>Branch Code: <input type=text value=' . $_SESSION['Items' . $identifier]->Branch . ' name=branchno>
     <tr><td><b>' . _('Customer Reference: ') . '</b></td><td><input type=text size=45 maxlength=40 name="customerref" value="' . $_SESSION['Items' . $identifier]->CustRef . '"></td></tr>
     <tr><td><b>' . _('Deliver Address: ') . '</b></td></tr>
     <tr><td>' . _('Deliver To:') . '</td><td><input type=text size=45 maxlength=40 name="DelTo" value="' . $_SESSION['Items' . $identifier]->DeliverTo . '"></td></tr>
     <tr><td>' . _('Street:') . '</td><td><input type=text size=45 maxlength=40 name="DelAdd1" value="' . $_SESSION['Items' . $identifier]->DelAdd1 . '"></td></tr>
     <tr><td>' . _('City/Suburb:') . '</td><td><input type=text size=45 maxlength=40 name="DelAdd2" value="' . $_SESSION['Items' . $identifier]->DelAdd2 . '"></td></tr>
     <tr><td>' . _('State:') . '</td><td><input type=text size=45 maxlength=40 name="DelAdd3" value="' . $_SESSION['Items' . $identifier]->DelAdd3 . '"></td></tr>
     <tr><td>' . _('Postcode:') . '</td><td><input type=text size=45 maxlength=40 name="DelAdd4" value="' . $_SESSION['Items' . $identifier]->DelAdd4 . '"></td></tr>
     <tr><td>' . _('Phone:') . '</td><td><input type=text size=45 maxlength=25 name="ContactPhone" value="' . $_SESSION['Items' . $identifier]->PhoneNo . '"></td></tr></table>';


    echo '<br /><div class="centre"><input type=submit name="Recalculate" Value="' . _('Re-Calculate') . '">
		<input type=submit name="DeliveryDetails" value="' . _('Update Invoice') . '" onclick="return confirm(\'' . _('Before do this action, Please Correct the Freight Amount.') . '\');">
                <input type=button name="InvoiceHistory" value="' . _('Invoice History') . '" onclick="popupwindow(' . $_SESSION['Items' . $identifier]->OrderNo . ');"></div><hr>';
} # end of if lines                                                                                
/* Now show the stock item selection search stuff below */

if ((!isset($_POST['QuickEntry'])
        AND !isset($_POST['SelectAsset']))) {

    echo '<input type="hidden" name="PartSearch" value="' . _('Yes Please') . '">';

    if ($_SESSION['FrequentlyOrderedItems'] > 0) { //show the Frequently Order Items selection where configured to do so
// Select the most recently ordered items for quick select
        $SixMonthsAgo = DateAdd(Date($_SESSION['DefaultDateFormat']), 'm', -6);

        $SQL = "SELECT stockmaster.units, 
						stockmaster.description, 
						stockmaster.stockid, 
						salesorderdetails.stkcode,
						SUM(qtyinvoiced) salesqty 
					FROM `salesorderdetails`INNER JOIN `stockmaster`
					ON  salesorderdetails.stkcode = stockmaster.stockid
					WHERE ActualDispatchDate >= '" . FormatDateForSQL($SixMonthsAgo) . "'
					GROUP BY stkcode
					ORDER BY salesqty DESC
					LIMIT " . $_SESSION['FrequentlyOrderedItems'];

        $result2 = DB_query($SQL, $db);
        echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ';
        echo _('Frequently Ordered Items') . '</p><br />';
        echo '<div class="page_help_text">' . _('Frequently Ordered Items') . _(', shows the most frequently ordered items in the last 6 months.  You can choose from this list, or search further for other items') . '.</div><br />';
        echo '<table class="table1">';
        $TableHeader = '<tr><th>' . _('Code') . '</th>
								<th>' . _('Description') . '</th>
								<th>' . _('Units') . '</th>
								<th>' . _('On Hand') . '</th>
								<th>' . _('On Demand') . '</th>
								<th>' . _('On Order') . '</th>
								<th>' . _('Available') . '</th>
								<th>' . _('Quantity') . '</th></tr>';
        echo $TableHeader;
        $j = 1;
        $k = 0; //row colour counter

        while ($myrow = DB_fetch_array($result2)) {
// This code needs sorting out, but until then :
            $ImageSource = _('No Image');
// Find the quantity in stock at location
            $QOHSQL = "SELECT sum(locstock.quantity) AS qoh,
									stockmaster.decimalplaces
							   FROM locstock INNER JOIN stockmaster
							   ON locstock.stockid=stockmaster.stockid
							   WHERE locstock.stockid='" . $myrow['stockid'] . "' AND
								loccode = '" . $_SESSION['Items' . $identifier]->Location . "'";
            $QOHResult = DB_query($QOHSQL, $db);
            $QOHRow = DB_fetch_array($QOHResult);
            $QOH = $QOHRow['qoh'];

            // Find the quantity on outstanding sales orders
            $sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
							FROM salesorderdetails,
								 salesorders
							WHERE salesorders.orderno = salesorderdetails.orderno 
							AND salesorders.fromstkloc='" . $_SESSION['Items' . $identifier]->Location . "' 
							AND salesorderdetails.completed=0 
							AND salesorders.quotation=0 
							AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";

            $ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items' . $identifier]->Location . ' ' .
                    _('cannot be retrieved because');
            $DemandResult = DB_query($sql, $db, $ErrMsg);

            $DemandRow = DB_fetch_row($DemandResult);
            if ($DemandRow[0] != null) {
                $DemandQty = $DemandRow[0];
            } else {
                $DemandQty = 0;
            }
            // Find the quantity on purchase orders
            $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo
						FROM purchorderdetails INNER JOIN purchorders
						WHERE purchorderdetails.completed=0
						AND purchorders.status<> 'Completed'
						AND purchorders.status<> 'Rejected'
						AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

            $ErrMsg = _('The order details for this product cannot be retrieved because');
            $PurchResult = db_query($sql, $db, $ErrMsg);

            $PurchRow = db_fetch_row($PurchResult);
            if ($PurchRow[0] != null) {
                $PurchQty = $PurchRow[0];
            } else {
                $PurchQty = 0;
            }

            // Find the quantity on works orders
            $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS qwo
						   FROM woitems
						   WHERE stockid='" . $myrow['stockid'] . "'";
            $ErrMsg = _('The order details for this product cannot be retrieved because');
            $WoResult = db_query($sql, $db, $ErrMsg);
            $WoRow = db_fetch_row($WoResult);
            if ($WoRow[0] != null) {
                $WoQty = $WoRow[0];
            } else {
                $WoQty = 0;
            }

            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            $OnOrder = $PurchQty + $WoQty;

            $Available = $QOH - $DemandQty + $OnOrder;

            printf('<td>%s</font></td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td><font size=1><input class="number"  tabindex=' . number_format($j + 7) . ' type="textbox" size=6 name="itm' . $myrow['stockid'] . '" value=0>
						</td>
						</tr>', $myrow['stockid'], $myrow['description'], $myrow['units'], number_format($QOH, $QOHRow['decimalplaces']), number_format($DemandQty, $QOHRow['decimalplaces']), number_format($OnOrder, $QOHRow['decimalplaces']), number_format($Available, $QOHRow['decimalplaces']));
            if ($j == 1) {
                $jsCall = '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.itm' . $myrow['stockid'] . ');}</script>';
            }
            $j++;
#end of page full new headings if
        }
#end of while loop for Frequently Ordered Items
        echo '<td style="text-align:center" colspan=8><input type="hidden" name="order_items" value=1><input tabindex=' . number_format($j + 8) . ' type="submit" value="' . _('Add to Invoice') . '"></td>';
        echo '</table>';
    } //end of if Frequently Ordered Items > 0
    echo '<p><div class="centre"><b><p>' . $msg . '</b></p>';
    echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ';
    echo _('Search for Order Items') . '</p>';
    echo '<div class="page_help_text">' . _('You can search items by category, description or item code') . '.</div><br />';
    echo '<table class="selection"><tr><td><b>' . _('Select a Stock Category') . ': </b><select tabindex=1 name="StockCat">';

    if (!isset($_POST['StockCat'])) {
        echo '<option selected value="All">' . _('All');
        $_POST['StockCat'] = 'All';
    } else {
        echo '<option value="All">' . _('All');
    }
    $SQL = "SELECT categoryid,
						categorydescription
				FROM stockcategory
				WHERE stocktype='F' OR stocktype='D'
				ORDER BY categorydescription";

    $result1 = DB_query($SQL, $db);
    while ($myrow1 = DB_fetch_array($result1)) {
        if ($_POST['StockCat'] == $myrow1['categoryid']) {
            echo '<option selected value=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'] . '</option>';
        } else {
            echo '<option value=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'] . '</option>';
        }
    }

    echo '</select></td>
			<td><b>' . _('Enter partial Description') . ':</b><input tabindex=2 type="Text" name="Keywords" size=20 maxlength=25 value="';

    if (isset($_POST['Keywords'])) {
        echo$_POST['Keywords'];
    }
    echo '"></td>';

    echo '<td align="right"><b>' . _('OR') . ' ' . _('Enter extract of the Stock Code') . ':</b><input tabindex=3 type="Text" name="StockCode" size=15 maxlength=18 value="';
    if (isset($_POST['StockCode'])) {
        echo $_POST['StockCode'];
    }
    echo '"></td></tr>';

    echo '<tr>
			<td style="text-align:center" colspan=6><input tabindex=4 type=submit name="Search" value="' . _('Search Now') . '"></td>';


    if (!isset($_POST['PartSearch'])) {
        echo '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.Keywords);}</script>';
    }
    if (in_array(2, $_SESSION['AllowedPageSecurityTokens'])) { //not a customer entry of own order
        echo '
			</tr></table><br />';
    }

    if (isset($SearchResult)) {
        echo '<br />';
        echo '<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Add to Invoice') . '</div>';
        echo '<br />';
        $j = 1;
        echo '<form action="' . $_SERVER['PHP_SELF'] . '?identifier=' . $identifier . '" method=post name="orderform">';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<table class="table1">';
        echo '<tr><td colspan=><input type="hidden" name="previous" value=' . number_format($Offset - 1) . '><input tabindex=' . number_format($j + 8) . ' type="submit" name="Prev" value="' . _('Prev') . '"></td>';
        echo '<td style="text-align:center" colspan=6><input type="hidden" name="order_items" value=1><input tabindex=' . number_format($j + 9) . ' type="submit" value="' . _('Add to Invoice') . '"></td>';
        echo '<td colspan=><input type="hidden" name="nextlist" value=' . number_format($Offset + 1) . '><input tabindex=' . number_format($j + 10) . ' type="submit" name="Next" value="' . _('Next') . '"></td></tr>';
        $TableHeader = '<tr><th>' . _('Code') . '</th>
					   			<th>' . _('Description') . '</th>
					   			<th>' . _('Units') . '</th>
					   			<th>' . _('On Hand') . '</th>
					   			<th>' . _('On Demand') . '</th>
					   			<th>' . _('On Order') . '</th>
					   			<th>' . _('Available') . '</th>
					   			<th>' . _('Quantity') . '</th></tr>';
        echo $TableHeader;
        $ImageSource = _('No Image');

        $k = 0; //row colour counter

        while ($myrow = DB_fetch_array($SearchResult)) {

            // Find the quantity in stock at location
            $QOHSQL = "SELECT quantity AS qoh,
									stockmaster.decimalplaces
							   FROM locstock INNER JOIN stockmaster
							   ON locstock.stockid = stockmaster.stockid
							   WHERE locstock.stockid='" . $myrow['stockid'] . "' AND
							   loccode = '" . $_SESSION['Items' . $identifier]->Location . "'";
            $QOHResult = DB_query($QOHSQL, $db);
            $QOHRow = DB_fetch_array($QOHResult);
            $QOH = $QOHRow['qoh'];

            // Find the quantity on outstanding sales orders
            $sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
						FROM salesorderdetails INNER JOIN salesorders 
						ON salesorders.orderno = salesorderdetails.orderno 
						 WHERE  salesorders.fromstkloc='" . $_SESSION['Items' . $identifier]->Location . "' 
						 AND salesorderdetails.completed=0 
						 AND salesorders.quotation=0 
						 AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";

            $ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items' . $identifier]->Location . ' ' . _('cannot be retrieved because');
            $DemandResult = DB_query($sql, $db, $ErrMsg);

            $DemandRow = DB_fetch_row($DemandResult);
            if ($DemandRow[0] != null) {
                $DemandQty = $DemandRow[0];
            } else {
                $DemandQty = 0;
            }

            // Find the quantity on purchase orders
            $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo
						 FROM purchorderdetails INNER JOIN purchorders 
						 ON purchorderdetails.orderno=purchorders.orderno
						 WHERE purchorderdetails.completed=0 
						 AND purchorders.status<>'Cancelled'
						 AND purchorders.status<>'Rejected'
						 AND purchorders.status<>'Pending'
						AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

            $ErrMsg = _('The order details for this product cannot be retrieved because');
            $PurchResult = db_query($sql, $db, $ErrMsg);

            $PurchRow = db_fetch_row($PurchResult);
            if ($PurchRow[0] != null) {
                $PurchQty = $PurchRow[0];
            } else {
                $PurchQty = 0;
            }

            // Find the quantity on works orders
            $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
					   FROM woitems
					   WHERE stockid='" . $myrow['stockid'] . "'";
            $ErrMsg = _('The order details for this product cannot be retrieved because');
            $WoResult = db_query($sql, $db, $ErrMsg);

            $WoRow = db_fetch_row($WoResult);
            if ($WoRow[0] != null) {
                $WoQty = $WoRow[0];
            } else {
                $WoQty = 0;
            }

            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k = 1;
            }
            $OnOrder = $PurchQty + $WoQty;
            $Available = $QOH - $DemandQty + $OnOrder;

            printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td><font size=1><input class="number"  tabindex=' . number_format($j + 7) . ' type="textbox" size=6 name="itm' . $myrow['stockid'] . '" value=0>
						</td>
						</tr>', $myrow['stockid'], $myrow['description'], $myrow['units'], number_format($QOH, $QOHRow['decimalplaces']), number_format($DemandQty, $QOHRow['decimalplaces']), number_format($OnOrder, $QOHRow['decimalplaces']), number_format($Available, $QOHRow['decimalplaces']));
            if ($j == 1) {
                $jsCall = '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.itm' . $myrow['stockid'] . ');}</script>';
            }
            $j++;
            #end of page full new headings if
        }
        #end of while loop
        echo '<tr><td><input type="hidden" name="previous" value=' . number_format($Offset - 1) . '><input tabindex=' . number_format($j + 7) . ' type="submit" name="Prev" value="' . _('Prev') . '"></td>';
        echo '<td style="text-align:center" colspan=6><input type="hidden" name="order_items" value=1><input tabindex=' . number_format($j + 8) . ' type="submit" value="' . _('Add to Invoice') . '"></td>';
        echo '<td><input type="hidden" name="nextlist" value=' . number_format($Offset + 1) . '><input tabindex=' . number_format($j + 9) . ' type="submit" name="Next" value="' . _('Next') . '"></td></tr>';
        echo '</table></form>';
        echo $jsCall;
    }#end if SearchResults to show
} /* end of PartSearch options to be displayed */

if ($_SESSION['Items' . $identifier]->ItemsOrdered >= 1) {
    echo '<br /><div class="centre"><input type=submit name="CancelOrder" value="' . _('Cancel Whole Order') . '" onclick="return confirm(\'' . _('Are you sure you wish to cancel this entire order?') . '\');"></div>';
}


echo '</form>';

include('includes/footer.inc');
?>