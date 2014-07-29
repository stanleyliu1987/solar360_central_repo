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
$ReleaseStockInvoice =$OrderSummary->SelectSumReleaseStockInvoice();
$DispatchStockInvoice =$OrderSummary->SelectSumDispatchStockInvoice();
$BackOrderInvoice =$OrderSummary->SelectSumBackOrderInvoice();
$DeliveryIssuesInvoice = $OrderSummary->SelectSumDeliveryIssues();
$DailySales= $OrderSummary->SelectSumDailySales();
echo '<a href="' . $rootpath . '/index.php">' . _('Back to Main Menu') . '</a>';
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Summary Overview') . '" alt="" />' . ' ' . _('Order Summary Overview') . '</p>';
echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table cellpadding=2 class=selection>';
echo '<th>Name</th><th>Total Value ($)</th><th>Total Number of Invoices</th>';
/* Retrieve Order stage message*/
echo '<tr><td><a href="' . $rootpath . '/reports/Awaiting_Invoice_Report" target="_blank">'._('Awaiting Invoices').'</a></td><td class="number">'.number_format($AwaitingInvoice['total_value'],2).'</td><td class="number">'.number_format($AwaitingInvoice['total_number'],0).'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Open_Invoices_Report" target="_blank">'._('Open Invoices').'</a></td><td class="number">'.number_format($OpenInvoice['total_value'],2).'</td><td class="number">'.number_format($OpenInvoice['total_number'],0).'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Credit_Hold_Report" target="_blank">'._('Credit Hold Invoice').'</a></td><td class="number">'.number_format($CreditHoldInvoice['total_value'],2).'</td><td class="number">'.number_format($CreditHoldInvoice['total_number'],0).'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Release_Stock_Report" target="_blank">'._('Release Stock').'</a></td><td></td><td class="number">'.number_format($ReleaseStockInvoice['total_number'],0).'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Dispatch_Stock_Report" target="_blank">'._('Dispatch Stock').'</a></td><td></td><td class="number">'.number_format($DispatchStockInvoice['total_number'],0).'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Back_Orders_Report" target="_blank">'._('Back Order').'</a></td><td></td><td class="number">'.number_format($BackOrderInvoice['total_number'],0).'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/reports/Delivery_Issues_Report" target="_blank">'._('Delivery Issues').'</a></td><td></td><td class="number">'.number_format($DeliveryIssuesInvoice['total_number'],0).'</td></tr>';
echo '<tr><td><a href="' . $rootpath . '/DailySalesInquiry.php" target="_blank">'._('Daily Sales').'</a></td><td class="number">'.number_format($DailySales['salesvalue'],2).'</td><td class="number">'.number_format($DailySales['ordernum'],0).'</td></tr>';
echo '</table><br />';
echo '</form></div>';


include('includes/footer.inc');
?>