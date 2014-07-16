

<?php

/* $Id: SelectInvoice.php 4579 2011-08-04 AU stan $*/

$PricesSecurity = 12;
$title = 'Consignment Note Report';

include('includes/SQL_CommonFunctions.inc');
include('includes/session.inc');
include('includes/header.inc');
$file="TrackConsignmentNote.php";

if ($_SESSION['InvoicePortraitFormat']==1){ //Invoice/credits in portrait
		$PrintCustomerTransactionScript = 'PrintCustTransPortrait.php';
} else { //produce pdfs in landscape
		$PrintCustomerTransactionScript = 'PrintCustTrans.php';
}
        
/* Retrieve Order Number */
if (isset($_GET['OrderNumber']) and $_GET['OrderNumber']!=''){
       
	$OrderNumber=ConvertInvToTran(trim($_GET['OrderNumber']),$db,$rootpath,$file);
        $InvoiceNumber=trim($_GET['OrderNumber']);
} 
elseif (isset($_POST['OrderNumber']) and $_POST['OrderNumber']!=''){
         
	$OrderNumber=ConvertInvToTran(trim($_POST['OrderNumber']),$db,$rootpath,$file);
        $InvoiceNumber=trim($_POST['OrderNumber']);
}

/* Retrieve Customer Code */
if (isset($_GET['CustCode']) and $_GET['CustCode']!=''){
	$CustomerCode=$_GET['CustCode'];      
} 
elseif (isset($_POST['CustCode']) and $_POST['CustCode']!=''){
	$CustomerCode=$_POST['CustCode'];    
}

/* Retrieve Customer Name */
if (isset($_GET['CustName']) and $_GET['CustName']!=''){
	$CustomerName=$_GET['CustName'];
} 
elseif (isset($_POST['CustName']) and $_POST['CustName']!=''){
        $CustomerName=$_POST['CustName'];
}

/* Retrieve Customer Branch */
if (isset($_GET['CustBranch']) and $_GET['CustBranch']!=''){ 
	$CustomerBranch=$_GET['CustBranch'];
} 
elseif (isset($_POST['CustBranch']) and $_POST['CustBranch']!=''){ 
        $CustomerBranch=$_POST['CustBranch'];

}


/* Sort function menu */
$sort=0;

if(isset($_GET['sortreport']) and isset($_GET['sorttag']) ){ 
   
        $sort=$_GET['sorttag'];
   
        if($_GET['sortreport']== 'InvoiceNo'){
            if($sort ==1){
               $SortFilter =' order by debtortrans.order_  asc';
               $sort=0;
               }
            else{
               $SortFilter =' order by debtortrans.order_  desc'; 
               $sort=1;
            }

        }
        elseif($_GET['sortreport'] == 'PaymentDate'){
            if($sort ==1){
               $SortFilter =' order by debtortrans.paymentdate  asc';
               $sort=0;
               }
            else{
               $SortFilter =' order by debtortrans.paymentdate  desc'; 
               $sort=1;
            }
        }
        else{
            if($sort ==1){
               $SortFilter =' order by debtortrans.maxdays  asc';
               $sort=0;
               }
            else{
               $SortFilter =' order by debtortrans.maxdays  desc'; 
               $sort=1;
            }

        }
       $optionMenue='<option  value="emptychoose" selected>Choose a sort field</option>
                     <option  value="InvoiceNo">Invoice No</option>
                     <option  value="PaymentDate">Payment Date</option>
                     <option  value="MaxDays">Max Days</option>';
       $_SESSION['sortFilter']=$SortFilter;
}
else{   
    if(isset($_SESSION['sortFilter'])){
        $SortFilter=$_SESSION['sortFilter'];
    }
    else{
        $SortFilter='order by debtortrans.order_ desc';
    }
        $optionMenue='<option  value="emptychoose" selected>Choose a sort field</option>
                      <option  value="InvoiceNo">Invoice No</option>
                      <option  value="PaymentDate">Payment Date</option>
                      <option  value="MaxDays">Max Days</option>';
}

if(isset($_GET['InvDelStatus']) and $_GET['InvDelStatus']=='yes'){ 
    $sqlupdateInvoiceDelStatus="UPDATE debtortrans
					SET order_stages='".$_GET['Invdelstatus']."' 
					WHERE id = '".$_GET['InvoiceId']."'";
    $ErrMsg =_('The invoice delivery status could not be updated');
    $resultInvDelStatusUpdate=DB_query($sqlupdateInvoiceDelStatus,$db, $ErrMsg); 
}

echo '<form name="form1" action="' . $_SERVER['PHP_SELF'] . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="UserID" id="UserID" value="' . $_SESSION['UserID'] . '" />';
echo '<a href="' . $rootpath . '/index.php">' . _('Back to Main Menu') . '</a>';

/*Display Invoice Number*/

if (isset($_POST['SearchAll'])){
    $OrderNumber='';
    $InvoiceNumber='';
    unset($CustomerCode);
    unset($CustomerName);
}

if (isset($OrderNumber) AND $OrderNumber!='') {
	if (!is_numeric($OrderNumber)){
		echo '<br><b>' . _('The Invoice Number entered') . ' <U>' . _('MUST') . '</U> ' . _('be numeric') . '.</b><br>';
		unset ($OrderNumber);
	} else {
		echo '<br>'._('Invoice Number') . '  ' . $InvoiceNumber.'';
	}
}
if(isset($_GET['ordertype'])){ 
    $_POST['OrderType']=$_GET['ordertype'];
}
if(isset($_GET['filtertype'])){ 
    $_POST['FilterType']=$_GET['filtertype'];
}

		//no criteria set then default to all customers
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
                                        debtorsmaster.invaddrbranch,
                                        debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
                                        custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					custbranch.phoneno,
                                        custbranch.email,
                                        custbranch.contactname,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        debtortrans.paymentdate,
                                        debtortrans.maxdays,
                                        order_stages.stages,
                                        order_stages.stages_id
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        order_stages
				WHERE  debtortrans.type=10
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.order_stages <5
                                AND order_stages.stages_id=debtortrans.order_stages
                                ".$SortFilter;
	
		if(strlen($_POST['OrderType']) > 0 AND $_POST['OrderType']!='ALL')  {
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
                                        debtorsmaster.invaddrbranch,
                                        debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
                                        custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					custbranch.phoneno,
                                        custbranch.email,
                                        custbranch.contactname,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        debtortrans.paymentdate,
                                        debtortrans.maxdays,
                                        order_stages.stages,
                                        order_stages.stages_id
				FROM    debtortrans,
					debtorsmaster,
					custbranch,
                                        order_stages
				WHERE  debtortrans.type=10
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.order_stages <5
                                AND order_stages.stages_id=debtortrans.order_stages
                                AND debtortrans.order_stages='".$_POST['OrderType']."'
                                ".$SortFilter;
                }
                elseif(strlen($_POST['FilterType']) > 0 AND $_POST['FilterType']!='NoFilter'){
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
                                        debtorsmaster.invaddrbranch,
                                        debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
                                        custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					custbranch.phoneno,
                                        custbranch.email,
                                        custbranch.contactname,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        debtortrans.paymentdate,
                                        debtortrans.maxdays,
                                        order_stages.stages,
                                        order_stages.stages_id
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        order_stages,
                                        purchorders
				WHERE debtortrans.order_=purchorders.ref_salesorder
                                AND purchorders.status <> 'Cancelled'
                                AND purchorders.consignment_id=''
                                AND purchorders.delivery_status=''
                                AND debtortrans.type=10
                                AND debtortrans.settled=1
                                AND debtortrans.alloc <> 0
                                AND debtortrans.paymentdate <> '0000-00-00'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.order_stages <5
                                AND order_stages.stages_id=debtortrans.order_stages
                                group by debtortrans.order_
                                ".$SortFilter;
                }
                elseif(strlen($CustomerCode) > 0){
                $SearchCode = '%' . str_replace(' ', '%', $CustomerCode) . '%';
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
                                        debtorsmaster.invaddrbranch,
                                        debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
                                        custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					custbranch.phoneno,
                                        custbranch.email,
                                        custbranch.contactname,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        debtortrans.paymentdate,
                                        debtortrans.maxdays,
                                        order_stages.stages,
                                        order_stages.stages_id
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        order_stages
				WHERE  debtortrans.type=10
				AND  debtortrans.debtorno " . LIKE . " '" . $SearchCode ."'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.order_stages <5
                                AND order_stages.stages_id=debtortrans.order_stages
                               ".$SortFilter;
                }
                elseif(strlen($CustomerName) > 0){
                $SearchName = '%' . str_replace(' ', '%', $CustomerName) . '%';
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
                                        debtorsmaster.invaddrbranch,
                                        debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
                                        custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					custbranch.phoneno,
                                        custbranch.email,
                                        custbranch.contactname,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        debtortrans.paymentdate,
                                        debtortrans.maxdays,
                                        order_stages.stages,
                                        order_stages.stages_id
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND debtorsmaster.name " . LIKE . " '" . $SearchName ."'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.order_stages <5
                                AND order_stages.stages_id=debtortrans.order_stages
                                ".$SortFilter;
                }
               elseif(strlen($CustomerBranch) > 0){ 
               $SearchBranchName = '%' . str_replace(' ', '%', $CustomerBranch) . '%';
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
                                        debtorsmaster.invaddrbranch,
                                        debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
                                        custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					custbranch.phoneno,
                                        custbranch.email,
                                        custbranch.contactname,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        debtortrans.paymentdate,
                                        debtortrans.maxdays,
                                        order_stages.stages,
                                        order_stages.stages_id
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND custbranch.brname " . LIKE . " '" . $SearchBranchName ."'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.order_stages <5
                                AND order_stages.stages_id=debtortrans.order_stages   
                                ".$SortFilter;
             
                }



/*EndSearch Invoice according to partial infomation*/


/*Display Invoice Search Options*/	
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'</p>';
echo '<table class=selection><tr><td>'._('Invoice Number') . ': <input type="text" id="OrderNumber" name="OrderNumber" maxlength="8" size="9" value="'.$InvoiceNumber.'"/>  '
    .'<input type=submit name="SearchOrders" value="' . _('Search Invoices') . '"></td></tr></table>';


echo '<br /><font size=1><div class="page_help_text">' ._('To search for Invoice for a specific part use the part selection facilities below')
		.'</div> </font>';
echo '<br /><table class="selection"><tr>';

echo '<td><font size=1>' . _('Enter a partial') . '<b>' . _(' Customer Code') . '</b>:</font></td>';
echo '<td><input type="Text" id="CustCode" name="CustCode" size=20 maxlength=25 value="'.$CustomerCode.'" /></td>';
echo '<td><font size=3><b>' . _('OR') . '</b></font><font size=1>' .  _(' A partial') .  '<b>' .  _(' Customer Name') . '</b>:</font></td>';
echo '<td><input type="Text" id="CustName" name="CustName" value="'.$CustomerName.'" /></td></tr>';

echo '<tr><td><font size=3><b>' . _('OR') . '</b></font><font size=1>' . _(' A partial') . '<b>' . _(' Customer Branch Name') . '</b>:</font></td>';
echo '<td><input type="Text" id="CustBranch" name="CustBranch" size=20 maxlength=25 value="'.$CustomerBranch.'" /></td>';   
echo '<td><font size=3><b>' . _('OR') . '</b></font><font size=1>' . _(' Choose a ') . '<b>' . _(' Filter Function') . '</b>:</font></td>';
echo '<td><select name="FilterType" id="FilterType" onchange="ChangeOrderTypeDefault(\''._('OrderType').'\');">';
echo '<option value="NoFilter">' . _('Choose a Filter') . '</option>';
if ($_POST['FilterType'] == "NoPOEmptyTrackInfo") {
echo '<option value="NoPOEmptyTrackInfo" selected>' . _('A. Non-Cancelled PO Without Tracking Info') . '</option>';
}
else{
echo '<option value="NoPOEmptyTrackInfo">' . _('A. Non-Cancelled PO Without Tracking Info') . '</option>';    
}
echo '</select></td></tr>';

echo '<tr><td><font size=3><b>' . _('OR') . '</b></font><font size=1>' . _(' Choose a ') . '<b>' . _(' Order Stage') . '</b>:</font></td>';
$resultStatus = DB_query('SELECT stages_id, stages FROM order_stages', $db);
echo '<td><select name="OrderType" id="OrderType" onchange="ChangeOrderTypeDefault(\''._('FilterType').'\');">';
echo '<option  value="ALL">' . _('Any') . '</option>';
		while ($myrow = DB_fetch_array($resultStatus)) {
			if ($_POST['OrderType'] == $myrow['stages_id']) {
				echo '<option  selected value="' . $myrow['stages_id'] . '">' . $myrow['stages']  . '</option>';
			} else {
				echo '<option value="' . $myrow['stages_id'] . '">' . $myrow['stages']  . '</option>';
			}
		} //end while loop
		
echo '</select></td></tr>';

echo '<table><tr><td><input type=submit name="SearchNow" value="' . _('Search Now') . '">';
echo '<input type=submit name="updatereport" value="' . _('Update Reports') . '">';
echo '<input type=button name="evareport" value="' . _('Evaluation Report') . '" onclick="popupevareportwindow();"></td></tr></table>';
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
                                        debtorsmaster.invaddrbranch,
                                        debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
                                        custbranch.brpostaddr1,
					custbranch.brpostaddr2,
					custbranch.brpostaddr3,
					custbranch.brpostaddr4,
					custbranch.brpostaddr5,
					custbranch.brpostaddr6,
					custbranch.phoneno,
                                        custbranch.email,
                                        custbranch.contactname,
					custbranch.brname,
					debtortrans.debtorno,
					debtortrans.branchcode,
                                        debtortrans.sales_ref_num,
                                        debtortrans.paymentdate,
                                        debtortrans.maxdays,
                                        order_stages.stages,
                                        order_stages.stages_id
				FROM debtortrans,
					debtorsmaster,
					custbranch,
                                        order_stages
				WHERE  debtortrans.type=10
                                AND debtortrans.transno='" . $OrderNumber . "'
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
                                AND debtortrans.order_stages <5
                                AND order_stages.stages_id=debtortrans.order_stages
                                ".$SortFilter;

       
	} 
        
/* Save Consignment Details into Purchase Order Table as Cron Job */
   	if (isset($_POST['updatereport'])) {
           require_once('eservice/eServices.php');					// Import the Star Track Express PHP API - do not modify this file
           require_once('eservice/CustomerConnect.php');				// Import ConnectDetails class - customer to modify this class as required
           include_once('eservice/config.php');
           include('eservice/UpdateConsignmentNoteReport.php');
           exit(0);
	} 
 



/*Pagination for Select Invoice List*/
   	if (isset($_POST['Next'])) {
			$Offset = $_POST['nextlist'];
		}
	if (isset($_POST['Prev'])) {
			$Offset = $_POST['previous'];
		}
        if(isset($_GET['Offset'])){
            $Offset=$_GET['Offset'];
        }
	if (!isset($Offset) or $Offset<0) {
			$Offset=0;
	}
        $SQL .=  " LIMIT " . $_SESSION['DefaultDisplayRecordsMax'] . " OFFSET " . number_format($_SESSION['DefaultDisplayRecordsMax']*$Offset);

	$ErrMsg = _('No orders were returned by the SQL because');
	
	$InvoicesResult = DB_query($SQL,$db,$ErrMsg);
    
	
        
   /* End Search Invoice base on specific Invoice Number*/
   
   /* Search the Largest PO number of each Invoice */     
    $SQLPOMAXCount="SELECT MAX(POCount) AS maxPO FROM ( SELECT  COUNT(*)  POCount FROM purchorders  WHERE ref_salesorder<> 0 GROUP BY ref_salesorder ) AS pomax";   

    $ErrMsg = _('No Po max count was returned by the SQL because');

    $MAXPOList = DB_query($SQLPOMAXCount,$db,$ErrMsg);
    if(DB_num_rows($MAXPOList)>0){
    $rowMaxPO = DB_fetch_array($MAXPOList);
    }
    $MaxNumPo=$rowMaxPO['maxPO'];
   /* End Search the Largest PO number of each Invoice*/ 
    
    
   /*Show a table of the Invoices returned by the SQL */

	echo '<table cellpadding=2  width=97% class=selection >';
        echo '<tr><td>Sort Report By: <select id="sortreport" onChange="sortConsignmentReport(\''.$Offset.'\', '.$sort.');">'.$optionMenue.'</select></td></tr>';
        $PreviousButtonColspan=$MaxNumPo+3;
       if($Offset !=0){
        echo '<tr><td colspan='.$PreviousButtonColspan.'><input type="hidden" name="previous" value='.number_format($Offset-1).'><input  type="submit" name="Prev" value="'._('Prev').'"></td>';
       }
       else{
        echo '<tr><td colspan='.$PreviousButtonColspan.'></td>';  
       }
        if (DB_num_rows($InvoicesResult)>=$_SESSION['DisplayRecordsMax']){
        echo '<td><div align=right><input type="hidden" name="nextlist" value='.number_format($Offset+1).'><input  type="submit" name="Next" value="'._('Next').'"></div></td></tr>';
            }
            
   /*Show all PO header for report*/        
        $h=1;
        while($h<=$MaxNumPo){
         $POheader.='</th><th>' . _('PO').$h;
         $h++;
        }
	echo '<tr><th>' . _('Invoice #') .
			'</th><th>' . _('Payment Date').
			'</th><th>' . _('Max Days') . 
                        '</th><th>' . _('Header Name') . $POheader.
			'</th></tr>';
   /*End Show all PO header for report*/  
        
        $i=1;
        $j=1;
	$k=0; 
        $FormatedOrderDate = 'Not Paid';
        $paymentDatevalue=0;
        
while ($myrow=DB_fetch_array($InvoicesResult)) {
     
    $HeaderLine=0;
    $HeaderLineBR='';
    /* Obtain the exact Payment Date from custallocns table */          

    $sqlPDate ="SELECT MIN(custallocns.datealloc) as paymentdate
				FROM custallocns
				WHERE custallocns.transid_allocto='" . $myrow['id'] ."'";
        
        $resultPD=DB_query($sqlPDate,$db);
        if(DB_num_rows($resultPD)==1){
       	$myrowPD = DB_fetch_array($resultPD);
        $PaymentDate=$myrowPD['paymentdate'];
       }
   /* update paymentdate */
       if(strtotime($PaymentDate) != strtotime($myrow['paymentdate']) ){ 
           $sqlupdatepaymentdate="UPDATE debtortrans
					SET paymentdate='".$PaymentDate."' 
					WHERE id = '".$myrow['id']."'";
           $ErrMsg =_('The payment date could not be updated');
           $resultPDUpdate=DB_query($sqlupdatepaymentdate,$db, $ErrMsg);
       }
       else{
            if($PaymentDate != null){
        $FormatedOrderDate = ConvertSQLDate($PaymentDate); 
        $paymentDatevalue=$FormatedOrderDate;
            }
          
       }
     
      
 /*alternate bgcolour of row for highlighting */           
     if ($k==1){ 
			echo '<tr class="EvenTableRows" valign=top>';
			$k=0;
		} else {
			echo '<tr class="OddTableRows" valign=top>';
			$k++;
		}
  
   /*Show a table of the Invoices without modified */
    $SQLCOUNTPOItems="select COUNT(purchorderdetails.orderno) as count
                              from purchorders inner join purchorderdetails on purchorders.orderno=purchorderdetails.orderno
                              where purchorders.ref_salesorder='".$myrow['order_']."'
                              group by purchorders.orderno";
    
    $ErrMsg = _('No Po Items Exist were returned by the SQL because');
    $COUNTPOList = DB_query($SQLCOUNTPOItems,$db,$ErrMsg); 
    $MaxPOItemCount=0;
    while ($POCount=DB_fetch_array($COUNTPOList)){
        if($POCount['count']>$MaxPOItemCount){
         $MaxPOItemCount=$POCount['count'];
        }
    }

    $SQLInvoicePOList="select suppliers.suppname,
                              purchorders.orderno,
                              purchorders.comments,
                              purchorders.ref_number,
                              purchorders.consignment_id,
                              purchorders.delivery_status,
                              purchorders.del_est_tag,
                              purchorders.del_est_date,
                              purchorders.days,
                              purchorders.status,
                              purchorders.delivery_options
                              from purchorders, suppliers 
                              where suppliers.supplierid=purchorders.supplierno and
                                    ref_salesorder='".$myrow['order_']."'";
    
    $ErrMsg = _('No Po Exist were returned by the SQL because');

    $InvoicePOList = DB_query($SQLInvoicePOList,$db,$ErrMsg);   
    $POListRow= '';  
    $NotNullNumberPO=0;
     while ($POInfo=DB_fetch_array($InvoicePOList)){
         
       $SupplierName= substr($POInfo['suppname'],0,15);
       $PONumber=$POInfo['ref_number'];
  
       //$PONumberButton='<input type="button" value='.$PONumber.' onclick="popupConsignmentNotewindow(\'' . $POInfo['consignment_id'] . '\')" >';
       //$SearchConsignmentNoteButton='<input type="button" value="Search By ID" onclick="getDelStatusDate('.$i.$j.');"/>';
       $POStatus=$POInfo['status'];
       if($POInfo['consignment_id']!=''){
           $NotNullNumberPO++;
       }
       $ConsignmentNote='<input id="conID_'.$i.$j.'" type=text name="consignmentId" size=10 value='.$POInfo['consignment_id'].'>';
       
      /* update delivery service type */
       if(isset($_GET['delservice']) and ($i.$j == $_GET['position'])){ 
     
           $sqlupdatedeloption="UPDATE purchorders
					SET delivery_options='".$_GET['delservice']."' 
					WHERE ref_number='".$POInfo['ref_number']."'";
           $resultDelOptionUpdate=DB_query($sqlupdatedeloption,$db);
       
 
       }
       
/* Populate Shippers name into delivery service menu */
  
 $sqlShipperAddress='SELECT shipper_id, shippername FROM shippers where shippername not like "%Obsolete%" order by shippername asc';
 
 $ErrMsg = _('No shipper was returned by the SQL because');
	
 $ShipperNameResult = DB_query($sqlShipperAddress,$db,$ErrMsg);
 
 $optionService='';
 
 if($POInfo['delivery_options'] !=0){ 
   $optionService='<option value="'.$POInfo['delivery_options'].'" selected>'.ShipperIdToName($POInfo['delivery_options'],$db,$rootpath,$file).'</option>';  
 }
 if(isset($_GET['delservice'])  and ($i.$j == $_GET['position'])){
   $optionService='<option value='.$_GET['delservice'].' selected>'.ShipperIdToName($_GET['delservice'],$db,$rootpath,$file).'</option>';  
 }
 
 while ($shiprow=DB_fetch_array($ShipperNameResult)) {

   if(isset($_GET['delservice'])) {
     if($i.$j == $_GET['position']){ 
         if($_GET['delservice'] != $shiprow['shipper_id']){ 
          $optionService.='<option value="'.$shiprow['shipper_id'].'">'.$shiprow['shippername'].'</option>';
          }
       } 
     else{
       if($POInfo['delivery_options'] != $shiprow['shipper_id']){
          $optionService.='<option value="'.$shiprow['shipper_id'].'">'.$shiprow['shippername'].'</option>';
        }
      }
   }
   else{
     if($POInfo['delivery_options'] != $shiprow['shipper_id']){ 
          $optionService.='<option value="'.$shiprow['shipper_id'].'">'.$shiprow['shippername'].'</option>';
      }
   }  
 }
 
 
       $ConsignmentNote.='<select id="chooseservice_'.$i.$j.'">'.$optionService.'</select>';
/* End Populate Shippers name into delivery service menu */
 
 
/* Set Up Delivery Status and Date for each PO */ 

      //From purchorder data
       $DelStatus='<input type=text name=delserivce value="'.$POInfo['delivery_status'].'" id="delStatus_'.$i.$j.'"/>';      
       // Re-Format the Dleivery Date
       if($POInfo['del_est_date']=="0000-00-00"){
       $Del_Est_Day=date('d/m/Y');    
       } 
       else{
       list($year, $month, $day) = split('-', $POInfo['del_est_date']);
       $Del_Est_Day=$day.'/'.$month.'/'.$year;
       }
       //From purchorder data
       $DelDate=' <input type=text name="delDate_'.$i.$j.'" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" id="delDate_'.$i.$j.'" value="'.$Del_Est_Day.'" />';          


/* Star Track, Toll, Others delivery Day */  
       if($POInfo['days']==0 and $POInfo['consignment_id']==''){
       $DelDays='<div id="difDays_'.$i.$j.'"></div>';   
       } 
       else{
       $DelDays='<div id="difDays_'.$i.$j.'">Days: '.$POInfo['days'].'</div>';
       }
    
       if($POInfo['days']>$MaxDelDays){
       $MaxDelDays=$POInfo['days'];
       }
       
/*
 * Retrieve PO items quantity * description
 */  
       $SQLPOItem = " SELECT  quantityord, itemdescription  from  purchorderdetails where orderno='".$POInfo['orderno']."'";
       
       $resultPOItems=DB_query($SQLPOItem,$db,'','',false,false);   
       $PoItemLine='';
       $Line=0;
       while ($poitem=DB_fetch_array($resultPOItems)){
           $PoItemLine.=$poitem['quantityord'].'*'.substr($poitem['itemdescription'],0,18).'<br>';
           $Line++;
       }
       while($Line<$MaxPOItemCount){
           $PoItemLine.='<br>';
           $Line++;
       }

       /* PO comment */
       $POComment='<textarea name=pocomment id="poComment_'.$i.$j.'">'.$POInfo['comments'].'</textarea>';
   
       $POList.='<td>'.$SupplierName.'<br>
                     '.$PONumber.'<br>  
                     '.$PoItemLine.'<br>    
                     '.$POStatus.'<br>  
                     '.$ConsignmentNote.'<br>
                     '.$DelStatus.'<br>
                     '.$DelDate.'
                     '.$DelDays.'<br>
                     '.$POComment.'<br>    
                                   <input type=hidden id=paymentdate_'.$i.$j.' value='.$paymentDatevalue.'>
                                   <input type=hidden id=porefnumber_'.$i.$j.' value='.$PONumber.'>
                                        
                     </td>';
       $POListRow.=$i.$j .',';
       $j++;  
      }
    
   /*Select Whole Invoice Delivery Status */

    $InvDelStatusSQL="select stages_id,stages from order_stages where stages_id<5";
    
    $ErrMsg = _('No Invoice Delivery Status Exist were returned by the SQL because');

    $InvDelStatusList = DB_query($InvDelStatusSQL,$db,$ErrMsg);   
          
     while ($InvDelStatusInfo=DB_fetch_array($InvDelStatusList)){
         
         if($InvDelStatusInfo['stages_id'] == $myrow['stages_id']){
      $InvDelStatusoptions.='<option value="'.$InvDelStatusInfo['stages_id'].'" selected>'.$InvDelStatusInfo['stages'].'</option>';
         }
         else{
      $InvDelStatusoptions.='<option value="'.$InvDelStatusInfo['stages_id'].'">'.$InvDelStatusInfo['stages'].'</option>';        
         }
     }   
    /*
     * Add Customer Address Before Invoice Link
     */
      $customername=$myrow['name'].'/'.$myrow['contactname'];
      $phonenumber=$myrow['phoneno'];
      
    if($myrow['invaddrbranch']==0){
       
        $address1=$myrow['address1'];
        $address2=$myrow['address2'].' '.$myrow['address3'].' '.$myrow['address4'].' '.$myrow['address5'].' '.$myrow['address6'];
     
    }
    else{
     
        $address1=$myrow['brpostaddr1'];
        $address2=$myrow['brpostaddr2'].' '.$myrow['brpostaddr3'].' '.$myrow['brpostaddr4'].' '.$myrow['brpostaddr5'].' '.$myrow['brpostaddr6'];
   
    }
    
    if($myrow['sales_ref_num']==''){
     $RowInvoicePDFLink='Empty Invoice #';   
     $RowSearchConsignmentButton=  '<input type="button"  value="Update Status"   onclick="getDelStatusDate(\''.substr($POListRow,0,-1).'\',\''.$i.'\',\''.$myrow['id'].'\');"/>';
    }
     else{
     $RowInvoicePDFLink ='<a href="'.$rootpath.'/'.$PrintCustomerTransactionScript.'?FromTransNo='.$myrow['transno'].'&InvOrCredit=Invoice&PrintPDF=True"><img src="'.$rootpath.'/css/' . $theme . '/images/pdf.png" title="' . _('Click for PDF') . '">'  . $myrow['sales_ref_num'] .  '</a>'; 
     $RowSearchConsignmentButton=  '<input type="button" value="Update Status" onclick="getDelStatusDate(\''.substr($POListRow,0,-1).'\',\''.$Offset.'\',\''.$i.'\',\''.$myrow['id'].'\');"/>'; 
     $RowDeliveryStatus='<select id="InvDelStatus_'.$i.'">'.$InvDelStatusoptions.'</select>';
     }
     $RowEmailClientSD= '<a href="'.$rootpath.'/EmailClientSD.php?CustEmail='.$myrow['email'].'&InvoiceNumber='.$myrow['sales_ref_num'].'&debtorno='.$myrow['debtorno'].'&branchcode='.$myrow['branchcode'].'" target="_blank">' . _('Email') . ' <img src="'.$rootpath.'/css/'.$theme.'/images/email.gif" title="' . _('Click to email the Client Stock Delivery Details') . '"></a>';
     $UpdateMessage='<p id="msgsuccess_'.$i.'"></p>';
   /* Add suppliement add to each row for making alternating line to the end*/  
     while($j<=$MaxNumPo){
           $POList.='<td></td>';
           $j++;
       }
     while($HeaderLine<$MaxPOItemCount){
           $HeaderLineBR.='<br> ';
           $HeaderLine++;
       }  
  
     $HeaderColumn=' Supplier Name : <br>
                     PO Number : <br>
                     Stock Items : <br>'.$HeaderLineBR.'
                     PO Status : <br> 
                     Con. Note ID :  <br> <br>
                     Delivery Status : <br> <br>
                     Date : <br> <br> <br> 
                     Comment: ';
                     
     /* update maxdays */
       if($MaxDelDays != $myrow['maxdays'] and isset($MaxDelDays)){
           $sqlupdatemaxdays="UPDATE debtortrans
					SET maxdays='".$MaxDelDays."' 
					WHERE id = '".$myrow['id']."'";
           $resultMDUpdate=DB_query($sqlupdatemaxdays,$db);
       }
      
   if($FormatedOrderDate == 'Not Paid' or $NotNullNumberPO==0){
      $MaxDelDays='';
   }
   //Output the whole Consingnment note Report  
   echo '<td width=11%>' .  $customername .'<br/>
                       ' .  $address1 .'<br/>
                       ' .  $address2 .'<br/>
                       ' .  $phonenumber .'<br/>    
                       ' .  $RowInvoicePDFLink . '<br/><br/>
                       '.   $RowSearchConsignmentButton.'<br/><br />
                       '.   $RowEmailClientSD.'<br/><br/>
                       '.   $RowDeliveryStatus .'    
                       '.   $UpdateMessage.' </td>   
	 <td>' . $FormatedOrderDate. '</td>
         <td>' .$MaxDelDays.'</td>
         <td  width=8%>' .$HeaderColumn.'</td>'.$POList.'</tr>';

   unset($MaxDelDays);            
   unset($POList);
   unset($POListRow);
   unset($InvDelStatusoptions);
   /*End Show a table of the Invoices without modified */  
  $i++;
  $j=1;
 }
 


echo '</table>';

echo '<script>defaultControl(document.forms[0].StockCode);</script>';
echo '<input type=hidden name=theme value='.$theme.'>
<input type=hidden name=rootpath value='.$rootpath.'>'; 
echo '</form>';

//include('eservice/SendersReference.php');
include('includes/footer.inc');
?>

