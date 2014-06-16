<?php

/* $Id: OrderStagesUpdate.php 19052014
 * Return Email Message to openwysiwyg textarea
 * editor  by Stan $ */

$PathPrefix = '../../';
$_SESSION['DatabaseName'] = 'solar360';
//$PageSecurity = 1; // set security level for webERP 
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');

/* Update debtortrans status by Stan */
DB_query("UPDATE debtortrans SET debtortrans.order_stages='" . $_POST["OrderStages"] . "'
        WHERE  debtortrans.id ='" . $_POST["TransID"] . "'", $db);

/* 16062014 Update Order stage message by Stan */
DB_query("INSERT INTO order_stages_messages (debtortran_fk,order_stage_change,userid,changedatetime)
          VALUES ('" . $_POST["TransID"] ."','" . $_POST["OrderStages"] ."', '" . $_POST["UserID"] ."','" . date('Y-m-d H:i:s')."')",$db);
?>