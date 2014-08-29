<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

   include('includes/session.inc');

   if(isset($_POST['InvoiceNumber']) and isset($_POST['CustEmail'])){
       $InvoiceNumber=substr(trim($_POST['InvoiceNumber']),1,strpos(trim($_POST['InvoiceNumber']),'-')-1);
       $CustEmail=strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($_POST['CustEmail'])));
       $SearchEmailString = '%' . str_replace(' ', '%', str_replace("  "," ",$CustEmail)) . '%';

       $result=userLogin($SearchEmailString,$InvoiceNumber,$db);

       if($result==0){
       echo 'No matched record retrieved, Please try to input correct invoice number and email address';
       }
       else{ 
      /*
       * Retrieve Transaction Date, Invoice Balancem and Delivery Status
       */ 
       $sqlTD = "SELECT id,
                        trandate,
                        debtorno,
                        ((ovamount+ovgst+ovfreight)-alloc) as balancedue,
                        stage
                 FROM debtortransview  WHERE order_='".$InvoiceNumber."'";
       $resultTD = DB_query($sqlTD,$db);
       $myrowOS = DB_fetch_array($resultTD);
      
       /*
        * Retrieve Payment Date
        */
       $sqlPDate ="SELECT MIN(custallocns.datealloc) as paymentdate
				FROM custallocns
				WHERE custallocns.transid_allocto='" . $myrowOS['id'] ."'";
        
        $resultPD=DB_query($sqlPDate,$db);
        if(DB_num_rows($resultPD)==1){
       	$myrowPD = DB_fetch_array($resultPD);
        $PaymentDate=$myrowPD['paymentdate'];
       }
       
       /*
        * Retrieve Total Balance
        */
       $sqlTB = "SELECT SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount- debtortrans.alloc) AS balance
                FROM    debtorsmaster,
     			paymentterms,
     			holdreasons,
     			currencies,
     			debtortrans
                WHERE  debtorsmaster.paymentterms = paymentterms.termsindicator
     		AND debtorsmaster.currcode = currencies.currabrev
     		AND debtorsmaster.holdreason = holdreasons.reasoncode
     		AND debtorsmaster.debtorno = '" . $myrowOS['debtorno'] . "'
     		AND debtorsmaster.debtorno = debtortrans.debtorno
		GROUP BY debtorsmaster.name,
			currencies.currency,
			paymentterms.terms,
			paymentterms.daysbeforedue,
			paymentterms.dayinfollowingmonth,
			debtorsmaster.creditlimit,
			holdreasons.dissallowinvoices,
			holdreasons.reasondescription";
      $ErrMsg = _('The customer details could not be retrieved by the SQL because');
      $CustomerResult = DB_query($sqlTB,$db,$ErrMsg);
      $CustomerRecord = DB_fetch_array($CustomerResult);
      
      if($myrowOS['balancedue']<0.01 and $myrowOS['balancedue']>(-0.01)){
          $myrowOS['balancedue']=0;
      }
      if($CustomerRecord['balance']<0.01 and $CustomerRecord['balance'] >(-0.01)){
          $CustomerRecord['balance']=0;
      }
      
      
      /* 1. Check if credit hold stage exists in a customer stage history 29082014*/
      $sqlcheckCredit="Select count(*) as total from order_stages_messages where debtortran_fk='".$myrowOS['id']."' and order_stage_change=6";
      $resultCheckCredit = DB_query($sqlcheckCredit,$db);
      $myrowCheckCredit = DB_fetch_array($resultCheckCredit);
      $imagealign="right";
      /* 2. Show the stage image banner according to updated stage and credit hold or not 29082014*/
      if($myrowCheckCredit['total']>0){
      if($myrowOS['stage']=='Credit Hold' or $myrowOS['stage']=='Open'){
          $statusImage='<img src="image/stage/Order_stage_credithold.jpg" width="600" height="120">';
      }
      elseif($myrowOS['stage']=='Release Order'){
          $statusImage='<img src="image/stage/Order_stage_credit_release.jpg" width="600" height="120">';
      }
      elseif($myrowOS['stage']=='Dispatch Stock'){
          $statusImage='<img src="image/stage/Order_stage_credit_dispatch.jpg" width="600" height="120">';
      }
      elseif($myrowOS['stage']=='Delivered'){
          $statusImage='<img src="image/stage/Order_stage_credit_delivered.jpg" width="600" height="120">';
      }
      else{
          $statusImage= 'Invoice Status: '.$myrowOS['stage'];
          $imagealign="left";
      }
      }
      else{
      if($myrowOS['stage']=='Open'){
          $statusImage='<img src="image/stage/Order_stage_open.jpg" width="600" height="120">';
      }
      elseif($myrowOS['stage']=='Release Order'){
          $statusImage='<img src="image/stage/Order_stage_release.jpg" width="600" height="120">';
      }
      elseif($myrowOS['stage']=='Dispatch Stock'){
          $statusImage='<img src="image/stage/Order_stage_dispatch_stock.jpg" width="600" height="120">';
      }
      elseif($myrowOS['stage']=='Delivered'){
          $statusImage='<img src="image/stage/Order_stage_credit_delivered.jpg" width="600" height="120">';
      }
      else{
          $statusImage= 'Invoice Status: '.$myrowOS['stage'];
          $imagealign="left";
      }
      }
      

       echo '<table class="tabletop">
      
	     <tr><td>' . _('Invoice Number: ') . ' ' . strtoupper($_POST['InvoiceNumber'])  . '</td></tr>
             <tr><td>' . _('Date: ') . ' ' . ConvertSQLDate($myrowOS['trandate'])  . '</td></tr>
             <tr><td>' ._('Invoice Payment Date: ').' '.ConvertSQLDate($PaymentDate).'</td></tr>      
             <tr><td>' ._('Invoice Balance Outstanding: ').' $'.number_format($myrowOS['balancedue'],2).'</td></tr>
             <tr><td align='.$imagealign.'>' .$statusImage.'</img></td></tr>
             <tr><td></td><td class="accountbalance" width=20%>' ._('Your Account Balance<sup>1</sup> : ').' $'.number_format($CustomerRecord['balance'],2).'</td></tr>     </table>';
       
       echo '<table class="table1">
             <tr><th>' . _('Part Number') . '</th>
	     <th width="40%">' . _('Description') . '</th>
	     <th>' . _('Quantity') . '</th>
	     <th>' . _('Con Note Number') . '</th>
             <th>' . _('Freight Company Type') . '</th>    
	     <th>' . _('Estimated / Delivery Date') . '</th>
             <th>' . _('Comments') . '</th>    </tr>';
       
       $k=0;	//row colour counter
        while ($myrow=DB_fetch_array($result)){
            
            if ($k==1){
			$RowStarter = '<tr class="EvenTableRows">';
			$k=0;
		} else {
			$RowStarter = '<tr class="OddTableRows">';
			 $k=1;
		}
                
            if($myrow['status']!='BackOrder'){
               $myrow['status']=''; 
            } 
            
            if($myrow['itemcode']=='S360-0000'){
               $myrow['consignment_id']='';
               $myrow['delivery_options']='';
               $myrow['del_est_date']='00/00/0000';
               $myrow['status']='';
            }

	     echo $RowStarter;
             /*05052014 by Stan Retrieve shipper details */
             $sqlShipper="Select * from shippers where shipper_id='".$myrow['delivery_options']."'";
             $ErrMsg = _('The shipper details could not be retrieved by the SQL because');
             $ShipperResult = DB_query($sqlShipper,$db,$ErrMsg);
             $ShipperRecord = DB_fetch_array($ShipperResult);
             $FormatShipname=explode(' ', $ShipperRecord['shippername'], 2);
             $freightCompany='<a href="'.$ShipperRecord['shipperwebsite'].'" target="_blank">'.$FormatShipname[1].'</a>';
            /* End of Customization */
             printf ('<td>%s</td>
		      <td>%s</td>
		      <td class=number>%s</td>
		      <td class=number>%s</td>
                      <td align=center>%s</td>
                      <td class=number>%s</td>
		      <td class=number>%s</td></tr>',
		      $myrow['itemcode'],
		      $myrow['itemdescription'],
		      $myrow['quantityord'],
		      $myrow['consignment_id'],
                      $freightCompany,
		      ConvertSQLDate($myrow['del_est_date']),
                      $myrow['status']);
            
        }
        
        echo '</table><p><br>';
        
      /** Check Amount Paid through Receipt**/
     $sqlShipAddress = "SELECT 	deliverto,
				deladd1,
				deladd2,
				deladd3,
				deladd4
				FROM salesorders
				WHERE orderno='".$InvoiceNumber."'";
		

       $resultShipAddress=DB_query($sqlShipAddress,$db);

       if(DB_num_rows($resultShipAddress)==1){
       	$myrowSA = DB_fetch_array($resultShipAddress);
       }
        echo '<table class="table2"><tr><td>' . _('Additional Information') . '</b></td></tr>';
	echo '<tr><td><b>' . _('Shipping Address: ') . '</td></tr>
	      <tr><td>' . $myrowSA['deladd1']. '
              ' . $myrowSA['deladd2']. ' ' . $myrowSA['deladd3']. ' ' . $myrowSA['deladd4']. '</td></tr></table><br>';
        
        echo '<table class="selection"><tr><td>         
              Payment of this invoice is considered as acceptance of our Terms and Conditions. Refer to our website for full list of our terms. 
              Delivery typically takes 3-5 working days from payment date. This could be as little as 2 days but it is dependent on your delivery 
              address and the physical location of the stock. Noting that all stock items are not housed in all locations. We say 3-5 working days
              as our experience has shown that it covers the delays relating to freight companies, potential delays with inbound air freight, etc. </td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>Note 1: A positive balance indicates the total funds you owe to Solar360 and a negative balance indicates the total funds Solar360 owes you.</td></tr>
            <tr><td>Please contact our customer services team on 1300 600 360 if you have additional queries.</td>
            <td width=17%><a href="http://solar360.com.au/index.php?action=login"><img src="image/OrderMores.jpg" width="119" height="29"></img></a></td></tr></table>'; 
   
 
       }
   }

      
?>
