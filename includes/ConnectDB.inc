<?php
/* $Id: ConnectDB.inc 4591 2011-06-09 10:33:38Z daintree $*/
/* $Version is compared against the value for config.confvalue WHERE confname=VersionNumber in the database 
 * this value is saved in the $_SESSION['Versionumber'] when includes/GetConfig.php is run
 * if  VersionNumber is < $Version  then the DB update script is run */

$Version='4.04.4'; //must update manually every time there is a DB change

require_once ($PathPrefix .'includes/MiscFunctions.php');

if (!isset($_SESSION['DatabaseName'])){ //need to get the database name from the file structure 
	if (isset($_POST['CompanyNameField'])){
		if (ContainsIllegalCharacters($_POST['CompanyNameField'])){
			prnMsg(_('The company database being logged into cannot contain any of the illegal characters'),'error');
			echo '<br /><a href="index.php">' . _('Back to login page') . '</a>';
			exit;
		}
		if (is_dir('companies/' . $_POST['CompanyNameField']) AND $_POST['CompanyNameField'] != '..'){
			$_SESSION['DatabaseName'] = $_POST['CompanyNameField'];
			include_once ($PathPrefix . 'includes/ConnectDB_' . $dbType . '.inc');
		} else {
			prnMsg(_('The company name entered' . ' (' . $_POST['CompanyNameField'] . ') ' . 'is not configured for use with this installation of webERP. Check that a directory named ' . $_POST['CompanyNameField'] . ' is set up under the companies sub-directory.'),'error');
			prnMsg(_('Check the company name entered' . ' (' . $_POST['CompanyNameField'] . ') ' . 'is the same as the database name.'),'error');
			prnMsg(_('The company name abbreviation entered at login must also have a company directory defined. See your system administrator'),'error');
   		}
	} elseif (isset($DatabaseName)) { /* Scripts that do not require a login must have the $DatabaseName variable set in hard code */
		$_SESSION['DatabaseName'] = $DatabaseName;
		include_once ($PathPrefix . 'includes/ConnectDB_' . $dbType . '.inc');
	} 
} else {
 	include_once($PathPrefix .'includes/ConnectDB_' . $dbType . '.inc');
}

?>
