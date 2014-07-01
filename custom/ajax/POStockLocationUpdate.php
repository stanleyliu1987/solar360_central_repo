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
DB_query("UPDATE purchorders SET intostocklocation='" . $_POST["StockLocation"] . "'
        WHERE  orderno ='" . $_POST["PO_OrderNo"] . "'", $db);

?>