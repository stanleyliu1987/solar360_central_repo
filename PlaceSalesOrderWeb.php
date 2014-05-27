<?php

/* $Id: SelectCompletedOrder.php 4551 2011-04-16 06:20:56Z daintree $*/

include('includes/session.inc');

$title = _('Search All Sales Orders');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') .
	'" alt="" />' . ' ' . _('Search And Import Sales Orders') . '</p>';

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';



/** Select new sales order from temporary Table **/

          $SQL = "SELECT import_salesorders.orderno,
					import_salesorders.debtorno,
					import_salesorders.branchcode,
					import_salesorders.customerref,
					import_salesorders.orddate,
					import_salesorders.deliverydate,
                                        import_salesorderdetails.completed,
					import_salesorders.deliverto, 
                                        import_csv_salesorders.deliveryname,
                                       REPLACE(import_csv_salesorders.deliverycompany, '''', '') as deliverycompany,
                                        import_csv_salesorders.deliveryaddress,
                                        import_csv_salesorders.deliverysuburb,
                                        import_csv_salesorders.deliverypostcode,
                                        import_csv_salesorders.deliverystate,
                                        import_csv_salesorders.billingname,
                                       REPLACE(import_csv_salesorders.billingcompany, '''', '') as billingcompany,
                                        import_csv_salesorders.billingaddress,
                                        import_csv_salesorders.billingsuburb,
                                        import_csv_salesorders.billingpostcode,
                                        import_csv_salesorders.billingstate,
                                        import_csv_salesorders.billingemail,
                                        import_csv_salesorders.optioncode,
                                        import_csv_salesorders.yourreference
				FROM import_salesorders,
					import_salesorderdetails,
                                        import_csv_salesorders
				WHERE import_salesorders.orderno = import_salesorderdetails.orderno
                                AND import_csv_salesorders.Number=import_salesorders.orderno
				AND import_salesorders.quotation=0
                                AND poplaced=0
				GROUP BY import_salesorders.orderno,
					import_salesorders.customerref,
					import_salesorders.orddate,
					import_salesorders.deliverydate,
					import_salesorders.deliverto
				ORDER BY import_salesorders.orderno";  
   
        
         /** Begin importing new Sales Order Data from Website into ERP **/
        if(isset($_GET['OrderNoForImport']) and is_numeric($_GET['OrderNoForImport'])){
     
            /** 1. Select Customer Code and Branch Code Based on their name **/
        
            $CustomerExistResult=CheckCustomerCodeExist(strtoupper($_GET['customername']),strtoupper($_GET['branchname']),$_GET['BillingEmail'],$db);

            if(empty($CustomerExistResult) or empty($_GET['customername']) or empty($_GET['branchname'])){
               prnMsg( _('No Customer Code or Branch Code by the SQL because, the name spelling does not match between website and erp'));
            }
            else{
              $CustomerBranchCode=explode(',',$CustomerExistResult); 
              $customerCode=$CustomerBranchCode[0];
              $branchCode=$CustomerBranchCode[1];
            
              /** 2. Check Stock Code exist or not in ERP **/
             $StockExist= CheckStockExist($_GET['OrderNoForImport'],$db);
             if($StockExist==false){
                prnMsg( _('Stock Code Does not exist in ERP, please update Stockcode both in Website and ERP'));
             }
             else{
             /** 3. Copy the Importing salesorder to actual salesorder table **/
           
                $SqlUpdateNewSO="Update import_salesorders set debtorno='". $customerCode."',  
                                                        branchcode='".$branchCode."',
                                                        poplaced=1    
                                                    where import_salesorders.orderno='". $_GET['OrderNoForImport']."'"; 
               
                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Customer Code cannot be updated');
	        $DbgMsg = _('The following SQL to update the new Sales Order');
	        $UpdateCodeResult = DB_query($SqlUpdateNewSO,$db,$ErrMsg,$DbgMsg,true);
                
              $SqlInsertNewSO="INSERT INTO salesorders (orderno, 
                              debtorno, 
                              branchcode, 
                              customerref, 
                              buyername, 
                              comments, 
                              orddate, 
                              ordertype,
                              shipvia,
                              deladd1, 
                              deladd2,
                              deladd3, 
                              deladd4, 
                              deladd5,
                              deladd6, 
                              contactphone,
                              contactemail, 
                              deliverto, 
                              deliverblind,
                              freightcost, 
                              fromstkloc, 
                              deliverydate, 
                              confirmeddate, 
                              printedpackingslip, 
                              datepackingslipprinted, 
                              quotation, 
                              quotedate, 
                              poplaced)
                              select * from import_salesorders where import_salesorders.orderno='".$_GET['OrderNoForImport']."'";
               
            $InsertSOResult = DB_query($SqlInsertNewSO,$db);

	    if (DB_error_no($db) !=0) {
		prnMsg( _('New Sales Order Cannot be inserted that because') . ' ' . DB_error_msg($db), 'info');
		echo "<br />$SQL";
	    }
            
                $SqlUpdateNewSOPO="Update salesorders set poplaced=0  
                                   where  salesorders.orderno='". $_GET['OrderNoForImport']."'"; 
               
                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Sales Order PO Placed cannot be updated');
	        $DbgMsg = _('The following SQL to update the PO Status Of Sales Order');
	        $UpdateNewPOStatusResult = DB_query($SqlUpdateNewSOPO,$db,$ErrMsg,$DbgMsg,true);
           
            
            /** 4. Copy the Importing salesorderdetails to actual salesorderdetails table **/
              $SqlInsertNewSODetail="INSERT INTO salesorderdetails (orderlineno,
	                            orderno,
	                            stkcode,
	                            qtyinvoiced,
	                            unitprice,
	                            quantity,
	                            estimate,
	                            discountpercent ,
	                            actualdispatchdate,
	                            completed,
	                            narrative,
	                            itemdue,
	                            poline,
	                            commissionrate,
	                            commissionearned)
                  select orderlineno,
                         orderno,
                         stkcode,
                         qtyinvoiced,
                         unitprice,
                         quantity,
                         estimate,
                         discountpercent,
                         actualdispatchdate,
                         completed,
                         narrative,
                         itemdue,
                         poline,
                         commissionrate,
                         commissionearned from import_salesorderdetails where import_salesorderdetails.orderno='".$_GET['OrderNoForImport']."'";
               
                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Sales Order Details cannot be inserted');
	        $DbgMsg = _('The following SQL to insert the new Sales Order Details');
	        $InsertDetailResult = DB_query($SqlInsertNewSODetail,$db,$ErrMsg,$DbgMsg,true);

            /** 5. Update Sales Order number in systype table **/
                $SqlUpdateNewSONumber="Update systypes set typeno='". $_GET['OrderNoForImport']."'
                                                    where typeid = 30"; 
                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Sales Order Number cannot be updated');
	        $DbgMsg = _('The following SQL to update the new Sales Order Number');
	        $UpdateSONumberResult = DB_query($SqlUpdateNewSONumber,$db,$ErrMsg,$DbgMsg,true);
                
                prnMsg(_('Sales Order') . ' ' . $_GET['OrderNoForImport'] . ' ' . _('From Customer') . ' ' . $_GET['customername'] . ' ' . _('has been created'),'success');
               // echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/PlaceSalesOrderWeb.php?SearchOrders=Search Orders'."' />";
                
                /** 6. Copy the salesorderdetails data into freightcostevaluation table **/
                /** check item has preferred supplier or not **/
                  $SQLSalesOrderItemsList="Select * from salesorderdetails where salesorderdetails.orderno ='". $_GET['OrderNoForImport']."'";
                  $ItemsResult = DB_query($SQLSalesOrderItemsList,$db,'','',true);
                  
                  while ($items=DB_fetch_array($ItemsResult)) {
                  $checksql="Select * from purchdata inner join salesorderdetails on purchdata.stockid=salesorderdetails.stkcode WHERE purchdata.preferred=1 and
                             salesorderdetails.orderno ='". $_GET['OrderNoForImport']."' and salesorderdetails.stkcode='".$items['stkcode']."'";
                  $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('preferred supplier cannot be retrieved');
	          $DbgMsg = _('The following SQL to check item preferred supplier');
	          $CheckPreferResult = DB_query($checksql,$db,$ErrMsg,$DbgMsg,true);
                  
                  /*07042014 by Stan Flows stock measurements data */
                  $measuresql="Select unitlength, unitwidth, unitheight from stockmeasurement where stockid='".$items['stkcode']."' Limit 1";
                  $MeasureResult = DB_query($measuresql,$db,'','',true);
                  $SQLMeasurementList=DB_fetch_array($MeasureResult);                  
                  if(DB_num_rows($CheckPreferResult)>0){
                  $SqlInsertNewSOFreightData="INSERT INTO freightcostevaluation (linenumber, itemcode, custcode, salesorder, quantity, height, width, length, prefsupplier) 
                                              SELECT salesorderdetails.orderlineno, 
                                                salesorderdetails.stkcode,
                                                salesorders.debtorno, 
                                                salesorderdetails.orderno, 
                                                salesorderdetails.quantity,
                                                '". $SQLMeasurementList["unitheight"]."'*salesorderdetails.quantity,
                                                '". $SQLMeasurementList["unitwidth"]."',
                                                '". $SQLMeasurementList["unitlength"]."',
                                                purchdata.supplierno FROM salesorderdetails INNER JOIN salesorders ON salesorders.orderno= salesorderdetails.orderno 
                                                INNER JOIN purchdata ON purchdata.stockid = salesorderdetails.stkcode WHERE purchdata.preferred=1 and
                                                salesorderdetails.orderno ='". $_GET['OrderNoForImport']."' and salesorderdetails.stkcode='".$items['stkcode']."'";
                  }
                  else{
                   $SqlInsertNewSOFreightData="INSERT INTO freightcostevaluation (linenumber, itemcode, custcode, salesorder, quantity, height, width, length, prefsupplier) 
                                               SELECT salesorderdetails.orderlineno, 
                                                salesorderdetails.stkcode,
                                                salesorders.debtorno, 
                                                salesorderdetails.orderno, 
                                                salesorderdetails.quantity,
                                                '". $SQLMeasurementList["unitheight"]."'*salesorderdetails.quantity,
                                                '". $SQLMeasurementList["unitwidth"]."',
                                                '". $SQLMeasurementList["unitlength"]."',
                                                360
                                                FROM salesorderdetails INNER JOIN salesorders ON salesorders.orderno= salesorderdetails.orderno 
                                                WHERE salesorderdetails.orderno ='". $_GET['OrderNoForImport']."' and salesorderdetails.stkcode='".$items['stkcode']."'";    
                  }
               
                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Freight Details cannot be inserted');
	        $DbgMsg = _('The following SQL to insert the new Freight Data Details');
	        $InsertDetailResult = DB_query($SqlInsertNewSOFreightData,$db,$ErrMsg,$DbgMsg,true);
            }
        }
    }
 }
        else{
         /** Transfer CSV importing table into temporary salesorder and salesorderdetails Table **/
          $orderlineno=0;
          $max_import_salesorderNo_Result=DB_query("SELECT MAX(import_salesorders.orderno) FROM import_salesorders",$db); 
          $maximportsalesorderArray=DB_fetch_array($max_import_salesorderNo_Result);
          $maximportsalesorderNo=$maximportsalesorderArray[0]; 
           if($maximportsalesorderNo!=null){
               $CSVImportSQL = "SELECT  import_csv_salesorders.deliveryname,
                                       REPLACE(import_csv_salesorders.deliverycompany, '''', '') as deliverycompany,
                                        import_csv_salesorders.deliveryaddress,
                                        import_csv_salesorders.deliverysuburb,
                                        import_csv_salesorders.deliverypostcode,
                                        import_csv_salesorders.deliverystate,
                                        import_csv_salesorders.billingname,
                                       REPLACE(import_csv_salesorders.billingcompany, '''', '') as billingcompany,
                                        import_csv_salesorders.billingaddress,
                                        import_csv_salesorders.billingsuburb,
                                        import_csv_salesorders.billingpostcode,
                                        import_csv_salesorders.billingstate,
                                        import_csv_salesorders.billingemail,
                                        import_csv_salesorders.optioncode,
                                        import_csv_salesorders.Number,
                                        import_csv_salesorders.comments,
                                        import_csv_salesorders.datepurchased,
                                        import_csv_salesorders.billingphone,
                                        import_csv_salesorders.billingemail,
                                        import_csv_salesorders.yourreference
                                     FROM import_csv_salesorders
				     WHERE import_csv_salesorders.Number > '".$maximportsalesorderNo."'
				     GROUP BY import_csv_salesorders.Number
				     ORDER BY import_csv_salesorders.Number"; 
           }
           else{
               $CSVImportSQL = "SELECT   import_csv_salesorders.deliveryname,
                                       REPLACE(import_csv_salesorders.deliverycompany, '''', '') as deliverycompany,
                                        import_csv_salesorders.deliveryaddress,
                                        import_csv_salesorders.deliverysuburb,
                                        import_csv_salesorders.deliverypostcode,
                                        import_csv_salesorders.deliverystate,
                                        import_csv_salesorders.billingname,
                                       REPLACE(import_csv_salesorders.billingcompany, '''', '') as billingcompany,
                                        import_csv_salesorders.billingaddress,
                                        import_csv_salesorders.billingsuburb,
                                        import_csv_salesorders.billingpostcode,
                                        import_csv_salesorders.billingstate,
                                        import_csv_salesorders.billingemail,
                                        import_csv_salesorders.optioncode,
                                        import_csv_salesorders.Number,
                                        import_csv_salesorders.comments,
                                        import_csv_salesorders.datepurchased,
                                        import_csv_salesorders.billingphone,
                                        import_csv_salesorders.billingemail,
                                        import_csv_salesorders.yourreference
                                        FROM import_csv_salesorders
				     GROUP BY import_csv_salesorders.Number
				     ORDER BY import_csv_salesorders.Number"; 
           }
           
     
          $CSVImportingSalesOrdersResult = DB_query($CSVImportSQL,$db); 
          
         if (DB_error_no($db) !=0) {
		prnMsg( _('No New Sales Order Created because') . ' ' . DB_error_msg($db), 'info');
		echo "<br />$SQL";
	    }
         else if(DB_num_rows($CSVImportingSalesOrdersResult)==0){
               prnMsg( _('No New Sales Order has been created from Website'));
            }
         else{
            while ($CSVSOImport=DB_fetch_array($CSVImportingSalesOrdersResult)) {
                
       /** Insert salesorder into temporary import salesorder table**/
                $customername=$CSVSOImport['billingcompany']==null ? $CSVSOImport['billingname'] : $CSVSOImport['billingcompany'];
                $branchname=$CSVSOImport['billingcompany']==null ? $CSVSOImport['billingname'] : $CSVSOImport['billingcompany'];
                $deliveryname=$CSVSOImport['deliverycompany']==null ? $CSVSOImport['deliveryname'] : $CSVSOImport['deliverycompany'];
                
       /** Check customer exist or not acoording to customername, branchname and branch email**/  
                $ExistCustomerName=CheckCustomerNameExist($customername,$branchname,$CSVSOImport['billingemail'],$db);
               
             if(!empty($ExistCustomerName)){
              $CustomerBranchName=explode(',',$ExistCustomerName); 
              $customername=$CustomerBranchName[0];
              $branchname=$CustomerBranchName[1];
             }
            

                 $SqlInsertNewSO="INSERT INTO import_salesorders (orderno, 
                              debtorno, 
                              branchcode,
                              customerref,
                              comments, 
                              orddate, 
                              ordertype,
                              shipvia,
                              deladd1, 
                              deladd2,
                              deladd3, 
                              deladd4, 
                              contactphone,
                              contactemail, 
                              deliverto, 
                              deliverblind,
                              freightcost, 
                              fromstkloc, 
                              deliverydate, 
                              confirmeddate, 
                              quotation, 
                              quotedate, 
                              poplaced) 
                              VALUES(	'". $CSVSOImport['Number'] . "',
                                        '". strtoupper(str_replace("'", '', trim($customername)))."',
                                        '". strtoupper(str_replace("'", '', trim($branchname)))."',
                                        '". $CSVSOImport['yourreference']."',
                                        '". $CSVSOImport['comments']."',
                                        '". $CSVSOImport['datepurchased']."',     
                                        1,
                                        1,
                                        '". strtoupper(str_replace("'", '', $CSVSOImport['deliveryaddress']))."',
                                        '". strtoupper(str_replace("'", '', $CSVSOImport['deliverysuburb']))."',
                                        '". strtoupper(str_replace("'", '', $CSVSOImport['deliverystate']))."',
                                        '". strtoupper(str_replace("'", '', $CSVSOImport['deliverypostcode']))."',
                                        '". $CSVSOImport['billingphone']."',
                                        '". $CSVSOImport['billingemail']."',
                                        '". strtoupper(str_replace("'", '', $deliveryname))."', 
                                        1,
                                        0,
                                        '001',
                                        '". $CSVSOImport['datepurchased']."',
                                        '". $CSVSOImport['datepurchased']."',
                                        0,
                                        '". $CSVSOImport['datepurchased']."',
                                        0
                                        )";
               
            $InsertSOResult = DB_query($SqlInsertNewSO,$db);

	    if (DB_error_no($db) !=0) {
		prnMsg( _('New Sales Order Cannot be inserted into tempoary Import sales order table that because') . ' ' . DB_error_msg($db), 'info');
		echo "<br />$SQL";
	    }
            
            else{
            /** Insert salesorderdetails into temporary import salesorderdetails table**/
             $CSVImportDetailsSQL = "SELECT   import_csv_salesorders.deliveryname,
                                       REPLACE(import_csv_salesorders.deliverycompany, '''', '') as deliverycompany,
                                        import_csv_salesorders.deliveryaddress,
                                        import_csv_salesorders.deliverysuburb,
                                        import_csv_salesorders.deliverypostcode,
                                        import_csv_salesorders.deliverystate,
                                        import_csv_salesorders.billingname,
                                       REPLACE(import_csv_salesorders.billingcompany, '''', '') as billingcompany,
                                        import_csv_salesorders.billingaddress,
                                        import_csv_salesorders.billingsuburb,
                                        import_csv_salesorders.billingpostcode,
                                        import_csv_salesorders.billingstate,
                                        import_csv_salesorders.billingemail,
                                        import_csv_salesorders.code,
                                        import_csv_salesorders.quantity,
                                        import_csv_salesorders.price,
                                        import_csv_salesorders.discount,
                                        import_csv_salesorders.id,
                                        import_csv_salesorders.optioncode,
                                        import_csv_salesorders.yourreference
                                        FROM import_csv_salesorders
				                       WHERE import_csv_salesorders.Number ='". $CSVSOImport['Number'] . "'
				                       ORDER BY import_csv_salesorders.Number"; 
             $CSVImportingSalesOrderDetailsResult = DB_query($CSVImportDetailsSQL,$db); 
             
             while ($CSVSODetailsImport=DB_fetch_array($CSVImportingSalesOrderDetailsResult)) {
                
       /** Insert salesorder into temporary import salesorder table**/
           
                 $stockoptioncode=(!empty($CSVSODetailsImport['optioncode']))?$CSVSODetailsImport['optioncode']:$CSVSODetailsImport['code'];
                 
//                 $stockSellPriceSQL = "SELECT price FROM prices
//				                      WHERE stockid ='".$stockoptioncode . "'"; 
//                 $StockSellPriceResult = DB_query($stockSellPriceSQL,$db); 
//                 $StockSellPriceList  = DB_fetch_array($StockSellPriceResult);
                 
                 /* 19032014 Retrieve tax rate of particular stock */
                 $TaxrateSQL = "SELECT  taxauthrates.taxrate FROM taxauthrates,stockmaster,taxgrouptaxes
                                WHERE taxauthrates.taxcatid=stockmaster.taxcatid AND stockmaster.stockid='".$stockoptioncode . "'
                                AND taxauthrates.taxauthority=taxgrouptaxes.taxauthid
                                AND taxgrouptaxes.taxgroupid=4
                                AND taxauthrates.dispatchtaxprovince=1 limit 1"; 
                 
                 $TaxrateResult = DB_query($TaxrateSQL,$db); 
                 $TaxRateList  = DB_fetch_array($TaxrateResult);
                 $UnitPriceNoGST=$CSVSODetailsImport['price']/(1+$TaxRateList[0]);
                 /* Finish Exclude GST rate unit price */ 
                 
                 /* Merege Duplicate Records */
                 /* Check Record Exist or not */
                 $sqlCheck="Select * from import_salesorderdetails where stkcode='".$stockoptioncode."' and orderno='".$CSVSOImport['Number']."'";
                 $StockCheckResult = DB_query($sqlCheck,$db); 
                 $ExistDuplicateRecords=DB_num_rows($StockCheckResult);
                 
                 /* if stock item already exist, then just update item qty */
                 if($ExistDuplicateRecords>0){
                     $updateRecord="Update import_salesorderdetails set quantity=quantity+'".$CSVSODetailsImport['quantity']."'";
                     $updateStockResult = DB_query($updateRecord,$db); 
                 }
                 /* else stock item is not existing, then insert a new record into salesorderdetails */
                 else{
                 $SqlInsertNewSODetail="INSERT INTO import_salesorderdetails (orderlineno,
	                            orderno,
	                            stkcode,
	                            unitprice,
	                            quantity,
	                            discountpercent,
	                            poline,
                                    itemdue,
                                    ref_csvorderno
                                    ) 
                                    VALUES(     '". $orderlineno . "',
                                                '". $CSVSOImport['Number']."',
                                                '". $stockoptioncode."',
                                                '". $UnitPriceNoGST."',
                                                '". $CSVSODetailsImport['quantity']."', 
                                                '". $CSVSODetailsImport['discount']."',
                                                0,
                                                '". $CSVSOImport['datepurchased']."',
                                                '". $CSVSODetailsImport['id']."'
                                        )";
               
               $InsertSODetailResult = DB_query($SqlInsertNewSODetail,$db);
                 }
	    if (DB_error_no($db) !=0) {
		prnMsg( _('New Sales Order Detail Cannot be inserted or updated into tempoary Import sales order table that because') . ' ' . DB_error_msg($db), 'info');
		echo "<br />$SQL";
	    }
            
           $orderlineno++;
               }  
            }
         $orderlineno=0;
        }
     }
         unset($orderlineno);
          
/** End Transfer CSV importing table into temporary salesorder and salesorderdetails Table **/   
 }
/** End importing new Sales Order Data from Website into ERP **/
      
        
      /** Begin Updating Billing address of Debtorsmaster **/
        if(isset($_GET['OrderNoForBillAddUpdate']) and is_numeric($_GET['OrderNoForBillAddUpdate'])){
            
            if($_GET['Billcustomername']==null){
              prnMsg( _('Customer Name is empty,so Billing Address Cannot be updated') , 'info');
            }
            else{
             $ExistCustomer=CheckCustomerCodeExist($_GET['Billcustomername'],$_GET['Billbranchname'],$_GET['BillingEmail'],$db);
             if(empty($ExistCustomer)){
              prnMsg( _('Customer Name is not exist,so Billing Address Cannot be updated') , 'info');    
             }
             else{
                $CustomerBranchCode=explode(',',$ExistCustomer);
                $SqlUpdateBillingAddress="Update debtorsmaster set address1='". strtoupper($_GET['BillAddress1'])."',  
                                                        address2='".strtoupper($_GET['BillAddress2'])."',
                                                        address3='".strtoupper($_GET['BillAddress3'])."',
                                                        address4='".strtoupper($_GET['BillAddress4'])."'
                                                    where debtorsmaster.debtorno='".$CustomerBranchCode[0]."'"; 
                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Billing Address cannot be updated');
	        $DbgMsg = _('The following SQL to update the billing address');
	        $UpdateBillAddressResult = DB_query($SqlUpdateBillingAddress,$db,$ErrMsg,$DbgMsg,true);  
                echo '<p>';
                prnMsg(_('Customer') . ' ' . $CustomerBranchCode[0] .' '. _('billing address has been updated'),'success');
             }
           }
        }
        
      /** Begin Updating Delivery address of Custbranch **/
        if(isset($_GET['OrderNoForDelAddUpdate']) and is_numeric($_GET['OrderNoForDelAddUpdate'])){
            
            if($_GET['Delcustomername']==null){
              prnMsg( _('Customer Name is empty,so Delivery Address Cannot be updated') , 'info');
            }
            else{
             $ExistCustomer=CheckCustomerCodeExist($_GET['Delcustomername'],$_GET['Delbranchname'],$_GET['BillingEmail'],$db);
             if(empty($ExistCustomer)){
              prnMsg( _('Customer Name is not exist,so Delivery Address Cannot be updated') , 'info');    
             }
             else{ 
                $CustomerBranchCode=explode(',',$ExistCustomer); 
                $SqlUpdateDeliveryAddress="Update custbranch set braddress1='". strtoupper($_GET['DelAddress1'])."',  
                                                        braddress2='".strtoupper($_GET['DelAddress2'])."',
                                                        braddress3='".strtoupper($_GET['DelAddress3'])."',
                                                        braddress4='".strtoupper($_GET['DelAddress4'])."'
                                                    where custbranch.branchcode='".$CustomerBranchCode[1]."' and
                                                          custbranch.debtorno='".$CustomerBranchCode[0]."'"; 
                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Delivery Address cannot be updated');
	        $DbgMsg = _('The following SQL to update the delivery address');
	        $UpdateDeliveryAddressResult = DB_query($SqlUpdateDeliveryAddress,$db,$ErrMsg,$DbgMsg,true);  
                echo '<p>';
                prnMsg(_('Customer') . ' ' . $CustomerBranchCode[0] .' '. _('delivery address has been updated'),'success');
             }
           } 
        }
        
        
        /** Cancel Importing Sales Order **/
        if(isset($_GET['CancelSO']) and is_numeric($_GET['CancelSO']) ){
            
            $SqlUpdateSOStatus="Update import_salesorders set poplaced=2 
                                                    where orderno='".$_GET['CancelSO']."'"; 
                $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Importing Sales Order cannot be Cancelled');
	        $DbgMsg = _('The following SQL to update the Importing Sales Order');
	        $UpdateImportSOStatusResult = DB_query($SqlUpdateSOStatus,$db,$ErrMsg,$DbgMsg,true);  
                echo '<p>';
                prnMsg(_('Sales Order') . ' ' . $_GET['CancelSO'] .' '. _(' has been Cancelled'),'success');
            
        }
        
        
	$SalesOrdersResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		prnMsg( _('No orders were returned by the SQL because') . ' ' . DB_error_msg($db), 'info');
		echo "<br />$SQL";
	}


if (!isset($_POST['OrdersAfterDate']) OR $_POST['OrdersAfterDate'] == '' OR ! Is_Date($_POST['OrdersAfterDate'])){
	$_POST['OrdersAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
}


If (isset($SalesOrdersResult)) {

/*show a table of the orders returned by the SQL */

	echo '<br /><table cellpadding=2 colspan=6 width=90% class=selection>';

	$tableheader = '<tr><th>' . _('Order') . ' #</th>
						<th>' . _('Customer') . '</th>
						<th>' . _('Branch') . '</th>
						<th>' . _('Cust Ref.') . ' #</th>
						<th>' . _('Order Date') . '</th>
						<th>' . _('Req Del Date') . '</th>
						<th>' . _('Delivery To') . '</th>
                                                <th>' . _('Bill To') . '</th>
						<th>' . _('Order Total Exc. GST') . '</th>
                                                <th>' . _('Check Customer') . '</th>
                                                <th>' . _('Entered') . '</th>
                                                <th>' . _('Update Bill Address') . '</th>
                                                <th>' . _('Update Delivery Address') . '</th> 
                                                <th>' . _('Cancel') . '</th>  </tr>';

	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($SalesOrdersResult)) {


		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		$ViewPage = $rootpath . '/WebsiteOrderDetails.php?OrderNumber=' . $myrow['orderno'];
		$FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
		$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
        
              
       /** Order Total Value **/
                
             $SQLOrderTotal = "SELECT  SUM(import_salesorderdetails.unitprice*import_salesorderdetails.quantity*(1-import_salesorderdetails.discountpercent)) AS ordervalue
                    FROM import_salesorderdetails
                    WHERE  import_salesorderdetails.orderno='".$myrow['orderno']."'";
             
             $SQLOrderTotalResult = DB_query($SQLOrderTotal,$db);
             
           
             if (DB_error_no($db) !=0) {
		prnMsg( _('Sales Order Total Cannot be extracted') . ' ' . DB_error_msg($db), 'info');
		echo "<br />$SQL";
	    }
             $SQLOrderTotalResultList=DB_fetch_array($SQLOrderTotalResult);
        
        $FormatedOrderValue = number_format($SQLOrderTotalResultList['ordervalue'],2);
        
         /** End Order Total Value **/
        
        $ParameterCustomer='\''.strtoupper($myrow['debtorno']).'\'';
        $ParameterBranch='\''.strtoupper($myrow['branchcode']).'\'';
        $ParameterEmail='\''.$myrow['billingemail'].'\'';
        
        $ImportPath= $rootpath . '/PlaceSalesOrderWeb.php?OrderNoForImport=' . $myrow['orderno'].'&customername='.strtoupper($myrow['debtorno'])
        .'&branchname='.strtoupper($myrow['branchcode']).'&BillingEmail='.$myrow['billingemail'];
        
        $UpdateBillAddressPath= $rootpath . '/PlaceSalesOrderWeb.php?OrderNoForBillAddUpdate=' . $myrow['orderno'].'&Billcustomername='.strtoupper($myrow['debtorno'])
        .'&Billbranchname='.strtoupper($myrow['branchcode']).'&BillAddress1='.strtoupper($myrow['billingaddress']).'&BillAddress2='.strtoupper($myrow['billingsuburb'])
        .'&BillAddress3='.strtoupper($myrow['billingstate']).'&BillAddress4='.strtoupper($myrow['billingpostcode']).'&BillingEmail='.$myrow['billingemail'];
          
        $UpdateDeliveryAddressPath= $rootpath . '/PlaceSalesOrderWeb.php?OrderNoForDelAddUpdate=' . $myrow['orderno'].'&Delcustomername='.strtoupper($myrow['debtorno'])
        .'&Delbranchname='.strtoupper($myrow['branchcode']).'&DelAddress1='.strtoupper($myrow['deliveryaddress']).'&DelAddress2='.strtoupper($myrow['deliverysuburb'])
        .'&DelAddress3='.strtoupper($myrow['deliverystate']).'&DelAddress4='.strtoupper($myrow['deliverypostcode']).'&BillingEmail='.$myrow['billingemail'];
       
        $CheckCustomer= '<input type=button name="CustomerCheck" value="' . _('Check Customer') . '" onclick="checkCustomerwindow('.$ParameterCustomer.','.$ParameterBranch.','.$ParameterEmail.');">';
        
        $CancelSOPath= $rootpath . '/PlaceSalesOrderWeb.php?CancelSO=' . $myrow['orderno'];

        $ImportNewOrder = '<a href="'.$ImportPath.'">Import</a>'; 
        
        $ExistCustomerTag=CheckCustomerCodeExist(strtoupper($myrow['debtorno']),strtoupper($myrow['branchcode']),$myrow['billingemail'],$db);
        if(!empty($ExistCustomerTag)){
        $UpdateBillAddress= '<a href="'.$UpdateBillAddressPath.'">Update</a>'; 
        $UpdateDelAddress= '<a href="'.$UpdateDeliveryAddressPath.'">Update</a>'; 
        }
        $CancelImportOrder= '<a href="'.$CancelSOPath.'">Cancel</a>'; 
        
        printf('<td><a href="%s">%s</a></td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class=number>%s</td>
                        <td>%s</td>
                        <td>%s</td>
		        <td>%s</td>
                        <td>%s</td>
		        <td>%s</td>
                        <td>%s</td>
			</tr>',
			$ViewPage,
			$myrow['orderno'],
			strtoupper($myrow['debtorno']),
			strtoupper($myrow['branchcode']),
			$myrow['customerref'],
			$FormatedOrderDate,
			$FormatedDelDate,
			strtoupper($myrow['deliveryaddress']).' '.strtoupper($myrow['deliverysuburb']).' '.strtoupper($myrow['deliverystate']).' '.strtoupper($myrow['deliverypostcode']),
                        strtoupper($myrow['billingaddress']).' '.strtoupper($myrow['billingsuburb']).' '.strtoupper($myrow['billingstate']).' '.strtoupper($myrow['billingpostcode']),
			$FormatedOrderValue,
                        $CheckCustomer,
                        $ImportNewOrder,
                        $UpdateBillAddress,
                        $UpdateDelAddress,
                        $CancelImportOrder);
        
        unset($UpdateBillAddress);
        unset($UpdateDelAddress);
        unset($myrow['ordervalue']);

//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}

echo '</form>';
include('includes/footer.inc');


/** Convert Website SO code into ERP SO code **/
function ConvertSalesOrderCode($SalesOrderWebCode,$db){
    
      $ConvertSOSQL = "SELECT stockid FROM stockmaster WHERE longdescription='". trim($SalesOrderWebCode)."'"; 
      $ConvertSOSQLResult = DB_query($ConvertSOSQL,$db);

      if (DB_error_no($db) !=0) {
		prnMsg( _('The Stock Code Cannot be found that because') . ' ' . DB_error_msg($db), 'info');
		echo "<br />$SQL";
	    }
      else if(DB_num_rows($ConvertSOSQLResult)==0){
        
               prnMsg( _('The Stock Code Cannot be found in ERP'));
            }
      else{     
      $ConvertSOArray=DB_fetch_array($ConvertSOSQLResult);
      }
      return $ConvertSOArray['stockid'];
}

function CheckCustomerCodeExist($Customername,$Branchname, $BillingEmail, $db){

$CustomerMatchName = strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($Customername)));

$BranchMatchName =strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($Branchname)));

$EmailMatchName = strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($BillingEmail)));


//insert wildcard characters in spaces
$SearchCustString = '%' . str_replace(' ', '%', str_replace("  "," ",$CustomerMatchName)) . '%';
$SearchBranString = '%' . str_replace(' ', '%', str_replace("  "," ",$BranchMatchName)) . '%';
$SearchEmailString = '%' . str_replace(' ', '%', str_replace("  "," ",$BillingEmail)) . '%';

$CustomerSQL = "SELECT debtorsmaster.debtorno, custbranch.branchcode FROM custbranch
						LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
						WHERE (debtorsmaster.name " . LIKE . " '" . $SearchCustString . "' and
                                                      custbranch.brname " . LIKE . " '" . $SearchBranString . "') or
                                                      custbranch.email ".LIKE."'".$SearchEmailString."'";

		$ErrMsg = _('The Customer cannot be retrieved because');
		$CustomerResult = db_query($CustomerSQL,$db,$ErrMsg); 
                $CustomerDebtornoList=DB_fetch_array($CustomerResult);            
             
                if(DB_num_rows($CustomerResult)==0){
                    return 0;
                }
                
                else{
                    return $CustomerDebtornoList['debtorno'].','.$CustomerDebtornoList['branchcode'];
                }
}

function CheckCustomerNameExist($Customername,$Branchname,$BillingEmail,$db){

$CustomerMatchName = strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($Customername)));

$BranchMatchName =strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($Branchname)));

$EmailMatchName = strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($BillingEmail)));

//insert wildcard characters in spaces
$SearchCustString = '%' . str_replace(' ', '%', str_replace("  "," ",$CustomerMatchName)) . '%';
$SearchBranString = '%' . str_replace(' ', '%', str_replace("  "," ",$BranchMatchName)) . '%';
$SearchEmailString = '%' . str_replace(' ', '%', str_replace("  "," ",$EmailMatchName)) . '%';

$CustomerSQL = "SELECT debtorsmaster.name, custbranch.brname FROM custbranch
						LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
						WHERE debtorsmaster.name " . LIKE . " '" . $SearchCustString . "' or
                                                      custbranch.brname " . LIKE . " '" . $SearchBranString . "' or 
                                                      custbranch.email ".LIKE."'".$SearchEmailString."' Limit 1";  

		$ErrMsg = _('The Customer cannot be retrieved because');
		$CustomerNameResult = db_query($CustomerSQL,$db,$ErrMsg); 
                $CustomerDebtornoNameList=DB_fetch_array($CustomerNameResult);            
             
                if(DB_num_rows($CustomerNameResult)==0){
                    return 0;
                }
                else{
                    return $CustomerDebtornoNameList['name'].','.$CustomerDebtornoNameList['brname'];
                }
}

function CheckStockExist($orderNumber,$db){
        $StockExist=true;
	$StockCodeFromWebsiteSQL = "SELECT stkcode	
			FROM import_salesorderdetails
			WHERE orderno ='" . $orderNumber . "'";
        $ErrMsg = _('The StockCode cannot be retrieved from Website because');
	$StockCodeFromWebsiteResult = db_query($StockCodeFromWebsiteSQL,$db,$ErrMsg); 
      
        while ($stockCodeRow=DB_fetch_array($StockCodeFromWebsiteResult)) {

        $StockCodeFromERPSQL = "SELECT stockid	
			FROM stockmaster
			WHERE stockid ='" . $stockCodeRow['stkcode'] . "'";
        $ErrMsg = _('The StockCode cannot be retrieved from ERP because');
	$StockCodeFromERPResult = db_query($StockCodeFromERPSQL,$db,$ErrMsg);
     
                if(DB_num_rows($StockCodeFromERPResult)==0){
             
                    $StockExist=false;
                    break;
                }
        }
        
        return $StockExist;       
 

}
?>