<?php

/* $Revision: 1.7 $ */
// User configurable variables
//---------------------------------------------------

//DefaultLanguage to use for the login screen and the setup of new users - the users language selection will override
$DefaultLanguage ='en_GB.utf8';

// Whether to display the demo login and password or not on the login screen
$allow_demo_mode = False;

//  Connection information for the database
// $host is the computer ip address or name where the database is located
// assuming that the web server is also the sql server
$host = 'localhost';

// assuming that the web server is also the sql server
$dbType = 'mysqli';
// assuming that the web server is also the sql server
$dbuser = 'stan';
// assuming that the web server is also the sql server
$dbpassword = 'sentric01';
// The timezone of the business - this allows the possibility of having;
putenv('TZ=Australia/Melbourne');
$AllowCompanySelectionBox = true;
$DefaultCompany = 'solar360';
$SessionLifeTime = 3600;
$MaximumExecutionTime =120;
$CryptFunction = 'sha1';
$DefaultClock = 12;
$rootpath = dirname($_SERVER['PHP_SELF']);
if (isset($DirectoryLevelsDeep)){
   for ($i=0;$i<$DirectoryLevelsDeep;$i++){
$rootpath = substr($rootpath,0, strrpos($rootpath,'/'));
} }
if ($rootpath == '/' OR $rootpath == '\\') {;
$rootpath = '';
}
error_reporting (E_ALL & ~E_NOTICE);
date_default_timezone_set("Australia/Melbourne");
?>
