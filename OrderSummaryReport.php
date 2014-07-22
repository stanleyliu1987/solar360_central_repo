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
echo '<a href="' . $rootpath . '/index.php">' . _('Back to Main Menu') . '</a>';
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Order Summary Report') . '" alt="" />' . ' ' . _('Order Summar Report') . '</p>';
echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table cellpadding=2 class=selection>';
echo '<th>Name</th><th>Total Value ($)</th><th>Total Number of Invoices</th>';
/* Retrieve Order stage message*/
echo '<tr><td><a href="' . $rootpath . '/reports/Awaiting_Invoice_Report" target="_blank">'._('Awaiting Invoices Summary').'</a></td><td>'.$AwaitingInvoice['total_value'].'</td><td>'.$AwaitingInvoice['total_number'].'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Open_Invoices_Report" target="_blank">'._('Open Invoices Summary').'</a></td><td>'.$OpenInvoice['total_value'].'</td><td>'.$OpenInvoice['total_number'].'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Credit_Hold_Report" target="_blank">'._('Credit Hold Invoice Summary').'</a></td><td>'.$CreditHoldInvoice['total_value'].'</td><td>'.$CreditHoldInvoice['total_number'].'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Cancelled_Invoice_Report" target="_blank">'._('Cancelled Invoice Summary').'</a></td><td>'.$CancelledInvoice['total_value'].'</td><td>'.$CancelledInvoice['total_number'].'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Release_Stock_Report" target="_blank">'._('Release Stock Summary').'</a></td><td></td><td>'.$ReleaseStockInvoice['total_number'].'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Dispatch_Stock_Report" target="_blank">'._('Dispatch Stock Summary').'</a></td><td></td><td>'.$DispatchStockInvoice['total_number'].'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Back_Orders_Report" target="_blank">'._('Back Order Summary').'</a></td><td></td><td>'.$BackOrderInvoice['total_number'].'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/DailySalesInquiry.php" target="_blank">'._('Daily Sales Summary').'</a></td><td>'.$DailySales['salesvalue'].'</td><td>'.$DailySales['ordernum'].'</td></tr>';
echo '</table><br />';
echo '</form></div>';


include('includes/footer.inc');
?>