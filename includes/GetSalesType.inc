<?php
/* $Id: GetPrice.inc 3477 2010-05-30 05:53:40Z daintree $*/
function GetSalesType ($SalesType, $db){

	
	/*Search by branch and customer for a date specified price */
	$sql="SELECT typeabbrev from salestypes where sales_type='".$SalesType."'";
	
	$ErrMsg =  _('There is a problem in retrieving the sales type information for part') . ' ' . _('the error message returned by the SQL server was');
	$result = DB_query($sql, $db,$ErrMsg);

		/*There is a price from one of the above so return that */
	$myrow=DB_fetch_row($result);
	Return $myrow[0];
	

}

?>