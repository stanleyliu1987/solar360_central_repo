<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WarehouseIntegrationModel{
    
    private $db;
    private $POList;
    
function __construct($db, $POList) {
        $this->db=$db;
        $this->POList=$POList;
    }
    /* Save Email Audit Log Details -- by Stan 22052014 */
function FTPPOList($remote, $FileName, $ftp_conn){
$export_data='';
if(isset($this->POList) and  $this->POList!= ''){
$POListArray = explode(",",  $this->POList); 
$sql = "SELECT * FROM ph_kingswarehouse where PO in (".trim($this->POList,",").")" ;
$ErrMsg =  _('An error occurred selecting all po headers');
$DbgMsg =  _('The SQL that was used to select po headers and failed in the process was');
$result = DB_query($sql,$this->db,$ErrMsg,$DbgMsg);
while ($item = DB_fetch_array($result)) {
    $export_data.=$item['PH'].str_repeat("^", 3).$item['Customer #'].str_repeat("^", 1).$item['Order Number'].str_repeat("^", 7).
                  $item["Ship Name"].str_repeat("^", 1).$item["Ship Address 1"].str_repeat("^", 2).$item["Ship City"].str_repeat("^", 1).
                  $item["Ship State"].str_repeat("^", 1).$item["Ship Post Code"].str_repeat("^", 1).$item["Ship Country"].str_repeat("^", 2).$item["Ship Phone #"].
                  str_repeat("^", 8).date("Ymd", strtotime($item["Ship Date Required"])).str_repeat("^", 7).date("Ymd").str_repeat("^", 6)."SLR".str_repeat("^", 1).$item["Special Instructions 1"].str_repeat("^", 38).
                  $item["Business Telephone Number"].str_repeat("^", 1).$item["Email Address"].str_repeat("^", 30)."\r\n";
    
    $sql_pd = "SELECT * FROM pd_kingswarehouse where `ORDER Number`='".$item['Order Number']."'";
    $ErrMsg =  _('An error occurred selecting all po details');
    $DbgMsg =  _('The SQL that was used to select po details and failed in the process was'); 
    $result_PD = DB_query($sql_pd,$this->db,$ErrMsg,$DbgMsg);
    while ($item_PD = DB_fetch_array($result_PD)) { 
    $export_data.=$item_PD['PD'].str_repeat("^", 1)."R".str_repeat("^", 1)."ROW".str_repeat("^", 2).$item_PD['Order Number'].str_repeat("^", 7).$item_PD['Product Code'].str_repeat("^", 2).$this->stripcomma($item_PD['Description']).str_repeat("^", 4).
                  $item_PD['Quantity Ordered'].str_repeat("^", 1).$item_PD['Quantity Pick'].str_repeat("^", 21).str_repeat("^", 44)."\r\n";   
    }
}
/* Write the file to a folder */
   $fp = fopen($FileName, 'w');
   fwrite($fp, $export_data);
   fclose($fp);
/* FTP upload */   
   $login = ftp_login($ftp_conn, "slr", "S0lar36@");
   ftp_pasv($ftp_conn, true);
   return ftp_put($ftp_conn, $remote, $FileName, FTP_ASCII);
    }
}
    
    /*Strip off the comma */
function stripcomma($str) { //because we're using comma as a delimiter
    $str = trim($str);
    $str = str_replace('"', '""', $str);
    $str = str_replace("\r", "", $str);
    $str = str_replace("\n", '\n', $str);
    $str = str_replace(",", ' ', $str);
    if($str == "" )
        return $str;
    else
        return $str;
}
     
    
}