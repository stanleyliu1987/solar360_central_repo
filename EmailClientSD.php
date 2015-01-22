<?php

/* $Id: EmailCustTrans.php 4590 2011-06-07 10:03:04Z daintree $*/

include ('includes/session.inc');
include ('includes/SQL_CommonFunctions.inc');

if (isset($_GET['CustEmail']) and $_GET['CustEmail']!=''){
$CustEmail=$_GET['CustEmail'];
} 
elseif(isset($_POST['CustEmail']) and $_POST['CustEmail']!='') {
$CustEmail=$_POST['CustEmail'];
}
if (isset($_GET['InvoiceNumber']) and $_GET['InvoiceNumber']!=''){
$InvoiceNumber=$_GET['InvoiceNumber'];
} 
elseif(isset($_POST['InvoiceNumber']) and $_POST['InvoiceNumber']!='') {
$InvoiceNumber=$_POST['InvoiceNumber'];
}
if (isset($_GET['debtorno']) and $_GET['debtorno']!=''){
$debtorno=$_GET['debtorno'];
} 
elseif(isset($_POST['debtorno']) and $_POST['debtorno']!='') {
$debtorno=$_POST['debtorno'];
}
if (isset($_GET['branchcode']) and $_GET['branchcode']!=''){
$branchcode=$_GET['branchcode'];
} 
elseif(isset($_POST['branchcode']) and $_POST['branchcode']!='') {
$branchcode=$_POST['branchcode'];
}
$title=_('Email') . ' ' . $TransactionType . ' ' . _('Number') . ' ' . $InvoiceNumber;
include ('includes/header.inc');
echo '<form action="SD_EmailFunction.php" method=post enctype="multipart/form-data">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type=hidden name="CustEmail" value="' . $CustEmail . '">';
echo '<input type=hidden name="InvoiceNumber" value="' . $InvoiceNumber . '">';
echo '<input type=hidden name="debtorno" value="' . $debtorno . '">';
echo '<input type=hidden name="branchcode" value="' . $branchcode . '">';

/* 16072014 By Stan Retrieve Customer CC address */
$SQL = "SELECT  altemail1, altemail2, altemail3,act_prim,act_alt1,act_alt2,act_alt3
	FROM custbranchemails WHERE branchcode='" . $branchcode . "' 
	AND debtorno='" .$debtorno . "'";

$ErrMsg = _('There was a problem retrieving the email details for the customer');
$ContactResult=DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($ContactResult)>0){
	$EmailAddrRow = DB_fetch_array($ContactResult);
        if($EmailAddrRow['act_alt1']==1 and isset($EmailAddrRow['altemail1'])){
        $EmailCCAddress = $EmailAddrRow['altemail1'];   
        }
        if($EmailAddrRow['act_alt2']==1 and isset($EmailAddrRow['altemail2'])){
        $EmailCCAddress .= ', '.$EmailAddrRow['altemail2'];   
        }
        if($EmailAddrRow['act_alt3']==1 and isset($EmailAddrRow['altemail3'])){
        $EmailCCAddress .= ', '.$EmailAddrRow['altemail3'];   
        }
         
} else {
        $EmailCCAddress='';
}
/* 15052014 Logic to Retrieve Customer Record details, duplicate with code in CustomerInquiry.php */
$EmailSubject="Invoice ".$InvoiceNumber. " Tracking Information";
/* End of logic */

/* 15072014 Retrieve Email Message */
$SearchOrderNumber=substr(trim($InvoiceNumber),1,strpos(trim($InvoiceNumber),'-')-1);
$SearchEmailString = '%' . str_replace(' ', '%', str_replace("  "," ",strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($CustEmail))))) . '%';
$sql="Select * from onlineordertracking AS ont LEFT JOIN stockmoves AS stm ON (ont.transno=stm.transno AND ont.itemcode=stm.stockid) where ont.email like '".$SearchEmailString."' and  ont.order_='".$SearchOrderNumber."'";
$result=DB_query($sql,$db,'','',false,false);
 $_POST['EmailMessage']= '<p class="MsoNormal">Dear [Customer],</p><br/><p class="MsoNormal">Please find below all the tracking information for the Invoice '.$InvoiceNumber.':</p>';
       if($result==0){
       $_POST['EmailMessage'].='No matched record retrieved, Please try to input correct invoice number and email address';
       }
       else{        
     $_POST['EmailMessage'].= '<br/><table class="SD_table_middle">
             <tr><th width="15%">' . _('Part Number') . '</th>
	     <th width="20%">' . _('Description') . '</th>
	     <th width="10%">' . _('Quantity') . '</th>
	     <th width="10%">' . _('Con Note Number') . '</th>
             <th width="15%">' . _('Freight Company Type') . '</th>    
	     <th width="20%">' . _('Estimated / Delivery Date') . '</th>
             <th width="10%">' . _('Comments') . '</th>    </tr>';
       
        $k=0;	//row colour counter 
        while ($myrow=DB_fetch_array($result)){ 
            
            if ($k==1){
			$RowStarter = '<tr class="EvenTableRows">';
			$k=0;
		} else {
			$RowStarter = '<tr class="OddTableRows">';
			 $k=1;
		}
            
            if($myrow['itemcode']=='S360-0000'){
               $myrow['consignment_id']='';
               $myrow['delivery_options']='';
               $myrow['del_est_date']='00/00/0000';
            }

	     $_POST['EmailMessage'].= $RowStarter;
             /*05052014 by Stan Retrieve shipper details */
             $sqlShipper="Select * from shippers where shipper_id='".$myrow['delivery_options']."'";
             $ErrMsg = _('The shipper details could not be retrieved by the SQL because');
             $ShipperResult = DB_query($sqlShipper,$db,$ErrMsg);
             $ShipperRecord = DB_fetch_array($ShipperResult);
             $FormatShipname=explode(' ', $ShipperRecord['shippername'], 2);
             $freightCompany='<a href="'.$ShipperRecord['shipperwebsite'].'" target="_blank">'.$FormatShipname[1].'</a>';
            /* End of Customization */
             $_POST['EmailMessage'].='<td align="center">'.$myrow['itemcode'].'</td>
		      <td>'.$myrow['itemdescription'].'</td>
		      <td align="center">'.$myrow['quantityord'].'</td>
		      <td align="center">'.$myrow['consignment_id'].'</td>
                      <td align="center">'.$freightCompany.'</td>
                      <td align="center">'.ConvertSQLDate($myrow['del_est_date']).'</td>
		      <td align="center">'.$myrow['narrative'].'</td></tr>';

            
        }
        
        $_POST['EmailMessage'].= '</table><p><br>';
        
      /** Check Amount Paid through Receipt**/
     $sqlShipAddress = "SELECT 	deliverto,
				deladd1,
				deladd2,
				deladd3,
				deladd4
				FROM salesorders
				WHERE orderno='".$SearchOrderNumber."'";
		

       $resultShipAddress=DB_query($sqlShipAddress,$db);

       if(DB_num_rows($resultShipAddress)==1){
       	$myrowSA = DB_fetch_array($resultShipAddress);
       }
        $_POST['EmailMessage'].= '<table class="SD_table_bottom"><tr><td>' . _('Additional Information') . '</b></td></tr>';
	$_POST['EmailMessage'].= '<tr><td><b>' . _('Shipping Address: ') . '</td></tr>
	      <tr><td>' . $myrowSA['deladd1']. '
              ' . $myrowSA['deladd2']. ' ' . $myrowSA['deladd3']. ' ' . $myrowSA['deladd4']. '</td></tr></table><br>';
}
$_POST['EmailMessage'].= '<br/><p class="MsoNormal">Please feel free to contact us for further information.</p>';
/* 16072014 Hard Code Signature */
$_POST['EmailMessage'].=  '<p class="MsoNormal"><b><span lang="EN-US" style="font-size:9.0pt;font-family:Arial,sans-serif; font-variant:small-caps;color:navy;mso-ansi-language:EN-US">&nbsp;</span></b></p> <p class="MsoNormal"><b><span lang="EN-US" style="font-size:9.0pt;font-family:Arial,sans-serif; font-variant:small-caps;color:navy;mso-ansi-language:EN-US">Regards, <o:p></o:p></span></b></p> <p class="MsoNormal"><span lang="EN-US" style="font-size: 8pt; font-family: Arial, sans-serif; font-variant: small-caps;">Customer Service Team</span></p><p class="MsoNormal"><span lang="EN-US" style="font-size: 8pt; font-family: Arial, sans-serif; font-variant: small-caps;"><img src="http://erp.solar360.com.au/wysiwyg/uploads/Solar360_erp_sign.jpg" border="0" alt="" hspace="" vspace="" style="width: 80px;"><br></span></p> <p class="MsoNormal"><b><span lang="EN-US" style="font-size: 8pt; font-family: Arial, sans-serif; font-variant: small-caps;">Solar Wholesale and Supply Chain<o:p></o:p></span></b></p> <p class="MsoNormal"><span lang="EN-US" style="font-size: 8pt; font-family: Arial, sans-serif; font-variant: small-caps;">Solar360 Pty Ltd | Level, 18, 499 St. Kilda Road Melbourne 3004<br> Tel: 1300 600 360<o:p></o:p></span></p> <p class="MsoNormal"><u><span lang="EN-US" style="font-size:8.0pt;font-family:Arial,sans-serif; color:blue;mso-ansi-language:EN-US"><a href="http://www.solar360.com.au">www.solar360.com.au</a></span></u><span lang="EN-US" style="color:#1F497D;mso-ansi-language:EN-US"><o:p></o:p></span></p> <p class="MsoNormal"><span lang="EN-US" style="font-size: 8pt; font-family: Arial, sans-serif;">Join us on Facebook:</span><span lang="EN-US" style="font-size:8.0pt;font-family:Arial,sans-serif;color:#1F497D; mso-ansi-language:EN-US"> <a href="http://facebook.com/solar360instalr" target="_blank">http://facebook.com/solar360instalr</a></span></p> <p class="MsoNormal"><b><span lang="EN-US" style="font-size:22.0pt;font-family: Webdings;color:green;mso-ansi-language:EN-US">P </span></b><b><span lang="EN-US" style="font-size:8.0pt;font-family:Arial,sans-serif;color:green;mso-ansi-language: EN-US">Respect the environment: think before you print</span></b></p>';
/* End of retrieving */
echo '<div>
      <p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' .
	_('ClientStockDelivery') . '" alt="" />' . ' ' . _('Invoice Number') . ' : ' . $InvoiceNumber . '<br /></div>';
/*15052014 Bottom Panel and Choose different templates */
echo '<br><div><table><tr><td>'  . _('From Address') . ':</td>
	<td><input type="text" name="EmailFromAddr" maxlength=60 size=60 value="' . $_SESSION['CompanyRecord']['email'] . '"></td></tr>';
echo '<tr><td>'  . _('To Address') . ':</td>
	<td><input type="text" name="EmailAddr" maxlength=60 size=60 value="' . $CustEmail . '"></td></tr>';
echo '<tr><td>' . _('CC') . ':</td>
	<td><input type="text" name="EmailAddrCC" maxlength=60 size=60 value="' . $EmailCCAddress . '"></td></tr>';
echo '<tr><td>' . _('BCC') . ':</td>
	<td><input type="text" name="EmailAddrBCC" maxlength=60 size=60></td></tr>';
echo '<tr><td>'. _('Subject') .':</td>
	<td><input type="Text" name="EmailSubject" value="'. $EmailSubject .'" size=86 maxlength=100 id="SDEmailSubject"></td></tr>';
echo '<tr><td>'. _('Email Message') .':</td>
	<td><textarea id="EmailMessage" name="EmailMessage">'.$_POST['EmailMessage'].'</textarea></td></tr>';
echo '<script>generate_wysiwyg("EmailMessage");</script></table>'; 
echo '<br><div class="centre"><input type=submit name="DoIt" value="' . _('Send') . '">';
echo '</div></div></form>';
include ('includes/footer.inc');
?>