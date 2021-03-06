<?php

/* $Id: config.distrib.php 4574 2011-05-25 10:30:45Z daintree $ */

// User configurable variables
//---------------------------------------------------

//DefaultLanguage to use for the login screen and the setup of new users - the users language selection will override
$DefaultLanguage ='en_GB.utf8';

// Whether to display the demo login and password or not on the login screen
$allow_demo_mode = True;

// webERP version

// $Version = '3.13-rc0 UTF-8 capable';

// The timezone of the business - this allows the possibility of having
// the web-server on a overseas machine but record local time
// this is not necessary if you have your own server locally
putenv('TZ=Europe/London');
// putenv('TZ=Australia/Melbourne');
// putenv('TZ=Australia/Sydney');
// putenv('TZ=Pacific/Auckland');

// Connection information for the database
// $host is the computer ip address or name where the database is located
// assuming that the web server is also the sql server
$host = 'localhost';
$mysqlport=3306;
//The type of db server being used - currently only postgres or mysql
$dbType = 'mysqli';
//$dbType = 'postgres' - DEPRECIATED;
//$dbType = 'mysql';
//$dbType = 'mysqli'; for PHP 5 and mysql > 4.1

// sql user & password
$dbuser = 'root';
$dbpassword = 'sentric01';

//It would probably be inappropraite to allow selection of the company in a hosted envionment so this option can be turned off with this parameter
$AllowCompanySelectionBox = true;

//If $AllowCompanySelectionBox = false above then the $DefaultCompany string is entered in the login screen as a default - otherwise the user is expected to know the name of the company to log into.
$DefaultCompany = 'solar360';

//The maximum time that a login session can be idle before automatic logout
//time is in seconds  3600 seconds in an hour
$SessionLifeTime = 3600;

//The maximum time that a script can execute for before the web-server should terminate it
$MaximumExecutionTime =120;

//The path to which session files should be stored in the server - useful for some multi-host web servers
//this can be left commented out
//$SessionSavePath = '/tmp';

// which encryption function should be used
//$CryptFunction = "md5"; // MD5 Hash
$CryptFunction = "sha1"; // SHA1 Hash
//$CryptFunction = ""; // Plain Text

//Setting to 12 or 24 determines the format of the clock display at the end of all screens
$DefaultClock = 12;
//$DefaultClock = 24;



// END OF USER CONFIGURABLE VARIABLES



/*The $rootpath is used in most scripts to tell the script the installation details of the files.

NOTE: In some windows installation this command doesn't work and the administrator must set this to the path of the installation manually:
eg. if the files are under the webserver root directory then rootpath =''; if they are under weberp then weberp is the rootpath - notice no additional slashes are necessary.
*/

$rootpath = dirname($_SERVER['PHP_SELF']);
if (isset($DirectoryLevelsDeep)){
	for ($i=0;$i<$DirectoryLevelsDeep;$i++){
		$rootpath = substr($rootpath,0, strrpos($rootpath,'/'));
	}
}

if ($rootpath == "/" OR $rootpath == "\\") {
	$rootpath = "";
}


/* Report all errors except E_NOTICE
This is the default value set in php.ini for most installations but just to be sure it is forced here
turning on NOTICES destroys things */

error_reporting (E_ALL && ~E_NOTICE);

/*Make sure there is nothing - not even spaces after this last ?> */
?>