<?php

/* $Id: EmailCustTrans.php 4590 2011-06-07 10:03:04Z daintree $*/

include ('includes/session.inc');
include ('includes/SQL_CommonFunctions.inc');

if (isset($_GET['OrderNo']) and $_GET['OrderNo']!=''){
$OrderNo=$_GET['OrderNo'];
} 
elseif(isset($_POST['OrderNo']) and $_POST['OrderNo']!='') {
$OrderNo=$_POST['OrderNo'];
}
$title=_('Email') . ' ' . $TransactionType . ' ' . _('Number') . ' ' . $_GET['FromTransNo'];


include ('includes/header.inc');
echo '<form action="PO_EmailFunction.php" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type=hidden name="OrderNo" value="' . $OrderNo . '">';

$SQL = "SELECT custbranch.email, debtortrans.debtorno, debtortrans.order_, altemail1, altemail2, altemail3,act_prim,act_alt1,act_alt2,act_alt3
		FROM custbranch INNER JOIN debtortrans ON custbranch.debtorno= debtortrans.debtorno
		AND custbranch.branchcode=debtortrans.branchcode
                LEFT JOIN custbranchemails ON custbranch.debtorno= custbranchemails.debtorno
		AND custbranch.branchcode=custbranchemails.branchcode
	WHERE debtortrans.type='" . $TypeCode . "' 
	AND debtortrans.transno='" .$_GET['FromTransNo'] . "'";

$ErrMsg = _('There was a problem retrieving the contact details for the customer');
$ContactResult=DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($ContactResult)>0){
	$EmailAddrRow = DB_fetch_array($ContactResult);
	$CustomerID = $EmailAddrRow['debtorno'];
        $InvoiceNumber= $EmailAddrRow['order_'];
        if($EmailAddrRow['act_prim']==1 and isset($EmailAddrRow['email'])){
        $EmailAddress = $EmailAddrRow['email'];    
        }
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
	$EmailAddress ='';
        $CustomerID='';
        $InvoiceNumber='';
        $EmailCCAddress='';
}
/* 15052014 Logic to Retrieve Customer Record details, duplicate with code in CustomerInquiry.php */
$SQL = "SELECT pur.ref_number,sup.suppname from purchorders as pur left join suppliers as sup on pur.supplierno = sup.supplierid 
        where pur.orderno='". $OrderNo."'";

$ErrMsg = _('The PO details could not be retrieved by the SQL because');
$PO_SUPP_Result = DB_query($SQL,$db,$ErrMsg);
$PO_SUPP_Record = DB_fetch_array($PO_SUPP_Result);
$EmailSubject="Order ".$PO_SUPP_Record['ref_number']." (PO/DD/CN)";
/* End of logic */
/* Retrive PO item details */
$SQL ="SELECT pod.itemcode, pod.itemdescription from purchorderdetails as pod where pod.orderno='". $OrderNo."'";
$PO_DET_Result = DB_query($SQL,$db,$ErrMsg);
if (DB_num_rows($PO_DET_Result)>0){
while ($pod = DB_fetch_array($PO_DET_Result)) {
$POD_Lines.= $pod['itemcode']. ' ('. $pod['itemdescription'].')<p>';
}
}
/* End of retrieving */
/* 15052014 Top Panel Content */
echo '<div>
      <p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' .
	_('Supplier') . '" alt="" />' . ' ' . _('Supplier') . ' : ' . $PO_SUPP_Record['suppname'] . '<br />' .
        _('PO Number') . ' : ' . $PO_SUPP_Record['ref_number'] . '<br />' .
	 _('Stock Items') . ': ' . $POD_Lines . '</p></div>';

/* 03072014 Checkbox for PO/DD/RCTI */
echo '<div align=center>'. _('PO Number:') . '<input type="checkbox" name="Supp_PDFAttach[]" value="PO">
           '. _('Delivery Docket:') . '<input type="checkbox" name="Supp_PDFAttach[]" value="DD"> 
           '. _('RCTI:') . '<input type="checkbox" name="Supp_PDFAttach[]" value="RCTI"> 
      </div>';
/* 15052014 Logic to Retrieve Email Templates Options */
$TemplateSQL= "SELECT * FROM emailtemplates where emailtype=18";
$templates = DB_query($TemplateSQL,$db);
/* End of logic */

/*15052014 Bottom Panel and Choose different templates */
echo '<br><div><table><tr><td>'._('Choose a Template:').'<select id="ChooseEmailTemplate" name="ChooseEmailTemplate">';
echo '<option selected>Please Choose a Template</option>';
while ($myrow = DB_fetch_array($templates)) {
echo '<option value='.$myrow["emailtemp_id"].'>'.$myrow["templatename"].'</option>';
}
echo '</select></td></tr>';
/* 150502014 Email Content Panel */
echo '<tr><td>'  . _('To Address') . ':</td>
	<td><input type="text" name="EmailAddr" maxlength=60 size=60 value="' . $EmailAddress . '"></td></tr>';
echo '<tr><td>' . _('CC') . ':</td>
	<td><input type="text" name="EmailAddrCC" maxlength=60 size=60 value="' . $EmailCCAddress . '"></td></tr>';
echo '<tr><td>' . _('BCC') . ':</td>
	<td><input type="text" name="EmailAddrBCC" maxlength=60 size=60></td></tr>';
echo '<tr><td>'. _('Subject') .':</td>
	<td><input type="Text" name="EmailSubject" value="'. $EmailSubject .'" size=86 maxlength=100></td></tr>';
echo '<tr><td>'. _('Email Message') .':</td>
	<td><textarea id="EmailMessage" name="EmailMessage">'.$_POST['EmailMessage'].'</textarea></td></tr>';
echo '<script>generate_wysiwyg("EmailMessage");</script></table>'; 

echo '<br><div class="centre"><input type=submit name="DoIt" value="' . _('Send') . '">';
echo '</div></div></form>';
include ('includes/footer.inc');
?>