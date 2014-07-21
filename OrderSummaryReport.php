<?php

/* $Id: OrderSummaryReport.php 4572 2011-05-23 10:14:06Z stan$*/
$title = _('Order Summary Report');
include('includes/session.inc');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$OrderSummary = new OrderSummaryModel($db);
$AwaitingInvoice=$OrderSummary->SelectSumAwaitingInvoice(); 
$OpenInvoice = $OrderSummary->SelectSumOpenInvoice();
$CreditHoldInvoice =$OrderSummary->SelectSumCreditHoldInvoice();
$CancelledInvoice =$OrderSummary->SelectSumCancelledInvoice();
$ReleaseStockInvoice =$OrderSummary->SelectSumReleaseStockInvoice();
$DispatchStockInvoice =$OrderSummary->SelectSumDispatchStockInvoice();
$BackOrderInvoice =$OrderSummary->SelectSumBackOrderInvoice();
$DailySales= $OrderSummary->SelectSumDailySales();

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Order Summary Report') . '" alt="" />' . ' ' . _('Order Summar Report') . '</p>';
echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table cellpadding=2 class=selection>';
echo '<th>Name</th><th>Total Value ($)</th><th>Total Number of Invoices</th>';
/* Retrieve Order stage message*/
echo '<tr><td>'._('Awaiting Invoices Summary').'</td><td>'.$AwaitingInvoice['total_value'].'</td><td>'.$AwaitingInvoice['total_number'].'</td></tr>';
echo '<tr><td>'._('Open Invoices Summary').'</td><td>'.$OpenInvoice['total_value'].'</td><td>'.$OpenInvoice['total_number'].'</td></tr>';
echo '<tr><td>'._('Credit Hold Invoice Summary').'</td><td>'.$CreditHoldInvoice['total_value'].'</td><td>'.$CreditHoldInvoice['total_number'].'</td></tr>';
echo '<tr><td>'._('Cancelled Invoice Summary').'</td><td>'.$CancelledInvoice['total_value'].'</td><td>'.$CancelledInvoice['total_number'].'</td></tr>';
echo '<tr><td>'._('Release Stock Summary').'</td><td></td><td>'.$ReleaseStockInvoice['total_number'].'</td></tr>';
echo '<tr><td>'._('Dispatch Stock Summary').'</td><td></td><td>'.$DispatchStockInvoice['total_number'].'</td></tr>';
echo '<tr><td>'._('Back Order Summary').'</td><td></td><td>'.$BackOrderInvoice['total_number'].'</td></tr>';
echo '<tr><td>'._('Daily Sales ').'</td><td>'.$DailySales['salesvalue'].'</td><td>'.$DailySales['ordernum'].'</td></tr>';
echo '</table><br />';
echo '</form></div>';


include('includes/footer.inc');
?>