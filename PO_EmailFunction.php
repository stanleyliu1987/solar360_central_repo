<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/DefinePOClass.php');
include ('includes/htmlMimeMail.php');

//Send Customized PO/DD/CN to Supplier
    if(isset($_POST['OrderNo']) and $_POST['OrderNo']!='') {
       $OrderNo=$_POST['OrderNo'];
    }
    $mail = new htmlMimeMail();
    if(isset($_POST['Supp_PDFAttach'])){          
        foreach($_POST['Supp_PDFAttach'] as $tp){ 
            if($tp=="PO"){
              $type= "PO_PDFAttach";
              $PO_PDFLink=Generate_SuppPDF($OrderNo, $type, $db);
              $Attachment = $mail->getFile($PO_PDFLink);
              $mail->addAttachment($Attachment, $PO_PDFLink, 'application/pdf');
            }
            elseif($tp=="DD"){
              $type= "DD_PDFAttach";
              $DD_PDFLink=Generate_SuppPDF($OrderNo, $type, $db);
              $Attachment = $mail->getFile($DD_PDFLink);
              $mail->addAttachment($Attachment, $DD_PDFLink, 'application/pdf');
            }
            elseif($tp=="RCTI"){
              $type= "RCTI_PDFAttach"; 
              $RCTI_PDFLink=Generate_SuppPDF($OrderNo, $type, $db);
              $Attachment = $mail->getFile($RCTI_PDFLink);
              $mail->addAttachment($Attachment, $RCTI_PDFLink, 'application/pdf');
            } 
        }
    }     
         for ($i = 0; $i < count($_FILES['ConsignmentPDF']['name']); $i++) {  
              if(is_uploaded_file($_FILES['ConsignmentPDF']['tmp_name'][$i])){
                $Attachment = $mail->getFile($_FILES['ConsignmentPDF']['tmp_name'][$i]);
                $mail->addAttachment($Attachment, $_FILES['ConsignmentPDF']['name'][$i], 'application/pdf');
              }
          }
                /* Send Email Function */
                $mail->setHtml(str_replace(array("\r","\n",'\r','\n'),'',htmlspecialchars_decode($_POST['EmailMessage'])));
                $mail->setHtmlCharset("UTF-8");
                $mail->setSubject($_POST['EmailSubject']);
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
                $mail->setCc($_POST['EmailAddrCC']);
                $mail->setBcc($_POST['EmailAddrBCC']);
		$Success = $mail->send(array($_POST['EmailAddr']),'smtp');
                /* Record Email Audit Log details */
                $emaillog=new EmailAuditLogModel($db);
                $emaillogbean=new EmailAuditLogBean();
                $emaillogbean->senddate=date('Y-m-d H:i:s');
                $emaillogbean->sendstatus=$Success;
                $emaillogbean->ordernumber=$_POST['InvoiceNumber']<>''?$_POST['InvoiceNumber']:'';
                $emaillogbean->emailtemplateid=$_POST['ChooseEmailTemplate']<>''?$_POST['ChooseEmailTemplate']:'';
                $emaillogbean->emailfromaddress=$_SESSION['CompanyRecord']['email']<>''?$_SESSION['CompanyRecord']['email']:'';
                $emaillogbean->emailtoaddress=$_POST['EmailAddr']<>''?$_POST['EmailAddr']:'';
                $emaillogbean->emailccaddress=$_POST['EmailAddrCC']<>''?$_POST['EmailAddrCC']:'';
                $emaillogbean->emailbccaddress=$_POST['EmailAddrBCC']<>''?$_POST['EmailAddrBCC']:'';
                $emaillogbean->userid=$_SESSION['UserID']<>''?$_SESSION['UserID']:'';
                $emaillog->SaveEmailAuditLog($emaillogbean);
                /* End of record the audit log */
                if(isset($_POST['Supp_PDFAttach'])){ 
		unlink($PO_PDFLink);
                unlink($DD_PDFLink); 
                unlink($RCTI_PDFLink); //delete the temporary file
                }
                if ($Success==1){
			$title = _('Email a Purchase Order');
			include('includes/header.inc');
			echo '<div class="centre"><br /><br /><br />';
			prnMsg( _('Purchase Order'). ' ' . $OrderNo.' ' . _('has been emailed to') .' ' . $_POST['EmailAddr'] . ' ' . _('as directed'), 'success');
			
		} else { //email failed
			$title = _('Email a Purchase Order');
			include('includes/header.inc');
			echo '<div class="centre"><br /><br /><br />';
			prnMsg( _('Emailing Purchase order'). ' ' . $OrderNo.' ' . _('to') .' ' . $_POST['EmailAddr'] . ' ' . _('failed'), 'error');
		}
    
function Generate_SuppPDF($OrderNo, $type ,$db){ 
$title = _('Print Purchase Order Number').' '. $OrderNo;

    if($type=="DD_PDFAttach"){
        $MakePDFDocket= True;
    }
    elseif($type=="RCTI_PDFAttach"){ 
        $MakePDFRCTI=True;
    }
    else{
     $MakePDFDocket=false;
     $MakePDFRCTI=false;   
    }
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
        $POHeader = DB_fetch_array($result);
/* Load the relevant xml file */
       $FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/PurchaseOrder.xml');

// Set the paper size/orintation
	$PaperSize = $FormDesign->PaperSize;
	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', _('Purchase Order') );
	$pdf->addInfo('Subject', _('Purchase Order Number' ) . ' ' . $OrderNo);
	$line_height = $FormDesign->LineHeight;
	$PageNumber = 1;
	/* Then there's an order to print and its not been printed already (or its been flagged for reprinting)
	Now ... Has it got any line items */

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

	if (DB_num_rows($result)>0){
		/*Yes there are line items to start the ball rolling with a page header */
		include('includes/PO_PDFOrderPageHeader.inc');
                $Bottom_Margin=270;
		$OrderTotal = 0;
              
		while (isset($result) and $POLine=DB_fetch_array($result)) {

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

		}
            if(!isset($MakePDFDocket)){	    
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
            }
            
        /* Add warehouse Name */
           $pdf->addText($FormDesign->Warehouse->x,  $FormDesign->Warehouse->y,$FormDesign->Warehouse->FontSize, $WarehouseName, 'left');
                
	/* check to see enough space left to print the 4 lines for the totals/footer */
                
                $YPos -= $line_height;
		if (($YPos-$Bottom_Margin)<($line_height)){
                $PageNumber++;
		include ('includes/PO_PDFOrderPageHeader.inc');
		}
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
            if(isset($MakePDFDocket) and $MakePDFDocket){
                $PdfFileName = 'DD_' . $POHeader['ref_number'];
            }
            elseif(isset($MakePDFRCTI) and $MakePDFRCTI){
                $PdfFileName = 'RCTI_' . $POHeader['ref_number'];
            }
            else{
                $PdfFileName = 'PO_' . $POHeader['ref_number'];
            }
            $pdf->Output($PdfFileName.  '.pdf', 'F'); 
            $pdf->__destruct();
            return $PdfFileName.'.pdf';
} /* There was enough info to either print or email the purchase order */




   