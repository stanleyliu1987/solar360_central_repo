<?php

/* $Id: SuppLoginSetup.php 4540 2011-04-06 10:01:30Z daintree $*/

include('includes/session.inc');
$title = _('Supplier Login Configuration');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$ModuleList = array(_('Orders'),
					_('Receivables'),
					_('Payables'),
					_('Purchasing'),
					_('Inventory'),
					_('Manufacturing'),
					_('General Ledger'),
					_('Asset Manager'),
					_('Petty Cash'),
					_('Setup'));

echo '<a href="' . $rootpath . '/SelectSupplier.php?">' . _('Back to Suppliers') . '</a><br>';

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Supplier') . '" alt="" />' . ' ' . _('Supplier') . ' : ' . $_SESSION['SupplierID'] . _(' has been selected') . '</p><br />';


//Make an array of the security roles where only one role is active and is ID 1

//For the security role selection box, we will only show roles that have:
//- Only one entry in securitygroups AND the tokenid of this entry == 1

//First get all available security role ID's'
$query_roles = "SELECT secroleid FROM securityroles";
$result_roles = DB_query($query_roles, $db);

//Check for every security role if they have only one entry in securitygroups, if so check if the tokenid == 1, then store in selection box
//Then they can be put in the $SecurityRoles array for the selection box;
$SecurityRoles = array();
while ($myroles = DB_fetch_array($result_roles)){

	$sqltoken = "SELECT tokenid FROM securitygroups WHERE secroleid = '" . $myroles['secroleid'] ."'";
	$result = DB_query($sqltoken,$db);
	$Number_roles = DB_num_rows($result);
	$myrow=DB_fetch_array($result);

	if ($Number_roles == 1 && $myrow['tokenid']==9 ) {

		$sql = "SELECT secroleid, secrolename FROM securityroles WHERE secroleid = '" . $myroles['secroleid'] ."'";
		$Sec_Result = DB_query($sql, $db);
		// Now load it into an aray using Key/Value pairs
		while( $Sec_row = DB_fetch_row($Sec_Result) ) {
		$SecurityRoles[$Sec_row[0]] = $Sec_row[1];
		}
		DB_free_result($Sec_Result);

	}

}

if (isset($_GET['SelectedUser'])){
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])){
	$SelectedUser = $_POST['SelectedUser'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (strlen($_POST['UserID'])<3){
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' & + \" \\ " . _('or a space'),'error');
	} elseif (strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'),'error');
	}
	//comment out except for demo!  Do not want anyone modifying demo user.
	/*
	  elseif ($_POST['UserID'] == 'demo') {
		prnMsg(_('The demonstration user called demo cannot be modified.'),'error');
		$InputError = 1;
	}
	*/

	/* Make a comma separated list of modules allowed ready to update the database*/
	$i=0;
	$ModulesAllowed = '';
	while ($i < count($ModuleList)){
		$FormVbl = 'Module_' . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ',';
		$i++;
	}
	$_POST['ModulesAllowed']= $ModulesAllowed;


	if (isset($SelectedUser) AND $InputError !=1) {

/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['Supp']) OR $_POST['Supp']==NULL OR $_POST['Supp']==''){
			$_POST['Supp']='';
		}
		$UpdatePassword = "";
		if ($_POST['Password'] != ""){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		$sql = "UPDATE www_users SET realname='" . $_POST['RealName'] . "',
						supplierid='" . $_POST['Supp'] ."',
						phone='" . $_POST['Phone'] ."',
						email='" . $_POST['Email'] ."',
						" . $UpdatePassword .",
						pagesize='" . $_POST['PageSize'] . "',
						fullaccess=" . $_POST['Access'] . ",
						theme='" . $_POST['Theme'] . "',
						language ='" . $_POST['UserLanguage'] . "',
						defaultlocation='" . $_POST['DefaultLocation'] ."',
						modulesallowed='" . $ModulesAllowed . "',
						blocked=" . $_POST['Blocked'] . "
					WHERE userid = '" . $SelectedUser . "'";

		prnMsg( _('The selected user record has been updated'), 'success' );
	} elseif ($InputError !=1) {

		$sql = "INSERT INTO www_users (userid,
						realname,
						supplierid,
						password,
						phone,
						email,
						pagesize,
						fullaccess,
						defaultlocation,
						lastvisitdate,
						modulesallowed,
						displayrecordsmax,
						theme,
						language)
					VALUES ('" . $_POST['UserID'] . "',
						'" . $_POST['RealName'] ."',
						'" . $_POST['Supp'] ."',
						'" . CryptPass($_POST['Password']) ."',
						'" . $_POST['Phone'] . "',
						'" . $_POST['Email'] ."',
						'" . $_POST['PageSize'] ."',
						'" . $_POST['Access'] . "',
						'" . $_POST['DefaultLocation'] ."',
						'" . date($_SESSION['DefaultDateFormat']) ."',
						'" . $ModulesAllowed . "',
						'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
						'" . $_POST['Theme'] . "',
						'". $_POST['UserLanguage'] ."')";
		prnMsg( _('A new user record has been inserted'), 'success' );
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Supp']);
		unset($_POST['BranchCode']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	// comment out except for demo!  Do not want anyopne deleting demo user.
	/*
	if ($SelectedUser == 'demo') {
		prnMsg(_('The demonstration user called demo cannot be deleted'),'error');
	} else {
	*/
		$sql="SELECT userid FROM audittrail where userid='" .  $SelectedUser ."'";
		$result=DB_query($sql, $db);
		if (DB_num_rows($result)!=0) {
			prnMsg(_('Cannot delete user as entries already exist in the audit trail'), 'warn');
		} else {

			$sql="DELETE FROM www_users WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The User could not be deleted because');;
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('User Deleted'),'info');
		}
		unset($SelectedUser);
	// }

}

if (!isset($SelectedUser)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT userid,
			realname,
			phone,
			email,
			supplierid,
			lastvisitdate,
			fullaccess,
			pagesize,
			theme,
			language
		FROM www_users WHERE supplierid = '" . $_SESSION['SupplierID'] . "'";
	$result = DB_query($sql,$db);

	echo '<table class=selection>';
	echo '<tr><th>' . _('User Login') . '</th>
		<th>' . _('Full Name') . '</th>
		<th>' . _('Telephone') . '</th>
		<th>' . _('Email') . '</th>
		<th>' . _('Supplier Code') . '</th>
		<th>' . _('Last Visit') . '</th>
		<th>' . _('Security Role') .'</th>
		<th>' . _('Report Size') .'</th>
		<th>' . _('Theme') .'</th>
		<th>' . _('Language') .'</th>
	</tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		$LastVisitDate = ConvertSQLDate($myrow['lastvisitdate']);

		/*The SecurityHeadings array is defined in config.php */

		echo '<td>'.$myrow['userid'].'</td>
			<td>'.$myrow['realname'].'</td>
			<td>'.$myrow['phone'].'</td>
			<td>'.$myrow['email'].'</td>
			<td>'.$myrow['supplierid'].'</td>
			<td>'.$LastVisitDate.'</td>
			<td>'.$SecurityRoles[($myrow['fullaccess'])].'</td>
			<td>'.$myrow['pagesize'].'</td>
			<td>'.$myrow['theme'].'</td>
			<td>'.$myrow['language'].'</td>
			<td><a href="' .$_SERVER['PHP_SELF']  . '?SelectedUser=' . $myrow[0] .'">' . _('Edit') . '</a></td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?SelectedUser=' . $myrow[0] . '&delete=1">' . _('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table><br>';
} //end of ifs and buts!


if (isset($SelectedUser)) {
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] .'?">' . _('Review Existing Users') . '</a></div><br>';
}

echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedUser)) {
	//editing an existing User

	$sql = "SELECT userid,
			realname,
			phone,
			email,
			supplierid,
			password,
			branchcode,
			pagesize,
			fullaccess,
			defaultlocation,
			modulesallowed,
			blocked,
			theme,
			language
		FROM www_users
		WHERE userid='" . $SelectedUser . "'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['UserID'] = $myrow['userid'];
	$_POST['RealName'] = $myrow['realname'];
	$_POST['Phone'] = $myrow['phone'];
	$_POST['Email'] = $myrow['email'];
	$_POST['Supp']	= $myrow['supplierid'];
	$_POST['BranchCode']  = $myrow['branchcode'];
	$_POST['PageSize'] = $myrow['pagesize'];
	$_POST['Access'] = $myrow['fullaccess'];
	$_POST['DefaultLocation'] = $myrow['defaultlocation'];
	$_POST['ModulesAllowed'] = $myrow['modulesallowed'];
	$_POST['Theme'] = $myrow['theme'];
	$_POST['UserLanguage'] = $myrow['language'];
	$_POST['Blocked'] = $myrow['blocked'];

	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '">';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '">';
	echo '<input type="hidden" name="ModulesAllowed" value="' . $_POST['ModulesAllowed'] . '">';

	echo '<table class=selection> <tr><td>' . _('User code') . ':</td><td>';
	echo $_POST['UserID'] . '</td></tr>';

} else { //end of if $SelectedUser only do the else when a new record is being entered

	echo '<table class=selection><tr><td>' . _('User Login') . ":</td><td><input type='text' name='UserID' size=22 maxlength=20 ></td></tr>";

	/*set the default modules to show to all
	this had trapped a few people previously*/
	$i=0;
	if (!isset($_POST['ModulesAllowed'])) {
		$_POST['ModulesAllowed']='0,0,1,1,0,0,0,0,0,0,';
	}
	foreach($ModuleList as $ModuleName){
		if ($i>0){
			$_POST['ModulesAllowed'] .=',';
		}
		$_POST['ModulesAllowed'] .= '1';
		$i++;
	}
}

if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
if (!isset($_POST['RealName'])) {
	$_POST['RealName']='';
}
if (!isset($_POST['Phone'])) {
	$_POST['Phone']='';
}
if (!isset($_POST['Email'])) {
	$_POST['Email']='';
}
echo '<tr><td>' . _('Password') . ":</td>
	<td><input type='password' name='Password' size=22 maxlength=20 value='" . $_POST['Password'] . "'></tr>";
echo '<tr><td>' . _('Full Name') . ":</td>
	<td><input type='text' name='RealName' value='" . $_POST['RealName'] . "' size=36 maxlength=35></td></tr>";
echo '<tr><td>' . _('Telephone No') . ":</td>
	<td><input type='text' name='Phone' value='" . $_POST['Phone'] . "' size=32 maxlength=30></td></tr>";
echo '<tr><td>' . _('Email Address') .":</td>
	<td><input type='text' name='Email' value='" . $_POST['Email'] ."' size=32 maxlength=55></td></tr>";
echo '<tr><td>' . _('Security Role') . ":</td><td><select name='Access'>";

foreach ($SecurityRoles as $SecKey => $SecVal) {
	if (isset($_POST['Access']) and $SecKey == $_POST['Access']){
		echo '<option selected value="' . $SecKey . '">' . $SecVal . '</option>';
	} else {
		echo '<option value="' . $SecKey . '">' . $SecVal . '</option>';
	}
}
echo '</select></td></tr>';
echo '<input type="hidden" name="ID" value="'.$_SESSION['UserID'].'">';

echo '<tr><td>' . _('Default Location') . ':</td>
	<td><select name="DefaultLocation">';

$sql = "SELECT loccode, locationname FROM locations";
$result = DB_query($sql,$db);

while ($myrow=DB_fetch_array($result)){

	if (isset($_POST['DefaultLocation']) and $myrow['loccode'] == $_POST['DefaultLocation']){

		echo '<option selected value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';

	} else {
		echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';

	}

}

//Supplier is fixed by selection of supplier
$_POST['Supp']=$_SESSION['SupplierID'];
echo '<input type="hidden" name="Supp" value="' . $_POST['Supp'] . '">';
echo '<tr><td>'._('Supplier Code').':</td>
	<td>' . $_POST['Supp'] . '</td></tr>';

echo '<tr><td>' . _('Reports Page Size') .':</td>
	<td><select name="PageSize">';

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A4'){
	echo '<option selected value="A4">' . _('A4') . '</option>';
} else {
	echo '<option value="A4">' . _('A4') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3'){
	echo '<option selected value="A3">' . _('A3') . '</option>';
} else {
	echo '<option value="A3">' . _('A3') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='A3_landscape'){
	echo '<option selected Value="A3_landscape">' . _('A3') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="A3_landscape">' . _('A3') . ' ' . _('landscape') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter'){
	echo '<option selected value="letter">' . _('Letter') . '</option>';
} else {
	echo '<option value="letter">' . _('Letter') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='letter_landscape'){
	echo '<option selected value="letter_landscape">' . _('Letter') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="letter_landscape">' . _('Letter') . ' ' . _('landscape') . '</option>';
}

if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal'){
	echo '<option selected value="legal">' . _('Legal') . '</option>';
} else {
	echo '<option Value="legal">' . _('Legal') . '</option>';
}
if(isset($_POST['PageSize']) and $_POST['PageSize']=='legal_landscape'){
	echo '<option selected value="legal_landscape">' . _('Legal') . ' ' . _('landscape') . '</option>';
} else {
	echo '<option value="legal_landscape">' . _('Legal') . ' ' . _('landscape') . '</option>';
}

echo '</select></td></tr>';

echo '<tr>
	<td>' . _('Theme') . ':</td>
	<td><select name="Theme">';

$ThemeDirectory = dir('css/');


while (false != ($ThemeName = $ThemeDirectory->read())){

	if (is_dir("css/$ThemeName") AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != 'CVS'){

		if (isset($_POST['Theme']) and $_POST['Theme'] == $ThemeName){
			echo '<option selected value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else if (!isset($_POST['Theme']) and ($_SESSION['DefaultTheme']==$ThemeName)) {
			echo '<option selected value="' . $ThemeName . '">' . $ThemeName . '</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName . '</option>';
		}
	}
}

echo '</select></td></tr>';


echo '<tr>
	<td>' . _('Language') . ':</td>
	<td><select name="UserLanguage">';

 $LangDirHandle = dir('locale/');


while (false != ($LanguageEntry = $LangDirHandle->read())){

	if (is_dir('locale/' . $LanguageEntry) AND $LanguageEntry != '..' AND $LanguageEntry != 'CVS' AND $LanguageEntry!='.'){

		if (isset($_POST['UserLanguage']) and $_POST['UserLanguage'] == $LanguageEntry){
			echo '<option selected value="' . $LanguageEntry . '">' . $LanguageEntry . '</option>';
		} elseif (!isset($_POST['UserLanguage']) and $LanguageEntry == $DefaultLanguage) {
			echo '<option selected value="' . $LanguageEntry . '">' . $LanguageEntry . '</option>';
		} else {
			echo '<option value="' . $LanguageEntry . '">' . $LanguageEntry . '</option>';
		}
	}
}

echo '</select></td></tr>';
/*Make an array out of the comma separated list of modules allowed*/
$ModulesAllowed = explode(',',$_POST['ModulesAllowed']);

$i=0;
foreach($ModuleList as $ModuleName){

	echo '<tr><td>' . _('Display') . ' ' . $ModuleName . ' ' . _('options') . ": </td><td><select name='Module_" . $i . "'>";
	if ($ModulesAllowed[$i]==0){
		echo '<option selected value=0>' . _('No');
		echo '<option value=1>' . _('Yes');
	} else {
	 	echo '<option selected value=1>' . _('Yes');
		echo '<option value=0>' . _('No');
	}
	echo '</select></td></tr>';
	$i++;
}

echo '<tr><td>' . _('Account Status') . ":</td><td><select name='Blocked'>";
if ($_POST['Blocked']==0){
	echo '<option selected value=0>' . _('Open') . '</option>';
	echo '<option value=1>' . _('Blocked') . '</option>';
} else {
 	echo '<option selected value=1>' . _('Blocked') . '</option>';
	echo '<option value=0>' . _('Open') . '</option>';
}
echo '</select></td></tr>';


echo "</table><br>
	<div class='centre'><input type='submit' name='submit' value='" . _('Enter Information') . "'></div></form>";

if (isset($_GET['SelectedUser'])) {
	echo '<script  type="text/javascript">defaultControl(document.forms[0].Password);</script>';
} else {
	echo '<script  type="text/javascript">defaultControl(document.forms[0].UserID);</script>';
}

include('includes/footer.inc');

?>