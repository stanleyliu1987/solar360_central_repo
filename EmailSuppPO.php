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
if (isset($_GET['InvoiceNumber']) and $_GET['InvoiceNumber']!=''){
$InvoiceNumber=$_GET['InvoiceNumber'];
} 
elseif(isset($_POST['InvoiceNumber']) and $_POST['InvoiceNumber']!='') {
$InvoiceNumber=$_POST['InvoiceNumber'];
}
$title=_('Email') . ' ' . $TransactionType . ' ' . _('Number') . ' ' . $InvoiceNumber;
include ('includes/header.inc');
echo '<form action="PO_EmailFunction.php" method=post enctype="multipart/form-data">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type=hidden name="OrderNo" value="' . $OrderNo . '">';

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
$SQL = "SELECT pur.ref_number,sup.suppname, pur.ref_salesorder from purchorders as pur left join suppliers as sup on pur.supplierno = sup.supplierid 
        where pur.orderno='". $OrderNo."'";

$ErrMsg = _('The PO details could not be retrieved by the SQL because');
$PO_SUPP_Result = DB_query($SQL,$db,$ErrMsg);
$PO_SUPP_Record = DB_fetch_array($PO_SUPP_Result);
$EmailSubject="Order ".$PO_SUPP_Record['ref_salesorder'];
/* End of logic */
/* Retrive PO item details */
$SQL ="SELECT pod.itemcode, pod.itemdescription from purchorderdetails as pod where pod.orderno='". $OrderNo."'";
$PO_DET_Result = DB_query($SQL,$db,$ErrMsg);
if (DB_num_rows($PO_DET_Result)>0){
while ($pod = DB_fetch_array($PO_DET_Result)) {
$POD_Lines.= $pod['itemcode']. ' ('. $pod['itemdescription'].')<br/>';
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
echo '<div align=center>'. _('Purchase Order:') . '<input type="checkbox" id="POCheckbox" name="Supp_PDFAttach[]" value="PO">
           '. _('Delivery Docket:') . '<input type="checkbox" id="DDCheckbox" name="Supp_PDFAttach[]" value="DD"> 
           '. _('RCTI:') . '<input type="checkbox" id="RCTICheckbox" name="Supp_PDFAttach[]" value="RCTI"> 
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
echo '<tr><td>'._('Attachments:').'<input type="file" name="ConsignmentPDF[]" multiple /></td></tr>';
/* 150502014 Email Content Panel */
echo '<tr><td>'  . _('To Address') . ':</td>
	<td><input type="text" name="EmailAddr" maxlength=60 size=60 value="' . $EmailAddress . '"></td></tr>';
echo '<tr><td>' . _('CC') . ':</td>
	<td><input type="text" name="EmailAddrCC" maxlength=60 size=60 value="' . $EmailCCAddress . '"></td></tr>';
echo '<tr><td>' . _('BCC') . ':</td>
	<td><input type="text" name="EmailAddrBCC" maxlength=60 size=60></td></tr>';
echo '<tr><td>'. _('Subject') .':</td>
	<td><input type="Text" name="EmailSubject" value="'. $EmailSubject .'" size=86 maxlength=100 id="POEmailSubject"></td></tr>';
echo '<tr><td>'. _('Email Message') .':</td>
	<td><textarea id="EmailMessage" name="EmailMessage">'.$_POST['EmailMessage'].'</textarea></td></tr>';
echo '<script>generate_wysiwyg("EmailMessage");</script></table>'; 
echo '<input type=hidden name="InvoiceNumber" value="' . $InvoiceNumber . '">';
echo '<br><div class="centre"><input type=submit name="DoIt" value="' . _('Send') . '">';
echo '</div></div></form>';
include ('includes/footer.inc');
?>