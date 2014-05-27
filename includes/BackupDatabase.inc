<?php
/* $Id: GetPrice.inc 3477 2010-05-30 05:53:40Z daintree $*/
function BackupDatabase (){

	
/*Search by branch and customer for a date specified price */
//$backupFile = 'pricesback/prices_'.date("Y-m-d-H-i-s").'.sql';
//$query      = "SELECT * INTO OUTFILE '$backupFile' FROM $TableName";
//$ErrMsg =  _('The backup function is not working');
//$result = DB_query($query, $db,$ErrMsg);

$dbhost='localhost';
$dbuser='dbuser';
$dbpass='sentric01';
$backupFile = 'prices2013.sql';
$database='solar360';
//$command = "mysqldump --opt -h $dbhost -u $dbuser -p $dbpass | gzip > $backupFile";
//system($command);
	

$command="mysqldump --opt -h localhost -u dbuser -psentric01 solar360 > prices2013.sql";

system($command);

}

?>