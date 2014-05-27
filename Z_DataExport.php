<?php

/* $Id: Z_DataExport.php 4543 2011-04-09 06:12:05Z daintree $*/


include('includes/session.inc');
include('includes/DefineSuppTransClass.php');
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


// EXPORT FOR PRICE LIST
if ( isset($_POST['pricelist']) ) {

		$SQL = "SELECT sales_type FROM salestypes WHERE typeabbrev='" . $_POST['SalesType'] . "'";
		$SalesTypeResult = DB_query($SQL,$db);
		$SalesTypeRow = DB_fetch_row($SalesTypeResult);
		$SalesTypeName = $SalesTypeRow[0];

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
			AND prices.typeabbrev='" . $_POST['SalesType'] . "'
			AND ( (prices.debtorno='') OR (prices.debtorno IS NULL))
                        AND taxcategories.taxcatid=stockmaster.taxcatid
			ORDER BY prices.currabrev,
				stockmaster.categoryid,
				stockmaster.stockid";
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
                      stripcomma('Buy') . ',' .
                      stripcomma('Sell') . ',' .
                      stripcomma('Inventory') . ',' .
                      stripcomma('Asset Acct') . ',' .
                      stripcomma('Income Acct') . ',' .
                      stripcomma('Expense/COS Acct') . ',' .      
        stripcomma('Item Picture') . ',' .
                      stripcomma('Description') . ',' .
     
        stripcomma('Use Desc. On Sale') . ',' .
        stripcomma('Custom List 1') . ',' .
        stripcomma('Custom List 2') . ',' .
        stripcomma('Custome List 3') . ',' .
        stripcomma('Custom Field 1') . ',' .
        stripcomma('Custom Field 2') . ',' .
        stripcomma('Custome Field 3') . ',' .
        
        stripcomma('Primary Supplier') . ',' .
        stripcomma('Supplier Item Number') . ',' .
        stripcomma('Tax Code When Bought') . ',' .
        stripcomma('Buy Unit Measure') . ',' . 
        stripcomma('# Items/Buy Unit') . ',' .
        stripcomma('Recorder Quantity') . ',' .
        stripcomma('minimum Level') . ',' . 
        stripcomma('Selling Price') . ',' .
        stripcomma('Sell Unit Measure') . ',' .
        stripcomma('Tax Code When Sold') . ',' .
        stripcomma('Sell Price Inclusive') . ',' . 
        stripcomma('Sales Tax Calc. Method') . ',' .  
        stripcomma('# Items/Sell Unit') . ',' .
        stripcomma('Quantity Break 1') . ',' . 
        stripcomma('Quantity Break 2') . ',' . 
        stripcomma('Quantity Break 3') . ',' . 
        stripcomma('Quantity Break 4') . ',' . 
        stripcomma('Quantity Break 5') . ',' .  
       
       stripcomma('Price Level A, Qty Break 1') . ',' . 
       stripcomma('Price Level B, Qty Break 1') . ',' . 
       stripcomma('Price Level C, Qty Break 1') . ',' . 
       stripcomma('Price Level D, Qty Break 1') . ',' . 
       stripcomma('Price Level E, Qty Break 1') . ',' .  
       stripcomma('Price Level F, Qty Break 1') . ',' .    
                        
       stripcomma('Price Level A, Qty Break 2') . ',' . 
       stripcomma('Price Level B, Qty Break 2') . ',' . 
       stripcomma('Price Level C, Qty Break 2') . ',' . 
       stripcomma('Price Level D, Qty Break 2') . ',' . 
       stripcomma('Price Level E, Qty Break 2') . ',' .  
       stripcomma('Price Level F, Qty Break 2') . ',' . 
                        
       stripcomma('Price Level A, Qty Break 3') . ',' . 
       stripcomma('Price Level B, Qty Break 3') . ',' . 
       stripcomma('Price Level C, Qty Break 3') . ',' . 
       stripcomma('Price Level D, Qty Break 3') . ',' . 
       stripcomma('Price Level E, Qty Break 3') . ',' .  
       stripcomma('Price Level F, Qty Break 3') . ',' . 
                        
       stripcomma('Price Level A, Qty Break 4') . ',' . 
       stripcomma('Price Level B, Qty Break 4') . ',' . 
       stripcomma('Price Level C, Qty Break 4') . ',' . 
       stripcomma('Price Level D, Qty Break 4') . ',' . 
       stripcomma('Price Level E, Qty Break 4') . ',' .  
       stripcomma('Price Level F, Qty Break 4') . ',' . 
                        
       stripcomma('Price Level A, Qty Break 5') . ',' . 
       stripcomma('Price Level B, Qty Break 5') . ',' . 
       stripcomma('Price Level C, Qty Break 5') . ',' . 
       stripcomma('Price Level D, Qty Break 5') . ',' . 
       stripcomma('Price Level E, Qty Break 5') . ',' .  
       stripcomma('Price Level F, Qty Break 5') . ',' . 
        
                      stripcomma('Inactive Item') . ',' .
                      stripcomma('Standard Cost') . "\n";
// 
//			stripcomma('barcode') . ',' .
//			stripcomma('units') . ',' .
//			stripcomma('mbflag') . ',' .
//			stripcomma('taxcatid') . ',' .
//			stripcomma('discontinued') . ',' .
//			stripcomma('price') . ',' .
//			stripcomma('qty') . ',' .
//			stripcomma('categoryid') . ',' .
//			stripcomma('categorydescription') . "\n";

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
                          purchdata.price
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
       if ( $resultSup ) {
			if( DB_num_rows($resultSup) > 0 ) {
				$SupRow = DB_fetch_row($resultSup);
				$Suppliername = $SupRow[0];
                                $Supplierpartno=$SupRow[1];
                                $SupplierPrice=$SupRow[2];
			}
			DB_free_result($resultSup);
                     
		}
             
       
    //Category type
              
              if(trim($PriceList['categoryid'])=="S-Acc"){
                    
                  $salesaccountName="Sales - Accessories";
                  $costaccountName="COGS - Accessories";
                  $AccsetaccountName="Merchandise Inventory";
                }
                elseif(trim($PriceList['categoryid'])=="S-Inv"){
             
                  $salesaccountName="Sales - Inverters";
                  $costaccountName="COGS - Inverters";
                  $AccsetaccountName="Merchandise Inventory";

                }
                elseif(trim($PriceList['categoryid'])=="S-Kit"){
               
                  $salesaccountName="Sales - Kits";
                  $costaccountName="COGS - Kits";
                  $AccsetaccountName="Merchandise Inventory";
                }
                elseif(trim($PriceList['categoryid'])=="S-Mou"){
                    
                  $salesaccountName="Sales - Mounting Systems";
                  $costaccountName="COGS - Mounting Systems";
                  $AccsetaccountName="Merchandise Inventory";
                }
                elseif(trim($PriceList['categoryid'])=="S-Pan"){
                    
                  $salesaccountName="Sales - Panels";
                  $costaccountName="COGS - Panels";
                  $AccsetaccountName="Merchandise Inventory";
                }
                elseif(trim($PriceList['categoryid'])=="S-CC"){
                    
                  $salesaccountName="C Card Surcharge Collected";
                  $costaccountName="Misc. Income";
                  $AccsetaccountName='';
                }
                elseif(trim($PriceList['categoryid'])=="S-Oth"){
                    
                  $salesaccountName="Freight Collected";
                  $costaccountName="COGS - Freight Paid";
                  $AccsetaccountName='';
                }
                else{
                  $salesaccountName="Sales - Accessories";
                  $costaccountName="COGS - Accessories";
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
                        stripcomma("B") . ',' .
                        stripcomma("S") . ',' .
                        stripcomma("I") . ',' .
                        stripcomma($GLAssetAccountCode) . ',' .
                        stripcomma($GLSalesAccountCode) . ',' .
                        stripcomma($GLCostsAccountCode) . ',' .','.
                        
			stripcomma($PriceList['description']) . ',' .',' .',' .',' .',' .',' .',' .',' .
                        
                        stripcomma($Suppliername) . ',' .
                        stripcomma($Supplierpartno) . ',' .
                        stripcomma($Taxcategory) . ',' .',' .
                        
                        stripcomma($PriceList['units']) . ',' .',' .',' .
                        
                        stripcomma($DisplayUnitPrice) . ',' .',' .
                        
                        stripcomma($Taxcategory) . ',' .',' .',' .
                        stripcomma($PriceList['units']) . ',' .',' .',' .',' .',' .',' .
                        ',' .',' .',' .',' .',' .',' .',' .',' .',' .',' .',' .',' .
                        ',' .',' .',' .',' .',' .',' .',' .',' .',' .',' .',' .',' .
                        ',' .',' .',' .',' .',' .',' .
                        stripcomma($inactive) . ',' .
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
                               custcontacts.phoneno,
                               custcontacts.contactname
                               FROM debtorsmaster left join custcontacts
                               on custcontacts.debtorno=debtorsmaster.debtorno
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
       
	
	$CSVContent = stripcomma('Co./Last Name') . ',' .
        stripcomma('First Name') . ',' .
        stripcomma('Card ID') . ',' .
        stripcomma('Card Status') . ',' .
        stripcomma('Currency Code') . ',' .
        
	stripcomma('Addr 1 - Line 1') . ',' .
        stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Phone # 2') . ',' .
        stripcomma('Phone # 3') . ',' .
        stripcomma('Fax #') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('WWW') . ',' .
        stripcomma('Contact Name') . ',' .
        stripcomma('Salutation') . ',' .
        
        
        stripcomma('Addr 2 - Line 1') . ',' .
	stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Phone # 2') . ',' .
        stripcomma('Phone # 3') . ',' .
        stripcomma('Fax #') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('WWW') . ',' .
        stripcomma('Contact Name') . ',' .
        stripcomma('Salutation') . ',' .
        
        
        stripcomma('Addr 3 - Line 1') . ',' .
	stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Phone # 2') . ',' .
        stripcomma('Phone # 3') . ',' .
        stripcomma('Fax #') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('WWW') . ',' .
        stripcomma('Contact Name') . ',' .
        stripcomma('Salutation') . ',' .
        
        
        stripcomma('Addr 4 - Line 1') . ',' .
	stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Phone # 2') . ',' .
        stripcomma('Phone # 3') . ',' .
        stripcomma('Fax #') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('WWW') . ',' .
        stripcomma('Contact Name') . ',' .
        stripcomma('Salutation') . ',' .
        
        
        stripcomma('Addr 5 - Line 1') . ',' .
	stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Phone # 2') . ',' .
        stripcomma('Phone # 3') . ',' .
        stripcomma('Fax #') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('WWW') . ',' .
        stripcomma('Contact Name') . ',' .
        stripcomma('Salutation') . ',' .  
         
        stripcomma('Picture') . ','. 
        stripcomma('Notes') . ','. 
        stripcomma('Identifiers') . ','. 
        stripcomma('Custom List 1') . ','. 
        stripcomma('Custom List 2') . ','. 
        stripcomma('Custom List 3') . ','. 
        stripcomma('Custom Field 1') . ','. 
        stripcomma('Custom Field 2') . ','. 
        stripcomma('Custom Field 3') . ','. 
        stripcomma('Billing Rate') . ','. 
        stripcomma('Terms - Payment is Due') . ','. 
        stripcomma('Discount Days') . ','. 
        stripcomma('Balance Due Days') . ','. 
        stripcomma('-% Discount') . ','. 
        stripcomma('-% Monthly Charge') . ','. 
        stripcomma('Tax Code') . ','. 
        stripcomma('Credit Limit') . ','. 
        stripcomma('Tax ID No.') . ','. 
        stripcomma('Volume Discount %') . ','. 
  										 			     
        
        stripcomma('Sales/Purchase Layout') . ','. 
        stripcomma('Price Level') . ','. 
        stripcomma('Payment Method') . ','. 
        stripcomma('Payment Notes') . ','. 
        stripcomma('Name on Card') . ','. 
        stripcomma('Card Number') . ','. 
        stripcomma('Expiry Date') . ','. 
        stripcomma('BSB') . ','. 
        stripcomma('Account Number') . ','. 
        stripcomma('Account Name') . ','. 
        stripcomma('A.B.N.') . ','. 
        stripcomma('A.B.N. Branch') . ','. 
        stripcomma('Account') . ','. 
        stripcomma('Salesperson ') . ','. "\n";

 	  	   
      		 					   		  								
	While ($CustList = DB_fetch_array($CustResult,$db)){
            
            $SQL = "SELECT debtorsmaster.debtorno,
			custbranch.branchcode,
			debtorsmaster.name,
			custbranch.braddress1,
			custbranch.braddress2,
			custbranch.braddress3,
			custbranch.braddress4,
			custbranch.braddress5,
			custbranch.braddress6,
			custbranch.disabletrans,
			custbranch.phoneno,
			custbranch.faxno,
			custbranch.email,
                        custbranch.contactname,
                        salesman.salesmanname
             
		FROM debtorsmaster,
			custbranch,
                        salesman
		WHERE debtorsmaster.debtorno=custbranch.debtorno
                AND salesman.salesmancode=custbranch.salesman
		AND ((defaultlocation = '".$_POST['Location']."') OR (defaultlocation = '') OR (defaultlocation IS NULL))
                AND custbranch.debtorno='".$CustList['debtorno']."'";
            
            
	     $BranchResult = DB_query($SQL,$db,'','',false,false);
             
             $CSVContent .= stripcomma(trim($CustList['name'])). ','. ','.
                            stripcomma(trim($CustList['debtorno'])).','.
                            'N'.','.','.
                        stripcomma($CustList['address1']) . ',' .','. ','. ','.
			stripcomma($CustList['address2']) . ',' .
			stripcomma($CustList['address3']) . ',' .
			stripcomma($CustList['address4']) . ',' .','.   
                        stripcomma(substr($CustList['phoneno'], 0, 4).' '.substr($CustList['phoneno'], 4)) . ',' .','.','.','.','.','.         
			stripcomma(trim($CustList['contactname'])).','.',';
                            
           //MYOB only allows maximum 5 Branch Addresses 
             While ($BranchList = DB_fetch_array($BranchResult,$db)){
		
                    if($cusTag>=4){
                          break;
                      }
                        
                        $CSVContent .= 
			stripcomma($BranchList['braddress1']) . ',' .','. ','. ','.
			stripcomma($BranchList['braddress2']) . ',' .
			stripcomma($BranchList['braddress3']) . ',' .
			stripcomma($BranchList['braddress4']) . ',' .','.   
                        stripcomma(substr($BranchList['phoneno'], 0, 4).' '.substr($BranchList['phoneno'], 4)) . ',' .','.','.','.          
			stripcomma($BranchList['email']) . ',' .','.
			stripcomma(trim($BranchList['contactname'])).','.',';
                      
                        $salesMan=str_replace(","," ",$BranchList['salesmanname']);
                        
                        $cusTag++;
             }
            
//            if($cusTag==1){
//                $CSVContent .=
//                 str_repeat(',',80).
//                 $salesMan.',';   
//             }
//             elseif($cusTag==2){
//               $CSVContent .= 
//                 str_repeat(',',64).
//                 $salesMan.',';      
//             }
//             elseif($cusTag==3){
//               $CSVContent .= 
//                 str_repeat(',',48).
//                 $salesMan.',';    
//             }
//             elseif($cusTag==4){
//          $CSVContent .= 
//                 str_repeat(',',32).
//                 $salesMan.',';   
//             }
          
             unset($cusTag);
             $CSVContent .="\n";
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

 
	$CSVContent = stripcomma('Co./Last Name') . ',' .
        stripcomma('First Name') . ',' .
        stripcomma('Card ID') . ',' .
        stripcomma('Card Status') . ',' .
        stripcomma('Currency Code') . ',' .
        
	stripcomma('Addr 1 - Line 1') . ',' .
        stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Phone # 2') . ',' .
        stripcomma('Phone # 3') . ',' .
        stripcomma('Fax #') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('WWW') . ',' .
        stripcomma('Contact Name') . ',' .
        stripcomma('Salutation') . ',' . 
        
         
        stripcomma('Addr 2 - Line 1') . ',' .
	stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Phone # 2') . ',' .
        stripcomma('Phone # 3') . ',' .
        stripcomma('Fax #') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('WWW') . ',' .
        stripcomma('Contact Name') . ',' .
        stripcomma('Salutation') . ',' .
        
        
        stripcomma('Addr 3 - Line 1') . ',' .
	stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Phone # 2') . ',' .
        stripcomma('Phone # 3') . ',' .
        stripcomma('Fax #') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('WWW') . ',' .
        stripcomma('Contact Name') . ',' .
        stripcomma('Salutation') . ',' .
        
        
        stripcomma('Addr 4 - Line 1') . ',' .
	stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Phone # 2') . ',' .
        stripcomma('Phone # 3') . ',' .
        stripcomma('Fax #') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('WWW') . ',' .
        stripcomma('Contact Name') . ',' .
        stripcomma('Salutation') . ',' .
        
        
        stripcomma('Addr 5 - Line 1') . ',' .
	stripcomma(' - Line 2') . ',' .
        stripcomma(' - Line 3') . ',' .
        stripcomma(' - Line 4') . ',' .
	stripcomma('City') . ',' .
	stripcomma('State') . ',' .
	stripcomma('Postcode') . ',' .
        stripcomma('Country') . ',' .
	stripcomma('Phone # 1') . ',' .
        stripcomma('Phone # 2') . ',' .
        stripcomma('Phone # 3') . ',' .
        stripcomma('Fax #') . ',' .
        stripcomma('Email') . ',' .
        stripcomma('WWW') . ',' .
        stripcomma('Contact Name') . ',' .
        stripcomma('Salutation') . ','. "\n";
										           	           	           	           				
	While ($SuppList = DB_fetch_array($SuppResult,$db)){
    
		$CSVContent .= stripcomma($SuppList['suppname']). ','. ','.
                               stripcomma($SuppList['supplierid']).','.
                            'N'.','.','.
			stripcomma($SuppList['address1']) . ',' .','. ','. ','.
			stripcomma($SuppList['address2']) . ',' .
			stripcomma($SuppList['address3']) . ',' .
			stripcomma($SuppList['address4']) . ',' .','.
			stripcomma($SuppList['tel']) . ',' .','.','.
                        stripcomma($SuppList['fax']).','.
                        stripcomma($SuppList['email']).','.','.
                        stripcomma($SuppList['contact']).','.','."\n"; 
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
                                        taxcategories.taxcatname
                                        
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
                                        taxcategories
                                     
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
        stripcomma('Co./Last Name') . ',' .
        
        stripcomma('First Name') . ',' .
	stripcomma('Addr 1- Line 1') . ',' .
	stripcomma('Addr 1- Line 2') . ',' .
	stripcomma('Addr 1- Line 3') . ',' .
	stripcomma('Addr 1- Line 4') . ',' .
        
        stripcomma('Inclusive') . ',' .
	stripcomma('Invoice # 1') . ',' .
	stripcomma('Date') . ',' .
        stripcomma('Customer PO') . ',' .
        stripcomma('Ship Via') . ',' .
        
        stripcomma('Delivery Status') . ',' .
        stripcomma('Item Number') . ',' .
        stripcomma('Quantity') . ',' .
        stripcomma('Description') . ',' .
        stripcomma('Price') . ',' .
        
        stripcomma('Inc-Tax Price') . ',' .
        stripcomma('Discount') . ',' .
        stripcomma('Total') . ',' .
        stripcomma('Inc-Tax Total') . ',' .
        stripcomma('Job') . ',' .
        
        stripcomma('Comment') . ',' .
        stripcomma('Journal Memo') . ',' .
        stripcomma('Salesperson Last Name') . ',' .
        stripcomma('Salesperson First Name') . ',' .
        stripcomma('Shipping Date') . ',' .
        stripcomma('Referral Source') . ',' .
        
        stripcomma('Tax Code') . ',' .
        stripcomma('Non-GST Amount') . ',' .
        stripcomma('GST Amount') . ',' .
        stripcomma('LCT Amount') . ',' .
        
        
        stripcomma('Freight Amount') . ',' .
        stripcomma('Inc-Tax Freight Amount') . ',' .
        stripcomma('Freight Tax Code') . ',' .
        
        stripcomma('Freight Non-GST Amount') . ',' .
        stripcomma('Freight GST Amount') . ',' .
        stripcomma('Freight LCT Amount') . ',' .
        
        stripcomma('Sale Status') . ',' .
        stripcomma('Currency Code') . ',' .
        stripcomma('Exchange Rate') . ',' .
        
        
        stripcomma('Terms - Payment is Due').','.
        stripcomma('Discount Days').','.
        stripcomma('Balance Due Days').','.
        
        stripcomma('%Discount').','.
        stripcomma('%Monthly Charge').','.
        stripcomma('Amount Paid').','.
        stripcomma('Payment Method').','.
        stripcomma('Payment Notes').','.
        stripcomma('Name on Card').','.
        stripcomma('Card Number').','.
        stripcomma('Expiry Date').','.
        
        
        stripcomma('Authorisation Code').','.
        stripcomma('BSB').','.
        stripcomma('Account Number').','.
        stripcomma('Drawer/Account Name').','.
        stripcomma('Cheque Number').','.
        stripcomma('Category').','.
        
        
        stripcomma('Location ID').','.
        stripcomma('Card ID').','.
        stripcomma('Record ID')."\n";
 
 /**
  * Use array_multisort to Sort the InvoiceResult with orderno ascending
  */
    foreach ($InvoiceResult as $key => $row) {
            $InvNoSequence[$key]  = $row['orderno']; 
        }
        
     array_multisort($InvNoSequence, SORT_ASC, $InvoiceResult);

	While ($InvoiceList = DB_fetch_array($InvoiceResult,$db)){
            
            
    /**
     * When all associated PO are completed, then import sales invoice into MYOB 
     */        
    $SQLPOCount="select status,ref_salesorder from purchorders where ref_salesorder='".$InvoiceList['order_']."' and
                                          status<>'Cancelled'";
       
    $ErrMsg = _('No Po count were returned by the SQL because');

    $NotCancelPOList = DB_query($SQLPOCount,$db,$ErrMsg);
  
    while($NotCanPOList = DB_fetch_array($NotCancelPOList,$db)){
   
        if($NotCanPOList['status']!='Completed'){
            if($NotCanPOList['status']!='Rejected'){
            $tag++;
           
            break;
            }
        }
    }

      if($tag>0){
           $tag=0;
           continue;
        }   
        
    /**
     * Create GAP between each separate inovice
     */    

        if($InvRecordId!=$InvoiceList['orderno'] and $i!=0){
                  $CSVContent .=   "\n";
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

            
    /**
     * Output the invoice
     */    
        $salesmanName = explode(" ", $InvoiceList['salesmanname']);
        
		$CSVContent .= stripcomma($InvoiceList['name']). ',' .','.
                        stripcomma($InvoiceList['name']). ',' .
			stripcomma($InvoiceList['brpostaddr1']) . ',' .
			stripcomma($InvoiceList['brpostaddr2'].' '.$InvoiceList['brpostaddr3'].' '.$InvoiceList['brpostaddr4']) . ',' .
			stripcomma('Phone'.' '.$InvoiceList['phoneno']) . ',' .
                        stripcomma('') . ',' .
                        stripcomma('W'.$InvoiceList['order_']).','.
	
                        stripcomma(ConvertSQLDate($InvoiceList['trandate'])) .','. 
                        stripcomma($InvoiceList['customerref']).','.','.
                        stripcomma('A') . ',' .
                        stripcomma($InvoiceList['stockid']).','.
                        stripcomma($InvoiceList['quantity']).','.
                        stripcomma($InvoiceList['description']).','.
                        stripcomma($InvoiceList['fxprice']).','.
                        stripcomma($InvoiceList['fxprice']*(1+$InvoiceList['taxrate'])).','.
                        stripcomma($InvoiceList['discountpercent']*100).'%'.','.
                        stripcomma($InvoiceList['fxprice']*(1-$InvoiceList['discountpercent'])*$InvoiceList['quantity']).','.
                        stripcomma($InvoiceList['fxprice']*(1+$InvoiceList['taxrate'])*(1-$InvoiceList['discountpercent'])*$InvoiceList['quantity']).','.','.
            
                        stripcomma($InvoiceList['comments']).','.
                        stripcomma('Sales;'.$InvoiceList['brname'].' / '.$InvoiceList['contactname']).','.
                        stripcomma($salesmanName[1]).','.
                        stripcomma($salesmanName[0]) .','.','.','.
            
                        stripcomma($Taxcategory).','.
                        stripcomma(0).','.
                        stripcomma($InvoiceList['quantity']*$InvoiceList['fxprice']*(1-$InvoiceList['discountpercent'])*$InvoiceList['taxrate']).','.
                        stripcomma(0).','.
                        stripcomma($InvoiceList['ovfreight']).','.
                        stripcomma($InvoiceList['ovfreight']*(1+$InvoiceList['taxrate'])).','.
                        stripcomma($FreightTaxCode).','.
                        stripcomma(0).','.
                        stripcomma($InvoiceList['ovfreight']*$InvoiceList['taxrate']).','.
                        stripcomma(0).','.
            
            
                        stripcomma(1).','.','.','.
                        stripcomma(5).','.
                        stripcomma(1).','.
                        stripcomma(30).','.
                        stripcomma($InvoiceList['discountpercent']*100).','.
                        stripcomma(0).','.
                       //stripcomma($InvoiceList['alloc']).','.
                        stripcomma(0).','.
                        stripcomma($BankAccountName).','.','.
                        ','.','.','.','.','.','.','.','.','.
                        stripcomma($stockLocation).','.
                        stripcomma($InvoiceList['debtorno']).
            "\n";
                
              $InvRecordId=$InvoiceList['orderno'];
              $i++;
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=InvoiceList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit; 
}

elseif (isset($_POST['POlist'])){
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
                                        INNER JOIN debtortrans ON debtortrans.order_= purchorders.ref_salesorder
                                        where purchorders.status='".$_POST['POStatus']."' and purchorders.orddate between '".FormatDateForSQL($_POST['StartPODate'])."'
                                            and '".FormatDateForSQL($_POST['EndPODate'])."' and purchorderdetails.itemcode<>'S360-0000'";

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
        stripcomma('Co./Last Name') . ',' .
        stripcomma('First Name') . ',' . 
	stripcomma('Addr 1- Line 1') . ',' .
	stripcomma('Addr 1- Line 2') . ',' .
	stripcomma('Addr 1- Line 3') . ',' .
	stripcomma('Addr 1- Line 4') . ',' .
        stripcomma('Inclusive') . ',' .
        stripcomma('Purchase#') . ',' .
	stripcomma('Date') . ',' .
        stripcomma('Supplier Invoice #') . ',' .
        stripcomma('Ship Via') . ',' .   
        stripcomma('Delivery Status') . ',' .
        
        stripcomma('Item Number') . ',' .
        stripcomma('Quantity') . ',' .
        stripcomma('Description') . ',' .
        stripcomma('Price') . ',' .
        stripcomma('Inc-Tax Price') . ',' .
        stripcomma('Discount') . ',' .
        stripcomma('Total') . ',' .
        stripcomma('Inc-Tax Total') . ',' .
        stripcomma('Job') . ',' .
        stripcomma('Comment') . ',' .
        stripcomma('Journal Memo') . ',' .
        stripcomma('Shipping Date') . ',' .
        stripcomma('Tax Code') . ',' .
        stripcomma('Non-GST Amount') . ',' .
        stripcomma('GST Amount') . ',' .
        stripcomma('Import Duty Amount') . ',' .
        
        stripcomma('Freight Amount') . ',' .
        stripcomma('Inc-Tax Freight Amount') . ',' .
        stripcomma('Freight Tax Code') . ',' .
        stripcomma('Freight Non-GST Amount') . ',' .
        stripcomma('Freight GST Amount') . ',' .
        stripcomma('Freight Import Duty Amount') . ',' .
        
        stripcomma('Purchase Status') . ',' .
        stripcomma('Currency Code') . ',' .
        stripcomma('Exchange Rate') . ',' .
        stripcomma('Terms - Payment is Due').','.
        stripcomma('Discount Days').','.
        stripcomma('Balance Due').','.
        stripcomma('%Discount').','.
        stripcomma('Amount Paid').','.
        stripcomma('Category').','.
        stripcomma('Order').','.
        stripcomma('Received').','.
        stripcomma('Billed').','.
        stripcomma('Location ID').','.
        stripcomma('Card ID').','.
        stripcomma('Record ID')."\n";
   
        
 /**
  * Use array_multisort to Sort the POResult with orderno ascending
  */
   foreach ($POResult as $key => $row) {
            $PONoSequence[$key]  = $row['orderno']; 
        }
        
     array_multisort($PONoSequence, SORT_ASC, $POResult);

	While ($POList = DB_fetch_array($POResult,$db)){
      
            /**
              PO Status
            **/        
            if($POList['status']=='Pending'){
                $POStatus='P';
            }
            else{
                $POStatus='A';
            }
            if($PORecordId!=$POList['orderno'] and $i!=0){
                  $CSVContent .=   "\n";
            }
        
           /**
            * Calculate item total amount
            */
            $SqlAP="select   purchorderdetails.quantityord,
                                        purchorderdetails.quantityrecd,
                                        purchorderdetails.itemdescription,
                                        purchorderdetails.unitprice,
                                        purchorderdetails.qtyinvoiced,
                                        purchorderdetails.actprice,
                                        taxauthrates.taxrate from 
                                        purchorderdetails 
                                        INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
                                        INNER JOIN taxauthrates ON taxauthrates.taxcatid=stockmaster.taxcatid
                                        where purchorderdetails.orderno='".$POList['orderno']."' and  purchorderdetails.itemcode<>'S360-0000'";
             
            $POAP = DB_query($SqlAP,$db,'','',false,false);
            
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
//            While ($POAPList = DB_fetch_array($POAP,$db)){
//                $amountPaid+=$POAPList['qtyinvoiced']*$POAPList['actprice']*(1+$POAPList['taxrate']);
//               
//            }
            
           /**
            * Calculate Freight Total Amount
            */
               $SqlFreAP="select   purchorderdetails.quantityord,
                                        purchorderdetails.quantityrecd,
                                        purchorderdetails.itemdescription,
                                        purchorderdetails.unitprice,
                                        purchorderdetails.qtyinvoiced,
                                        purchorderdetails.actprice,
                                        taxauthrates.taxrate from 
                                        purchorderdetails 
                                        INNER JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
                                        INNER JOIN taxauthrates ON taxauthrates.taxcatid=stockmaster.taxcatid
                                        where purchorderdetails.orderno='".$POList['orderno']."' and  purchorderdetails.itemcode='S360-0000'";
             
            $POFreAP = DB_query($SqlFreAP,$db,'','',false,false);
            
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
        $FreNoIncGSTAmount=0;
        $FreIncGSTAmount=0;
        $FreGSTAmount=0;
            While ($POFreAPList = DB_fetch_array($POFreAP,$db)){
                
                $FreNoIncGSTAmount+=$POFreAPList['quantityord']*$POFreAPList['unitprice'];
                $FreIncGSTAmount+=$POFreAPList['quantityord']*$POFreAPList['unitprice']*(1+$POFreAPList['taxrate']);
                $FreGSTAmount+=$POFreAPList['quantityord']*$POFreAPList['unitprice']*$POFreAPList['taxrate'];
            }
            
           /**
            * Tax Code
            */
               if(trim($POList['taxcatname'])=="Taxable supply" or trim($POList['taxcatname'])=="Freight" ){
                    
                    $Taxcategory="GST";
                }
                elseif (trim($POList['taxcatname'])=="Exempt" or trim($POList['taxcatname'])=="CC Exempt"){
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
     * Update Stock Location
     */   

        if(trim($POList['categoryid'])=="S-CC" or trim($POList['categoryid'])=="S-Oth"){
                $stockLocation=" ";
       }
         else{
               $stockLocation="Location1";  
         }
                 
                
		$CSVContent .= stripcomma($POList['suppname']). ',' .
                        stripcomma(''). ',' .
			stripcomma($POList['deladd1']) . ',' .
			stripcomma($POList['deladd2'].' '.$POList['address3'].' '.$POList['address4']) . ',' .
			stripcomma('Phone'.' '.$POList['telephone']) . ',' .
                        stripcomma('') . ',' .
                        stripcomma('') . ',' .
			stripcomma($POList['ref_salesorder'].$POList['supplierno']) . ',' .
                        stripcomma(ConvertSQLDate($POList['trandate'])) .','. 
                        stripcomma($POList['supinvref']) .','.
                        stripcomma('') .','.
                        stripcomma($POStatus) . ',' .
            
                        stripcomma($POList['stockid']).','.
                        stripcomma($POList['quantityord']).','.
                        stripcomma($POList['itemdescription']).','.
                        stripcomma($POList['unitprice']).','.
                        stripcomma($POList['unitprice']*(1+$POList['taxrate'])).','.
                        stripcomma(round($POList['discountpercentage'],2)*100).'%'.','.
                        stripcomma($POList['quantityord']*$POList['unitprice']*(1-$POList['discountpercentage'])).','.
                        stripcomma($POList['quantityord']*$POList['unitprice']*(1+$POList['taxrate'])*(1-$POList['discountpercentage'])).','.
                        stripcomma('') .','.
                        stripcomma($POList['comments']).','.
                        stripcomma('Purchase; '.$POList['suppname']).','.
                        stripcomma('') .','.
                        stripcomma($Taxcategory) .','.
                        stripcomma(0) .','.
                        stripcomma($POList['quantityord']*$POList['unitprice']*($POList['taxrate'])*(1-$POList['discountpercentage'])).','.
                        stripcomma(0) .','.
                        stripcomma($FreNoIncGSTAmount) .','.
                        stripcomma($FreIncGSTAmount) .','.
                        stripcomma($FreightTaxCode) .','.
                        stripcomma(0) .','.
                        stripcomma($FreGSTAmount) .','.
                        stripcomma(0) .','.
                        
                        stripcomma('B') .','.
                        stripcomma('') .','.
                        stripcomma('') .','.
                        stripcomma(2) .','.
                        stripcomma(0) .','.
                        stripcomma(30) .','.
                        stripcomma(round($POList['discountpercentage'],2)*100).'%'.','.
                        stripcomma(0).','.
                        stripcomma('') .','.
                        stripcomma('') .','.
                        stripcomma($POList['quantityrecd']) .','.
                        stripcomma($POList['quantityrecd']) .','.
                        stripcomma($stockLocation) .','.
                        stripcomma($POList['supplierno']) .','.
                        stripcomma('') .','.
                     
            "\n";
                
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

elseif (isset($_POST['InvoiceRecPaylist'])){
 $SQL = "SELECT debtortrans.id,
                                        debtortrans.transno,
                                        debtortrans.inputdate AS ReceiptDate,
                                        -debtortrans.alloc AS AmountApplied,
                                        
					debtorsmaster.name,
                                        debtorsmaster.debtorno,
                                        bankaccounts.accountcode,
                                        custallocns.transid_allocto
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
                                order by debtortrans.transno";

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
	
      
 $CSVContent = 
        stripcomma('Co./Last Name') . ',' .
        stripcomma('First Name') . ',' .
	stripcomma('Deposit Account #') . ',' .
	stripcomma('ID #') . ',' .
	stripcomma('Receipt Date') . ',' .
	stripcomma('Invoice #') . ',' .
        stripcomma('Customer PO') . ',' .
        stripcomma('Invoice Date') . ',' .
        stripcomma('Amount Applied') . ',' .
        
        stripcomma('Memo') . ',' .
        stripcomma('Currency Code') . ',' .
        stripcomma('Exchange Rate') . ',' .
        stripcomma('Payment Method') . ',' .
        
        stripcomma('Payment Notes') . ',' .
        stripcomma('Name on Card') . ',' .
        stripcomma('Card Number') . ',' .
        stripcomma('Expiry Date') . ',' .
        stripcomma('Authorisation Code') . ',' .
        stripcomma('BSB') . ',' .
        stripcomma('Account Number') . ',' .
        stripcomma('Drawer/Account Name') . ',' .
        stripcomma('Cheque Number') . ',' .

        stripcomma('Card ID').','.
        stripcomma('Record ID')."\n";
 


   While ($InvoiceRecPayList = DB_fetch_array($InvoiceRecPayResult,$db)){
        

    /**
     * Map Bank account between ERP and MYOB
     */        
    $SQLInvoiceDate="select debtortrans.trandate as InvoiceDate,
                            debtortrans.order_ AS InvoiceNumber
                                          from debtortrans where 
                                          debtortrans.id='".$InvoiceRecPayList['transid_allocto']."' and
                                          debtortrans.type=10";
       
    $ErrMsg = _('No invoice date were returned by the SQL because');

    $SQLInvoiceDateList = DB_query($SQLInvoiceDate,$db,$ErrMsg);

    if ($SQLInvoiceDateList) {

			if( DB_num_rows($SQLInvoiceDateList) > 0 ) {
				$Row = DB_fetch_row($SQLInvoiceDateList);
				$InvoiceDate = $Row[0];     
                                $InvoiceNumber = $Row[1]; 
			}
			DB_free_result($SQLInvoiceDateList);
	}
        
        if($InvoiceRecPayList['accountcode']==11112){
            $accountcode='1-1112';
        }
        elseif($InvoiceRecPayList['accountcode']==11110){
            $accountcode='1-1110';
        }

            
    /**
     * Output the invoice
     */    
    
		$CSVContent .= stripcomma($InvoiceRecPayList['name']). ',' .','.
                               stripcomma($accountcode).','.','.
                               stripcomma(ConvertSQLDate($InvoiceRecPayList['ReceiptDate'])).','.
                               stripcomma('W'.$InvoiceNumber).','.','.
                               stripcomma(ConvertSQLDate($InvoiceDate)).','.
                               stripcomma($InvoiceRecPayList['AmountApplied']).','.
                               ','.','.','.','.','.','.','.','.','.','.','.','.','.
                               stripcomma($InvoiceRecPayList['debtorno']).','.
                               ','.
            "\n";
                $CSVContent .=   "\n";
          
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=InvoiceReceiptPaymentList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit; 
}


elseif (isset($_POST['POBillPaylist'])){
    
    $_SESSION['SuppTrans'] = new SuppTrans;
    
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
                               sum(purchorderdetails.qtyinvoiced*purchorderdetails.unitprice) as totalamount,
                               supptrans.trandate,
                               purchorders.ref_salesorder
                               
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
				     
                                supptrans.inputdate between '".FormatDateForSQL($_POST['StartPOBillPayDate'])."' and '".FormatDateForSQL($_POST['EndPOBillPayDate'])."'
                                GROUP BY purchorders.orderno, suppliers.supplierid   
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
        stripcomma('Co./Last Name') . ',' .
        stripcomma('First Name') . ',' .
	stripcomma('PayeeLine1') . ',' .
	stripcomma('PayeeLine2') . ',' .
	stripcomma('PayeeLine3') . ',' .
	stripcomma('PayeeLine4') . ',' .
        stripcomma('Payment Account #') . ',' .
        stripcomma('Cheque Number') . ',' .
        stripcomma('Payment Date') . ',' .
        
        stripcomma('Statement Text') . ',' .
        stripcomma('Purchase #') . ',' .
        stripcomma('Suppliers #') . ',' .
        stripcomma('Bill Date') . ',' .
        
        stripcomma('Amount Applied') . ',' .
        stripcomma('Memo') . ',' .
        stripcomma('Already Printed') . ',' .
        stripcomma('Currency Code') . ',' .
        stripcomma('Exchange Rate') . ',' .
        stripcomma('Card ID') . ',' .
        stripcomma('Record ID') . ',' .
        stripcomma('Delivery Status') . "\n";
 
 


   While ($POBillPayResultList = DB_fetch_array($POBillPayResult,$db)){
    /**
     * Calculate Tax Amount
     */ 
       
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
    $SQLPOBankPayment="SELECT banktrans.bankact,
                            banktrans.transdate
                    FROM    supptrans,
			    suppallocs,
		            banktrans
		      
		    WHERE suppallocs.transid_allocto='".$POBillPayResultList['transid_allocto']."' AND
			  supptrans.id= '".$POBillPayResultList['transid_allocfrom']."'AND
			  supptrans.type=22 AND
			  banktrans.type=22 AND
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
        
        if($PaymentAccount==11112){
            $accountcode='1-1112';
        }
        elseif($PaymentAccount==11110){
            $accountcode='1-1110';
        }
           
    /**
     * Output the invoice
     */    
    
		$CSVContent .= stripcomma($POBillPayResultList['suppname']). ',' .','.
                               stripcomma($POBillPayResultList['suppname']).','.
                               stripcomma($POBillPayResultList['address1']).','.
                               stripcomma($POBillPayResultList['address2'].' '.$POBillPayResultList['address3'].' '.$POBillPayResultList['address4']).','.','.
                               stripcomma($accountcode).','.','.
                               stripcomma(ConvertSQLDate($PaymentDate)).','.','.
                               stripcomma($POBillPayResultList['orderno'].$POBillPayResultList['supplierid']).','.','.
                               stripcomma(ConvertSQLDate($BillDate)).','.
                               stripcomma($POBillPayResultList['totalamount']*(1+$TaxRate)).','.
                               ','.','.','.','.
                               stripcomma($POBillPayResultList['supplierid']).','.','.','."\n";
                $CSVContent .=   "\n";
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
	$sql = 'SELECT sales_type, typeabbrev FROM salestypes';
	$SalesTypesResult=DB_query($sql,$db);
	echo '<tr><td>' . _('For Sales Type/Price List') . ':</td>';
	echo '<td><select name="SalesType">';
	while ($myrow=DB_fetch_array($SalesTypesResult)){
	          echo '<option Value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'];
	}
	echo '</select></td></tr>';

	$sql = 'SELECT loccode, locationname FROM locations';
	$SalesTypesResult=DB_query($sql,$db);
	echo '<tr><td>' . _('For Location') . ':</td>';
	echo '<td><select name="Location">';
	while ($myrow=DB_fetch_array($SalesTypesResult)){
	          echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
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
        
        echo '<tr><td>'._('Status of PO : ').'<select name="POStatus">';
 	echo '<option value="Completed" selected="selected">' . _('Completed') . '</option>';
	echo '<option value="Pending">' . _('Pending') . '</option>';
        echo '<option value="Authorised">' . _('Authorised') . '</option>';
        echo '<option value="Cancelled">' . _('Cancelled') . '</option>';
	echo '</select></td></tr>';
        
        echo '<tr><td>'._('Start Order Date of PO : ').'<input type=text name="StartPODate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '<tr><td>'._('End Order Date of PO : ').'<input type=text name="EndPODate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
        echo '</table>';
	echo "<div class='centre'><div class='centre'><input type='Submit' name='POlist' value='" . _('Export') . "'></div>";
	echo '</form><br />';
        
        
        // SELECT EXPORT FOR Invoice List

	echo "<br />";
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Invoice List Export') . '</th></tr>';
        
        echo '<tr><td>'._('Start Date of Invoice : ').'<input type=text name="StartInvDate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '<tr><td>'._('End Date of Invoice : ').'<input type=text name="EndInvDate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '</table>';
	echo "<div class='centre'><div class='centre'><input type='Submit' name='Invoicelist' value='" . _('Export') . "'></div>";
	echo '</form><br />';
        

        // SELECT EXPORT FOR Invoice Receipt Payment

	echo "<br />";
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan=2>' . _('Invoice Receipt Payment Export') . '</th></tr>';
        
        echo '<tr><td>'._('Start Date of Receipt : ').'<input type=text name="StartInvRePayDate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
	echo '<tr><td>'._('End Date of Receipt : ').'<input type=text name="EndInvRePayDate" maxlength="10" size="10" class=date alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . Date($_SESSION['DefaultDateFormat']) . '"></td></tr>';
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