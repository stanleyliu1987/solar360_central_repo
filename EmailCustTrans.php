<?php

/* $Id: EmailCustTrans.php 4590 2011-06-07 10:03:04Z daintree $*/

include ('includes/session.inc');
include ('includes/SQL_CommonFunctions.inc');

if ($_GET['InvOrCredit']=='Invoice'){
	$TransactionType = _('Invoice');
	$TypeCode = 10;
} else {
	$TransactionType = _('Credit Note');
	$TypeCode =11;
}
$title=_('Email') . ' ' . $TransactionType . ' ' . _('Number') . ' ' . $_GET['FromTransNo'];

//if (isset($_POST['DoIt']) AND IsEmailAddress($_POST['EmailAddr'])){
//	if ($_SESSION['InvoicePortraitFormat']==0){
//		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/PrintCustTrans.php?FromTransNo=' . $_POST['TransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">';
//
//		prnMsg(_('The transaction should have been emailed off') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ')' . '<a href="' . $rootpath . '/PrintCustTrans.php?FromTransNo=' . $_POST['FromTransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">' . _('click here') . '</a> ' . _('to email the customer transaction'),'success');
//	} else {
//		echo '<meta http-equiv="Refresh" content=0; url=' . $rootpath . '/PrintCustTransPortrait.php?FromTransNo=' . $_POST['TransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '&EmailSubject='.$_POST['EmailSubject'].'&EmailMessage='.$msg.'>';
//
//		prnMsg(_('The transaction should have been emailed off. If this does not happen (perhaps the browser does not support META Refresh)') . '<a href="' . $rootpath . '/PrintCustTransPortrait.php?FromTransNo=' . $_POST['FromTransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">' . _('click here') . '</a> ' . _('to email the customer transaction'),'success');
//	}
//	exit;
//} elseif (isset($_POST['DoIt'])) {
//	$_GET['InvOrCredit'] = $_POST['InvOrCredit'];
//	$_GET['FromTransNo'] = $_POST['FromTransNo'];
//	prnMsg(_('The email address does not appear to be a valid email address. The transaction was not emailed'),'warn');
//}

include ('includes/header.inc');
$PostPage=$_SESSION['InvoicePortraitFormat']==0 ? 'PrintCustTrans.php':'PrintCustTransPortrait.php';
echo '<form action="'.$PostPage.'" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<input type=hidden name="FromTransNo" value="' . $_GET['FromTransNo'] . '">';
echo '<input type=hidden name="InvOrCredit" value="' . $_GET['InvOrCredit'] . '">';

echo '<input type=hidden name="PrintPDF" value="Yes">';


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
$SQL = "SELECT debtorsmaster.name,
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

	$SQL = "SELECT debtorsmaster.name, 
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

/* 15052014 Logic to Retrieve Email Templates Options */
$TemplateSQL= "SELECT * FROM emailtemplates where emailtype=10";
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
echo '<input type=hidden name="InvoiceNumber" value="' . $InvoiceNumber . '">';
echo '<br><div class="centre"><input type=submit name="DoIt" value="' . _('Send') . '"></div></div></form>';
include ('includes/footer.inc');
?>