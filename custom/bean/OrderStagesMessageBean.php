<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OrderStagesMessageBean extends Bean{
    protected $id;
    protected $debtortran_fk;
    protected $order_stage_change;
    protected $userid;
    protected $changedatetime;
 
    public function getId() {
        return $this->id;
    }

    public function getDebtortran_fk() {
        return $this->debtortran_fk;
    }

    public function getOrder_stage_change() {
        return $this->order_stage_change;
    }

    public function getUserid() {
        return $this->userid;
    }

    public function getChangedatetime() {
        return $this->changedatetime;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDebtortran_fk($debtortran_fk) {
        $this->debtortran_fk = $debtortran_fk;
    }

    public function setOrder_stage_change($order_stage_change) {
        $this->order_stage_change = $order_stage_change;
    }

    public function setUserid($userid) {
        $this->userid = $userid;
    }

    public function setChangedatetime($changedatetime) {
        $this->changedatetime = $changedatetime;
    }
    
    
}
