<?php

/* $Id: PrintCustTransPortrait.php 4536 2011-04-02 09:40:49Z daintree $ */
include('includes/DefinePOClass.php');
include('includes/session.inc');

if(isset($_GET['InvoiceNo']) and $_GET['InvoiceNo'] != ''){
    $InvoiceNo=$_GET['InvoiceNo'] ;
}
if (isset($_GET['InvOrCredit'])) {
	$InvOrCredit = $_GET['InvOrCredit'];
} elseif (isset($_POST['InvOrCredit'])) {
	$InvOrCredit = $_POST['InvOrCredit'];
}

if($InvOrCredit=='Invoice'){
    
if (isset($_GET['FromTransNo'])) {
	$FromTransNo = $_GET['FromTransNo'];
} elseif (isset($_POST['FromTransNo'])){
/* 29052014 if Email invoice to customer rather than print */
if (isset($_POST['EmailAddr'])){ 
    $FromTransNo =$_POST['FromTransNo'];
}
else{
    $FromTransNo = ConvertSOtoInvNo($_POST['FromTransNo'],$db,$rootpath);
}
} else {
	$FromTransNo = '';
}

/* End of logic */
If (!isset($_POST['ToTransNo'])
	or trim($_POST['ToTransNo'])==''
	or ConvertSOtoInvNo($_POST['ToTransNo'],$db,$rootpath) < $FromTransNo) {

	$_POST['ToTransNo'] = $FromTransNo;
   }
}

else{
 if (isset($_GET['FromTransNo'])) {
	$FromTransNo = $_GET['FromTransNo'];
} elseif (isset($_POST['FromTransNo'])){
	$FromTransNo = $_POST['FromTransNo'];
} else {
	$FromTransNo = '';
}

If (!isset($_POST['ToTransNo'])
	or trim($_POST['ToTransNo'])==''
	or $_POST['ToTransNo'] < $FromTransNo) {

	$_POST['ToTransNo'] = $FromTransNo;
   }   
}

if (isset($_GET['PrintPDF'])) {
	$PrintPDF = $_GET['PrintPDF'];
} elseif (isset($_POST['PrintPDF'])) {
	$PrintPDF = $_POST['PrintPDF'];
}



$FirstTrans = $FromTransNo; /* Need to start a new page only on subsequent transactions */

$TotalSaleAmount;

If (isset($PrintPDF)
	and $PrintPDF!=''
	and isset($FromTransNo)
	and isset($InvOrCredit)
	and $FromTransNo!=''){

	include ('includes/class.pdf.php');


    $Page_Width=595;
    $Page_Height=842;
    $Top_Margin=30;
    $Bottom_Margin=270;
    $Left_Margin=40;
    $Right_Margin=30;

	$pdf = new Cpdf('P', 'pt', 'A4');
	$pdf->addInfo('Author','webERP ' . $Version);
	$pdf->addInfo('Creator','webERP http://www.weberp.org');

	if ($InvOrCredit=='Invoice'){
		$pdf->addInfo('Title',_('Inv_')  . $_POST['ToTransNo']);
		$pdf->addInfo('Subject',_('Invoices from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
	} else {
		$pdf->addInfo('Title',_('Sales Credit Note') );
		$pdf->addInfo('Subject',_('Credit Notes from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
	}

	$pdf->setAutoPageBreak(0);
	$pdf->setPrintHeader(false);
	$pdf->AddPage();
	$pdf->cMargin = 0;

	$FirstPage = true;
	$line_height=16;

	while ($FromTransNo <= $_POST['ToTransNo']){

	/*retrieve the invoice details from the database to print
	notice that salesorder record must be present to print the invoice purging of sales orders will
	nobble the invoice reprints */

	// check if the user has set a default bank account for invoices, if not leave it blank
		$sql = "SELECT bankaccounts.invoice, 
					bankaccounts.bankaccountnumber, 
					bankaccounts.bankaccountcode
				FROM bankaccounts
				WHERE bankaccounts.invoice = '1'";
		$result=DB_query($sql,$db,'','',false,false);
		if (DB_error_no($db)!=1) {
			if (DB_num_rows($result)==1){
				$myrow = DB_fetch_array($result);
				$DefaultBankAccountNumber = _('Account:') .' ' .$myrow['bankaccountnumber'];
				$DefaultBankAccountCode =  _('BSB:') .' ' .$myrow['bankaccountcode'];
				$DefaultAccName= _('Acc Name:') .' '.$_SESSION['CompanyRecord']['coyname'];
                                $DefaultBank=_('Bank:') .' '._('ANZ');
			} else {
				$DefaultBankAccountNumber = '';
				$DefaultBankAccountCode =  '';
				$DefaultAccName='';
			}
		} else {
			$DefaultBankAccountNumber = '';
			$DefaultBankAccountCode =  '';
			$DefaultAccName='';
		}
// gather the invoice data

		if ($InvOrCredit=='Invoice') {
			$sql = "SELECT debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
                                        debtortrans.mod_flag,
					debtortrans.consignment,
                                        debtortrans.sales_ref_num,
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
                                        paymentterms.daysbeforedue,
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
                                        salesorders.contactphone,
					locations.locationname,
					shippers.shippername,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
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
                                        salesorders.fromstkloc
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesorders,
					shippers,
					salesman,
					locations,
					paymentterms
				WHERE debtortrans.order_ = salesorders.orderno
				AND debtortrans.type=10
				AND debtortrans.transno='" . $FromTransNo . "'
				AND debtortrans.shipvia=shippers.shipper_id
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtorsmaster.paymentterms=paymentterms.termsindicator
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode
				AND salesorders.fromstkloc=locations.loccode";

			if (isset($_POST['PrintEDI']) and $_POST['PrintEDI']=='No'){
				$sql = $sql . ' AND debtorsmaster.ediinvoices=0';
			}
		} 
		else {

			$sql = "SELECT debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
                                        debtortrans.mod_flag,
                                        debtortrans.sales_ref_num,
					debtorsmaster.invaddrbranch,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					debtorsmaster.taxref,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
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
                                        paymentterms.daysbeforedue,
					paymentterms.terms
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesman,
					paymentterms
				WHERE debtortrans.type=11
				AND debtorsmaster.paymentterms = paymentterms.termsindicator
				AND debtortrans.transno='" . $FromTransNo ."'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode";

			if (isset($_POST['PrintEDI']) and $_POST['PrintEDI']=='No'){
				$sql = $sql . ' AND debtorsmaster.ediinvoices=0';
			}
		}
		 $result=DB_query($sql,$db,'','',false,false);
		 
		/** Check Amount Paid through Receipt**/
		$sqlAP = "SELECT debtortrans.alloc 
				FROM debtortrans
				WHERE debtortrans.type=10
				AND debtortrans.transno='" . $FromTransNo ."'";
		

       $resultAP=DB_query($sqlAP,$db);
	  
       if(DB_num_rows($resultAP)==1){
       	$myrowAP = DB_fetch_array($resultAP);
        $tempAP=$myrowAP['alloc'];
       	$DisplayAmountPaid =number_format($myrowAP['alloc'],2);
       }
       else{
       	$DisplayAmountPaid=number_format(0,2);
       }

	   if (DB_error_no($db)!=0) {

			$title = _('Transaction Print Error Report');
			include ('includes/header.inc');

			prnMsg( _('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available'),'error');
			if ($debug==1){
			    prnMsg (_('The SQL used to get this information that failed was') . "<br />" . $sql,'error');
			}
			include ('includes/footer.inc');
			exit;
	    }
	    
	    if (DB_num_rows($result)==1){
			$myrow = DB_fetch_array($result);

			$ExchRate = $myrow['rate'];
                        $Daysbeforedue = $myrow['daysbeforedue'];

			if ($InvOrCredit == 'Invoice') {
				$sql = "SELECT stockmoves.stockid,
						stockmaster.description,
                                                stockmaster.mbflag,
						-stockmoves.qty as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmaster.controlled,
						stockmaster.units,
						stockmoves.stkmoveno
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=10
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
			} else {
				/* only credit notes to be retrieved */
				$sql = "SELECT stockmoves.stockid,
						stockmaster.description,
                                                stockmaster.mbflag,
						stockmoves.qty as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmaster.controlled,
						stockmaster.units,
						stockmoves.stkmoveno
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=11
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
			} // end else

		$result=DB_query($sql,$db);
		if (DB_error_no($db)!=0) {
			$title = _('Transaction Print Error Report');
			include ('includes/header.inc');
			echo '<br />' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
			if ($debug==1){
			    echo '<br />' . _('The SQL used to get this information that failed was') . '<br />' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}


		if (DB_num_rows($result)>0){

	    $PageNumber = 1;
            $FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/Invoice.xml');
			
            include('includes/PDFTransPageHeaderPortrait.inc');
			
			$line_height = $FormDesign->LineHeight;
			
			$FirstPage = False;

		    while ($myrow2=DB_fetch_array($result)){
					if ($myrow2['discountpercent'] == 0) {
						$DisplayDiscount = '';
					} else {
						$DisplayDiscount = number_format($myrow2['discountpercent'] * 100, 2) . '%';
						$DiscountPrice = $myrow2['fxprice'] * (1 - $myrow2['discountpercent']);
					}
				$DisplayNet = $myrow2['fxnet'];
				$DisplayPrice = $myrow2['fxprice'];
				$DisplayQty = $myrow2['quantity'];
                                $Narrative=htmlspecialchars_decode($myrow2['narrative']);
	       /* Retrieve the assemble products 12/11/2014 by Stan */
                if($myrow2['mbflag']=='A'){
                    	/*Now look for assembly components that would go negative */
				$ComponentsSQL = "SELECT bom.component,
                                        bom.quantity,
					stockmaster.description
                                        FROM bom INNER JOIN locstock
						ON bom.component=locstock.stockid
						INNER JOIN stockmaster
						ON stockmaster.stockid=bom.component
						WHERE bom.parent='" . $myrow2['stockid'] . "'
						AND locstock.loccode='" . $myrow['fromstkloc'] . "'
						AND effectiveafter <'" . Date('Y-m-d') . "'
						AND effectiveto >='" . Date('Y-m-d') . "'";

				$ErrMsg = _('Could not retrieve the component quantity left at the location once the assembly item on this order is invoiced (for the purposes of checking that stock will not go negative because)');
				$ComponentsResult = DB_query($ComponentsSQL,$db,$ErrMsg);
                                $sub_description=array();
                                while ($com = DB_fetch_array($ComponentsResult)){
					$sub_description[]=$com['component'].' '.$com['description'].' x'.$com['quantity'];
				}
                }

               /* display item details*/
		$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column1->x, $YPos, $FormDesign->Data->Column1->Length, $FormDesign->Data->Column1->FontSize, $myrow2['stockid'],'left');
          
                if(strlen($Narrative)>0){
                    $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x, $YPos, $FormDesign->Data->Column2->Length, $FormDesign->Data->Column2->FontSize, $myrow2['description'].'  -  '.$Narrative,'left');
                }
                else{
                    $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x, $YPos, $FormDesign->Data->Column2->Length, $FormDesign->Data->Column2->FontSize, $myrow2['description'],'left');
                }
                while(strlen($LeftOvers)>1){
                                $YPos-=10;
				$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x, $YPos, $FormDesign->Data->Column2->Length, $FormDesign->Data->Column2->FontSize, $LeftOvers,'left');
			}
                                                
                $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column3->x, $YPos, $FormDesign->Data->Column3->Length, $FormDesign->Data->Column3->FontSize, number_format($DisplayQty,0),'left');
                $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column4->x, $YPos, $FormDesign->Data->Column4->Length, $FormDesign->Data->Column4->FontSize, number_format($DisplayPrice,2),'right');
          //      $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column5->x, $YPos, $FormDesign->Data->Column5->Length, $FormDesign->Data->Column5->FontSize, $myrow2['units'],'left');
                $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column5->x, $YPos, $FormDesign->Data->Column5->Length, $FormDesign->Data->Column5->FontSize, $DisplayDiscount,'right');
                $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column6->x, $YPos, $FormDesign->Data->Column6->Length, $FormDesign->Data->Column6->FontSize, number_format($DisplayNet,2),'right');
              if(count($sub_description)>0){
                  $YPos-=20;
                   $pdf->addTextWrap($FormDesign->Data->Column1->x, $YPos, $FormDesign->Data->Column1->Length+15, $FormDesign->Data->Column1->FontSize, 'Each Pack Contains:','left');
              }
              $i=0;
              while ($i < count($sub_description)) {
                  
		  $LeftOvers =$pdf->addTextWrap($FormDesign->Data->Column2->x, $YPos, $FormDesign->Data->Column2->Length+700, $FormDesign->Data->Column2->FontSize, $sub_description[$i],'left');  
                  while(strlen($LeftOvers)>1){
                                $YPos-=10;
				$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x, $YPos, $FormDesign->Data->Column2->Length+700, $FormDesign->Data->Column2->FontSize, $LeftOvers,'left');
			}
                  $YPos-=10;      
                  $i++;
              }        
		
                $TotalSaleAmount+=$DisplayNet;

				if ($myrow2['controlled']==1){

					$GetControlMovts = DB_query("SELECT moveqty, 
														serialno
												 FROM   stockserialmoves
												 WHERE  stockmoveno='" . $myrow2['stkmoveno'] . "'",$db);

					if ($myrow2['serialised']==1){
						while ($ControlledMovtRow = DB_fetch_array($GetControlMovts)){
							$YPos -= (10*$lines);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+82,$YPos,100,$FontSize,$ControlledMovtRow['serialno'],'left');
							if ($YPos-$line_height <= $Bottom_Margin){
								/* head up a new invoice/credit note page */
								/*draw the vertical column lines right to the bottom */
								//PrintLinesToBottom ();
	   		        			include ('includes/PDFTransPageHeaderPortrait.inc');
			   				} //end if need a new page headed up
						}
					} else {
						while ($ControlledMovtRow = DB_fetch_array($GetControlMovts)){
							$YPos -= (10*$lines);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+82,$YPos,100,$FontSize,(-$ControlledMovtRow['moveqty']) . ' x ' . $ControlledMovtRow['serialno'],'left');
							if ($YPos-$line_height <= $Bottom_Margin){
								/* head up a new invoice/credit note page */
								/*draw the vertical column lines right to the bottom */
								//PrintLinesToBottom ();
	   		        			include ('includes/PDFTransPageHeaderPortrait.inc');
			   				} //end if need a new page headed up

						}
					}
				}
				
                                $YPos -= 2*$line_height;

				
//				for ($i=0;$i<sizeOf($lines);$i++) {
//				while (strlen($lines[$i])>1){
//					if ($YPos-$line_height <= $Bottom_Margin){
//						/* head up a new invoice/credit note page */
//						/*draw the vertical column lines right to the bottom */
//						//PrintLinesToBottom ();
//	   		        	include ('includes/PDFTransPageHeaderPortrait.inc');
//			   		} //end if need a new page headed up
//			   		/*increment a line down for the next line item */
//			   		if (strlen($lines[$i])>1){
//						$lines[$i] = $pdf->addTextWrap($Left_Margin+85,$YPos,181,$FontSize,stripslashes($lines[$i]));
//					}
//					$YPos -= ($line_height);
//				}
//				}
				if ($YPos <= $Bottom_Margin){

					/* head up a new invoice/credit note page */
					/*draw the vertical column lines right to the bottom */
					//PrintLinesToBottom ();
					include ('includes/PDFTransPageHeaderPortrait.inc');
				} //end if need a new page headed up
			} /*end while there are line items to print out*/

		} /*end if there are stock movements to show on the invoice or credit note*/

		$YPos -= $line_height;

		/* check to see enough space left to print the 4 lines for the totals/footer */
		if (($YPos-$Bottom_Margin)<($line_height)){
			//PrintLinesToBottom ();
			include ('includes/PDFTransPageHeaderPortrait.inc');
		}

		/*Now print out the footer and totals */

		if ($InvOrCredit=='Invoice') {

                    if($myrow['mod_flag']==2){
                     $DisplaySubTot = number_format(0,2);
                     $DisplayFreight = number_format(0,2);  
                     $DisplayTax = number_format(0,2);
                     $temptotal=$TotalSaleAmount;
		     $DisplayTotal=number_format($TotalSaleAmount,2);
		     $DisplayBalance=number_format($temptotal-$tempAP,2);
                
                    }
                    else{
		     $DisplaySubTot = number_format($TotalSaleAmount,2);
		     $DisplayFreight = number_format($myrow['ovfreight'],2);
		     $DisplayTax = number_format($myrow['ovgst'],2);
		     $temptotal=$myrow['ovfreight']+$myrow['ovgst']+$TotalSaleAmount;
		     $DisplayTotal=number_format($myrow['ovfreight']+$myrow['ovgst']+$TotalSaleAmount,2);
		     $DisplayBalance=number_format($temptotal-$tempAP,2);
                  
                    }
                    $CustomerReference=$myrow['customerref'];
                     $Comments=$myrow['comments'];
		     
		} else {
		     $DisplaySubTot = number_format(-$myrow['ovamount'],2);
		     $DisplayFreight = number_format(-$myrow['ovfreight'],2);
		     $DisplayTax = number_format(-$myrow['ovgst'],2);
		     $DisplayTotal = number_format(-$myrow['ovfreight']-$myrow['ovgst']-$myrow['ovamount'],2);
		}
	/*Print out the invoice text entered */
		$YPos = $Bottom_Margin+(14*$line_height);
        $pdf->line($FormDesign->DrawVerticalLine3->x1,$FormDesign->DrawVerticalLine3->y1,$FormDesign->DrawVerticalLine3->x2,$FormDesign->DrawVerticalLine3->y2);
	/* Print out the payment terms */
		$FontSize=$FormDesign->PaymentTerms->Line1->FontSize;
	    $pdf->addText($FormDesign->PaymentTerms->Caption->x, $FormDesign->PaymentTerms->Caption->y, $FormDesign->PaymentTerms->Caption->FontSize, _('Terms and conditions:'));
            $LeftOvers='';
            $TermsCondition1="Payment of this invoice is considered as acceptance of our Terms and Conditions. Refer to our website for full list of our terms. Delivery can take up to 5 days from order release."; 
	    $LeftOvers=$pdf->addTextWrap($FormDesign->PaymentTerms->Line1->x,$FormDesign->PaymentTerms->Line1->y,180,$FormDesign->PaymentTerms->Line1->FontSize,$TermsCondition1);          
	    $tempSpace=$FormDesign->PaymentTerms->Line1->y-10;
	    while (strlen($LeftOvers)>1){		
	    $LeftOvers = $pdf->addTextWrap($FormDesign->PaymentTerms->Line1->x,
	                 $tempSpace,
	                 190,$FontSize,$LeftOvers);
	                 $tempSpace-=10;
		}
                
      /* highlight this line text */          
             $pdf->SetTextColor(255, 0, 0); 
             $pdf->SetFont('', 'BU', '', '', 'false');
             $tempSpace-=5;  
             $TermsCondition2="Note Transit Insurance is not included unless specifically bought on this invoice."; 
             $LeftOvers=$pdf->addTextWrap($FormDesign->PaymentTerms->Line1->x,$tempSpace,180,$FormDesign->PaymentTerms->Line1->FontSize,$TermsCondition2); 
	     $tempSpace=$tempSpace-10;
	     while (strlen($LeftOvers)>1){		
	     $LeftOvers = $pdf->addTextWrap($FormDesign->PaymentTerms->Line1->x,
	                 $tempSpace,
	                 190,$FontSize,$LeftOvers);
	                 $tempSpace-=10;
		}
            $pdf->SetTextColor(0);    
            $pdf->SetFont('', '', '', '', 'false');
            
           if ($InvOrCredit=='Invoice'){     
           $TermsCondition3="For Credit Card Payment please call us on 1300 600 360. CC Surcharge is applied at the rate of 1.5% on Visa and MasterCard."; 
                }
           $tempSpace-=5;      
           $LeftOvers=$pdf->addTextWrap($FormDesign->PaymentTerms->Line1->x,$tempSpace,180,$FormDesign->PaymentTerms->Line1->FontSize,$TermsCondition3); 
	   $tempSpace=$tempSpace-10;
	   while (strlen($LeftOvers)>1){		
	   $LeftOvers = $pdf->addTextWrap($FormDesign->PaymentTerms->Line1->x,
	                 $tempSpace,
	                 190,$FontSize,$LeftOvers);
	                 $tempSpace-=10;
		}
        
        $pdf->SetTextColor(0,100,0);
        	if ($InvOrCredit=='Invoice'){
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->SubTotal->x, $FormDesign->InvoiceTotal->SubTotal->y,$FormDesign->InvoiceTotal->SubTotal->width, $FormDesign->InvoiceTotal->SubTotal->FontSize, _('Sale Amount'),'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->Freight->x, $FormDesign->InvoiceTotal->Freight->y,$FormDesign->InvoiceTotal->Freight->width,$FormDesign->InvoiceTotal->Freight->FontSize, _('Freight'),'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->Tax->x, $FormDesign->InvoiceTotal->Tax->y, $FormDesign->InvoiceTotal->Tax->width, $FormDesign->InvoiceTotal->Tax->FontSize, _('GST'),'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->Total->x, $FormDesign->InvoiceTotal->Total->y, $FormDesign->InvoiceTotal->Total->width, $FormDesign->InvoiceTotal->Total->FontSize, _('Total'),'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->Ampaid->x, $FormDesign->InvoiceTotal->Ampaid->y, $FormDesign->InvoiceTotal->Ampaid->width, $FormDesign->InvoiceTotal->Ampaid->FontSize, _('Amount Paid'),'right');
            if($Daysbeforedue != 1){ 
            $pdf->addTextWrap($FormDesign->InvoiceTotal->Paydueterm->x, $FormDesign->InvoiceTotal->Paydueterm->y, $FormDesign->InvoiceTotal->Paydueterm->width, $FormDesign->InvoiceTotal->Paydueterm->FontSize, _('Payment on this invoice is due on '.ConvertSQLDate(date('Y-m-d', strtotime("+".$Daysbeforedue." days", strtotime($myrow['trandate']))))),'right');
            }
            else{
            $pdf->addTextWrap($FormDesign->InvoiceTotal->Payduenoterm->x, $FormDesign->InvoiceTotal->Payduenoterm->y, $FormDesign->InvoiceTotal->Payduenoterm->width, $FormDesign->InvoiceTotal->Payduenoterm->FontSize, _('Payment due prior to order release'),'right');    
            }
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->ToInv->x, $FormDesign->InvoiceTotal->ToInv->y, $FormDesign->InvoiceTotal->ToInv->width, $FormDesign->InvoiceTotal->ToInv->FontSize, _('Balance Due'),'right');
	    $pdf->SetTextColor(0);

	    $pdf->addTextWrap($FormDesign->InvoiceTotalData->SubTotalData->x, $FormDesign->InvoiceTotalData->SubTotalData->y, $FormDesign->InvoiceTotalData->SubTotalData->width, $FormDesign->InvoiceTotalData->SubTotalData->FontSize, $DisplaySubTot,'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotalData->FreightData->x, $FormDesign->InvoiceTotalData->FreightData->y,$FormDesign->InvoiceTotalData->FreightData->width, $FormDesign->InvoiceTotalData->FreightData->FontSize, $DisplayFreight,'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotalData->TaxData->x, $FormDesign->InvoiceTotalData->TaxData->y, $FormDesign->InvoiceTotalData->TaxData->width,$FormDesign->InvoiceTotalData->TaxData->FontSize, $DisplayTax,'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotalData->TotalData->x, $FormDesign->InvoiceTotalData->TotalData->y, $FormDesign->InvoiceTotalData->TotalData->width, $FormDesign->InvoiceTotalData->TotalData->FontSize, $DisplayTotal,'right');
            $pdf->addTextWrap($FormDesign->InvoiceTotalData->AmpaidData->x, $FormDesign->InvoiceTotalData->AmpaidData->y,$FormDesign->InvoiceTotalData->AmpaidData->width, $FormDesign->InvoiceTotalData->AmpaidData->FontSize, $DisplayAmountPaid,'right');
                }
                elseif($InvOrCredit=='Credit'){
            $pdf->addTextWrap($FormDesign->InvoiceTotal->SubTotal->x, $FormDesign->InvoiceTotal->SubTotal->y,$FormDesign->InvoiceTotal->SubTotal->width, $FormDesign->InvoiceTotal->SubTotal->FontSize, _('Credit Amount'),'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->Freight->x, $FormDesign->InvoiceTotal->Freight->y,$FormDesign->InvoiceTotal->Freight->width,$FormDesign->InvoiceTotal->Freight->FontSize, _('Freight'),'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->Tax->x, $FormDesign->InvoiceTotal->Tax->y, $FormDesign->InvoiceTotal->Tax->width, $FormDesign->InvoiceTotal->Tax->FontSize, _('GST'),'right');
            $pdf->SetTextColor(0);

	    $pdf->addTextWrap($FormDesign->InvoiceTotalData->SubTotalData->x, $FormDesign->InvoiceTotalData->SubTotalData->y, $FormDesign->InvoiceTotalData->SubTotalData->width, $FormDesign->InvoiceTotalData->SubTotalData->FontSize, $DisplaySubTot,'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotalData->FreightData->x, $FormDesign->InvoiceTotalData->FreightData->y,$FormDesign->InvoiceTotalData->FreightData->width, $FormDesign->InvoiceTotalData->FreightData->FontSize, $DisplayFreight,'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotalData->TaxData->x, $FormDesign->InvoiceTotalData->TaxData->y, $FormDesign->InvoiceTotalData->TaxData->width,$FormDesign->InvoiceTotalData->TaxData->FontSize, $DisplayTax,'right');
	   
                }

		/*vertical to separate totals from comments and ROMALPA */
		
		$YPos+=10;
		if ($InvOrCredit=='Invoice'){

		    $pdf->addTextWrap($FormDesign->InvoiceTotalData->ToInvData->x, $FormDesign->InvoiceTotalData->ToInvData->y, $FormDesign->InvoiceTotalData->ToInvData->width,$FormDesign->InvoiceTotalData->ToInvData->FontSize, $DisplayBalance,'right');
		
		    $FontSize=8;
                    if( isset($CustomerReference) and  $CustomerReference!=''){
                    $pdf->addTextWrap($FormDesign->Custref->Caption->x, $FormDesign->Custref->Caption->y, $FormDesign->Custref->Caption->Length, $FormDesign->Custref->Caption->FontSize, _('Your Ref: '),'left');
                    $LeftOvers = $pdf->addTextWrap($FormDesign->Custref->Content->x, $FormDesign->Custref->Content->y, $FormDesign->Custref->Content->Length, $FormDesign->Custref->Content->FontSize, $CustomerReference,'left');
                    if (strlen($LeftOvers)>0){
	            $LeftOvers = $pdf->addTextWrap($FormDesign->Custref->Content->x, $FormDesign->Custref->Content->y, $FormDesign->Custref->Content->Length, $FormDesign->Custref->Content->FontSize, $LeftOvers,'left');
                    }
                  }
                  
                    if( isset($Comments) and  $Comments!=''){
                    $LeftOvers = $pdf->addTextWrap($FormDesign->Comments->x, $FormDesign->Comments->y, $FormDesign->Comments->Length, $FormDesign->Comments->FontSize, $Comments,'left');
                    if (strlen($LeftOvers)>0){
	            $LeftOvers = $pdf->addTextWrap($FormDesign->Comments->x, $FormDesign->Comments->y-10, $FormDesign->Comments->Length, $FormDesign->Comments->FontSize, $LeftOvers,'left');
                    }
                  }
			
//		$LeftOvers=$pdf->addTextWrap($FormDesign->RoClause->x,$FormDesign->RoClause->y,200,$FormDesign->RoClause->FontSize, $_SESSION['RomalpaClause']);
// 
//	        while (strlen($LeftOvers)>1 AND $FormDesign->RoClause->y-$line_height > $Bottom_Margin){
//				
//	             $LeftOvers = $pdf->addTextWrap($FormDesign->RoClause->x,
//	                          $FormDesign->RoClause->y-10,
//	                          200,$FontSize,$LeftOvers);
//	                         
//		}
		//	if(strlen($_SESSION['RomalpaClause'])>50){
	
				
		//	$LeftOvers = $pdf->addText($FormDesign->RoClause->x, $FormDesign->RoClause->y, $FormDesign->RoClause->FontSize,substr($_SESSION['RomalpaClause'],0,50).'-');
		//	$LeftOvers = $pdf->addText($FormDesign->RoClause->x, $FormDesign->RoClause->y-10, $FormDesign->RoClause->FontSize,substr($_SESSION['RomalpaClause'],51,50));
		//	}
		//	else{
		//	$LeftOvers = $pdf->addText($FormDesign->RoClause->x, $FormDesign->RoClause->y, $FormDesign->RoClause->FontSize,$_SESSION['RomalpaClause']);
		//	}
		//	while (strlen($LeftOvers)>0 AND $YPos > $Bottom_Margin){
		//		$YPos -=10;
		//		$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos-18,280,$FontSize,$LeftOvers);
		//	}
/* Add Images for Visa / Mastercard / Paypal */
//			if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg')) {
//				$pdf->addJpegFromFile('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg',$Page_Width/2 -60,$YPos-15,0,20);
//			}
// Print Bank acount details if available and default for invoices is selected
	$pdf->addText($FormDesign->PaymentMethod->Caption->x, $FormDesign->PaymentMethod->Caption->y, $FormDesign->PaymentMethod->Caption->FontSize, _('Payment Methods'));
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail1->x, $FormDesign->PaymentMethod->AccountDetail1->y, $FormDesign->PaymentMethod->AccountDetail1->FontSize, _('Please quote Invoice number on payment:'));
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail2->x, $FormDesign->PaymentMethod->AccountDetail2->y, $FormDesign->PaymentMethod->AccountDetail2->FontSize, $DefaultBank);
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail3->x, $FormDesign->PaymentMethod->AccountDetail3->y, $FormDesign->PaymentMethod->AccountDetail3->FontSize, $DefaultBankAccountCode); 
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail4->x, $FormDesign->PaymentMethod->AccountDetail4->y, $FormDesign->PaymentMethod->AccountDetail4->FontSize, $DefaultBankAccountNumber); 
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail5->x, $FormDesign->PaymentMethod->AccountDetail5->y, $FormDesign->PaymentMethod->AccountDetail5->FontSize, $DefaultAccName); 
	
		
		} else {
                        $pdf->addTextWrap($FormDesign->InvoiceTotalData->ToInvData->x, $FormDesign->InvoiceTotalData->ToInvData->y, $FormDesign->InvoiceTotalData->ToInvData->width,$FormDesign->InvoiceTotalData->ToInvData->FontSize, $DisplayTotal,'right');
			$pdf->addText($FormDesign->InvoiceTotal->ToInv->x-20, $FormDesign->InvoiceTotal->ToInv->y+10, $FormDesign->InvoiceTotal->ToInv->FontSize, _('TOTAL CREDIT'));
 		}
	    } /* end of check to see that there was an invoice record to print */

	    $FromTransNo++;
            unset($TotalSaleAmount);
            unset($tempAP);
	} /* end loop to print invoices */

/* 15052014 Send Email Through POST Method, Will replace the GET method due to meta syntax not allow double quotation*/
	if (isset($_POST['EmailAddr'])){ 
		include('includes/header.inc');
                include ('includes/htmlMimeMail.php');

                $FileName =   'Inv_' . $myrow['sales_ref_num'] . '.pdf';
                if($InvOrCredit=='Invoice'){
                    $FromTransNo=$_POST['InvoiceNumber'];
                }
                /* 05152014 use different email templates */
                if(isset($_POST['EmailSubject']) and $_POST['EmailSubject']!=''){
                $EmailSubject=$_POST['EmailSubject'];
                }
                else{
                $EmailSubject=$InvOrCredit . ' ' . $FromTransNo;    
                }
                if(isset($_POST['EmailMessage']) and $_POST['EmailMessage']!=''){
                $EmailMessage=str_replace(array("\r","\n",'\r','\n'),'',htmlspecialchars_decode($_POST['EmailMessage']));
                }
                else{
                $EmailMessage=_('Please find attached') . ' ' . $InvOrCredit . ' ' . $FromTransNo;    
                }
		$pdf->Output($FileName,'F');
		$mail = new htmlMimeMail();
                /* 29082014 send customer statement as well if checkbox ticked */  
                if(isset($_POST['CS_PDFAttach']) and $_POST['CS_PDFAttach']!=''){          
                $CS_PDFLink=Generate_CSPDF($_POST['CustomerNumber'], $_POST['CustomerNumber'], $db);
                $CustStatAttachment = $mail->getFile($CS_PDFLink);
                $mail->addAttachment($CustStatAttachment, $CS_PDFLink, 'application/pdf');
                unlink($CS_PDFLink);//delete the temporary file
                }
		$Attachment = $mail->getFile($FileName);
                $mail->setHtml($EmailMessage);
                $mail->setHtmlCharset("UTF-8");
                $mail->setSubject($EmailSubject);
		$mail->addAttachment($Attachment, $FileName, 'application/pdf');
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
                $mail->setCc($_POST['EmailAddrCC']);
                $mail->setBcc($_POST['EmailAddrBCC']);              
		$result = $mail->send(array($_POST['EmailAddr']),'smtp');
                /* Record Email Audit Log details */
                $emaillog=new EmailAuditLogModel($db);
                $emaillogbean=new EmailAuditLogBean();
                $emaillogbean->senddate=date('Y-m-d H:i:s');
                $emaillogbean->sendstatus=$result;
                $emaillogbean->ordernumber=$_POST['InvoiceNumber']<>''?$_POST['InvoiceNumber']:'';
                $emaillogbean->emailtemplateid=$_POST['ChooseEmailTemplate']<>''?$_POST['ChooseEmailTemplate']:'';
                $emaillogbean->emailfromaddress=$_SESSION['CompanyRecord']['email']<>''?$_SESSION['CompanyRecord']['email']:'';
                $emaillogbean->emailtoaddress=$_POST['EmailAddr']<>''?$_POST['EmailAddr']:'';
                $emaillogbean->emailccaddress=$_POST['EmailAddrCC']<>''?$_POST['EmailAddrCC']:'';
                $emaillogbean->emailbccaddress=$_POST['EmailAddrBCC']<>''?$_POST['EmailAddrBCC']:'';
                $emaillogbean->userid=$_SESSION['UserID']<>''?$_SESSION['UserID']:'';
                $emaillog->SaveEmailAuditLog($emaillogbean);
                /* End of record the audit log */
		unlink($FileName);
                

		$title = _('Emailing') . ' ' .$InvOrCredit . ' ' . _('Number') . ' ' . $_POST['InvoiceNumber'];
		include('includes/header.inc');
		echo '<p>' . $InvOrCredit . ' '  . _('number') . ' ' . $FromTransNo . ' ' . _('has been emailed to') . ' ' . $_POST['EmailAddr'];
		include('includes/footer.inc');
		exit;

	}
/* End of Change */	
////email the invoice to address supplied
//	if (isset($_GET['Email'])){ 
//		include('includes/header.inc');
//
//		include ('includes/htmlMimeMail.php');
//		$FileName =  'Inv_' . $_GET['FromTransNo'] . '.pdf';
//                /* 05152014 use different email templates */
//                if(isset($_GET['EmailSubject']) and $_GET['EmailSubject']!=''){
//                $EmailSubject=$_GET['EmailSubject'];
//                }
//                else{
//                $EmailSubject=$InvOrCredit . ' ' . $_GET['FromTransNo'];    
//                }
//                if(isset($_GET['EmailMessage']) and $_GET['EmailMessage']!=''){
//                $EmailMessage=htmlspecialchars_decode($_GET['EmailMessage']);
//                }
//                else{
//                $EmailMessage=_('Please find attached') . ' ' . $InvOrCredit . ' ' . $_GET['FromTransNo'];    
//                }
//		$pdf->Output($FileName,'F');
//		$mail = new htmlMimeMail();
//		
//		$Attachment = $mail->getFile($FileName);
//		//$mail->setText(_('Please find attached') . ' ' . $InvOrCredit . ' ' . $_GET['FromTransNo'] );
//                $mail->setHtml($EmailMessage);
//                $mail->SetSubject($EmailSubject);
//		$mail->addAttachment($Attachment, $FileName, 'application/pdf');
//		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
//		$result = $mail->send(array($_GET['Email']));
//
//		unlink($FileName); //delete the temporary file
//
//		$title = _('Emailing') . ' ' .$InvOrCredit . ' ' . _('Number') . ' ' . $FromTransNo;
//		include('includes/header.inc');
//                if($InvOrCredit=='Invoice'){
//                    $FromTransNo=$InvoiceNo;
//                }
//		echo '<p>' . $InvOrCredit . ' '  . _('number') . ' ' . $FromTransNo . ' ' . _('has been emailed to') . ' ' . $_GET['Email'];
//		include('includes/footer.inc');
//		exit;
//
//	} 
	else { //its not an email just print the invoice to PDF
            if ($InvOrCredit=='Invoice'){
                //Change To Avoid the PDF Error
                ob_end_clean();
		$pdf->OutputD( 'Inv_' . $myrow['sales_ref_num'] . '.pdf');
            }
            else{                
                //Change To Avoid the PDF Error
                ob_end_clean();
                $pdf->OutputD( 'CN_' . $FromTransNo . '.pdf');
            }
	}
	$pdf->__destruct();
} else { /*The option to print PDF was not hit */

	$title=_('Select Invoices/Credit Notes To Print');
	include('includes/header.inc');

	if (!isset($FromTransNo) OR $FromTransNo=='') {


	/*if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */

		echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST"><table class="selection">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . _('Print Invoices or Credit Notes (Portrait Mode)') . '</p>';
		echo '<tr><td>' . _('Print Invoices or Credit Notes') . '</td>
			<td><select name="InvOrCredit">';
		if ($InvOrCredit=='Invoice' OR !isset($InvOrCredit)){

		   echo '<option selected value="Invoice">' . _('Invoices') . '</option>';
		   echo '<option value="Credit">' . _('Credit Notes') . '</option>';
		} else {
		   echo '<option selected value="Credit">' . _('Credit Notes') . '</option>';
		   echo '<option VALUE="Invoice">' . _('Invoices') . '</option>';
		}
		echo '</select></td></tr>';

		echo '<tr><td>' . _('Print EDI Transactions') . '</td><td><select name="PrintEDI">';
		if ($InvOrCredit=='Invoice' OR !isset($InvOrCredit)){

		   echo '<option selected value="No">' . _('Do not Print PDF EDI Transactions') . '</option>';
		   echo '<option VALUE="Yes">' . _('Print PDF EDI Transactions Too') . '</option>';

		} else {

		   echo '<option VALUE="No">' . _('Do not Print PDF EDI Transactions') . '</option>';
		   echo '<option selected value="Yes">' . _('Print PDF EDI Transactions Too') . '</option>';

		}

		echo '</select></td></tr>';
		echo '<tr><td>' . _('Start invoice/credit note number to print') . '</td>
				<td><input class="number" type="text" max="6" size="7" name="FromTransNo" value="'.$InvoiceNo.'"></td></tr>';
		echo '<tr><td>' . _('End invoice/credit note number to print') . '</td>
				<td><input class="number" type="text" max="6" size="7" name="ToTransNo"></td></tr>
			</table>';
		echo '<div class="centre"><br /><input type="submit" name="Print" value="' . _('Print Preview') . '"><p>';
		echo '<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '"><p>';
                /* 06052014 by Stan Customize link to jumpt to send email page */
                if((isset($InvoiceNo) and $InvoiceNo!='')){
                echo '<a href="'.$rootpath.'/EmailCustTrans.php?FromTransNo='.ConvertSOtoInvNo($InvoiceNo,$db,$rootpath).'&InvOrCredit=Invoice" target="_blank">'. _('Send Email to Customer'). '</a><br /><br /></div>';
                }
                /* End of Customization */

		$sql = 'SELECT typeno FROM systypes WHERE typeid=30';

		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);

		echo '<div class="page_help_text"><b>' . _('The last Sales Order created was number') . ' ' . $myrow[0] . '</b><br />' . _('If only a single invoice is required') . ', ' . _('enter the invoice number to print in the Start transaction number to print field and leave the End transaction number to print field blank') . '. ' . _('Only use the end invoice to print field if you wish to print a sequential range of invoices') . '';

		$sql = "SELECT typeno FROM systypes WHERE typeid='11'";

		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);

		echo '<br /><b>' . _('The last credit note created was number') . ' ' . $myrow[0] . '</b><br />' . _('A sequential range can be printed using the same method as for invoices above') . '. ' . _('A single credit note can be printed by only entering a start transaction number') . '</DIV';

	} else {

		while ($FromTransNo <= $_POST['ToTransNo']){

	/*retrieve the invoice details from the database to print
	notice that salesorder record must be present to print the invoice purging of sales orders will
	nobble the invoice reprints */

			if ($InvOrCredit=='Invoice') {

			   $sql = "SELECT
			   		debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					salesorders.deliverto,
					salesorders.deladd1,
					salesorders.deladd2,
					salesorders.deladd3,
					salesorders.deladd4,
					salesorders.deladd5,
					salesorders.deladd6,
					salesorders.customerref,
					salesorders.orderno,
					salesorders.orddate,
					shippers.shippername,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.phoneno,
                                        custbranch.contactname,
					salesman.salesmanname,
					debtortrans.debtorno
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesorders,
					shippers,
					salesman
				WHERE debtortrans.order_ = salesorders.orderno
				AND debtortrans.type=10
				AND debtortrans.transno='" . $FromTransNo . "'
				AND debtortrans.shipvia=shippers.shipper_id
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode";
			} else {

			   $sql = "SELECT debtortrans.trandate,
			   		debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.phoneno,
                                        custbranch.contactname,
					salesman.salesmanname,
					debtortrans.debtorno
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesman
				WHERE debtortrans.type=11
				AND debtortrans.transno='" . $FromTransNo . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode";

			}

			$result=DB_query($sql,$db);
			if (DB_num_rows($result)==0 OR DB_error_no($db)!=0) {
				echo '<p>' . _('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available');
				if ($debug==1){
					echo _('The SQL used to get this information that failed was') . "<br />$sql";
				}
				break;
				include('includes/footer.inc');
				exit;
			} elseif (DB_num_rows($result)==1){

				$myrow = DB_fetch_array($result);
	/* Then there's an invoice (or credit note) to print. So print out the invoice header and GST Number from the company record */
				if (count($_SESSION['AllowedPageSecurityTokens'])==1 AND in_array(1, $_SESSION['AllowedPageSecurityTokens']) AND $myrow['debtorno'] != $_SESSION['CustomerID']){
					echo '<p><font color=RED size=4>' . _('This transaction is addressed to another customer and cannot be displayed for privacy reasons') . '. ' . _('Please select only transactions relevant to your company');
					exit;
				}

				$ExchRate = $myrow['rate'];
				$PageNumber = 1;

				echo '<table class="table1">
						<tr><td valign=top width=10%><img src="' . $_SESSION['LogoFile'] . '" width="110" height="60"></td><td bgcolor="#bbb"><b>';

				if ($InvOrCredit=='Invoice') {
				   echo '<font size=4>' . _('TAX INVOICE') . ' ';
				} else {
				   echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
				}
				echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font>' . '</td></tr></table>';

	/*Now print out the logo and company name and address */
				echo '<table class"table1"><tr><td><font size=4 color="#333"><b>' . $_SESSION['CompanyRecord']['coyname'] . '</b></font><br />';
				echo $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice3'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice4'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice5'] . '<br />';
				echo $_SESSION['CompanyRecord']['regoffice6'] . '<br />';
				echo _('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />';
				//echo _('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br />';
				echo _('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br />';

				echo '</td><td width=50% class=number>';

	/*Now the customer charged to details in a sub table within a cell of the main table*/

				echo '<table class="table1"><tr><td align="left" bgcolor="#bbb"><b>' . _('Charge To') . ':</b></td></tr><tr><td bgcolor="#eee">';
				echo $myrow['name'] . 
					'<br />' . $myrow['address1'] . 
					'<br />' . $myrow['address2'] . 
					'<br />' . $myrow['address3'] . 
					'<br />' . $myrow['address4'] . 
					'<br />' . $myrow['address5'] . 
					'<br />' . $myrow['address6'];
				
				echo '</td></tr></table>';
				/*end of the small table showing charge to account details */
			
				echo '</td></tr></table>';
				/*end of the main table showing the company name and charge to details */

				if ($InvOrCredit=='Invoice') {

				   echo '<table class="table1">
				   		<tr>
				   			<td align=left bgcolor="#bbb"><b>' . _('Charge Branch') . ':</b></td>
							<td align=left bgcolor="#bbb"><b>' . _('Delivered To') . ':</b></td>
						</tr>';
				   echo '<tr>
				   		<td bgcolor="#eee">' .$myrow['brname'] . 
                                                                        '<br />' . $myrow['contactname'] . 
                                                                        '<br />' . $myrow['phoneno'] .  
									'<br />' . $myrow['braddress1'] . 
									'<br />' . $myrow['braddress2'] . 
									'<br />' . $myrow['braddress3'] . 
									'<br />' . $myrow['braddress4'] . 
									'<br />' . $myrow['braddress5'] . 
									'<br />' . $myrow['braddress6'] .
                                                                     
						'</td>';

				   echo '<td bgcolor="#eee">' . $myrow['deliverto'] . 
                                                                        '<br />' . $myrow['contactname'] . 
                                                                        '<br />' . $myrow['phoneno'] . 
									'<br />' . $myrow['deladd1'] . 
									'<br />' . $myrow['deladd2'] . 
									'<br />' . $myrow['deladd3'] . 
									'<br />' . $myrow['deladd4'] . 
									'<br />' . $myrow['deladd5'] . 
									'<br />' . $myrow['deladd6'] .
                                                                         
						'</td>';
				   echo '</tr>
				   </table><hr>';

				   echo '<table class="table1">
				   		<tr>
							<td align=left bgcolor="#bbb"><b>' . _('Your Order Ref') . '</b></td>
							<td align=left bgcolor="#bbb"><b>' . _('Our Order No') . '</b></td>
							<td align=left bgcolor="#bbb"><b>' . _('Order Date') . '</b></td>
							<td align=left bgcolor="#bbb"><b>' . _('Invoice Date') . '</b></td>
							<td align=left bgcolor="#bbb"><b>' . _('Sales Person') . '</font></b></td>
							<td align=left bgcolor="#bbb"><b>' . _('Shipper') . '</b></td>
						
						</tr>';
				   	echo '<tr>
							<td bgcolor="#EEEEEE">' . $myrow['customerref'] . '</td>
							<td bgcolor="#EEEEEE">' .$myrow['orderno'] . '</td>
							<td bgcolor="#EEEEEE">' . ConvertSQLDate($myrow['orddate']) . '</td>
							<td bgcolor="#EEEEEE">' . ConvertSQLDate($myrow['trandate']) . '</td>
							<td bgcolor="#EEEEEE">' . $myrow['salesmanname'] . '</td>
							<td bgcolor="#EEEEEE">' . $myrow['shippername'] . '</td>
						
						</tr>
					</table>';

				   $sql ="SELECT stockmoves.stockid,
				   		stockmaster.description,
                                                stockmaster.mbflag,
						-stockmoves.qty as quantity,
						stockmoves.discountpercent,
						((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmoves.narrative,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=10
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";

				} else { /* then its a credit note */

				   echo '<table WIDTH=50%><tr>
				   		<td align=left bgcolor="#BBBBBB"><b>' . _('Branch') . ':</b></td>
						</tr>';
				   echo '<tr>
				   		<td bgcolor="#EEEEEE">' .$myrow['brname'] . 
										'<br />' . $myrow['braddress1'] . 
										'<br />' . $myrow['braddress2'] . 
										'<br />' . $myrow['braddress3'] . 
										'<br />' . $myrow['braddress4'] . 
										'<br />' . $myrow['braddress5'] . 
										'<br />' . $myrow['braddress6'] . 
								'</td>
					</tr></table>';
				   echo '<hr><table class="table1"><tr>
				   		<td align=left bgcolor="#bbbbbb"><b>' . _('Date') . '</b></td>
						<td align=left bgcolor="#BBBBBB"><b>' . _('Sales Person') . '</font></b></td>
					</tr>';
				   echo '<tr>
				   		<td bgcolor="#EEEEEE">' . ConvertSQLDate($myrow['trandate']) . '</td>
						<td bgcolor="#EEEEEE">' . $myrow['salesmanname'] . '</td>
					</tr></table>';


				   $sql ="SELECT stockmoves.stockid,
				   		stockmaster.description,
                                                stockmaster.mbflag,
						stockmoves.qty as quantity,
						stockmoves.discountpercent, ((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
						(stockmoves.price * " . $ExchRate . ") AS fxprice,
						stockmaster.units,
						stockmoves.narrative
					FROM stockmoves,
						stockmaster
					WHERE stockmoves.stockid = stockmaster.stockid
					AND stockmoves.type=11
					AND stockmoves.transno='" . $FromTransNo . "'
					AND stockmoves.show_on_inv_crds=1";
				}

				echo '<hr>';
				echo '<div class="centre"><font size=2>' . _('All amounts stated in') . ' ' . $myrow['currcode'] . '</font></div>';

				$result=DB_query($sql,$db);
				if (DB_error_no($db)!=0) {
					echo '<br />' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
					if ($debug==1){
						 echo '<br />' . _('The SQL used to get this information that failed was') . '<br />' . $sql;
					}
					exit;
				}

				if (DB_num_rows($result)>0){
					echo '<table class="table1">
						<tr><th>' . _('Item Code') . '</th>
						<th>' . _('Item Description') . '</th>
						<th>' . _('Quantity') . '</th>
						<th>' . _('Unit') . '</th>
						<th>' . _('Price') . '</th>
						<th>' . _('Discount') . '</th>
						<th>' . _('Total') . '</th></tr>';

					$LineCounter =17;
					$k=0;	//row colour counter

					while ($myrow2=DB_fetch_array($result)){

					      if ($k==1){
						  $RowStarter = '<tr class="EvenTableRows">';
						  $k=0;
					      } else {
						  $RowStarter = '<tr class="OddTableRows">';
						  $k=1;
					      }

					      echo $RowStarter;

					      $DisplayPrice =number_format($myrow2['fxprice'],2);
					      $DisplayQty = number_format($myrow2['quantity'],2);
					      $DisplayNet = number_format($myrow2['fxnet'],2);

					      if ($myrow2['discountpercent']==0){
						   $DisplayDiscount ='';
					      } else {
						   $DisplayDiscount = number_format($myrow2['discountpercent']*100,2) . '%';
					      }

					      printf ('<td>%s</td>
					      		<td>%s</td>
							<td class=number>%s</td>
							<td class=number>%s</td>
							<td class=number>%s</td>
							<td class=number>%s</td>
							<td class=number>%s</td>
							</tr>',
							$myrow2['stockid'],
							$myrow2['description'],
							$DisplayQty,
							$myrow2['units'],
							$DisplayPrice,
							$DisplayDiscount,
							$DisplayNet);

					      if (strlen($myrow2['narrative'])>1){
					      		echo $RowStarter . '<td></td><td colspan=6>' . $myrow2['narrative'] . '</td></tr>';
							$LineCounter++;
					      }

					      $LineCounter++;

					      if ($LineCounter == ($_SESSION['PageLength'] - 2)){

						/* head up a new invoice/credit note page */

						   $PageNumber++;
						   echo '</table><table class="table1"><tr><td VALIGN=TOP><img src="' . $_SESSION['LogoFile'] . '"></td><td bgcolor="#bbb"><b>';
						   if ($InvOrCredit=='Invoice') {
							    echo '<font size=4>' . _('TAX INVOICE') . ' ';
						   } else {
							    echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
						   }
						   echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br /><font size=1>' . _('GST Number') . ' - ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr></table>';

	/*Now print out company name and address */
						    echo '<table class="table1"><tr>
						    	<td><font size=4 color="#333"><b>' . $_SESSION['CompanyRecord']['coyname'] . '</b></font><br />';
						    echo $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
						    echo $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
						    echo $_SESSION['CompanyRecord']['regoffice3'] . '<br />';
						    echo $_SESSION['CompanyRecord']['regoffice4'] . '<br />';
						    echo $_SESSION['CompanyRecord']['regoffice5'] . '<br />';
						    echo $_SESSION['CompanyRecord']['regoffice6'] . '<br />';
						    echo _('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />';
						   // echo _('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br />';
						    echo _('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br />';
						    echo '</td><td class=number>' . _('Page') . ': ' .  $PageNumber . '</td></tr></table>';
						    echo '<table class="table1"><tr>
						    	<th>' . _('Item Code') . '</th>
							<th>' . _('Item Description') . '</th>
							<th>' . _('Quantity') . '</th>
							<th>' . _('Unit') . '</th>
							<th>' . _('Price') . '</th>
							<th>' . _('Discount') . '</th>
							<th>' . _('Total') . '</th></tr>';

						    $LineCounter = 10;

					      } //end if need a new page headed up
					} //end while there are line items to print out
					echo '</table>';
				} /*end if there are stock movements to show on the invoice or credit note*/

				/* check to see enough space left to print the totals/footer */
				$LinesRequiredForText = floor(strlen($myrow['invtext'])/140);

				if ($LineCounter >= ($_SESSION['PageLength'] - 8 - $LinesRequiredForText)){

					/* head up a new invoice/credit note page */

					$PageNumber++;
					echo '<table class="table1"><tr><td VALIGN="TOP"><img src="' . $_SESSION['LogoFile'] . '"></td><td bgcolor="#bbb"><b>';
					if ($InvOrCredit=='Invoice') {
					      echo '<font size=4>' . _('TAX INVOICE') .' ';
					} else {
					      echo '<font color=RED size=4>' . _('TAX CREDIT NOTE') . ' ';
					}
					echo '</b>' . _('Number') . ' ' . $FromTransNo . '</font><br /><font size=1>' . _('GST Number') . ' - ' . $_SESSION['CompanyRecord']['gstno'] . '</td></tr></table>';

	/*Print out the logo and company name and address */
					echo '<table class="table1"><tr><td><font size=4 color="#333"><b>' . $_SESSION['CompanyRecord']['coyname'] . '</b></font><br />';
					echo $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice3'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice4'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice5'] . '<br />';
					echo $_SESSION['CompanyRecord']['regoffice6'] . '<br />';
					echo _('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />';
					//echo _('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br />';
					echo _('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br />';
					echo '</td><td class=number>' . _('Page') . ": $PageNumber</td></tr></table>";
					echo '<table class="table1"><tr>
						<th>' . _('Item Code') . '</th>
						<th>' . _('Item Description') . '</th>
						<th>' . _('Quantity') . '</th>
						<th>' . _('Unit') . '</th>
						<th>' . _('Price') . '</th>
						<th>' . _('Discount') . '</th>
						<th>' . _('Total') . '</th></tr></table>';

					$LineCounter = 10;
				}

	/*Space out the footer to the bottom of the page */

				echo '<br /><br />' . $myrow['invtext'];

				$LineCounter=$LineCounter+2+$LinesRequiredForText;
				while ($LineCounter < ($_SESSION['PageLength'] -6)){
					echo '<br />';
					$LineCounter++;
				}

	/*Now print out the footer and totals */

				if ($InvOrCredit=='Invoice') {

				   $DisplaySubTot = number_format($myrow['ovamount'],2);
				   $DisplayFreight = number_format($myrow['ovfreight'],2);
				   $DisplayTax = number_format($myrow['ovgst'],2);
				   $DisplayTotal = number_format($myrow['ovfreight']+$myrow['ovgst']+$myrow['ovamount'],2);
				} else {
				   $DisplaySubTot = number_format(-$myrow['ovamount'],2);
				   $DisplayFreight = number_format(-$myrow['ovfreight'],2);
				   $DisplayTax = number_format(-$myrow['ovgst'],2);
				   $DisplayTotal = number_format(-$myrow['ovfreight']-$myrow['ovgst']-$myrow['ovamount'],2);
				}
	/*Print out the invoice text entered */
				echo '<table class="table1"><tr>
					<td class="number">' . _('Sub Total') . '</td>
					<td class="number" bgcolor="#EEEEEE" WIDTH="15%">' . $DisplaySubTot . '</td></tr>';
				echo '<tr><td class="number">' . _('Freight') . '</td>
					<td class="number" bgcolor="#EEEEEE">' . $DisplayFreight . '</td></tr>';
				echo '<tr><td class="number">' . _('Tax') . '</td>
					<td class="number" bgcolor="#EEEEEE">' . $DisplayTax . '</td></tr>';
				if ($InvOrCredit=='Invoice'){
				     echo '<tr><td class="number"><b>' . _('TOTAL AMOUNT') . '</b></td>
				     	<td class="number" bgcolor="#EEEEEE"><U><b>' . $DisplayTotal . '</b></U></td></tr>';
				} else {
				     echo '<tr><td class=number><font color=RED><b>' . _('TOTAL CREDIT') . '</b></font></td>
				     		<td class="number" bgcolor="#EEEEEE"><font color="red"><U><b>' . $DisplayTotal . '</b></u></font></td></tr>';
				}
				echo '</table>';
			} /* end of check to see that there was an invoice record to print */
			$FromTransNo++;
		} /* end loop to print invoices */
	} /*end of if FromTransNo exists */
	include('includes/footer.inc');

} /*end of else not PrintPDF */



function PrintLinesToBottom () {

	global $pdf;
	global $PageNumber;
	global $TopOfColHeadings;
	global $Left_Margin;
	global $Bottom_Margin;
	global $line_height;

/*draw the vertical column lines right to the bottom */
	$pdf->line($Left_Margin+78, $TopOfColHeadings+12,$Left_Margin+78,$Bottom_Margin);

	/*Print a column vertical line */
	$pdf->line($Left_Margin+268, $TopOfColHeadings+12,$Left_Margin+268,$Bottom_Margin);

	/*Print a column vertical line */
	$pdf->line($Left_Margin+348, $TopOfColHeadings+12,$Left_Margin+348,$Bottom_Margin);

	/*Print a column vertical line */
	$pdf->line($Left_Margin+388, $TopOfColHeadings+12,$Left_Margin+388,$Bottom_Margin);

	/*Print a column vertical line */
	$pdf->line($Left_Margin+418, $TopOfColHeadings+12,$Left_Margin+418,$Bottom_Margin);

	$pdf->line($Left_Margin+448, $TopOfColHeadings+12,$Left_Margin+448,$Bottom_Margin);

	$PageNumber++;

}

function Generate_CSPDF($FromCust, $ToCust, $db){ 

$PaperSize='A4_Landscape';
$_POST['FromCust'] =strtoupper($FromCust);
$_POST['ToCust'] = strtoupper($ToCust);
$_POST['PrintPDF'] = 'Yes';

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', _('Customer Statements') );
	$pdf->addInfo('Subject', _('Statements from') . ' ' . $_POST['FromCust'] . ' ' . _('to') . ' ' . $_POST['ToCust']);
	$PageNumber = 1;
	$line_height=16;

	$FirstStatement = True;

/* Do a quick tidy up to settle any transactions that should have been settled at the time of allocation but for whatever reason weren't */
	$ErrMsg = _('There was a problem settling the old transactions.');
	$DbgMsg = _('The SQL used to settle outstanding transactions was');
	$sql = "UPDATE debtortrans SET settled=1
		WHERE ABS(debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst-debtortrans.alloc)<0.009";
	$SettleAsNec = DB_query($sql,$db, $ErrMsg, $DbgMsg);

/*Figure out who all the customers in this range are */
	$ErrMsg= _('There was a problem retrieving the customer information for the statements from the database');
	$sql = "SELECT debtorsmaster.debtorno,
			debtorsmaster.name,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtorsmaster.address6,
			debtorsmaster.lastpaid,
			debtorsmaster.lastpaiddate,
			currencies.currency,
			paymentterms.terms
		FROM debtorsmaster INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
		INNER JOIN paymentterms
			ON debtorsmaster.paymentterms=paymentterms.termsindicator
		WHERE debtorsmaster.debtorno >='" . $_POST['FromCust'] ."'
		AND debtorsmaster.debtorno <='" . $_POST['ToCust'] ."'
		ORDER BY debtorsmaster.debtorno";
	$StatementResults=DB_query($sql,$db, $ErrMsg);

	if (DB_Num_Rows($StatementResults) == 0){
		$title = _('Print Statements') . ' - ' . _('No Customers Found');
	        require('includes/header.inc');
		echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . _('Print Customer Account Statements') . '</p>';
		prnMsg( _('There were no Customers matching your selection of '). $_POST['FromCust']. ' - '.
			$_POST['ToCust'].'.' , 'error');
//		echo '</div>';
		include('includes/footer.inc');
		exit();
	}

	while ($StmtHeader=DB_fetch_array($StatementResults)){	 /*loop through all the customers returned */

	/*now get all the outstanding transaction ie Settled=0 */
		$ErrMsg =  _('There was a problem retrieving the outstanding transactions for') . ' ' .	$StmtHeader['name'] . ' '. _('from the database') . '.';
		$sql = "SELECT systypes.typename,
				debtortrans.transno,
				debtortrans.trandate,
				debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst as total,
				debtortrans.alloc,
                                debtortrans.order_,
				debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst-debtortrans.alloc as ostdg
			FROM debtortrans INNER JOIN systypes
				ON debtortrans.type=systypes.typeid
			WHERE debtortrans.debtorno='" . $StmtHeader['debtorno'] . "'
			AND debtortrans.settled=0
			ORDER BY debtortrans.id";

		$OstdgTrans=DB_query($sql,$db, $ErrMsg);

	   	$NumberOfRecordsReturned = DB_num_rows($OstdgTrans);

/*now get all the settled transactions which were allocated this month */
		$ErrMsg = _('There was a problem retrieving the transactions that were settled over the course of the last month for'). ' ' . $StmtHeader['name'] . ' ' . _('from the database');
	   	if ($_SESSION['Show_Settled_LastMonth']==1){
	   		$sql = "SELECT DISTINCT debtortrans.id,
						systypes.typename,
						debtortrans.transno,
						debtortrans.trandate,
						debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst AS total,
						debtortrans.alloc,
                                                debtortrans.order_,
						debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst-debtortrans.alloc AS ostdg
				FROM debtortrans INNER JOIN systypes
					ON debtortrans.type=systypes.typeid
				INNER JOIN custallocns
					ON (debtortrans.id=custallocns.transid_allocfrom
						OR debtortrans.id=custallocns.transid_allocto)
				WHERE custallocns.datealloc >='" .
					Date('Y-m-d',Mktime(0,0,0,Date('m')-1,Date('d'),Date('y'))) . "'
				AND debtortrans.debtorno='" . $StmtHeader['debtorno'] . "'
				AND debtortrans.settled=1
				ORDER BY debtortrans.id";

			$SetldTrans=DB_query($sql,$db, $ErrMsg);
			$NumberOfRecordsReturned += DB_num_rows($SetldTrans);

	   	}

	  	if ( $NumberOfRecordsReturned >=1){

		/* Then there's a statement to print. So print out the statement header from the company record */

	      		$PageNumber =1;

			if ($FirstStatement==True){
				$FirstStatement=False;
	      		} else {
				$pdf->newPage();
	      		}

	      		include('includes/PDFStatementPageHeader.inc');


			if ($_SESSION['Show_Settled_LastMonth']==1){
				if (DB_num_rows($SetldTrans)>=1) {

					$FontSize=12;
					$YPos -= $line_height;
					$pdf->addText($Left_Margin+1,$YPos+15,$FontSize, _('Settled Transactions'));

					$YPos -= ($line_height);

					$FontSize=10;

					while ($myrow=DB_fetch_array($SetldTrans)){

						$DisplayAlloc = number_format($myrow['alloc'],2);
						$DisplayOutstanding = number_format($myrow['ostdg'],2);

						$FontSize=9;

						$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos,60,$FontSize,$myrow['typename'], 'left');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+110,$YPos,50,$FontSize,$myrow['order_'], 'left');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+211,$YPos,50,$FontSize,ConvertSQLDate($myrow['trandate']), 'left');

						$FontSize=10;
						if ($myrow['total']>0){
							$DisplayTotal = number_format($myrow['total'],2);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,60,$FontSize,$DisplayTotal, 'right');
						} else {
							$DisplayTotal = number_format(-$myrow['total'],2);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+382,$YPos,60,$FontSize,$DisplayTotal, 'right');
						}
						$LeftOvers = $pdf->addTextWrap($Left_Margin+459,$YPos,60,$FontSize,$DisplayAlloc, 'right');
						$LeftOvers = $pdf->addTextWrap($Left_Margin+536,$YPos,60,$FontSize,$DisplayOutstanding, 'right');

						if ($YPos-$line_height <= $Bottom_Margin){
		/* head up a new statement page */

							$PageNumber++;
							$pdf->newPage();
							include ('includes/PDFStatementPageHeader.inc');
						} //end if need a new page headed up

						/*increment a line down for the next line item */
						$YPos -= ($line_height);

					} //end while there transactions settled this month to print out
				}
			} // end of if there are transaction that were settled this month

	      		if (DB_num_rows($OstdgTrans)>=1){

		      		$YPos -= ($line_height);
				if ($YPos-(2 * $line_height) <= $Bottom_Margin){
					$PageNumber++;
					$pdf->newPage();
					include ('includes/PDFStatementPageHeader.inc');
				}
			/*Now the same again for outstanding transactions */

			$FontSize=12;
			$pdf->addText($Left_Margin+1,$YPos+20,$FontSize, _('Outstanding Transactions') );
			$YPos -= $line_height;

			while ($myrow=DB_fetch_array($OstdgTrans)){

				$DisplayAlloc = number_format($myrow['alloc'],2);
				$DisplayOutstanding = number_format($myrow['ostdg'],2);

				$FontSize=9;
				$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos,60,$FontSize,$myrow['typename'], 'left');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+110,$YPos,50,$FontSize,$myrow['order_'], 'left');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+211,$YPos,50,$FontSize,ConvertSQLDate($myrow['trandate']), 'left');

				$FontSize=10;
				if ($myrow['total']>0){
					$DisplayTotal = number_format($myrow['total'],2);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+300,$YPos,55,$FontSize,$DisplayTotal, 'right');
				} else {
					$DisplayTotal = number_format(-$myrow['total'],2);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+382,$YPos,55,$FontSize,$DisplayTotal, 'right');
				}

				$LeftOvers = $pdf->addTextWrap($Left_Margin+459,$YPos,59,$FontSize,$DisplayAlloc, 'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+536,$YPos,60,$FontSize,$DisplayOutstanding, 'right');

				/*Now show also in the remittance advice sectin */
				$FontSize=8;
				$LeftOvers = $pdf->addTextWrap($Perforation+10,$YPos,30,$FontSize,$myrow['typename'], 'left');
				$LeftOvers = $pdf->addTextWrap($Perforation+75,$YPos,30,$FontSize,$myrow['order_'], 'left');
				$LeftOvers = $pdf->addTextWrap($Perforation+90,$YPos,60,$FontSize,$DisplayOutstanding, 'right');

				if ($YPos-$line_height <= $Bottom_Margin){
		/* head up a new statement page */

					$PageNumber++;
					$pdf->newPage();
					include ('includes/PDFStatementPageHeader.inc');
				} //end if need a new page headed up

				/*increment a line down for the next line item */
				$YPos -= ($line_height);

			} //end while there are outstanding transaction to print
		} // end if there are outstanding transaction to print


		/* check to see enough space left to print the totals/footer
		which is made up of 2 ruled lines, the totals/aging another 2 lines
		and details of the last payment made - in all 6 lines */
		if (($YPos-$Bottom_Margin)<(4*$line_height)){

		/* head up a new statement/credit note page */
			$PageNumber++;
			$pdf->newPage();
		include ('includes/PDFStatementPageHeader.inc');
		}
			/*Now figure out the aged analysis for the customer under review */

		$SQL = "SELECT debtorsmaster.name,
				currencies.currency,
				paymentterms.terms,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription,
				SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
				debtortrans.ovdiscount - debtortrans.alloc) AS balance,
				SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
					CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >=
					paymentterms.daysbeforedue
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				ELSE
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . interval('1', 'MONTH') . "), " . interval('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= 0
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				END) AS due,
				Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
					AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >=
					(paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				ELSE
					CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . interval('1','MONTH') . "), " . interval('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') .")) >= " . $_SESSION['PastDueDays1'] . ")
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				END) AS overdue1,
				Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
					CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
					AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue +
					" . $_SESSION['PastDueDays2'] . ")
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				ELSE
					CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . interval('1','MONTH') . "), " .
					interval('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . "))
					>= " . $_SESSION['PastDueDays2'] . ")
					THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight +
					debtortrans.ovdiscount - debtortrans.alloc
					ELSE 0 END
				END) AS overdue2
			FROM debtorsmaster INNER JOIN paymentterms
				ON debtorsmaster.paymentterms = paymentterms.termsindicator
			INNER JOIN currencies
				ON debtorsmaster.currcode = currencies.currabrev
			INNER JOIN holdreasons
				ON debtorsmaster.holdreason = holdreasons.reasoncode
			INNER JOIN debtortrans
				ON debtorsmaster.debtorno = debtortrans.debtorno
			WHERE
				debtorsmaster.debtorno = '" . $StmtHeader['debtorno'] . "'
			GROUP BY
				debtorsmaster.name,
				currencies.currency,
				paymentterms.terms,
				paymentterms.daysbeforedue,
				paymentterms.dayinfollowingmonth,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription";

			$ErrMsg = 'The customer details could not be retrieved by the SQL because';
			$CustomerResult = DB_query($SQL,$db);

		/*there should be only one record returned ?? */
			$AgedAnalysis = DB_fetch_array($CustomerResult,$db);


		/*Now print out the footer and totals */

			$DisplayDue = number_format($AgedAnalysis['due']-$AgedAnalysis['overdue1'],2);
			$DisplayCurrent = number_format($AgedAnalysis['balance']-$AgedAnalysis['due'],2);
			$DisplayBalance = number_format($AgedAnalysis['balance'],2);
			$DisplayOverdue1 = number_format($AgedAnalysis['overdue1']-$AgedAnalysis['overdue2'],2);
			$DisplayOverdue2 = number_format($AgedAnalysis['overdue2'],2);


			$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+(4*$line_height),$Left_Margin,$Bottom_Margin+(4*$line_height));

			$FontSize=10;


			$pdf->addText($Left_Margin+75, ($Bottom_Margin+10)+(3*$line_height)+4, $FontSize, _('Current'). ' ');
			$pdf->addText($Left_Margin+158, ($Bottom_Margin+10)+(3*$line_height)+4, $FontSize, _('Past Due').' ');
			$pdf->addText($Left_Margin+242, ($Bottom_Margin+10)+(3*$line_height)+4, $FontSize, $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' ' . _('days') );
			$pdf->addText($Left_Margin+315, ($Bottom_Margin+10)+(3*$line_height)+4, $FontSize, _('Over').' ' . $_SESSION['PastDueDays2'] . ' '. _('days'));
			$pdf->addText($Left_Margin+442, ($Bottom_Margin+10)+(3*$line_height)+4, $FontSize, _('Total Balance') );

			$LeftOvers = $pdf->addTextWrap($Left_Margin+37, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayCurrent, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+130, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayDue, 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+222, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayOverdue1, 'right');

			$LeftOvers = $pdf->addTextWrap($Left_Margin+305, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayOverdue2, 'right');

			$LeftOvers = $pdf->addTextWrap($Left_Margin+432, $Bottom_Margin+(2*$line_height)+8,70,$FontSize,$DisplayBalance, 'right');


			/*draw a line under the balance info */
			$YPos = $Bottom_Margin+(2*$line_height);
			$pdf->line($Left_Margin, $YPos,$Perforation,$YPos);


			if (strlen($StmtHeader['lastpaiddate'])>1 && $StmtHeader['lastpaid']!=0){
				$pdf->addText($Left_Margin+5, $Bottom_Margin+13, $FontSize, _('Last payment received').' ' . ConvertSQLDate($StmtHeader['lastpaiddate']) .
					'    ' . _('Amount received was').' ' . number_format($StmtHeader['lastpaid'],2));

			}
			/*also show the total due in the remittance section */
			if ($AgedAnalysis['balance']>0){ /*No point showing a negative balance for payment! */
					$FontSize=8;
					$LeftOvers = $pdf->addTextWrap($Perforation+2, $Bottom_Margin+(2*$line_height)+8,40,$FontSize, _('Payment'), 'left');
					$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-90, $Bottom_Margin+(2*$line_height)+8,88,$FontSize,$DisplayBalance, 'right');

			}

		} /* end of check to see that there were statement transactons to print */

	} /* end loop to print statements */
        $FileName='Customer_Statement_' . date('Y-m-d') . '.pdf';
        $pdf->Output($FileName, 'F');//UldisN
        $pdf->__destruct(); //UldisN
        return $FileName;
}

?>