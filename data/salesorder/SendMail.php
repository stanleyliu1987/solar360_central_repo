<?php

include('htmlMimeMail.php');
include('readCSVFile.php');
$orderDateOutput='';
$orderMsgOutput='';

//Extract Mail content from ERP table
//Edit Mail Subject
$orderDateList=array_unique($orderDateList);
foreach($orderDateList as $orderdate){
    $orderDateOutput.=$orderdate.' ';
}

//Edit Mail Message
$orderMsgOutput="<table border=1 width=750>
        <tr><th>Customer Name</th>
        <th>Order Number</th>
        <th>Product Name</th>
        <th>Product Code</th>
        <th>Product Quantity</th>
        <th>Product Price</th>
        <th>Date Purchased</th></tr>";

foreach($orderProductInfo as $k=>$orderproduct){
    $orderMsgOutput.= 
    "<tr><td>".$orderProductInfo[$k]['customername']."</td>
    <td align=right>".$orderProductInfo[$k]['Number']."</td>
    <td>".$orderProductInfo[$k]['productname']."</td>
    <td align=right width=16%>".$orderProductInfo[$k]['productcode']."</td>
    <td align=right>".$orderProductInfo[$k]['productquantity']."</td>
    <td align=right>".$orderProductInfo[$k]['productprice']."</td>
    <td align=right width=22%>".$orderProductInfo[$k]['datepurchased']."</td></tr>";
   
    $k++;
}
$orderMsgOutput.="</table><br>";
$orderMsgOutput.="<div>Click the link below to View Orders: <br><br>
                 <a href=http://erp.solar360.com.au/PlaceSalesOrderWeb.php>View Order</a></div><br><br>";

//$orderMsgOutput.="<div><img src=http://erp2.solar360.com.au/companies/solar360/logo.jpg width=80 height=80></div>";

//Send New order to admin by email
$mailParam= array();
//$mailParam['sendto'] = "stanley.liu@sentric.com.au";
$mailParam['sendto'] = "admin@solar360.com.au";
$mailParam['sendsubject'] = "New Orders have been processed on ".$orderDateOutput;
$mailParam['sendmsg'] = "<div style=width:85% align=center><h2>Recent Sales Order</h2></div> <br>".$orderMsgOutput;
$mailParam['sendfrom'] = "erp@solar360.com.au";

//$mailParam['sendheaders'] ='MIME-Version: 1.0' . "\r\n";
//$mailParam['sendheaders'] .= 'Content-type: text/html;charset=iso-8859-1' . "\r\n";
$mailParam['sendheaders'] = "From:" . $mailParam['sendfrom']."\r\n";
$mailParam['sendheaders'] .= 'Cc: info@sentric.com.au, stanley.liu@sentric.com.au' . "\r\n";


//mail($mailParam['sendto'], $mailParam['sendsubject'], $mailParam['sendmsg'],$mailParam['sendheaders']); 

//require_once('function_mime_mailer.php'); 
mime_mailer($mailParam['sendto'], $mailParam['sendsubject'], $mailParam['sendmsg'],$mailParam['sendheaders']); 

//$mail->send($mailParam);

?>