<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OrderStagesMessageModel {

    private $db;

    function __construct($db) {
        $this->db = $db;
    }

    /* Save Order Stage Message Details -- by Stan 17062014 */

    function SaveOrderStagesMessage($orderstagemsgbean) {
        $ErrMsg = _('Order Initial Stage cannot be saved because');
        $OrderStagesMessage = DB_query("INSERT INTO order_stages_messages (debtortran_fk,
                                       order_stage_change,
                                       userid,
                                       changedatetime)
                                     VALUES ('" . $orderstagemsgbean->debtortranid . "',
                                            '" . $orderstagemsgbean->orderstagechange . "',
                                            '" . $orderstagemsgbean->userid . "',
                                            '" . $orderstagemsgbean->changedatetime . "')", $this->db, $ErrMsg);
        return $OrderStagesMessage;
    }

    /* Select Order Stage Message Details -- by Stan 17062014 */

    function SelectAllOrderStagesMessage($transfk) {
        $ErrMsg = _('The order stage change message details could not be retrieved by the SQL because');
        $OrderStagesMessage = DB_query("SELECT * FROM order_stages_messages WHERE debtortran_fk='".$transfk."' order by changedatetime desc", $this->db, $ErrMsg);
        return $OrderStagesMessage;
    }

}
