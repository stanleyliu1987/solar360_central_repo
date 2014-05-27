<?php

/* 04042014 by Stan syncronize stockmeasure with stockmaster $*/

include('includes/session.inc');
$title = _('Data Synchronize') . ' / ' . _('Stock Mesurement');
include('includes/header.inc');
$sql = "INSERT INTO stockmeasurement (stockid, unitlength, unitwidth, unitheight )
	SELECT stockid,0,0,0 from stockmaster where stockid not in (SELECT stockid from stockmeasurement)";

$ErrMsg =  _('The stock table and mesurment table could no be syncronized because');
$DbgMsg = _('The SQL that was used to add the item failed was');
$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
if (DB_error_no($db) ==0) {
    prnMsg( _('Stock syncronize process running completed') );
}
else{
    prnMsg( _('Stock syncronize process running failed due to database issue'), 'error');
}
include('includes/footer.inc');

/* End of customization */
?>