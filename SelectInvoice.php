<?php

/* $Id: SelectInvoice.php 4579 2011-08-04 AU stan $*/

$PricesSecurity = 12;
$title = 'Search Invoice';
include('includes/session.inc');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['OrderNumber'])){
	$OrderNumber=trim($_GET['OrderNumber']);
} elseif (isset($_POST['OrderNumber'])){
	$OrderNumber=trim($_POST['OrderNumber']);
}

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<a href="' . $rootpath . '/index.php">' . _('Back to Main Menu') . '</a>';


if (isset($_GET['RelaseOrderNumber']) and $_GET['RelaseOrderNumber']!='') {
    
       $SQL = DB_Txn_Begin($db);
    
     /* Update invoice details to make mod_flag to modified, and settled to 0 */
	$SQL = "UPDATE debtortrans, debtortranstaxes SET 
                       debtortrans.mod_flag=1,
                       debtortrans.settled=0
                WHERE  debtortrans.id= debtortranstaxes.debtortransid AND                                   
                       debtortrans.transno ='" . $_GET['RelaseOrderNumber'] . "' and debtortrans.type=10";  
       
	$ErrMsg =_('The invoice detail lines could not be released because');
	$RelseInvResult=DB_query($SQL,$db,$ErrMsg,true);

    /**
     * Update the Related PO status to Pending
     */       
       $StatusComments = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Pending') . ' ' . _('by ') . $_SESSION['UsersRealName'] . '<br />';
       $SQL = "UPDATE purchorders SET status='Pending',
				      stat_comment=CONCAT('" . $StatusComments . "',stat_comment),
				      allowprint=0
	       WHERE purchorders.ref_salesorder ='" . ConvertTranToInv($_GET['RelaseOrderNumber'], $db, $rootpath, 'SelectInvoice.php') . "'";

       $ErrMsg = _('The PO order status could not be updated to pending because');
       $DbgMsg = _('The following SQL to update the purchase order was used');
       $UpdateToPendingResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    
       $SQL = DB_Txn_Commit($db);
}
/*Display Invoice Number*/

if (isset($OrderNumber) AND $OrderNumber!='') {
	if (!is_numeric($OrderNumber)){
		echo '<br><b>' . _('The Invoice Number entered') . ' <U>' . _('MUST') . '</U> ' . _('be numeric') . '.</b><br>';
		unset ($OrderNumber);
	} else {
		echo '<br>'._('Invoice Number') . '  ' . $OrderNumber.'';
	}
}

                 $SQL = "SELECT debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
                                        debtortrans.order_,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num
				FROM debtortrans,
					debtorsmaster,
					custbranch
					
				WHERE  debtortrans.type=10
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";
/*Search Invoice according to partial infomation*/

if (isset($_POST['SearchParts'])) {
	
	
	if (!empty($_POST['CustCode']) AND !empty($_POST['CustName'])) {
	
		//insert wildcard characters in spaces
		$SearchName = '%' . str_replace(' ', '%', $_POST['CustName']) . '%';
                $SearchCode = '%' . str_replace(' ', '%', $_POST['CustCode']) . '%';
                $SQL = "SELECT  debtortrans.id, debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num
				FROM debtortrans,
					debtorsmaster,
					custbranch	
				WHERE  debtortrans.type=10
                                AND debtorsmaster.name " . LIKE . " '" . $SearchName ."'
				AND  debtortrans.debtorno " . LIKE . " '" . $SearchCode ."'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                 order by debtortrans.trandate desc ,debtortrans.sales_ref_num desc";
              
  
        }
        elseif(!empty($_POST['CustCode']) AND empty($_POST['CustName'])){
                $SearchCode = '%' . str_replace(' ', '%', $_POST['CustCode']) . '%';
                $SQL = "SELECT debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num
				FROM debtortrans,
					debtorsmaster,
					custbranch	
				WHERE  debtortrans.type=10
				AND  debtortrans.debtorno " . LIKE . " '" . $SearchCode ."'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                 order by debtortrans.trandate desc , debtortrans.sales_ref_num asc";
        }
        elseif(empty($_POST['CustCode']) AND !empty($_POST['CustName'])){
            $SearchName = '%' . str_replace(' ', '%', $_POST['CustName']) . '%';
            $SQL = "SELECT  debtortrans.id,  debtortrans.transno,
                                        debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
                                        debtortrans.order_,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num
				FROM debtortrans,
					debtorsmaster,
					custbranch	
				WHERE  debtortrans.type=10
                                AND debtorsmaster.name " . LIKE . " '" . $SearchName ."'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                 order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";
        }
   
}



/*EndSearch Invoice according to partial infomation*/


/*Display Invoice Search Options*/	
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'</p>';
echo '<table class=selection><tr><td>'._('Invoice Number') . ': <input type="text" name="OrderNumber" maxlength="8" size="9" />  '
    .'<input type=submit name="SearchOrders" value="' . _('Search Invoices') . '"></td></tr></table>';


echo '<br /><font size=1><div class="page_help_text">' ._('To search for Invoice for a specific part use the part selection facilities below')
		.'</div> </font>';
echo '<br /><table class="selection"><tr>';

echo '<td><font size=1>' . _('Enter a partial') . '<b>' . _(' Customer Code') . '</b>:</font></td>';
echo '<td><input type="Text" name="CustCode" size=20 maxlength=25></td></tr>';
echo '<tr><td><font size<b>' . _('OR') . '</b></font><font size=1>' .  _(' A partial') .  '<b>' .  _(' Customer Name') . '</b>:</font></td>';
echo '<td><input type="Text" name="CustName"></td></tr></table><br>';
echo '<table><tr><td><input type=submit name="SearchParts" value="' . _('Search Parts') . '">';
echo '<input type=submit name="SearchParts" value="' . _('Show All') . '"></td></tr></table>';
echo '<br />';






/*Search Invoice base on specific Invoice Number*/
	if (isset($OrderNumber) && $OrderNumber !='') {
     $SQL = "SELECT debtortrans.id, 
                    debtortrans.transno,
                                        debtortrans.trandate,
                                        debtortrans.order_,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
                                        debtortrans.mod_flag,
					debtorsmaster.name,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num
				FROM debtortrans,
					debtorsmaster,
					custbranch	
				WHERE  debtortrans.type=10
                                AND debtortrans.order_='" . $OrderNumber . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                order by debtortrans.trandate desc , debtortrans.sales_ref_num desc";

       
	} else {
// search via status
   } 
   
   //Pagination for Select Invoice List
   	if (isset($_POST['Next'])) {
			$Offset = $_POST['nextlist'];
		}
		if (isset($_POST['Prev'])) {
			$Offset = $_POST['previous'];
		}
		if (!isset($Offset) or $Offset<0) {
			$Offset=0;
		}
         $SQL .=  " LIMIT " . $_SESSION['DefaultDisplayRecordsMax'] . " OFFSET " . number_format($_SESSION['DefaultDisplayRecordsMax']*$Offset);

	$ErrMsg = _('No orders were returned by the SQL because');
	
	$InvoicesResult = DB_query($SQL,$db,$ErrMsg);
    
	
        
   /* End Search Invoice base on specific Invoice Number*/

   /*Show a table of the Invoices returned by the SQL */

	echo '<table cellpadding=2  width=97% class=selection>';
        
        echo '<tr><td colspan=6><input type="hidden" name="previous" value='.number_format($Offset-1).'><input  type="submit" name="Prev" value="'._('Prev').'"></td>';
        
        if (DB_num_rows($InvoicesResult)>=$_SESSION['DisplayRecordsMax']){
        echo '<td><div align=right><input type="hidden" name="nextlist" value='.number_format($Offset+1).'><input  type="submit" name="Next" value="'._('Next').'"></div></td></tr>';
            }
	echo '<tr><th>' . _('Invoice #') .
			'</th><th>' . _('Invoice Date') .
			'</th><th>' . _('Customer') .
			'</th><th>' . _('Branch') .
			'</th>';
	if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
		echo '<th>' . _('Balance') .'</th>';
	}
	echo '<th>' . _('Edit') . '</th>
	      <th>' . _('Status') . '</th>

				</tr>';
	$j = 1;
	$k=0; 
        
while ($myrow=DB_fetch_array($InvoicesResult)) {
    
    $SQLPOExist="select * from purchorders where ref_salesorder='".$myrow['order_']."'";
    
    $ErrMsg = _('No Po Exist were returned by the SQL because');

    $POExistList = DB_query($SQLPOExist,$db,$ErrMsg);

    $SQLPOCount="select * from purchorders where ref_salesorder='".$myrow['order_']."' and
                                          status<>'Completed' and status<>'Cancelled'";
       
    $ErrMsg = _('No Po count were returned by the SQL because');

    $FinishPOList = DB_query($SQLPOCount,$db,$ErrMsg);
    
    
 /*alternate bgcolour of row for highlighting */           
     if ($k==1){ 
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
                
    $ModifyPage = $rootpath . '/Invoice_Modification.php?' . SID . '&ModifyOrderNumber=' . $myrow['transno'].'&ismodified='.$myrow['mod_flag'];
  
	$sqlAP = "SELECT debtortrans.alloc 
				FROM debtortrans
				WHERE debtortrans.type=10
				AND debtortrans.transno='" . $myrow['transno'] ."'";


      $resultAP=DB_query($sqlAP,$db);
        if(DB_num_rows($resultAP)==1){
       	$myrowAP = DB_fetch_array($resultAP);
        $tempAP=$myrowAP['alloc'];
       }
       
      if ($myrow['mod_flag']== 0) {
			$status = _('UnModified');
                        $temptotal=$myrow['ovfreight']+$myrow['ovgst']+$myrow['ovamount'];
	                $DisplayBalance=number_format($temptotal-$tempAP,2);
	} 
     else if($myrow['mod_flag']== 1){
         $status = _('Modified');
           $temptotal=$myrow['ovfreight']+$myrow['ovgst']+$myrow['ovamount'];
	   $DisplayBalance=number_format($temptotal-$tempAP,2);
     }
	else {
			$status = _('Cancelled');
                        $released='<a href="SelectInvoice.php?RelaseOrderNumber=' . $myrow['transno'].'">' . _('Release') . '</a>';
                        $DisplayBalance=number_format(0,2);
	}
  /*  
   * Turn GL OFF    
        $SQL="Select count(*) from gltrans where typeno='" . $myrow['transno'] . "' and posted=1";
        $ifPosted = DB_query($SQL,$db,$ErrMsg);
        $myrowIfPost=DB_fetch_array($ifPosted);
        if($myrowIfPost[0]>0){
          $status = _('Posted');
          $DisplayBalance=number_format(0,2);
        }
    */    
   /*Show a table of the Invoices without modified */

          
	
       
	$FormatedOrderDate = ConvertSQLDate($myrow['trandate']);
	$FormatedOrderValue = number_format($myrow['ordervalue'],2);

		echo '<td>' . $myrow['sales_ref_num'] . '</td>
					<td>' . $FormatedOrderDate . '</td>
					<td>' . $myrow['name'] . '</td>
					<td>' . $myrow['brname'] . '</td>';
		if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
			echo '<td class=number>'.$DisplayBalance . '</td>';
		}
                
        if(DB_num_rows($POExistList)!=0 and DB_num_rows($FinishPOList)==0 and $status != 'Cancelled'){
            $status='Completed';
        }       
                
	if( $status != 'Cancelled' and $status!='Completed'){	
                echo '<td><a href="'.$ModifyPage.'">' . _('Edit') . '</a></td>';        
            }
        else{
                echo '<td>' . _('N/A') . '</td>';        
            }
                 if($status != 'Cancelled'){                                   
		echo	'<td>' . $status  . '</td></tr>';
                 }
                 else{
                echo	'<td>' . $status  . '  '.$released.'</td></tr>';     
                 }
                unset($tempAP); 
   
   /*End Show a table of the Invoices without modified */  

 }	

echo '</table>';
echo '<script>defaultControl(document.forms[0].StockCode);</script>';

echo '</form>';
include('includes/footer.inc');
?>