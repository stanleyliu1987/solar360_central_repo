<?php

/* $Id: PO_PDFPurchOrder.php 4560 2011-05-02 10:33:55Z daintree $*/

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/DefinePOClass.php');
 

if(!isset($_GET['OrderNo']) && !isset($_POST['OrderNo'])){
	$title = _('Select a Purchase Order');
	include('includes/header.inc');
	echo '<div class="centre"><br /><br /><br />';
	prnMsg( _('Select a Purchase Order Number to Print before calling this page') , 'error');
	echo '<br />
				<br />
				<br />
				<table class="table_index">
					<tr><td class="menu_group_item">
						<li><a href="'. $rootpath . '/PO_SelectOSPurchOrder.php">' . _('Outstanding Purchase Orders') . '</a></li>
						<li><a href="'. $rootpath . '/PO_SelectPurchOrder.php">' . _('Purchase Order Inquiry') . '</a></li>
						</td>
					</tr></table>
				</div>
				<br />
				<br />
				<br />';
	include('includes/footer.inc');
	exit();

	echo '<div class="centre"><br /><br /><br />' . _('This page must be called with a purchase order number to print');
	echo '<br /><a href="'. $rootpath . '/index.php">' . _('Back to the menu') . '</a></div>';
	exit;
}
if (isset($_GET['OrderNo'])){
	$OrderNo = $_GET['OrderNo'];
} elseif (isset($_POST['OrderNo'])){
	$OrderNo = $_POST['OrderNo'];
}
$title = _('Print Purchase Order Number').' '. $OrderNo;

if (isset($_POST['PrintOrEmail']) AND isset($_POST['EmailTo']) ){
	if ($_POST['PrintOrEmail'] =='Email' AND ! IsEmailAddress($_POST['EmailTo'])){
		include('includes/header.inc');
		prnMsg( _('The email address entered does not appear to be valid. No emails have been sent.'),'warn');
		include('includes/footer.inc');
		exit;
	}
}
$ViewingOnly = 0;

if (isset($_GET['ViewingOnly']) AND $_GET['ViewingOnly']!='') {
	$ViewingOnly = $_GET['ViewingOnly'];
} elseif (isset($_POST['ViewingOnly']) AND $_POST['ViewingOnly']!='') {
	$ViewingOnly = $_POST['ViewingOnly'];
}
/* If we are previewing the order then we dont want to email it */
if ($OrderNo == 'Preview') { //OrderNo is set to 'Preview' when just looking at the format of the printed order
	$_POST['PrintOrEmail']='Print';
	/*These are required to kid the system - I hate this */
	$_POST['ShowAmounts']='Yes';
	$OrderStatus = _('Printed');
	$MakePDFThenDisplayIt = True;
}

if (isset($_POST['DoIt'])  AND ($_POST['PrintOrEmail']=='Print' OR $ViewingOnly==1) ){
	$MakePDFThenDisplayIt = True;
	$MakePDFThenEmailIt = False;
} elseif (isset($_POST['DoIt']) AND $_POST['PrintOrEmail']=='Email' AND isset($_POST['EmailTo'])){
	$MakePDFThenEmailIt = True;
	$MakePDFThenDisplayIt = False;
}
//Print Docket
elseif(isset($_GET['PDocket'])){
    $MakePDFThenDisplayIt = True;
    $MakePDFThenEmailIt = False;
    $MakePDFDocket= True;
}

//Print RCTI
elseif(isset($_GET['RCTI'])){
    $_POST['ShowAmounts']='Yes';  
    $MakePDFThenDisplayIt = True;
    $MakePDFThenEmailIt = False;
    $MakePDFRCTI=True;
}

//Directly Print PO
elseif(isset($_GET['PrintDirPO']) and $_GET['PrintDirPO']==1){
  $_POST['ShowAmounts']='Yes';  
  $MakePDFThenDisplayIt = True;
  $MakePDFThenEmailIt = False;
  $OrderStatus= $_GET['POStatus'];
}
if (isset($OrderNo) AND $OrderNo != '' AND $OrderNo > 0 AND $OrderNo != 'Preview'){
    
        /* Check related Invoice existed or not */
        $sqlCheckInvoice = "SELECT * FROM purchorders  WHERE orderno='" . $OrderNo ."' AND ref_salesorder <> '' ";
        $resultCheckInvoice=DB_query($sqlCheckInvoice,$db);
        
        /*retrieve the order details from the database to print */
	$ErrMsg = _('There was a problem retrieving the purchase order header details for Order Number'). ' ' . $OrderNo .
			' ' . _('from the database');
        
	if (DB_num_rows($resultCheckInvoice)==0){
           $sql = "SELECT	purchorders.supplierno,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
                                        suppliers.telephone,
                                        suppliers.taxref,
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
                                        
                                        purchorders.suppdeladdress1,
					purchorders.suppdeladdress2,
					purchorders.suppdeladdress3,
					purchorders.suppdeladdress4,
					purchorders.suppdeladdress5,
					purchorders.suppdeladdress6,
                                        purchorders.SupplierContact,
                                        purchorders.supptel,
                                        purchorders.supwarehouseno,
                        
					purchorders.allowprint,
					purchorders.requisitionno,
                                        purchorders.tel,
					purchorders.initiator,
					purchorders.paymentterms,
                                        purchorders.contact,
					suppliers.currcode,
					purchorders.status,
					purchorders.stat_comment,
                                        purchorders.ref_number,
                                        purchorders.ref_salesorder,
                                        shippers.shippername

				FROM purchorders INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
                                        INNER JOIN shippers ON shippers.shipper_id=purchorders.deliveryby
				WHERE purchorders.orderno='" . $OrderNo ."'"; 
            
        }
        else{

	$sql = "SELECT	purchorders.supplierno,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
                                        suppliers.telephone,
                                        suppliers.taxref,
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
                                        
                                        purchorders.suppdeladdress1,
					purchorders.suppdeladdress2,
					purchorders.suppdeladdress3,
					purchorders.suppdeladdress4,
					purchorders.suppdeladdress5,
					purchorders.suppdeladdress6,
                                        purchorders.SupplierContact,
                                        purchorders.supptel,
                                        purchorders.supwarehouseno,
                        
					purchorders.allowprint,
					purchorders.requisitionno,
                                        purchorders.tel,
					purchorders.initiator,
					purchorders.paymentterms,
                                        purchorders.contact,
					suppliers.currcode,
					purchorders.status,
					purchorders.stat_comment,
                                        purchorders.ref_number,
                                        purchorders.ref_salesorder,
                                        shippers.shippername,
                                        debtortrans.trandate 
                                        
				FROM purchorders INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
                                        INNER JOIN shippers ON shippers.shipper_id=purchorders.deliveryby
                                        INNER JOIN debtortrans ON debtortrans.order_= purchorders.ref_salesorder
				WHERE purchorders.orderno='" . $OrderNo ."'
                                      AND debtortrans.type=10";
        }
        
	$result=DB_query($sql,$db, $ErrMsg);
	if (DB_num_rows($result)==0){ /*There is no order header returned */
		$title = _('Print Purchase Order Error');
		include('includes/header.inc');
		echo '<div class="centre"><br /><br /><br />';
		prnMsg( _('Unable to Locate Purchase Order Number') . ' : ' . $OrderNo . ' ', 'error');
		echo '<br />
			<br />
			<br />
			<table class="table_index">
				<tr><td class="menu_group_item">
				<li><a href="'. $rootpath . '/PO_SelectOSPurchOrder.php">' . _('Outstanding Purchase Orders') . '</a></li>
				<li><a href="'. $rootpath . '/PO_SelectPurchOrder.php">' . _('Purchase Order Inquiry') . '</a></li>
				</td>
				</tr>
			</table>
			</div><br /><br /><br />';
		include('includes/footer.inc');
		exit();
	} elseif (DB_num_rows($result)==1){ /*There is only one order header returned  (as it should be!)*/

	$POHeader = DB_fetch_array($result);
        
        /* Get Warehouse Name*/
        $ErrMsg = _('There was a problem retrieving the warehouse details for Order Number'). ' ' . $OrderNo .
			' ' . _('from the database');
        $sqlwarehouse = "SELECT warehousename FROM supplierwarehouse 
				     WHERE warehousecode='" . $POHeader['supwarehouseno'] ."' and
                                           supplierid='".$POHeader['supplierno']."'";
	$resultWarehouse=DB_query($sqlwarehouse,$db, $ErrMsg);
        $POWarehouse = DB_fetch_array($resultWarehouse);
        $WarehouseName=$POWarehouse['warehousename'];
		
        if(!isset($_GET['PDocket']) or !isset($_GET['RCTI'])){
		if ($POHeader['status'] != 'Completed' AND $POHeader['status'] != 'Printed' And $POHeader['status'] != 'Authorised') {
			include('includes/header.inc');
			prnMsg( _('Purchase orders can only be printed once they have been authorised') . '. ' . _('This order is currently at a status of') . ' ' . _($OrderStatus),'warn');
			include('includes/footer.inc');
			exit;
		}
        }
		if ($ViewingOnly==0) {
			if ($POHeader['allowprint']==0){
				$title = _('Purchase Order Already Printed');
				include('includes/header.inc');
				echo '<p>';
				prnMsg( _('Purchase Order Number').' ' . $OrderNo . ' '. _('has previously been printed') . '. ' . _('It was printed on'). ' ' .
				ConvertSQLDate($POHeader['dateprinted']) . '<br />'.
					_('To re-print the order it must be modified to allow a reprint'). '<br />'.
					_('This check is there to ensure that duplicate purchase orders are not sent to the supplier resulting in several deliveries of the same supplies'), 'warn');
				echo '<br /><table class="table_index">
					<tr><td class="menu_group_item">
 					<li><a href="' . $rootpath . '/PO_PDFPurchOrder.php?OrderNo=' . $OrderNo . '&ViewingOnly=1">'.
				_('Print This Order as a Copy'). '</a>
 				<li><a href="' . $rootpath . '/PO_Header.php?ModifyOrderNumber=' . $OrderNo . '">'.
				_('Modify the order to allow a real reprint'). '</a>' .
				'<li><a href="'. $rootpath .'/PO_SelectPurchOrder.php">'.
				_('Select another order'). '</a>'.
				'<li><a href="' . $rootpath . '/index.php">'. _('Back to the menu').'</a>';
				include('includes/footer.inc');
				exit;
			}//AllowedToPrint
		}//not ViewingOnly
	}// 1 valid record
}//if there is a valid order number
else if ($OrderNo=='Preview') {// We are previewing the order
/* Fill the order header details with dummy data */
	$POHeader['supplierno']=str_pad('',10,'x');
	$POHeader['suppname']=str_pad('',40,'x');
	$POHeader['address1']=str_pad('',40,'x');
	$POHeader['address2']=str_pad('',40,'x');
	$POHeader['address3']=str_pad('',40,'x');
	$POHeader['address4']=str_pad('',30,'x');
	$POHeader['comments']=str_pad('',50,'x');
	$POHeader['orddate']='1900-01-01';
	$POHeader['rate']='0.0000';
	$POHeader['dateprinted']='1900-01-01';
	$POHeader['deladd1']=str_pad('',40,'x');
	$POHeader['deladd2']=str_pad('',40,'x');
	$POHeader['deladd3']=str_pad('',40,'x');
	$POHeader['deladd4']=str_pad('',40,'x');
	$POHeader['deladd5']=str_pad('',20,'x');
	$POHeader['deladd6']=str_pad('',15,'x');
	$POHeader['allowprint']=1;
	$POHeader['requisitionno']=str_pad('',15,'x');
	$POHeader['initiator']=str_pad('',50,'x');
	$POHeader['paymentterms']=str_pad('',15,'x');
	$POHeader['currcode']='XXX';
} // end of If we are previewing the order
/* Load the relevant xml file */
if (isset($MakePDFThenDisplayIt) or isset($MakePDFThenEmailIt)) {
	if ($OrderNo=='Preview') {
		$FormDesign = simplexml_load_file(sys_get_temp_dir().'/PurchaseOrder.xml');
	} else {
		$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/PurchaseOrder.xml');
	}
// Set the paper size/orintation
	$PaperSize = $FormDesign->PaperSize;
	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', _('Purchase Order') );
	$pdf->addInfo('Subject', _('Purchase Order Number' ) . ' ' . $OrderNo);
	$line_height = $FormDesign->LineHeight;
	$PageNumber = 1;
	/* Then there's an order to print and its not been printed already (or its been flagged for reprinting)
	Now ... Has it got any line items */
	if ($OrderNo !='Preview') { // It is a real order
		$ErrMsg = _('There was a problem retrieving the line details for order number') . ' ' . $OrderNo . ' ' .
			_('from the database');
		$sql = "SELECT itemcode,
						deliverydate,
						itemdescription,
						unitprice,
						suppliersunit,
						quantityord,
						decimalplaces,
						conversionfactor,
						suppliers_partno,
                                                narrative,
                                                supinvref
				FROM purchorderdetails LEFT JOIN stockmaster
					ON purchorderdetails.itemcode=stockmaster.stockid
				WHERE orderno ='" . $OrderNo ."'";
		$result=DB_query($sql,$db);
	}
	if ($OrderNo=='Preview' or DB_num_rows($result)>0){
		/*Yes there are line items to start the ball rolling with a page header */
		include('includes/PO_PDFOrderPageHeader.inc');
                $Bottom_Margin=270;
		$OrderTotal = 0;
              
		while ((isset($OrderNo) and $OrderNo=='Preview') 
				OR (isset($result) and $POLine=DB_fetch_array($result))) {
			/* If we are previewing the order then fill the
			 * order line with dummy data */
			if ($OrderNo=='Preview') {
				$POLine['itemcode']=str_pad('',10,'x');
				$POLine['deliverydate']='1900-01-01';
				$POLine['itemdescription']=str_pad('',50,'x');
				$POLine['unitprice']=9999.99;
				$POLine['units']=str_pad('',4,'x');
				$POLine['quantityord']=999.99;
				$POLine['decimalplaces']=2;
			}
			$DisplayQty = number_format($POLine['quantityord']/$POLine['conversionfactor'],0);
			if ($_POST['ShowAmounts']=='Yes'){
				$DisplayPrice = number_format($POLine['unitprice']*$POLine['conversionfactor'],2);
			} else {
				$DisplayPrice = '----';
			}
			$DisplayDelDate = ConvertSQLDate($POLine['deliverydate'],2);
			if ($_POST['ShowAmounts']=='Yes'){
				$DisplayLineTotal = number_format($POLine['unitprice']*$POLine['quantityord'],2);
			} else {
				$DisplayLineTotal = '----';
			}
			$Desc=$POLine['itemdescription'];
			
			$OrderTotal += ($POLine['unitprice']*$POLine['quantityord']);
			
			$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column2->x,$YPos,$FormDesign->Data->Column2->Length,$FormDesign->Data->Column2->FontSize,$Desc.' - '.$POLine['narrative'], 'left');
                        if (strlen($LeftOvers)>1){
				$LeftOvers = $pdf->addTextWrap($Left_Margin+90,$YPos-$line_height,270,$FontSize,$LeftOvers, 'left');
			}
                        
			$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column3->x,$YPos,$FormDesign->Data->Column3->Length,$FormDesign->Data->Column3->FontSize,$DisplayQty, 'center');
                        if(!isset($MakePDFDocket)){
                        $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column1->x,$YPos,$FormDesign->Data->Column1->Length,$FormDesign->Data->Column1->FontSize,$POLine['suppliers_partno'], 'left');
                        if(!isset($MakePDFRCTI)){   
                        $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column4->x,$YPos,$FormDesign->Data->Column4->Length,$FormDesign->Data->Column4->FontSize,$DisplayDelDate, 'left');
                        }
			$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column5->x,$YPos,$FormDesign->Data->Column5->Length,$FormDesign->Data->Column5->FontSize,$DisplayPrice, 'left');
			$LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column6->x,$YPos,$FormDesign->Data->Column6->Length,$FormDesign->Data->Column6->FontSize,$DisplayLineTotal, 'left');
                        }
                        else{
                        $LeftOvers = $pdf->addTextWrap($FormDesign->Data->Column1->x,$YPos,$FormDesign->Data->Column1->Length,$FormDesign->Data->Column1->FontSize,$POLine['itemcode'], 'left');   
                        }
			if (strlen($LeftOvers)>1){
				$LeftOvers = $pdf->addTextWrap($Left_Margin+1+94,$YPos-$line_height,270,$FontSize,$LeftOvers, 'left');
				$YPos-=$line_height;
			}
                        
                        $YPos -= 2*$line_height;
                        
                       
                        
                        if ($YPos <= $Bottom_Margin){
                        $PageNumber++;             
			include ('includes/PO_PDFOrderPageHeader.inc');
                     } 
		
		        
 
//
//			if ($YPos-$line_height <= $Bottom_Margin){
//				/* We reached the end of the page so finsih off the page and start a newy */
//				$PageNumber++;
//				$YPos=$Page_Height - $FormDesign->Data->y;
//				include ('includes/PO_PDFOrderPageHeader.inc');
//			} //end if need a new page headed up
//			
//			/*increment a line down for the next line item */
//			$YPos -= 2*$line_height;
			/* If we are previewing we want to stop showing order
			 * lines after the first one */
			if ($OrderNo=='Preview') {
//				unlink(sys_get_temp_dir().'/PurchaseOrder.xml');
				unset($OrderNo);
			}
		}
        //   if(!isset($MakePDFDocket)){	    
                /*Now the Comments split over two lines if necessary */
                $CommentPart = explode("<br />",  nl2br($POHeader['comments']));
                // $LeftOvers = $pdf->addTextWrap($FormDesign->Comments->x,  $YPos,$FormDesign->Comments->Length,$FormDesign->Comments->FontSize, $CommentPart[0], 'left');
            for($i=0;$i<count($CommentPart);$i++){
              
              $LeftOvers = $pdf->addTextWrap($FormDesign->Comments->x,  $YPos,$FormDesign->Comments->Length,$FormDesign->Comments->FontSize, $CommentPart[$i], 'left');
             if (strlen($LeftOvers)>0){
	      $LeftOvers = $pdf->addTextWrap($FormDesign->Comments->x, $YPos-$line_height,$FormDesign->Comments->Length,$FormDesign->Comments->FontSize,nl2br($LeftOvers), 'left');
                   }
                   $YPos -= $line_height;
            }    
       //     }
            
        /* Add warehouse Name */
           $pdf->addText($FormDesign->Warehouse->x,  $FormDesign->Warehouse->y,$FormDesign->Warehouse->FontSize, $WarehouseName, 'left');
        /* Set up Signature Text 28082014 */
           if(isset($MakePDFDocket)){
           /* Draw Lines first */
           $pdf->line($FormDesign->DeliveryLine->Hline1 ->x1,$FormDesign->DeliveryLine->Hline1->y1,$FormDesign->DeliveryLine->Hline1->x2,$FormDesign->DeliveryLine->Hline1->y2);
           $pdf->line($FormDesign->DeliveryLine->Hline2->x1,$FormDesign->DeliveryLine->Hline2->y1,$FormDesign->DeliveryLine->Hline2->x2,$FormDesign->DeliveryLine->Hline2->y2);
           $pdf->line($FormDesign->DeliveryLine->Vline1->x1,$FormDesign->DeliveryLine->Vline1->y1,$FormDesign->DeliveryLine->Vline1->x2,$FormDesign->DeliveryLine->Vline1->y2);
           /* Draw Text second */    
           $pdf->addText($FormDesign->Pickedby->Name->x, $FormDesign->Pickedby->Name->y, $FormDesign->Pickedby->Name->FontSize, _('Picked By:') );   
           $pdf->addText($FormDesign->Pickedby->Signature->x, $FormDesign->Pickedby->Signature->y, $FormDesign->Pickedby->Signature->FontSize, _('Signature:') );  
           $pdf->addText($FormDesign->Driver->Name->x, $FormDesign->Driver->Name->y, $FormDesign->Driver->Name->FontSize, _('Driver Name:') );
           $pdf->addText($FormDesign->Driver->Signature->x, $FormDesign->Driver->Signature->y, $FormDesign->Driver->Signature->FontSize, _('Signature:') );  
           }
	/* check to see enough space left to print the 4 lines for the totals/footer */
                
                $YPos -= $line_height;
		if (($YPos-$Bottom_Margin)<($line_height)){
                $PageNumber++;
		include ('includes/PO_PDFOrderPageHeader.inc');
		}
//              end while there are line items to print out
//		if ($YPos-$line_height <= $Bottom_Margin){ // need to ensure space for totals
//				$PageNumber++;
//				include ('includes/PO_PDFOrderPageHeader.inc');
//		} //end if need a new page headed up
                
                /* Create GST manually 2011/07/18 by Stan*/
		if ($_POST['ShowAmounts']=='Yes'){
			$DisplayOrderTotal = number_format($OrderTotal,2);
			$DisplayFreight=number_format(0,2);
			$DisplayGST=number_format($OrderTotal*0.1,2);
                        $GSTvalue=$OrderTotal*0.1;
			$DisplayTotal=number_format($OrderTotal+0+$GSTvalue,2);
		} else {
			$DisplayOrderTotal = '----';
		}
if(!isset($MakePDFDocket)){		
$pdf->SetTextColor(0,100,0);
		$pdf->addTextWrap($FormDesign->OrderTotal->SubTotal->x,$FormDesign->OrderTotal->SubTotal->y, $FormDesign->OrderTotal->SubTotal->width, $FormDesign->OrderTotal->SubTotal->FontSize, _('Sale Amount'),'right');
		//$pdf->addTextWrap($FormDesign->OrderTotal->Freight->x,$FormDesign->OrderTotal->Freight->y, $FormDesign->OrderTotal->Freight->width, $FormDesign->OrderTotal->Freight->FontSize, _('(Pro.)Freight'),'right');
		$pdf->addTextWrap($FormDesign->OrderTotal->Tax->x,$FormDesign->OrderTotal->Tax->y, $FormDesign->OrderTotal->Tax->width, $FormDesign->OrderTotal->Tax->FontSize, _('GST'),'right');
	    $pdf->addTextWrap($FormDesign->OrderTotal->Total->x,$FormDesign->OrderTotal->Total->y, $FormDesign->OrderTotal->Total->width, $FormDesign->OrderTotal->Total->FontSize, _('Total'),'right');
$pdf->SetTextColor(0);
	    $pdf->addTextWrap($FormDesign->OrderTotalData->SubTotalData->x,$FormDesign->OrderTotalData->SubTotalData->y,$FormDesign->OrderTotalData->SubTotalData->width,$FormDesign->OrderTotalData->SubTotalData->FontSize,$DisplayOrderTotal, 'right');
	   // $pdf->addTextWrap($FormDesign->OrderTotalData->FreightData->x,$FormDesign->OrderTotalData->FreightData->y,$FormDesign->OrderTotalData->FreightData->width,$FormDesign->OrderTotalData->FreightData->FontSize,$DisplayFreight, 'right');
	    $pdf->addTextWrap($FormDesign->OrderTotalData->TaxData->x,$FormDesign->OrderTotalData->TaxData->y,$FormDesign->OrderTotalData->TaxData->width,$FormDesign->OrderTotalData->TaxData->FontSize,$DisplayGST, 'right');
		$pdf->addTextWrap($FormDesign->OrderTotalData->TotalData->x,$FormDesign->OrderTotalData->TotalData->y,$FormDesign->OrderTotalData->TotalData->width,$FormDesign->OrderTotalData->TotalData->FontSize,$DisplayTotal, 'right');
	} /*end if there are order details to show on the order*/
	//} /* end of check to see that there was an order selected to print */
 }
	$Success = 1; //assume the best and email goes - has to be set to 1 to allow update status
	if ($MakePDFThenDisplayIt){
            if(isset($MakePDFDocket)){
		$pdf->OutputD( 'DD_' . $POHeader['ref_number'] .  '.pdf'); 
            }
            elseif(isset($MakePDFRCTI)){
                $pdf->OutputD( 'RCTI_' . $OrderNo .  '.pdf'); 
            }
            else{
                $pdf->OutputD( 'PO_' . $POHeader['ref_number'] .  '.pdf'); 
            }
		$pdf->__destruct(); //UldisN
	} else { /* must be MakingPDF to email it */
		
		$PdfFileName = 'PO_' . $OrderNo  . '.pdf';
		$pdf->Output($_SESSION['reports_dir'] . '/' . $PdfFileName,'F');
		$pdf->__destruct(); 
		include('includes/htmlMimeMail.php');
		$mail = new htmlMimeMail();
		$attachment = $mail->getFile($_SESSION['reports_dir'] . '/' . $PdfFileName);
		$mail->setText( _('Please find herewith our purchase order number').' ' . $OrderNo);
		$mail->setSubject( _('Purchase Order Number').' ' . $OrderNo);
		$mail->addAttachment($attachment, $PdfFileName, 'application/pdf');
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . "<" . $_SESSION['CompanyRecord']['email'] .">");
		$Success = $mail->send(array($_POST['EmailTo']));
		if ($Success==1){
			$title = _('Email a Purchase Order');
			include('includes/header.inc');
			echo '<div class="centre"><br /><br /><br />';
			prnMsg( _('Purchase Order'). ' ' . $OrderNo.' ' . _('has been emailed to') .' ' . $_POST['EmailTo'] . ' ' . _('as directed'), 'success');
			
		} else { //email failed
			$title = _('Email a Purchase Order');
			include('includes/header.inc');
			echo '<div class="centre"><br /><br /><br />';
			prnMsg( _('Emailing Purchase order'). ' ' . $OrderNo.' ' . _('to') .' ' . $_POST['EmailTo'] . ' ' . _('failed'), 'error');
		}
	}
	if ($ViewingOnly==0 AND $Success==1) {
		$StatusComment =  date($_SESSION['DefaultDateFormat']) .' - ' . _('Printed by') . '<a href="mailto:'.$_SESSION['UserEmail'] .'">'.$_SESSION['UsersRealName']. '</a><br />' . $POHeader['stat_comment'];
		
		$sql = "UPDATE purchorders	SET	allowprint =  0,
										dateprinted  = '" . Date('Y-m-d') . "',
										status = 'Printed',
										stat_comment = '" . $StatusComment . "'
				WHERE purchorders.orderno = '" .  $OrderNo."'";
		$result = DB_query($sql,$db);
	}
	include('includes/footer.inc');
} /* There was enough info to either print or email the purchase order */
 else { /*the user has just gone into the page need to ask the question whether to print the order or email it to the supplier */
	include ('includes/header.inc');
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
        echo '<span style="float:left"><a href="'. $rootpath . '/PO_SelectOSPurchOrder.php">'. _('Back to Purchase Orders'). '</a></span>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if ($ViewingOnly==1){
		echo '<input type=hidden name="ViewingOnly" value=1>';
	}
	echo '<br /><br />';
	echo '<input type=hidden name="OrderNo" value="'. $OrderNo. '">';
	echo '<table><tr><td>'. _('Print or Email the Order'). '</td><td>
		<select name="PrintOrEmail">';
	if (!isset($_POST['PrintOrEmail'])){
		$_POST['PrintOrEmail'] = 'Print';
	}
	if ($ViewingOnly!=0){
		echo '<option selected value="Print">'. _('Print');
	} else {
		if ($_POST['PrintOrEmail']=='Print'){
			echo '<option selected value="Print">'. _('Print');
			echo '<option value="Email">' . _('Email');
		} else {
			echo '<option value="Print">'. _('Print');
			echo '<option selected value="Email">'. _('Email');
		}
	}
	echo '</select></td></tr>';
	echo '<tr><td>'. _('Show Amounts on the Order'). '</td><td>
		<select name="ShowAmounts">';
	if (!isset($_POST['ShowAmounts'])){
		$_POST['ShowAmounts'] = 'Yes';
	}
	if ($_POST['ShowAmounts']=='Yes'){
		echo '<option selected value="Yes">'. _('Yes');
		echo '<option value="No">' . _('No');
	} else {
		echo '<option value="Yes">'. _('Yes');
		echo '<option selected value="No">'. _('No');
	}
	echo '</select></td></tr>';
	if ($_POST['PrintOrEmail']=='Email'){
		$ErrMsg = _('There was a problem retrieving the contact details for the supplier');
		$SQL = "SELECT suppliercontacts.contact,
						suppliercontacts.email
				FROM suppliercontacts INNER JOIN purchorders
				ON suppliercontacts.supplierid=purchorders.supplierno
				WHERE purchorders.orderno='".$OrderNo."'";
		$ContactsResult=DB_query($SQL,$db, $ErrMsg);
		if (DB_num_rows($ContactsResult)>0){
			echo '<tr><td>'. _('Email to') .':</td><td><select name="EmailTo">';
			while ($ContactDetails = DB_fetch_array($ContactsResult)){
				if (strlen($ContactDetails['email'])>2 AND strpos($ContactDetails['email'],'@')>0){
					if ($_POST['EmailTo']==$ContactDetails['email']){
						echo '<option selected value="' . $ContactDetails['email'] . '">' . $ContactDetails['Contact'] . ' - ' . $ContactDetails['email'] . '</option>';
					} else {
						echo '<option value="' . $ContactDetails['email'] . '">' . $ContactDetails['contact'] . ' - ' . $ContactDetails['email'] . '</option>';
					}
				}
			}
			echo '</select></td></tr></table>';
		} else {
			echo '</table><br />';
			prnMsg ( _('There are no contacts defined for the supplier of this order') . '. ' .
				_('You must first set up supplier contacts before emailing an order'), 'error');
			echo '<br />';
		}
	} else {
		echo '</table>';
	}
	echo '<br /><div class="centre"><input type=submit name="DoIt" value="' . _('OK') . '"></div>';
	echo '</form>';
	include('includes/footer.inc');
}
?>