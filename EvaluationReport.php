<?php
include('includes/session.inc');
include('includes/DefineInvCartClass.php');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


echo '<form id="EvaForm" action="' . $_SERVER['PHP_SELF'] . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="page_help_text">' . _('Evaluationn Performance Report') . '</div><br />';
/* Search function to select particular invoice no or payment date */

echo '<br /><table class="selectInvoice"><tr>';
echo '<td><font size=1>' . _('Invoice Start :') . '</b></font></td>';
echo '<td><input type="Text" name="InvoiceStart" size=20 maxlength=25></td></tr>';
echo '<td><font size=1>' . _('Invoice End :') . '</b></font></td>';
echo '<td><input type="Text" name="InvoiceEnd" size=20 maxlength=25></td></tr></table><br>';

echo '<br /><table class="selectPaymentDate"><tr>';
echo '<td><font size=1>' . _('Payment Date Start:') . '<b></font></td>';
echo '<td><input type="Text" class="date" name="PaymentDateStart" size=20 maxlength=25 alt="d/m/Y"></td></tr>';
echo '<td><font size=1>' . _('Payment Date End:') . '<b></font></td>';
echo '<td><input type="Text" class="date" name="PaymentDateEnd" size=20 maxlength=25 alt="d/m/Y"></td></tr></table><br>';

echo '<table><tr><td><input type=submit name="SearchDays" value="' . _('Search') . '"></td></tr></table>';
echo '<br />';


 /*Select Maximu Order No */
 $SQLMaxOrderList="SELECT MAX(order_) AS maxorder,MAX(paymentdate) AS maxpamentdate  FROM debtortrans";
 $ErrMsg = _('The line items of the max Order cannot be retrieved because');
 $MaxOrderResult = db_query($SQLMaxOrderList,$db,$ErrMsg);
 $MaxOrderArray=DB_fetch_array($MaxOrderResult);
 $MaxOrderNoResult=$MaxOrderArray['maxorder'];
 $MaxPaymentDateResult=$MaxOrderArray['maxpamentdate'];
 
    if(isset($_POST['InvoiceStart']) and $_POST['InvoiceStart']!=''){
    $InvoiceStart=$_POST['InvoiceStart'];
    }
    else{
    $InvoiceStart=0;    
    }
    if(isset($_POST['InvoiceEnd']) and $_POST['InvoiceEnd']!=''){
    $InvoiceEnd=$_POST['InvoiceEnd'];
    }
    else{
    $InvoiceEnd=$MaxOrderNoResult;    
    }
    if(isset($_POST['PaymentDateStart']) and $_POST['PaymentDateStart']!=''){
    $PaymentDateStart=FormatDateForSQL($_POST['PaymentDateStart']);
    }
    else{
    $PaymentDateStart='0000-00-00';    
    }
    if(isset($_POST['PaymentDateEnd']) and $_POST['PaymentDateEnd']!=''){
    $PaymentDateEnd=FormatDateForSQL($_POST['PaymentDateEnd']);
    }
    else{
    $PaymentDateEnd=$MaxPaymentDateResult; 
    }

/*Select distinct days */
 $SQLMaxDaysList="SELECT DISTINCT days FROM purchorders WHERE days>0  ORDER BY days asc";
 $ErrMsg = _('The line items of the max days cannot be retrieved because');
 $MaxDaysResult = db_query($SQLMaxDaysList,$db,$ErrMsg);
 
 /*Select total days */
 $SQLSumDaysList="SELECT count(days) as sum FROM purchorders WHERE days>0";
 $ErrMsg = _('The line items of the max days cannot be retrieved because');
 $SumDaysResult = db_query($SQLSumDaysList,$db,$ErrMsg);
 $SumDaysArray=DB_fetch_array($SumDaysResult);
 $SumResult=$SumDaysArray['sum'];
 
 $k=0;
 echo '<div class="page_help_text">Invoice Number from '.$InvoiceStart.' to '.$InvoiceEnd.' &nbsp;&nbsp;&nbsp;
                                   Payment Date from '.$PaymentDateStart.' to '.$PaymentDateEnd.'
       </div><br/><br/>';
 
 echo '<table cellpadding=2  width=50% class=selection >';
 echo '<tr><th>' . _('Max Days') .
                        '</th><th>' . _('Invoice No. %').
			'</th><th>' . _('Per. %').
			'</th><th>' . _('Accum Per. %') . 
			'</th></tr>'; 
 while ($myrow=DB_fetch_array($MaxDaysResult)) {

     
	    if ($k==1){ 
			echo '<tr class="EvenTableRows" valign=top>';
			$k=0;
		} else {
			echo '<tr class="OddTableRows" valign=top>';
			$k++;
	
                        }
                        
/**
 *  Get total number of days
 */                     
  $SQLTotalList="SELECT count(days) as  countdays FROM purchorders, debtortrans WHERE 
                                                            purchorders.ref_salesorder=debtortrans.order_ and
                                                            debtortrans.order_ >='".$InvoiceStart."' and
                                                            debtortrans.order_ <='".$InvoiceEnd."' and
                                                            debtortrans.paymentdate >='".$PaymentDateStart."' and
                                                            debtortrans.paymentdate <='".$PaymentDateEnd."' and
                                                            purchorders.days>0";
   $ErrMsg = _('The total number days cannot be retrieved because');
   $TotalResult = db_query($SQLTotalList,$db,$ErrMsg);              
   $totaldaysrow=DB_fetch_array($TotalResult);
   $SumResult=$totaldaysrow['countdays'];
/**
 *  Get relevant invoice of total number of days
 */
   $SQLTotalDaysList="SELECT count(days) as  countdays FROM purchorders, debtortrans WHERE 
                                                            purchorders.ref_salesorder=debtortrans.order_ and
                                                            purchorders.days='".$myrow['days']."' and 
                                                            debtortrans.order_ >='".$InvoiceStart."' and
                                                            debtortrans.order_ <='".$InvoiceEnd."' and
                                                            debtortrans.paymentdate >='".$PaymentDateStart."' and
                                                            debtortrans.paymentdate <='".$PaymentDateEnd."'";
   $ErrMsg = _('The total relevant number days cannot be retrieved because');
   $TotalDaysResult = db_query($SQLTotalDaysList,$db,$ErrMsg);              
   $totalrow=DB_fetch_array($TotalDaysResult);	
   
   $AccumPercentage+=$totalrow['countdays']/$SumResult*100;
   
			echo '<td align=right>' . $myrow['days'] . '</td>
                              <td align=right>' . $totalrow['countdays'] . '</td>
                              <td align=right>' . number_format($totalrow['countdays']/$SumResult*100,2) . '</td>
                              <td align=right>' . number_format($AccumPercentage,2) . '</td>
                              </tr>';

 } /* end of loop around items */    
              
 echo '</table></form>'; 
    
 
 
              
?>
