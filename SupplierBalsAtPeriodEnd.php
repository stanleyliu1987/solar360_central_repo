<?php

/* $Id: SupplierBalsAtPeriodEnd.php 4551 2011-04-16 06:20:56Z daintree $*/

include('includes/session.inc');

If (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1){

	include('includes/PDFStarter.php');

	$pdf->addInfo('Title',_('Supplier Balance Listing'));
	$pdf->addInfo('Subject',_('Supplier Balances'));

	$FontSize=12;
	$PageNumber=0;
	$line_height=12;

      /*Now figure out the aged analysis for the Supplier range under review */

	$SQL = "SELECT suppliers.supplierid,
			suppliers.suppname,
  			currencies.currency,
			SUM((supptrans.ovamount + supptrans.ovgst - supptrans.alloc)/supptrans.rate) AS balance,
			SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS fxbalance,
			SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' THEN
	(supptrans.ovamount + supptrans.ovgst)/supptrans.rate ELSE 0 END)
	 AS afterdatetrans,
	 	Sum(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "'
				AND (supptrans.type=22 OR supptrans.type=21) THEN
	       supptrans.diffonexch ELSE 0 END)
	 AS afterdatediffonexch,
			Sum(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' THEN
	supptrans.ovamount + supptrans.ovgst ELSE 0 END
	) AS fxafterdatetrans
	FROM suppliers,
		currencies,
		supptrans
	WHERE suppliers.currcode = currencies.currabrev
		AND suppliers.supplierid = supptrans.supplierno
		AND suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
		AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
	GROUP BY suppliers.supplierid,
		suppliers.suppname,
		currencies.currency";

	$SupplierResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		$title = _('Supplier Balances - Problem Report');
		include('includes/header.inc');
		prnMsg(_('The Supplier details could not be retrieved by the SQL because') . ' ' . DB_error_msg($db),'error');
		echo '<br /><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($SupplierResult) ==0) {
		$title = _('Supplier Balances - Problem Report');
		include('includes/header.inc');
		prnMsg(_('There are no supplier balances to list'),'error');
		echo '<br /><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	}

	include ('includes/PDFSupplierBalsPageHeader.inc');

	$TotBal=0;

	While ($SupplierBalances = DB_fetch_array($SupplierResult,$db)){

		$Balance = $SupplierBalances['balance'] - $SupplierBalances['afterdatetrans'] + $SupplierBalances['afterdatediffonexch'];
		$FXBalance = $SupplierBalances['fxbalance'] - $SupplierBalances['fxafterdatetrans'];

		if (ABS($Balance)>0.009 OR ABS($FXBalance)>0.009) {

			$DisplayBalance = number_format($SupplierBalances['balance'] - $SupplierBalances['afterdatetrans'],2);
			$DisplayFXBalance = number_format($SupplierBalances['fxbalance'] - $SupplierBalances['fxafterdatetrans'],2);

			$TotBal += $Balance;

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,220-$Left_Margin,$FontSize,$SupplierBalances['supplierid'] . ' - ' . $SupplierBalances['suppname'],'left');
			$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
			$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayFXBalance,'right');
			$LeftOvers = $pdf->addTextWrap(350,$YPos,100,$FontSize,$SupplierBalances['currency'],'left');

			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
			include('includes/PDFSupplierBalsPageHeader.inc');
			}
		}
	} /*end Supplier aged analysis while loop */

	$YPos -=$line_height;
	if ($YPos < $Bottom_Margin + (2*$line_height)){
		$PageNumber++;
		include('includes/PDFSupplierBalsPageHeader.inc');
	}

	$DisplayTotBalance = number_format($TotBal,2);

	$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayTotBalance,'right');

	$pdf->OutputD($_SESSION['DatabaseName'] . '_Supplier_Balances_at_Period_End_' . Date('Y-m-d') . '.pdf');
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$title=_('Supplier Balances At A Period End');
	include('includes/header.inc');

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' .
		_('Supplier Allocations') . '" alt="" />' . ' ' . $title . '</p>';
	if (!isset($_POST['FromCriteria'])) {
		$_POST['FromCriteria'] = '1';
	}
	if (!isset($_POST['ToCriteria'])) {
		$_POST['ToCriteria'] = 'zzzzzz';
	}
	/*if $FromCriteria is not set then show a form to allow input	*/

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post"><table class="selection">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<tr><td>' . _('From Supplier Code') . ':</font></td>
			<td><input Type=text maxlength=6 size=7 name=FromCriteria value="'.$_POST['FromCriteria'].'"></td></tr>';
	echo '<tr><td>' . _('To Supplier Code') . ':</td>
			<td><input Type=text maxlength=6 size=7 name=ToCriteria value="'.$_POST['ToCriteria'].'"></td></tr>';

	echo '<tr><td>' . _('Balances As At') . ':</td>
			<td><select Name="PeriodEnd">';

	$sql = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";

	$ErrMsg = _('Could not retrieve period data because');
	$Periods = DB_query($sql,$db,$ErrMsg);

	while ($myrow = DB_fetch_array($Periods,$db)){
		echo '<option value="' . $myrow['lastdate_in_period'] . '" selected="TRUE">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period'],'M',-1).'</option>';
	}
	echo '</select></td></tr>';

	echo '</table>
			<br /><div class="centre"><input type=Submit Name="PrintPDF" Value="' . _('Print PDF') . '"></div>';

	include('includes/footer.inc');
}/*end of else not PrintPDF */

?>