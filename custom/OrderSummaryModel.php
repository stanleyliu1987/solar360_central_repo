<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OrderSummaryModel {

    private $db;

    function __construct($db) {
        $this->db = $db;
    }

    /* Select Awaiting Invoice Summary Details -- by Stan 21072014 */

    function SelectSumAwaitingInvoice() {
        $ErrMsg = _('The summary of awaiting invoice could not be retrieved by the SQL because');
        $sumawaitinvoice = DB_query("SELECT COUNT(*) AS total_number, SUM(order_value) AS total_value FROM awaiting_invoice_report", $this->db, $ErrMsg);
        return DB_fetch_assoc($sumawaitinvoice);
    }

    /* Select Open Invoice Summary Details -- by Stan 21072014 */

    function SelectSumOpenInvoice() {
        $ErrMsg = _('The summary of open invoice could not be retrieved by the SQL because');
        $sumopeninvoice = DB_query("SELECT COUNT(*) AS total_number, SUM(invoice_value) AS total_value FROM open_invoices_report", $this->db, $ErrMsg);
        return DB_fetch_assoc($sumopeninvoice);
    }

    /* Select Credit Hold Invoice Summary Details -- by Stan 21072014 */

    function SelectSumCreditHoldInvoice() {
        $ErrMsg = _('The summary of credit hold invoice could not be retrieved by the SQL because');
        $sumcreditholdinvoice = DB_query("SELECT COUNT(*) AS total_number, SUM(invoice_value) AS total_value FROM credit_hold_report", $this->db, $ErrMsg);
        return DB_fetch_assoc($sumcreditholdinvoice);
    }

    /* Select Cancelled Invoice Summary Details -- by Stan 21072014 */

    function SelectSumCancelledInvoice() {
        $ErrMsg = _('The summary of cancelled invoice could not be retrieved by the SQL because');
        $sumcancelledinvoice = DB_query("SELECT COUNT(*) AS total_number, SUM(invoice_value) AS total_value FROM cancelled_invoices_report", $this->db, $ErrMsg);
        return DB_fetch_assoc($sumcancelledinvoice);
    }

    /* Select Release Stock Invoice Summary Details -- by Stan 21072014 */

    function SelectSumReleaseStockInvoice() {
        $ErrMsg = _('The summary of Release stock invoice could not be retrieved by the SQL because');
        $sumreleaseinvoice = DB_query("SELECT COUNT(*) AS total_number FROM release_stock_report", $this->db, $ErrMsg);
        return DB_fetch_assoc($sumreleaseinvoice);
    }

    /* Select Dispatch Stock Invoice Summary Details -- by Stan 21072014 */

    function SelectSumDispatchStockInvoice() {
        $ErrMsg = _('The summary of Dispatch stock invoice could not be retrieved by the SQL because');
        $sumdispatchinvoice = DB_query("SELECT COUNT(*) AS total_number FROM dispatch_stock_report", $this->db, $ErrMsg);
        return DB_fetch_assoc($sumdispatchinvoice);
    }

    /* Select BackOrder Stock Invoice Summary Details -- by Stan 21072014 */

    function SelectSumBackOrderInvoice() {
        $ErrMsg = _('The summary of BackOrder stock invoice could not be retrieved by the SQL because');
        $sumbackorderinvoice = DB_query("SELECT COUNT(*) AS total_number FROM back_orders_report", $this->db, $ErrMsg);
        return DB_fetch_assoc($sumbackorderinvoice);
    }

    /* Select Daily Sales Summary Details -- by Stan 21072014 */

    function SelectSumDailySales() {
        $ErrMsg = _('The summary of Daily Sales could not be retrieved by the SQL because');
        $sumdailysales = DB_query("SELECT trandate,ROUND(SUM(price*(1-discountpercent)* (-qty)),2) AS salesvalue,
COUNT(DISTINCT stockmoves.transno) AS ordernum
FROM stockmoves
INNER JOIN stockmaster
ON stockmoves.stockid=stockmaster.stockid
INNER JOIN custbranch 
ON stockmoves.debtorno=custbranch.debtorno
AND stockmoves.branchcode=custbranch.branchcode
WHERE (stockmoves.type=10 OR stockmoves.type=11) AND trandate=CURDATE()", $this->db, $ErrMsg);
        return DB_fetch_assoc($sumdailysales);
    }

}
