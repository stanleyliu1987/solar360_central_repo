<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include ('includes/htmlMimeMail.php');

//Send Customized PO/DD/CN to Supplier
if (isset($_POST['InvoiceNumber']) and $_POST['InvoiceNumber'] != '') {
    $InvoiceNumber = $_POST['InvoiceNumber'];
}
if(isset($_POST['EmailFromAddr']) and $_POST['EmailFromAddr']!=''){
$EmailFromAddr =  $_POST['EmailFromAddr'];  
}
else{
$EmailFromAddr =  $_SESSION['CompanyRecord']['email'];     
}
$mail = new htmlMimeMail();

/* Send Email Function */
$mail->setHtml(str_replace(array("\r", "\n", '\r', '\n'), '', htmlspecialchars_decode($_POST['EmailMessage'])));
$mail->setHtmlCharset("UTF-8");
$mail->setSubject($_POST['EmailSubject']);
$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $EmailFromAddr . '>');
$mail->setCc($_POST['EmailAddrCC']);
$mail->setBcc($_POST['EmailAddrBCC']);
$Success = $mail->send(array($_POST['EmailAddr']), 'stmp');
/* Record Email Audit Log details */
$emaillog = new EmailAuditLogModel($db);
$emaillogbean = new EmailAuditLogBean();
$emaillogbean->senddate = date('Y-m-d H:i:s');
$emaillogbean->sendstatus = $Success;
$emaillogbean->ordernumber = $_POST['InvoiceNumber'] <> '' ? $_POST['InvoiceNumber'] : '';
$emaillogbean->emailtemplateid = $_POST['ChooseEmailTemplate'] <> '' ? $_POST['ChooseEmailTemplate'] : '';
$emaillogbean->emailfromaddress = $EmailFromAddr;
$emaillogbean->emailtoaddress = $_POST['EmailAddr'] <> '' ? $_POST['EmailAddr'] : '';
$emaillogbean->emailccaddress = $_POST['EmailAddrCC'] <> '' ? $_POST['EmailAddrCC'] : '';
$emaillogbean->emailbccaddress = $_POST['EmailAddrBCC'] <> '' ? $_POST['EmailAddrBCC'] : '';
$emaillogbean->userid = $_SESSION['UserID'] <> '' ? $_SESSION['UserID'] : '';
$emaillog->SaveEmailAuditLog($emaillogbean);

if ($Success == 1) {
    $title = _('Email a Client Stock Delivery Details');
    include('includes/header.inc');
    echo '<div class="centre"><br /><br /><br />';
    prnMsg(_('Client Stock Delivery Details') . ' ' . $InvoiceNumber . ' ' . _('has been emailed to') . ' ' . $_POST['EmailAddr'] . ' ' . _('as directed'), 'success');
} else { //email failed
    $title = _('Email a Client Stock Delivery Details');
    include('includes/header.inc');
    echo '<div class="centre"><br /><br /><br />';
    prnMsg(_('Client Stock Delivery Details') . ' ' . $InvoiceNumber . ' ' . _('to') . ' ' . $_POST['EmailAddr'] . ' ' . _('failed'), 'error');
}





   