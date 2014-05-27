<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

if(isset($_GET['theme']) or isset($_POST['theme'])){
  
    if(isset($_GET['theme'])){
    $_SESSION['theme']=$_GET['theme'];
    }
    else{
    $_SESSION['theme']=$_POST['theme']; 
    }
   
}
if(isset($_GET['rootpath']) or isset($_POST['rootpath'])){
  
    if(isset($_GET['rootpath'])){
    $_SESSION['rootpath']=$_GET['rootpath'];
    }
    else{
       
    $_SESSION['rootpath']=$_POST['rootpath']; 
    }
   
}

if(isset($_GET['freightcosttxt'])){
    
    $_SESSION['freightcosttxt']=$_GET['freightcosttxt'];
}

?>
