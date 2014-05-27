<?php
/* $Id: Shippers.php 4370 2010-12-22 16:17:19Z tim_schofield $*/

//$PageSecurity = 15;

include('includes/session.inc');
$title = _('Email Templates Maintenance');
include('includes/header.inc');
if (isset($_GET['SelectedEmailTemplate'])){
	$SelectedEmailTemplate = $_GET['SelectedEmailTemplate'];
} else if (isset($_POST['SelectedEmailTemplate'])){
	$SelectedEmailTemplate = $_POST['SelectedEmailTemplate'];
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if ( isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

        if( trim($_POST['TemplateName']) == '' ) {
		$InputError = 1;
		prnMsg( _("The Template Name may not be empty"), 'error');
		$Errors[$i] = 'TemplateName';
		$i++;
	}
        if( trim($_POST['EmailMessage']) == '' ) {
		$InputError = 1;
		prnMsg( _("The Email's message may not be empty"), 'error');
		$Errors[$i] = 'EmailMessage';
		$i++;
	}

	if (isset($SelectedEmailTemplate) AND $InputError !=1) {

		/*SelectedShipper could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE emailtemplates SET emailtype='" . $_POST['EmailType'] . "', templatename='".$_POST['TemplateName']."' 
                        , emailmessage='".$_POST['EmailMessage']."' WHERE emailtemp_id = '".$SelectedEmailTemplate."'";
		$msg = _('The email template record has been updated');
	} elseif ($InputError !=1) {

	/*SelectedShipper is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Shipper form */

		$sql = "INSERT INTO emailtemplates (emailtype, templatename, emailmessage) VALUES ('" . $_POST['EmailType'] . "','".$_POST['TemplateName']."','".$_POST['EmailMessage']."')";		
                $msg = _('The email template record has been added');
	}

	//run the SQL from either of the above possibilites
	if ($InputError !=1) {
		$result = DB_query($sql,$db);
		echo '<br>';
		prnMsg($msg, 'success');
		unset($SelectedEmailTemplate);
		unset($_POST['EmailType']);
                unset($_POST['TemplateName']);
		unset($_POST['EmailMessage']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

//	$sql= "SELECT COUNT(*) FROM salesorders WHERE salesorders.shipvia='".$SelectedShipper."'";
//	$result = DB_query($sql,$db);
//	$myrow = DB_fetch_row($result);
//	if ($myrow[0]>0) {
//		$CancelDelete = 1;
//		echo '<br>';
//		prnMsg( _('Cannot delete this shipper because sales orders have been created using this shipper') . '. ' . _('There are'). ' '.
//			$myrow[0] . ' '. _('sales orders using this shipper code'), 'error');
//
//	} else {
		$sql="DELETE FROM emailtemplates WHERE emailtemp_id='".$SelectedEmailTemplate."'";
		$result = DB_query($sql,$db);
		echo '<br>';
		prnMsg( _('The email template record has been deleted'), 'success');
	//}
	unset($SelectedEmailTemplate);
	unset($_GET['delete']);
}

if (!isset($SelectedEmailTemplate)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedShipper will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Shippers will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Search') .
		'" alt="" />' . ' ' . $title . '</p>';

	$sql = "SELECT * FROM emailtemplates ORDER BY emailtemp_id";
	$result = DB_query($sql,$db);

	echo '<table class=selection>
		<tr><th>'. _('Email Template ID'). '</th><th>'. _('Email Type'). '</th><th>'. _('Template Name'). '</th></tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
         $EmailTypeList = DB_query('SELECT typeid,typename FROM systypes where typeno<>0', $db);
         while ($tprow = DB_fetch_array($EmailTypeList)) {
			if ($myrow[1] == $tprow['typeid']) {
				$emailtype=$tprow['typename'];
			} 
		}
		printf('<td>%s</td>
			<td>%s</td>
                        <td>%s</td>
			<td><a href="%sSelectedEmailTemplate=%s">'. _('Edit').' </td>
			<td><a href="%sSelectedEmailTemplate=%s&delete=1">'. _('Delete'). '</td></tr>',
			$myrow[0],
			$emailtype,
                        $myrow[2],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow[0],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow[0]);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}


if (isset($SelectedEmailTemplate)) {
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Search') .
		'" alt="" />' . ' ' . $title . '</p>';
	echo '<div class="centre"><a href="'.$_SERVER['PHP_SELF'] . '?' . SID.'">'._('REVIEW RECORDS').'</a></div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedEmailTemplate)) {
		//editing an existing Shipper

		$sql = "SELECT emailtemp_id,emailtype,templatename,emailmessage FROM emailtemplates WHERE emailtemp_id='".$SelectedEmailTemplate."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['EmailTemp_ID'] = $myrow['emailtemp_id'];
		$_POST['EmailType']	= $myrow['emailtype'];
                $_POST['TemplateName']= $myrow['templatename'];
                $_POST['EmailMessage']= $myrow['emailmessage'];

		echo '<input type=hidden name="SelectedEmailTemplate" VALUE='. $SelectedEmailTemplate .'>';
		echo '<input type=hidden name="EmailTemp_ID" VALUE=' . $_POST['EmailTemp_ID'] . '>';
		echo '<br /><table class=selection><tr><td>'. _('Email Template ID').':</td><td>' . $_POST['EmailTemp_ID'] . '</td></tr>';
	} else {
		echo "<br /><table class=selection>";
	}
	if (!isset($_POST['TemplateName'])) {
		$_POST['TemplateName']='';
	}
        
        if (!isset($_POST['EmailMessage'])) {
		$_POST['EmailMessage']='';
	}


	echo '<tr><td>'. _('Template Name') .':</td>
	<td><input type="Text" name="TemplateName"'. (in_array('TemplateName',$Errors) ? 'class="inputerror"' : '' ) .
		' value="'. $_POST['TemplateName'] .'" size=86 maxlength=100></td></tr>';
                    
        echo '<tr><td>'. _('Email Message') .':</td>
	<td><textarea id="EmailMessage" name="EmailMessage"'. (in_array('EmailMessage',$Errors) ? 'class="inputerror"' : '' ).'>'. $_POST['EmailMessage'] .'</textarea></td></tr>';
        echo '<script>generate_wysiwyg("EmailMessage");</script>';
        $EmailTypeList = DB_query('SELECT typeid,typename FROM systypes where typeno<>0', $db);
        echo '<tr><td>'. _('Email Type') .':</td>';
        echo '<td><select name="EmailType" id="EmailType"">';

		while ($myrow = DB_fetch_array($EmailTypeList)) {
			if ($_POST['EmailType'] == $myrow['typeid']) {
				echo '<option  selected value="' . $myrow['typeid'] . '">' . $myrow['typename']  . '</option>';
			} else {
				echo '<option value="' . $myrow['typeid'] . '">' . $myrow['typename']  . '</option>';
			}
		} //end while loop
		
        echo '</select></td></tr></table>';
        echo '<br /><div class="centre"><input type="Submit" name="submit" value="'. _('Enter Information').'"></div>

	</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>