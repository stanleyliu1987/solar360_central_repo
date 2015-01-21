<?php
$file_handle = fopen($ftp_download_erp_dir."/latestsalesorder.csv", "r");
$orderNoList=array();
$ordercsvfile=array();
$orderDateList=array();
$orderProductInfo=array();
$last_order_no='';
$i=0;
$j=0;

// Read header of CSV file
$header_line = fgetcsv($file_handle, 1024);  
while (($orderdata = fgetcsv($file_handle, 1024, ",")) !== FALSE) {
$ordercsvfile[] = $orderdata; //add the row to the main array.
}
// Put unique order number into array
for ($c=0; $c < count($ordercsvfile); $c++) {
if($ordercsvfile[$c][0]!=$last_order_no){
  $orderNoList [$i]= $ordercsvfile[$c][0];
  $last_order_no=$ordercsvfile[$c][0];
  $i++;
 }
}

//Select Order from import_csv_salesorder table based on order number

foreach ($orderNoList as $orderNo) {
    
$SelectOrderInfo="SELECT billingcompany,
                         billingname,
                         datepurchased,
                         Number,
                         productname,
                         code,
                         quantity,
                         price,
                         optioncode
                         FROM import_csv_salesorders 
                                       where Number='" . $orderNo ."'";
$OrderresultList=mysql_query($SelectOrderInfo);
while($Orderresult = mysql_fetch_array($OrderresultList)) {
	// Print out the contents of each row into a table
        $orderProductInfo[$j]['customername']=$Orderresult['billingcompany']==null ? $Orderresult['billingname'] : $Orderresult['billingcompany'];
        $orderProductInfo[$j]['Number']=$Orderresult['Number'];
        $orderProductInfo[$j]['productname']=$Orderresult['productname'];
        $orderProductInfo[$j]['productcode']=$Orderresult['optioncode']==null ? $Orderresult['code'] : $Orderresult['optioncode'];
        $orderProductInfo[$j]['productquantity']=$Orderresult['quantity'];
        $orderProductInfo[$j]['productprice']=$Orderresult['price'];
        $orderProductInfo[$j]['datepurchased']=$Orderresult['datepurchased'];
      
	$orderDateList[$j]= $Orderresult['datepurchased'];
        $j++;
} 
}

fclose($file_handle);
?>




