<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class KPIReportingModel {

    private $db;
    private $InvoiceStartDate;
    private $InvoiceEndDate;

    function __construct($db, $InvoiceStartDate, $InvoiceEndDate) {
        $this->db = $db;
        $this->InvoiceStartDate = $InvoiceStartDate;
        $this->InvoiceEndDate = $InvoiceEndDate;
    }

    /* Retrieve Total Invoice -- by Stan 13102014 */

    function TotalInvoiceResult() {

        $SQL = "SELECT * FROM `import_csv_salesorders`  AS ics
WHERE DATE(ics.`datepurchased`) BETWEEN '" . $this->InvoiceStartDate . "' AND '" . $this->InvoiceEndDate . "' GROUP BY Number";
        $ErrMsg = _('No total order number were returned by the SQL because');
        $TotalInvoiceResult= DB_query($SQL, $this->db, $ErrMsg);
        return DB_num_rows($TotalInvoiceResult);
        
    }
    
      /* Retrieve Total before time Invoice -- by Stan 14102014 */

    function TotalBeforeTimeInvoiceResult() {

        $SQL = "SELECT *  FROM `import_csv_salesorders` AS ics
WHERE TIME(ics.`datepurchased`) <= '09:30:00' and DATE(ics.`datepurchased`) BETWEEN '" . $this->InvoiceStartDate . "' AND '" . $this->InvoiceEndDate . "'  GROUP BY Number";
        $ErrMsg = _('No total order number were returned by the SQL because');
        $TotalBeforeTimeInvoiceResult= DB_query($SQL, $this->db, $ErrMsg);
        return DB_num_rows($TotalBeforeTimeInvoiceResult);
    }
    
      /* Retrieve Total after time Invoice -- by Stan 14102014 */

    function TotalAfterTimeInvoiceResult() {

        $SQL = "SELECT * FROM `import_csv_salesorders` AS ics
WHERE TIME(ics.`datepurchased`) > '09:30:00' and DATE(ics.`datepurchased`) BETWEEN '" . $this->InvoiceStartDate . "' AND '" . $this->InvoiceEndDate . "' GROUP BY Number";
        $ErrMsg = _('No total order number were returned by the SQL because');
        $TotalAfterTimeInvoiceResult= DB_query($SQL, $this->db, $ErrMsg);
        return DB_num_rows($TotalAfterTimeInvoiceResult);
    }
    /* Calculate the KPI 1 Order to Invoice -- by Stan 13102014 */

    function KPIOrderToInvoiceResult() {
        
        $SQL = "SELECT ics.*  FROM `import_csv_salesorders` AS ics INNER JOIN `debtortrans` AS deb ON ics.`Number`=deb.`order_` 
WHERE deb.`inputdate` BETWEEN ics.`datepurchased` AND DATE_ADD(ics.`datepurchased`, INTERVAL 4 HOUR)
AND TIME(ics.`datepurchased`) BETWEEN '09:00:00' AND '17:00:00'
AND DATE(ics.`datepurchased`) BETWEEN '" . $this->InvoiceStartDate . "' AND '" . $this->InvoiceEndDate . "' AND deb.type=10
GROUP BY ics.Number";
        $ErrMsg = _('No total order number were returned by the SQL because');
        $OrderToInvoiceResult= DB_query($SQL, $this->db, $ErrMsg);
        return DB_num_rows($OrderToInvoiceResult);
    }
    
  /* Calculate the KPI 2 Released Invoice to send PO/DD Email -- by Stan 134102014 */  
        function KPIReleasedInvtoPODDEmailResult() {
        
        $SQL = "SELECT COUNT(*) AS Total  FROM `import_csv_salesorders` AS ics INNER JOIN `debtortrans` AS deb ON ics.`Number`=deb.`order_` INNER JOIN
            
(SELECT id, debtortran_fk, order_stage_change,MIN(changedatetime) AS releasetime FROM `order_stages_messages` 
WHERE order_stage_change=2 GROUP BY debtortran_fk,order_stage_change) AS osm
ON osm.debtortran_fk=deb.id INNER JOIN

(SELECT MIN(senddate) AS PODDtime, ordernumber FROM `emailauditlog` AS elog LEFT JOIN `emailtemplates` AS etem ON elog.`emailtemplateid`=etem.`emailtemp_id`
WHERE etem.`emailtype`=18 GROUP BY ordernumber) AS eml
ON eml.ordernumber=deb.order_ 

WHERE HOUR(TIMEDIFF(releasetime, PODDtime)) <=2
AND TIME(ics.`datepurchased`) BETWEEN '09:00:00' AND '17:00:00'
AND DATE(ics.`datepurchased`) BETWEEN '" . $this->InvoiceStartDate . "' AND '" . $this->InvoiceEndDate . "' AND deb.type=10
GROUP BY ics.Number";
        $ErrMsg = _('No total order number were returned by the SQL because');
        $ReleasedInvtoPODDEmailResult= DB_query($SQL, $this->db, $ErrMsg);
        return DB_num_rows($ReleasedInvtoPODDEmailResult);
    }
    
      /* Calculate the KPI 3A Dispatch Stock Date before 9:30 am -- by Stan 134102014 */  
        function KPIDispatchBeforeTimeResult() {
        
        $SQL = "SELECT COUNT(*) AS Total  FROM `import_csv_salesorders` AS ics INNER JOIN `debtortrans` AS deb ON ics.`Number`=deb.`order_` INNER JOIN

(SELECT id, debtortran_fk, order_stage_change,MIN(changedatetime) AS dispatchtime FROM `order_stages_messages` 
WHERE order_stage_change=3 GROUP BY debtortran_fk,order_stage_change) AS osm
ON osm.debtortran_fk=deb.id 

WHERE osm.dispatchtime <= CONCAT(DATE(ics.`datepurchased`), ' ', '23:59:59')
AND TIME(ics.`datepurchased`) <= '09:30:00'
AND DATE(ics.`datepurchased`) BETWEEN '" . $this->InvoiceStartDate . "' AND '" . $this->InvoiceEndDate . "' AND deb.type=10
GROUP BY ics.Number";
        $ErrMsg = _('No total order number were returned by the SQL because');
        $DispatchBeforeTimeResult= DB_query($SQL, $this->db, $ErrMsg);
        return DB_num_rows($DispatchBeforeTimeResult);
    }
   /* Calculate the KPI 3A Dispatch Stock Date before 9:30 am -- by Stan 134102014 */  
        function KPIDispatchAfterTimeResult() {
        
        $SQL = "SELECT COUNT(*) AS Total FROM `import_csv_salesorders` AS ics INNER JOIN `debtortrans` AS deb ON ics.`Number`=deb.`order_` INNER JOIN

(SELECT id, debtortran_fk, order_stage_change,MIN(changedatetime) AS dispatchtime FROM `order_stages_messages` 
WHERE order_stage_change=3 GROUP BY debtortran_fk,order_stage_change) AS osm
ON osm.debtortran_fk=deb.id 

WHERE osm.dispatchtime <= CONCAT(DATE(DATE_ADD(ics.`datepurchased`, INTERVAL 1 DAY)), ' ', '23:59:59')
AND TIME(ics.`datepurchased`) > '09:30:00'
AND DATE(ics.`datepurchased`) BETWEEN '" . $this->InvoiceStartDate . "' AND '" . $this->InvoiceEndDate . "'AND deb.type=10
GROUP BY ics.Number";
        $ErrMsg = _('No total order number were returned by the SQL because');
        $DispatchAfterTimeResult= DB_query($SQL, $this->db, $ErrMsg);
        return DB_num_rows($DispatchAfterTimeResult);
    }

}
