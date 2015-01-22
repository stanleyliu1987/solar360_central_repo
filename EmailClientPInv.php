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
if (isset($_GET['SalesOrderNo']) and $_GET['SalesOrderNo']!=''){
$ProformaInvNo=$_GET['SalesOrderNo'];
} 
elseif(isset($_POST['SalesOrderNo']) and $_POST['SalesOrderNo']!='') {
$ProformaInvNo=$_POST['SalesOrderNo'];
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
$title=_('Email') . ' ' . $TransactionType . ' ' . _('Number') . ' ' . $ProformaInvNo;
include ('includes/header.inc');
echo '<form action="PDFProformaInv.php" method=post enctype="multipart/form-data">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type=hidden name="CustEmail" value="' . $CustEmail . '">';
echo '<input type=hidden name="ProformaInvNo" value="' . $ProformaInvNo . '">';
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
$EmailSubject="Proforma Invoice ".$ProformaInvNo;
/* End of logic */
/* 15052014 Logic to Retrieve Email Templates Options */
$TemplateSQL= "SELECT * FROM emailtemplates where emailtype=10";
$templates = DB_query($TemplateSQL,$db);
/* End of logic */

echo '<div>
      <p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' .
	_('Send Proforma Invoice Email') . '" alt="" />' . ' ' . _('Proforma Invoice Number') . ' : ' . $ProformaInvNo . '<br /></div>';

echo '<br><div><table><tr><td>'._('Choose a Template:').'<select id="ChooseEmailTemplate" name="ChooseEmailTemplate">';
echo '<option selected>Please Choose a Template</option>';
while ($myrow = DB_fetch_array($templates)) {
echo '<option value='.$myrow["emailtemp_id"].'>'.$myrow["templatename"].'</option>';
}
echo '</select></td></tr>';
/*15052014 Bottom Panel and Choose different templates */
echo '<tr><td>'  . _('From Address') . ':</td>
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