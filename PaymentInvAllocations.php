<?php

/* $Id: PaymentAllocations.php 4306 2010-12-22 16:05:52Z tim_schofield $*/
/* $Revision: 1.6 $ */
/*
	This page is called from SupplierInquiry.php when the 'view payments' button is selected
*/


//$PageSecurity = 5;

include('includes/session.inc');

$title = _('Invoice Payment Allocations');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

	if (!isset($_GET['CustID'])){
		prnMsg( _('Customer ID Number is not Set, can not display result'),'warn');
			include('includes/footer.inc');
			exit;
	}

	if (!isset($_GET['InvID'])){
		prnMsg( _('Invoice Number is not Set, can not display result'),'warn');
		include('includes/footer.inc');
		exit;
	}
$CustID = $_GET['CustID'];
$InvID = $_GET['InvID'];

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Payments') . '" alt="" />' . ' ' . _('Payment Allocation for Customer') . ': ' . $CustID . _(' and') . ' ' . _('Invoice') . ': ' . $InvID . '</p>';

echo '<div class="page_help_text">' . _('This shows how the payment to the customer was allocated') . '<a href="CustomerInquiry.php?CustomerID=' . $CustID . '"><br> ' . _('Back to customer inquiry') . '</a></div><br>';

//echo "<br><font size=4 color=BLUE>Payment Allocation for Supplier: '$SuppID' and Invoice: '$InvID'</font>";

//	$_SESSION['SuppID'] = new SupplierID;
//	$_SESSION['InvID'] = new InvoiceID;

$SQL= "SELECT amt, datealloc
	FROM custallocns
	WHERE transid_allocto='".$InvID."'";

/*
Might be a way of doing this query without a subquery

$SQL= "SELECT supptrans.supplierno,
		supptrans.suppreference,
		supptrans.trandate,
		supptrans.alloc
	FROM supptrans INNER JOIN suppallocs ON supptrans.id=suppallocs.transid_allocfrom
	WHERE supptrans.supplierno = '$SuppID'
	AND supptrans.suppreference = '$InvID'
*/

$Result = DB_query($SQL, $db);
if (DB_num_rows($Result) == 0){
	prnMsg(_('There may be a problem retrieving the information. No data is returned'),'warn');
	echo '<br><a HREF ="javascript:history.back()">' . _('Go back') . '</a>';
	include('includes/foooter.inc');
	exit;
}

echo '<table cellpadding=2 colspan=7 width=80% class=selection>';
$TableHeader = "<tr>
<th>" . _('Payment') . '<br>' . _('Date') . "</th>
<th>" . _('Total Payment') . '<br>' . _('Amount') .	'</th></tr>';

echo $TableHeader;

$j=1;
$k=0; //row colour counter
  while ($myrow = DB_fetch_array($Result)) {
	if ($k == 1){
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k++;
	}

	echo '<td>'.ConvertSQLDate($myrow['datealloc']).'</td>
		<td class=number>'.number_format($myrow['amt'],2).'</td>
		</tr>';

		$j++;
		if ($j == 18){
			$j=1;
			echo $TableHeader;
		}

}
  echo '</table>';

include('includes/footer.inc');
?>
