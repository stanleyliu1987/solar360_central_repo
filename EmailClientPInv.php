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
$CustomerID=$debtorno;
/* 15052014 Logic to Retrieve Customer Record details, duplicate with code in CustomerInquiry.php */
$SQL = "SELECT debtorsmaster.name,
    debtorsmaster.debtorno,
		currencies.currency,
		currencies.decimalplaces,
		paymentterms.terms,
		debtorsmaster.creditlimit,
		holdreasons.dissallowinvoices,
		holdreasons.reasondescription,
		SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount
- debtortrans.alloc) AS balance,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= 0 THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		END) AS due,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " .
		$_SESSION['PastDueDays1'] . ")
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, ". INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ")
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount
			- debtortrans.alloc ELSE 0 END
		END) AS overdue1,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		END) AS overdue2
		FROM debtorsmaster,
     			paymentterms,
     			holdreasons,
     			currencies,
     			debtortrans
		WHERE  debtorsmaster.paymentterms = paymentterms.termsindicator
     		AND debtorsmaster.currcode = currencies.currabrev
     		AND debtorsmaster.holdreason = holdreasons.reasoncode
     		AND debtorsmaster.debtorno = '" . $CustomerID . "'
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
$CustomerResult = DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($CustomerResult)==0){

	/*Because there is no balance - so just retrieve the header information about the customer - the choice is do one query to get the balance and transactions for those customers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = True;

	$SQL = "SELECT debtorsmaster.name, debtorsmaster.debtorno,
					currencies.currency,
					currencies.decimalplaces,
					paymentterms.terms,
					debtorsmaster.creditlimit, 
					holdreasons.dissallowinvoices, 
					holdreasons.reasondescription
			FROM debtorsmaster INNER JOIN paymentterms
			ON debtorsmaster.paymentterms = paymentterms.termsindicator
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason = holdreasons.reasoncode 
			INNER JOIN currencies 
			ON debtorsmaster.currcode = currencies.currabrev
			WHERE debtorsmaster.debtorno = '" . $CustomerID . "'";
		
	$ErrMsg =_('The customer details could not be retrieved by the SQL because');
	$CustomerResult = DB_query($SQL,$db,$ErrMsg);

} else {
	$NIL_BALANCE = False;
}

$CustomerRecord = DB_fetch_array($CustomerResult);

if ($NIL_BALANCE==True){
	$CustomerRecord['balance']=0;
	$CustomerRecord['due']=0;
	$CustomerRecord['overdue1']=0;
	$CustomerRecord['overdue2']=0;
}
$EmailSubject="Invoice For ".$CustomerRecord['name']." (Order: ".$InvoiceNumber.")";
/* End of logic */
echo '<input type=hidden name="CustomerNumber" value="' . $CustomerRecord['debtorno'] . '">';
/* 15052014 Top Panel Content */
echo '<div>
      <p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' .
	_('Customer') . '" alt="" />' . ' ' . _('Customer') . ' : ' . $CustomerRecord['name'] . ' - (' . _('All amounts stated in') .
	' ' . $CustomerRecord['currency'] . ')<br /><br />' . _('Terms') . ' : ' . $CustomerRecord['terms'] . '<br />' . _('Credit Limit') .
	': ' . number_format($CustomerRecord['creditlimit'],0) . ' ' . _('Credit Status') . ': ' . $CustomerRecord['reasondescription'] . '</p>';
echo '<table>
	<tr>
		<th width=20%>' . _('Total Balance') . '</th>
		<th width=20%>' . _('Current') . '</th>
		<th width=20%>' . _('Now Due') . '</th>
		<th width=20%>' . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</th>
		<th width=20%>' . _('Over') . ' ' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</th></tr>';

echo '<tr><td class=number>' . number_format($CustomerRecord['balance'],$CustomerRecord['decimalplaces']) . '</td>
	<td class=number>' . number_format(($CustomerRecord['balance'] - $CustomerRecord['due']),$CustomerRecord['decimalplaces']) . '</td>
	<td class=number>' . number_format(($CustomerRecord['due']-$CustomerRecord['overdue1']),$CustomerRecord['decimalplaces']) . '</td>
	<td class=number>' . number_format(($CustomerRecord['overdue1']-$CustomerRecord['overdue2']) ,$CustomerRecord['decimalplaces']) . '</td>
	<td class=number>' . number_format($CustomerRecord['overdue2'],$CustomerRecord['decimalplaces']) . '</td>
	</tr></table></div>';

/* 15052014 Logic to Retrieve Customer Record details, duplicate with code in CustomerInquiry.php */
$EmailSubject="Proforma Invoice For ".$CustomerRecord['name'] ." (Order:".$ProformaInvNo.")";
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