<?php

/* $Id: OrderStagesUpdate.php 19052014
 * Return Email Message to openwysiwyg textarea
 * editor  by Stan $ */

$PathPrefix = '../';
include($PathPrefix . 'includes/FileIncludes.php');

/* Update debtortrans status by Stan */
DB_query("UPDATE purchorders SET intostocklocation='" . $_POST["StockLocation"] . "'
        WHERE  orderno ='" . $_POST["PO_OrderNo"] . "'", $db);

?>