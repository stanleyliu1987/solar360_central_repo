<?php
// This code was created by phpMyBackupPro v.2.3 
// http://www.phpMyBackupPro.net
$_POST['db']=array("crmsolar_production", "solar360", "team360site_main", "wordpresssites", );
$_POST['tables']="on";
$_POST['data']="on";
$_POST['drop']="on";
$_POST['zip']="zip";
$period=(3600*24)*1;
$security_key="5c3a952c60d870cd669f999102081b4e";
// switch to the phpMyBackupPro v.2.3 directory
@chdir("/client-data/solar360/weberp");
@include("backup.php");
@chdir("/client-data/solar360/weberp/");
?>