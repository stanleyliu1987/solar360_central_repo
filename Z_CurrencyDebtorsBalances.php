<?php
/* $Id: Z_CurrencyDebtorsBalances.php 4543 2011-04-09 06:12:05Z daintree $*/

include('includes/session.inc');
$title=_('Currency Debtor Balances');
include('includes/header.inc');

echo '<font size=4><b>' . _('Debtors Balances By Currency Totals') . '</b></font>';

$sql = "SELECT SUM(ovamount+ovgst+ovdiscount+ovfreight-alloc) AS currencybalance,
		currcode,
		SUM((ovamount+ovgst+ovdiscount+ovfreight-alloc)/rate) AS localbalance
	FROM debtortrans INNER JOIN debtorsmaster
		ON debtortrans.debtorno=debtorsmaster.debtorno
	WHERE (ovamount+ovgst+ovdiscount+ovfreight-alloc)<>0 GROUP BY currcode";

$result = DB_query($sql,$db);


$LocalTotal =0;

echo '<table>';

while ($myrow=DB_fetch_array($result)){

	echo '<tr><td><font size=4>' . _('Total Debtor Balances in') . ' </font></td>
		<td><font size=4>' . $myrow['currcode'] . '</font></td>
		<td class=number><font size=4>' . number_format($myrow['currencybalance'],2) . '</font></td>
		<td><font size=4> in ' . $_SESSION['CompanyRecord']['currencydefault'] . '</font></td>
		<td class=number><font size=4>' . number_format($myrow['localbalance'],2) . '</font></td></tr>';
	$LocalTotal += $myrow['localbalance'];
}

echo '<tr><td colspan=4><font size=4>' . _('Total Balances in local currency') . ':</font></td>
	<td class=number><font size=4>' . number_format($LocalTotal,2) . '</font></td></tr>';

echo '</table>';

include('includes/footer.inc');
?>