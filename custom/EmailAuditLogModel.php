<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EmailAuditLogModel{
    
    private $db;
    
    function __construct($db) {
        $this->db=$db;
    }
    /* Save Email Audit Log Details -- by Stan 22052014 */
    function SaveEmailAuditLog($emaillogbean){
         $ErrMsg =  _('The Email audit log cannot be saved because');
         $SaveEmailAuditLog= DB_query("INSERT INTO emailauditlog (senddate,
                                                     sendstatus,
                                                     ordernumber,
                                                     emailtemplateid,
                                                     emailfromaddress,
                                                     emailtoaddress,
                                                     emailccaddress,
                                                     emailbccaddress,userid)
                                     VALUES ('" . $emaillogbean->senddate ."',
                                            '" . $emaillogbean->sendstatus ."',
                                            '" . $emaillogbean->ordernumber ."',
                                            '" . $emaillogbean->emailtemplateid."',
                                            '" . $emaillogbean->emailfromaddress."',
                                            '" . $emaillogbean->emailtoaddress."',
                                            '" . $emaillogbean->emailccaddress."', '" . $emaillogbean->emailbccaddress."', '" . $emaillogbean->userid."')",
                                            $this->db, $ErrMsg);
          return $SaveEmailAuditLog;
    }
    
    /* Retrieve Email Audit Log Details by Invoice Number */
    function SelectEmailAuditLogByOrderNumber($ordernumber) {
        $ErrMsg = _('The email audit log details could not be retrieved by the SQL because');
        return DB_query("SELECT userid, senddate FROM emailauditlog WHERE ordernumber='".$ordernumber."'", $this->db, $ErrMsg);
    
    }
}