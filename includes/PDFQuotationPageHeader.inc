<?php
/* $Id: PDFQuotationPageHeader.inc 4486 2011-02-08 09:20:50Z daintree $*/
/* pdf-php by R&OS code to set up a new sales order page */
if ($PageNumber>1){
	$pdf->newPage();
}

/** LOGO and TITLE**/
$pdf->SetLineWidth($FormDesign->LineWidth); 
$pdf->SetTextColor(0,100,0);
$YPos = $Page_Height - $Top_Margin;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin+$FormDesign->logo->x,$Page_Height- $FormDesign->logo->y,$FormDesign->logo->width,$FormDesign->logo->height);
$pdf->addText($FormDesign->INTitle->x, $FormDesign->INTitle->y, $FormDesign->INTitle->FontSize, _('Quote') . ' ');
$pdf->SetTextColor(0);
/** End LOGO and TITLE**/


/** Company Name **/
$pdf->addText($FormDesign->CompanyName->x, $FormDesign->CompanyName->y, $FormDesign->CompanyName->FontSize, $_SESSION['CompanyRecord']['coyname']);

if ($_SESSION['CompanyRecord']['regoffice1'] <> '' or $_SESSION['CompanyRecord']['regoffice2']<> '') {
  $LineCount += 1;
  $pdf->addText($FormDesign->CompanyAddress->Line1->x, $FormDesign->CompanyAddress->Line1->y,$FormDesign->CompanyAddress->Line1->FontSize, 
                $_SESSION['CompanyRecord']['regoffice1'].', '.$_SESSION['CompanyRecord']['regoffice2'].'  /  '.
                _('Phone').' '.$_SESSION['CompanyRecord']['telephone']);
}

if (($_SESSION['CompanyRecord']['regoffice3'] <> '') OR ($_SESSION['CompanyRecord']['regoffice4'] <> '') OR ($_SESSION['CompanyRecord']['regoffice5'] <> '')) { 
  $LineCount += 1;
   $pdf->addText($FormDesign->CompanyAddress->Line2->x, $FormDesign->CompanyAddress->Line2->y,$FormDesign->CompanyAddress->Line2->FontSize, 
                $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5'].'          /'.
                '   '. _('ABN') . ' ' . $_SESSION['CompanyRecord']['companynumber']);
 
}
/** End Company Name **/


/** Billing Address and Ship Address **/
$pdf->line($FormDesign->DrawLine->x1, $FormDesign->DrawLine->y1,$FormDesign->DrawLine->x2, $FormDesign->DrawLine->y2);
/*Now the customer company info */
$pdf->SetTextColor(0,100,0);
$pdf->addText($FormDesign->InvoiceNumber->x, $FormDesign->InvoiceNumber->y,$FormDesign->InvoiceNumber->FontSize, _('Quote No:'). ' ' . 'W'.$_GET['QuotationNo']);
$pdf->addText($FormDesign->InvoiceDate->x, $FormDesign->InvoiceDate->y, $FormDesign->InvoiceDate->FontSize, _('Date:') . ' '.ConvertSQLDate($myrow['orddate']) );
$pdf->SetTextColor(0);

/*Idnetify Bill Address */
$pdf->addText($FormDesign->BillAddress->Caption->x,$FormDesign->BillAddress->Caption->y, $FormDesign->BillAddress->Caption->FontSize, _('Bill To') . ':' );

$nameFirstPart= $myrow['name'];
$nameSecondPart= $myrow['contactname']; 

   
$pdf->addText($FormDesign->BillAddress->Line1->x, $FormDesign->BillAddress->Line1->y, $FormDesign->BillAddress->Line1->FontSize, $nameFirstPart);
if(isset($nameSecondPart) and $nameSecondPart!=''){
   $pdf->addText($FormDesign->BillAddress->Line1->x, $FormDesign->BillAddress->Line1->y-10, $FormDesign->BillAddress->Line1->FontSize, $nameSecondPart);
   $pdf->addText($FormDesign->BillAddress->Line2->x, $FormDesign->BillAddress->Line2->y-10, $FormDesign->BillAddress->Line2->FontSize, $myrow['address1']);
   $pdf->addText($FormDesign->BillAddress->Line3->x, $FormDesign->BillAddress->Line3->y-10, $FormDesign->BillAddress->Line3->FontSize, $myrow['address2'].' '.$myrow['address3'].' '.$myrow['address4'].' '.$myrow['address5'].' '.$myrow['address6']);
   $pdf->addText($FormDesign->BillAddress->Line4->x, $FormDesign->BillAddress->Line4->y-10, $FormDesign->BillAddress->Line4->FontSize, _('PHONE').' '.$myrow['phoneno']);
    }
else{
$pdf->addText($FormDesign->BillAddress->Line2->x, $FormDesign->BillAddress->Line2->y, $FormDesign->BillAddress->Line2->FontSize, $myrow['address1']);
$pdf->addText($FormDesign->BillAddress->Line3->x, $FormDesign->BillAddress->Line3->y, $FormDesign->BillAddress->Line3->FontSize, $myrow['address2'].' '.$myrow['address3'].' '.$myrow['address4'].' '.$myrow['address5'].' '.$myrow['address6']);
$pdf->addText($FormDesign->BillAddress->Line4->x, $FormDesign->BillAddress->Line4->y, $FormDesign->BillAddress->Line4->FontSize, _('PHONE').' '.$myrow['phoneno']);
    }
/*End Bill Address */

/*Idnetify Ship Address */
$pdf->addText($FormDesign->ShipAddress->Caption->x,$FormDesign->ShipAddress->Caption->y, $FormDesign->ShipAddress->Caption->FontSize, _('Ship To') . ':' );    

$delFirstPart=  $myrow['deliverto'];
$delSecondPart= $myrow['contactname']; 

   
$pdf->addText($FormDesign->ShipAddress->Line1->x, $FormDesign->ShipAddress->Line1->y, $FormDesign->ShipAddress->Line1->FontSize, $delFirstPart);
if(isset($delSecondPart) and $delSecondPart!=''){
   $pdf->addText($FormDesign->ShipAddress->Line1->x, $FormDesign->ShipAddress->Line1->y-10, $FormDesign->ShipAddress->Line1->FontSize, $delSecondPart);
   $pdf->addText($FormDesign->ShipAddress->Line2->x, $FormDesign->ShipAddress->Line2->y-10, $FormDesign->ShipAddress->Line2->FontSize, $myrow['deladd1']);
   $pdf->addText($FormDesign->ShipAddress->Line3->x, $FormDesign->ShipAddress->Line3->y-10, $FormDesign->ShipAddress->Line3->FontSize, $myrow['deladd2'].' '.$myrow['deladd3'].' '.$myrow['deladd4'].' '.$myrow['deladd5'].' '.$myrow['deladd6']);
   $pdf->addText($FormDesign->ShipAddress->Line4->x, $FormDesign->ShipAddress->Line4->y-10, $FormDesign->ShipAddress->Line4->FontSize, _('PHONE').' '.$myrow['contactphone']);
}
else{
$pdf->addText($FormDesign->ShipAddress->Line2->x, $FormDesign->ShipAddress->Line2->y, $FormDesign->ShipAddress->Line2->FontSize, $myrow['deladd1']);
$pdf->addText($FormDesign->ShipAddress->Line3->x, $FormDesign->ShipAddress->Line3->y, $FormDesign->ShipAddress->Line3->FontSize, $myrow['deladd2'].' '.$myrow['deladd3'].' '.$myrow['deladd4'].' '.$myrow['deladd5'].' '.$myrow['deladd6']);
$pdf->addText($FormDesign->ShipAddress->Line4->x, $FormDesign->ShipAddress->Line4->y, $FormDesign->ShipAddress->Line4->FontSize, _('PHONE').' '.$myrow['contactphone']);
}


/*Now the currency the order is in */
//$pdf->addText($FormDesign->Currency->x, $FormDesign->Currency->y,$FormDesign->Currency->FontSize, _('All amounts stated in').' - ' . $POHeader['currcode']);

$pdf->line($FormDesign->DrawVerticalLine->x1,$FormDesign->DrawVerticalLine->y1,$FormDesign->DrawVerticalLine->x2,$FormDesign->DrawVerticalLine->y2);
//$pdf->line($FormDesign->DrawLine1->x1,$FormDesign->DrawLine1->y1,$FormDesign->DrawLine1->x2,$FormDesign->DrawLine1->y2);

$pdf->Rectangle($FormDesign->HeaderRectangle->x, $FormDesign->HeaderRectangle->y, $FormDesign->HeaderRectangle->width,$FormDesign->HeaderRectangle->height);


$pdf->addText($FormDesign->Headings->Column1->x, $FormDesign->Headings->Column1->y, $FormDesign->Headings->Column1->FontSize, _('ITEM') );
$pdf->addText($FormDesign->Headings->Column2->x, $FormDesign->Headings->Column2->y, $FormDesign->Headings->Column2->FontSize, _('DESCRIPTION') );
$pdf->addText($FormDesign->Headings->Column3->x, $FormDesign->Headings->Column3->y, $FormDesign->Headings->Column3->FontSize, _('QTY') );
$pdf->addText($FormDesign->Headings->Column4->x, $FormDesign->Headings->Column4->y, $FormDesign->Headings->Column4->FontSize, _('PRICE') );
$pdf->addText($FormDesign->Headings->Column5->x, $FormDesign->Headings->Column5->y, $FormDesign->Headings->Column5->FontSize, _('DISC%') );
$pdf->addText($FormDesign->Headings->Column6->x, $FormDesign->Headings->Column6->y, $FormDesign->Headings->Column6->FontSize, _('TOTAL') );
/*draw a rectangle to hold the data lines */
$pdf->Rectangle($FormDesign->DataRectangle->x,  $FormDesign->DataRectangle->y, $FormDesign->DataRectangle->width,$FormDesign->DataRectangle->height);


$pdf->line($FormDesign->DrawLine2->x1,$FormDesign->DrawLine2->y1,$FormDesign->DrawLine2->x2,$FormDesign->DrawLine2->y2);
$pdf->line($FormDesign->DrawLine3->x1,$FormDesign->DrawLine3->y1,$FormDesign->DrawLine3->x2,$FormDesign->DrawLine3->y2);
$pdf->line($FormDesign->DrawVerticalLine2->x1,$FormDesign->DrawVerticalLine2->y1,$FormDesign->DrawVerticalLine2->x2,$FormDesign->DrawVerticalLine2->y2);

$YPos -= 18*($line_height);



?>
