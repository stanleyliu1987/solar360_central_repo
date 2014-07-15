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
$title=_('Email') . ' ' . $TransactionType . ' ' . _('Number') . ' ' . $InvoiceNumber;
include ('includes/header.inc');
echo '<form action="SD_EmailFunction.php" method=post enctype="multipart/form-data">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type=hidden name="CustEmail" value="' . $CustEmail . '">';
echo '<input type=hidden name="InvoiceNumber" value="' . $InvoiceNumber . '">';

$SQL = "SELECT suw.email, suw.alt1email, suw.alt2email, suw.alt3email from purchorders as puo left join supplierwarehouse as suw on (puo.supplierno=suw.supplierid and
        puo.supwarehouseno=suw.warehousecode) WHERE puo.orderno='".$OrderNo."'";
$ErrMsg = _('There was a problem retrieving the supplier warehouse contact details');
$SupWarehouseResult=DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($SupWarehouseResult)>0){
	$EmailAddrRow = DB_fetch_array($SupWarehouseResult);
	$EmailAddress = $EmailAddrRow['email'];
        if(isset($EmailAddrRow['alt1email']) and $EmailAddrRow['alt1email']!=''){
        $EmailCCAddress = $EmailAddrRow['alt1email'];   
        }
        if(isset($EmailAddrRow['alt2email']) and $EmailAddrRow['alt2email']!=''){
        $EmailCCAddress .= ', '.$EmailAddrRow['alt2email'];   
        }
        if(isset($EmailAddrRow['alt3email']) and $EmailAddrRow['alt3email']!=''){
        $EmailCCAddress .= ', '.$EmailAddrRow['alt3email'];   
        }

         
} else {
	$EmailAddress ='';
        $EmailCCAddress ='';
}
/* 15052014 Logic to Retrieve Customer Record details, duplicate with code in CustomerInquiry.php */
$EmailSubject="Order ".$InvoiceNumber. " Tracking Email";
/* End of logic */

/* 15052014 Logic to Retrieve Email Templates Options */
$TemplateSQL= "SELECT * FROM emailtemplates where emailtype=18";
$templates = DB_query($TemplateSQL,$db);
/* End of logic */

/* 15072014 Retrieve Email Message */
$SearchOrderNumber=substr(trim($InvoiceNumber),1,strpos(trim($InvoiceNumber),'-')-1);
$SearchEmailString = '%' . str_replace(' ', '%', str_replace("  "," ",strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($CustEmail))))) . '%';
$sql="Select * from onlineordertracking where email like '".$SearchEmailString."' and  order_='".$SearchOrderNumber."'";
$result=DB_query($sql,$db,'','',false,false);
       if($result==0){
       $_POST['EmailMessage']='No matched record retrieved, Please try to input correct invoice number and email address';
       }
       else{ 
      /*
       * Retrieve Transaction Date, Invoice Balancem and Delivery Status
       */ 
       $sqlTD = "SELECT id,
                        trandate,
                        debtorno,
                        ((ovamount+ovgst+ovfreight)-alloc) as balancedue,
                        stage
                 FROM debtortransview  WHERE order_='".$InvoiceNumber."'";
      
       $resultTD = DB_query($sqlTD,$db);
       $myrowOS = DB_fetch_array($resultTD);
       /*
        * Retrieve Payment Date
        */
       $sqlPDate ="SELECT MIN(custallocns.datealloc) as paymentdate
				FROM custallocns
				WHERE custallocns.transid_allocto='" . $myrowOS['id'] ."'";
        
        $resultPD=DB_query($sqlPDate,$db);
        if(DB_num_rows($resultPD)==1){
       	$myrowPD = DB_fetch_array($resultPD);
        $PaymentDate=$myrowPD['paymentdate'];
       }
       
       /*
        * Retrieve Total Balance
        */
       $sqlTB = "SELECT SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount- debtortrans.alloc) AS balance
                FROM    debtorsmaster,
     			paymentterms,
     			holdreasons,
     			currencies,
     			debtortrans
                WHERE  debtorsmaster.paymentterms = paymentterms.termsindicator
     		AND debtorsmaster.currcode = currencies.currabrev
     		AND debtorsmaster.holdreason = holdreasons.reasoncode
     		AND debtorsmaster.debtorno = '" . $myrowOS['debtorno'] . "'
     		AND debtorsmaster.debtorno = debtortrans.debtorno
		GROUP BY debtorsmaster.name,
			currencies.currency,
			paymentterms.terms,
			paymentterms.daysbeforedue,
			paymentterms.dayinfollowingmonth,
			debtorsmaster.creditlimit,
			holdreasons.dissallowinvoices,
			holdreasons.reasondescription";
      $ErrMsg = _('The customer details could not be retrieved by the SQL because');
      $CustomerResult = DB_query($sqlTB,$db,$ErrMsg);
      $CustomerRecord = DB_fetch_array($CustomerResult);
      
      if($myrowOS['balancedue']<0.01 and $myrowOS['balancedue']>(-0.01)){
          $myrowOS['balancedue']=0;
      }
      if($CustomerRecord['balance']<0.01 and $CustomerRecord['balance'] >(-0.01)){
          $CustomerRecord['balance']=0;
      }
      $_POST['EmailMessage']= '<table class="SD_table_top">
      
	     <tr><td>' . _('Invoice Number: ') . ' ' . strtoupper($_POST['InvoiceNumber'])  . '</td></tr>
             <tr><td>' . _('Date: ') . ' ' . ConvertSQLDate($myrowOS['trandate'])  . '</td></tr>
             <tr><td>' ._('Invoice Payment Date: ').' '.ConvertSQLDate($PaymentDate).'</td></tr>      
             <tr><td>' ._('Invoice Balance Outstanding: ').' $'.number_format($myrowOS['balancedue'],2).'</td></tr>
             <tr><td>' ._('Invoice Status: ').' '.$myrowOS['stage'].'</td></tr> 
             <tr><td></td><td class="accountbalance" width=20%>' ._('Your Account Balance<sup>1</sup> : ').' $'.number_format($CustomerRecord['balance'],2).'</td></tr>     </table><br/>';
     
      $_POST['EmailMessage'].= '<table class="SD_table_middle">
             <tr><th>' . _('Part Number') . '</th>
	     <th width="40%">' . _('Description') . '</th>
	     <th>' . _('Quantity') . '</th>
	     <th>' . _('Con Note Number') . '</th>
             <th>' . _('Freight Company Type') . '</th>    
	     <th>' . _('Estimated / Delivery Date') . '</th>
             <th>' . _('Comments') . '</th>    </tr>';
       
        $k=0;	//row colour counter 
        while ($myrow=DB_fetch_array($result)){ 
            
            if ($k==1){
			$RowStarter = '<tr class="EvenTableRows">';
			$k=0;
		} else {
			$RowStarter = '<tr class="OddTableRows">';
			 $k=1;
		}
                
            if($myrow['status']!='BackOrder'){
               $myrow['status']=''; 
            } 
            
            if($myrow['itemcode']=='S360-0000'){
               $myrow['consignment_id']='';
               $myrow['delivery_options']='';
               $myrow['del_est_date']='00/00/0000';
               $myrow['status']='';
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
             $_POST['EmailMessage'].='<td>'.$myrow['itemcode'].'</td>
		      <td>'.$myrow['itemdescription'].'</td>
		      <td class=number>'.$myrow['quantityord'].'</td>
		      <td class=number>'.$myrow['consignment_id'].'</td>
                      <td align=center>'.$freightCompany.'</td>
                      <td class=number>'.ConvertSQLDate($myrow['del_est_date']).'</td>
		      <td class=number>'.$myrow['status'].'</td></tr>';

            
        }
        
        $_POST['EmailMessage'].= '</table><p><br>';
        
      /** Check Amount Paid through Receipt**/
     $sqlShipAddress = "SELECT 	deliverto,
				deladd1,
				deladd2,
				deladd3,
				deladd4
				FROM salesorders
				WHERE orderno='".$InvoiceNumber."'";
		

       $resultShipAddress=DB_query($sqlShipAddress,$db);

       if(DB_num_rows($resultShipAddress)==1){
       	$myrowSA = DB_fetch_array($resultShipAddress);
       }
        $_POST['EmailMessage'].= '<table class="SD_table_bottom"><tr><td>' . _('Additional Information') . '</b></td></tr>';
	$_POST['EmailMessage'].= '<tr><td><b>' . _('Shipping Address: ') . '</td></tr>
	      <tr><td>' . $myrowSA['deladd1']. '
              ' . $myrowSA['deladd2']. ' ' . $myrowSA['deladd3']. ' ' . $myrowSA['deladd4']. '</td></tr></table><br>';
}
/* End of retrieving */
echo '<div>
      <p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' .
	_('ClientStockDelivery') . '" alt="" />' . ' ' . _('Invoice Number') . ' : ' . $InvoiceNumber . '<br /></div>';
/*15052014 Bottom Panel and Choose different templates */
echo '<br><div><table><tr><td>'._('Choose a Template:').'<select id="ChooseEmailTemplate" name="ChooseEmailTemplate">';
echo '<option selected>Please Choose a Template</option>';
while ($myrow = DB_fetch_array($templates)) {
echo '<option value='.$myrow["emailtemp_id"].'>'.$myrow["templatename"].'</option>';
}
echo '</select></td></tr>';
/* 150502014 Email Content Panel */
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