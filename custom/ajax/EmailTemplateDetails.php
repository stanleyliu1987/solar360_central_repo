<?php

/* $Id: EmailTemplateDetails.php 19052014
 * Return Email Message to openwysiwyg textarea
 * editor  by Stan $ */

$PathPrefix = '../../';
$_SESSION['DatabaseName']='solar360';
//$PageSecurity = 1; // set security level for webERP 
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');

$TemplateId = $_POST["TemplateId"];
$sql = "SELECT emailtemp_id,emailtype,templatename,emailmessage FROM emailtemplates WHERE emailtemp_id='" . $TemplateId . "'";
$result = DB_query($sql, $db);
$myrow = DB_fetch_array($result);

echo htmlspecialchars_decode($myrow['emailmessage']);
?>