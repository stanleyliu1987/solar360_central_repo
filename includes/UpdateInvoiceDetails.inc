<?php
include('includes/GetSalesTransGLCodes.inc');

if(isset($_POST['debtorno']) and $_POST['debtorno']!=''){
    $debtorno=$_POST['debtorno'];
}
else{
    $debtorno=$_SESSION['Items'.$identifier]->DebtorNo;
}
if(isset($_POST['branchno']) and $_POST['branchno']!=''){
    $branchno=$_POST['branchno'];
}
else{
    $branchno=$_SESSION['Items'.$identifier]->Branch;
}

 $WebSalesOrderNo=ConvertTranToInv($_SESSION['Items'.$identifier]->OrderNo,$db,$rootpath,$file);
 $invoiceid=ConvertTranToID($_SESSION['Items'.$identifier]->OrderNo,$db,$rootpath,$file);
 
 /*
  * Initialize Purchorderdetails quantity
  */
        $SQL = "UPDATE purchorderdetails, purchorders
			    SET purchorderdetails.quantityord=0
                            WHERE purchorders.orderno=purchorderdetails.orderno and  purchorders.ref_salesorder= '" .$WebSalesOrderNo. "'";
                             
	$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchorderdetails quantity could not be updated because');
	$DbgMsg = _('The following SQL to update purchorderdetails quantity was initialized');
 	$Result= DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);  

 $SQLCheckCustomer= "SELECT *   FROM custbranch
				WHERE branchcode= '".$branchno."' and
                                      debtorno= '".$debtorno."'"; 

 $ErrMsg = _('The Customer cannot be retrieved because');
 $CustomerResult = DB_query($SQLCheckCustomer,$db,$ErrMsg);
 if(DB_num_rows($CustomerResult)==0){
    prnMsg(_('Customer is not existed and cannot be Updated'),'error');
    exit;
 }   
    

/** Choose the SalesOrder Date **/
$SQLDate="SELECT salesorders.orddate						
       from salesorders,
            debtortrans
       where salesorders.orderno = debtortrans.order_ 
             AND debtortrans.transno='" . $_SESSION['Items'.$identifier]->OrderNo . "'
             AND debtortrans.type=10";
             
$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The salesorder date cannot be retrieved because');
$DbgMsg = _('The following SQL to select order date record was used');
$OrderDateResult= DB_query($SQLDate, $db,$ErrMsg,$DbgMsg,true);
$OrderDateRow = DB_fetch_array($OrderDateResult);
$OrderPlaceDate=$OrderDateRow[0];

/** Choose the Invoice Date **/
//$SQLInvDate="SELECT debtortrans.trandate						
//       from debtortrans
//       where  debtortrans.transno='" . $_SESSION['Items'.$identifier]->OrderNo . "'
//             AND debtortrans.type=10";
//             
//$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtortrans date cannot be retrieved because');
//$DbgMsg = _('The following SQL to select invoice date record was used');
//$InvoiceDateResult= DB_query($SQLInvDate, $db,$ErrMsg,$DbgMsg,true);
//$InvoiceDateRow = DB_fetch_array($InvoiceDateResult);
$InovicePlaceDate=FormatDateForSQL($_POST['DispatchDate']);

/*Now insert the mod_DebtorTrans and update debtortrans */

	$SQL = "INSERT INTO mod_debtortrans (	transno,
									type,
									debtorno,
									branchcode,
									trandate,
									inputdate,
									prd,
									reference,
									tpe,
									order_,
									ovamount,
									ovgst,
									ovfreight,
									rate,
                                                                        alloc,
									invtext,
									shipvia,
									consignment,Invoice_order )
                                                                        
             SELECT  transno,
									type,
									debtorno,
									branchcode,
									trandate,
									inputdate,
									prd,
									reference,
									tpe,
									order_,
									ovamount,
									ovgst,
									ovfreight,
									rate,
                                                                        alloc,
									invtext,
									shipvia,
									consignment,
                                                                        id
       from debtortrans where type=10 and transno= '" . $_SESSION['Items'.$identifier]->OrderNo . "'";
  

	$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
	$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
 	$Result= DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);
        $ModDebtorTransID = DB_Last_Insert_ID($db,'mod_debtortrans','id');
        
        $_SESSION['Items'.$identifier]->total=0;
   
       	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

				$Quantity = $_POST['Quantity_' . $OrderLine->LineNumber];
                                $Price = $_POST['Price_' . $OrderLine->LineNumber];
			        $Discount=$_POST['Discount_' . $OrderLine->LineNumber];
                               
                                 
                        $LineTotalWithoutDis = $Quantity*$Price;
			$LineTotal = $Quantity*$Price* (1 - $OrderLine->DiscountPercent);
			$DisplayLineTotal = number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
			$DisplayDiscount = number_format(($OrderLine->DiscountPercent * 100),2);
			$_SESSION['Items'.$identifier]->total +=  $LineTotal;
                       
                        
                        $ovgst+=$LineTotal*$_POST['TaxRate_' . $OrderLine->LineNumber]/100;
               
 
		} /* end of loop around items */
               
        
        //Total GST        
        $ovgst=$ovgst+$_POST['Freight']*$_POST['FreGST']/100;
        
        $SQL = "UPDATE debtortrans
			SET 
                            debtorno='".$debtorno."',
                            branchcode='".$branchno."',
                            trandate='".FormatDateForSQL($_POST['DispatchDate'])."',
                            ovamount ='".$_SESSION['Items'.$identifier]->total."',
                            ovgst ='".$ovgst."',
                            ovfreight='". $_POST['Freight'] ."',
                            settled=0,    
                            mod_flag = 1    
			WHERE  type=10 and transno= '" . $_SESSION['Items'.$identifier]->OrderNo . "'";

	$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The invoice status cannot be updated');
	$DbgMsg = _('The following SQL to update the invoiced was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
         
       $SQL = "Select debtortranstaxes.debtortransid, debtortranstaxes.taxauthid from debtortranstaxes, debtortrans
	       Where debtortranstaxes.debtortransid =debtortrans.id
	       and  debtortrans.type=10 and debtortrans.transno= '" . $_SESSION['Items'.$identifier]->OrderNo . "'";
       $resultTaxExistId=DB_query($SQL,$db,'','',false,false);
       $myrowid = DB_fetch_array($resultTaxExistId);
     
       if(DB_num_rows($resultTaxExistId)!=0){
          
       $SQL = "UPDATE debtortranstaxes
			SET taxamount= ".$ovgst."
			WHERE debtortransid= " . $myrowid[0] . " and taxauthid=".$myrowid[1]."";

	$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCEs') . ': ' . _('The invoice status cannot be updated');
	$DbgMsg = _('The following SQL to update the invoiced was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
       }


        
/*Now insert the mod_stockmoves */
        
       $SQL = "INSERT INTO mod_stockmoves (	stockid,
					        type,
						transno,
						loccode,
						trandate,
						debtorno,
						branchcode,
                                                price,
						prd,
						reference,
						qty,
                                                discountpercent,
						standardcost,
                                                show_on_inv_crds,
						newqoh,
                                                hidemovt,
						narrative,
                                                debtranversion)
                                                                        
             SELECT  stockid,
					        type,
						transno,
						loccode,
						trandate,
						debtorno,
						branchcode,
                                                price,
						prd,
						reference,
						qty,
                                                discountpercent,
						standardcost,
                                                show_on_inv_crds,
						newqoh,
                                                hidemovt,
						narrative,
                                                '".$ModDebtorTransID."'
                                                 
       from stockmoves where type=10 and transno= '" . $_SESSION['Items'.$identifier]->OrderNo . "'";
						

	$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The mod_stockmoves record could not be inserted because');
	$DbgMsg = _('The following SQL to insert the mod_stockmoves record was used');
 	$Result= DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);
        
        $CancelQuan=0;
        
        /*
         * Delete Original GL Info
         */
       $SQL = "DELETE from gltrans    
	       WHERE type=10 and typeno= '" . $_SESSION['Items'.$identifier]->OrderNo . "'";

	$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The invoice GL cannot be deleted');
	$DbgMsg = _('The following SQL to delete Invoice GL was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true); 
        
        /*
         * Update comment in salesorder
         */
        $SQL = "update salesorders set 
                       debtorno='".$debtorno."',
                       branchcode='".$branchno."',    
                       comments='".$_POST['Comments']."',
                       deladd1='".$_POST['DelAdd1']."',
                       deliverto='".$_POST['DelTo']."',
                       deladd2='".$_POST['DelAdd2']."',
                       deladd3='".$_POST['DelAdd3']."',
                       deladd4='".$_POST['DelAdd4']."',
                       salesorders.customerref='".$_POST['customerref']."',
                       contactphone= '".$_POST['ContactPhone']."'
	               WHERE orderno='".$_SESSION['salesorderNum']."'";

	$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The salesorder comments cannot be updated');
	$DbgMsg = _('The following SQL to update Salesorders was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true); 
        
        
        
        usort($_SESSION['Items'.$identifier]->LineItems, function($a, $b)
       {   
            return $a->MBflag < $b->MBflag;
       });

        //Update or Insert Each Product
        foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

		  $Quantity = $_POST['Quantity_' . $OrderLine->LineNumber];
                  $Price = $_POST['Price_' . $OrderLine->LineNumber];
		  $Discount=$_POST['Discount_' . $OrderLine->LineNumber]/100;
                  $Narrative=$_POST['Narrative_' . $OrderLine->LineNumber];
                  $CancelQuan+=$Quantity; 
       
         /*
         * Check Salesorder details exist or not 
         */
   $SQL = "SELECT  count(*)                                        
          from stockmoves, salesorderdetails, debtortrans, freightcostevaluation where 
          salesorderdetails.stkcode=stockmoves.stockid and
          salesorderdetails.orderno=debtortrans.order_ and
          stockmoves.transno = debtortrans.transno and
          stockmoves.type=10 and 
          stockmoves.transno= '" . $_SESSION['Items'.$identifier]->OrderNo . "'  and
          stockmoves.stockid='".$OrderLine->StockID."' and 
          freightcostevaluation.salesorder =salesorderdetails.orderno and
          freightcostevaluation.itemcode= salesorderdetails.stkcode and
          freightcostevaluation.linenumber= salesorderdetails.orderlineno";
            
   $result=DB_query($SQL,$db,'','',false,false);
    
             
	      
      if (DB_error_no($db)!=1) {
                 $myrowCount = DB_fetch_array($result);
			if ($myrowCount[0]==0){
                /**
                 * 1. Update location stock quantity
                 */  
                /* Disconnet the locstock table updates until the stock stage changed to Dispatch Stock By Stan 261102014*/            
//                  $SQL ="UPDATE locstock SET quantity=quantity-$Quantity WHERE loccode=001 and stockid='".$OrderLine->StockID."'";
//                          
//                  $ErrMsg = _('CRITICAL ERROR') . ' ' . _('CANNOT UPDATE AVAILABLE STOCK1').'';
//	          $DbgMsg = _('The following SQL to update the invoiced was used');
//	          $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);  
                  
                /**
                 * 2. Insert New stock into salesorderdetails table
                 */
                   $LineItemsSQL = "INSERT INTO salesorderdetails (
					orderlineno,
					orderno,
					stkcode,
                                        qtyinvoiced,
					unitprice,
					quantity,
					discountpercent,
                                        actualdispatchdate,
                                        completed,
					narrative,
					poline,
					itemdue,
                                        fromstkloc)
					VALUES (
					'" . $OrderLine->LineNumber . "',
					'" . $_SESSION['salesorderNum']. "',
					'" . $OrderLine->StockID. "',
                                        '" . $Quantity . "',
					'" . $Price . "',
					'" . $Quantity . "',
					'" . floatval($Discount) . "',
                                        '" . $OrderPlaceDate . "',
                                        1,
					'" . $Narrative . "',
					'" . $OrderLine->POLine . "',
					'" . FormatDateForSQL($_POST['ItemDue_'.$OrderLine->LineNumber]) . "',
                                        '".$_POST['FromStkLoc_'.$OrderLine->LineNumber]."'    
				)";
                
	          $ErrMsg = _('Unable to add the sales order line');
	          $Ins_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);   
                  $PeriodNo = GetPeriod(ConvertSQLDate($InovicePlaceDate), $db);
                /**
                 * 3. Insert New stock into stockmoves table
                 */
//                  $SQL = "INSERT INTO stockmoves (	stockid,
//					        type,
//						transno,
//						loccode,
//						trandate,
//						debtorno,
//						branchcode,
//                                                price,
//						prd,
//						reference,
//						qty,
//                                                discountpercent,
//						standardcost,
//                                                show_on_inv_crds,
//						newqoh,
//                                                hidemovt,
//						narrative)
//                                                                                                              
//             SELECT  '".$OrderLine->StockID."',
//					        type,
//						transno,
//						loccode,
//						'".$InovicePlaceDate."',
//						'".$debtorno."',
//						'".$branchno."',
//                                               '" . $Price . "',
//						prd,
//						reference,
//						'".-$Quantity."',
//                                                '".$Discount."',
//						'".$OrderLine->StandardCost."',
//                                                1,
//						newqoh,
//                                                hidemovt,
//						'".$Narrative."'
//                                    
//        from stockmoves where type=10 and transno= '" . $_SESSION['Items'.$identifier]->OrderNo . "' Limit 1";
                  
                       $SQL = "INSERT INTO stockmoves (	stockid,
					        type,
						transno,
						loccode,
						trandate,
						debtorno,
						branchcode,
                                                price,
						prd,
						reference,
						qty,
                                                discountpercent,
						standardcost,
                                                show_on_inv_crds,
						narrative)
                                                                                                              
             Values(  '".$OrderLine->StockID."',
					        10,
						'".$_SESSION['Items'.$identifier]->OrderNo."',
						'".$_POST['FromStkLoc_'.$OrderLine->LineNumber]."',
						'".$InovicePlaceDate."',
						'".$debtorno."',
						'".$branchno."',
                                                '" . $Price . "',
						'" .$PeriodNo."',
						'" .$WebSalesOrderNo."',
						'".-$Quantity."',
                                                '".$Discount."',
						'".$OrderLine->StandardCost."',
                                                1,
						'".$Narrative."' )";
                              
                  $ErrMsg = _('CRITICAL ERROR') . ' ' . _('CANNOT UPDATE AVAILABLE STOCK2').'';
	          $DbgMsg = _('The following SQL to update the invoiced was used');
	          $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);  
                  $ItemGstRate=$_POST['TaxRate_' . $OrderLine->LineNumber]/100;
                  /* Retrieve last insert id from stockmoves table */
                  $StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
                  
                  if(DB_num_rows($resultTaxExistId)!=0){
                
                  $SQL = "INSERT INTO stockmovestaxes (	stkmoveno,
					        taxauthid,
						taxrate,
						taxontax,
						taxcalculationorder)
                                                                                                              
                  SELECT  '" . $StkMoveNo . "',
					        taxauthid,
						'".$ItemGstRate."',
						taxontax,
						taxcalculationorder
                                from stockmovestaxes Limit 1";
                              
                  $ErrMsg = _('CRITICAL ERROR') . ' ' . _('CANNOT Insert a Stock Tax').'';
	          $DbgMsg = _('The following SQL to insert the invoiced was used');
	          $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);  
     
                }
                
                  /* Insert Assemble product into the stockmoves and stockmovestax table*/
                  if($OrderLine->MBflag== 'A'){
                  $SQL = "SELECT bom.component,
								bom.quantity,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard
							FROM bom,
								stockmaster
							WHERE bom.component=stockmaster.stockid
							AND bom.parent='" . $OrderLine->StockID . "'
							AND bom.effectiveto >= '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";
		
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not retrieve assembly components from the database for'). ' '. $OrderLine->StockID . _('because').' ';
				$DbgMsg = _('The SQL that failed was');
				$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				while ($AssParts = DB_fetch_array($AssResult,$db)){
                                   				/*Need to get the BOM for this part and make
				stock moves for the components then update the Location stock balances */
				$Assembly=True;
				$StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
				$SQL = "SELECT bom.component,
								bom.quantity,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard
							FROM bom,
								stockmaster
							WHERE bom.component=stockmaster.stockid
							AND bom.parent='" . $OrderLine->StockID . "'
							AND bom.effectiveto >= '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";
		
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not retrieve assembly components from the database for'). ' '. $OrderLine->StockID . _('because').' ';
				$DbgMsg = _('The SQL that failed was');
				$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				while ($AssParts = DB_fetch_array($AssResult,$db)){

					$StandardCost += ($AssParts['standard'] * $AssParts['quantity']) ;
					/* Need to get the current location quantity
					will need it later for the stock movement */
	                  		$SQL="SELECT locstock.quantity
							FROM locstock
							WHERE locstock.stockid='" . $AssParts['component'] . "'
							AND loccode= '" . $_SESSION['Items']->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Can not retrieve assembly components location stock quantities because ');
					$DbgMsg = _('The SQL that failed was');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	                  		if (DB_num_rows($Result)==1){
	                  			$LocQtyRow = DB_fetch_row($Result);
	                  			$QtyOnHandPrior = $LocQtyRow[0];
					} else {
						/*There must be some error this should never happen */
						$QtyOnHandPrior = 0;
					}
					if (empty($AssParts['standard'])) {
						$AssParts['standard']=0;
					}
					$SQL = "INSERT INTO stockmoves (	stockid,
													type,
													transno,
													loccode,
													trandate,
													debtorno,
													branchcode,
													prd,
													reference,
													qty,
													standardcost,
													show_on_inv_crds,
													newqoh) 
			       Values(  '" . $AssParts['component'] . "',
					        10,
						'".$_SESSION['Items'.$identifier]->OrderNo."',
						'".$_POST['FromStkLoc_'.$OrderLine->LineNumber]."',
						'".$InovicePlaceDate."',
						'".$debtorno."',
						'".$branchno."',
						'" .$PeriodNo."',
						'" . _('Assembly') . ': ' . $OrderLine->StockID . ' ' . _('Order') . ': ' . $_SESSION['ProcessingOrder'] . "',
                                                '" . -$AssParts['quantity'] * (-$Quantity). "',
						'" . $AssParts['standard'] . "',
						0,
						'" . ($QtyOnHandPrior - $AssParts['quantity'] * (-$Quantity)) . "' )";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for the assembly components of'). ' '. $OrderLine->StockID . ' ' . _('could not be inserted because');
					$DbgMsg = _('The following SQL to insert the assembly components stock movement records was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

/* Disconnet the locstock table updates until the stock stage changed to Dispatch Stock By Stan 261102014*/
//					$SQL = "UPDATE locstock
//							SET quantity = locstock.quantity - " . $AssParts['quantity'] * (-$Quantity) . "
//							WHERE locstock.stockid = '" . $AssParts['component'] . "'
//							AND loccode = '" . $_SESSION['Items']->Location . "'";
//
//					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated for an assembly component because');
//					$DbgMsg = _('The following SQL to update the locations stock record for the component was used');
//					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true); 
                                }      
                  }
             }
                  
             
         /*
          * Insert Freight Cost Measurements into freightcostmeasurement table
          */
                $DbgMsg = _('The Insert SQL that failed was'); 
                $LineFreightCostSQL = "INSERT INTO freightcostevaluation  ( linenumber,itemcode,
                                                                              custcode,
                                                                              salesorder,
                                                                              quantity,
                                                                              height,
                                                                              width,
                                                                              length,
                                                                              cube,
                                                                              weight,
                                                                              chargeweight,
                                                                              shipper,
                                                                              servicetype,
                                                                              freightamount,
                                                                              prefsupplier,
                                                                              suppwarehouse,
                                                                              comment )
					VALUES (
                                        '" . $OrderLine->LineNumber . "',
                                        '" . $OrderLine->StockID ."',
					'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
					'" . $WebSalesOrderNo . "',
					'" . $OrderLine->Quantity . "',
					'" . $OrderLine->ItemHeight . "',
					'" . $OrderLine->ItemWidth . "',
					'" . $OrderLine->ItemLength . "',
					'" . $OrderLine->ItemVolume . "',
                                        '" . $OrderLine->ItemWeight . "',
                                        '" . $OrderLine->ItemChargeWeight . "',    
                                        '" . $OrderLine->ItemShipper . "',
                                        '" . $OrderLine->ItemServiceType . "',    
                                        '" . $OrderLine->FreightCost . "',    
                                        '" . $OrderLine->ItemPrefSupp . "', 
                                        '" . $OrderLine->ItemSuppWare . "',     
                                        '" . $OrderLine->ItemFreComment . "'   
                                        )";
		$ErrMsg = _('Unable to add the freight cost evaluation line');
		$Ins_LineFreightCostResult = DB_query($LineFreightCostSQL,$db,$ErrMsg,$DbgMsg,true);          
      }
                            
                        
         else{
        /**
         * 1. Update location stock quantity
         */
//       $SQL ="UPDATE locstock SET quantity=quantity-$Quantity-(SELECT qty FROM stockmoves 
//              WHERE type=10 and transno= '" . $_SESSION['Items'.$identifier]->OrderNo . "'
//              and  stockid='".$OrderLine->StockID."' and price<>0 Limit 1) WHERE loccode=001 and stockid='".$OrderLine->StockID."'";
//                          
//                  $ErrMsg = _('CRITICAL ERROR') . ' ' . _('CANNOT UPDATE AVAILABLE STOCK3').'';
//	          $DbgMsg = _('The following SQL to update the invoiced was used');
//	          $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);  
        /**
         * 2. Update salesorderdetails table
         */ 
        $SQL = "UPDATE salesorderdetails
			    SET 
                            orderlineno='".$OrderLine->LineNumber."',
                            qtyinvoiced='".$Quantity."',
		            unitprice='".$Price."',
			    quantity='".$Quantity."',
                            discountpercent='".floatval($Discount)."',
                            actualdispatchdate='".$OrderPlaceDate."',
                            narrative='" . $Narrative . "',
			    poline='" . $OrderLine->POLine . "',
			    itemdue='" . FormatDateForSQL($_POST['ItemDue_'.$OrderLine->LineNumber]) . "',
                            fromstkloc='" . $_POST['FromStkLoc_'.$OrderLine->LineNumber] . "'
			    WHERE orderno= '" . $_SESSION['salesorderNum'] . "'
                             and  stkcode='".$OrderLine->StockID."' and orderlineno='".$OrderLine->LineNumber."'";
                             
	$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The salesorderdetails record could not be updated because');
	$DbgMsg = _('The following SQL to update salesorderdetails record was used');
 	$Result= DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);         
                  
        /**
         * 3. Update stockmoves table
         */           
        $SQL = "UPDATE stockmoves
			    SET 
                            debtorno='".$debtorno."',
                            branchcode='".$branchno."', 
                            loccode='".$_POST['FromStkLoc_'.$OrderLine->LineNumber]."',    
                            trandate='".$InovicePlaceDate."',
                            price='" . $Price . "',
                            qty ='".-$Quantity."',
                            discountpercent ='".$Discount."',
                            standardcost='".$OrderLine->StandardCost."'  ,
                            narrative='".$Narrative."' 
			    WHERE stkmoveno='".$OrderLine->Stockmoveid."'";

	$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stockmoves record could not be updated because');
	$DbgMsg = _('The following SQL to updated the stockmoves record was used');
 	$Result= DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);
        
        /* Update Assemble products in the stockmoves table 27112014 by Stan*/
        if($OrderLine->MBflag== 'A'){
                              $SQL = "SELECT bom.component      FROM bom,
								stockmaster
							WHERE bom.component=stockmaster.stockid
							AND bom.parent='" . $OrderLine->StockID . "'
							AND bom.effectiveto >= '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";
		
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not retrieve assembly components from the database for'). ' '. $OrderLine->StockID . _('because').' ';
				$DbgMsg = _('The SQL that failed was');
				$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			while ($AssParts = DB_fetch_array($AssResult,$db)){
                            $SQL = "UPDATE stockmoves
			    SET 
                            debtorno='".$debtorno."',
                            branchcode='".$branchno."', 
                            loccode='".$_POST['FromStkLoc_'.$OrderLine->LineNumber]."',    
                            trandate='".$InovicePlaceDate."',
                            qty ='".(-$Quantity)."' * qty,
                            discountpercent ='".$Discount."',
                            narrative='".$Narrative."' 
			    WHERE stockid='".$AssParts['component']."' and reference like CONCAT('Assembly: ','".$OrderLine->StockID."','%')";
                         
	                    $ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stockmoves record could not be updated because');
	                    $DbgMsg = _('The following SQL to updated the stockmoves record was used');
 	                    $Result= DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);                                
                       }
        }
        /* Update Item Freight Measurement records */
        
        $LineItemsSQL = "UPDATE freightcostevaluation SET custcode= '".$debtorno."',
                                                          quantity='"  . $OrderLine->Quantity  . "',
							  height='" . $OrderLine->ItemHeight . "',
							  width='" .  $OrderLine->ItemWidth . "',
							  length='" . $OrderLine->ItemLength. "',
                                                          cube='" .   $OrderLine->ItemVolume. "',
                                                          weight='" . $OrderLine->ItemWeight. "',
                                                          chargeweight='" . $OrderLine->ItemChargeWeight. "',    
                                                          shipper='" .$OrderLine->ItemShipper. "',
                                                          servicetype='" .$OrderLine->ItemServiceType. "',    
                                                          freightamount ='" .$OrderLine->FreightCost . "',
                                                          prefsupplier= '" . $OrderLine->ItemPrefSupp . "', 
                                                          suppwarehouse='" . $OrderLine->ItemSuppWare . "', 
                                                          comment='" .$OrderLine->ItemFreComment . "' 
						WHERE salesorder='" . $WebSalesOrderNo . "'
						AND itemcode='" . $OrderLine->StockID  . "'";

		$DbgMsg = _('The SQL that was used to modify the order line and failed was');
		$ErrMsg = _('The updated order line cannot be modified because');
		$Upd_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);
        
   
         }
    
  }
  
  
  
  /**
   * Get Area code
   */
  
  $SQL = "SELECT area,
					defaultshipvia
			FROM custbranch
			WHERE custbranch.debtorno ='". $_SESSION['Items'.$identifier]->DebtorNo . "'
			AND custbranch.branchcode = '" . $_SESSION['Items'.$identifier]->Branch . "'";

	$ErrMsg = _('We were unable to load Area where the Sale is to from the BRANCHES table') . '. ' . _('Please remedy this');
	$Result = DB_query($SQL,$db, $ErrMsg);
	$myrow = DB_fetch_row($Result);
	$Area = $myrow[0];
	$DefaultShipVia = $myrow[1];
	DB_free_result($Result);
        


  /**
   * Update or insert salesanalysis
   */
        
        $SalesAnalyTime=GetPeriod(Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate()), $db);
        
   	$SQL="SELECT COUNT(*),
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
					AND salesanalysis.typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
					AND salesanalysis.periodno='" . $SalesAnalyTime . "'
					AND salesanalysis.cust " . LIKE . " '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
					AND salesanalysis.custbranch " . LIKE . " '" . $_SESSION['Items'.$identifier]->Branch . "'
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
			$DbgMsg = '<br />'. _('SQL to count the no of sales analysis records');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$myrow = DB_fetch_row($Result);
           
                    if ($myrow[0]>0){  /*Update the existing record that already exists */

                            if($Quantity==0){
                                $SQL = "DELETE from salesanalysis   
			               WHERE salesanalysis.area='" . $myrow[5] . "'
					     AND salesanalysis.salesperson='" . $myrow[8] . "'
				             AND typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
					     AND periodno = '" . $SalesAnalyTime . "'
					     AND cust " . LIKE . " '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
					     AND custbranch " . LIKE . " '" .$_SESSION['Items'.$identifier]->Branch . "'
					     AND stockid " . LIKE . " '" . $OrderLine->StockID . "'
					     AND salesanalysis.stkcategory ='" . $myrow[2] . "'
					     AND budgetoractual=1";
                            }
                            else{
				$SQL = "UPDATE salesanalysis	SET  cust='".$debtorno."',
                                                                     custbranch='".$branchno."',  
                                                                     amt=" . ($Price * $Quantity/ $_SESSION['CurrencyRate']) . ",
												cost=" . ($OrderLine->StandardCost * $Quantity) . ",
												qty=" . $Quantity . ",
												disc=" . ($Discount * $Price * $Quantity / $_SESSION['CurrencyRate']) . "
								WHERE salesanalysis.area='" . $myrow[5] . "'
								AND salesanalysis.salesperson='" . $myrow[8] . "'
								AND typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
								AND periodno = '" . $SalesAnalyTime . "'
								AND cust " . LIKE . " '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
								AND custbranch " . LIKE . " '" .$_SESSION['Items'.$identifier]->Branch . "'
								AND stockid " . LIKE . " '" . $OrderLine->StockID . "'
								AND salesanalysis.stkcategory ='" . $myrow[2] . "'
								AND budgetoractual=1";
                            }

			} else { /* insert a new sales analysis record */

				$SQL = "INSERT INTO salesanalysis (typeabbrev,
												periodno,
												amt,
												cost,
												cust,
												custbranch,
												qty,
												disc,
												stockid,
												area,
												budgetoractual,
												salesperson,
												stkcategory )
								SELECT '" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
										'" . $SalesAnalyTime . "',
										'" . ($Price * $Quantity / $_SESSION['CurrencyRate']) . "',
										'" . ($OrderLine->StandardCost * $Quantity) . "',
										'" . $debtorno . "',
										'" . $branchno . "',
										'" . $Quantity . "',
										'" . ($Discount * $Price * $Quantity / $_SESSION['CurrencyRate']) . "',
										'" . $OrderLine->StockID . "',
										custbranch.area,
										1,
										custbranch.salesman,
										stockmaster.categoryid
								FROM stockmaster,
									custbranch
								WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
								AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
								AND custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'";
			}

			$ErrMsg = _('Sales analysis record could not be added or updated because');
			$DbgMsg = _('The following SQL to insert the sales analysis record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                     
/**
 * Start to insert Data Into GL
 */
  
  	if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $OrderLine->StandardCost !=0 AND $Quantity!=0){

				$SQL = "INSERT INTO gltrans (  type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (
										10,
										'" . $_SESSION['Items'.$identifier]->OrderNo. "',
										'" . $InovicePlaceDate . "',
										'" . $PeriodNo . "',
										'" . GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db) . "',
										'" . $debtorno . " - " . $OrderLine->StockID . " x " . $Quantity . " @ " . $OrderLine->StandardCost . "',
										'" . $OrderLine->StandardCost * $Quantity . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                
                                $StockGLCode = GetStockGLCode($OrderLine->StockID,$db);

				$SQL = "INSERT INTO gltrans (	type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (
										10,
										'" .  $_SESSION['Items'.$identifier]->OrderNo . "',
										'" . $InovicePlaceDate . "',
										'" . $PeriodNo . "',
										'" . $StockGLCode['stockact'] . "',
										'" . $debtorno . " - " . $OrderLine->StockID . " x " . $Quantity . " @ " . $OrderLine->StandardCost. "',
										'" . (-$OrderLine->StandardCost * $Quantity ) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the cost of sales GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                
                             
        }
       
        
        	if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $OrderLine->Price !=0 AND $OrderLine->StandardCost !=0 AND $Quantity!=0){

					//Post sales transaction to GL credit sales
					$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);

					$SQL = "INSERT INTO gltrans (  type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount )
										VALUES (
											10,
											'" . $_SESSION['Items'.$identifier]->OrderNo . "',
											'" . $InovicePlaceDate . "',
											'" . $PeriodNo . "',
											'" . $SalesGLAccounts['salesglcode'] . "',
											'" . $debtorno . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->Price . "',
											'" . (-$OrderLine->Price * $Quantity/$_SESSION['CurrencyRate']) . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales GL posting could not be inserted because');
					$DbgMsg = '<br />' ._('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
        
        	                    if ($OrderLine->DiscountPercent !=0){

						$SQL = "INSERT INTO gltrans ( type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount)
												VALUES (
													10,
													'" . $_SESSION['Items'.$identifier]->OrderNo . "',
													'" . $InovicePlaceDate . "',
													'" . $PeriodNo . "',
													'" . $SalesGLAccounts['discountglcode'] . "',
													'" . $debtorno . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%',
													'" . ($OrderLine->Price * $Quantity * $OrderLine->DiscountPercent/$_SESSION['CurrencyRate']) . "')";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales discount GL posting could not be inserted because');
						$DbgMsg = _('The following SQL to insert the GLTrans record was used');
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					}
                
                                        
                }
                
       
      /*
       * Synchronise with Related Purchase Order
       */
                

      /*
       * A1. Retrieve Related PO and Check Items Exist in Current Purchase Order, and Items not belongs to Assembly
       */
      if($OrderLine->MBflag== 'B') 
      { 
       

       $SQLCheckPOItem = " SELECT  count(*) from purchorders
                         inner join purchorderdetails on purchorders.orderno=purchorderdetails.orderno
                         where purchorders.ref_salesorder= '" .$WebSalesOrderNo. "'  and
                         purchorderdetails.itemcode='".$OrderLine->StockID."'";
            
       $resultPOItem=DB_query($SQLCheckPOItem,$db,'','',false,false);   
       
       if (DB_error_no($db) !=0) {

		echo '<p>' . _('The Related PO could not be retrieved by the SQL because') . ' ' . DB_error_msg($db);
		echo '<br /><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}

		exit;
	}
        else{
             $myrowCount = DB_fetch_array($resultPOItem); 
	     if ($myrowCount[0]==1){ 
      /*
       * A2. If Item exists, update its details
       */        
        $SQL = "UPDATE purchorderdetails, purchorders
			    SET 
                            purchorderdetails.quantityord='".$Quantity."',
                            purchorderdetails.narrative='".$Narrative."',    
                            purchorders.deladd1='".$_POST['DelAdd1']."',
                            purchorders.deladd2='".$_POST['DelAdd2']."',
                            purchorders.deladd3='".$_POST['DelAdd3']."',
                            purchorders.deladd4='".$_POST['DelAdd4']."',
                            purchorders.tel='".$_POST['ContactPhone']."',
                            purchorders.contact='".$_POST['DelTo']."'  
			    WHERE purchorders.orderno=purchorderdetails.orderno
                            and  purchorderdetails.itemcode= '" . $OrderLine->StockID . "'
                            and  purchorders.ref_salesorder= '" .$WebSalesOrderNo. "'";
                             
	$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchorderdetails record could not be updated because');
	$DbgMsg = _('The following SQL to update purchorderdetails record was used');
 	$Result= DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);       
            }
             
             else{
      /*
       * A3. If Item not exists, check whether it belongs to existed supplier
       */
        $SQLCheckPOSupp = " SELECT  purchdata.supplierno,
                                    purchorders.orderno
                                    from purchorders
                                    inner join purchdata on purchorders.supplierno=purchdata.supplierno
                                    where purchorders.ref_salesorder= '" . $WebSalesOrderNo. "'  
                                    and purchdata.stockid='".$OrderLine->StockID."'
                                    and purchdata.preferred=1";
            
       $resultPOSupp=DB_query($SQLCheckPOSupp,$db,'','',false,false);          
       if (DB_error_no($db) !=0) {

		echo '<p>' . _('The Related Supplier could not be retrieved by the SQL because') . ' ' . DB_error_msg($db);
		echo '<br /><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}

		exit;
	}
        
        else{
             $myrowCount = DB_fetch_array($resultPOSupp);
	     if (DB_num_rows($resultPOSupp)==1){  
      /*
       * A4. If supplier exists, then add new item line
       */  
             $sql = "SELECT description,
			    stockid,
			    units,
			    decimalplaces,
			    stockact,
			    accountname
			    FROM stockmaster INNER JOIN stockcategory
					ON stockcategory.categoryid = stockmaster.categoryid
					INNER JOIN chartmaster
					ON chartmaster.accountcode = stockcategory.stockact
					WHERE  stockmaster.stockid = '". $OrderLine->StockID . "'";

	     $ErrMsg = _('The item details for') . ' ' . $OrderLine->StockID . ' ' . _('could not be retrieved because');
	     $DbgMsg = _('The SQL used to retrieve the item details but failed was');
	     $ItemResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
                                
	if (DB_num_rows($ItemResult)==1){
	     
             $ItemRow = DB_fetch_array($ItemResult);			
	     $sql = "SELECT price,
			    conversionfactor,
		            supplierdescription,
			    suppliersuom,
			    suppliers_partno,
			    leadtime,
			    MAX(purchdata.effectivefrom) AS latesteffectivefrom
			                FROM purchdata 
					WHERE purchdata.supplierno = '" . $myrowCount[0] . "'
					AND purchdata.effectivefrom <='" . Date('Y-m-d') . "'
					AND purchdata.stockid = '". $OrderLine->StockID . "'
					GROUP BY 	purchdata.price,
								purchdata.conversionfactor,
								purchdata.supplierdescription,
								purchdata.suppliersuom,
								purchdata.suppliers_partno,
								purchdata.leadtime";
												
				 $ErrMsg = _('The purchasing data for') . ' ' . $OrderLine->StockID . ' ' . _('could not be retrieved because');
				 $DbgMsg = _('The SQL used to retrieve the purchasing data but failed was');
				 $PurchDataResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				
                                 if (DB_num_rows($PurchDataResult)==1){ //the purchasing data is set up
					$PurchRow = DB_fetch_array($PurchDataResult);
					$PurchPrice = $PurchRow['price']/$PurchRow['conversionfactor'];
					$ConversionFactor = $PurchRow['conversionfactor'];
					$SupplierDescription = '';
				 if (strlen($PurchRow['supplierdescription'])>2){
					$SupplierDescription .= $PurchRow['supplierdescription'];
					} 
                                 else  {
					$SupplierDescription .= $ItemRow['description'];
					}
					$SuppliersUnitOfMeasure = $PurchRow['suppliersuom'];
					$SuppliersPartNo = $PurchRow['suppliers_partno'];
					$LeadTime = $PurchRow['leadtime'];
				} else { // no purchasing data setup
					$PurchPrice = 0; 
					$ConversionFactor = 1;
					$SupplierDescription = 	$ItemRow['description'];
					$SuppliersUnitOfMeasure = $ItemRow['unitname'];
					$SuppliersPartNo = '';
				}  
                                
                                
             $sql = "INSERT INTO purchorderdetails ( orderno,
						     itemcode,
						     deliverydate,
						     itemdescription,
                                                     glcode,
						     unitprice,
						     quantityord,
						     shiptref,
					             jobref,
						     suppliersunit,
						     suppliers_partno,
						     assetid,
						     conversionfactor,
                                                     narrative)
				VALUES (
					'" . $myrowCount[1] . "',
					'" . $OrderLine->StockID . "',
					'" . Date('Y-m-d') . "',
					'" . $SupplierDescription. "',
					'" . $ItemRow['stockact'] . "',
					'" . $PurchPrice . "',
					'" . $Quantity . "',
					0,
					0,
					'" . $SuppliersUnitOfMeasure . "',
					'" . $SuppliersPartNo . "',
					0,
					'" . $ConversionFactor . "',
                                        '" . $Narrative . "'    )";
			$ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
			$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
			$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
                        
             }
             else { //no rows returned by the SQL to get the item
		prnMsg (_('The item code') . ' ' . $ItemCode . ' ' . _('does not exist in the database and therefore cannot be added to the Purchase order'),'error');
		if ($debug==1){
			echo '<br / >'.$sql;
			}
		include('includes/footer.inc');
		exit;
	  } 
      }  
          
         elseif(DB_num_rows($resultPOSupp)==0){  
      /*
       * A5. If supplier not exists, then create new PO
       */        
       $SQLSelectSup = " SELECT  purchdata.supplierno from purchdata
                           where  purchdata.stockid='".$OrderLine->StockID."'
                           and purchdata.preferred=1";
            
       $resultSelectSup=DB_query($SQLSelectSup,$db,'','',false,false);
       if (DB_error_no($db) !=0 or DB_num_rows($resultSelectSup)!=1) {

		echo '<p>' . _('The Related Supplier could not be retrieved or more than one or 0 prefreed supplier selected') . ' ' . DB_error_msg($db);
		echo '<br /><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}

		exit;
                
        }
        else{         
            
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
                                AND stockmaster.stockid='".$OrderLine->StockID."'
				AND salesorders.orderno='". $WebSalesOrderNo."'
				ORDER BY purchdata.supplierno,
					 purchdata.stockid";
                        $ItemResult = DB_query($sql,$db,$ErrMsg);
                        $ItemRow = DB_fetch_array($ItemResult);
                  
                        $UserDetails  = ' ' . $_SESSION['UsersRealName'] . ' ';
                        
                        $TransactionStart = DB_Txn_Begin($db);
                        
                        $PO_OrderNo = GetNextTransNo(18, $db); //get the next PO number
                        $myrowSelectSup = DB_fetch_row($resultSelectSup);
                        $SupplierID = $myrowSelectSup[0];
                        $Order_Value = 0;
                       
                        /* Now get all the required details for the supplier */
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

                        $StatusComment = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order Created by') . ' ' . $UserDetails . ' - ' . _('Auto created from sales orders') . '<br />';
                        /* Insert to purchase order header record */
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
                                                                                                '" . $WebSalesOrderNo . "',
                                                                                                '" . 'W' . $WebSalesOrderNo . $SupplierID . $PO_OrderNo . "'  )";

                        $ErrMsg = _('The purchase order header record could not be inserted into the database because');
                        $DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
                        $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                        
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
                    						     '" . $Quantity . "',
                    						     '" . $ItemRow['suppliersuom'] . "',
                    						     '" . $ItemRow['suppliers_partno'] . "',
                    						     '" . $ItemRow['conversionfactor']  . "')";
				$ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
				$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');

				$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);  
                                
                                 if (DB_error_no($db) ==0){
                                     echo '<p>';
				     prnMsg(_('Purchase Order') . ' ' . $PO_OrderNo . ' ' . _('on') . ' ' . $SupplierID . ' ' . _('has been created'),'success');
				     DB_Txn_Commit($db);
                                 }
                           
                    }
             
                }
        else{
             echo '<p>';
             prnMsg(_('Stock Item ') . ' ' . $OrderLine->StockID . ' ' . _(' exists in multiple PO(s), so the PO will not updated, please check the related Purchase Orders.'),'WARNING'); 
        }        
            }
         }
       }
    }
    
    /*
     * B1. Item belongs to Accessary Item and Retrieve sub-items from BOM Item
     */
     elseif($OrderLine->MBflag== 'A'){
    
       $SQLRetrieveSubItem = " SELECT * from bom where parent= '" .$OrderLine->StockID. "'";
            
       $resultSubItems=DB_query($SQLRetrieveSubItem,$db,'','',false,false); 
       
       if (DB_error_no($db) !=0) {

		echo '<p>' . _('The Related BOM sub itmes could not be retrieved by the SQL because') . ' ' . DB_error_msg($db);
		echo '<br /><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}

		exit;
	}
        else{
          
           while ($subrow=db_fetch_array($resultSubItems)) {
    /*
     * B2. Retrieve Related PO and Check Items Exist in Current Purchase Order
     */
         //array_push($BOMExistProList, $subrow['component']);  
         
         $SQLCheckPOItem = " SELECT  count(*) from purchorders
                         inner join purchorderdetails on purchorders.orderno=purchorderdetails.orderno
                         where purchorders.ref_salesorder= '" .$WebSalesOrderNo. "'  and
                         purchorderdetails.itemcode='".$subrow['component']."'";
            
        $resultPOItem=DB_query($SQLCheckPOItem,$db,'','',false,false);   
       
       if (DB_error_no($db) !=0) {

		echo '<p>' . _('The Related PO could not be retrieved by the SQL because') . ' ' . DB_error_msg($db);
		echo '<br /><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}

		exit;
	}
        else{
             $myrowCount = DB_fetch_array($resultPOItem); 
	     if ($myrowCount[0]==1){ 
    /*
     * B3. If Item exists, update its details
     */ 

        $updatequantity="purchorderdetails.quantityord+'".$Quantity*$subrow['quantity']."'";
         
        $SQL = "UPDATE purchorderdetails, purchorders
			    SET 
                            purchorderdetails.quantityord=".$updatequantity.",
                            purchorderdetails.narrative='".$Narrative."',    
                            purchorders.deladd1='".$_POST['DelAdd1']."',
                            purchorders.deladd2='".$_POST['DelAdd2']."',
                            purchorders.deladd3='".$_POST['DelAdd3']."',
                            purchorders.deladd4='".$_POST['DelAdd4']."',
                            purchorders.tel='".$_POST['ContactPhone']."',
                            purchorders.contact='".$_POST['DelTo']."'  
			    WHERE purchorders.orderno=purchorderdetails.orderno
                            and  purchorderdetails.itemcode= '" . $subrow['component'] . "'
                            and  purchorders.ref_salesorder= '" .$WebSalesOrderNo. "'";
                             
	$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The purchorderdetails record could not be updated because');
	$DbgMsg = _('The following SQL to update purchorderdetails record was used');
 	$Result= DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);       
            }
             else{
      /*
       * B4. If Item not exists, check whether it belongs to existed supplier
       */
        $SQLCheckPOSupp = " SELECT  purchdata.supplierno,
                                    purchorders.orderno
                                    from purchorders
                                    inner join purchdata on purchorders.supplierno=purchdata.supplierno
                                    where purchorders.ref_salesorder= '" . $WebSalesOrderNo. "'  
                                    and purchdata.stockid='".$subrow['component']."'
                                    and purchdata.preferred=1
                                    ";
            
       $resultPOSupp=DB_query($SQLCheckPOSupp,$db,'','',false,false);          
       if (DB_error_no($db) !=0) {

		echo '<p>' . _('The Related Supplier could not be retrieved by the SQL because') . ' ' . DB_error_msg($db);
		echo '<br /><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}

		exit;
	}
        
        else{
             $myrowCount = DB_fetch_array($resultPOSupp);
	     if (DB_num_rows($resultPOSupp)==1){  
      /*
       * B5. If supplier exists, then add new item line
       */  
             $sql = "SELECT description,
			    stockid,
			    units,
			    decimalplaces,
			    stockact,
			    accountname
			    FROM stockmaster INNER JOIN stockcategory
					ON stockcategory.categoryid = stockmaster.categoryid
					INNER JOIN chartmaster
					ON chartmaster.accountcode = stockcategory.stockact
					WHERE  stockmaster.stockid = '". $subrow['component'] . "'";

	     $ErrMsg = _('The item details for') . ' ' . $subrow['component'] . ' ' . _('could not be retrieved because');
	     $DbgMsg = _('The SQL used to retrieve the item details but failed was');
	     $ItemResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
                                
	if (DB_num_rows($ItemResult)==1){
	     
             $ItemRow = DB_fetch_array($ItemResult);			
	     $sql = "SELECT price,
			    conversionfactor,
		            supplierdescription,
			    suppliersuom,
			    suppliers_partno,
			    leadtime,
			    MAX(purchdata.effectivefrom) AS latesteffectivefrom
			                FROM purchdata 
					WHERE purchdata.supplierno = '" . $myrowCount[0] . "'
					AND purchdata.effectivefrom <='" . Date('Y-m-d') . "'
					AND purchdata.stockid = '". $subrow['component'] . "'
					GROUP BY 	purchdata.price,
								purchdata.conversionfactor,
								purchdata.supplierdescription,
								purchdata.suppliersuom,
								purchdata.suppliers_partno,
								purchdata.leadtime";
												
				 $ErrMsg = _('The purchasing data for') . ' ' . $subrow['component'] . ' ' . _('could not be retrieved because');
				 $DbgMsg = _('The SQL used to retrieve the purchasing data but failed was');
				 $PurchDataResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				
                                 if (DB_num_rows($PurchDataResult)==1){ //the purchasing data is set up
					$PurchRow = DB_fetch_array($PurchDataResult);
					$PurchPrice = $PurchRow['price']/$PurchRow['conversionfactor'];
					$ConversionFactor = $PurchRow['conversionfactor'];
					$SupplierDescription = '';
				 if (strlen($PurchRow['supplierdescription'])>2){
					$SupplierDescription .= $PurchRow['supplierdescription'];
					} 
                                 else  {
					$SupplierDescription .= $ItemRow['description'];
					}
					$SuppliersUnitOfMeasure = $PurchRow['suppliersuom'];
					$SuppliersPartNo = $PurchRow['suppliers_partno'];
					$LeadTime = $PurchRow['leadtime'];
				} else { // no purchasing data setup
					$PurchPrice = 0; 
					$ConversionFactor = 1;
					$SupplierDescription = 	$ItemRow['description'];
					$SuppliersUnitOfMeasure = $ItemRow['unitname'];
					$SuppliersPartNo = '';
				}  
                                
                                
             $sql = "INSERT INTO purchorderdetails ( orderno,
						     itemcode,
						     deliverydate,
						     itemdescription,
                                                     glcode,
						     unitprice,
						     quantityord,
						     shiptref,
					             jobref,
						     suppliersunit,
						     suppliers_partno,
						     assetid,
						     conversionfactor,
                                                     narrative)
				VALUES (
					'" . $myrowCount[1] . "',
					'" . $subrow['component'] . "',
					'" . Date('Y-m-d') . "',
					'" . $SupplierDescription. "',
					'" . $ItemRow['stockact'] . "',
					'" . $PurchPrice . "',
					'" . $Quantity*$subrow['quantity']. "',
					0,
					0,
					'" . $SuppliersUnitOfMeasure . "',
					'" . $SuppliersPartNo . "',
					0,
					'" . $ConversionFactor . "',
                                        '" . $Narrative . "'    )";
			$ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
			$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
			$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
                        
             }
             else { //no rows returned by the SQL to get the item
		prnMsg (_('The item code') . ' ' . $ItemCode . ' ' . _('does not exist in the database and therefore cannot be added to the Purchase order'),'error');
		if ($debug==1){
			echo '<br / >'.$sql;
			}
		include('includes/footer.inc');
		exit;
	       } 
            }
            
      elseif(DB_num_rows($resultPOSupp)==0){  
    /*
     * B5. If supplier not exists, then create new PO
     */        
       $SQLSelectSup = " SELECT  purchdata.supplierno from purchdata
                           where  purchdata.stockid='".$subrow['component']."'
                           and purchdata.preferred=1";
            
       $resultSelectSup=DB_query($SQLSelectSup,$db,'','',false,false);
       if (DB_error_no($db) !=0 or DB_num_rows($resultSelectSup)!=1) {

		echo '<p>' . _('The Related Supplier could not be retrieved or more than one or 0 prefreed supplier selected') . ' ' . DB_error_msg($db);
		echo '<br /><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}

		exit;
                
        }
        else{         
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
                                AND stockmaster.stockid='".$subrow['component']."'
				AND salesorders.orderno='". $WebSalesOrderNo."'
				ORDER BY purchdata.supplierno,
					 purchdata.stockid";
       
       
                        $ItemResult = DB_query($sql,$db,$ErrMsg);
                        $ItemRow = DB_fetch_array($ItemResult);
                  
                        $UserDetails  = ' ' . $_SESSION['UsersRealName'] . ' ';
                        
                        $TransactionStart = DB_Txn_Begin($db);
                        
                        $PO_OrderNo = GetNextTransNo(18, $db); //get the next PO number
                        $myrowSelectSup = DB_fetch_row($resultSelectSup);
                        $SupplierID = $myrowSelectSup[0];
                        $Order_Value = 0;
                       
                        /* Now get all the required details for the supplier */
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

                        $StatusComment = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order Created by') . ' ' . $UserDetails . ' - ' . _('Auto created from sales orders') . '<br />';
                        /* Insert to purchase order header record */
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
                                                                                                '" . $WebSalesOrderNo . "',
                                                                                                '" . 'W' . $WebSalesOrderNo . $SupplierID . $PO_OrderNo . "'  )";

                        $ErrMsg = _('The purchase order header record could not be inserted into the database because');
                        $DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
                        $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                        
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
                    						     '" . $Quantity*$subrow['quantity'] . "',
                    						     '" . $ItemRow['suppliersuom'] . "',
                    						     '" . $ItemRow['suppliers_partno'] . "',
                    						     '" . $ItemRow['conversionfactor']  . "')";
				$ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
				$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');

				$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);  
                                
                                 if (DB_error_no($db) ==0){
                                     echo '<p>';
				     prnMsg(_('Purchase Order') . ' ' . $PO_OrderNo . ' ' . _('on') . ' ' . $SupplierID . ' ' . _('has been created'),'success');
				     DB_Txn_Commit($db);
                          }
                           
                       }
             
                   }
        else{
             echo '<p>';
             prnMsg(_('Stock Item ') . ' ' . $subrow['component']. ' ' . _(' exists in multiple PO(s), so the PO will not updated, please check the related Purchase Orders.'),'WARNING'); 
        }
             }
        }
    }
} 
        }
    }
}
        
       
      /*
       * 6. If all itmes quantity in PO is 0, then Cancel that PO,
       *    otherwise delete qty equal 0's item from Purchaseorderdetails
       *    Table
       */           
       $SQLCheckPO = " SELECT  purchorders.orderno from purchorders
                       where purchorders.ref_salesorder= '" . $WebSalesOrderNo. "' and
                             purchorders.status<>'Cancelled'";
        
       $resultPO=DB_query($SQLCheckPO,$db,'','',false,false);          
       if (DB_error_no($db) !=0) {

		echo '<p>' . _('The Related PO Item  could not be retrieved by the SQL because') . ' ' . DB_error_msg($db);
		echo '<br /><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}

		exit;
	} 
        else{
       
            while ($myrow=db_fetch_array($resultPO)) {
     
               $SQLCheckPOItemQty = " SELECT * from purchorders
                                      inner join purchorderdetails on purchorders.orderno=purchorderdetails.orderno
                                      where purchorders.orderno= '" . $myrow['orderno']. "'  and
                                      purchorderdetails.quantityord<>0";
               $resultPOItemQty=DB_query($SQLCheckPOItemQty,$db,'','',false,false);   
             /*
              *  a. All Items Has been Deleted, PO needs to be Cancelled
              */    
            if (DB_num_rows($resultPOItemQty)==0){ 
                   $SQL = "UPDATE purchorders SET status='"._('Cancelled')."',
					          allowprint=0
                                              where orderno='".$myrow['orderno']."'";               
	           $ErrMsg =_('The purchorder status cannot update to cancelled because');
	           $DelResult=DB_query($SQL,$db,$ErrMsg);
                   if (DB_error_no($db) ==0){
                    echo '<p>';
		    prnMsg(_('Purchase Order') . ' ' . $myrow['orderno']. ' '  . _('has been cancelled'),'info');
	         }

             }
             /*
              *  b. Some Items Qty not 0, then delete Items which qty is 0
              */
             
             else{
                  $SQL = "DELETE from purchorderdetails  
			  WHERE orderno='".$myrow['orderno']."'
                          and quantityord=0";

	          $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Purchorderdetails cannot be deleted');
	          $DbgMsg = _('The following SQL to delete purchorderdetails was used');
	          $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true); 
               
             }
          }
        }   
 
/**
 * Insert the AR, Freight and GST Payable into GL
 */

	if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $Quantity!=0){

/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
		if (($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $ovgst) !=0) {
			$SQL = "INSERT INTO gltrans (	type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
									VALUES (
										10,
										'" . $_SESSION['Items'.$identifier]->OrderNo . "',
										'" . $InovicePlaceDate . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
										'" . $debtorno . "',
										'" . (($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $ovgst)/$_SESSION['CurrencyRate']) . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The total debtor GL posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the total debtors control GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}

		/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */

		if ($_SESSION['Items'.$identifier]->FreightCost !=0) {
			$SQL = "INSERT INTO gltrans (
						type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount	)
				VALUES (
					10,
					'" . $_SESSION['Items'.$identifier]->OrderNo . "',
					'" . $InovicePlaceDate . "',
					'" . $PeriodNo . "',
					'" . $_SESSION['CompanyRecord']['freightact'] . "',
					'" . $debtorno . "',
					'" . (-($_SESSION['Items'.$identifier]->FreightCost)/$_SESSION['CurrencyRate']) . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freight GL posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
             
		
			if ($ovgst !=0 ){
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
										VALUES (
											10,
											'" . $_SESSION['Items'.$identifier]->OrderNo . "',
											'" . $InovicePlaceDate . "',
											'" . $PeriodNo . "',
											'" . 2300 . "',
											'" . $debtorno . "',
											'" . (-$ovgst/$_SESSION['CurrencyRate']) . "')";
					
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The tax GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
		}
	}
        //End Foreach loop
        
        /**
         * 1. Delete qty equals 0 from salesorder details table
         */
        $SQL = "DELETE from salesorderdetails    
			WHERE orderno= '" . $_SESSION['salesorderNum'] . "'
                        and qtyinvoiced=0 and quantity=0";

	$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The salesorderdetails status cannot be updated');
	$DbgMsg = _('The following SQL to delete salesorderdetails was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true); 
        
        /**
         * 2. Re-arrange Order Line Number
         */
              
        $SQL = "SELECT * from salesorderdetails where orderno= '" .  $WebSalesOrderNo . "' order by orderlineno asc";
        $result=DB_query($SQL,$db,'','',false,false);
        $NewOrderLineNO=0;
        while ($row=db_fetch_array($result)) {
        $SQLLineNumber = "Update salesorderdetails set orderlineno='".$NewOrderLineNO."' 
                          where orderlineno='".$row['orderlineno'] ."' and orderno= '".$row['orderno'] ."'";                        
	$ErrMsg =_('The salesorderline number lines could not be update because');
	$DelResult=DB_query($SQLLineNumber,$db,$ErrMsg); 
        $NewOrderLineNO++;
         }
        
        /**
         * 3. Delete qty equals 0 from stockmoves table and stockmovestaxe table
         */
        $SQL = "DELETE stockmoves, stockmovestaxes FROM stockmoves INNER JOIN stockmovestaxes ON
                      stockmoves.stkmoveno= stockmovestaxes.stkmoveno WHERE 
                      stockmoves.type=10 AND 
                      stockmoves.transno= '" . $_SESSION['Items'.$identifier]->OrderNo . "' AND stockmoves.qty=0";

	$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stockmoves or stockmovestaxes status cannot be deleted');
	$DbgMsg = _('The following SQL to delete the invoiced was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true); 
        
        /* 4. Delete qty equals 0 from stckmoves table for the assembl components */
        $SQL = "DELETE FROM stockmoves WHERE stockmoves.type=10 AND 
                      stockmoves.transno= '" . $_SESSION['Items'.$identifier]->OrderNo . "' AND stockmoves.qty=0";

	$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stockmoves status cannot be deleted');
	$DbgMsg = _('The following SQL to delete the invoiced was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true); 
        
  
        /**
         * 1. Delete qty equals 0 from freightcostevaluation table
         */
        $SQL = "DELETE from freightcostevaluation  
			WHERE salesorder= '" . $_SESSION['salesorderNum'] . "'
                        and quantity=0";

	$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freightcostvaluation cannot be deleted');
	$DbgMsg = _('The following SQL to delete freightcostevaluation was used');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true); 
        
        /**
         * 2. Re-arrange freight cost value Line Number
         */
              
        $SQL = "SELECT * from freightcostevaluation where salesorder= '" .  $WebSalesOrderNo . "' order by linenumber asc";
        $result=DB_query($SQL,$db,'','',false,false);
        $NewOrderLineNO=0;
        while ($row=db_fetch_array($result)) {
        $SQLLineNumber = "Update freightcostevaluation set linenumber='".$NewOrderLineNO."' 
                          where linenumber='".$row['linenumber'] ."' and salesorder= '".$row['salesorder'] ."'";                        
	$ErrMsg =_('The freightcostevaluation number lines could not be update because');
	$DelResult=DB_query($SQLLineNumber,$db,$ErrMsg); 
        $NewOrderLineNO++;
         }
         
         
        if($CancelQuan==0){
            $SQL = "Update debtortrans set mod_flag=2,debtortrans.order_stages=5 where transno ='" . $_SESSION['ExistingOrder'] . "' and type=10";
                                    
	    $ErrMsg =_('The invoice detail lines could not be deleted because');
	    $DelResult=DB_query($SQL,$db,$ErrMsg);
/* 01072014 Insert Initial Status of each order into order stages messages table By Stan*/
        $orderstage=new OrderStagesMessageModel($db);
        $orderstagebean=new OrderStagesMessageBean();
        $orderstagebean->changedatetime=date('Y-m-d H:i:s');
        $orderstagebean->debtortranid=$invoiceid;
        $orderstagebean->orderstagechange=5;
        $orderstagebean->userid=$_SESSION['UserID'];
        $orderstage->SaveOrderStagesMessage($orderstagebean);         
/* Insert the tax totals for each tax authority where tax was charged on the invoice */
            $_SESSION['ExistingOrder']=0;
        }
        
        unset($_SESSION['TotalFreight']);
        prnMsg(_('Invoice '.$_SESSION['salesorderNum'].' has been Updated'),'success');
        //$redirectpage=$rootpath .'/PrintCustTransPortrait.php?InvoiceNo='.strip_tags($_SESSION['salesorderNum']);
        //echo '<meta http-equiv="Refresh" content="1; url=' . $redirectpage. '">';
	include('includes/footer.inc');
	exit;

?>
