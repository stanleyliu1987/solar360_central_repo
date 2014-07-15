<?php
/* $Id: DateFunctions.inc 4482 2011-02-06 04:29:33Z daintree $*/

/*
date validation and parsing functions

These functions refer to the session variable defining the date format
The date format is defined in SystemParameters called DefaultDateFormat
this can be a string either 'd/m/Y' for UK/Australia/New Zealand dates or
'm/d/Y' for US/Canada format dates

or Y/m/d  for Sweden ;) Anders Eriksson anders@weberp.se.

or d.m.Y  for Germany ;) Juergen Ruemmler heinrich@ruemmler.net

*/


function GetTimeFromDate($DateEntry){
        if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	}
        
        if (strlen($Date_Array[2])>4) {
		$Time_Array= substr(trim($Date_Array[2]),2,6);
	}
        return $Time_Array;
}

function ConvertSQLDate($DateEntry) {

//for MySQL dates are in the format YYYY-mm-dd

	if (strpos($DateEntry,'/')) {
		$Date_Array = explode('/',$DateEntry);
	} elseif (strpos ($DateEntry,'-')) {
		$Date_Array = explode('-',$DateEntry);
	} elseif (strpos ($DateEntry,'.')) {
		$Date_Array = explode('.',$DateEntry);
	} else {
		//prnMsg(_('The date does not appear to be in a valid format. The date being converted from SQL format was:') . ' ' . $DateEntry,'error');
		switch ($_SESSION['DefaultDateFormat']) {
			case 'd/m/Y':
				return '0/0/000';
				break;
			case 'd.m.Y':
				return '0.0.000';
				break;
			case 'm/d/Y':
				return '0/0/0000';
				break;
			case 'Y/m/d':
				return '0000/0/0';
				break;
		}
	}

	if (strlen($Date_Array[2])>4) {  /*chop off the time stuff */
		$Date_Array[2]= substr($Date_Array[2],0,2);
	}

	if ($_SESSION['DefaultDateFormat']=='d/m/Y'){
		return $Date_Array[2].'/'.$Date_Array[1].'/'.$Date_Array[0];
	} elseif ($_SESSION['DefaultDateFormat']=='d.m.Y'){
		return $Date_Array[2].'.'.$Date_Array[1].'.'.$Date_Array[0];
	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y'){
		return $Date_Array[1].'/'.$Date_Array[2].'/'.$Date_Array[0];
	} elseif ($_SESSION['DefaultDateFormat']=='Y/m/d'){
		return $Date_Array[0].'/'.$Date_Array[1].'/'.$Date_Array[2];
	}

} // end function ConvertSQLDate


?>