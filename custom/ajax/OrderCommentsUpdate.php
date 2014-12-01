<?php

/* $Id: OrderStagesUpdate.php 19052014
 * Return Email Message to openwysiwyg textarea
 * editor  by Stan $ */

$PathPrefix = '../';
include($PathPrefix . 'includes/FileIncludes.php');

/* Update debtortrans status by Stan */
DB_query("UPDATE debtortrans SET debtortrans.order_comments='" . $_POST["OrderComments"] . "'
        WHERE  debtortrans.id ='" . $_POST["TransID"] . "'", $db);
?>