<?php
/* $Id: session.inc 4577 2011-05-28 05:38:42Z daintree $*/

session_start();
$_SESSION['DatabaseName'] = 'solar360';
$_SESSION['DefaultDateFormat']='d/m/Y';

$rootpath = dirname($_SERVER['PHP_SELF']);
if (isset($DirectoryLevelsDeep)){
   for ($i=0;$i<$DirectoryLevelsDeep;$i++){
$rootpath = substr($rootpath,0, strrpos($rootpath,'/'));
} }
if ($rootpath == '/' OR $rootpath == '\\') {;
$rootpath = '';
}
include('function/MiscFunctions.php');
include('ConnectDB.inc');
include('function/DateFunctions.inc');
CheckSessionExpire();


?>