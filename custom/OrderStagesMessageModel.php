<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OrderStagesMessageModel{
    
    private $db;
    
    function __construct($db) {
        $this->db=$db;
    }
    /* Save Email Audit Log Details -- by Stan 22052014 */
    function SaveOrderStagesMessage($orderstagemsgbean){
         $ErrMsg =  _('Order Initial Stage cannot be saved because');
         $OrderStagesMessage= DB_query("INSERT INTO order_stages_messages (debtortran_fk,
                                       order_stage_change,
                                       userid,
                                       changedatetime)
                                     VALUES ('" . $orderstagemsgbean->debtortranid ."',
                                            '" . $orderstagemsgbean->orderstagechange ."',
                                            '" . $orderstagemsgbean->userid ."',
                                            '" . $orderstagemsgbean->changedatetime."')",
                                            $this->db, $ErrMsg);
          return $OrderStagesMessage;
    }
}