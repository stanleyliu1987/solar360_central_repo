<?php

/* $Id: DefineCartClass.php 4560 2011-05-02 10:33:55Z daintree $*/

/* Definition of the cart class
this class can hold all the information for:

i)   a sales order
ii)  an invoice
iii) a credit note

*/


Class Cart {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $total; /*total cost of the items ordered */
	var $totalVolume;
	var $totalWeight;
	var $LineCounter;
	var $ItemsOrdered; /*no of different line items ordered */
	var $DeliveryDate;
	var $DefaultSalesType;
	var $SalesTypeName;
	var $DefaultCurrency;
	var $CurrDecimalPlaces;
	var $PaymentTerms;
	var $DeliverTo;
	var $DelAdd1;
	var $DelAdd2;
	var $DelAdd3;
	var $DelAdd4;
	var $DelAdd5;
	var $DelAdd6;
	var $PhoneNo;
	var $Email;
	var $CustRef;
	var $Comments;
	var $Location;
	var $LocationName;
	var $DebtorNo;
	var $CustomerName;
	var $Orig_OrderDate;
	var $Branch;
	var $TransID;
	var $ShipVia;
	var $FreightTaxes;
	Var $OrderNo;
	Var $Consignment;
	Var $Quotation;
	Var $DeliverBlind;
	Var $CreditAvailable; //in customer currency
	Var $TaxGroup;
	Var $DispatchTaxProvince;
	VAR $vtigerProductID;
	Var $DefaultPOLine;
	Var $DeliveryDays;
	var $TaxTotals;
	var $TaxGLCodes;
        var $StockHeight;
        /* Add Stock freight cost measurements */
        var $ItemHeight;
        var $ItemWidth;
        var $ItemLength;
        var $ItemVolume;
        var $ItemWeight;
        var $ItemChargeWeight;
        var $ItemShipper;
        var $ItemServiceType;
        var $FreightCost;
        var $ItemPrefSupp;
        var $ItemSuppWare;
        var $ItemFreComment;
        /* Item Stock Location */
        var $FromStkLoc;
        

	function Cart(){
	/*Constructor function initialises a new shopping cart */
		$this->LineItems = array();
		$this->total=0;
		$this->ItemsOrdered=0;
		$this->LineCounter=0;
		$this->DefaultSalesType="";
		$this->FreightCost =0;
		$this->FreightTaxes = array();
		$this->CurrDecimalPlaces=2; //default
                $this->ItemHeight=0.0;
                $this->ItemWidth=0.0;
                $this->ItemLength=0.0;
                $this->ItemVolume=0.0;
                $this->ItemWeight=0.0;
                $this->ItemChargeWeight=0.0;
                $this->ItemFreComment='';
                $this->DebtorNo='';
	}

	function add_to_cart($StockID,
							$Qty,
							$Descr,
							$Price,
							$Disc=0,
							$UOM,
							$Volume,
							$Weight,
							$QOHatLoc=0,
							$MBflag='B',
							$ActDispatchDate=NULL,
							$QtyInvoiced=0,
							$DiscCat='',
							$Controlled=0,
							$Serialised=0,
							$DecimalPlaces=0,
							$Narrative='',
							$UpdateDB='No',
							$LineNumber=-1,
							$TaxCategory=0,
							$vtigerProductID='',
							$ItemDue = '',
							$POLine='',
							$StandardCost=0,
							$EOQ=1,
							$NextSerialNo=0,
							$ExRate=1,
                                                        $FreightCost,
                                                        $StockParent,
                                                        $DebtorNo,
                                                        $OrderNo,
                                                        $ItemHeight,
                                                        $ItemWidth,
                                                        $ItemLength,
                                                        $ItemVolume,
                                                        $ItemWeight,
                                                        $ItemChargeWeight,
                                                        $ItemShipper,
                                                        $ItemServiceType,
                                                        $ItemPrefSupp,
                                                        $ItemSuppWare,
                                                        $ItemFreComment,$FromStkLoc){

		if (isset($StockID) AND $StockID!="" AND $Qty>0 AND isset($Qty)){

			if ($Price<0){ /*madness check - use a credit note to give money away!*/
				$Price=0;
			}

			if ($LineNumber==-1){
				$LineNumber = $this->LineCounter;
			}

			$this->LineItems[$LineNumber] = new LineDetails($LineNumber,
															$StockID,
															$Descr,
															$Qty,
															$Price,
															$Disc,
															$UOM,
															$Volume,
															$Weight,
															$QOHatLoc,
															$MBflag,
															$ActDispatchDate,
															$QtyInvoiced,
															$DiscCat,
															$Controlled,
															$Serialised,
															$DecimalPlaces,
															$Narrative,
															$TaxCategory,
															$ItemDue,
															$POLine,
															$StandardCost,
															$EOQ,
															$NextSerialNo,
															$ExRate,
                                                                                                                        $FreightCost,
                                                                                                                        $StockParent,
                                                                                                                        $DebtorNo,
                                                                                                                        $OrderNo,
                                                                                                                        $ItemHeight,
                                                                                                                        $ItemWidth,
                                                                                                                        $ItemLength,
                                                                                                                        $ItemVolume,
                                                                                                                        $ItemWeight,
                                                                                                                        $ItemChargeWeight,
                                                                                                                        $ItemShipper,
                                                                                                                        $ItemServiceType, 
                                                                                                                        $ItemPrefSupp,
                                                                                                                        $ItemSuppWare,
                                                                                                                        $ItemFreComment,$FromStkLoc);
			$this->ItemsOrdered++;

			if ($UpdateDB=='Yes'){
				/*ExistingOrder !=0 set means that an order is selected or created for entry
				of items - ExistingOrder is set to 0 in scripts that should not allow
				adding items to the order - New orders have line items added at the time of
				committing the order to the DB in DeliveryDetails.php
				 GET['ModifyOrderNumber'] is only set when the items are first
				being retrieved from the DB - dont want to add them again - would return
				errors anyway */

				global $db;
				$sql = "INSERT INTO salesorderdetails (orderlineno,
																						orderno,
																						stkcode,
																						quantity,
																						unitprice,
																						discountpercent,
																						itemdue,
																						poline,fromstkloc)
																					VALUES(" . $LineNumber. ",
																						" . $_SESSION['ExistingOrder'] . ",
																						'" . trim(strtoupper($StockID)) ."',
																						" . $Qty . ",
																						" . $Price . ",
																						" . $Disc . ",'
																						" . $ItemDue . "',
																						" . $POLine . "," . $FromStkLoc .")";
                                
				$result = DB_query($sql,$db ,_('The order line for') . ' ' . strtoupper($StockID) . ' ' ._('could not be inserted'));
                                
                                /* Insert into a new line into FreightCostEvaluation */
                                           $LineFreightCostSQL = "INSERT INTO freightcostevaluation  ( linenumber,
                                                                              itemcode,
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
                                        " . $LineNumber . ",
                                        '".$StockID ."',
					'" . $DebtorNo . "',
					'" . $OrderNo . "',
					'" . $Qty . "',
					'" . $ItemHeight . "',
					'" . $ItemWidth . "',
					'" . $ItemLength . "',
					'" . $ItemVolume . "',
                                        '" . $ItemWeight . "',
                                        '" . $ItemChargeWeight . "',    
                                        '" . $ItemShipper . "',
                                        '" . $ItemServiceType ."',    
                                        '" . $FreightCost . "',    
                                        '" . $ItemPrefSupp . "', 
                                        '" . $ItemSuppWare . "',     
                                        '" . $ItemFreComment . "'   
                                        )";
		$ErrMsg = _('Unable to add the freight cost evaluation line');
		$Ins_LineFreightCostResult = DB_query($LineFreightCostSQL,$db,$ErrMsg,$DbgMsg,true);
                
			}

			$this->LineCounter = $LineNumber + 1;
			Return 1;
		}
		Return 0;
	}

	function update_cart_item( $UpdateLineNumber,
														$Qty,
														$Price,
														$Disc,
														$Narrative,
														$UpdateDB='No',
														$ItemDue,
														$POLine,
														$GPPercent,
                                                                                                                $FreightCost, $FromStkLoc){

		if ($Qty>0){
			$this->LineItems[$UpdateLineNumber]->Quantity = $Qty;
		}
		$this->LineItems[$UpdateLineNumber]->Price = $Price;
		$this->LineItems[$UpdateLineNumber]->DiscountPercent = $Disc;
		$this->LineItems[$UpdateLineNumber]->Narrative = $Narrative;
		$this->LineItems[$UpdateLineNumber]->ItemDue = $ItemDue;
		$this->LineItems[$UpdateLineNumber]->POLine = $POLine;
		$this->LineItems[$UpdateLineNumber]->GPPercent = $GPPercent;
                $this->LineItems[$UpdateLineNumber]->FreightCost = $FreightCost;
                $this->LineItems[$UpdateLineNumber]->FromStkLoc = $FromStkLoc;
		if ($UpdateDB=='Yes'){
			global $db; 
			$result = DB_query("UPDATE salesorderdetails SET quantity=" . $Qty . ",
																										unitprice=" . $Price . ",
																										discountpercent=" . $Disc . ",
																										narrative ='" . DB_escape_string($Narrative) . "',
																										itemdue = '" . FormatDateForSQL($ItemDue) . "',
																										poline = '" . DB_escape_string($POLine) . "',
                                                                                                                                                                                                                fromstkloc = '" . DB_escape_string($FromStkLoc) . "'    
														WHERE orderno=" . $_SESSION['ExistingOrder'] . "
														AND orderlineno=" . $UpdateLineNumber
													, $db
				, _('The order line number') . ' ' . $UpdateLineNumber .  ' ' . _('could not be updated'));
                        
              /*08042014 by Stan update stock measurements in freightcostvaluation table if its an already order */ 
                if($_SESSION['ExistingOrder'] !=0){        
                  $measuresql="Select sm.unitlength, sm.unitwidth, sm.unitheight from stockmeasurement as sm inner join salesorderdetails as sod on sm.stockid=sod.stkcode
                               where sod.orderno=" . $_SESSION['ExistingOrder'] . " AND sod.orderlineno=" . $UpdateLineNumber." Limit 1";
                  $MeasureResult = DB_query($measuresql,$db,'','',true);
                  $SQLMeasurementList=DB_fetch_array($MeasureResult);
                 
                  $this->LineItems[$UpdateLineNumber]->ItemLength = $SQLMeasurementList["unitlength"];
                  $this->LineItems[$UpdateLineNumber]->ItemWidth = $SQLMeasurementList["unitwidth"];
                  $this->LineItems[$UpdateLineNumber]->ItemHeight = $Qty*$SQLMeasurementList["unitheight"];
             
                  $result = DB_query("UPDATE freightcostevaluation SET height=" . $Qty . "*". $SQLMeasurementList["unitheight"].",
                                                                       width=". $SQLMeasurementList["unitwidth"].",
                                                                       length=" . $SQLMeasurementList["unitlength"]."    
                                      WHERE salesorder=" . $_SESSION['ExistingOrder'] . " AND linenumber=" . $UpdateLineNumber, $db
				, _('The freight cost valuation line number') . ' ' . $UpdateLineNumber .  ' ' . _('could not be updated'));
                    }
            /* End of customization */
		}
	}

	function remove_from_cart($LineNumber, $UpdateDB='No'){

		if (!isset($LineNumber) || $LineNumber=='' || $LineNumber < 0){ /* over check it */
			prnMsg(_('No Line Number passed to remove_from_cart, so nothing has been removed.'), 'error');
			return;
		}
		if ($UpdateDB=='Yes'){
			global $db;
			if ($this->Some_Already_Delivered($LineNumber)==0){
				/* nothing has been delivered, delete it. */
				$result = DB_query("DELETE FROM salesorderdetails
									WHERE orderno='" . $_SESSION['ExistingOrder'] . "'
									AND orderlineno='" . $LineNumber . "'",
									$db,
									_('The order line could not be deleted because')
									);
				prnMsg( _('Deleted Line Number'). ' ' . $LineNumber . ' ' . _('from existing Order Number').' ' . $_SESSION['ExistingOrder'], 'success');
                                
                                /* Remove particular line from freightcostevaluation */

                                $result = DB_query("DELETE FROM freightcostevaluation
									WHERE salesorder='" . $_SESSION['ExistingOrder'] . "'
                                                                        AND linenumber='" . $LineNumber . "'",
									$db,
									_('The freight cost  line could not be deleted because')
									);
				prnMsg( _('Deleted freight cost Line Number'). ' ' . $stockid . ' ' . _('from existing Freight List').' ' . $_SESSION['ExistingOrder'], 'success');
                             
			} else {
				/* something has been delivered. Clear the remaining Qty and Mark Completed */
				$result = DB_query("UPDATE salesorderdetails SET quantity=qtyinvoiced, completed=1
									WHERE orderno='".$_SESSION['ExistingOrder']."' AND orderlineno='" . $LineNumber . "'" ,
									$db,
								   _('The order line could not be updated as completed because')
								   );
				prnMsg(_('Removed Remaining Quantity and set Line Number '). ' ' . $LineNumber . ' ' . _('as Completed for existing Order Number').' ' . $_SESSION['ExistingOrder'], 'success');
			}
		}
		/* Since we need to check the LineItem above and might affect the DB, don't unset until after DB is updates occur */
		unset($this->LineItems[$LineNumber]);
		$this->ItemsOrdered--;
                $this->LineCounter--;

	}//remove_from_cart()

	function Get_StockID_List(){
		/* Makes a comma seperated list of the stock items ordered
		for use in SQL expressions */

		$StockID_List="";
		foreach ($this->LineItems as $StockItem) {
			$StockID_List .= ",'" . $StockItem->StockID . "'";
		}

		return substr($StockID_List, 1);

	}

	function Any_Already_Delivered(){
		/* Checks if there have been deliveries of line items */

		foreach ($this->LineItems as $StockItem) {
			if ($StockItem->QtyInv !=0){
				return 1;
			}
		}

		return 0;

	}

	function Some_Already_Delivered($LineNumber){
		/* Checks if there have been deliveries of a specific line item */

		if ($this->LineItems[$LineNumber]->QtyInv !=0){
			return $this->LineItems[$LineNumber]->QtyInv;
		}
		return 0;
	}

	function AllDummyLineItems(){
		foreach ($this->LineItems as $StockItem) {
			if($StockItem->MBflag !='D'){
				return false;
			}
		}
		return true;
	}

	function GetExistingTaxes($LineNumber, $stkmoveno){

		global $db;

		/*Gets the Taxes and rates applicable to this line from the TaxGroup of the branch and TaxCategory of the item
		and the taxprovince of the dispatch location */

		$sql = "SELECT stockmovestaxes.taxauthid,
				taxauthorities.description,
				taxauthorities.taxglcode,
				stockmovestaxes.taxcalculationorder,
				stockmovestaxes.taxontax,
				stockmovestaxes.taxrate
			FROM stockmovestaxes INNER JOIN taxauthorities
				ON stockmovestaxes.taxauthid = taxauthorities.taxid
			WHERE stkmoveno = '" . $stkmoveno . "'
			ORDER BY taxcalculationorder";

		$ErrMsg = _('The taxes and rates for this item could not be retrieved because');
		$GetTaxRatesResult = DB_query($sql,$db,$ErrMsg);

		while ($myrow = DB_fetch_array($GetTaxRatesResult)){

			$this->LineItems[$LineNumber]->Taxes[$myrow['taxcalculationorder']] =
								  new Tax($myrow['taxcalculationorder'],
										$myrow['taxauthid'],
										$myrow['description'],
										$myrow['taxrate'],
										$myrow['taxontax'],
										$myrow['taxglcode']);
		}
	} //end method GetExistingTaxes

	function GetTaxes($LineNumber){

		global $db;

		/*Gets the Taxes and rates applicable to this line from the TaxGroup of the branch and TaxCategory of the item
		and the taxprovince of the dispatch location */

		$SQL = "SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.taxglcode,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate
			FROM taxauthrates INNER JOIN taxgrouptaxes ON
				taxauthrates.taxauthority=taxgrouptaxes.taxauthid
				INNER JOIN taxauthorities ON
				taxauthrates.taxauthority=taxauthorities.taxid
			WHERE taxgrouptaxes.taxgroupid=" . $this->TaxGroup . "
			AND taxauthrates.dispatchtaxprovince=" . $this->DispatchTaxProvince . "
			AND taxauthrates.taxcatid = " . $this->LineItems[$LineNumber]->TaxCategory . "
			ORDER BY taxgrouptaxes.calculationorder";

		$ErrMsg = _('The taxes and rates for this item could not be retrieved because');
		$GetTaxRatesResult = DB_query($SQL,$db,$ErrMsg);
		unset($this->LineItems[$LineNumber]->Taxes);
		if (DB_num_rows($GetTaxRatesResult)==0){
		
			prnMsg(_('It appears that taxes are not defined correctly for this customer tax group') ,'error');
		} else {
			
			while ($myrow = DB_fetch_array($GetTaxRatesResult)){
		$this->LineItems[$LineNumber]->Taxes[$myrow['calculationorder']] = new Tax($myrow['calculationorder'],
														$myrow['taxauthid'],
														$myrow['description'],
														$myrow['taxrate'],
														$myrow['taxontax'],
														$myrow['taxglcode']);
			} //end loop around different taxes
		} //end if there are some taxes defined
	} //end method GetTaxes

	function GetFreightTaxes () {

		global $db;

		/*Gets the Taxes and rates applicable to the freight based on the tax group of the branch combined with the tax category for this particular freight
		and SESSION['FreightTaxCategory'] the taxprovince of the dispatch location */

		$sql = "SELECT taxcatid FROM taxcategories WHERE taxcatname='Freight'";
		$TaxCatQuery = DB_query($sql, $db);

		if ($TaxCatRow = DB_fetch_array($TaxCatQuery)) {
		  $TaxCatID = $TaxCatRow['taxcatid'];
		} else {
  		  prnMsg( _('Cannot find tax category Freight which must always be defined'),'error');
		  exit();
		}

		$SQL = "SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.taxglcode,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate
				FROM taxauthrates INNER JOIN taxgrouptaxes ON
					taxauthrates.taxauthority=taxgrouptaxes.taxauthid
					INNER JOIN taxauthorities ON
					taxauthrates.taxauthority=taxauthorities.taxid
				WHERE taxgrouptaxes.taxgroupid='" . $this->TaxGroup . "'
				AND taxauthrates.dispatchtaxprovince='" . $this->DispatchTaxProvince . "'
				AND taxauthrates.taxcatid = '" . $TaxCatID . "'
				ORDER BY taxgrouptaxes.calculationorder";

		$ErrMsg = _('The taxes and rates for this item could not be retrieved because');
		$GetTaxRatesResult = DB_query($SQL,$db,$ErrMsg);

		while ($myrow = DB_fetch_array($GetTaxRatesResult)){

			$this->FreightTaxes[$myrow['calculationorder']] = new Tax($myrow['calculationorder'],
											$myrow['taxauthid'],
											$myrow['description'],
											$myrow['taxrate'],
											$myrow['taxontax'],
											$myrow['taxglcode']);
		}
	} //end method GetFreightTaxes()

} /* end of cart class defintion */

Class LineDetails {
	Var $LineNumber;
	Var $StockID;
	Var $ItemDescription;
	Var $Quantity;
	Var $Price;
	Var $DiscountPercent;
	Var $Units;
	Var $Volume;
	Var $Weight;
	Var $ActDispDate;
	Var $QtyInv;
	Var $QtyDispatched;
	Var $StandardCost;
	Var $QOHatLoc;
	Var $MBflag;	/*Make Buy Dummy, Assembly or Kitset */
	Var $DiscCat; /* Discount Category of the item if any */
	Var $Controlled;
	Var $Serialised;
	Var $DecimalPlaces;
	Var $SerialItems;
	Var $Narrative;
	Var $TaxCategory;
	Var $Taxes;
	Var $WorkOrderNo;
	Var $ItemDue;
	Var $POLine;
	Var $EOQ;
	Var $NextSerialNo;
	Var $GPPercent;
        var $StockParent;
        /* Add Stock freight cost measurements */
        var $DebtorNo;
        var $OrderNo;
        var $ItemHeight;
        var $ItemWidth;
        var $ItemLength;
        var $ItemVolume;
        var $ItemWeight;
        var $ItemChargeWeight;
        var $ItemShipper;
        var $ItemServiceType;
        var $FreightCost;
        var $ItemPrefSupp;
        var $ItemSuppWare;
        var $ItemFreComment;
        var $FromStkLoc;

	function LineDetails ($LineNumber,
							$StockItem,
							$Descr,
							$Qty,
							$Prc,
							$DiscPercent,
							$UOM,
							$Volume,
							$Weight,
							$QOHatLoc,
							$MBflag,
							$ActDispatchDate,
							$QtyInvoiced,
							$DiscCat,
							$Controlled,
							$Serialised,
							$DecimalPlaces,
							$Narrative,
							$TaxCategory,
							$ItemDue,
							$POLine,
							$StandardCost,
							$EOQ,
							$NextSerialNo,
							$ExRate,
                                                        $FreightCost,
                                                        $StockParent,
                                                        $DebtorNo,
                                                        $OrderNo,
                                                        $ItemHeight,
                                                        $ItemWidth,
                                                        $ItemLength,
                                                        $ItemVolume,
                                                        $ItemWeight,
                                                        $ItemChargeWeight,
                                                        $ItemShipper,
                                                        $ItemServiceType,
                                                        $ItemPrefSupp,
                                                        $ItemSuppWare,
                                                        $ItemFreComment,$FromStkLoc){

/* Constructor function to add a new LineDetail object with passed params */
		$this->LineNumber = $LineNumber;
		$this->StockID =$StockItem;
		$this->ItemDescription = $Descr;
		$this->Quantity = $Qty;
		$this->Price = $Prc;
		$this->DiscountPercent = $DiscPercent;
		$this->Units = $UOM;
		$this->Volume = $Volume;
		$this->Weight = $Weight;
		$this->ActDispDate = $ActDispatchDate;
		$this->QtyInv = $QtyInvoiced;
		if ($Controlled==1){
			$this->QtyDispatched = 0;
		} else {
			$this->QtyDispatched = $Qty - $QtyInvoiced;
		}
		$this->QOHatLoc = $QOHatLoc;
		$this->MBflag = $MBflag;
		$this->DiscCat = $DiscCat;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->SerialItems = array();
		$this->Narrative = $Narrative;
		$this->Taxes = array();
		$this->TaxCategory = $TaxCategory;
		$this->WorkOrderNo = 0;
		$this->ItemDue = $ItemDue;
		$this->POLine = $POLine;
		$this->StandardCost = $StandardCost;
		$this->EOQ = $EOQ;
		$this->NextSerialNo = $NextSerialNo;

		if ($Prc > 0){
			$this->GPPercent = ((($Prc * (1 - $DiscPercent)) - ($StandardCost * $ExRate))*100)/$Prc;
		} else {
			$this->GPPercent = 0;
		}
                $this->FreightCost=$FreightCost;
                $this->StockParent=$StockParent;
                
                 /* Add Stock freight cost measurements */
                $this->DebtorNo=$DebtorNo;
                $this->OrderNo=$OrderNo;
                $this->ItemHeight=$ItemHeight;
                $this->ItemWidth=$ItemWidth;
                $this->ItemLength=$ItemLength;
                $this->ItemVolume=$ItemVolume;
                $this->ItemWeight=$ItemWeight;
                $this->ItemChargeWeight=$ItemChargeWeight;
                $this->ItemShipper=$ItemShipper;
                $this->ItemServiceType=$ItemServiceType;
                $this->FreightCost=$FreightCost;
                $this->ItemPrefSupp=$ItemPrefSupp;
                $this->ItemSuppWare=$ItemSuppWare;
                $this->ItemFreComment=$ItemFreComment;
                $this->FromStkLoc=$FromStkLoc;
                
	} //end constructor function for LineDetails

}

Class Tax {
	Var $TaxCalculationOrder;  /*the index for the array */
	Var $TaxAuthID;
	Var $TaxAuthDescription;
	Var $TaxRate;
	Var $TaxOnTax;
	var $TaxGLCode;

	function Tax ($TaxCalculationOrder,
			$TaxAuthID,
			$TaxAuthDescription,
			$TaxRate,
			$TaxOnTax,
			$TaxGLCode){

		$this->TaxCalculationOrder = $TaxCalculationOrder;
		$this->TaxAuthID = $TaxAuthID;
		$this->TaxAuthDescription = $TaxAuthDescription;
		$this->TaxRate =  $TaxRate;
		$this->TaxOnTax = $TaxOnTax;
		$this->TaxGLCode = $TaxGLCode;
	}
}

?>
