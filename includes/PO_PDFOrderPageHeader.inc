<?php
/* $Id: PO_PDFOrderPageHeader.inc 4529 2011-04-01 09:52:49Z daintree $*/
/*
	R & OS PHP-PDF class code to set up a new page
	a new page is implicit on the establishment of a new pdf object so
	only for subsequent pages
*/
if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin;
$pdf->SetLineWidth($FormDesign->LineWidth);
$pdf->SetTextColor(0,100,0);
if(isset($MakePDFDocket)){
$pdf->addText($FormDesign->POTitle->x, $FormDesign->POTitle->y,$FormDesign->POTitle->FontSize, _('Delivery Docket'));  
}
elseif(isset($MakePDFRCTI)){
$pdf->addText($FormDesign->POTitle->x+19, $FormDesign->POTitle->y,10, _('Recipient Created'));     
$pdf->addText($FormDesign->POTitle->x, $FormDesign->POTitle->y-14,$FormDesign->POTitle->FontSize, _('Tax Invoice'));      
}
else{
$pdf->addText($FormDesign->POTitle->x, $FormDesign->POTitle->y,$FormDesign->POTitle->FontSize, _('Purchase Order'));
}
$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin+$FormDesign->logo->x,$Page_Height- $FormDesign->logo->y,$FormDesign->logo->width,$FormDesign->logo->height);
$pdf->SetTextColor(0);

/*Paid Sign on PO as Watermark*/
if ($POHeader['status'] == 'Completed'){ 
$sql = "SELECT purchorderdetails.supinvref FROM purchorderdetails inner join supptrans on  supptrans.suppreference=purchorderdetails.supinvref 
	WHERE purchorderdetails.orderno ='" . $OrderNo ."' and supptrans.settled=1 group by purchorderdetails.supinvref";
$resultStatusPO=DB_query($sql,$db);
while ($SuppInvRef = DB_fetch_array($resultStatusPO)){ 
    $SuppInvRefList.= $SuppInvRef['supinvref'].' ';
}
$SuppInvRefList='Paid To '.$SuppInvRefList;
// Get the page width/height
$myPageWidth = $pdf->getPageWidth();
$myPageHeight = $pdf->getPageHeight();
// Find the middle of the page and adjust.
$myX = ( $myPageWidth / 2 ) - 170;
$myY = ( $myPageHeight / 2 ) + 100;
// Set the transparency of the text to really light
$pdf->SetAlpha(0.2);
// Rotate 45 degrees and write the watermarking text
$pdf->StartTransform();
$pdf->Rotate(45, $myX, $myY);
$pdf->SetFontSize(75);

if(DB_num_rows($resultStatusPO)!=0){
$pdf->Text($myX, $myY, $SuppInvRefList);
$pdf->StopTransform();
$pdf->SetAlpha(1);
$pdf->setPageMark();
}
else{
$pdf->Text($myX, $myY, 'Completed');
$pdf->StopTransform();
$pdf->SetAlpha(1);
$pdf->setPageMark();   
}
}


/*Now print out the company name and address */
if(!isset($MakePDFRCTI)) {
$pdf->addText($FormDesign->CompanyName->x, $FormDesign->CompanyName->y, $FormDesign->CompanyName->FontSize, $_SESSION['CompanyRecord']['coyname']);
$pdf->addText($FormDesign->CompanyAddress->Line1->x, $FormDesign->CompanyAddress->Line1->y, $FormDesign->CompanyAddress->Line1->FontSize,  
$_SESSION['CompanyRecord']['regoffice1'].' '.
$_SESSION['CompanyRecord']['regoffice2']
.'   /   '. _('PHONE'). ': '. $_SESSION['CompanyRecord']['telephone']);

$pdf->addText($FormDesign->CompanyAddress->Line2->x, $FormDesign->CompanyAddress->Line2->y, $FormDesign->CompanyAddress->Line2->FontSize,  
$_SESSION['CompanyRecord']['regoffice3'].' '.
$_SESSION['CompanyRecord']['regoffice4'].' '.
$_SESSION['CompanyRecord']['regoffice5'].'          /    '.
_('ABN') . ': ' .  $_SESSION['CompanyRecord']['companynumber']);
}

$pdf->line($FormDesign->DrawLine->x1,$FormDesign->DrawLine->y1,$FormDesign->DrawLine->x2,$FormDesign->DrawLine->y2);
/*Purchase Number */
$pdf->SetTextColor(0,100,0);
if(isset($MakePDFDocket)){
$pdf->addText($FormDesign->OrderNumber->x, $FormDesign->OrderNumber->y,$FormDesign->OrderNumber->FontSize, _('Delivery No: ') .'W'.$POHeader['ref_salesorder'].'-'.$OrderNo);   
}
elseif(isset($MakePDFRCTI)){
$pdf->addText($FormDesign->OrderNumber->x, $FormDesign->OrderNumber->y,$FormDesign->OrderNumber->FontSize, _('Invoice No: ') .$POHeader['ref_number']);    
}
else{
$pdf->addText($FormDesign->OrderNumber->x, $FormDesign->OrderNumber->y,$FormDesign->OrderNumber->FontSize, _('Purchase No: ') .$POHeader['ref_number']);
}


if(!isset($MakePDFDocket)){
/*Now the Order date */
$pdf->addText($FormDesign->OrderDate->x, $FormDesign->OrderDate->y, $FormDesign->OrderDate->FontSize, _('Date:').' '.ConvertSQLDate($POHeader['trandate']));
$pdf->SetTextColor(0); 

/*Now the Supplier details */
$supaddress2=empty($POHeader['suppdeladdress2'])?$POHeader['address2']:$POHeader['suppdeladdress2'];
$supaddress3=empty($POHeader['suppdeladdress3'])?$POHeader['address3']:$POHeader['suppdeladdress3'];
$supaddress4=empty($POHeader['suppdeladdress4'])?$POHeader['address4']:$POHeader['suppdeladdress4'];
$supptel=empty($POHeader['supptel'])?$POHeader['telephone']:$POHeader['supptel'];
$pdf->addText($FormDesign->SupplierAddress->Caption->x, $FormDesign->SupplierAddress->Caption->y, $FormDesign->SupplierAddress->Caption->FontSize, _('Supplier').':');
if(!isset($MakePDFRCTI)){
$pdf->addText($FormDesign->SupplierAddress->Caption2->x, $FormDesign->SupplierAddress->Caption2->y, $FormDesign->SupplierAddress->Caption2->FontSize, _('Warehouse').':');
}
$pdf->addText($FormDesign->SupplierAddress->Line1->x, $FormDesign->SupplierAddress->Line1->y, $FormDesign->SupplierAddress->Line1->FontSize, substr($POHeader['suppname'],0,35));
$pdf->addText($FormDesign->SupplierAddress->Line2->x, $FormDesign->SupplierAddress->Line2->y, $FormDesign->SupplierAddress->Line2->FontSize, empty($POHeader['suppdeladdress1'])?$POHeader['address1']:$POHeader['suppdeladdress1']);
$pdf->addText($FormDesign->SupplierAddress->Line3->x, $FormDesign->SupplierAddress->Line3->y, $FormDesign->SupplierAddress->Line3->FontSize, $supaddress2.' '.$supaddress3.' '.$supaddress4);

/*Now the delivery details */
if(!isset($MakePDFRCTI)){
$pdf->addText($FormDesign->SupplierAddress->Line4->x, $FormDesign->SupplierAddress->Line4->y, $FormDesign->SupplierAddress->Line4->FontSize, _('PHONE').' '.$supptel);    
$pdf->addText($FormDesign->DeliveryAddress->Caption->x, $FormDesign->DeliveryAddress->Caption->y, $FormDesign->DeliveryAddress->Caption->FontSize, _('Ship To') . ':' );
$pdf->addText($FormDesign->DeliveryAddress->Line1->x, $FormDesign->DeliveryAddress->Line1->y, $FormDesign->DeliveryAddress->Line1->FontSize, substr($POHeader['contact'],0,35));
$pdf->addText($FormDesign->DeliveryAddress->Line2->x, $FormDesign->DeliveryAddress->Line2->y, $FormDesign->DeliveryAddress->Line2->FontSize, $POHeader['deladd1']);
$pdf->addText($FormDesign->DeliveryAddress->Line3->x, $FormDesign->DeliveryAddress->Line3->y, $FormDesign->DeliveryAddress->Line3->FontSize, $POHeader['deladd2'].' '.$POHeader['deladd3'].' '.$POHeader['deladd4']);
$pdf->addText($FormDesign->DeliveryAddress->Line4->x, $FormDesign->DeliveryAddress->Line4->y, $FormDesign->DeliveryAddress->Line4->FontSize, empty($POHeader['tel'])?'':_('PHONE').' '.$POHeader['tel']);
}
else{
$pdf->addText($FormDesign->SupplierAddress->Line4->x, $FormDesign->SupplierAddress->Line4->y, $FormDesign->SupplierAddress->Line4->FontSize, _('ABN: ').' '.$POHeader['taxref']);    
$pdf->addText($FormDesign->DeliveryAddress->Caption->x, $FormDesign->DeliveryAddress->Caption->y, $FormDesign->DeliveryAddress->Caption->FontSize, _('Customer/') );
$pdf->addText($FormDesign->DeliveryAddress->Caption->x, $FormDesign->DeliveryAddress->Caption->y-15, $FormDesign->DeliveryAddress->Caption->FontSize, _('Recipient') . ':' );
$pdf->addText($FormDesign->DeliveryAddress->Line1->x, $FormDesign->DeliveryAddress->Line1->y, $FormDesign->DeliveryAddress->Line1->FontSize, 'Solar360');
$pdf->addText($FormDesign->DeliveryAddress->Line2->x, $FormDesign->DeliveryAddress->Line2->y, $FormDesign->DeliveryAddress->Line2->FontSize, 'Level 18, 499 St Kilda Rd');
$pdf->addText($FormDesign->DeliveryAddress->Line3->x, $FormDesign->DeliveryAddress->Line3->y, $FormDesign->DeliveryAddress->Line3->FontSize, 'Melbourne VIC 3004');
$pdf->addText($FormDesign->DeliveryAddress->Line4->x, $FormDesign->DeliveryAddress->Line4->y, $FormDesign->DeliveryAddress->Line4->FontSize, _('ABN: 99 147 163 488'));  
}




/*Draw Lines */
$pdf->SetTextColor(0,100,0);
$pdf->line($FormDesign->DrawVerticalLine->x1,$FormDesign->DrawVerticalLine->y1,$FormDesign->DrawVerticalLine->x2,$FormDesign->DrawVerticalLine->y2);
//$pdf->line($FormDesign->DrawLine1->x1,$FormDesign->DrawLine1->y1,$FormDesign->DrawLine1->x2,$FormDesign->DrawLine1->y2);
$pdf->SetTextColor(0);
}
else{
/*Now the Order date */
$pdf->addText($FormDesign->OrderDate->x, $FormDesign->OrderDate->y, $FormDesign->OrderDate->FontSize, _('Date:').' '.date("d/m/Y"));
$pdf->SetTextColor(0);

 /*Now the Supplier details */
$pdf->addText($FormDesign->SupplierAddress->Caption->x, $FormDesign->SupplierAddress->Caption->y, $FormDesign->SupplierAddress->Caption->FontSize, _('Deliver To').':');

//$pdf->addText($FormDesign->SupplierAddress->Line1->x+5, $FormDesign->SupplierAddress->Line1->y, $FormDesign->SupplierAddress->Line1->FontSize, substr($POHeader['contact'],0,35));
//$pdf->addText($FormDesign->SupplierAddress->Line2->x+5, $FormDesign->SupplierAddress->Line2->y, $FormDesign->SupplierAddress->Line2->FontSize, $POHeader['deladd1']);
//$pdf->addText($FormDesign->SupplierAddress->Line3->x+5, $FormDesign->SupplierAddress->Line3->y, $FormDesign->SupplierAddress->Line3->FontSize, $POHeader['deladd2'].' '.$POHeader['deladd3'].' '.$POHeader['deladd4']);
//$pdf->addText($FormDesign->SupplierAddress->Line4->x+5, $FormDesign->SupplierAddress->Line4->y, $FormDesign->SupplierAddress->Line4->FontSize, empty($POHeader['tel'])?'':_('PHONE').' '.$POHeader['tel']);   
//

    $ErrMsg = _('There was a problem retrieving the purchase order delivery address'). ' ' . $OrderNo .
    $sqlDocketDel="select salesorders.deladd1,
                 salesorders.deladd2,
                 salesorders.deladd3,
                 salesorders.deladd4,
                 salesorders.contactphone,
                 salesorders.deliverto,
                 salesorders.customerref,
                 custbranch.contactname
                 from 
                 purchorders,
                 salesorders,
		 debtortrans,
                 custbranch
                 where purchorders.orderno='".$OrderNo."'AND
                       purchorders.ref_salesorder = salesorders.orderno AND
                       debtortrans.order_ = salesorders.orderno AND
		       debtortrans.type=10 and
                       debtortrans.debtorno=custbranch.debtorno and
		       debtortrans.branchcode=custbranch.branchcode";
    $resultDel=DB_query($sqlDocketDel,$db, $ErrMsg);
    $PODocketDel = DB_fetch_array($resultDel);
 
$delFirstPart=  $PODocketDel['deliverto'];
$delSecondPart= $PODocketDel['contactname']; 
$salesReference= $PODocketDel['customerref'];
 
if(isset($salesReference) and $salesReference != '' ){
   $pdf->addText($FormDesign->SalesReference->Caption->x, $FormDesign->SalesReference->Caption->y, $FormDesign->SalesReference->Caption->FontSize, _('Your Ref').': '.$salesReference);
    
}
   $pdf->addText($FormDesign->SupplierAddress->Line1->x, $FormDesign->SupplierAddress->Line1->y, $FormDesign->SupplierAddress->Line1->FontSize, $delFirstPart);
if(isset($delSecondPart) and $delSecondPart!=''){
   $pdf->addText($FormDesign->SupplierAddress->Line1->x, $FormDesign->SupplierAddress->Line1->y-10, $FormDesign->SupplierAddress->Line1->FontSize, $delSecondPart);
   $pdf->addText($FormDesign->SupplierAddress->Line2->x, $FormDesign->SupplierAddress->Line2->y-10, $FormDesign->SupplierAddress->Line2->FontSize, $PODocketDel['deladd1']);
   $pdf->addText($FormDesign->SupplierAddress->Line3->x, $FormDesign->SupplierAddress->Line3->y-10, $FormDesign->SupplierAddress->Line3->FontSize, $PODocketDel['deladd2'].' '.$PODocketDel['deladd3'].' '.$PODocketDel['deladd4'].' '.$PODocketDel['deladd5'].' '.$PODocketDel['deladd6']);
   $pdf->addText($FormDesign->SupplierAddress->Line4->x, $FormDesign->SupplierAddress->Line4->y-10, $FormDesign->SupplierAddress->Line4->FontSize, _('PHONE').' '.$PODocketDel['contactphone']);
}
else{
$pdf->addText($FormDesign->SupplierAddress->Line2->x, $FormDesign->SupplierAddress->Line2->y, $FormDesign->SupplierAddress->Line2->FontSize, $PODocketDel['deladd1']);
$pdf->addText($FormDesign->SupplierAddress->Line3->x, $FormDesign->SupplierAddress->Line3->y, $FormDesign->SupplierAddress->Line3->FontSize, $PODocketDel['deladd2'].' '.$PODocketDel['deladd3'].' '.$PODocketDel['deladd4'].' '.$PODocketDel['deladd5'].' '.$PODocketDel['deladd6']);
$pdf->addText($FormDesign->SupplierAddress->Line4->x, $FormDesign->SupplierAddress->Line4->y, $FormDesign->SupplierAddress->Line4->FontSize, _('PHONE').' '.$PODocketDel['contactphone']);
}



}

/*Find the description of the payment terms and display.  If it is a preview then just insert dummy data */
if ($OrderNo != 'Preview') {
	$sql="SELECT terms FROM paymentterms where termsindicator='".$POHeader['paymentterms']."'";
	$termsresult=DB_query($sql, $db);
	$myrow=DB_fetch_array($termsresult);
	
	
			
	/* Print out the Courier Information */
//	$FontSize=$FormDesign->CourierInfo->CI->FontSize;
//	$pdf->addText($FormDesign->CourierInfo->CICaption->x, $FormDesign->CourierInfo->CICaption->y, $FormDesign->CourierInfo->CICaption->FontSize, _('Courier Information:'));
//        $LeftOvers=$pdf->addTextWrap($FormDesign->CourierInfo->CI->x,$FormDesign->CourierInfo->CI->y,300,$FormDesign->CourierInfo->CI->FontSize, $POHeader['shippername']);
        $LeftOvers='';
        
	    $tempSpace=$FormDesign->CourierInfo->CI->y-10;
	    while (strlen($LeftOvers)>1 AND $FormDesign->CourierInfo->CI->y-$line_height > $Bottom_Margin){
				
	    $LeftOvers = $pdf->addTextWrap($FormDesign->CourierInfo->CI->x,
	                 $tempSpace,
	                 180,$FontSize,$LeftOvers);
	                 $tempSpace-=10;
		}
		
	/*Term condition needs to split into mutilple lines*/
              
    $FontSize=$FormDesign->PaymentTerms->PT->FontSize;
    $Termsconditions="Purchase of these goods is governed by supply agreement.";
    $Termsconditionsfordocket="Goods have been delivered as per Solar360's Terms Conditions. Refer to our website for details.";
    $TermsconditionsforRCTI="The recipient and the supplier declare that this agreement relates to the above supplies. The recipient will issue tax invoices in 
respect of these supplies. The supplier will not issue tax invoices in respect of these supplies. The supplier acknowledges that it 
is registered for GST and that it will notify the recipient if it ceases to be registered. The recipient acknowledges that it is 
registered for GST and that it will notify the supplier if it ceases to be registered. Acceptance of this RCTI constitutes acceptance
of the terms of this written agreement. Both parties to this supply agree that they are parties to an RCTI agreement as outlined in 
GSTR 2000/10. The supplier agrees to notify the recipient if the supplier does not wish to accept the proposed agreement.";
    if(isset($MakePDFDocket)){
        $pdf->SetTextColor(0,100,0);  
        $LeftOvers=$pdf->addTextWrap($FormDesign->PaymentTerms->PTDoc->x,$FormDesign->PaymentTerms->PTDoc->y,500,$FormDesign->PaymentTerms->PTDoc->FontSize, $Termsconditionsfordocket);
        $tempSpace=$FormDesign->PaymentTerms->PTDoc->y-20;
        while (strlen($LeftOvers)>1 ){
	      $LeftOvers = $pdf->addTextWrap($FormDesign->PaymentTerms->PTDoc->x,
	                 $tempSpace,
	                 300,$FormDesign->PaymentTerms->PTDoc->FontSize,$LeftOvers);
	                 $tempSpace-=10;
		}
   
    }
    elseif(isset($MakePDFRCTI)){
        $pdf->SetTextColor(0,100,0);
        $pdf->addText($FormDesign->PaymentTerms->PTRCTICaption->x, $FormDesign->PaymentTerms->PTRCTICaption->y,$FormDesign->PaymentTerms->PTRCTICaption->FontSize, _('Written Agreement').' :  ');
        $LeftOvers=$pdf->addTextWrap($FormDesign->PaymentTerms->PTRCTI->x,$FormDesign->PaymentTerms->PTRCTI->y,550,$FormDesign->PaymentTerms->PTRCTI->FontSize, $TermsconditionsforRCTI);
        $tempSpace=$FormDesign->PaymentTerms->PTRCTI->y-10;
        while (strlen($LeftOvers)>1 ){
	      $LeftOvers = $pdf->addTextWrap($FormDesign->PaymentTerms->PTRCTI->x,
	                 $tempSpace,
	                 550,$FormDesign->PaymentTerms->PTRCTI->FontSize,$LeftOvers);
	                 $tempSpace-=10;
		}
    }
    else{
     $pdf->addText($FormDesign->PaymentTerms->PTCaption->x, $FormDesign->PaymentTerms->PTCaption->y,$FormDesign->PaymentTerms->PTCaption->FontSize, _('Terms and Conditions').' :  ');
     $LeftOvers=$pdf->addTextWrap($FormDesign->PaymentTerms->PT->x,$FormDesign->PaymentTerms->PT->y,300,$FormDesign->PaymentTerms->PT->FontSize, $Termsconditions);
     $tempSpace=$FormDesign->PaymentTerms->PT->y-20;
     while (strlen($LeftOvers)>1 ){
	      $LeftOvers = $pdf->addTextWrap($FormDesign->PaymentTerms->PT->x,
	                 $tempSpace,
	                 300,$FormDesign->PaymentTerms->PT->FontSize,$LeftOvers);
	                 $tempSpace-=10;
		}
    }
   		
		
} else {
	$pdf->addText($FormDesign->PaymentTerms->x, $FormDesign->PaymentTerms->y, $FormDesign->PaymentTerms->FontSize, _('Terms and Conditions').' :  '.'XXXXXXXX');
}

$pdf->SetTextColor(0); 
///*Now the Comments split over two lines if necessary */
//$LeftOvers = $pdf->addTextWrap($FormDesign->Comments->x,  $FormDesign->Comments->y,$FormDesign->Comments->Length,$FormDesign->Comments->FontSize, $POHeader['comments'], 'left');
//if (strlen($LeftOvers)>0){
//	$LeftOvers = $pdf->addTextWrap($FormDesign->Comments->x, $FormDesign->Comments->y-$line_height,$FormDesign->Comments->Length,$FormDesign->Comments->FontSize,$LeftOvers, 'left');
//}

/*draw a square grid for entering line headings */

$pdf->Rectangle($FormDesign->HeaderRectangle->x, $FormDesign->HeaderRectangle->y, $FormDesign->HeaderRectangle->width,$FormDesign->HeaderRectangle->height);
/*Set up headings */
$pdf->addText($FormDesign->Headings->Column1->x, $FormDesign->Headings->Column1->y, $FormDesign->Headings->Column1->FontSize, _('CODE') );
$pdf->addText($FormDesign->Headings->Column2->x, $FormDesign->Headings->Column2->y, $FormDesign->Headings->Column2->FontSize, _('ITEM') );
$pdf->addText($FormDesign->Headings->Column3->x, $FormDesign->Headings->Column3->y, $FormDesign->Headings->Column3->FontSize, _('DESCRIPTION') );
$pdf->addText($FormDesign->Headings->Column4->x, $FormDesign->Headings->Column4->y, $FormDesign->Headings->Column4->FontSize, _('QTY') );


if(!isset($MakePDFDocket)){
$pdf->addText($FormDesign->Headings->Column5->x, $FormDesign->Headings->Column5->y, $FormDesign->Headings->Column5->FontSize, _('PRICE') );
$pdf->addText($FormDesign->Headings->Column6->x, $FormDesign->Headings->Column6->y, $FormDesign->Headings->Column6->FontSize, _('TOTAL') );
$pdf->line($FormDesign->DrawLine2->x1,$FormDesign->DrawLine2->y1,$FormDesign->DrawLine2->x2,$FormDesign->DrawLine2->y2);
$pdf->line($FormDesign->DrawVerticalLine2->x1,$FormDesign->DrawVerticalLine2->y1,$FormDesign->DrawVerticalLine2->x2,$FormDesign->DrawVerticalLine2->y2);
}
/*draw a rectangle to hold the data lines */
$pdf->Rectangle($FormDesign->DataRectangle->x, $FormDesign->DataRectangle->y, $FormDesign->DataRectangle->width,$FormDesign->DataRectangle->height);

$YPos=$Page_Height - $FormDesign->Data->y+4*$line_height;
?>