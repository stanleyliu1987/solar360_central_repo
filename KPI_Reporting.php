<?php

/* $Id: OrderManagement.php 4579 2011-08-04 AU stan $ */

$PricesSecurity = 12;
$title = 'KPI Reports Summary';
include('includes/session.inc');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="UserID" id="UserID" value="' . $_SESSION['UserID'] . '" />';
echo '<a href="' . $rootpath . '/index.php">' . _('Back to Main Menu') . '</a>';
/* Initial Invoice Start and End Date 14102014*/
$InvoiceStart = date('Y-m-d');
$InvoiceEnd = date('Y-m-d');
$HoursSOToInv=4;
$HoursReleaseToPO=2;
/* Search Invoice according to partial infomation */
if (isset($_POST['Search'])) {
    if (isset($_POST['InvoiceStart']) and $_POST['InvoiceStart'] != '') {
        $InvoiceStart = FormatDateForSQL($_POST['InvoiceStart']);
    }

    if (isset($_POST['InvoiceEnd']) and $_POST['InvoiceEnd'] != '') {
        $InvoiceEnd = FormatDateForSQL($_POST['InvoiceEnd']);
    }
    
    if($InvoiceEnd<$InvoiceStart){
       $msg = _('Invoice end date cannot be before start date, please choose again');
       $msgtype="error";
    }
    if(isset($_POST['HoursSOToInv']) and ($_POST['HoursSOToInv']!='') and is_numeric($_POST['HoursSOToInv']) and $_POST['HoursSOToInv']>0){
        $HoursSOToInv=$_POST['HoursSOToInv'];
    }
    
    if(isset($_POST['HoursReleaseToPO']) and ($_POST['HoursReleaseToPO']!='') and is_numeric($_POST['HoursReleaseToPO']) and $_POST['HoursReleaseToPO']>0){
        $HoursReleaseToPO=$_POST['HoursReleaseToPO'];
    }

    $KPIReporting = new KPIReportingModel($db, $InvoiceStart, $InvoiceEnd);

    /* 0. Retrieve Total Order, Total before/after order within the selected time frame */
    $TotalInvoiceResult = $KPIReporting->TotalInvoiceResult();

    $TotalBeforeTimeInvoiceResult= $KPIReporting->TotalBeforeTimeInvoiceResult();

    $TotalAfterTimeInvoiceResult= $KPIReporting->TotalAfterTimeInvoiceResult();
    
    $TotalOnCustomerRequiredTimeInvoiceResult= $KPIReporting->TotalOnCustomerRequiredTimeInvoiceResult();

    /* 1. KPI1 Sales Order to Invoice within 4 hours */
    $KPIOrderToInvoiceResult = $KPIReporting->KPIOrderToInvoiceResult($HoursSOToInv); 
    /* 2. KPI2 Release Invoice to PO/DD Emial within 2 hours */
    $KPIReleasedInvToPODDEmailResult = $KPIReporting->KPIReleasedInvtoPODDEmailResult($HoursReleaseToPO);
    /* 3. KPI3 Dispatch Stock Date (order before or after the 9:30am) */
    $KPIDispatchBeforeTimeResult = $KPIReporting->KPIDispatchBeforeTimeResult();
    $KPIDispatchAfterTimeResult = $KPIReporting->KPIDispatchAfterTimeResult();
    /* 4. KPI4 Stock Delivery Date */

    /* 5. KPI5 Customer Required Delivery Date Achieved */
    $KPIFullCustomerDelDateResult = $KPIReporting->KPIWithinCustomerRequiredDateResult();       
}

if (strlen($msg)>1){
	prnMsg($msg,$msgtype);
}
/* EndSearch Invoice according to partial infomation */

/* Display Invoice Search Options */
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . $title . '</p>';
echo '<table cellpadding="3" colspan="4" class="selection">';
echo '<tr><td><font size=1>' . _('Invoice Start :') . '</b></font></td>';
echo '<td><input type="Text" class="date" name="InvoiceStart" size=20 maxlength=25 alt="d/m/Y" value="' . ConvertSQLDate($InvoiceStart) . '"></td>';
echo '<td><font size=1>' . _('Invoice End :') . '</b></font></td>';
echo '<td><input type="Text" class="date" name="InvoiceEnd" size=20 maxlength=25 alt="d/m/Y" value="' . ConvertSQLDate($InvoiceEnd) . '"></td></tr>';

echo '<tr><td><font size=1>' . _('KPI Processing Hours (Sales Order to Invoice) :') . '</b></font></td>';
echo '<td><input type="Text" name="HoursSOToInv" size=3 maxlength=3  value="' . $HoursSOToInv . '"></td>';
echo '<td><font size=1>' ._('KPI Processing Hours (Released Invoice to PO/DD Emails) :') . '</b></font></td>';
echo '<td><input type="Text" name="HoursReleaseToPO" size=3 maxlength=3  value="' . $HoursReleaseToPO . '"></td></tr></table><br>';

echo '<div align=center><input type=submit name="Search" value="' . _('Search Now') . '"></div>';

/* Display Total Order Details */
if (isset($_POST['Search'])) {
   if ($TotalInvoiceResult > 0){ 
    echo '<div class="page_help_text">Time Period from ' . $InvoiceStart . ' to ' . $InvoiceEnd . '</div>';
    echo '<table cellpadding=2  width=50% class=selection >';
    echo '<tr><th>' . _('KPI Type') .'</th>'.
    '<th>' . _('Order Number') .'</th>'.
   '<th>' . _('Total Order Number') .'</th>'.         
    '<th>' . _('KPI %') .'</th></tr>'; 
    /* Display KPI1 Report */
if (isset($KPIOrderToInvoiceResult) and $KPIOrderToInvoiceResult > 0) {  
    echo '<tr><td align=right>The processing time of invoice placed after the sales order received within '.$HoursSOToInv.' hours time frame</td>
                              <td align=right>' . $KPIOrderToInvoiceResult . '</td>
                              <td align=right>' . $TotalInvoiceResult . '</td>
                              <td align=right>' . number_format($KPIOrderToInvoiceResult / $TotalInvoiceResult * 100, 2) . '</td>
                              </tr>';
      }
      
    /* Display KPI2 Report */
if (isset($KPIReleasedInvToPODDEmailResult) and $KPIReleasedInvToPODDEmailResult > 0) {
    echo '<tr><td align=right>The processing time of sending PO/DD email to the Supplier after the invoiced released within '.$HoursReleaseToPO.' hours time frame</td>
                              <td align=right>' . $KPIReleasedInvToPODDEmailResult . '</td>
                              <td align=right>' . $TotalInvoiceResult . '</td>    
                              <td align=right>' . number_format($KPIReleasedInvToPODDEmailResult / $TotalInvoiceResult * 100, 2) . '</td>
                              </tr>';
      }
      
    /* Display KPI3 Report */
if (isset($KPIDispatchBeforeTimeResult) and $KPIDispatchBeforeTimeResult > 0) {
    echo '<tr><td align=right>Dispatch stock date is the same day as order placed date (before 9:30am)</td>
                              <td align=right>' . $KPIDispatchBeforeTimeResult . '</td>
                              <td align=right>' .$TotalBeforeTimeInvoiceResult . '</td>    
                              <td align=right>' . number_format($KPIDispatchBeforeTimeResult / $TotalBeforeTimeInvoiceResult * 100, 2) . '</td>
                              </tr>';
      }
if (isset($KPIDispatchAfterTimeResult) and $KPIDispatchAfterTimeResult > 0) {
    echo '<tr><td align=right>Dispatch stock date is the day after of the order placed date (after 9:30am)</td>
                              <td align=right>' . $KPIDispatchAfterTimeResult . '</td>
                              <td align=right>' . $TotalAfterTimeInvoiceResult . '</td>    
                              <td align=right>' . number_format($KPIDispatchAfterTimeResult / $TotalAfterTimeInvoiceResult * 100, 2) . '</td>
                              </tr>';
      }
      
    /* Display KPI4 Report */  
      
    /* Display KPI5 Report */ 
if (isset($KPIFullCustomerDelDateResult) and $KPIFullCustomerDelDateResult > 0) {
    echo '<tr><td align=right>Stock delivered on customer required date</td>
                              <td align=right>' . $KPIFullCustomerDelDateResult . '</td>
                              <td align=right>' . $TotalOnCustomerRequiredTimeInvoiceResult . '</td>    
                              <td align=right>' . number_format($KPIFullCustomerDelDateResult / $TotalOnCustomerRequiredTimeInvoiceResult * 100, 2) . '</td>
                              </tr>';
      }
   }
   /* Empty result message */
   else{
         prnMsg( _('Invoice results cannot be found in the system, please select again'),'info'); 
      }
}

echo '</table>';
echo '</form>';
include('includes/footer.inc');
?>