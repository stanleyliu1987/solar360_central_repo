<?php

/* $Id: EmailTemplateDetails.php 19052014
 * Return Email Message to openwysiwyg textarea
 * editor  by Stan $ */

$PathPrefix = '../';
$_SESSION['DatabaseName']='solar360';
//$PageSecurity = 1; // set security level for webERP 
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');

$OrderStagesID = $_POST["OrderStages"];
$TransnoID = $_POST["TransnoID"];
$SQL = "UPDATE debtortrans SET debtortrans.order_stages='".$OrderStagesID."'
        WHERE  debtortrans.transno ='" . $TransnoID. "' and debtortrans.type=10";  
echo $SQL;
$result = DB_query($SQL, $db);
echo $result;
?>