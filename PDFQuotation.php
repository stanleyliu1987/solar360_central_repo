<?php

/* $Id: PDFQuotation.php 4500 2011-02-27 09:18:42Z daintree $*/

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

//Get Out if we have no order number to work with
If (!isset($_GET['QuotationNo']) || $_GET['QuotationNo']==""){
        $title = _('Select Quotation To Print');
        include('includes/header.inc');
        echo '<div class="centre"><br><br><br>';
        prnMsg( _('Select a Quotation to Print before calling this page') , 'error');
        echo '<br><br><br><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'&Quotations=Quotes_Only">' . _('Quotations') . '</a></li>
                </td></tr></table></div><br><br><br>';
        include('includes/footer.inc');
        exit();
}

/*retrieve the order details from the database to print */
$ErrMsg = _('There was a problem retrieving the quotation header details for Order Number') . ' ' . $_GET['QuotationNo'] . ' ' . _('from the database');

$sql = "SELECT salesorders.customerref,
		salesorders.comments,
		salesorders.orddate,
		salesorders.deliverto,
		salesorders.deladd1,
		salesorders.deladd2,
		salesorders.deladd3,
		salesorders.deladd4,
		salesorders.deladd5,
		salesorders.deladd6,
                salesorders.contactphone,
		debtorsmaster.name,
		debtorsmaster.address1,
		debtorsmaster.address2,
		debtorsmaster.address3,
		debtorsmaster.address4,
		debtorsmaster.address5,
		debtorsmaster.address6,
		shippers.shippername,
		salesorders.printedpackingslip,
		salesorders.datepackingslipprinted,
		salesorders.branchcode,
                salesorders.freightcost,
		locations.taxprovinceid,
		locations.locationname,
                custbranch.phoneno,
                custbranch.contactname
	FROM salesorders,
		debtorsmaster,
		shippers,
		locations,
                custbranch
	WHERE salesorders.debtorno=debtorsmaster.debtorno
	AND salesorders.shipvia=shippers.shipper_id
	AND salesorders.fromstkloc=locations.loccode
	AND salesorders.quotation=1
        AND custbranch.debtorno=salesorders.debtorno
        AND custbranch.branchcode=salesorders.branchcode
	AND salesorders.orderno='" . $_GET['QuotationNo'] ."'";

$result=DB_query($sql,$db, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($result)==0){
        $title = _('Print Quotation Error');
        include('includes/header.inc');
         echo '<div class="centre"><br><br><br>';
        prnMsg( _('Unable to Locate Quotation Number') . ' : ' . $_GET['QuotationNo'] . ' ', 'error');
        echo '<br><br><br><table class="table_index"><tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSalesOrder.php?'. SID .'&Quotations=Quotes_Only">' . _('Outstanding Quotations') . '</a></li>
                </td></tr></table></div><br><br><br>';
        include('includes/footer.inc');
        exit;
} elseif (DB_num_rows($result)==1){ /*There is only one order header returned - thats good! */

        $myrow = DB_fetch_array($result);
}
$customerName=$myrow['name'];

/*retrieve the order details from the database to print */

/* Then there's an order to print and its not been printed already (or its been flagged for reprinting/ge_Width=807;
)
LETS GO */
$PaperSize = 'A4';
include('includes/PDFStarter.php');
$pdf->addInfo('Title', _('Customer Quotation') );
$pdf->addInfo('Subject', _('Quotation') . ' ' . $_GET['QuotationNo']);
$FontSize=12;
$PageNumber = 1;
$line_height=16;
$Bottom_Margin=270;
// $pdf->selectFont('./fonts/Helvetica.afm');

/* Now ... Has the order got any line items still outstanding to be invoiced */

$ErrMsg = _('There was a problem retrieving the quotation line details for quotation Number') . ' ' .
	$_GET['QuotationNo'] . ' ' . _('from the database');

$sql = "SELECT salesorderdetails.stkcode,
		stockmaster.description,
		salesorderdetails.quantity,
		salesorderdetails.qtyinvoiced,
		salesorderdetails.unitprice,
		salesorderdetails.discountpercent,
		stockmaster.taxcatid,
		salesorderdetails.narrative
	FROM salesorderdetails INNER JOIN stockmaster
		ON salesorderdetails.stkcode=stockmaster.stockid
	WHERE salesorderdetails.orderno='" . $_GET['QuotationNo'] . "'";

$result=DB_query($sql,$db, $ErrMsg);

$ListCount = 0; // UldisN

if (DB_num_rows($result)>0){
	/*Yes there are line items to start the ball rolling with a page header */
        $FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/Invoice.xml');
	include('includes/PDFQuotationPageHeader.inc');
        $line_height = $FormDesign->LineHeight;
	$QuotationTotal =0;
	$QuotationTotalEx=0;
	$TaxTotal=0;

	while ($myrow2=DB_fetch_array($result)){

        $ListCount ++;

		if ((strlen($myrow2['narrative']) >200 AND $YPos-$line_height <= 75)
			OR (strlen($myrow2['narrative']) >1 AND $YPos-$line_height <= 62)
			OR $YPos-$line_height <= 50){
		/* We reached the end of the page so finsih off the page and start a newy */
			$PageNumber++;
			include ('includes/PDFQuotationPageHeader.inc');

		} //end if need a new page headed up

		$DisplayQty = number_format($myrow2['quantity'],2);
		$DisplayPrevDel = number_format($myrow2['qtyinvoiced'],2);
		$DisplayPrice = number_format($myrow2['unitprice'],2);
		$DisplayDiscount = number_format($myrow2['discountpercent']*100,2) . '%';
		$SubTot =  $myrow2['unitprice']*$myrow2['quantity']*(1-$myrow2['discountpercent']);
		$TaxProv = $myrow['taxprovinceid'];
		$TaxCat = $myrow2['taxcatid'];
		$Branch = $myrow['branchcode'];
		$sql3 = " select taxgrouptaxes.taxauthid from taxgrouptaxes INNER JOIN custbranch ON taxgrouptaxes.taxgroupid=custbranch.taxgroupid WHERE custbranch.branchcode='" .$Branch ."'";
		$result3=DB_query($sql3,$db, $ErrMsg);
		while ($myrow3=DB_fetch_array($result3)){
			$TaxAuth = $myrow3['taxauthid'];
		}

		$sql4 = "SELECT * FROM taxauthrates WHERE dispatchtaxprovince='" .$TaxProv ."' AND taxcatid='" .$TaxCat ."' AND taxauthority='" .$TaxAuth ."'";
		$result4=DB_query($sql4,$db, $ErrMsg);
		while ($myrow4=DB_fetch_array($result4)){
			$TaxClass = 100 * $myrow4['taxrate'];
		}

		$DisplayTaxClass = $TaxClass . "%";
		$TaxAmount =  (($SubTot/100)*(100+$TaxClass))-$SubTot;
		$DisplayTaxAmount = number_format($TaxAmount,2);

		$LineTotal = $SubTot;
		$DisplayTotal = number_format($LineTotal,2);
                $Narrative=htmlspecialchars_decode($myrow2['narrative']);
		
		
               /* display item details*/
		$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column1->x, $YPos, $FormDesign->Data->Column1->Length, $FormDesign->Data->Column1->FontSize, $myrow2['stkcode'],'left');
                if(strlen($Narrative)>0){
                    $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x, $YPos, $FormDesign->Data->Column2->Length, $FormDesign->Data->Column2->FontSize, $myrow2['description'].'  -  '.$Narrative,'left');
                }
                else{
                    $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x, $YPos, $FormDesign->Data->Column2->Length, $FormDesign->Data->Column2->FontSize, $myrow2['description'],'left');
                }
                if (strlen($LeftOvers)>1){
				$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x, $YPos-10, $FormDesign->Data->Column2->Length, $FormDesign->Data->Column2->FontSize, $LeftOvers,'left');
			}

                        
                $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column3->x, $YPos, $FormDesign->Data->Column3->Length, $FormDesign->Data->Column3->FontSize, $DisplayQty,'left');
                $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column4->x, $YPos, $FormDesign->Data->Column4->Length, $FormDesign->Data->Column4->FontSize, $DisplayPrice,'right');
          //      $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column5->x, $YPos, $FormDesign->Data->Column5->Length, $FormDesign->Data->Column5->FontSize, $myrow2['units'],'left');
                $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column5->x, $YPos, $FormDesign->Data->Column5->Length, $FormDesign->Data->Column5->FontSize, $DisplayDiscount,'right');
                $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column6->x, $YPos, $FormDesign->Data->Column6->Length, $FormDesign->Data->Column6->FontSize, $DisplayTotal,'right');
                
                
                $YPos -= 2*$line_height;
                  	if ($YPos <= $Bottom_Margin){

					/* head up a new invoice/credit note page */
					/*draw the vertical column lines right to the bottom */
					//PrintLinesToBottom ();
					//include('includes/PDFQuotationPageHeader.inc');
                            include ('includes/PDFTransPageHeaderPortrait.inc');
				} 
              $DisplayItemTotal+=$LineTotal;
       
	} //end while there are line items to print out
        
        $YPos -= $line_height;
       /* check to see enough space left to print the 4 lines for the totals/footer */
	if (($YPos-$Bottom_Margin)<($line_height)){
	//PrintLinesToBottom ();
	include ('includes/PDFTransPageHeaderPortrait.inc');
		}
        /*Print out the invoice text entered */
	$YPos = $Bottom_Margin+(14*$line_height);
        $pdf->line($FormDesign->DrawVerticalLine3->x1,$FormDesign->DrawVerticalLine3->y1,$FormDesign->DrawVerticalLine3->x2,$FormDesign->DrawVerticalLine3->y2);
	/* Print out the payment terms */
	$FontSize=$FormDesign->PaymentTerms->Line1->FontSize;
	$pdf->addText($FormDesign->PaymentTerms->Caption->x, $FormDesign->PaymentTerms->Caption->y, $FormDesign->PaymentTerms->Caption->FontSize, _('Terms and conditions:'));
        $LeftOvers='';
        $TermsCondition1="Payment of this invoice is considered as acceptance of our Terms and Conditions. Refer to our website for full list of our terms. Delivery can take up to 5 days after the full payment has been received."; 
        $LeftOvers=$pdf->addTextWrap($FormDesign->PaymentTerms->Line1->x,$FormDesign->PaymentTerms->Line1->y,180,$FormDesign->PaymentTerms->Line1->FontSize,$TermsCondition1); 
        $tempSpace=$FormDesign->PaymentTerms->Line1->y-10;
	    while (strlen($LeftOvers)>1){		
	    $LeftOvers = $pdf->addTextWrap($FormDesign->PaymentTerms->Line1->x,
	                 $tempSpace,
	                 190,$FontSize,$LeftOvers);
	                 $tempSpace-=10;
		}
                
        $TermsCondition2=" Credit Card surcharge is applied at the rate of 1.5% on Visa & MasterCard, 3.5% on AMEX. This quote is valid for 7 days."; 
        $LeftOvers=$pdf->addTextWrap($FormDesign->PaymentTerms->Line1->x,$tempSpace-10,180,$FormDesign->PaymentTerms->Line1->FontSize,$TermsCondition2); 
        $tempSpace=$tempSpace-20;
	    while (strlen($LeftOvers)>1){		
	    $LeftOvers = $pdf->addTextWrap($FormDesign->PaymentTerms->Line1->x,
	                 $tempSpace,
	                 190,$FontSize,$LeftOvers);
	                 $tempSpace-=10;
		}
                
    /* Final Calculation */
            $TotalGSTAmount=($TaxClass*($DisplayItemTotal+$myrow['freightcost']))/100;
            $pdf->SetTextColor(0,100,0);
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->SubTotal->x, $FormDesign->InvoiceTotal->SubTotal->y,$FormDesign->InvoiceTotal->SubTotal->width, $FormDesign->InvoiceTotal->SubTotal->FontSize, _('Sale Amount'),'right');
            $pdf->addTextWrap($FormDesign->InvoiceTotal->Tax->x, $FormDesign->InvoiceTotal->Tax->y, $FormDesign->InvoiceTotal->Tax->width, $FormDesign->InvoiceTotal->Tax->FontSize, _('Freight'),'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->Ampaid->x, $FormDesign->InvoiceTotal->Ampaid->y, $FormDesign->InvoiceTotal->Ampaid->width, $FormDesign->InvoiceTotal->Ampaid->FontSize, _('GST'),'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotal->ToInv->x, $FormDesign->InvoiceTotal->ToInv->y, $FormDesign->InvoiceTotal->ToInv->width, $FormDesign->InvoiceTotal->ToInv->FontSize, _('Total'),'right');
	    $pdf->SetTextColor(0);

	    $pdf->addTextWrap($FormDesign->InvoiceTotalData->SubTotalData->x, $FormDesign->InvoiceTotalData->SubTotalData->y, $FormDesign->InvoiceTotalData->SubTotalData->width, $FormDesign->InvoiceTotalData->SubTotalData->FontSize,number_format($DisplayItemTotal,2),'right');
	    $pdf->addTextWrap($FormDesign->InvoiceTotalData->TaxData->x, $FormDesign->InvoiceTotalData->TaxData->y, $FormDesign->InvoiceTotalData->TaxData->width,$FormDesign->InvoiceTotalData->TaxData->FontSize, number_format($myrow['freightcost'],2),'right');
            $pdf->addTextWrap($FormDesign->InvoiceTotalData->AmpaidData->x, $FormDesign->InvoiceTotalData->AmpaidData->y,$FormDesign->InvoiceTotalData->AmpaidData->width, $FormDesign->InvoiceTotalData->AmpaidData->FontSize, number_format($TotalGSTAmount,2),'right');
        
	/*vertical to separate totals from comments and ROMALPA */
		$DisplayTotalAmount=$DisplayItemTotal+$myrow['freightcost']+$TotalGSTAmount;
		$YPos+=10;
	        $pdf->addTextWrap($FormDesign->InvoiceTotalData->ToInvData->x, $FormDesign->InvoiceTotalData->ToInvData->y, $FormDesign->InvoiceTotalData->ToInvData->width,$FormDesign->InvoiceTotalData->ToInvData->FontSize, number_format($DisplayTotalAmount,2),'right');
		$FontSize=8;
                $CustomerReference=$myrow['customerref'];
                $Comments=$myrow['comments'];
                
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
	            $LeftOvers = $pdf->addTextWrap($FormDesign->Comments->x, $FormDesign->Comments->y, $FormDesign->Comments->Length, $FormDesign->Comments->FontSize, $LeftOvers,'left');
                    }
                  }
        /** Display Payment Methods **/          
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
	$pdf->addText($FormDesign->PaymentMethod->Caption->x, $FormDesign->PaymentMethod->Caption->y, $FormDesign->PaymentMethod->Caption->FontSize, _('Payment Methods'));
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail1->x, $FormDesign->PaymentMethod->AccountDetail1->y, $FormDesign->PaymentMethod->AccountDetail1->FontSize, _('Credit Card at time of order'));
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail2->x, $FormDesign->PaymentMethod->AccountDetail2->y, $FormDesign->PaymentMethod->AccountDetail2->FontSize, _('For Direct Deposits'));
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail3->x, $FormDesign->PaymentMethod->AccountDetail3->y, $FormDesign->PaymentMethod->AccountDetail3->FontSize, _('Please quote Invoice number on payment:'));
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail4->x, $FormDesign->PaymentMethod->AccountDetail4->y, $FormDesign->PaymentMethod->AccountDetail4->FontSize, $DefaultBank);
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail5->x, $FormDesign->PaymentMethod->AccountDetail5->y, $FormDesign->PaymentMethod->AccountDetail5->FontSize, $DefaultBankAccountCode); 
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail6->x, $FormDesign->PaymentMethod->AccountDetail6->y, $FormDesign->PaymentMethod->AccountDetail6->FontSize, $DefaultBankAccountNumber); 
        $pdf->addText($FormDesign->PaymentMethod->AccountDetail7->x, $FormDesign->PaymentMethod->AccountDetail7->y, $FormDesign->PaymentMethod->AccountDetail7->FontSize, $DefaultAccName); 

} /*end if there are line details to show on the quotation*/

unset($DisplayItemTotal);
//$pdfcode = $pdf->output('PDFQuotation.pdf', 'I');
//$len = strlen($pdfcode);
if ($ListCount == 0){
        $title = _('Print Quotation Error');
        include('includes/header.inc');
        echo '<p>'. _('There were no items on the quotation') . '. ' . _('The quotation cannot be printed').
                '<br><a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . '&Quotation=Quotes_only">'. _('Print Another Quotation').
                '</a>' . '<br>'. '<a href="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</a>';
        include('includes/footer.inc');
	exit;
} else {
/*
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=Quotation.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
//echo 'here';
	$pdf->Output('PDFQuotation.pdf', 'I');
*/
    $pdf->OutputI('W'.$_GET['QuotationNo'] . '-'.$customerName  . '.pdf');//UldisN
    $pdf->__destruct(); //UldisN
}
?>