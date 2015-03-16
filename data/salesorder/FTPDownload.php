<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$conn_id=ftp_connect($ftp_server);
  /*
   * 1. Establish connection with FTP server
   */
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

if ((!$conn_id) || (!$login_result)) {
       echo "FTP connection has failed!";
       echo "Attempted to connect to $ftp_server for user $ftp_user_name...\n";
       exit;
   } else {
       echo "Connected to $ftp_server, for user $ftp_user_name"."...\n";
   }
 // Switched to passive mode after logging in 
   ftp_pasv($conn_id, true);
   
   /*
    * 2. Grab all files from remote directory
    */
   $listfiles = ftp_nlist($conn_id, '.');

   foreach($listfiles as $file) {
   if (ftp_get($conn_id, $file, $file, FTP_BINARY)) {
       
   rename($file,$ftp_download_erp_dir.'/'.$file);
   
   copy($ftp_download_erp_dir.'/'.$file,$ftp_download_backup_dir.'/'.$file);
       
   echo "Successfully written to $file\n";
   
 /*
  * 3. Delete Files on FTP server
  */
   if (ftp_delete($conn_id, $file)) {
    echo "$file deleted successful\n";
   } else {
   echo "could not delete $file\n";
   }
  } else {
   echo "There was a problem\n";
   }
  }
  
?>
