<?php

/* $Id: PDFTransPageHeaderPortrait.inc 4464 2011-01-11 08:58:07Z daintree $ */
/* R&OS PHP-pdf class code to head up a new page */

if (!$FirstPage or $PageNumber>1){ /* only initiate a new page if its not the first */
	$pdf->newPage();
}
$pdf->SetLineWidth($FormDesign->LineWidth); 
$pdf->SetTextColor(0,100,0);
$YPos = $Page_Height - $Top_Margin;

$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin+$FormDesign->logo->x,$Page_Height- $FormDesign->logo->y,$FormDesign->logo->width,$FormDesign->logo->height);

//$FontSize =15;
if ($InvOrCredit=='Invoice') {
    $pdf->addText($FormDesign->INTitle->x, $FormDesign->INTitle->y, $FormDesign->INTitle->FontSize, _('Tax Invoice') . ' ');
} else {
	$pdf->addText($FormDesign->INTitle->x, $FormDesign->INTitle->y, $FormDesign->INTitle->FontSize, _('Tax Credit Note') . ' ');
}
 $pdf->SetTextColor(0);
/*page number*/

//$pdf->addText($Page_Width-128, $YPos, $FontSize, _('Page'));
//$pdf->addText($Page_Width-60, $YPos, $FontSize, $PageNumber);

///*draw a nice curved corner box around the billing details */
///*from the top right */
//$pdf->partEllipse($XPos+225,$YPos+67,0,90,10,10);
///*line to the top left */
//$pdf->line($XPos+225, $YPos+77,$XPos, $YPos+77);
///*Do top left corner */
//$pdf->partEllipse($XPos, $YPos+67,90,180,10,10);
///*Do a line to the bottom left corner */
//$pdf->line($XPos-10, $YPos+67,$XPos-10, $YPos-10);
///*Now do the bottom left corner 180 - 270 coming back west*/
//$pdf->partEllipse($XPos, $YPos-10,180,270,10,10);
///*Now a line to the bottom right */
//$pdf->line($XPos, $YPos-20,$XPos+225, $YPos-20);
///*Now do the bottom right corner */
//$pdf->partEllipse($XPos+225, $YPos-10,270,360,10,10);
///*Finally join up to the top right corner where started */
//$pdf->line($XPos+235, $YPos-10,$XPos+235, $YPos+67);

//$LineCount = 1;
//$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, _('Number'));
//$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, $FromTransNo);
//$LineCount += 1;
//$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, _('Customer Code'));
//$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, $myrow['debtorno'] . ' ' . _('Branch') . ' ' . $myrow['branchcode']);
//$LineCount += 1;
//$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, _('Date'));
//$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, ConvertSQLDate($myrow['trandate']));

//if ($InvOrCredit=='Invoice') {
//	$LineCount += 1;
//	$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, _('Order No'));
//	$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, $myrow['orderno']);
//	$LineCount += 1;
//	$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, _('Order Date'));
//	$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, ConvertSQLDate($myrow['orddate']));
//	$LineCount += 1;
//	$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, _('Dispatch Detail'));
//	$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, $myrow['shippername'] . '-' . $myrow['consignment']);
//	$LineCount += 1;
//	$pdf->addText($Page_Width-268, $YPos-$LineCount*$LineHeight, $FontSize, _('Dispatched From'));
//	$pdf->addText($Page_Width-180, $YPos-$LineCount*$LineHeight, $FontSize, $myrow['locationname']);
//}

/*End of the text in the right side box */

/*Now print out company info at the top left */


//$pdf->addText($XPos-120, $YPos-$LineCount*$LineHeight, $FontSize, $_SESSION['CompanyRecord']['coyname']);
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


$pdf->line($FormDesign->DrawLine->x1, $FormDesign->DrawLine->y1,$FormDesign->DrawLine->x2, $FormDesign->DrawLine->y2);
/*Now the customer company info */
 $pdf->SetTextColor(0,100,0);
 if ($InvOrCredit=='Invoice') {
$pdf->addText($FormDesign->InvoiceNumber->x, $FormDesign->InvoiceNumber->y,$FormDesign->InvoiceNumber->FontSize, _('Invoice No:'). ' ' . $myrow['sales_ref_num']);
 }
 elseif($InvOrCredit=='Credit'){
 $pdf->addText($FormDesign->InvoiceNumber->x, $FormDesign->InvoiceNumber->y,$FormDesign->InvoiceNumber->FontSize, _('Credit Note No:'). ' ' . $FromTransNo);    
 }
$pdf->addText($FormDesign->InvoiceDate->x, $FormDesign->InvoiceDate->y, $FormDesign->InvoiceDate->FontSize, _('Date:') . ' '.ConvertSQLDate($myrow['trandate']) );
 $pdf->SetTextColor(0);
/*Now the Bill To details */
$pdf->addText($FormDesign->BillAddress->Caption->x,$FormDesign->BillAddress->Caption->y, $FormDesign->BillAddress->Caption->FontSize, _('Bill To') . ':' );

/*Idnetify Contactname exist or not and jump to second line */

$nameFirstPart=  $myrow['name'];
$nameSecondPart=  $myrow['contactname']; 
 

//$addr1FirstPart=  substr($myrow['address1'], 0, 40);
//$addr1SecondPart= substr($myrow['address1'], 40, 80); 
if($myrow['invaddrbranch']==0){
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
}


else{
$pdf->addText($FormDesign->BillAddress->Line1->x, $FormDesign->BillAddress->Line1->y, $FormDesign->BillAddress->Line1->FontSize, $nameFirstPart);
if(isset($nameSecondPart) and $nameSecondPart!=''){
   $pdf->addText($FormDesign->BillAddress->Line1->x, $FormDesign->BillAddress->Line1->y-10, $FormDesign->BillAddress->Line1->FontSize, $nameSecondPart);
   $pdf->addText($FormDesign->BillAddress->Line2->x, $FormDesign->BillAddress->Line2->y-10, $FormDesign->BillAddress->Line2->FontSize, $myrow['brpostaddr1']);
   $pdf->addText($FormDesign->BillAddress->Line3->x, $FormDesign->BillAddress->Line3->y-10, $FormDesign->BillAddress->Line3->FontSize, $myrow['brpostaddr2'].' '.$myrow['brpostaddr3'].' '.$myrow['brpostaddr4'].' '.$myrow['brpostaddr5'].' '.$myrow['brpostaddr6']);
   $pdf->addText($FormDesign->BillAddress->Line4->x, $FormDesign->BillAddress->Line4->y-10, $FormDesign->BillAddress->Line4->FontSize, _('PHONE').' '.$myrow['phoneno']);
    }
    
else{
$pdf->addText($FormDesign->BillAddress->Line2->x, $FormDesign->BillAddress->Line2->y, $FormDesign->BillAddress->Line2->FontSize, $myrow['brpostaddr1']);
$pdf->addText($FormDesign->BillAddress->Line3->x, $FormDesign->BillAddress->Line3->y, $FormDesign->BillAddress->Line3->FontSize, $myrow['brpostaddr2'].' '.$myrow['brpostaddr3'].' '.$myrow['brpostaddr4'].' '.$myrow['brpostaddr5'].' '.$myrow['brpostaddr6']);
$pdf->addText($FormDesign->BillAddress->Line4->x, $FormDesign->BillAddress->Line4->y, $FormDesign->BillAddress->Line4->FontSize, _('PHONE').' '.$myrow['phoneno']);
    }

}


if ($InvOrCredit=='Invoice') {

   $pdf->addText($FormDesign->ShipAddress->Caption->x,$FormDesign->ShipAddress->Caption->y, $FormDesign->ShipAddress->Caption->FontSize, _('Ship To') . ':' );

 // $LineCount += 1;
// Before trying to call htmlspecialchars_decode, check that its supported, if not substitute a compatible version
if (!function_exists('htmlspecialchars_decode')) {
        function htmlspecialchars_decode($str) {
                $trans = get_html_translation_table(HTML_SPECIALCHARS);

                $decode = ARRAY();
                foreach ($trans AS $char=>$entity) {
                        $decode[$entity] = $char;
                }

                $str = strtr($str, $decode);

                return $str;
        }
}

/*Idnetify Contactname exist or not and jump to second line */

$delFirstPart=  $myrow['deliverto'];
$delSecondPart=  $myrow['contactname']; 


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


}
//if ($InvOrCredit=='Credit'){
///* then its a credit note */
//	$pdf->addText($FormDesign->ShipAddress->Caption->x,$FormDesign->ShipAddress->Caption->y, $FormDesign->ShipAddress->Caption->FontSize, _('Charge Branch') . ':' );
//	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Charge Branch') . ':');
//        
//if($myrow['contactname']!=null){
//$nameFirstPart=  substr($myrow['brname'].' / '.$myrow['contactname'], 0, 40);
//$nameSecondPart=  substr($myrow['brname'].' / '.$myrow['contactname'], 40, 80); 
//   }
//else{
//$nameFirstPart=  substr($myrow['brname'], 0, 40); 
//$nameSecondPart=  substr($myrow['brname'], 40, 80); 
//   }
//    
//    $pdf->addText($FormDesign->ShipAddress->Line1->x, $FormDesign->ShipAddress->Line1->y, $FormDesign->ShipAddress->Line1->FontSize, $nameFirstPart);
// if(isset($nameSecondPart) and $nameSecondPart!=''){
//    $pdf->addText($FormDesign->ShipAddress->Line1->x, $FormDesign->ShipAddress->Line1->y-10, $FormDesign->ShipAddress->Line1->FontSize, $nameSecondPart);
//    $pdf->addText($FormDesign->ShipAddress->Line2->x,  $FormDesign->ShipAddress->Line2->y-10, $FormDesign->ShipAddress->Line2->FontSize, $myrow['braddress1']);
//    $pdf->addText($FormDesign->ShipAddress->Line3->x, $FormDesign->ShipAddress->Line3->y-10, $FormDesign->ShipAddress->Line3->FontSize, $myrow['braddress2'].' '.$myrow['braddress3'].' '.$myrow['braddress4'].' '.$myrow['braddress5'].' '.$myrow['braddress6']); 
//    $pdf->addText($FormDesign->ShipAddress->Line4->x, $FormDesign->ShipAddress->Line4->y-10, $FormDesign->ShipAddress->Line4->FontSize, _('PHONE').' '.$myrow['contactphone']);
// }
// else{
//    $pdf->addText($FormDesign->ShipAddress->Line2->x,  $FormDesign->ShipAddress->Line2->y-10, $FormDesign->ShipAddress->Line2->FontSize, $myrow['braddress1']);
//    $pdf->addText($FormDesign->ShipAddress->Line3->x, $FormDesign->ShipAddress->Line3->y-10, $FormDesign->ShipAddress->Line3->FontSize, $myrow['braddress2'].' '.$myrow['braddress3'].' '.$myrow['braddress4'].' '.$myrow['braddress5'].' '.$myrow['braddress6']); 
//    $pdf->addText($FormDesign->ShipAddress->Line4->x, $FormDesign->ShipAddress->Line4->y-10, $FormDesign->ShipAddress->Line4->FontSize, _('PHONE').' '.$myrow['contactphone']);  
// }
//}

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
//$pdf->line($FormDesign->DrawVerticalLine3->x1,$FormDesign->DrawVerticalLine3->y1,$FormDesign->DrawVerticalLine3->x2,$FormDesign->DrawVerticalLine3->y2);

/*draw a line */
//$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= 17*($line_height);

?>
