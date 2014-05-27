<?php
/* $Id: Z_ImportStocks.php 4563 2011-05-11 09:59:44Z daintree $*/
/* Script to make stock locations for all parts that do not have stock location records set up*/

//$PageSecurity = 15;
include('includes/session.inc');
$title = _('Import Items');
include('includes/header.inc');
include('includes/GetSalesType.inc');
//include('includes/BackupDatabase.inc');
// If this script is called with a file object, then the file contents are imported
// If this script is called with the gettemplate flag, then a template file is served
// Otherwise, a file upload form is displayed

$headers = array(
	'StockID',         	//  0 'STOCKID',
	'SalesType',     	//  1 'SalesType',
	'Currency', 	//  2 'Currency',
	'Price'      	//  3 'Price',

);
$correcttype=0;
$NoUpdate=0;
$NoInsert=0;
$NoFail=0;

if ($_FILES['userfile']['name']) { //start file processing
        //BackupDatabase();
	//initialize
	$allowType= array('application/vnd.ms-excel', 'application/x-httpd-php');
	$fieldTarget = 4;

	//check file info
	$fileName = $_FILES['userfile']['name'];
	$tmpName  = $_FILES['userfile']['tmp_name'];
	$fileSize = $_FILES['userfile']['size'];
	$fileType = $_FILES['userfile']['type'];
        foreach ($allowType as $at){
	if ($fileType == $at) { $correcttype++;}
	  }

        if($correcttype==0){
        prnMsg (_('File has type '. $fileType. ', but only '. $allowType. ' is allowed.'),'error');
		include('includes/footer.inc');
		exit;
        }
	//get file handle
	$handle = fopen($tmpName, 'r');

	//get the header row
	$headRow = fgetcsv($handle, 10000, ",");

	//check for correct number of fields
	if ( count($headRow) != count($headers) ) {
		prnMsg (_('File contains '. count($headRow). ' columns, expected '. count($headers). '. Try downloading a new template.'),'error');
		fclose($handle);
		include('includes/footer.inc');
		exit;
	}

	//test header row field name and sequence
	$head = 0;
	foreach ($headRow as $headField) {
		if ( strtoupper($headField) != strtoupper($headers[$head]) ) {
			prnMsg (_('File contains incorrect headers ('. strtoupper($headField). ' != '. strtoupper($headers[$head]). '. Try downloading a new template.'),'error');
			fclose($handle);
			include('includes/footer.inc');
			exit;
		}
		$head++;
	}

	//start database transaction
	DB_Txn_Begin($db);

	//loop through file rows
	$row = 1;
	while ( ($myrow = fgetcsv($handle, 10000, ",")) !== FALSE ) {
		//check for correct number of fields
		$fieldCount = count($myrow);
		if ($fieldCount != $fieldTarget){
			prnMsg (_($fieldTarget. ' fields required, '. $fieldCount. ' fields received'),'error');
			fclose($handle);
			include('includes/footer.inc');
			exit;
		}

		// cleanup the data (csv files often import with empty strings and such)
		$StockID = strtoupper($myrow[0]);

		//first off check if the item already exists, if exist update price data, otherwise insert price data
		$sql = "SELECT COUNT(prices.stockid) FROM prices WHERE prices.stockid='".$StockID."' and 
                                                                                  prices.typeabbrev='" . $myrow[1]. "' and
                                                                                  prices.currabrev='" . $myrow[2] . "'";
		$result = DB_query($sql,$db);
		$existrow = DB_fetch_row($result);
		if ($existrow[0] != 0) { 
		/* update prices table */
                $sqlupdateprice = 'UPDATE prices  SET   prices.price=' . $myrow[3] . "
							WHERE prices.stockid='" . $StockID . "'
                                                        AND prices.typeabbrev='" . $myrow[1]. "'
							AND prices.currabrev='" . $myrow[2] . "'
                                                        AND prices.debtorno = ''
                                                        AND prices.branchcode = '' ";
                
                $ErrMsg =  _($StockID.' price could not be updated because');
		$DbgMsg = _('The SQL that was used to update the price list failed was');
		$resultUP = DB_query($sqlupdateprice,$db, $ErrMsg, $DbgMsg, true, false);
                       
                        if (DB_error_no($db) ==0) {
				$NoUpdate+=DB_affected_rows($resultUP);
			} else { //location insert failed so set some useful error info
                                $NoFail++;
				DB_Txn_Rollback($db);
				prnMsg(_('Failed update on '. $StockID). _($resultUP),'error');
			}
		}

		else{
		//attempt to insert the stock item
			$sqlinsertprice = "INSERT INTO prices (
					    stockid,
                                            typeabbrev,
                                            currabrev,
                                            price,
                                            startdate,
                                            enddate)
				VALUES ('".$StockID."',
					'" . $myrow[1]  . "',
					'" . $myrow[2]	. "',
					'" . $myrow[3]	. "',
                                        '0000-00-00 00:00:00',
                                        '0000-00-00 00:00:00'); ";

			$ErrMsg =  _($StockID.' price could not be added because');
			$DbgMsg = _('The SQL that was used to add the price list failed was');
			$resultIN = DB_query($sqlinsertprice,$db, $ErrMsg, $DbgMsg, true, false);
                        
                        if (DB_error_no($db) ==0) {
                            $NoInsert+=DB_affected_rows($resultIN);
					
			} else { //location insert failed so set some useful error info
			    $NoFail++;		
                            DB_Txn_Rollback($db);
			    prnMsg(_('Failed import on '. $StockID ). _($resultIN),'error');
			}
                }
		$row++;
	}
        if($NoFail>0){
        DB_Txn_Rollback($db);
	prnMsg( '<br />'. _('0 records Import Successfully.').
                '<br/>'._('0 records Update Successfully.').
                '<br/>'.$NoFail._(' records Import Fails.'), 'error');
        }
        else{
	DB_Txn_Commit($db);
	prnMsg( '<br />'.$NoInsert  . ' '. _(' records Import Successfully.').
                '<br/>'.$NoUpdate._(' records Update Successfully.').
                '<br/>'.$NoFail._(' records Import Fails.'), 'success');
        }
        
	fclose($handle);

} 

elseif ( isset($_POST['gettemplate']) || isset($_GET['gettemplate']) ) { //download an import template

	echo '<br /><br /><br />"'. implode('","',$headers). '"<br /><br /><br />';
        echo  '<br /><a href="' . $rootpath . '/Z_ImportPriceList.php">' . _('Select a file to upload') . '</a>';
  
} 

else { //show file upload form

	echo '  <div>Please backup prices table, before you do any importing job </div>
		<br />
		<a href="Z_ImportPriceList.php?gettemplate=1">Get Import Template</a>
		<br />
		<br />
	';
	echo "<form ENCtype='multipart/form-data' action='Z_ImportPriceList.php' method=post>";
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '">';
       
        echo '1. First Please follow the link to <a href=http://support.sentric.com.au/phpbackup target=_blank>Backup Your Database</a><br/><br/><br/>';
     
	echo "2. <input type='hidden' name='MAX_FILE_SIZE' value='1000000'>" .
			_('Upload Price List CSV file') . ": <input name='userfile' type='file'>
			<input type='submit' VALUE='" . _('Import') . "'>
		</form>
	";

}


include('includes/footer.inc');
?>