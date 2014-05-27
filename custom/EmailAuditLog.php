<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EmailAuditLog{
    
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
                                                     emailccaddress)
                                     VALUES ('" . $emaillogbean->senddate ."',
                                            '" . $emaillogbean->sendstatus ."',
                                            '" . $emaillogbean->ordernumber ."',
                                            '" . $emaillogbean->emailtemplateid."',
                                            '" . $emaillogbean->emailfromaddress."',
                                            '" . $emaillogbean->emailtoaddress."',
                                            '" . $emaillogbean->emailccaddress."')",
                                            $this->db, $ErrMsg);
          return $SaveEmailAuditLog;
    }
}