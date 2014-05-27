<?php
/* $Id: Z_ImportStocks.php 4563 2011-05-11 09:59:44Z daintree $*/
/* Script to make stock locations for all parts that do not have stock location records set up*/

//$PageSecurity = 15;
include('includes/session.inc');
$title = _('Import Items');
include('includes/header.inc');
include('includes/GetSalesType.inc');
ini_set('max_execution_time', 60000);
ini_set("memory_limit", "1024M");
function readCSVFile($file, $countheader){ 
      //BackupDatabase();
	//initialize
	$allowType= array('application/vnd.ms-excel', 'application/x-httpd-php');
        //check file info
	$fileName = $file['name'];
	$tmpName  = $file['tmp_name'];
	$fileSize = $file['size'];
	$fileType = $file['type'];
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
	$headRow = fgetcsv($handle, 0, ",");

	//check for correct number of fields
	if ( count($headRow) != count($countheader) ) {
		prnMsg (_('File contains '. count($headRow). ' columns, expected '. count($countheader). '. Try downloading a new template.'),'error');
		fclose($handle);
		include('includes/footer.inc');
		exit;
	}

	//test header row field name and sequence
	$head = 0;
	foreach ($headRow as $headField) { 
		if ( strtoupper($headField) != strtoupper($countheader[$head]) ) {
			prnMsg (_('File contains incorrect headers ('. strtoupper($headField). ' != '. strtoupper($countheader[$head]). '. Try downloading a new template.'),'error');
			fclose($handle);
			include('includes/footer.inc');
			exit;
		}
		$head++;
	}
        return $handle;
}
//include('includes/BackupDatabase.inc');
// If this script is called with a file object, then the file contents are imported
// If this script is called with the gettemplate flag, then a template file is served
// Otherwise, a file upload form is displayed
$correcttype=0;
$NoUpdate=0;
$NoInsert=0;
$NoFail=0;

/* Upload price list file */
if ( isset($_POST['pricelist']) ) {
    
$headers = array('StockID',         	//  0 'STOCKID',
	         'SalesType',     	//  1 'SalesType',
	         'Currency', 	        //  2 'Currency',
	         'Price'      	        //  3 'Price',
);
if ($_FILES['pricelistfile']['name']) { //start file processing
    
        $handle=readCSVFile($_FILES['pricelistfile'],$headers);
        $fieldTarget = 4;
	//start database transaction
	DB_Txn_Begin($db);

	//loop through file rows
	$row = 1;
	while ( ($myrow = fgetcsv($handle, 0, ",")) !== FALSE ) {
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
        echo  '<br /><a href="' . $rootpath . '/Z_ImportCSVData.php">' . _('Return Back') . '</a>';
	fclose($handle);

  } 
}
/* Upload post code list file */
elseif(isset($_POST['postcodelist'])) {
    
$headers = array('Postcode',         	//  0 'Postcode',
	         'Suburb',     	        //  1 'Suburb',
	         'State', 	        //  2 'State',
	         'RateArea',      	//  3 'RateArea',
                 'RateAreaSubcode',     //  4 'RateAreaSubcode',
                 'RateAreaDescription', //  5 'RateAreaDescription',
                 'RateAreaZones'        //  6 'RateAreaZones',
);
if ($_FILES['postcodelistfile']['name']) { //start file processing
    
        $handle=readCSVFile($_FILES['postcodelistfile'],$headers);
        $fieldTarget = 7;
	//start database transaction
	DB_Txn_Begin($db);

	//loop through file rows
	$row = 1;
        
        $sqlinsertareamaster = "INSERT INTO fre_areamaster (
					   freightcompany,
                                           postcode,
                                           ratearea,
                                           rateareasubcode,
                                           suburb,
                                           state,
                                           rateareadesc,
                                           rateareazone)
				VALUES";
        
        // cleanup the data (csv files often import with empty strings and such)
        $FreightCompany = $_POST['Shipper'];

        //first off check if the item already exists, if exist update price data, otherwise insert price data
        $sql = "DELETE FROM fre_areamaster where freightcompany='".$FreightCompany."'";
        $result = DB_query($sql, $db);
        $existrow = DB_fetch_row($result);

	while ( ($myrow = fgetcsv($handle, 0, ",")) !== FALSE ) {
		//check for correct number of fields
		$fieldCount = count($myrow);
		if ($fieldCount != $fieldTarget){
			prnMsg (_($fieldTarget. ' fields required, '. $fieldCount. ' fields received'),'error');
			fclose($handle);
			include('includes/footer.inc');
			exit;
		}
		//attempt to insert the stock item
		$sqlinsertareamaster .= " ($FreightCompany,
					'" . $myrow[0]  . "',
					'" . $myrow[3]	. "',
					'" . $myrow[4]	. "',
                                        '" . str_replace("'", "\'", $myrow[1]). "',
                                        '" . $myrow[2]	. "',
                                        '" . str_replace("'", "\'", $myrow[5])	. "',
                                        '" . str_replace("'", "\'", $myrow[6])	. "'),";
                $postcode=$myrow[0];
                $ratearea=$myrow[3];
                $rateareasubcode=$myrow[4];
                $suburb=$myrow[1];

	}
        $cleansql=substr($sqlinsertareamaster,0,-1).';';
        $ErrMsg =  _($FreightCompany.' '.$postcode.' '.$suburb.' '.$ratearea.' '.$rateareasubcode. ' areamaster record could not be added because ');
	$DbgMsg = _(' The SQL that was used to add the aremaster record failed was');
	$resultINAM = DB_query($cleansql,$db, $ErrMsg, $DbgMsg, true, false);
    
        if(DB_error_no($db) >0){
        $errormsg=DB_error_msg($db);
        DB_Txn_Rollback($db);
	prnMsg(_('Failed import on  '. _($ErrMsg)).$errormsg,'error');
        }
        else{   
	DB_Txn_Commit($db);
	prnMsg( '<br />'. _('Import Successfully.'),'success');
        }
        echo  '<br /><a href="' . $rootpath . '/Z_ImportCSVData.php">' . _('Return Back') . '</a>'; 
	fclose($handle);

  } 
}

/* Upload area delivery rate list file */
elseif(isset($_POST['adratelist'])) {
    
$headers = array('FromArea',         	//  0 'FromArea',
	         'From',     	        //  1 'From',
	         'ToArea', 	        //  2 'ToArea',
	         'To',      	        //  3 'To',
                 'MinCharge',           //  4 'MinCharge'  
                 'CommonRate',          //  5 'CommonRate',
                 'Range1',              // 6 'Range1',
                 'Rate1',               // 7 'Rate1',
                 'Range2',              // 8 'Range2',
                 'Rate2',               // 9 'Rate2',
                 'Range3',              // 10 'Range3',
                 'Rate3',               // 11 'Rate3',
                 'Range4',              // 12 'Range4',
                 'Rate4',               // 13 'Rate4',
                 'Range5',              // 14 'Range5',
                 'Rate5',               // 15 'Rate5',
                 'Servicetype'          // 16 'Servicetype,  
);
if ($_FILES['adratelistfile']['name']) { //start file processing
    
        $handle=readCSVFile($_FILES['adratelistfile'],$headers);
        $fieldTarget = 17;
	//start database transaction
	DB_Txn_Begin($db);

	//loop through file rows
	$row = 1;
     
        $sqlinsertadrate = "INSERT INTO fre_arearate (
					areadelref,
                                        weightrangeref,
                                        ratevalue)
				VALUES";
        
        // cleanup the data (csv files often import with empty strings and such)
		$FreightCompany=$_POST['ShipperAD'];
                
        //first off check area delivery and rate records
		$sql = "DELETE FROM fre_areadelivery where freightcompany='".$FreightCompany."'";
		$result = DB_query($sql,$db);
		$existrow = DB_fetch_row($result);
        
	while ( ($myrow = fgetcsv($handle, 0, ",")) !== FALSE ) { 
		//check for correct number of fields
		$fieldCount = count($myrow);
		if ($fieldCount != $fieldTarget){
			prnMsg (_($fieldTarget. ' fields required, '. $fieldCount. ' fields received'),'error');
			fclose($handle);
			include('includes/footer.inc');
			exit;
		}
                //Split area code and area sub code
                $FromAreaCode=explode('-',$myrow[0]);
                $ToAreaCode=explode('-',$myrow[2]);
                
                $FromSubcode=ltrim($FromAreaCode[1],'0')==''? 0:ltrim($FromAreaCode[1],'0');
                $ToSubcode=ltrim($ToAreaCode[1],'0')==''? 0:ltrim($ToAreaCode[1],'0');
                
                //Retrieve the service type id
                $sqlservicetype= "Select id from fre_servicetype where typename='".$myrow[16]."'";
                $ErrMsg = _($FreightCompany . ' ' . $fromarea . ' to  ' . $toarea . ' area code record could not be added because ');
                $DbgMsg = _('The SQL that was used to retrieve the service type failed was');
                $resultst = DB_query($sqlservicetype, $db, $ErrMsg, $DbgMsg, true, false);
                $strow = DB_fetch_row($resultst);
                
                //attempt to insert the Delivery Area
		$sqlinsertareadel = " INSERT INTO fre_areadelivery (
					 freightcompany,
                                         fromarea,
                                         fromareasubcode,
                                         toarea,
                                         toareasubcode,
                                         commonrate,
                                         minchargerate,
                                         servicetype)
				VALUES ('".$FreightCompany."',
					'" . $FromAreaCode[0]  . "',
					'" . $FromSubcode. "',
					'" . $ToAreaCode[0]	. "',
                                        '" . $ToSubcode. "',
                                        '" . $myrow[5]	. "',
                                        '" . $myrow[4]	. "',
                                        '" . $strow[0]  . "'  ),";
                
                $cleanareadelsql=substr($sqlinsertareadel,0,-1).';';    
                $ErrMsg = _($FreightCompany . ' ' . $fromarea . ' to  ' . $toarea . ' area code record could not be added because ');
                $DbgMsg = _('The SQL that was used to add the are code record failed was');
                $resultINAD = DB_query($cleanareadelsql, $db, $ErrMsg, $DbgMsg, true, false);
                
                if (DB_error_no($db) >0) {
                DB_Txn_Rollback($db);
                } else {
                $NoInsertCommit+=DB_affected_rows($resultINAD);    
                DB_Txn_Commit($db);
                }
                
                $lastadID = DB_Last_Insert_ID($db, 'fre_areadelivery', 'id');

                // attemp to insert into fre_arearate
                $sqlinsertadrate .= " ('".$lastadID."',1,'" .$myrow[7]."'),
                                      ('".$lastadID."',2,'" .$myrow[9]."'),
                                      ('".$lastadID."',3,'" .$myrow[11]."'),
                                      ('".$lastadID."',4,'" .$myrow[13]."'),
                                      ('".$lastadID."',5,'" .$myrow[15]."'),";
       

	}
        
        $cleanadratesql=substr($sqlinsertadrate,0,-1).';';
        $ErrMsg = _($FreightCompany .  ' area rate record could not be added because ');
        $DbgMsg = _('The SQL that was used to add the are rate record failed was');     
	$resultINADR = DB_query($cleanadratesql,$db, $ErrMsg, $DbgMsg, true, false);
     
        
        if (DB_error_no($db) > 0) {
          $errormsg=DB_error_msg($db);
          DB_Txn_Rollback($db);
	  prnMsg(_('Failed import on '. _($ErrMsg).$errormsg),'error'); 
          DB_Txn_Rollback($db);
        } else {
          DB_Txn_Commit($db);
          prnMsg( '<br />'.$NoInsertCommit.' '. _('Import Successfully.'),'success');
        }
       
        echo  '<br /><a href="' . $rootpath . '/Z_ImportCSVData.php">' . _('Return Back') . '</a>';
	fclose($handle);

  } 
}

elseif (isset($_GET['gettemplate']) and $_GET['gettemplate']!='' ) { //download an import template

    if($_GET['gettemplate']=='pricelist'){
        $headers = array('StockID',         	//  0 'STOCKID',
	         'SalesType',     	//  1 'SalesType',
	         'Currency', 	        //  2 'Currency',
	         'Price'      	        //  3 'Price',
         );
	echo '<br /><br /><br />"'. implode('","',$headers). '"<br /><br /><br />';
        echo  '<br /><a href="' . $rootpath . '/Z_ImportCSVData.php">' . _('Select a file to upload') . '</a>';
    }
    elseif($_GET['gettemplate']=='postcodelist'){
      $headers = array('Postcode',         	//  0 'Postcode',
	         'Suburb',     	        //  1 'Suburb',
	         'State', 	        //  2 'State',
	         'RateArea',      	//  3 'RateArea',
                 'RateAreaSubcode',     //  4 'RateAreaSubcode',
                 'RateAreaDescription', //  5 'RateAreaDescription',
                 'RateAreaZones'        //  6 'RateAreaZones',
         );
        echo '<br /><br /><br />"'. implode('","',$headers). '"<br /><br /><br />';
        echo  '<br /><a href="' . $rootpath . '/Z_ImportCSVData.php">' . _('Select a file to upload') . '</a>';
    }
    elseif($GET['gettemplate']=='adratelist'){
      $headers = array('FromArea',         	//  0 'FromArea',
	         'From',     	        //  1 'From',
	         'ToArea', 	        //  2 'ToArea',
	         'To',      	        //  3 'To',
                 'MinCharge',           //  4 'MinCharge'  
                 'CommonRate',          //  5 'CommonRate',
                 'Range1',              // 6 'Range1',
                 'Rate1',               // 7 'Rate1',
                 'Range2',              // 8 'Range2',
                 'Rate2',               // 9 'Rate2',
                 'Range3',              // 10 'Range3',
                 'Rate3',               // 11 'Rate3',
                 'Range4',              // 12 'Range4',
                 'Rate4',               // 13 'Rate4',
                 'Range5',              // 14 'Range5',
                 'Rate5',               // 15 'Rate5',
                 'Servicetype'          // 16 'Servicetype,  
                );
        echo '<br /><br /><br />"'. implode('","',$headers). '"<br /><br /><br />';
        echo  '<br /><a href="' . $rootpath . '/Z_ImportCSVData.php">' . _('Select a file to upload') . '</a>';  
    }
} 

else { //show file upload form

    
	echo '<div>Please backup prices table, before you do any importing job</div> <br/>';
        echo 'First Please follow the link to <a href=http://support.sentric.com.au/phpbackup target=_blank>Backup Your Database</a><br/>';
   /* Import Price List */
	echo '<form ENCtype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '">';
        echo "<input type='hidden' name='MAX_FILE_SIZE' value='1000000'>";
        echo '<table>';
	echo '<tr><th colspan=2>' . _('Price List Import') . '</th></tr>';
        echo '<tr><td><a href="Z_ImportCSVData.php?gettemplate=pricelist">Get Import Price List Template</a></td></tr>';
        echo '<tr><td>'. _('Upload Price List CSV file') . ": <input name='pricelistfile' type='file'><input type='submit' name='pricelist' VALUE='" . _('Import') . "'>
              </td></tr></table>";
	echo '</form><br /><br />';
        
    /* Import Postcode List */
	echo '<form ENCtype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '">';
        echo "<input type='hidden' name='MAX_FILE_SIZE' value='1000000'>";
        echo '<table>';
	echo '<tr><th colspan=2>' . _('PostCode List Import') . '</th></tr>';
        /* Retrieve shipper info list */
        $sql = 'SELECT shipper_id, shippername FROM shippers order by shipper_id desc';
	$ShipperListResult=DB_query($sql,$db);
	echo '<tr><td>' . _('Select a Shipper') . ':';
	echo '<select name="Shipper">';
	while ($myrow=DB_fetch_array($ShipperListResult)){
	          echo '<option Value="' . $myrow['shipper_id'] . '">' . $myrow['shippername'];
	}
	echo '</select></td></tr>';
        echo '<tr><td><a href="Z_ImportCSVData.php?gettemplate=postcodelist">Get Import Postcode List Template</a></td></tr>';
        echo '<tr><td>'. _('Upload Postcode List CSV file') . ": <input name='postcodelistfile' type='file'><input type='submit' name='postcodelist' VALUE='" . _('Import') . "'>
              </td></tr></table>";
	echo '</form><br /><br />';
        
     /* Import Area Delivery And Rate List */
	echo '<form ENCtype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'" method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '">';
        echo "<input type='hidden' name='MAX_FILE_SIZE' value='1000000'>";
        echo '<table>';
	echo '<tr><th colspan=2>' . _('Area Delivery and Rate List Import') . '</th></tr>';
        /* Retrieve shipper info list */
        $sql = 'SELECT shipper_id, shippername FROM shippers order by shipper_id desc';
	$ShipperListResult=DB_query($sql,$db);
	echo '<tr><td>' . _('Select a Shipper') . ':';
	echo '<select name="ShipperAD">';
	while ($myrow=DB_fetch_array($ShipperListResult)){
	          echo '<option Value="' . $myrow['shipper_id'] . '">' . $myrow['shippername'];
	}
	echo '</select></td></tr>';
        echo '<tr><td><a href="Z_ImportCSVData.php?gettemplate=adratelist">Get Import Area Delivery and Rate List Template</a></td></tr>';
        echo '<tr><td>'. _('Upload Area Delivery and Rate List CSV file') . ": <input name='adratelistfile' type='file'><input type='submit' name='adratelist' VALUE='" . _('Import') . "'>
              </td></tr></table>";
	echo '</form>';

}


include('includes/footer.inc');
?>