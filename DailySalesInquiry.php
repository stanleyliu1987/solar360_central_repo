<?php

/* $Id: DailySalesInquiry.php 4572 2011-05-23 10:14:06Z daintree $*/

include('includes/session.inc');
$title = _('Daily Sales Inquiry');
if(isset($_GET['notop']) and $_GET['notop']==1)
{include('includes/header_no_top_menu.inc');  }
else
{include('includes/header.inc'); }
include('includes/DefineCartClass.php');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Daily Sales') . '" alt="" />' . ' ' . _('Daily Sales') . '</p>';
echo '<div class="page_help_text">' . _('Select the month to show daily sales for') . '</div><br>';

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table cellpadding=2 class=selection><tr>';

echo '<td>' . _('Month to Show') . ':</td><td><select tabindex=1 name="MonthToShow">';


if (!isset($_POST['MonthToShow'])){
	$_POST['MonthToShow'] = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
}

$PeriodsResult = DB_query("SELECT periodno, lastdate_in_period FROM periods",$db);

while ($PeriodRow = DB_fetch_array($PeriodsResult)){
	if ($_POST['MonthToShow']==$PeriodRow['periodno']) {
	     echo '<option selected Value="' . $PeriodRow['periodno'] . '">' . MonthAndYearFromSQLDate($PeriodRow['lastdate_in_period']) . '</option>';
		 $EndDateSQL = $PeriodRow['lastdate_in_period'];
	} else {
	     echo '<option Value="' . $PeriodRow['periodno'] . '">' . MonthAndYearFromSQLDate($PeriodRow['lastdate_in_period']) . '</option>';
	}
}
echo '</select></td>';
echo '<td>' . _('Salesperson') . ':</td><td><select tabindex=2 name="Salesperson">';

$SalespeopleResult = DB_query("SELECT salesmancode, salesmanname FROM salesman",$db);
if (!isset($_POST['Salesperson'])){
	$_POST['Salesperson'] = 'All';
	echo '<option selected value="All">' . _('All') . '</option>';
} else {
	echo '<option value="All">' . _('All') . '</option>';
}
while ($SalespersonRow = DB_fetch_array($SalespeopleResult)){

	if ($_POST['Salesperson']==$SalespersonRow['salesmancode']) {
	     echo '<option selected value="' . $SalespersonRow['salesmancode'] . '">' . $SalespersonRow['salesmanname'] . '</option>';
	} else {
	     echo '<option Value="' . $SalespersonRow['salesmancode'] . '">' . $SalespersonRow['salesmanname'] . '</option>';
	}
}
echo '</select></td>';

echo '</tr></table><br /><div class="centre"><input tabindex=4 type=submit name="ShowResults" VALUE="' . _('Show Daily Sales For The Selected Month') . '">';
echo '</form></div>';
echo '<br />';
/*Now get and display the sales data returned */
if (strpos($EndDateSQL,'/')) {
	$Date_Array = explode('/',$EndDateSQL);
} elseif (strpos ($EndDateSQL,'-')) {
	$Date_Array = explode('-',$EndDateSQL);
} elseif (strpos ($EndDateSQL,'.')) {
	$Date_Array = explode('.',$EndDateSQL);
}

if (strlen($Date_Array[2])>4) {
	$Date_Array[2]= substr($Date_Array[2],0,2);
}

$StartDateSQL =  date('Y-m-d', mktime(0,0,0, (int)$Date_Array[1],1,(int)$Date_Array[0]));

$sql = "SELECT 	trandate,
				SUM(price*(1-discountpercent)* (-qty)) as salesvalue,
				SUM(CASE WHEN mbflag='A' THEN 0 ELSE (standardcost * -qty) END) as cost,
                                COUNT(DISTINCT stockmoves.transno) as ordernum
			FROM stockmoves
			INNER JOIN stockmaster
			ON stockmoves.stockid=stockmaster.stockid
			INNER JOIN custbranch 
			ON stockmoves.debtorno=custbranch.debtorno
				AND stockmoves.branchcode=custbranch.branchcode
			WHERE (stockmoves.type=10 or stockmoves.type=11)
			AND trandate>='" . $StartDateSQL . "'
			AND trandate<='" . $EndDateSQL . "'";

if ($_POST['Salesperson']!='All') {
	$sql .= " AND custbranch.salesman='" . $_POST['Salesperson'] . "'";
}

$sql .= " GROUP BY stockmoves.trandate ORDER BY stockmoves.trandate";
$ErrMsg = _('The sales data could not be retrieved because') . ' - ' . DB_error_msg($db);
$SalesResult = DB_query($sql, $db,$ErrMsg);

echo '<table cellpadding=2 class=selection>';

echo'<tr>
	<th>' . _('Sunday') . '</th>
	<th>' . _('Monday') . '</th>
	<th>' . _('Tuesday') . '</th>
	<th>' . _('Wednesday') . '</th>
	<th>' . _('Thursday') . '</th>
	<th>' . _('Friday') . '</th>
	<th>' . _('Saturday') . '</th>
        <th>' . _('Total P/W') . '</th>
        <th>' . _('Average P/W') . '</th></tr>';

$CumulativeTotalSales = 0;
$CumulativeTotalCost = 0;
$BilledDays = 0;
$DaySalesArray = array();

while ($DaySalesRow=DB_fetch_array($SalesResult)) {
	$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])] = new Cart;
	if ($DaySalesRow['salesvalue'] > 0) {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]->Sales = $DaySalesRow['salesvalue'];
	} else {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]->Sales = 0;
    }
	if ($DaySalesRow['salesvalue'] > 0 ) {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]->GPPercent = ($DaySalesRow['salesvalue']-$DaySalesRow['cost'])/$DaySalesRow['salesvalue'];
    } else {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]->GPPercent = 0;
    }
       if($DaySalesRow['ordernum']>0){
           $DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]->OrderNum = $DaySalesRow['ordernum'];
       }
       else{
           $DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]->OrderNum = 0;
       }
	$BilledDays++;
	$CumulativeTotalSales += $DaySalesRow['salesvalue'];
	$CumulativeTotalCost += $DaySalesRow['cost'];
}
//end of while loop
echo '<tr>';
$ColumnCounter = DayOfWeekFromSQLDate($StartDateSQL);
for ($i=0;$i<$ColumnCounter;$i++){
	echo '<td></td>';
}
$DayNumber = 1;
/*Set up day number headings*/
for ($i=$ColumnCounter;$i<=6;$i++){
       echo '<th>' . $DayNumber . '</th>';
       $DayNumber++;
}
echo '</tr><tr>';
for ($i=0;$i<$ColumnCounter;$i++){
	echo '<td></td>';
}

$LastDayOfMonth = DayOfMonthFromSQLDate($EndDateSQL);
for ($i=1;$i<=$LastDayOfMonth;$i++){
		$ColumnCounter++;
		if(isset($DaySalesArray[$i])) {
                        $TotalWeekSalesValue +=$DaySalesArray[$i]->Sales;
                        $TotalWeekSalesNo +=$DaySalesArray[$i]->OrderNum;
                        $TotalMonthSalesNo +=$DaySalesArray[$i]->OrderNum;
                        $BillDaysforWeek +=1;
			echo '<td class="number" style="outline: 1px solid gray;">' . number_format($DaySalesArray[$i]->Sales,2) . '<br />'.number_format($DaySalesArray[$i]->OrderNum,0).'<br />' .  number_format($DaySalesArray[$i]->GPPercent*100,2) . '%</td>';
		} else {
                        $TotalWeekSalesValue +=0;
                        $TotalWeekSalesNo +=0;
                        $TotalMonthSalesNo +=0;
			echo '<td class="number" style="outline: 1px solid gray;">' . number_format(0,2) . '<br />'.number_format(0,0) .'<br />' .  number_format(0,2) . '%</td>';
		} 
                if($i==$LastDayOfMonth and $ColumnCounter<7){
                    for ($r=$ColumnCounter; $r<7; $r++){
                        echo '<td></td>';
                    }
                    echo '<td class="number">'.number_format($TotalWeekSalesValue,2).'<br />'.number_format($TotalWeekSalesNo,0).'</td>';
                    echo '<td class="number">'.number_format($TotalWeekSalesValue/$BillDaysforWeek,2).'<br />'.number_format($TotalWeekSalesNo/$BillDaysforWeek,0).'</td>';
                }
		if ($ColumnCounter==7){ 
                        echo '<td class="number">'.number_format($TotalWeekSalesValue,2).'<br />'.number_format($TotalWeekSalesNo,0).'</td>';
                        echo '<td class="number">'.number_format($TotalWeekSalesValue/$BillDaysforWeek,2).'<br />'.number_format($TotalWeekSalesNo/$BillDaysforWeek,0).'</td>';
			echo '</tr><tr>';
                        for ($j=1;$j<=7;$j++){
                            if($DayNumber<=$LastDayOfMonth){
			    echo '<th>' . $DayNumber. '</th>';
                            }
                            
                            $DayNumber++;
                            if($DayNumber>$LastDayOfMonth){
                                   break;
                            }
                        }
                        echo '</tr><tr>';
			$ColumnCounter=0;
                        $TotalWeekSalesValue=0;
                        $TotalWeekSalesNo=0; 
                        $BillDaysforWeek=0;
                        
		}


}
if ($ColumnCounter!=0) {
	echo '</tr><tr>';
}

if ($CumulativeTotalSales !=0){
	$AverageGPPercent = ($CumulativeTotalSales - $CumulativeTotalCost)*100/$CumulativeTotalSales;
	$AverageDailySales = $CumulativeTotalSales/$BilledDays;
} else {
	$AverageGPPercent = 0;
	$AverageDailySales = 0;
}

echo '<th colspan=7>' . _('Total Sales for month') . ': ' . number_format($CumulativeTotalSales,0) . ' ' . _('GP%') . ': ' . number_format($AverageGPPercent,1) . '% ' . _('Avg Daily Sales') . ': ' . number_format($AverageDailySales,0) . '</th>'.
     '<th colspan=2> '._('Total Sales No.') . ': '.number_format($TotalMonthSalesNo,0) . ' '._('Avg Sales No.') . ': '.number_format($TotalMonthSalesNo/$BilledDays,0) .'</th></tr>';

echo '</table>';

include('includes/footer.inc');
?>