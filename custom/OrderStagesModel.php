<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OrderStagesModel {

    private $db;

    function __construct($db) {
        $this->db = $db;
    }


    /* Select Order Stage Message Details -- by Stan 17062014 */
    function SelectOrderStagesNameById($stages_id) {
        $ErrMsg = _('The order stage details could not be retrieved by the SQL because');
        $stagenameresult=DB_query("SELECT stages FROM order_stages WHERE stages_id='".$stages_id."'", $this->db, $ErrMsg);
        $stagerow=DB_fetch_assoc($stagenameresult);
        return $stagerow['stages'];
    }

}
