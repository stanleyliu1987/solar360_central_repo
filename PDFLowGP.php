<?php

/* $Id: PDFLowGP.php 4551 2011-04-16 06:20:56Z daintree $*/

include('includes/session.inc');

if (!isset($_POST['FromCat'])  OR $_POST['FromCat']=='') {
	$title=_('Low Gross Profit Sales');
}
$debug=0;
if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', _('Low Gross Profit Sales'));
	$pdf->addInfo('Subject', _('Low Gross Profit Sales'));
	$FontSize=10;
	$PageNumber=1;
	$line_height=12;

	$title = _('Low GP sales') . ' - ' . _('Problem Report');

	if (! Is_Date($_POST['FromDate']) OR ! Is_Date($_POST['ToDate'])){
		include('includes/header.inc');
		prnMsg(_('The dates entered must be in the format') . ' '  . $_SESSION['DefaultDateFormat'],'error');
		include('includes/footer.inc');
		exit;
	}

	  /*Now figure out the data to report for the category range under review */
	$SQL = "SELECT stockmaster.categoryid,
						stockmaster.stockid,
                                                stockmaster.description,
						stockmoves.transno,
						stockmoves.trandate,
						systypes.typename,
						stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost as unitcost,
						stockmoves.qty,
						stockmoves.debtorno,
						stockmoves.branchcode,
						stockmoves.price*(1-stockmoves.discountpercent) as sellingprice,
						((stockmoves.price*(1-stockmoves.discountpercent)) - (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))*(-qty) AS gp,
						debtorsmaster.name,
                                                debtortrans.order_
				FROM stockmaster,
						stockmoves,
						systypes,
						debtorsmaster,
                                                debtortrans
				WHERE stockmoves.type=systypes.typeid
                                AND debtortrans.transno=stockmoves.transno
				AND stockmaster.stockid=stockmoves.stockid
				AND stockmoves.trandate >= '" . FormatDateForSQL($_POST['FromDate']) . "'
				AND stockmoves.trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'
				AND ((stockmoves.price*(1-stockmoves.discountpercent)) - (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))/(stockmoves.price*(1-stockmoves.discountpercent)) <=" . ($_POST['GPMin']/100) . "
				AND stockmoves.debtorno=debtorsmaster.debtorno
				ORDER BY stockmoves.transno";

	$LowGPSalesResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {

	  include('includes/header.inc');
		prnMsg(_('The low GP items could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
		echo '<br /><a href="' .$rootpath .'/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
		  echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	if (DB_num_rows($LowGPSalesResult) == 0) {

		include('includes/header.inc');
		prnMsg(_('No low GP items retrieved'), 'warn');
		echo '<br /><a href="'  . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
		  echo '<br />' .  $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	include ('includes/PDFLowGPPageHeader.inc');
	$Tot_Val=0;
        $TotalGP=0;
        $TotalSell=0;
        $TotalCost=0;
	$Category = '';
	$CatTot_Val=0;
	while ($LowGPItems = DB_fetch_array($LowGPSalesResult,$db)){
                
		$YPos -=$line_height;
		$FontSize=8;

		$LeftOvers = $pdf->addTextWrap($Left_Margin+2,$YPos,30,$FontSize,'W'.$LowGPItems['order_']);
                
                $LeftOvers = $pdf->addTextWrap(85,$YPos,70,$FontSize,substr(ConvertSQLDate($LowGPItems['trandate']),0,5).'  '.substr($LowGPItems['description'],0,15).'  x'.(-$LowGPItems['qty']));
		$LeftOvers = $pdf->addTextWrap(220,$YPos,50,$FontSize,$LowGPItems['name']);
		$DisplayUnitCost = number_format($LowGPItems['unitcost']*(-$LowGPItems['qty']),2);
		$DisplaySellingPrice = number_format($LowGPItems['sellingprice']*(-$LowGPItems['qty']),2);
		$DisplayGP = number_format($LowGPItems['gp'],2);
		$DisplayGPPercent = number_format(($LowGPItems['gp']*100)/($LowGPItems['sellingprice']*(-$LowGPItems['qty'])),1);

		$LeftOvers = $pdf->addTextWrap(326,$YPos,60,$FontSize,$DisplaySellingPrice,'right');
		$LeftOvers = $pdf->addTextWrap(384,$YPos,62,$FontSize,$DisplayUnitCost, 'right');
		$LeftOvers = $pdf->addTextWrap(440,$YPos,60,$FontSize,$DisplayGP, 'right');
		$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayGPPercent . '%', 'right');

		if ($YPos < $Bottom_Margin + $line_height){
			include('includes/PDFLowGPPageHeader.inc');
		}
                
                $TotalGP+=$LowGPItems['gp'];
                $TotalSell+=$LowGPItems['sellingprice']*(-$LowGPItems['qty']);
                $TotalCost+=$LowGPItems['unitcost']*(-$LowGPItems['qty']);

	} /*end low GP items while loop */
     

	$YPos -= (2*$line_height);
        /*Total value */
        $LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,'Total Summary', 'right');
        $LeftOvers = $pdf->addTextWrap(326,$YPos,60,$FontSize,number_format($TotalSell,2), 'right');
        $LeftOvers = $pdf->addTextWrap(386,$YPos,60,$FontSize,number_format($TotalCost,2), 'right');
        $LeftOvers = $pdf->addTextWrap(440,$YPos,60,$FontSize,number_format($TotalGP,2), 'right');
        $LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,number_format(($TotalGP*100)/$TotalSell,2).'%', 'right');
	$FontSize =10;
	$pdf->OutputD($_SESSION['DatabaseName'] . '_LowGPSales_' . date('Y-m-d') . '.pdf');
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	include('includes/header.inc');

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . $title . '" alt="" />' . ' '
		. _('Low Gross Profit Report') . '</p>';

	if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate'])) {

	/*if $FromDate is not set then show a form to allow input */
		$_POST['FromDate']=Date($_SESSION['DefaultDateFormat']);
		$_POST['ToDate']=Date($_SESSION['DefaultDateFormat']);
		$_POST['GPMin']=0;
		echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">
			<table class="selection">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<tr><td>' . _('Sales Made From') . ' (' . _('in the format') . ' ' . $_SESSION['DefaultDateFormat'] . '):</td>
				<td><input type=text class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" size=10 maxlength="10" value="' . $_POST['FromDate'] . '"></td>
			</tr>';

		echo '<tr><td>' . _('Sales Made To') . ' (' . _('in the format') . ' ' . $_SESSION['DefaultDateFormat'] . '):</td>
					<td><input type=text class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" size="10" maxlength="10" value="' . $_POST['ToDate'] . '"></td>
			</tr>';

		echo '<tr><td>' . _('Show sales with GP') . '%' . _('below') . ':</td>
								<td><input type=text class="number" name="GPMin" maxlength="3" size="3" value="' . $_POST['GPMin'] . '"></td>
						</tr>';

		echo '</table>
				<br /><div class="centre"><input type="submit" name="PrintPDF" value="' . _('Print PDF') . '"></div>';
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>