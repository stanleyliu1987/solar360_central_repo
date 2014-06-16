<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CustomerTransSearchModel{
    
    private $db;
    private $CustomerID;
    private $PastDueDays1;
    private $PastDueDays2;
    
    function __construct($db, $CustomerID, $PastDueDays1, $PastDueDays2) {
        $this->db=$db;
        $this->CustomerID=$CustomerID;
        $this->PastDueDays1=$PastDueDays1;
        $this->PastDueDays2=$PastDueDays2;
    }
    /* Save Email Audit Log Details -- by Stan 22052014 */
    function SearchCustomerTransResult($param){

 if(isset($param["OrderNumber"]) && $param["OrderNumber"]!=''){
           $SQL = "SELECT debtortrans.id, 
                    debtortrans.transno,
                                        debtortrans.trandate,
                                        debtortrans.order_,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        invoice_status.status as invoice_status,
                                        order_stages.stages as order_stages,
                                        debtortrans.order_stages
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        invoice_status,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND debtortrans.order_='" . $param["OrderNumber"] . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.mod_flag=invoice_status.status_id
                                AND debtortrans.order_stages=order_stages.stages_id  
                                order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";

       
	}
else{
 if (strlen($param["CustName"]) > 0) { 
			//insert wildcard characters in spaces
                        $SQL = "SELECT  debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        invoice_status.status as invoice_status,
                                        order_stages.stages as order_stages,
                                        debtortrans.order_stages
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        invoice_status,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND debtorsmaster.name " . LIKE . " '" . '%' . str_replace(' ', '%', $param['CustName']) . '%' . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.mod_flag=invoice_status.status_id
                                AND debtortrans.order_stages=order_stages.stages_id   
                                 order by debtortrans.trandate desc , debtortrans.sales_ref_num desc"; 
             
		} elseif (strlen($param["CustCode"]) > 0) {  
                        $SQL = "SELECT  debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        invoice_status.status as invoice_status,
                                        order_stages.stages as order_stages,
                                        debtortrans.order_stages
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        invoice_status,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND debtorsmaster.debtorno "  . LIKE . " '%" . strtoupper(trim($param["CustCode"])) . "%'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.mod_flag=invoice_status.status_id
                                AND debtortrans.order_stages=order_stages.stages_id   
                                 order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";
                       
		} elseif (strlen($param['CustPhone']) > 0) {                 
                     $SQL = "SELECT  debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        invoice_status.status as invoice_status,
                                        order_stages.stages as order_stages,
                                        debtortrans.order_stages
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        invoice_status,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND custbranch.phoneno " . LIKE . " '%" . $param['CustPhone'] . "%'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.mod_flag=invoice_status.status_id
                                AND debtortrans.order_stages=order_stages.stages_id   
                                order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";
			// Added an option to search by address. I tried having it search address1, address2, address3, and address4, but my knowledge of MYSQL is limited.  This will work okay if you select the CSV Format then you can search though the address1 field. I would like to extend this to all 4 address fields. Gilles Deacur

		} elseif (strlen($param['CustAdd']) > 0) {
                                         $SQL = "SELECT  debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        invoice_status.status as invoice_status,
                                        order_stages.stages as order_stages,
                                        debtortrans.order_stages
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        invoice_status,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND (debtorsmaster.address1 " . LIKE . " '%" . $param['CustAdd'] . "%'
						OR debtorsmaster.address2 " . LIKE . " '%" . $param['CustAdd'] . "%'
						OR debtorsmaster.address3 "  . LIKE . " '%" . $param['CustAdd'] . "%'
						OR debtorsmaster.address4 "  . LIKE . " '%" . $param['CustAdd'] . "%')
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.mod_flag=invoice_status.status_id
                                AND debtortrans.order_stages=order_stages.stages_id   
                                order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";
			// End added search feature. Gilles Deacur

		} elseif (strlen($param['CustBranchContact']) > 0) {
			$SQL = "SELECT  debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        invoice_status.status as invoice_status,
                                        order_stages.stages as order_stages,
                                        debtortrans.order_stages
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        invoice_status,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND custbranch.contactname " . LIKE . " '" . '%' . str_replace(' ', '%', $param['CustBranchContact']) . '%' . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.mod_flag=invoice_status.status_id
                                AND debtortrans.order_stages=order_stages.stages_id   
                                order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";
			// End added search feature. Gilles Deacur

		}elseif (strlen($param['CustType']) > 0 AND $param['CustType']!='ALL') {
                    	    $SQL = "SELECT  debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        invoice_status.status as invoice_status,
                                        order_stages.stages as order_stages,
                                        debtortrans.order_stages
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        invoice_status,
                                        order_stages,
                                        debtortype
				WHERE  debtortrans.type=10
                                AND debtortype.typename = '" . $param['CustType'] . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.mod_flag=invoice_status.status_id
                                AND debtortrans.order_stages=order_stages.stages_id   
                                AND debtorsmaster.typeid= debtortype.typeid
                                order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";
                            
		} elseif (strlen($param['Area']) > 0 AND $param['Area']!='ALL') {
			$SQL = "SELECT  debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        invoice_status.status as invoice_status,
                                        order_stages.stages as order_stages,
                                        debtortrans.order_stages
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        invoice_status,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND custbranch.area = '" . $param['Area'] . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.mod_flag=invoice_status.status_id
                                AND debtortrans.order_stages=order_stages.stages_id   
                                order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";                    

		} elseif (strlen($param['OrderStage']) > 0 AND $param['OrderStage']!='ALL') { 
                    			$SQL = "SELECT  debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        invoice_status.status as invoice_status,
                                        order_stages.stages as order_stages,
                                        debtortrans.order_stages
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        invoice_status,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND debtortrans.order_stages = '" . $param['OrderStage'] . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.mod_flag=invoice_status.status_id
                                AND debtortrans.order_stages=order_stages.stages_id   
                                order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";  
		}
                           
            else{
                 $SQL = "SELECT debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
                                        debtortrans.order_,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        invoice_status.status as invoice_status,
                                        order_stages.stages as order_stages,
                                        debtortrans.order_stages
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					invoice_status,
                                        order_stages
				WHERE  debtortrans.type=10
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.mod_flag=invoice_status.status_id
                                AND debtortrans.order_stages=order_stages.stages_id
                                order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";
           }   
         }
         $ErrMsg = _('No orders were returned by the SQL because');
	 Return DB_query($SQL,$this->db,$ErrMsg);
    }
    
    /*16062014 Search Over Due Result by Stan*/
    function SearchCustomerOverdueResult(){
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
		$this->PastDueDays1 . ")
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, ". INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= " . $this->PastDueDays1 . ")
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount
			- debtortrans.alloc ELSE 0 END
		END) AS overdue1,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $this->PastDueDays2 . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= " . $this->PastDueDays2 . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		END) AS overdue2
		FROM debtorsmaster,
     			paymentterms,
     			holdreasons,
     			currencies,
     			debtortrans
		WHERE  debtorsmaster.paymentterms = paymentterms.termsindicator
     		AND debtorsmaster.currcode = currencies.currabrev
     		AND debtorsmaster.holdreason = holdreasons.reasoncode
     		AND debtorsmaster.debtorno = '" . $this->CustomerID . "'
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
return DB_fetch_array(DB_query($SQL,$this->db,$ErrMsg));
    }
}