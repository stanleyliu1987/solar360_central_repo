<?php

/* $Id: StockQuantityByDate.php 4558 2011-04-29 12:43:19Z daintree $ */

include('includes/session.inc');
$title = _('Stock On Hand By Date');
include('includes/header.inc');

echo '<p Class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventory') .
'" alt="" /><b>' . $title. '</b></p>';

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$sql = "SELECT categoryid, categorydescription FROM stockcategory";
$resultStkLocs = DB_query($sql, $db);

echo '<table class=selection><tr>';
echo '<td>' . _('For Stock Category') . ':</td>
	<td><select name="StockCategory"> ';
echo '<option value="All">' . _('All') . '</option>';

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockCategory']) AND $_POST['StockCategory']!='All'){
		if ($myrow['categoryid'] == $_POST['StockCategory']){
		     echo '<option selected value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		}
	}else {
		 echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
	}
}
echo '</select></td>';

$sql = "SELECT loccode, locationname FROM locations";
$resultStkLocs = DB_query($sql, $db);

echo '<td>' . _('For Stock Location') . ':</td>
	<td><select name="StockLocation"> ';

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo '<option selected value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
}
echo '</select></td>';

if (!isset($_POST['OnHandDate'])){
	$_POST['OnHandDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),0,Date('y')));
}

echo '<td>' . _('On-Hand On Date') . ':</td>
	<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="OnHandDate" size=12 maxlength=12 value="' . $_POST['OnHandDate'] . '"></td></tr>';
echo '<tr><td colspan=6><div class="centre"><input type=submit name="ShowStatus" value="' . _('Show Stock Status') .'"></div></td></tr></table>';
echo '</form>';

$TotalQuantity = 0;

if(isset($_POST['ShowStatus']) AND Is_Date($_POST['OnHandDate'])) {
        if ($_POST['StockCategory']=='All') {
                 $sql = "SELECT stockid,
                                 description,
                                 decimalplaces
                         FROM stockmaster
                         WHERE (mbflag='M' OR mbflag='B')";
         } else {
                 $sql = "SELECT stockid,
                                 description,
                                 decimalplaces
                         FROM stockmaster
                         WHERE categoryid = '" . $_POST['StockCategory'] . "'
                         AND (mbflag='M' OR mbflag='B')";
         }

	$ErrMsg = _('The stock items in the category selected cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');

	$StockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	$SQLOnHandDate = FormatDateForSQL($_POST['OnHandDate']);

	echo '<br /><table cellpadding=5 cellspacing=1 class=selection>';

	$tableheader = '<tr>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Quantity On Hand') . '</th></tr>';
	echo $tableheader;

	while ($myrows=DB_fetch_array($StockResult)) {

		$sql = "SELECT stockid,
				newqoh
				FROM stockmoves
				WHERE stockmoves.trandate <= '". $SQLOnHandDate . "'
				AND stockid = '" . $myrows['stockid'] . "'
				AND loccode = '" . $_POST['StockLocation'] ."'
				ORDER BY stkmoveno DESC LIMIT 1";

		$ErrMsg =  _('The stock held as at') . ' ' . $_POST['OnHandDate'] . ' ' . _('could not be retrieved because');

		$LocStockResult = DB_query($sql, $db, $ErrMsg);

		$NumRows = DB_num_rows($LocStockResult, $db);

		$j = 1;
		$k=0; //row colour counter

		while ($LocQtyRow=DB_fetch_array($LocStockResult)) {

			if ($k==1){
				echo '<tr class="OddTableRows">';
				$k=0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k=1;
			}

			if($NumRows == 0){
				printf('<td><a target="_blank" href="' . $rootpath . '/StockStatus.php?%s">%s</td>
					<td>%s</td>
					<td class="number">%s</td>',
					'StockID=' . strtoupper($myrows['stockid']),
					strtoupper($myrows['stockid']),
					$myrows['description'],
					0);
			} else {
				printf('<td><a target="_blank" href="' . $rootpath . '/StockStatus.php?%s">%s</td>
					<td>%s</td>
					<td class=number>%s</td>',
					'StockID=' . strtoupper($myrows['stockid']),
					strtoupper($myrows['stockid']),
					$myrows['description'],
					number_format($LocQtyRow['newqoh'],$myrows['decimalplaces']));

				$TotalQuantity += $LocQtyRow['newqoh'];
			}
			$j++;
			if ($j == 12){
				$j=1;
				echo $tableheader;
			}
		//end of page full new headings if
		}

	}//end of while loop
	echo '<tr><td>' . _('Total Quantity') . ': ' . $TotalQuantity . '</td></tr>
		</table>';
}

include('includes/footer.inc');
?>