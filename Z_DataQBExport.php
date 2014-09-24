<?php

/* $Id: Z_DataExport.php 4543 2011-04-09 06:12:05Z daintree $*/


include('includes/session.inc');
include('includes/DefineSuppTransClass.php');
include ('includes/SQL_CommonFunctions.inc');

$_SESSION['DefaultDateFormat']='d/m/Y';
   
function stripcomma($str) { //because we're using comma as a delimiter
    $str = trim($str);
    $str = str_replace('"', '""', $str);
    $str = str_replace("\r", "", $str);
    $str = str_replace("\n", '\n', $str);
    $str = str_replace(",", ' ', $str);
    if($str == "" )
        return $str;
    else
        return $str;
}

function NULLToZero( &$Field ) {
    if( is_null($Field) )
        return '0';
    else
        return $Field;
}

function NULLToPrice( &$Field ) {
    if( is_null($Field) )
        return '-1';
    else
        return $Field;
}

function dumpCSVFreightDsicountValue($LastDumpInvoice){
      $CSVFreDisc .=  stripcomma($LastDumpInvoice[0]). ',' . 
                          stripcomma($LastDumpInvoice[1]). ',' .
                          stripcomma($LastDumpInvoice[2]). ',' .
                          stripcomma($LastDumpInvoice[3]). ',' .
                          stripcomma($LastDumpInvoice[4]). ',' .
                          stripcomma($LastDumpInvoice[5]). ',' .
                          stripcomma($LastDumpInvoice[6]). ',' .
                          stripcomma($LastDumpInvoice[7]). ',' .
                          stripcomma($LastDumpInvoice[8]). ',' .
                          stripcomma($LastDumpInvoice[9]). ',' .
                          stripcomma($LastDumpInvoice[10]). ',' .
                          stripcomma($LastDumpInvoice[11]). ',' .
                          stripcomma($LastDumpInvoice[12]). ',' .
                          stripcomma('Freight'). ',' .
                          stripcomma(1). ',' .
                          stripcomma('This is Freight Item'). ',' .
                          stripcomma($LastDumpInvoice[13]). ',' .
                          stripcomma($LastDumpInvoice[15]). ',' .
                          stripcomma($LastDumpInvoice[16]). "\n";
                  
                     if($LastDumpInvoice[14]>0)  {
      $CSVFreDisc .=  stripcomma($LastDumpInvoice[0]). ',' . 
                          stripcomma($LastDumpInvoice[1]). ',' .
                          stripcomma($LastDumpInvoice[2]). ',' .
                          stripcomma($LastDumpInvoice[3]). ',' .
                          stripcomma($LastDumpInvoice[4]). ',' .
                          stripcomma($LastDumpInvoice[5]). ',' .
                          stripcomma($LastDumpInvoice[6]). ',' .
                          stripcomma($LastDumpInvoice[7]). ',' .
                          stripcomma($LastDumpInvoice[8]). ',' .
                          stripcomma($LastDumpInvoice[9]). ',' .
                          stripcomma($LastDumpInvoice[10]). ',' .
                          stripcomma($LastDumpInvoice[11]). ',' .
                          stripcomma($LastDumpInvoice[12]). ',' .
                          stripcomma('Discount'). ',' .
                          stripcomma(''). ',' .
                          stripcomma('This is Discount Item'). ',' .
                          stripcomma(-$LastDumpInvoice[14]). ',' .
                          stripcomma($LastDumpInvoice[15]). ',' .
                          stripcomma($LastDumpInvoice[16]). "\n";  
                     }
                     
                     return $CSVFreDisc;
}


// EXPORT FOR PRICE LIST
if ( isset($_POST['pricelist']) ) {

		$SQL = "SELECT sales_type FROM salestypes WHERE typeabbrev='" . $_POST['SalesType'] . "'";
		$SalesTypeResult = DB_query($SQL,$db);
		$SalesTypeRow = DB_fetch_row($SalesTypeResult);
		$SalesTypeName = $SalesTypeRow[0];
                
if($_POST['InventoryType']=='InventoryPart'){
		$SQL = "SELECT prices.typeabbrev,
				prices.stockid,
				stockmaster.description,
				prices.currabrev,
				prices.price,
				stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost
					as standardcost,
				stockmaster.categoryid,
				stockcategory.categorydescription,
                                stockmaster.longdescription,
				stockmaster.barcode,
				stockmaster.units,
				stockmaster.mbflag,
				stockmaster.taxcatid,
				stockmaster.discontinued,
                                stockmaster.materialcost,
                                taxcategories.taxcatname
			FROM    prices,
				stockmaster,
				stockcategory,
                                taxcategories  
			WHERE stockmaster.stockid=prices.stockid
			AND stockmaster.categoryid=stockcategory.categoryid
                        AND stockmaster.categoryid <> 'S-CC'
                        AND stockmaster.categoryid <> 'S-Oth'
                        AND stockmaster.categoryid <> 'S-Kit'
                        AND prices.typeabbrev='" . $_POST['SalesType'] . "'
			AND ( (prices.debtorno='') OR (prices.debtorno IS NULL))
                        AND taxcategories.taxcatid=stockmaster.taxcatid
			ORDER BY prices.currabrev,
				stockmaster.categoryid,
				stockmaster.stockid";
}
else{
              $SQL = "SELECT prices.typeabbrev,
				prices.stockid,
				stockmaster.description,
				prices.currabrev,
				prices.price,
				stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost
					as standardcost,
				stockmaster.categoryid,
				stockcategory.categorydescription,
                                stockmaster.longdescription,
				stockmaster.barcode,
				stockmaster.units,
				stockmaster.mbflag,
				stockmaster.taxcatid,
				stockmaster.discontinued,
                                stockmaster.materialcost,
                                taxcategories.taxcatname
			FROM    prices,
				stockmaster,
				stockcategory,
                                taxcategories  
			WHERE stockmaster.stockid=prices.stockid
			AND stockmaster.categoryid=stockcategory.categoryid
			AND ((stockmaster.categoryid = 'S-CC') OR (stockmaster.categoryid = 'S-Oth'))
                        AND prices.typeabbrev='" . $_POST['SalesType'] . "'
			AND ( (prices.debtorno='') OR (prices.debtorno IS NULL))
                        AND taxcategories.taxcatid=stockmaster.taxcatid
			ORDER BY prices.currabrev,
				stockmaster.categoryid,
				stockmaster.stockid";
}
	$PricesResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Price List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Price List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	

	$CSVContent = stripcomma('Item Number') . ',' .
                      stripcomma('Item Name') . ',' .
                      stripcomma('Asset Acct') . ',' .
                      stripcomma('Income Acct') . ',' .
                      stripcomma('COGS Acct') . ',' .      
                      stripcomma('Description') . ',' .
                      stripcomma('Use Desc On Sale') . ',' .
                      
                      stripcomma('Primary Supplier') . ',' .
                      stripcomma('Supplier Item Number') . ',' .
                      stripcomma('Tax Code When Bought') . ',' .
                      stripcomma('Selling Price') . ',' .
                      stripcomma('Sell Unit Measure') . ',' .      
                      stripcomma('Tax Code When Sold') . ',' .
                      stripcomma('Standard Cost') . "\n";

	While ($PriceList = DB_fetch_array($PricesResult,$db)){
		$Qty = 0;
		$sqlQty = "SELECT newqoh
			FROM stockmoves
			WHERE stockid = '".$PriceList['stockid']."'
			AND loccode = '".$_POST['Location']."'
			ORDER BY stkmoveno DESC LIMIT 1";
		$resultQty = DB_query($sqlQty, $db, $ErrMsg);
		if ( $resultQty ) {
			if( DB_num_rows($resultQty) > 0 ) {
				$Row = DB_fetch_row($resultQty);
				$Qty = $Row[0];
			}
			DB_free_result($resultQty);
		}
                
     //Item Active or Inactive
                
		$DisplayUnitPrice = $PriceList['price'];
                if($PriceList['discontinued']==1){
                 $inactive="Y";   
                }else{
                 $inactive="N";   
                }
                
                
     //Choose Supplier for particular Product
       $sqlSupplier = "SELECT  suppliers.suppname,
                          purchdata.suppliers_partno,
                          purchdata.price,
                          purchdata.supplierdescription
			  FROM suppliers, 
                               purchdata,
                               stockmaster
			WHERE stockmaster.stockid= purchdata.stockid
                        AND purchdata.supplierno=suppliers.supplierid
                        AND purchdata.preferred=1
                        AND stockmaster.stockid='" . $PriceList['stockid'] . "'";
       
       $resultSup = DB_query($sqlSupplier, $db, $ErrMsg);
       unset($Suppliername);
       unset($Supplierpartno);
       unset($PurchaseDescription);
       if ( $resultSup ) {
			if( DB_num_rows($resultSup) > 0 ) {
				$SupRow = DB_fetch_row($resultSup);
				$Suppliername = $SupRow[0];
                                $Supplierpartno=$SupRow[1];
                                $SupplierPrice=$SupRow[2];
                                $PurchaseDescription=$SupRow[3];
			}
			DB_free_result($resultSup);
                     
		}
             
       
    //Category type
              
              if(trim($PriceList['categoryid'])=="S-Acc"){
                    
                  $salesaccountName="Sales:Accessories";
                  $costaccountName="Cost of Sales:Purchases:Accessories";
                  $AccsetaccountName="Merchandise Inventory";
                }
                elseif(trim($PriceList['categoryid'])=="S-Inv"){
             
                  $salesaccountName="Sales:Inverters";
                  $costaccountName="Cost of Sales:Purchases:Inverters";
                  $AccsetaccountName="Merchandise Inventory";

                }
                elseif(trim($PriceList['categoryid'])=="S-Kit"){
               
                  $salesaccountName="Sales:Kits";
                  $costaccountName="Cost of Sales:Purchases:Kits";
                  $AccsetaccountName="Merchandise Inventory";
                }
                elseif(trim($PriceList['categoryid'])=="S-Mou"){
                    
                  $salesaccountName="Sales:Mounting Systems";
                  $costaccountName="Cost of Sales:Purchases:Mounting Systems";
                  $AccsetaccountName="Merchandise Inventory";
                }
                elseif(trim($PriceList['categoryid'])=="S-Pan"){
                    
                  $salesaccountName="Sales:Panels";
                  $costaccountName="Cost of Sales:Purchases:Panels";
                  $AccsetaccountName="Merchandise Inventory";
                }
                elseif(trim($PriceList['categoryid'])=="S-CC"){
                    
                  $salesaccountName="C Card Surcharge Collected";
                  $costaccountName="Misc. Income";
                  $AccsetaccountName='';
                }
                elseif(trim($PriceList['categoryid'])=="S-Oth"){
                    
                  $salesaccountName="Freight Collected";
                  $costaccountName="COGS:Freight Paid";
                  $AccsetaccountName='';
                }
                else{
                  $salesaccountName="Sales:Accessories";
                  $costaccountName="Cost of Sales:Purchases:Accessories";
                  $AccsetaccountName="Merchandise Inventory";
                }
                
                $sqlGLSalesAccount="select accountcode from chartmaster where accountname='".$salesaccountName."'";
 
                $resultSalGLAccode = DB_query($sqlGLSalesAccount, $db, $ErrMsg);
                
               if ($resultSalGLAccode) {
			if( DB_num_rows($resultSalGLAccode) > 0 ) {
				$Row = DB_fetch_row($resultSalGLAccode);
				$GLSalesAccountCode = $Row[0];     
			}
			DB_free_result($resultSalGLAccode);
		}
                
               $sqlGLCostAccount="select accountcode from chartmaster where accountname='".$costaccountName."'";
               
               $resultCosGLAccode = DB_query($sqlGLCostAccount, $db, $ErrMsg);
                
              if ($resultCosGLAccode) {
			if( DB_num_rows($resultCosGLAccode) > 0 ) {
				$Row = DB_fetch_row($resultCosGLAccode);
				$GLCostsAccountCode = $Row[0];     
			}
			DB_free_result($resultCosGLAccode);
		}
                
               $GLAssetAccountCode='';
               
                $sqlGLAssetAccount="select accountcode from chartmaster where accountname='".$AccsetaccountName."'";
               
               $resultAssetGLAccode = DB_query($sqlGLAssetAccount, $db, $ErrMsg);
               
              if ($resultAssetGLAccode) {
			if( DB_num_rows($resultAssetGLAccode) > 0 ) {
				$Row = DB_fetch_row($resultAssetGLAccode);
				$GLAssetAccountCode = $Row[0];     
			}
     
			DB_free_result($resultAssetGLAccode);
		}
              
                   
            
                if(trim($PriceList['taxcatname'])=="Taxable supply" or trim($PriceList['taxcatname'])=="Freight" ){
                    
                    $Taxcategory="GST";
                }
                elseif (trim($PriceList['taxcatname'])=="Exempt" or trim($PriceList['taxcatname'])=="CC Exempt"){
                    $Taxcategory="FRE";
                }
                
//                $GLAssetAccountCode="Inventory Asset";
//                $GLSalesAccountCode="Sales Income";
//                $GLCostsAccountCode="Cost of Goods Sold";

		$CSVContent .= (stripcomma($PriceList['stockid']) . ',' .
                        stripcomma(str_replace(',', '" "', $PriceList['description'])) . ',' .
                        stripcomma($AccsetaccountName) . ',' .
                        stripcomma($salesaccountName) . ',' .
                        stripcomma($costaccountName) . ',' .
                        stripcomma($PurchaseDescription) . ',' .
                        stripcomma($PriceList['description']) . ',' .
                        stripcomma($Suppliername) . ',' .
                        stripcomma($Supplierpartno) . ',' .
                        stripcomma($Taxcategory) . ',' .
                        stripcomma($DisplayUnitPrice) . ',' .',' .
                        stripcomma($Taxcategory) . ',' .
                        stripcomma($SupplierPrice) . "\n"
                    
			);
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=ProductList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;

}
elseif ( isset($_POST['custlist']) ) {
    
        $SQL= "SELECT DISTINCT debtorsmaster.debtorno,
                               debtorsmaster.name,
                               debtorsmaster.address1,
                               debtorsmaster.address2,
                               debtorsmaster.address3,
                               debtorsmaster.address4,
                               custbranch.braddress1,
			       custbranch.braddress2,
			       custbranch.braddress3,
			       custbranch.braddress4,
			       custbranch.phoneno,
			       custbranch.faxno,
			       custbranch.email,
                               custbranch.contactname
                               FROM debtorsmaster 
                               left join custcontacts on custcontacts.debtorno=debtorsmaster.debtorno
                               left join custbranch on debtorsmaster.debtorno=custbranch.debtorno 
                               GROUP BY debtorsmaster.debtorno ORDER BY debtorsmaster.debtorno";

        $CustResult = DB_query($SQL,$db,'','',false,false);
	


	if (DB_error_no($db) !=0) {
		$title = _('Customer List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Customer List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
       
	
	$CSVContent = stripcomma('Co Last Name') . ',' .
        stripcomma('First Name') . ',' .
        stripcomma('Card ID') . ',' .
 
	stripcomma('Bill Addr1') . ',' .
        stripcomma('Bill Addr2') . ',' .
	stripcomma('Bill City') . ',' .
	stripcomma('Bill State') . ',' .
	stripcomma('Bill Postcode') . ',' .
        stripcomma('Bill Country') . ',' .
	stripcomma('Phone') . ',' .
                
        stripcomma('Ship Addr1') . ',' .
        stripcomma('Ship Addr2') . ',' .
        stripcomma('Ship City') . ',' .
        stripcomma('Ship State') . ',' .
        stripcomma('Ship Postcode') . ',' .
        stripcomma('Ship Country') . "\n";

 	  	   
      		 					   		  								
	While ($CustList = DB_fetch_array($CustResult,$db)){

             $CSVContent .= stripcomma(substr(trim($CustList['name']),0,40)). ','. ','.
                            stripcomma(trim($CustList['debtorno'])).','.
                            stripcomma($CustList['name']) . ',' .
                            stripcomma($CustList['address1']) . ',' .
			    stripcomma(substr($CustList['address2'],0,20)) . ',' .
			    stripcomma($CustList['address3']) . ',' .
			    stripcomma($CustList['address4']) . ',' .','.   
                            stripcomma(substr($CustList['phoneno'], 0, 4).' '.substr($CustList['phoneno'], 4)) . ',' .
                            stripcomma($CustList['name']) . ',' .
                            stripcomma($CustList['braddress1']). ','.
                            stripcomma(substr($CustList['braddress2'],0,20)). ','.
                            stripcomma($CustList['braddress3']). ','.
                            stripcomma($CustList['braddress4']). ','."\n";
          
               }
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=CustList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
}

elseif ( isset($_POST['supplierlist']) ) {
	$SQL = "SELECT DISTINCT suppliers.supplierid,
                                suppliers.suppname,
                                suppliers.address1,
                                suppliers.address2,
                                suppliers.address3,
                                suppliers.address4,
                                suppliercontacts.contact,
                                suppliercontacts.tel,
                                suppliercontacts.email,
                                suppliercontacts.fax
                                FROM suppliers left join suppliercontacts
                                on suppliers.supplierid=suppliercontacts.supplierid
                                group by suppliers.supplierid";
        

	$SuppResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Supp List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Supplier List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

 
	$CSVContent = stripcomma('Co Last Name') . ',' .
        stripcomma('Card ID') . ',' .
	stripcomma('Addr 1 - Line 1') . ',' .
        stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('Contact Name') . "\n";
										           	           	           	           				
	While ($SuppList = DB_fetch_array($SuppResult,$db)){
    
		$CSVContent .= 
                        stripcomma(substr($SuppList['suppname'],0,40)). ','.
                        stripcomma($SuppList['supplierid']).','.
			stripcomma($SuppList['address1']) . ',' .','. ','. ','.
			stripcomma(substr($SuppList['address2'],0,20)) . ',' .
			stripcomma($SuppList['address3']) . ',' .
			stripcomma($SuppList['address4']) . ',' .','.
			stripcomma($SuppList['tel']) . ',' .
                        stripcomma($SuppList['email']).','.
                        stripcomma($SuppList['contact'])."\n"; 
	}
   
  
              
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SuppList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
}


elseif (isset($_POST['Invoicelist'])){ 
    
   if(isset($_POST['InvoiceCreditType']) and $_POST['InvoiceCreditType']!=''){
        
      $InvoiceCreditType=$_POST['InvoiceCreditType'];
    }
    
   if($InvoiceCreditType=='invoice'){
   $filename= 'InvoiceList.csv';    
   $SQL = "SELECT debtortrans.id,
                                        debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
                                        debtortrans.alloc,
					debtortrans.invtext,
                                        debtortrans.mod_flag,
                                        debtortrans.sales_ref_num,
                                        debtortrans.order_,
					debtortrans.consignment,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					debtorsmaster.invaddrbranch,
					debtorsmaster.taxref,
					paymentterms.terms,
					salesorders.deliverto,
					salesorders.deladd1,
					salesorders.deladd2,
					salesorders.deladd3,
					salesorders.deladd4,
					salesorders.deladd5,
					salesorders.deladd6,
					salesorders.customerref,
                                        salesorders.comments,
					salesorders.orderno,
					salesorders.orddate,
					locations.locationname,
					shippers.shippername,
					custbranch.brname,
					custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					custbranch.phoneno,
                                        custbranch.contactname,
					salesman.salesmanname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        stockmaster.stockid,
                                        stockmaster.longdescription,
                                        stockmaster.description,
                                        stockmaster.categoryid,
                                        -stockmoves.qty as quantity,
                                        (stockmoves.price*debtortrans.rate) AS fxprice,
				        stockmoves.discountpercent,
                                        taxauthrates.taxrate,
                                        taxcategories.taxcatname,
                                        (stockmoves.price*stockmoves.discountpercent*(-stockmoves.qty)) AS discountvalue
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesorders,
					shippers,
					salesman,
					locations,
					paymentterms,
                                        stockmoves,
				        stockmaster,
                                        taxauthrates,
                                        taxcategories,
                                        taxgrouptaxes, 
				        taxauthorities 
				WHERE debtortrans.order_ = salesorders.orderno
				AND debtortrans.type=10
				AND debtortrans.shipvia=shippers.shipper_id
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtorsmaster.paymentterms=paymentterms.termsindicator
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode
				AND salesorders.fromstkloc=locations.loccode
                                AND debtortrans.transno=stockmoves.transno
                                AND stockmoves.stockid = stockmaster.stockid
			        AND stockmoves.type=10
                                AND stockmoves.show_on_inv_crds=1
                                AND taxcategories.taxcatid=stockmaster.taxcatid
                                AND taxauthrates.taxcatid=stockmaster.taxcatid 
                                
                                AND taxauthrates.taxauthority=taxgrouptaxes.taxauthid
                                AND taxauthrates.taxauthority=taxauthorities.taxid
                                AND taxgrouptaxes.taxgroupid=custbranch.taxgroupid
			        AND taxauthrates.dispatchtaxprovince=locations.taxprovinceid
                              
                                AND debtortrans.trandate between '".FormatDateForSQL($_POST['StartInvDate'])."' and '".FormatDateForSQL($_POST['EndInvDate'])."'
                                order by salesorders.orderno    ";

	$InvoiceResult = DB_query($SQL,$db,'','',false,false);
     
	if (DB_error_no($db) !=0) {
		$title = _('Invoice List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Invoice List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	
      
 $CSVContent = 
        stripcomma('Co Last Name') . ',' .
	stripcomma('Billing Street') . ',' .
	stripcomma('Biling City') . ',' .
        stripcomma('Billing State') . ',' .
        stripcomma('Billing Postcode') . ',' .
	stripcomma('Phone') . ',' .
         
	stripcomma('Shipping Street') . ',' .
	stripcomma('Shipping City') . ',' .
        stripcomma('Shipping State') . ',' .
        stripcomma('Shipping Postcode') . ',' .
   
	stripcomma('Invoice # 1') . ',' .
	stripcomma('Date') . ',' .
        stripcomma('Customer PO') . ',' .
       
        stripcomma('Item Number') . ',' .
        stripcomma('Quantity') . ',' .
        stripcomma('Description') . ',' .
        stripcomma('Price') . ',' .
        stripcomma('Comment') . ',' .
        stripcomma('Tax Code')."\n";
 
 /**
  * Use array_multisort to Sort the InvoiceResult with orderno ascending
  */
    foreach ($InvoiceResult as $key => $row) {
            $InvNoSequence[$key]  = $row['orderno']; 
        }
        
     array_multisort($InvNoSequence, SORT_ASC, $InvoiceResult);
     DB_data_seek($InvoiceResult,0);
     
     While ($InvoiceList = DB_fetch_array($InvoiceResult,$db)){
                 
    /**
     * Create GAP between each separate inovice
     */    

        if($LastDumpInvoice[17]!=$InvoiceList['orderno'] and $i!=0){
          $CSVContent .=dumpCSVFreightDsicountValue($LastDumpInvoice); 
          unset($OvDiscount);
        }
            
    /**
     * Dynamic Mapping Tax code
     */    
                if(trim($InvoiceList['taxcatname'])=="Taxable supply" or trim($InvoiceList['taxcatname'])=="Freight" ){
                    
                    $Taxcategory="GST";
                }
                elseif (trim($InvoiceList['taxcatname'])=="Exempt" or trim($InvoiceList['taxcatname'])=="CC Exempt"){
                    $Taxcategory="FRE";
                }  
                
               //Freight Tax Code
                $sqlFTC="Select taxauthrates.taxrate from taxauthrates inner join taxcategories on 
                         taxcategories.taxcatid=taxauthrates.taxcatid where taxcategories.taxcatname='Freight' limit 1";
                $freightGST = DB_query($sqlFTC,$db,'','',false,false);
                
     
                
               While ($freightRate = DB_fetch_array($freightGST,$db)){
                if($freightRate['taxrate']>0)
                   $FreightTaxCode="GST";
                else
                   $FreightTaxCode="FRE"; 
               
            }
            
    /**
     * Map Bank account between ERP and MYOB
     */        
    $SQLBankName="select bankaccounts.bankaccountname 
                                          from banktrans, debtortrans, bankaccounts, custallocns where 
                                          debtortrans.transno=banktrans.transno and
                                          debtortrans.type=12 and
                                          banktrans.type=12 and
                                          banktrans.bankact=bankaccounts.accountcode and
                                          custallocns.transid_allocto='".$InvoiceList['id']."' and
                                          debtortrans.id= custallocns.transid_allocfrom ORDER BY custallocns.id DESC limit 1 ";
       
    $ErrMsg = _('No bank account were returned by the SQL because');

    $SQLBankNameList = DB_query($SQLBankName,$db,$ErrMsg);
    
    /**
     * Update Stock Location
     */   

     //   if(trim($InvoiceList['categoryid'])=="S-CC" or trim($InvoiceList['categoryid'])=="S-Oth"){
                $stockLocation=" ";
//       }
//         else{
//               $stockLocation="Location1";  
//         }

    if ($SQLBankNameList) {

			if( DB_num_rows($SQLBankNameList) > 0 ) {
				$Row = DB_fetch_row($SQLBankNameList);
				$BankAccountName = $Row[0];     
			}
			DB_free_result($SQLBankNameList);
	}
        
if($InvoiceList['invaddrbranch']==0){
    
   $BillAddressStreet=$InvoiceList['address1'];
   $BillAddressCity=$InvoiceList['address2'];
   $BillAddressState=$InvoiceList['address3'];
   $BillAddressPostcode=$InvoiceList['address4'];
   $BillAddressPhone= $InvoiceList['phoneno'];

}


else{
    
   $BillAddressStreet=$InvoiceList['brpostaddr1'];
   $BillAddressCity=$InvoiceList['brpostaddr2'];
   $BillAddressState=$InvoiceList['brpostaddr3'];
   $BillAddressPostcode=$InvoiceList['brpostaddr4'];
   $BillAddressPhone= $InvoiceList['phoneno']; 

}
            
    /**
     * Output the invoice
     */    
        $salesmanName = explode(" ", $InvoiceList['salesmanname']);
        
		$CSVContent .= stripcomma($InvoiceList['name']). ',' .
			stripcomma($BillAddressStreet) . ',' .
			stripcomma($BillAddressCity).','.
                        stripcomma($BillAddressState) . ',' .
                        stripcomma($BillAddressPostcode) . ',' .       
			stripcomma($BillAddressPhone) . ',' .
                        stripcomma($InvoiceList['deladd1']) . ',' .
			stripcomma($InvoiceList['deladd2']).','.
                        stripcomma($InvoiceList['deladd3']) . ',' .
                        stripcomma($InvoiceList['deladd4']) . ',' .    
                        stripcomma('W'.$InvoiceList['order_']).','.
                        stripcomma(ConvertSQLDate($InvoiceList['trandate'])) .','. 
                        stripcomma(substr($InvoiceList['customerref'],0,25)).','.
                        stripcomma($InvoiceList['stockid']).','.
                        stripcomma($InvoiceList['quantity']).','.
                        stripcomma($InvoiceList['description']).','.
                        stripcomma($InvoiceList['fxprice']).','.
                        stripcomma($InvoiceList['comments']).','.
                        stripcomma($Taxcategory)."\n";
                
                $OvDiscount+=$InvoiceList['discountvalue'];
                $LastDumpInvoice=array($InvoiceList['name'],
                                       $BillAddressStreet,
                                       $BillAddressCity,
                                       $BillAddressState,
                                       $BillAddressPostcode,
                                       $BillAddressPhone,
                                       $InvoiceList['deladd1'],
                                       $InvoiceList['deladd2'],
                                       $InvoiceList['deladd3'],
                                       $InvoiceList['deladd4'],
                                       'W'.$InvoiceList['order_'],
                                       ConvertSQLDate($InvoiceList['trandate']),
                                       substr($InvoiceList['customerref'],0,25),
                                       $InvoiceList['ovfreight'],
                                       $OvDiscount,
                                       $InvoiceList['comments'],
                                       $Taxcategory,
                                       $InvoiceList['orderno']);
                        
 
                $i++;
	}
        $CSVContent .=dumpCSVFreightDsicountValue($LastDumpInvoice); 
        unset($OvDiscount);
        }
        
        else{
           $filename= 'CreditNoteList.csv';   
           $SQL = "SELECT debtortrans.id,
                                        debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
                                        debtortrans.alloc,
					debtortrans.invtext,
                                        debtortrans.mod_flag,
                                        debtortrans.sales_ref_num,
                                        debtortrans.order_,
					debtortrans.consignment,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					debtorsmaster.invaddrbranch,
					debtorsmaster.taxref,
					paymentterms.terms,
					locations.locationname,
					custbranch.brname,
					custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					custbranch.phoneno,
                                        custbranch.contactname,
					salesman.salesmanname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        stockmaster.stockid,
                                        stockmaster.longdescription,
                                        stockmaster.description,
                                        stockmaster.categoryid,
                                        stockmoves.qty AS quantity,
                                        (stockmoves.price*debtortrans.rate) AS fxprice,
				        stockmoves.discountpercent,
                                        taxauthrates.taxrate,
                                        taxcategories.taxcatname,
                                        (stockmoves.price*stockmoves.discountpercent*(-stockmoves.qty)) AS discountvalue
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesman,
					locations,
					paymentterms,
                                        stockmoves,
				        stockmaster,
                                        taxauthrates,
                                        taxcategories,
                                        taxgrouptaxes, 
				        taxauthorities 
				WHERE  debtortrans.type=11
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtorsmaster.paymentterms=paymentterms.termsindicator
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode
				AND locations.loccode=custbranch.defaultlocation
                                AND debtortrans.transno=stockmoves.transno
                                AND stockmoves.stockid = stockmaster.stockid
			        AND stockmoves.type=11
                                AND stockmoves.show_on_inv_crds=1
                                AND taxcategories.taxcatid=stockmaster.taxcatid
                                AND taxauthrates.taxcatid=stockmaster.taxcatid 
                                AND taxauthrates.taxauthority=taxgrouptaxes.taxauthid
                                AND taxauthrates.taxauthority=taxauthorities.taxid
                                AND taxgrouptaxes.taxgroupid=custbranch.taxgroupid
			        AND taxauthrates.dispatchtaxprovince=locations.taxprovinceid
			            AND debtortrans.trandate BETWEEN '".FormatDateForSQL($_POST['StartInvDate'])."' AND '".FormatDateForSQL($_POST['EndInvDate'])."'
			            ORDER BY debtortrans.transno";

	$CreditnoteResult = DB_query($SQL,$db,'','',false,false);
     
	if (DB_error_no($db) !=0) {
		$title = _('Credit Note List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Credit Note List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	
      
 $CSVContent = 
        stripcomma('Co Last Name') . ',' .
        stripcomma('Ref No') .','.
	stripcomma('Billing Street') . ',' .
	stripcomma('Biling City') . ',' .
        stripcomma('Billing State') . ',' .
        stripcomma('Billing Postcode') . ',' .
	stripcomma('Phone') . ',' .
         
	stripcomma('Shipping Street') . ',' .
	stripcomma('Shipping City') . ',' .
        stripcomma('Shipping State') . ',' .
        stripcomma('Shipping Postcode') . ',' .
	stripcomma('Txn Date') . ',' .
      
        stripcomma('Item Number') . ',' .
        stripcomma('Quantity') . ',' .
        stripcomma('Description') . ',' .
        stripcomma('Price') . ',' .
        stripcomma('Comment') . ',' .
        stripcomma('Tax Code')."\n";
 
 /**
  * Use array_multisort to Sort the InvoiceResult with orderno ascending
  */
    foreach ($CreditnoteResult as $key => $row) {
            $CreditNoSequence[$key]  = $row['transno']; 
        }
        
     array_multisort($CreditNoSequence, SORT_ASC, $CreditnoteResult);
DB_data_seek($CreditnoteResult,0);
     
     While ($CreditnoteList = DB_fetch_array($CreditnoteResult,$db)){
                 
  
    /**
     * Dynamic Mapping Tax code
     */    
                if(trim($CreditnoteList['taxcatname'])=="Taxable supply" or trim($CreditnoteList['taxcatname'])=="Freight" ){
                    
                    $Taxcategory="GST";
                }
                elseif (trim($CreditnoteList['taxcatname'])=="Exempt" or trim($CreditnoteList['taxcatname'])=="CC Exempt"){
                    $Taxcategory="FRE";
                }  
                
               //Freight Tax Code
                $sqlFTC="Select taxauthrates.taxrate from taxauthrates inner join taxcategories on 
                         taxcategories.taxcatid=taxauthrates.taxcatid where taxcategories.taxcatname='Freight' limit 1";
                $freightGST = DB_query($sqlFTC,$db,'','',false,false);
                
     
                
               While ($freightRate = DB_fetch_array($freightGST,$db)){
                if($freightRate['taxrate']>0)
                   $FreightTaxCode="GST";
                else
                   $FreightTaxCode="FRE"; 
               
            }

        
if($InvoiceList['invaddrbranch']==0){
    
   $BillAddressStreet=$CreditnoteList['address1'];
   $BillAddressCity=$CreditnoteList['address2'];
   $BillAddressState=$CreditnoteList['address3'];
   $BillAddressPostcode=$CreditnoteList['address4'];
   $BillAddressPhone= $CreditnoteList['phoneno'];

}


else{
    
   $BillAddressStreet=$CreditnoteList['brpostaddr1'];
   $BillAddressCity=$CreditnoteList['brpostaddr2'];
   $BillAddressState=$CreditnoteList['brpostaddr3'];
   $BillAddressPostcode=$CreditnoteList['brpostaddr4'];
   $BillAddressPhone= $CreditnoteList['phoneno']; 

}
            
    /**
     * Output the invoice
     */    
        $salesmanName = explode(" ", $CreditnoteList['salesmanname']);
        
		$CSVContent .= stripcomma($CreditnoteList['name']). ',' .
                        stripcomma($CreditnoteList['transno']). ',' .
			stripcomma($BillAddressStreet) . ',' .
			stripcomma($BillAddressCity).','.
                        stripcomma($BillAddressState) . ',' .
                        stripcomma($BillAddressPostcode) . ',' .       
			stripcomma($BillAddressPhone) . ',' .
                        stripcomma($BillAddressStreet) . ',' .
			stripcomma($BillAddressCity).','.
                        stripcomma($BillAddressState) . ',' .
                        stripcomma($BillAddressPostcode) . ',' .   
                        stripcomma(ConvertSQLDate($CreditnoteList['trandate'])) .','. 
                        stripcomma($CreditnoteList['stockid']).','.
                        stripcomma($CreditnoteList['quantity']).','.
                        stripcomma($CreditnoteList['description']).','.
                        stripcomma($CreditnoteList['fxprice']).','.
                        stripcomma($CreditnoteList['comments']).','.
                        stripcomma($Taxcategory)."\n";
                
                $OvDiscount+=$CreditnoteList['discountvalue'];
//                $LastDumpInvoice=array($CreditnoteList['name'],
//                                       $BillAddressStreet,
//                                       $BillAddressCity,
//                                       $BillAddressState,
//                                       $BillAddressPostcode,
//                                       $BillAddressPhone,
//                                       $BillAddressStreet,
//                                       $BillAddressCity,
//                                       $BillAddressState,
//                                       $BillAddressPostcode,
//                                       ConvertSQLDate($CreditnoteList['trandate']),
//                                       $CreditnoteList['ovfreight'],
//                                       $OvDiscount,
//                                       $CreditnoteList['comments'],
//                                       $Taxcategory,
//                                       $CreditnoteList['orderno']);
//                        
 
                $i++;
	}
       // $CSVContent .=dumpCSVFreightDsicountValue($LastDumpInvoice); 
       // unset($OvDiscount);   
        }
        
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename='.$filename);
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit; 
}

elseif (isset($_POST['POlist'])){
    
  /* Retrieve the PO export type */  
      if(isset($_POST['POCreditType']) and $_POST['POCreditType']!=''){
        
         $POCreditType=$_POST['POCreditType'];
    }
    
  /* Retrieve local tax province */
    	$LocalTaxProvinceResult = DB_query("SELECT taxprovinceid
								FROM locations
								WHERE loccode = '" . $_SESSION['UserStockLocation'] . "'", $db);

	if(DB_num_rows($LocalTaxProvinceResult)==0){
		prnMsg(_('The tax province associated with your user account has not been set up in this database. Tax calculations are based on the tax group of the supplier and the tax province of the user entering the invoice. The system administrator should redefine your account with a valid default stocking location and this location should refer to a valid tax province'),'error');
		include('includes/footer.inc');
		exit;
	}

	$LocalTaxProvinceRow = DB_fetch_row($LocalTaxProvinceResult);
	$LocalTaxProvince = $LocalTaxProvinceRow[0];
 
 if($POCreditType == 'POList' ){
  /* Export PO with related invoice */
  $sql = "SELECT    purchorders.supplierno, purchorders.orderno,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
                                        suppliers.telephone,
					purchorders.comments,
					purchorders.orddate,
					purchorders.rate,
					purchorders.dateprinted,
					purchorders.deladd1,
					purchorders.deladd2,
					purchorders.deladd3,
					purchorders.deladd4,
					purchorders.deladd5,
					purchorders.deladd6,
					purchorders.allowprint,
					purchorders.requisitionno,
                                        purchorders.tel,
					purchorders.initiator,
					purchorders.paymentterms,
                                        purchorders.comments,
                                        purchorders.contact,
					suppliers.currcode,
					purchorders.status,
					purchorders.stat_comment,
                                        purchorders.ref_number,
                                        shippers.shippername,
                                        stockmaster.longdescription,
                                        purchorderdetails.quantityord,
                                        purchorderdetails.quantityrecd,
                                        purchorderdetails.itemdescription,
                                        purchorderdetails.unitprice,
                                        purchorderdetails.qtyinvoiced,
                                        purchorderdetails.actprice,
                                        purchorderdetails.supinvref,
                                        taxauthrates.taxrate,
                                        taxcategories.taxcatname,
                                        debtortrans.trandate,
                                        purchorders.ref_salesorder,
                                        stockmaster.stockid,
                                        stockmaster.categoryid,
                                        round((stockmaster.materialcost-purchorderdetails.unitprice)/stockmaster.materialcost) as discountpercentage
				        FROM purchorders INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
                                        INNER JOIN shippers ON shippers.shipper_id=purchorders.deliveryby
                                        INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
                                        INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
                                        INNER JOIN taxauthrates ON taxauthrates.taxcatid=stockmaster.taxcatid
                                        INNER JOIN taxcategories ON taxcategories.taxcatid=stockmaster.taxcatid
                                        

                                        INNER JOIN taxgrouptaxes ON taxauthrates.taxauthority=taxgrouptaxes.taxauthid
				        INNER JOIN taxauthorities ON taxauthrates.taxauthority=taxauthorities.taxid
		
                            

                                        INNER JOIN debtortrans ON debtortrans.order_= purchorders.ref_salesorder
                                        where  debtortrans.trandate between '".FormatDateForSQL($_POST['StartPODate'])."'
                                            and '".FormatDateForSQL($_POST['EndPODate'])."' AND ref_salesorder <>0 
                                            AND taxgrouptaxes.taxgroupid=suppliers.taxgroupid
			                    AND taxauthrates.dispatchtaxprovince='" .$LocalTaxProvince . "'
			                    AND taxauthrates.taxcatid = '" . $_SESSION['DefaultTaxCategory'] . "' ";

	$POResult = DB_query($sql,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Po List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Po List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$CSVContent =   
        stripcomma('Co Full Name') . ',' .
	stripcomma('Street') . ',' .
	stripcomma('Suburb') . ',' .
	stripcomma('City') . ',' .
        stripcomma('Postcode') . ',' .
	stripcomma('Phone') . ',' .
        stripcomma('Purchase#') . ',' .
	stripcomma('Date') . ',' .
        stripcomma('Supplier Invoice #') . ',' . 
        
        stripcomma('Item Number') . ',' .
        stripcomma('Quantity') . ',' .
        stripcomma('Description') . ',' .
        stripcomma('Price') . ',' .
        stripcomma('Inc-Tax Price') . ',' .
        stripcomma('Total') . ',' .
        stripcomma('Inc-Tax Total') . ',' .
        stripcomma('Comment') . ',' .
                
   
        stripcomma('Tax Code') . ',' .
        stripcomma('GST Amount') . ',' .
        stripcomma('Received').','.
        stripcomma('Billed')."\n";
   
        
 /**
  * Use array_multisort to Sort the POResult with orderno ascending
  */
   foreach ($POResult as $key => $row) {
            $PONoSequence[$key]  = $row['orderno']; 
        }
        
     array_multisort($PONoSequence, SORT_ASC, $POResult);
     DB_data_seek($POResult,0);
	While ($POList = DB_fetch_array($POResult,$db)){
           
            /**
             * Cancel or Rejetected PO 
             */
          if($POList['status']== "Cancelled" or $POList['status']=="Rejected"){
              $POList['quantityord']=0;
              $POList['unitprice']=0;
              $POList['quantityrecd']=0;      
          }
           /**
            * Tax Code
            */
               if(trim($POList['taxcatname'])=="Taxable supply" or trim($POList['taxcatname'])=="Freight" ){
                    
                    $Taxcategory="NCG";
                }
                elseif (trim($POList['taxcatname'])=="Exempt" or trim($POList['taxcatname'])=="CC Exempt"){
                    $Taxcategory="FRE";
                }
                
     
		$CSVContent .= stripcomma($POList['suppname']). ',' .
			stripcomma($POList['address1']) . ',' .
			stripcomma($POList['address2']).','.
                        stripcomma($POList['address3']).','.
                        stripcomma($POList['address4']) . ',' .
			stripcomma($POList['telephone']) . ',' .
			stripcomma('W'.$POList['ref_salesorder'].'-'.$POList['orderno']) . ',' .
                        stripcomma(ConvertSQLDate($POList['trandate'])) .','. 
                        stripcomma($POList['supinvref']) .','.
                        
                        stripcomma($POList['stockid']).','.
                        stripcomma($POList['quantityord']).','.
                        stripcomma($POList['itemdescription']).','.
                        stripcomma($POList['unitprice']).','.
                        stripcomma($POList['unitprice']*(1+$POList['taxrate'])).','.

                        stripcomma($POList['quantityord']*$POList['unitprice']).','.
                        stripcomma($POList['quantityord']*$POList['unitprice']*(1+$POList['taxrate'])).','.
                        stripcomma($POList['comments']).','.

                        stripcomma($Taxcategory) .','.
                        stripcomma($POList['quantityord']*$POList['unitprice']*($POList['taxrate'])).','.

                        stripcomma($POList['quantityrecd']) .','.
                        stripcomma($POList['quantityrecd']) . "\n";
                
                $PORecordId=$POList['orderno'];
                $i++;
               // $amountPaid=0;
	}
        
        /* Export PO without related invoice */
         $sql = "SELECT    purchorders.supplierno, purchorders.orderno,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
                                        suppliers.telephone,
					purchorders.comments,
					purchorders.orddate as trandate,
					purchorders.rate,
					purchorders.dateprinted,
					purchorders.deladd1,
					purchorders.deladd2,
					purchorders.deladd3,
					purchorders.deladd4,
					purchorders.deladd5,
					purchorders.deladd6,
					purchorders.allowprint,
					purchorders.requisitionno,
                                        purchorders.tel,
					purchorders.initiator,
					purchorders.paymentterms,
                                        purchorders.comments,
                                        purchorders.contact,
					suppliers.currcode,
					purchorders.status,
					purchorders.stat_comment,
                                        purchorders.ref_number,
                                        shippers.shippername,
                                        stockmaster.longdescription,
                                        purchorderdetails.quantityord,
                                        purchorderdetails.quantityrecd,
                                        purchorderdetails.itemdescription,
                                        purchorderdetails.unitprice,
                                        purchorderdetails.qtyinvoiced,
                                        purchorderdetails.actprice,
                                        purchorderdetails.supinvref,
                                        taxauthrates.taxrate,
                                        taxcategories.taxcatname,
                                        purchorders.ref_salesorder,
                                        stockmaster.stockid,
                                        stockmaster.categoryid,
                                        round((stockmaster.materialcost-purchorderdetails.unitprice)/stockmaster.materialcost) as discountpercentage
				FROM purchorders INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
                                        INNER JOIN shippers ON shippers.shipper_id=purchorders.deliveryby
                                        INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
                                        INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
                                        INNER JOIN taxauthrates ON taxauthrates.taxcatid=stockmaster.taxcatid
                                        INNER JOIN taxcategories ON taxcategories.taxcatid=stockmaster.taxcatid
                                        
                                        INNER JOIN taxgrouptaxes ON taxauthrates.taxauthority=taxgrouptaxes.taxauthid
				        INNER JOIN taxauthorities ON taxauthrates.taxauthority=taxauthorities.taxid
		
                                        where  purchorders.orddate between '".FormatDateForSQL($_POST['StartPODate'])."'
                                            and '".FormatDateForSQL($_POST['EndPODate'])."' AND ref_salesorder =0
                                            AND taxgrouptaxes.taxgroupid=suppliers.taxgroupid
			                    AND taxauthrates.dispatchtaxprovince='" .$LocalTaxProvince . "'
			                    AND taxauthrates.taxcatid = '" . $_SESSION['DefaultTaxCategory'] . "' ";
           
      	$POResult = DB_query($sql,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Po List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Po List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
   
 /**
  * Use array_multisort to Sort the POResult with orderno ascending
  */
   foreach ($POResult as $key => $row) {
            $PONoSequence[$key]  = $row['orderno']; 
        }
        
     array_multisort($PONoSequence, SORT_ASC, $POResult);
     DB_data_seek($POResult,0);
	While ($POList = DB_fetch_array($POResult,$db)){
           
            /**
             * Cancel or Rejetected PO 
             */
          if($POList['status']== "Cancelled" or $POList['status']=="Rejected"){
              $POList['quantityord']=0;
              $POList['unitprice']=0;
              $POList['quantityrecd']=0;      
          }
           /**
            * Tax Code
            */
               if(trim($POList['taxcatname'])=="Taxable supply" or trim($POList['taxcatname'])=="Freight" ){
                    
                    $Taxcategory="NCG";
                }
                elseif (trim($POList['taxcatname'])=="Exempt" or trim($POList['taxcatname'])=="CC Exempt"){
                    $Taxcategory="FRE";
                }
                
     
		$CSVContent .= stripcomma($POList['suppname']). ',' .
			stripcomma($POList['address1']) . ',' .
			stripcomma($POList['address2']).','.
                        stripcomma($POList['address3']).','.
                        stripcomma($POList['address4']) . ',' .
			stripcomma($POList['telephone']) . ',' .
			stripcomma('W'.'-'.$POList['orderno']) . ',' .
                        stripcomma(ConvertSQLDate($POList['trandate'])) .','. 
                        stripcomma($POList['supinvref']) .','.
                        
                        stripcomma($POList['stockid']).','.
                        stripcomma($POList['quantityord']).','.
                        stripcomma($POList['itemdescription']).','.
                        stripcomma($POList['unitprice']).','.
                        stripcomma($POList['unitprice']*(1+$POList['taxrate'])).','.

                        stripcomma($POList['quantityord']*$POList['unitprice']).','.
                        stripcomma($POList['quantityord']*$POList['unitprice']*(1+$POList['taxrate'])).','.
                        stripcomma($POList['comments']).','.

                        stripcomma($Taxcategory) .','.
                        stripcomma($POList['quantityord']*$POList['unitprice']*($POList['taxrate'])).','.

                        stripcomma($POList['quantityrecd']) .','.
                        stripcomma($POList['quantityrecd']) . "\n";
                
                $PORecordId=$POList['orderno'];
                $i++;
               // $amountPaid=0;
	}
      
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=POList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit; 
        
 }
 
 elseif($POCreditType == 'DebitNoteList'){
      $sql = "SELECT    purchorders.supplierno, purchorders.orderno,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
                                        suppliers.telephone,
					purchorders.comments,
					purchorders.orddate,
					purchorders.rate,
					purchorders.dateprinted,
					purchorders.deladd1,
					purchorders.deladd2,
					purchorders.deladd3,
					purchorders.deladd4,
					purchorders.deladd5,
					purchorders.deladd6,
					purchorders.allowprint,
					purchorders.requisitionno,
                                        purchorders.tel,
					purchorders.initiator,
					purchorders.paymentterms,
                                        purchorders.comments,
                                        purchorders.contact,
					suppliers.currcode,
					purchorders.status,
					purchorders.stat_comment,
                                        purchorders.ref_number,
                                        shippers.shippername,
                                        stockmaster.longdescription,
                                        purchorderdetails.quantityord,
                                        purchorderdetails.quantityrecd,
                                        purchorderdetails.itemdescription,
                                        purchorderdetails.unitprice,
                                        purchorderdetails.qtyinvoiced,
                                        purchorderdetails.actprice,
                                        purchorderdetails.supinvref,
                                        taxauthrates.taxrate,
                                        taxcategories.taxcatname,
                                        purchorders.ref_salesorder,
                                        stockmaster.stockid,
                                        stockmaster.categoryid,
                                        supptrans.transno,
                                        supptrans.trandate, 
                                        supptrans.suppreference,
                                        podebitnotedetails.debititemqty,
                                        round((stockmaster.materialcost-purchorderdetails.unitprice)/stockmaster.materialcost) as discountpercentage
				        FROM purchorders INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
                                        INNER JOIN shippers ON shippers.shipper_id=purchorders.deliveryby
                                        INNER JOIN purchorderdetails ON purchorders.orderno=purchorderdetails.orderno
                                        INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
                                        INNER JOIN taxauthrates ON taxauthrates.taxcatid=stockmaster.taxcatid
                                        INNER JOIN taxcategories ON taxcategories.taxcatid=stockmaster.taxcatid
                                        INNER JOIN taxgrouptaxes ON taxauthrates.taxauthority=taxgrouptaxes.taxauthid
				        INNER JOIN taxauthorities ON taxauthrates.taxauthority=taxauthorities.taxid
                                        INNER JOIN podebitnotedetails ON podebitnotedetails.podetailsref=purchorderdetails.podetailitem
		                        INNER JOIN supptrans ON supptrans.transno=podebitnotedetails.debitnoteref
                                        where  supptrans.trandate between '".FormatDateForSQL($_POST['StartPODate'])."'
                                            and '".FormatDateForSQL($_POST['EndPODate'])."' 
                                            AND supptrans.type=21    
                                            AND taxgrouptaxes.taxgroupid=suppliers.taxgroupid
			                    AND taxauthrates.dispatchtaxprovince='" .$LocalTaxProvince . "'
			                    AND taxauthrates.taxcatid = '" . $_SESSION['DefaultTaxCategory'] . "'
                                            ORDER BY supptrans.transno";
      
      $POResult = DB_query($sql,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Po Debit Note List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Po Debit Note List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$CSVContent =   
        stripcomma('Co Full Name') . ',' .
	stripcomma('Street') . ',' .
	stripcomma('Suburb') . ',' .
	stripcomma('City') . ',' .
        stripcomma('Postcode') . ',' .
	stripcomma('Phone') . ',' .
        stripcomma('Purchase#') . ',' .
	stripcomma('Date') . ',' .
        stripcomma('Supplier Invoice #') . ',' . 
        stripcomma('Item Number') . ',' .
        stripcomma('Quantity') . ',' .
        stripcomma('Description') . ',' .
        stripcomma('Price') . ',' .
        stripcomma('Inc-Tax Price') . ',' .
        stripcomma('Total') . ',' .
        stripcomma('Inc-Tax Total') . ',' .
        stripcomma('Comment') . ',' .
        stripcomma('Tax Code') . ',' .
        stripcomma('GST Amount') . ',' .
        stripcomma('Received').','.
        stripcomma('Billed')."\n";
        
 /**
  * Use array_multisort to Sort the POResult with orderno ascending
  */
   foreach ($POResult as $key => $row) {
            $PONoSequence[$key]  = $row['orderno']; 
        }
        
     array_multisort($PONoSequence, SORT_ASC, $POResult);
     DB_data_seek($POResult,0);
     While ($POList = DB_fetch_array($POResult,$db)){
           
            /**
             * Cancel or Rejetected PO 
             */
          if($POList['status']== "Cancelled" or $POList['status']=="Rejected"){
              $POList['quantityord']=0;
              $POList['unitprice']=0;
              $POList['quantityrecd']=0;      
          }
           /**
            * Tax Code
            */
               if(trim($POList['taxcatname'])=="Taxable supply" or trim($POList['taxcatname'])=="Freight" ){
                    
                    $Taxcategory="NCG";
                }
                elseif (trim($POList['taxcatname'])=="Exempt" or trim($POList['taxcatname'])=="CC Exempt"){
                    $Taxcategory="FRE";
                }
                
     
		$CSVContent .= stripcomma($POList['suppname']). ',' .
			stripcomma($POList['address1']) . ',' .
			stripcomma($POList['address2']).','.
                        stripcomma($POList['address3']).','.
                        stripcomma($POList['address4']) . ',' .
			stripcomma($POList['telephone']) . ',' .
			stripcomma($POList['suppreference']) . ',' .
                        stripcomma(ConvertSQLDate($POList['trandate'])) .','. 
                        stripcomma($POList['supinvref']) .','.
                        stripcomma($POList['stockid']).','.
                        stripcomma($POList['debititemqty']).','.
                        stripcomma($POList['itemdescription']).','.
                        stripcomma($POList['unitprice']).','.
                        stripcomma($POList['unitprice']*(1+$POList['taxrate'])).','.
                        stripcomma($POList['debititemqty']*$POList['unitprice']).','.
                        stripcomma($POList['debititemqty']*$POList['unitprice']*(1+$POList['taxrate'])).','.
                        stripcomma($POList['comments'].' DN'.$POList['transno']).','.
                        stripcomma($Taxcategory) .','.
                        stripcomma($POList['debititemqty']*$POList['unitprice']*($POList['taxrate'])).','.
                        stripcomma($POList['debititemqty']) .','.
                        stripcomma($POList['debititemqty']) . "\n";
               
	}
        header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=PODebitNoteList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit; 
   
 }
 
}

elseif (isset($_POST['InvoiceRecPaylist'])){
    
    if(isset($_POST['InvoiceReceiptType']) and $_POST['InvoiceReceiptType']!=''){
        
      $PaymentType=$_POST['InvoiceReceiptType'];
    }
     
 $CSVContent = 
        stripcomma('Co Last Name') . ',' .
        stripcomma('First Name') . ',' .
	stripcomma('Deposit Account #') . ',' .
	stripcomma('ID #') . ',' .
	stripcomma('Receipt Date') . ',' .
	stripcomma('Invoice #') . ',' .
        stripcomma('Invoice Date') . ',' .
        stripcomma('Amount Applied') . ',' .
        stripcomma('Memo') . ',' .
        stripcomma('Currency Code') . ',' .
        stripcomma('Exchange Rate') . ',' .
        stripcomma('Payment Method') . ',' .
        stripcomma('Payment Notes') .','.
        stripcomma('Ref Number').','.
        stripcomma('Total Amount')."\n";
 
 /*
  * Invoice Receipt Payment List
  */
 if($PaymentType!='creditpayment'){
 $SQL = "SELECT debtortrans.id,
                                        debtortrans.transno,
                                        (-debtortrans.ovamount) AS TotalAmount,
                                        debtortrans.trandate AS ReceiptDate,
                                        debtortrans.invtext as TxtRef,
                                        custallocns.amt AS AmountApplied,
					debtorsmaster.name,
                                        debtorsmaster.debtorno,
                                        bankaccounts.accountcode,
                                        custallocns.transid_allocto,
                                        bankaccounts.bankaccountname
				FROM    debtortrans,
					debtorsmaster,
					banktrans,
                                        bankaccounts, 
                                        custallocns 
				WHERE debtortrans.type=12
                                AND banktrans.type=12 
				AND debtortrans.debtorno=debtorsmaster.debtorno
                                AND debtortrans.transno=banktrans.transno 
                                AND banktrans.bankact=bankaccounts.accountcode 
			        AND debtortrans.id= custallocns.transid_allocfrom
                                AND debtortrans.trandate between '".FormatDateForSQL($_POST['StartInvRePayDate'])."' and '".FormatDateForSQL($_POST['EndInvRePayDate'])."'
                                ORDER BY transid_allocto";

	$InvoiceRecPayResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Invoice Receipt Payment List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Invoice Receipt Payment List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
       
   $j=0;
   $filename='InvoiceReceipt.csv';
   While ($InvoiceRecPayList = DB_fetch_array($InvoiceRecPayResult,$db)){
      

    /**
     * Map Bank account between ERP and QB
     */        
    $SQLInvoiceDate="select debtortrans.trandate as InvoiceDate,
                            debtortrans.order_ AS InvoiceNumber,
                            debtortrans.alloc AS AmountApplied
                                          from debtortrans where 
                                          debtortrans.id='".$InvoiceRecPayList['transid_allocto']."' and
                                          debtortrans.type=10";
       
    $ErrMsg = _('No invoice date were returned by the SQL because');

    $SQLInvoiceDateList = DB_query($SQLInvoiceDate,$db,$ErrMsg);

    if ($SQLInvoiceDateList) {

			if( DB_num_rows($SQLInvoiceDateList) > 0 ) {
				$Row = DB_fetch_row($SQLInvoiceDateList);
				$InvoiceDate = $Row[0];     
                                $InvoiceNumber = 'W'.$Row[1];
                                $InvoiceAllocateAmount=$Row[2];
			}
                        else{
                           $InvoiceDate = '';    
                           $InvoiceNumber = '';
                           $InvoiceAllocateAmount=0; 
                        }
			DB_free_result($SQLInvoiceDateList);
	}
        
        
        /* Obtain payment number for a particular invoice */
       $SQLPaymentNo="select count(*) from custallocns, debtortrans where  debtortrans.id= custallocns.transid_allocfrom AND 
                      custallocns.transid_allocto='".$InvoiceRecPayList['transid_allocto']."' and 
                      debtortrans.type=12 and    
                      debtortrans.trandate between '".FormatDateForSQL($_POST['StartInvRePayDate'])."' and '".FormatDateForSQL($_POST['EndInvRePayDate'])."'";
       $ErrMsg = _('No payment can be selected');
       $SQLPaymentNoList = DB_query($SQLPaymentNo,$db,$ErrMsg);
       $PayNoRow = DB_fetch_row($SQLPaymentNoList);
       $PayNumber=$PayNoRow[0];
       DB_free_result($SQLPaymentNoList);
       
       /*Export Partial A of Payments */
//       if($PaymentType=='invoicereceiptA') { 
//          $filename='InvoiceReceiptPaymentA.csv';
//          if($InvoiceNumber==''){
//              continue;
//          }
//          if($TempInvoiceNumber==$InvoiceNumber){
//              continue;
//          }
//       } 
//       
//       /*Export Partial B of Payments $j==1 then Partial B, $j==2 then Partial C, 
//        * Can be furhter extend this function*/
////       if($PaymentType=='invoicereceiptB'){ 
////          $FileName='InvoiceReceiptPaymentB.csv'; 
////          if($InvoiceNumber==''){
////              continue;
////           }
////          if($TempInvoiceNumber==$InvoiceNumber){
////           if($j==1){  
////               $j++;
////           }
////           else{ 
////              $j++;
////              $TempInvoiceNumber=$InvoiceNumber;
////              continue;
////          }
////         }
////         else{
////          $j=0;
////          if($PayNumber>1){ 
////              $j++;  
////          } 
////          $TempInvoiceNumber=$InvoiceNumber;
////          continue;
////      }
//// }
//       
//        if($PaymentType=='invoicereceiptB'){ 
//          $filename='InvoiceReceiptPaymentB.csv'; 
//          if($InvoiceNumber==''){
//              continue;
//           }
//          if($TempInvoiceNumber==$InvoiceNumber){
//           $TempInvoiceNumber=$InvoiceNumber;  
//           $j++;
//           if($j!=1){ 
//             continue;
//           }
//         }
//         else{
//          $j=0;
//          $TempInvoiceNumber=$InvoiceNumber;
//          continue;
//      }
// }
//       if($PaymentType=='invoicereceiptC'){ 
//          $filename='InvoiceReceiptPaymentC.csv'; 
//          if($InvoiceNumber==''){
//              continue;
//           }
//          if($TempInvoiceNumber==$InvoiceNumber){
//           $TempInvoiceNumber=$InvoiceNumber;  
//           $j++;
//           if($j!=2){ 
//             continue;
//           }
//       }
//         else{
//          $j=0;
//          $TempInvoiceNumber=$InvoiceNumber;
//          continue;
//      }
// }
//        if($PaymentType=='invoicereceiptD'){ 
//          $filename='InvoiceReceiptPaymentD.csv'; 
//          if($InvoiceNumber==''){
//              continue;
//           }
//          if($TempInvoiceNumber==$InvoiceNumber){
//           $TempInvoiceNumber=$InvoiceNumber;  
//           $j++;
//           if($j!=3){ 
//             continue;
//           }
//       }
//         else{
//          $j=0;
//          $TempInvoiceNumber=$InvoiceNumber;
//          continue;
//      }
// }
    /**
     * Output the invoice
     */    
		$CSVContent .= stripcomma($InvoiceRecPayList['name']). ',' .','.
                               stripcomma($InvoiceRecPayList['bankaccountname']).','.','.
                               stripcomma(ConvertSQLDate($InvoiceRecPayList['ReceiptDate'])).','.
                               stripcomma($InvoiceNumber).','.
                               stripcomma($InvoiceDate).','.
                               stripcomma($InvoiceRecPayList['AmountApplied']).','.
                               stripcomma($InvoiceRecPayList['transno']).'_'.stripcomma($InvoiceRecPayList['TxtRef']).','.
                               ','.','.','.','.
                               stripcomma($InvoiceRecPayList['id']).','.
                               stripcomma($InvoiceRecPayList['TotalAmount'])."\n";
           
            $TempInvoiceNumber=$InvoiceNumber;
            unset($InvoiceDate);
            unset($InvoiceNumber);
            
	}
        
      
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename='.$filename);
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit; 
}

/*
 * Credit Payment List
 */
else{
    
    
     $SQL = "SELECT debtortrans.id,
                                        debtortrans.transno,
                                        debtortrans.inputdate AS ReceiptDate,
                                        ROUND((-debtortrans.ovamount)+ (-debtortrans.ovgst)+(-debtortrans.ovfreight),2) AS AmountApplied,
                                        debtortrans.trandate as InvoiceDate,
                                        debtortrans.order_,
					debtorsmaster.name,
                                        debtorsmaster.debtorno
				FROM    debtortrans,
					debtorsmaster
				WHERE debtortrans.type=12
				AND debtortrans.debtorno=debtorsmaster.debtorno
                                AND debtortrans.trandate between '".FormatDateForSQL($_POST['StartInvRePayDate'])."' and '".FormatDateForSQL($_POST['EndInvRePayDate'])."'
                                AND debtortrans.alloc>0   
                                AND debtortrans.ovamount >0
                                order by debtortrans.transno";

	$CreditPayResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Credit Payment List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Credit Payment List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
        
   While ($CreditPayList = DB_fetch_array($CreditPayResult,$db)){
      
    /**
     * Output the invoice
     */  
       $RefInvoiceNumber='';
       
       if($CreditPayList['order_']!=0){
           $RefInvoiceNumber= 'W'.$CreditPayList['order_'];
       }
    
		$CSVContent .= stripcomma($CreditPayList['name']). ',' .','.','.','.
                               stripcomma(ConvertSQLDate($CreditPayList['ReceiptDate'])).','.
                               stripcomma($RefInvoiceNumber).','.
                               stripcomma(ConvertSQLDate($CreditPayList['InvoiceDate'])).','.
                               stripcomma($CreditPayList['AmountApplied']).','.
                               ','.','.','.','."\n";
            
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=CreditPaymentList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit; 
}
}


elseif (isset($_POST['POBillPaylist'])){
    

    
 $SQL = "SELECT                suppliers.suppname, 
                               suppliers.address1,
                               suppliers.address2,
                               suppliers.address3,
                               suppliers.address4,       
                               suppallocs.transid_allocfrom,
                               suppallocs.transid_allocto,
                               purchorders.ref_salesorder as orderno,
                               suppliers.supplierid,
                               suppliers.taxgroupid,
                               suppallocs.amt AS totalamount,
                               supptrans.trandate,
                               supptrans.suppreference,
                               purchorders.ref_salesorder,
                               purchorders.orderno as purchoroder
			       FROM    supptrans,
					suppallocs,
					purchorderdetails,
					purchorders,
					suppliers
				WHERE purchorderdetails.suppinvtranref=supptrans.id AND
				      purchorderdetails.qtyinvoiced<>0 AND
				      supptrans.type=20 AND
				      suppallocs.transid_allocto=supptrans.id AND
				      purchorderdetails.orderno=purchorders.orderno AND
				      purchorders.supplierno=suppliers.supplierid AND 
                                (SELECT pm.trandate FROM  supptrans as pm
		          WHERE  pm.id= suppallocs.transid_allocfrom AND
			  pm.type=22)  between '".FormatDateForSQL($_POST['StartPOBillPayDate'])."' and '".FormatDateForSQL($_POST['EndPOBillPayDate'])."'
                                GROUP BY suppallocs.id    
                                ORDER BY purchorders.ref_salesorder";

	$POBillPayResult = DB_query($SQL,$db,'','',false,false);
     
	if (DB_error_no($db) !=0) {
		$title = _('PO Bill Payment List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The PO Bill Payment List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	
      
 $CSVContent = 
        stripcomma('Co Last Name') . ',' .
        stripcomma('First Name') . ',' .

        stripcomma('Payment Account #') . ',' .
        stripcomma('Cheque Number') . ',' .
        stripcomma('Payment Date') . ',' .
        
        stripcomma('Statement Text') . ',' .
        stripcomma('Purchase #') . ',' .
        stripcomma('Suppliers #') . ',' .
        stripcomma('Bill Date') . ',' .
        
        stripcomma('Amount Applied') . ',' .
        stripcomma('Memo') .  ','.
        stripcomma('RefNumber'). "\n";
 
 


   While ($POBillPayResultList = DB_fetch_array($POBillPayResult,$db)){
    /**
     * Calculate Tax Amount
     */ 
       $TaxRate=0;
       $_SESSION['SuppTrans'] = new SuppTrans;
       $_SESSION['SuppTrans']->TaxGroup = $POBillPayResultList['taxgroupid'];
       
       
	$LocalTaxProvinceResult = DB_query("SELECT taxprovinceid
								FROM locations
								WHERE loccode = '" . $_SESSION['UserStockLocation'] . "'", $db);

	if(DB_num_rows($LocalTaxProvinceResult)==0){
		prnMsg(_('The tax province associated with your user account has not been set up in this database. Tax calculations are based on the tax group of the supplier and the tax province of the user entering the invoice. The system administrator should redefine your account with a valid default stocking location and this location should refer to a valid tax province'),'error');
		include('includes/footer.inc');
		exit;
	}

	$LocalTaxProvinceRow = DB_fetch_row($LocalTaxProvinceResult);
	$_SESSION['SuppTrans']->LocalTaxProvince = $LocalTaxProvinceRow[0];

	$_SESSION['SuppTrans']->GetTaxes();
        
        foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {
         $TaxRate= $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate ; 
        }
        
    /**
     * Select Bill Date
     */        
    $SQLBillDate="SELECT debtortrans.trandate as billdate
                       FROM   debtortrans
		       WHERE  debtortrans.order_= '".$POBillPayResultList['ref_salesorder']. "'";
    
    $ErrMsg = _('No Bill Date were returned by the SQL because');

    $SQLBillDateList = DB_query($SQLBillDate,$db,$ErrMsg);
   
    if ($SQLBillDateList) {

			if( DB_num_rows($SQLBillDateList) > 0 ) {
				$Row = DB_fetch_row($SQLBillDateList);
				$BillDate = $Row[0];     
                        
			}
			DB_free_result($SQLBillDateList);
	}
    
    /**
     * Extract Bank and PO Payment Details
     */        
    $SQLPOBankPayment="SELECT bankaccounts.bankaccountname, 
                              supptrans.trandate,
                              banktrans.bankact
                    FROM    supptrans,
			    suppallocs,
		            banktrans,
                            bankaccounts
		      
		    WHERE suppallocs.transid_allocto='".$POBillPayResultList['transid_allocto']."' AND
			  supptrans.id= '".$POBillPayResultList['transid_allocfrom']."'AND
			  supptrans.type=22 AND
			  banktrans.type=22 AND
                          banktrans.bankact=bankaccounts.accountcode  AND 
			  banktrans.transno=supptrans.transno ";
       
    $ErrMsg = _('No Bank and PO Payment Details were returned by the SQL because');

    $SQLPOBankPaymentList = DB_query($SQLPOBankPayment,$db,$ErrMsg);

    if ($SQLPOBankPaymentList) {

			if( DB_num_rows($SQLPOBankPaymentList) > 0 ) {
				$Row = DB_fetch_row($SQLPOBankPaymentList);
				$PaymentAccount = $Row[0];     
                                $PaymentDate = $Row[1]; 
                               
			}
			DB_free_result($SQLPOBankPaymentList);
	}
        

        if($POBillPayResultList['suppreference']==''){
           $POBillPayResultList['suppreference']='payment00'; 
        }
                  
    /**
     * Output the invoice
     */    
        if($POBillPayResultList['orderno']==0){
            $PO_referenceNo='W'.'-'.$POBillPayResultList['purchoroder'];
        }
        else{
            $PO_referenceNo='W'.$POBillPayResultList['orderno'].'-'.$POBillPayResultList['purchoroder'];
        }
        
    
		$CSVContent .= stripcomma($POBillPayResultList['suppname']). ',' .','.
                               stripcomma($PaymentAccount).','.substr($POBillPayResultList['suppreference'],0,11).','.
                               stripcomma(ConvertSQLDate($PaymentDate)).','.','.
                               stripcomma($PO_referenceNo).','.','.
                               stripcomma(ConvertSQLDate($BillDate)).','.
                               stripcomma($POBillPayResultList['totalamount']).','.','.
                               stripcomma($POBillPayResultList['transid_allocfrom'])."\n";
              // $CSVContent .=   "\n";
                $PORecordId=$POBillPayResultList['orderno'];
                $POSupplierID=$POBillPayResultList['supplierid'];
                $i++;
                
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=POBillPaymentList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit; 
}

elseif ( isset($_POST['salesmanlist']) ) {
	$SQL = "SELECT salesmancode,
			salesmanname,
			smantel,
			smanfax,
			commissionrate1,
			breakpoint,
			commissionrate2
		FROM salesman";

	$SalesManResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Salesman List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Salesman List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$CSVContent =   stripcomma('Co./Last Name') . ',' .
			stripcomma('First Name') . ',' .
			stripcomma('Card ID') . ',' .
			stripcomma('Card Status') . "\n";


	While ($SalesManList = DB_fetch_array($SalesManResult,$db)){

		$CommissionRate1 = $SalesManList['commissionrate1'];
		$BreakPoint 	 = $SalesManList['breakpoint'];
		$CommissionRate2 = $SalesManList['commissionrate2'];

		$CSVContent .= (stripcomma($SalesManList['salesmanname']) . ','. ',' .
			stripcomma($SalesManList['salesmancode']) . ',' .
			stripcomma('N') . "\n");
	}
	header('Content-type: application/text');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SalesmanList.txt');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['imagelist']) ) {
	$SQL = "SELECT stockid
		FROM stockmaster
		ORDER BY stockid";
	$ImageResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Security Token List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Image List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$CSVContent = stripcomma('stockid') . ','.
				  stripcomma('filename') . ','.
				  stripcomma('url') . "\n";
	$baseurl = 'http://'. $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . 'getstockimg.php?automake=1&stockid=%s.png';
	While ($ImageList = DB_fetch_array($ImageResult,$db)){
		$url = sprintf($baseurl, urlencode($ImageList['stockid']));
		$CSVContent .= (
			stripcomma($ImageList['stockid']) . ',' .
			stripcomma($ImageList['stockid'] . '.png') . ',' .
			stripcomma($url) . "\n");
	}

	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=ImageList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['sectokenlist']) ) {
	$SQL = "SELECT tokenid,
			tokenname
		FROM securitytokens";

	$SecTokenResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Security Token List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Security Token List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$CSVContent = stripcomma('tokenid') . ',' .
			stripcomma('tokenname') . "\n";


	While ($SecTokenList = DB_fetch_array($SecTokenResult,$db)){

		$CSVContent .= (stripcomma($SecTokenList['tokenid']) . ',' .
			stripcomma($SecTokenList['tokenname']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SecTokenList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['secrolelist']) ) {
	$SQL = "SELECT secroleid,
			secrolename
		FROM securityroles";

	$SecRoleResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Security Role List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Security Role List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$CSVContent = stripcomma('secroleid') . ',' .
			stripcomma('secrolename') . "\n";


	While ($SecRoleList = DB_fetch_array($SecRoleResult,$db)){

		$CSVContent .= (stripcomma($SecRoleList['secroleid']) . ',' .
			stripcomma($SecRoleList['secrolename']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SecRoleList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['secgrouplist']) ) {
	$SQL = "SELECT secroleid,
			tokenid
		FROM securitygroups";

	$SecGroupResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Security Group List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Security Group List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$CSVContent = stripcomma('secroleid') . ',' .
			stripcomma('tokenid') . "\n";


	While ($SecGroupList = DB_fetch_array($SecGroupResult,$db)){

		$CSVContent .= (stripcomma($SecGroupList['secroleid']) . ',' .
			stripcomma($SecGroupList['tokenid']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SecGroupList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['secuserlist']) ) {
	$SQL = "SELECT userid,
			password,
			realname,
			customerid,
			phone,
			email,
			defaultlocation,
			fullaccess,
			lastvisitdate,
			branchcode,
			pagesize,
			modulesallowed,
			blocked,
			displayrecordsmax,
			theme,
			language
		FROM www_users
		WHERE (customerid <> '') OR
			(NOT customerid IS NULL)";

	$SecUserResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Security User List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Security User List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$CSVContent = stripcomma('userid') . ',' .
			stripcomma('password') . ','.
			stripcomma('realname') . ','.
			stripcomma('customerid') . ','.
			stripcomma('phone') . ','.
			stripcomma('email') . ','.
			stripcomma('defaultlocation') . ','.
			stripcomma('fullaccess') . ','.
			stripcomma('lastvisitdate') . ','.
			stripcomma('branchcode') . ','.
			stripcomma('pagesize') . ','.
			stripcomma('modulesallowed') . ','.
			stripcomma('blocked') . ','.
			stripcomma('displayrecordsmax') . ','.
			stripcomma('theme') . ','.
			stripcomma('language') . ','.
			stripcomma('pinno') . ','.
			stripcomma('swipecard') . "\n";


	While ($SecUserList = DB_fetch_array($SecUserResult,$db)){

		$CSVContent .= (stripcomma($SecUserList['userid']) . ',' .
			stripcomma($SecUserList['password']) . ',' .
			stripcomma($SecUserList['realname']) . ',' .
			stripcomma($SecUserList['customerid']) . ',' .
			stripcomma($SecUserList['phone']) . ',' .
			stripcomma($SecUserList['email']) . ',' .
			stripcomma($SecUserList['defaultlocation']) . ',' .
			stripcomma($SecUserList['fullaccess']) . ',' .
			stripcomma($SecUserList['lastvisitdate']) . ',' .
			stripcomma($SecUserList['branchcode']) . ',' .
			stripcomma($SecUserList['pagesize']) . ',' .
			stripcomma($SecUserList['modulesallowed']) . ',' .
			stripcomma($SecUserList['blocked']) . ',' .
			stripcomma($SecUserList['displayrecordsmax']) . ',' .
			stripcomma($SecUserList['theme']) . ',' .
			stripcomma($SecUserList['language']) . ',' .
			stripcomma($SecUserList['pinno']) . ',' .
			stripcomma($SecUserList['swipecard']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SecUserList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} else {
	$title = _('Data Exports');
	include('includes/header.inc');

	// SELECT EXPORT FOR PRICE LIST

	echo '<br />';
	echo '<form method="post" action="' . $_SERVER['PHP_SELF']  . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Product List Export') . '</th></tr>';

	$sql = 'SELECT sales_type, typeabbrev FROM salestypes order by sales_type desc';
	$SalesTypesResult=DB_query($sql,$db);
	echo '<tr><td>' . _('For Sales Type/Price List') . ':</td>';
	echo '<td><select name="SalesType">';
	while ($myrow=DB_fetch_array($SalesTypesResult)){
	          echo '<option Value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'];
	}
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Inventory Type') . ':</td>';
	echo '<td><select name="InventoryType">';
	echo '<option Value="InventoryPart">' . _('Inventory Part').'</option>';
	echo '<option Value="NonInventoryPart">' . _('Non Inventory Part').'</option>';
	echo '</select></td></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='Submit' name='pricelist' value='" . _('Export') . "'></div>";
	echo '</form><br />';

	// SELECT EXPORT FOR CUSTOMER LIST


	echo "<br />";
	// Export Stock For Location
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
      
        
	echo '<tr><th colspan=2>' . _('Customer List Export') . '</th></tr>';
	$sql = 'SELECT loccode, locationname FROM locations';
	$SalesTypesResult=DB_query($sql,$db);
	echo '<tr><td>' . _('For Location') . ':</td>';
	echo '<td><select name="Location">';
	while ($myrow=DB_fetch_array($SalesTypesResult)){
	          echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='Submit' name='custlist' value='" . _('Export') . "'></div>";
	echo '</form><br />';
        
        // SELECT EXPORT FOR SALES MAN

	echo "<br />";
	// Export Stock For Location
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Supplier List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><div class='centre'><input type='Submit' name='supplierlist' value='" . _('Export') . "'></div>";
	echo '</form><br />';
       
        // SELECT EXPORT FOR PO List

	echo "<br />";
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('PO List Export') . '</th></tr>';
        echo '<tr><td>' . _('Choose A Type') . ':</td>';
        echo '<td><select name="POCreditType">';
	echo '<option Value="POList">' . _('PO List').'</option>';
	echo '<option Value="DebitNoteList">' . _('PO Debit Note List').'</option>';
	echo '</select></td></tr>'; 
        echo '<tr><td>'._('Start Order Date of PO : ').'</td><td><input type=text name="StartPODate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '<tr><td>'._('End Order Date of PO : ').'</td><td><input type=text name="EndPODate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
        echo '</table>';
	echo "<div class='centre'><div class='centre'><input type='Submit' name='POlist' value='" . _('Export') . "'></div>";
	echo '</form><br />';
        
        
        // SELECT EXPORT FOR Invoice List

	echo "<br />";
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Invoice List / Credit Note Export') . '</th></tr>';
        echo '<tr><td>' . _('Choose A Type') . ':</td>';
        echo '<td><select name="InvoiceCreditType">';
	echo '<option Value="invoice">' . _('Invoice List').'</option>';
	echo '<option Value="creditnote">' . _('Credit Note List').'</option>';
	echo '</select></td></tr>'; 
        echo '<tr><td>'._('Start Date of Invoice : ').'</td><td><input type=text name="StartInvDate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '<tr><td>'._('End Date of Invoice : ').'</td><td><input type=text name="EndInvDate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '</table>';
	echo "<div class='centre'><div class='centre'><input type='Submit' name='Invoicelist' value='" . _('Export') . "'></div>";
	echo '</form><br />';
        

        // SELECT EXPORT FOR Invoice Receipt Payment

	echo "<br />";
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Invoice Receipt Payment Export') . '</th></tr>';
        echo '<tr><td>' . _('Choose A Type') . ':</td>';
        echo '<td><select name="InvoiceReceiptType">';
	echo '<option Value="invoicereceipt">' . _('Invoice Receipt Payment').'</option>';
//        echo '<option Value="invoicereceiptB">' . _('Invoice Receipt B Payment').'</option>';
//        echo '<option Value="invoicereceiptC">' . _('Invoice Receipt C Payment').'</option>';
//        echo '<option Value="invoicereceiptD">' . _('Invoice Receipt D Payment').'</option>';
	echo '<option Value="creditpayment">' . _('Credit Payment').'</option>';
	echo '</select></td></tr>'; 
        echo '<tr><td>'._('Start Date of Receipt : ').'</td><td><input type=text name="StartInvRePayDate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '<tr><td>'._('End Date of Receipt : ').'</td><td><input type=text name="EndInvRePayDate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '</table>';
	echo "<div class='centre'><div class='centre'><input type='Submit' name='InvoiceRecPaylist' value='" . _('Export') . "'></div>";
	echo '</form><br />';
        
        // SELECT EXPORT FOR PO Bill Payment

	echo "<br />";
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('PO Bill Payment Export') . '</th></tr>';
        
        echo '<tr><td>'._('Start Date of PO Bill Payment : ').'<input type=text name="StartPOBillPayDate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '<tr><td>'._('End Date of PO Bill Payment : ').'<input type=text name="EndPOBillPayDate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '</table>';
	echo "<div class='centre'><div class='centre'><input type='Submit' name='POBillPaylist' value='" . _('Export') . "'></div>";
	echo '</form><br />';

	// SELECT EXPORT FOR SALES MAN

	echo "<br />";
	// Export Stock For Location
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Salesman List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><div class='centre'><input type='Submit' name='salesmanlist' value='" . _('Export') . "'></div>";
	echo '</form><br />';

	// SELECT EXPORT FOR IMAGES
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Image List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='Submit' name='imagelist' value='" . _('Export') . "'></div>";
	echo '</form><br />';

	// SELECT EXPORT SECURITY TOKENS
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Security Token List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='Submit' name='sectokenlist' value='" . _('Export') . "'></div>";
	echo '</form><br />';

	// SELECT EXPORT SECURITY ROLES
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Security Role List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='Submit' name='secrolelist' value='" . _('Export') . "'></div>";
	echo '</form><br />';

	// SELECT EXPORT SECURITY GROUPS
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Security Group List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='Submit' name='secgrouplist' value='" . _('Export') . "'></div>";
	echo '</form><br />';

	// SELECT EXPORT SECURITY USERS
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Security User List Export') . '</th></tr>';
	echo '</table>';
	echo '<div class="centre"><input type="Submit" name="secuserlist" value="' . _('Export') . '"></div>';
	echo '</form><br />';


	include('includes/footer.inc');
}
?>