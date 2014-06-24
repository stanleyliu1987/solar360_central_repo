<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EmailAuditLogBean extends Bean{
    protected $senddate;
    protected $sendstatus;
    protected $ordernumber;
    protected $emailtemplateid;
    protected $emailfromaddress;
    protected $emailtoaddress;
    protected $emailccaddress;
    protected $emailbccaddress;
    protected $userid;
    public function getUserid() {
        return $this->userid;
    }

    public function setUserid($userid) {
        $this->userid = $userid;
    }

        public function getSenddate() {
        return $this->senddate;
    }

    public function getSendstatus() {
        return $this->sendstatus;
    }

    public function getOrdernumber() {
        return $this->ordernumber;
    }

    public function getEmailtemplateid() {
        return $this->emailtemplateid;
    }

    public function getEmailfromaddress() {
        return $this->emailfromaddress;
    }

    public function getEmailtoaddress() {
        return $this->emailtoaddress;
    }

    public function setSenddate($senddate) {
        $this->senddate = $senddate;
    }

    public function setSendstatus($sendstatus) {
        $this->sendstatus = $sendstatus;
    }

    public function setOrdernumber($ordernumber) {
        $this->ordernumber = $ordernumber;
    }

    public function setEmailtemplateid($emailtemplateid) {
        $this->emailtemplateid = $emailtemplateid;
    }

    public function setEmailfromaddress($emailfromaddress) {
        $this->emailfromaddress = $emailfromaddress;
    }

    public function setEmailtoaddress($emailtoaddress) {
        $this->emailtoaddress = $emailtoaddress;
    }


    public function getEmailccaddress() {
        return $this->emailccaddress;
    }

    public function getEmailbccaddress() {
        return $this->emailbccaddress;
    }

    public function setEmailccaddress($emailccaddress) {
        $this->emailccaddress = $emailccaddress;
    }

    public function setEmailbccaddress($emailbccaddress) {
        $this->emailbccaddress = $emailbccaddress;
    }


    
    
}
