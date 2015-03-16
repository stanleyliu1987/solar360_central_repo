<?php

include_once('config.php');

/**
 * 1. Download new order csv file into local directory
 */
include_once('FTPDownload.php');

/**
 * 2. Upload new order to ERP
 */


//Establish connection with MySQL
$connection = mysql_connect("localhost", $dbuser, $dbpassword);
if (!$connection) {
  die("Not connected : " . mysql_error());
}
mysql_select_db($database, $connection) or die( "Unable to select database");

// delete and rename file logic
$files = glob( $ftp_download_erp_dir.'/*.*' );
foreach($files as $file){
   if(str_replace($ftp_download_erp_dir.'/', '', $file) !== 'latestsalesorder.csv'){
       unlink($ftp_download_erp_dir.'/latestsalesorder.csv');
       rename($file,$ftp_download_erp_dir.'/latestsalesorder.csv');
       echo '1. New file update successfully... <p>';
       

$file_handle = fopen($ftp_download_erp_dir."/latestsalesorder.csv", "r");
$ordercsvfile=array();


// Read header of CSV file
$header_line = fgetcsv($file_handle, 1024);  
while (($orderdata = fgetcsv($file_handle, 1024, ",")) !== FALSE) {
$ordercsvfile[] = $orderdata; //add the row to the main array.
}
// Put unique order number into array
/* 21012015 Only non-blank file will flow into the ERP and send email to the end user */
if(count($ordercsvfile)>0){
for ($c=0; $c < count($ordercsvfile); $c++) {
    if(substr($ordercsvfile[$c][5],2,1) =='/'){ 
    $datepart = split('[/ :]',$ordercsvfile[$c][5]);
    $newdate=$datepart[1].'/'.$datepart[0].'/'.$datepart[2].' '.$datepart[3].':'.$datepart[4];
    }
    else{ 
    $newdate=$ordercsvfile[$c][5];   
    }
mysql_query("INSERT INTO import_csv_salesorders (Number,
                                                 deliverycharge,
                                                 tax,
                                                 discount,
                                                 total,
                                                 datepurchased,
                                                 comments,
                                                 STATUS,
                                                 deliveryname,
                                                 deliveryphone,
                                                 deliverycompany,
                                                 deliveryaddress,
                                                 deliverysuburb,
                                                 deliverypostcode,
                                                 deliverystate,
                                                 deliverycountry,
                                                 deliverymethod,
                                                 billingname,
                                                 billingemail,
                                                 billingphone,
                                                 billingcompany,
                                                 billingaddress,
                                                 billingsuburb,
                                                 billingpostcode,
                                                 billingstate,
                                                 billingcountry,
                                                 paymentmethod,
                                                 productname,
                                                 CODE,
                                                 quantity,
                                                 price,
                                                 options,
                                                 optioncode,
                                                 yourreference)
             VALUES ('".$ordercsvfile[$c][0]."', 
                     '".$ordercsvfile[$c][1]."',
                     '".$ordercsvfile[$c][2]."',
                     '".$ordercsvfile[$c][3]."',
                     '".$ordercsvfile[$c][4]."',
                     '".date('Y-m-d H:i:s', strtotime($newdate))."',
                     '".string_off_quote($ordercsvfile[$c][6])."',
                     '".$ordercsvfile[$c][7]."',
                     '".string_off_quote($ordercsvfile[$c][8])."',
                     '".$ordercsvfile[$c][9]."',
                     '".string_off_quote($ordercsvfile[$c][10])."',
                     '".string_off_quote($ordercsvfile[$c][11])."',
                     '".string_off_quote($ordercsvfile[$c][12])."',
                     '".$ordercsvfile[$c][13]."',
                     '".$ordercsvfile[$c][14]."',
                     '".$ordercsvfile[$c][15]."',
                     '".$ordercsvfile[$c][16]."',
                     '".string_off_quote($ordercsvfile[$c][17])."',
                     '".$ordercsvfile[$c][18]."',
                     '".$ordercsvfile[$c][19]."',
                     '".string_off_quote($ordercsvfile[$c][20])."',
                     '".string_off_quote($ordercsvfile[$c][21])."',
                     '".string_off_quote($ordercsvfile[$c][22])."',
                     '".$ordercsvfile[$c][23]."',
                     '".$ordercsvfile[$c][24]."',
                     '".$ordercsvfile[$c][25]."',
                     '".string_off_quote($ordercsvfile[$c][26])."',
                     '".string_off_quote($ordercsvfile[$c][27])."',
                     '".$ordercsvfile[$c][28]."',
                     '".$ordercsvfile[$c][29]."',
                     '".$ordercsvfile[$c][30]."',
                     '".$ordercsvfile[$c][31]."',
                     '".$ordercsvfile[$c][32]."',
                     '".string_off_quote($ordercsvfile[$c][33])."')") or die(mysql_error());
}
echo '2. Sales Order Integration Successfully... <p>';

fclose($file_handle);

/**
 * 3. Send new order Email to Solar360
 */

include('SendMail.php');
echo '3. Email Sending Successfully... <p>';
       }
    }
 }

mysql_close();

/**
 * 4. Double check files in latest directory
 */
$files = glob( $ftp_download_erp_dir.'/*.*' );
array_multisort(
    array_map( 'filemtime', $files ),
    SORT_DESC,
    SORT_NUMERIC,
    $files
);
// delete and rename file logic
foreach($files as $file){
   if(str_replace($ftp_download_erp_dir.'/', '', $file) !== 'latestsalesorder.csv'){
       unlink($file);
       echo '4.'.$file.' has been deleted';
   }
 
}


function string_sanitize($string) {
    return preg_replace("/[^a-zA-Z0-9 ]+/i", "", html_entity_decode($string, ENT_QUOTES));
  
}

function string_off_quote($string){
    return str_replace(array('\'', '"'), '', $string);
}
?>




