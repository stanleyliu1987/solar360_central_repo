<?php

/* $Id: OrderManagement.php 4579 2011-08-04 AU stan $*/

$PricesSecurity = 12;
$title = 'Search Invoice';
include('includes/session.inc');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$CustomerSearch=new CustomerTransSearchModel($db);
$SearchArray='';
$RowIndex = 0;


if(!isset($_POST['PageOffset']) || $_POST['PageOffset'] == 0){
    $_POST['PageOffset']=1;
} 
    
if ($_SESSION['InvoicePortraitFormat']==1){ //Invoice/credits in portrait
		$PrintCustomerTransactionScript = 'PrintCustTransPortrait.php';
} else { //produce pdfs in landscape
		$PrintCustomerTransactionScript = 'PrintCustTrans.php';
}
echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="UserID" id="UserID" value="' . $_SESSION['UserID'] . '" />';
echo '<a href="' . $rootpath . '/index.php">' . _('Back to Main Menu') . '</a>';


if (isset($_GET['RelaseOrderNumber']) and $_GET['RelaseOrderNumber']!='') {
    
       $SQL = DB_Txn_Begin($db);
    
     /* Update invoice details to make mod_flag to modified, and settled to 0 */
	$SQL = "UPDATE debtortrans, debtortranstaxes SET 
                       debtortrans.mod_flag=1,
                       debtortrans.order_stages=1,
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
	       WHERE purchorders.ref_salesorder ='" . ConvertTranToInv($_GET['RelaseOrderNumber'], $db, $rootpath, 'Ordermanagement.php') . "'";

       $ErrMsg = _('The PO order status could not be updated to pending because');
       $DbgMsg = _('The following SQL to update the purchase order was used');
       $UpdateToPendingResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
    
       $SQL = DB_Txn_Commit($db);
}

/*Search Invoice according to partial infomation*/
if (isset($_POST['Search']) or isset($_POST['SearchOrders']) or isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
        if (strlen($_POST['OrderNumber'])>0) {
		$msg = _('Search Result: Order Number has been used in search') . '<br />';
	}elseif (strlen($_POST['Keywords'])>0) {
		$msg = _('Search Result: Customer Name has been used in search') . '<br />';
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
	}elseif (strlen($_POST['CustCode'])>0) {
		$msg = _('Search Result: Customer Code has been used in search') . '<br />';
	}elseif (strlen($_POST['CustPhone'])>0) {
		$msg = _('Search Result: Customer Phone has been used in search') . '<br />';
	}elseif (strlen($_POST['CustAdd'])>0) {
		$msg = _('Search Result: Customer Address has been used in search') . '<br />';
	}elseif (strlen($_POST['CustBranchContact'])>0) {
		$msg = _('Search Result: Customer Branch Contact has been used in search') . '<br />';
	} elseif (isset($_POST['CustType']) AND $_POST['CustType']!='ALL') {
		$msg = _('Search Result: Customer Type has been used in search') . '<br />';
	} elseif (isset($_POST['Area']) AND $_POST['Area']!='ALL') {
		$msg = _('Search Result: Customer branch area has been used in search') . '<br />';
	} elseif (isset($_POST['OrderStages']) AND $_POST['OrderStages']!='ALL') {
		$msg = _('Search Result: Customer order stage has been used in search') . '<br />';
	}
        $SearchArray = array(
         "OrderNumber"=>$_POST['OrderNumber'],
         "CustName"=>$_POST['Keywords'],
         "CustCode"=>$_POST['CustCode'],
         "CustPhone"=>$_POST['CustPhone'],   
         "CustAdd"=>$_POST['CustAdd'],   
         "CustBranchContact"=>$_POST['CustBranchContact'],
         "CustType"=>$_POST['CustType'],
         "Area"=>$_POST['Area'],
         "OrderStage"=>$_POST['OrderStages']   
            );
 } 
       $InvoicesResult=$CustomerSearch->SearchCustomerTransResult($SearchArray); 
/*EndSearch Invoice according to partial infomation*/

if (strlen($msg)>1){
	prnMsg($msg,'info');
}
/*Display Invoice Search Options*/	
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'</p>';
echo '<table class=selection><tr><td>'._('Invoice Number') . ': <input type="text" name="OrderNumber" maxlength="8" size="9" value="' . $_POST['OrderNumber'] . '" />  '
    .'<input type=submit name="SearchOrders" value="' . _('Search Invoices') . '"></td></tr></table>';

/* Search By Customer Details */
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Customers').'</p>';
echo '<table cellpadding="3" colspan="4" class="selection">';
echo '<tr><td colspan="2">' . _('Enter a partial Name') . ':</td><td>';
echo '<input type="Text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25">';
echo '</td><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Code') . ':</td><td>';
echo '<input type="Text" name="CustCode" value="' . $_POST['CustCode'] . '" size="15" maxlength="18">';
echo '</td></tr><tr><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Phone Number') . ':</td><td>';
echo '<input type="Text" name="CustPhone" value="' . $_POST['CustPhone'] . '" size="15" maxlength="18"></td>';
echo '<td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter part of the Address') . ':</td><td>';
echo '<input type="Text" name="CustAdd" value="' . $_POST['CustAdd'] . '" size=20 maxlength=25></td></tr>';
echo '<tr><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Enter a partial Branch Contact') . ':</td><td>';
echo '<input type="Text" name="CustBranchContact" value="' . $_POST['CustBranchContact'] . '" size="20" maxlength="25"></td>';

/* Search By Customer Types */
echo '<td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Choose a Type') . ':</td><td>';
if (isset($_POST['CustType'])) {
	// Show Customer Type drop down list
	$result2 = DB_query('SELECT typeid, typename FROM debtortype', $db);
	// Error if no customer types setup
	if (DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="CustomerTypes.php?" target="_parent">Setup Types</a>';
		echo '<tr><td colspan=2>' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
	} else {
		// If OK show select box with option selected
		echo '<select name="CustType">';
		echo '<option value="ALL">' . _('Any') . '</option>';
		while ($myrow = DB_fetch_array($result2)) {
			if ($_POST['CustType'] == $myrow['typename']) {
				echo '<option selected value="' . $myrow['typename'] . '">' . $myrow['typename']  . '</option>';
			} else {
				echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename']  . '</option>';
			}
		} //end while loop
		DB_data_seek($result2, 0);
		echo '</select></td>';
	}
} else {
	// No option selected yet, so show Customer Type drop down list
	$result2 = DB_query('SELECT typeid, typename FROM debtortype', $db);
	// Error if no customer types setup
	if (DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="CustomerTypes.php?" target="_parent">Setup Types</a>';
		echo '<tr><td colspan=2>' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
	} else {
		// if OK show select box with available options to choose
		echo '<select name="CustType">';
		echo '<option value="ALL">' . _('Any'). '</option>';
		while ($myrow = DB_fetch_array($result2)) {
			echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
		} //end while loop
		DB_data_seek($result2, 0);
		echo '</select></td></tr>';
	}
}

/* Option to select a order stage */
$OrderStagesSQL= "SELECT * FROM order_stages";
$orderstageslist = DB_query($OrderStagesSQL,$db);
/* End of logic */      
echo '<tr><td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Choose a Order Stage') . ':</td><td>';
$result2 = DB_query("SELECT * FROM order_stages order by stages_id", $db);
// Error if no sales areas setup
if (DB_num_rows($result2) != 0) {
	// if OK show select box with available options to choose
	echo '<select name="OrderStages">';
	echo '<option value="ALL">' . _('Any') . '</option>';
	while ($myrow = DB_fetch_array($result2)) {
		if (isset($_POST['OrderStages']) and $_POST['OrderStages']==$myrow['stages_id']) {
			echo '<option selected value="' . $myrow['stages_id'] . '">' . $myrow['stages'] . '</option>';
		} else {
			echo '<option value="' . $myrow['stages_id'] . '">' . $myrow['stages'] . '</option>';
		}
	} //end while loop
	DB_data_seek($result2, 0);
	echo '</select></td>';
}
/* Option to select a sales area */
echo '<td><font size=3><b>' . _('OR') . '</b></font></td><td>' . _('Choose an Area') . ':</td><td>';
$result2 = DB_query('SELECT areacode, areadescription FROM areas', $db);
// Error if no sales areas setup
if (DB_num_rows($result2) == 0) {
	$DataError = 1;
	echo '<a href="Areas.php?" target="_parent">' . _('Setup Types') . '</a>';
	echo '<tr><td colspan=2>' . prnMsg(_('No Sales Areas defined'), 'error') . '</td></tr>';
} else {
	// if OK show select box with available options to choose
	echo '<select name="Area">';
	echo '<option value="ALL">' . _('Any') . '</option>';
	while ($myrow = DB_fetch_array($result2)) {
		if (isset($_POST['Area']) and $_POST['Area']==$myrow['areacode']) {
			echo '<option selected value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		}
	} //end while loop
	DB_data_seek($result2, 0);
	echo '</select></td></tr>';      
}
echo '</table><br />';
 echo '<div align=center><input type=submit name="Search" value="' . _('Search Now') . '"></div>';
   
//Pagination for Select Invoice List
    if (isset($InvoicesResult)) {
	$ListCount = DB_num_rows($InvoicesResult);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		if ($ListPageMax > 1) {
			echo '<p><div class=centre>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
				} else {
					echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type=submit name="Go" value="' . _('Go') . '">
				<input type=submit name="Previous" value="' . _('Previous') . '">
				<input type=submit name="Next" value="' . _('Next') . '">';
			echo '</div>';
		}	
        
   /* End Search Invoice base on specific Invoice Number*/

   /*Show a table of the Invoices returned by the SQL */
	echo '<table cellpadding=2  width=97% class=selection>';
	$TableHeader= '<tr><th>' . _('Invoice #') .
			'</th><th>' . _('Invoice Date') .'</th>'.
			'<th>' . _('Customer') .'</th>';
	if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
        $TableHeader.=  '<th>' . _('Invoice Value') .'</th>';    
	$TableHeader.=  '<th>' . _('Balance') .'</th>';
	}
	$TableHeader.=  '<th>' . _('Order Stages') . '</th>
	                 <th>' . _('Invoice Status') . '</th></tr>';
        echo $TableHeader;
	$j = 1;
	$k=0; 
        DB_data_seek($InvoicesResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
while ($myrow=DB_fetch_array($InvoicesResult) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

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
      $DisplayBalance=number_format($myrow['ovfreight']+$myrow['ovgst']+$myrow['ovamount']-$tempAP,2);
      $DisplayInvoiceValue=number_format($myrow['ovfreight']+$myrow['ovgst']+$myrow['ovamount'],2);
      if(DB_num_rows($POExistList)!=0 and DB_num_rows($FinishPOList)==0 and $myrow['invoice_status'] != 'Undo'){
          $Invoice_status='Completed'; 
        }
      else{
          if ($myrow['invoice_status']== 'Undo') { 
          $Invoice_status='<a href="OrderManagement.php?RelaseOrderNumber=' . $myrow['transno'].'">' . _('Undo') . '</a>';
          $DisplayBalance=number_format(0,2);
	} 
          else{
          $Invoice_status='<a href="'.$ModifyPage.'">' . _('Edit') . '</a>';   
         }
      }  
/* 05062014 by Stan Order Status dropdown list */
$OrderStagesSQL= "SELECT * FROM order_stages";
$orderstageslist = DB_query($OrderStagesSQL,$db);
/* End of logic */      
$OrderStagesDropdown= '<select id="OrderStagesList_'.$myrow['id'].'" name="OrderStagesList_'.$myrow['id'].'" onchange="ChangeOrderStages(\''.$myrow['id'].'\');">';
while ($os = DB_fetch_array($orderstageslist)) {
if($os["stages_id"]==$myrow['order_stages'])    
$OrderStagesDropdown.= '<option value='.$os["stages_id"].' selected>'.$os["stages"].'</option>';
else
$OrderStagesDropdown.= '<option value='.$os["stages_id"].'>'.$os["stages"].'</option>';    
}
$OrderStagesDropdown.= '</select>';     
   
   /* 16062014 Check Order Stage Change history by Stan */
$reportparam= json_encode(array('transid'=>$myrow['id'],'invoiceno'=>$myrow['sales_ref_num'], 'orderno'=>$myrow['order_']));
$HistoryButton='<button id="OrderStageHistory_'.$myrow['id'].'" name="OrderStageHistory_'.$myrow['id'].'" value=\'' . $reportparam . '\' disabled >History</button>';
   /* End of Logic */
   /*Show a table of the Invoices without modified */
        $FormatedOrderDate = ConvertSQLDate($myrow['trandate']);
	$FormatedOrderValue = number_format($myrow['ordervalue'],2);
        /* 12062014 Replace Invoice no with pdf download link by Stan*/
        $RowInvoicePDFLink ='<a href="'.$rootpath.'/'.$PrintCustomerTransactionScript.'?FromTransNo='.$myrow['transno'].'&InvOrCredit=Invoice&PrintPDF=True"><img src="'.$rootpath.'/css/' . $theme . '/images/pdf.png" title="' . _('Click for PDF') . '">'  . $myrow['sales_ref_num'] .  '</a>';     
		echo '<td>' . $RowInvoicePDFLink . '</td>
					<td>' . $FormatedOrderDate . '</td>
					<td>' . $myrow['name'] . '</td>';
		if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
                        echo '<td class=number>'.$DisplayInvoiceValue . '</td>';
			echo '<td class=number>'.$DisplayBalance . '</td>';
		}
                echo  '<td><span>'.$OrderStagesDropdown.'</span><span> '.$HistoryButton.'</span></td>
                       <td>'.$Invoice_status.'</td>';

        unset($tempAP);
        $j++;
	if ($j == 11 AND ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $TableHeader;
	}
   $RowIndex++;
   /*End Show a table of the Invoices without modified */  
}
 }	

echo '</table>';
echo '<script>defaultControl(document.forms[0].StockCode);</script>';

echo '</form>';
include('includes/footer.inc');
?>