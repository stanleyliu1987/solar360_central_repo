<?php
/* $Id: SelectProduct.php 4579 2011-05-29 04:07:16Z daintree $*/

$PricesSecurity = 12;//don't show pricing info unless security token 12 available to user
$SuppliersSecurity = 9; //don't show supplier purchasing info unless security token 9 available to user

include ('includes/session.inc');
$title = _('Search Inventory Items');
include ('includes/header.inc');

if (isset($_GET['StockID'])) {
	//The page is called with a StockID
	$_GET['StockID'] = trim(strtoupper($_GET['StockID']));
	$_POST['Select'] = trim(strtoupper($_GET['StockID']));
}
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/inventory.png" title="' . _('Inventory Items') . '" alt="" />' . ' ' . _('Inventory Items') . '</p>';
if (isset($_GET['NewSearch']) or isset($_POST['Next']) or isset($_POST['Previous']) or isset($_POST['Go'])) {
	unset($StockID);
	unset($_SESSION['SelectedStockItem']);
	unset($_POST['Select']);
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_POST['StockCode'])) {
	$_POST['StockCode'] = trim(strtoupper($_POST['StockCode']));
}
// Always show the search facilities
$SQL = "SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$result1 = DB_query($SQL, $db);
if (DB_num_rows($result1) == 0) {
	echo '<p><font size=4 color=red>' . _('Problem Report') . ':</font><br />' . _('There are no stock categories currently defined please use the link below to set them up').'</p>';
	echo '<br /><a href="' . $rootpath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
	exit;
}
// end of showing search facilities
/* displays item options if there is one and only one selected */
if (!isset($_POST['Search']) AND (isset($_POST['Select']) OR isset($_SESSION['SelectedStockItem']))) {
	if (isset($_POST['Select'])) {
		$_SESSION['SelectedStockItem'] = $_POST['Select'];
		$StockID = $_POST['Select'];
		unset($_POST['Select']);
	} else {
		$StockID = $_SESSION['SelectedStockItem'];
	}
	$result = DB_query("SELECT stockmaster.description,
								stockmaster.mbflag,
								stockcategory.stocktype,
								stockmaster.units,
								stockmaster.decimalplaces,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS cost,
								stockmaster.discontinued,
								stockmaster.eoq,
								stockmaster.volume,
								stockmaster.kgs
								FROM stockmaster INNER JOIN stockcategory
								ON stockmaster.categoryid=stockcategory.categoryid
						WHERE stockid='" . $StockID . "'", $db);
	$myrow = DB_fetch_array($result);
	$Its_A_Kitset_Assembly_Or_Dummy = false;
	$Its_A_Dummy = false;
	$Its_A_Kitset = false;
	$Its_A_Labour_Item = false;
	echo '<table width="90%"><tr><th colspan="3"><img src="' . $rootpath . '/css/' . $theme . '/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b>' . ' ' . $StockID . ' - ' . $myrow['description'] . '</b></th></tr>';
	
	echo '<tr><td width="40%" valign="top">
			<table align="left">'; //nested table
	echo '<tr><th class="number">' . _('Item Type:') . '</th>
			<td colspan="2" class="select">';
	switch ($myrow['mbflag']) {
		case 'A':
			echo _('Assembly Item');
			$Its_A_Kitset_Assembly_Or_Dummy = True;
		break;
		case 'K':
			echo _('Kitset Item');
			$Its_A_Kitset_Assembly_Or_Dummy = True;
			$Its_A_Kitset = True;
		break;
		case 'D':
			echo _('Service/Labour Item');
			$Its_A_Kitset_Assembly_Or_Dummy = True;
			$Its_A_Dummy = True;
			if ($myrow['stocktype'] == 'L') {
				$Its_A_Labour_Item = True;
			}
		break;
		case 'B':
			echo _('Purchased Item');
		break;
		default:
			echo _('Manufactured Item');
		break;
	}
	echo '</td><th class="number">' . _('Control Level:') . '</th><td class="select">';
	if ($myrow['serialised'] == 1) {
		echo _('serialised');
	} elseif ($myrow['controlled'] == 1) {
		echo _('Batchs/Lots');
	} else {
		echo _('N/A');
	}
	echo '</td><th class="number">' . _('Units') . ':</th>
			<td class="select">' . $myrow['units'] . '</td></tr>';
	echo '<tr><th class="number">' . _('Volume') . ':</th>
			<td class="select" colspan="2">' . number_format($myrow['volume'], 3) . '</td>
			<th class="number">' . _('Weight') . ':</th>
			<td class="select">' . number_format($myrow['kgs'], 3) . '</td>
			<th class="number">' . _('EOQ') . ':</th>
			<td class="select">' . number_format($myrow['eoq'], $myrow['decimalplaces']) . '</td></tr>';
	if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
		echo '<tr><th colspan="2">' . _('Sell Price') . ':</th>
				<td class="select">';
		$PriceResult = DB_query("SELECT typeabbrev, price FROM prices
								WHERE currabrev ='" . $_SESSION['CompanyRecord']['currencydefault'] . "'
								AND typeabbrev = '" . $_SESSION['DefaultPriceList'] . "'
								AND debtorno=''
								AND branchcode=''
								AND stockid='" . $StockID . "'", $db);
		if ($myrow['mbflag'] == 'K' OR $myrow['mbflag'] == 'A') {
			$CostResult = DB_query("SELECT SUM(bom.quantity * (stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost)) AS cost
									FROM bom INNER JOIN
										stockmaster
									ON bom.component=stockmaster.stockid
									WHERE bom.parent='" . $StockID . "'
									AND bom.effectiveto > '" . Date('Y-m-d') . "'
									AND bom.effectiveafter < '" . Date('Y-m-d') . "'", $db);
			$CostRow = DB_fetch_row($CostResult);
			$Cost = $CostRow[0];
		} else {
			$Cost = $myrow['cost'];
		}
		if (DB_num_rows($PriceResult) == 0) {
			echo _('No Default Price Set in Home Currency');
			$Price = 0;
		} else {
			$PriceRow = DB_fetch_row($PriceResult);
			$Price = $PriceRow[1];
			echo $PriceRow[0] . '</td><td class="select">' . number_format($Price, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<th class="number">' . _('Gross Profit') . '</th>
				<td class="select">';
			if ($Price > 0) {
				$GP = number_format(($Price - $Cost) * 100 / $Price, 1);
			} else {
				$GP = _('N/A');
			}
			echo $GP . '%' . '</td></tr>';
			while ($PriceRow = DB_fetch_row($PriceResult)) {
				$Price = $PriceRow[1];
				echo '<tr><td></td>
						<th>' . $PriceRow[0] . '</th>
						<td class="select">' . number_format($Price, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<th class="number">' . _('Gross Profit') . '</th>
						<td class="select">';
				if ($Price > 0) {
					$GP = number_format(($Price - $Cost) * 100 / $Price, 1);
				} else {
					$GP = _('N/A');
				}
				echo $GP . '%' . '</td></tr>';
				echo '</td></tr>';
			}
		}
		if ($myrow['mbflag'] == 'K' OR $myrow['mbflag'] == 'A') {
			$CostResult = DB_query("SELECT SUM(bom.quantity * (stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost)) AS cost
									FROM bom INNER JOIN
										stockmaster
									ON bom.component=stockmaster.stockid
									WHERE bom.parent='" . $StockID . "'
									AND bom.effectiveto > '" . Date('Y-m-d') . "'
									AND bom.effectiveafter < '" . Date('Y-m-d') . "'", $db);
			$CostRow = DB_fetch_row($CostResult);
			$Cost = $CostRow[0];
		} else {
			$Cost = $myrow['cost'];
		}
		echo '<th class="number">' . _('Cost') . '</th>
			<td class="select">' . number_format($Cost, 3) . '</td>';
	} //end of if PricesSecuirty allows viewing of prices
	echo '</table>'; //end of first nested table
	// Item Category Property mod: display the item properties
	echo '<table align="left">';
	$CatValResult = DB_query("SELECT categoryid
							FROM stockmaster
							WHERE stockid='" . $StockID . "'", $db);
	$CatValRow = DB_fetch_row($CatValResult);
	$CatValue = $CatValRow[0];
	$sql = "SELECT stkcatpropid,
					label,
					controltype,
					defaultvalue
				FROM stockcatproperties
				WHERE categoryid ='" . $CatValue . "'
				AND reqatsalesorder =0
				ORDER BY stkcatpropid";
	$PropertiesResult = DB_query($sql, $db);
	$PropertyCounter = 0;
	$PropertyWidth = array();
	while ($PropertyRow = DB_fetch_array($PropertiesResult)) {
		$PropValResult = DB_query("SELECT value
									FROM stockitemproperties
									WHERE stockid='" . $StockID . "'
									AND stkcatpropid ='" . $PropertyRow['stkcatpropid']."'", $db);
		$PropValRow = DB_fetch_row($PropValResult);
		$PropertyValue = $PropValRow[0];
		echo '<tr><th align="right">' . $PropertyRow['label'] . ':</th>';
		switch ($PropertyRow['controltype']) {
			case 0; //textbox
			echo '<td class="select" width="60"><input type="text" name="PropValue' . $PropertyCounter . '" value="' . $PropertyValue . '" />';
		break;
		case 1; //select box
		$OptionValues = explode(',', $PropertyRow['defaultvalue']);
		echo '<td align="left" width="60"><select name="PropValue' . $PropertyCounter . '">';
		foreach($OptionValues as $PropertyOptionValue) {
			if ($PropertyOptionValue == $PropertyValue) {
				echo '<option selected="True" value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
			} else {
				echo '<option value="' . $PropertyOptionValue . '">' . $PropertyOptionValue . '</option>';
			}
		}
		echo '</select>';
	break;
	case 2; //checkbox
	echo '<td align="left" width="60"><input type="checkbox" name="PropValue' . $PropertyCounter . '"';
	if ($PropertyValue == 1) {
		echo ' checked';
	}
	echo ' />';
break;
} //end switch
echo '</td></tr>';
$PropertyCounter++;
} //end loop round properties for the item category
echo '</table>'; //end of Item Category Property mod
echo '<td style="width: 15%; vertical-align: top">
			<table>'; //nested table to show QOH/orders
$QOH = 0;
switch ($myrow['mbflag']) {
	case 'A':
	case 'D':
	case 'K':
		$QOH = _('N/A');
		$QOO = _('N/A');
	break;
	case 'M':
	case 'B':
		$QOHResult = DB_query("SELECT sum(quantity)
						FROM locstock
						WHERE stockid = '" . $StockID . "'", $db);
		$QOHRow = DB_fetch_row($QOHResult);
		$QOH = number_format($QOHRow[0], $myrow['decimalplaces']);
		$QOOSQL="SELECT SUM(purchorderdetails.quantityord -purchorderdetails.quantityrecd) AS QtyOnOrder
					FROM purchorders INNER JOIN purchorderdetails
					ON purchorders.orderno=purchorderdetails.orderno
					WHERE purchorderdetails.itemcode='" . $StockID . "'
					AND purchorderdetails.completed =0 
					AND purchorders.status<>'Cancelled'
					AND purchorders.status<>'Pending'
					AND purchorders.status<>'Rejected'";
		$QOOResult = DB_query($QOOSQL, $db);
		if (DB_num_rows($QOOResult) == 0) {
			$QOO = 0;
		} else {
			$QOORow = DB_fetch_row($QOOResult);
			$QOO = $QOORow[0];
		}
		//Also the on work order quantities
		$sql = "SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS qtywo
				FROM woitems INNER JOIN workorders
				ON woitems.wo=workorders.wo
				WHERE workorders.closed=0
				AND woitems.stockid='" . $StockID . "'";
		$ErrMsg = _('The quantity on work orders for this product cannot be retrieved because');
		$QOOResult = DB_query($sql, $db, $ErrMsg);
		if (DB_num_rows($QOOResult) == 1) {
			$QOORow = DB_fetch_row($QOOResult);
			$QOO+= $QOORow[0];
		}
		$QOO = number_format($QOO, $myrow['decimalplaces']);
	break;
}
$Demand = 0;
$DemResult = DB_query("SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
						FROM salesorderdetails INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
						WHERE salesorderdetails.completed=0
						AND salesorders.quotation=0
						AND salesorderdetails.stkcode='" . $StockID . "'", $db);
$DemRow = DB_fetch_row($DemResult);
$Demand = $DemRow[0];
$DemAsComponentResult = DB_query("SELECT  SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
										FROM salesorderdetails,
											salesorders,
											bom,
											stockmaster
										WHERE salesorderdetails.stkcode=bom.parent
										AND salesorders.orderno = salesorderdetails.orderno
										AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
										AND bom.component='" . $StockID . "'
										AND stockmaster.stockid=bom.parent
										AND stockmaster.mbflag='A'
										AND salesorders.quotation=0", $db);
$DemAsComponentRow = DB_fetch_row($DemAsComponentResult);
$Demand+= $DemAsComponentRow[0];
//Also the demand for the item as a component of works orders
$sql = "SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd)) AS woqtydemo
		FROM woitems INNER JOIN worequirements
		ON woitems.stockid=worequirements.parentstockid
		INNER JOIN workorders
		ON woitems.wo=workorders.wo
		AND woitems.wo=worequirements.wo
		WHERE  worequirements.stockid='" . $StockID . "'
		AND workorders.closed=0";
$ErrMsg = _('The workorder component demand for this product cannot be retrieved because');
$DemandResult = DB_query($sql, $db, $ErrMsg);
if (DB_num_rows($DemandResult) == 1) {
	$DemandRow = DB_fetch_row($DemandResult);
	$Demand+= $DemandRow[0];
}
echo '<tr><th class="number" width="15%">' . _('Quantity On Hand') . ':</th>
		<td width="17%" class="select">' . $QOH . '</td></tr>';
echo '<tr><th class="number" width="15%">' . _('Quantity Demand') . ':</th>
		<td width="17%" class="select">' . number_format($Demand, $myrow['decimalplaces']) . '</td></tr>';
echo '<tr><th class="number" width="15%">' . _('Quantity On Order') . ':</th>
		<td width="17%" class="select">' . $QOO . '</td></tr>
				</table>'; //end of nested table
echo '</td>'; //end cell of master table

if (($myrow['mbflag'] == 'B' OR ($myrow['mbflag'] == 'M')) 
	AND (in_array($SuppliersSecurity, $_SESSION['AllowedPageSecurityTokens']))){

	echo '<td width="50%" valign="top"><table>
			<tr><th width="50%">' . _('Supplier') . '</th>
				<th width="15%">' . _('Cost') . '</th>
				<th width="5%">' . _('Curr') . '</th>
				<th width="15%">' . _('Eff Date') . '</th>
				<th width="10%">' . _('Lead Time') . '</th>
                           <th width="10%">' . _('Min Order Qty') . '</th>
				<th width="5%">' . _('Prefer') . '</th></tr>';
	$SuppResult = DB_query("SELECT  suppliers.suppname,
									suppliers.currcode,
									suppliers.supplierid,
									purchdata.price,
									purchdata.effectivefrom,
									purchdata.leadtime,
									purchdata.conversionfactor,
									purchdata.minorderqty,
									purchdata.preferred,
									currencies.decimalplaces
								FROM purchdata INNER JOIN suppliers
								ON purchdata.supplierno=suppliers.supplierid 
								INNER JOIN currencies
								ON suppliers.currcode=currencies.currabrev
								WHERE purchdata.stockid = '" . $StockID . "'
							ORDER BY purchdata.preferred DESC, purchdata.effectivefrom DESC", $db);
	while ($SuppRow = DB_fetch_array($SuppResult)) {
		echo '<tr><td class="select">' . $SuppRow['suppname'] . '</td>
					<td class="select">' . number_format($SuppRow['price'] / $SuppRow['conversionfactor'], $SuppRow['decimalplaces']) . '</td>
					<td class="select">' . $SuppRow['currcode'] . '</td>
					<td class="select">' . ConvertSQLDate($SuppRow['effectivefrom']) . '</td>
					<td class="select">' . $SuppRow['leadtime'] . '</td>
					<td class="select">' . $SuppRow['minorderqty'] . '</td>';

		if ($SuppRow['preferred']==1) { //then this is the preferred supplier
			echo '<td class="select">' . _('Yes') . '</td>';
		} else {
			echo '<td class="select">' . _('No') . '</td>';
		}
		echo '<td class="select"><a href="' . $rootpath . '/PO_Header.php?NewOrder=Yes&SelectedSupplier=' .
			$SuppRow['supplierid'] . '&StockID=' . $StockID . '&Quantity='.$SuppRow['minorderqty'].'">' . _('Order') . ' </a></td>';
		echo '</tr>';
	}
	echo '</table></td>';
	DB_data_seek($result, 0);
}
echo '</td></tr></table><br />'; // end first item details table
echo '<table width="90%"><tr>
		<th width="33%">' . _('Item Inquiries') . '</th>
		<th width="33%">' . _('Item Transactions') . '</th>
		<th width="33%">' . _('Item Maintenance') . '</th>
	</tr>';
echo '<tr><td valign="top" class="select">';
/*Stock Inquiry Options */
echo '<a href="' . $rootpath . '/StockMovements.php?StockID=' . $StockID . '">' . _('Show Stock Movements') . '</a><br />';
if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
	echo '<a href="' . $rootpath . '/StockStatus.php?StockID=' . $StockID . '">' . _('Show Stock Status') . '</a><br />';
	echo '<a href="' . $rootpath . '/StockUsage.php?StockID=' . $StockID . '">' . _('Show Stock Usage') . '</a><br />';
}
echo '<a href="' . $rootpath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Sales Orders') . '</a><br />';
echo '<a href="' . $rootpath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a><br />';
if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
	echo '<a href="' . $rootpath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Purchase Orders') . '</a><br />';
	echo '<a href="' . $rootpath . '/PO_SelectPurchOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search All Purchase Orders') . '</a><br />';
	echo '<a href="' . $rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg">' . _('Show Part Picture (if available)') . '</a><br />';
}
if ($Its_A_Dummy == False) {
	echo '<a href="' . $rootpath . '/BOMInquiry.php?StockID=' . $StockID . '">' . _('View Costed Bill Of Material') . '</a><br />';
	echo '<a href="' . $rootpath . '/WhereUsedInquiry.php?StockID=' . $StockID . '">' . _('Where This Item Is Used') . '</a><br />';
}
if ($Its_A_Labour_Item == True) {
	echo '<a href="' . $rootpath . '/WhereUsedInquiry.php?StockID=' . $StockID . '">' . _('Where This Labour Item Is Used') . '</a><br />';
}
wikiLink('Product', $StockID);
echo '</td><td valign="top" class="select">';
/* Stock Transactions */
if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
	echo '<a href="' . $rootpath . '/StockAdjustments.php?StockID=' . $StockID . '">' . _('Quantity Adjustments') . '</a><br />';
	echo '<a href="' . $rootpath . '/StockTransfers.php?StockID=' . $StockID . '">' . _('Location Transfers') . '</a><br />';
	//show the item image if it has been uploaded
	if( isset($StockID) and file_exists($_SESSION['part_pics_dir'] . '/' .$StockID.'.jpg') ) {
		echo '<div class="centre"><img src="GetStockImage.php?automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . $StockID . '&text=&width=120&height=120">';
	}
	if (($myrow['mbflag'] == 'B') AND (in_array($SuppliersSecurity, $_SESSION['AllowedPageSecurityTokens']))){
		echo '<br />';
		$SuppResult = DB_query("SELECT suppliers.suppname,
										suppliers.supplierid,
										purchdata.preferred,
										purchdata.minorderqty,
										purchdata.leadtime
									FROM purchdata INNER JOIN suppliers
									ON purchdata.supplierno=suppliers.supplierid
									WHERE purchdata.stockid = '" . $StockID . "'", $db);
		while ($SuppRow = DB_fetch_array($SuppResult)) {
			if (($myrow['eoq'] < $SuppRow['minorderqty'])) {
				$EOQ = $SuppRow['minorderqty'];
			} else {
				$EOQ = $myrow['eoq'];
			}
			echo '<a href="' . $rootpath . '/PO_Header.php?NewOrder=Yes' . '&SelectedSupplier=' . $SuppRow['supplierid'] . '&StockID=' . $StockID . '&Quantity='.$EOQ.'&LeadTime='.$SuppRow['leadtime'].'">'. _('Purchase this Item from') . ' ' . $SuppRow['suppname'] . '</a>
			<br />';
			
			/**/
		} /* end of while */
	} /* end of $myrow['mbflag'] == 'B' */
} /* end of ($Its_A_Kitset_Assembly_Or_Dummy == False) */
echo '</td><td valign="top" class="select">';
/* Stock Maintenance Options */
echo '<a href="' . $rootpath . '/Stocks.php?">' . _('Add Inventory Items') . '</a><br />';
echo '<a href="' . $rootpath . '/Stocks.php?StockID=' . $StockID . '">' . _('Modify Item Details') . '</a><br />';
if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
	echo '<a href="' . $rootpath . '/StockReorderLevel.php?StockID=' . $StockID . '">' . _('Maintain Reorder Levels') . '</a><br />';
	echo '<a href="' . $rootpath . '/StockCostUpdate.php?StockID=' . $StockID . '">' . _('Maintain Standard Cost') . '</a><br />';
	echo '<a href="' . $rootpath . '/PurchData.php?StockID=' . $StockID . '">' . _('Maintain Purchasing Data') . '</a><br />';
}
if ($Its_A_Labour_Item == True) {
	echo '<a href="' . $rootpath . '/StockCostUpdate.php?StockID=' . $StockID . '">' . _('Maintain Standard Cost') . '</a><br />';
}
if (!$Its_A_Kitset) {
	echo '<a href="' . $rootpath . '/Prices.php?Item=' . $StockID . '">' . _('Maintain Pricing') . '</a><br />';
	if (isset($_SESSION['CustomerID']) AND $_SESSION['CustomerID'] != "" AND Strlen($_SESSION['CustomerID']) > 0) {
		echo '<a href="' . $rootpath . '/Prices_Customer.php?Item=' . $StockID . '">' . _('Special Prices for customer') . ' - ' . $_SESSION['CustomerID'] . '</a><br />';
	}
	echo '<a href="' . $rootpath . '/DiscountCategories.php?StockID=' . $StockID . '">' . _('Maintain Discount Category') . '</a><br />';
}
echo '</td></tr></table>';
} else {
	// options (links) to pages. This requires stock id also to be passed.
	echo '<table width="90%" colspan="2" cellpadding="4">';
	echo '<tr>
		<th width="33%">' . _('Item Inquiries') . '</th>
		<th width="33%">' . _('Item Transactions') . '</th>
		<th width="33%">' . _('Item Maintenance') . '</th>
	</tr>';
	echo '<tr><td class="select">';
	/*Stock Inquiry Options */
	echo '</td><td class="select">';
	/* Stock Transactions */
	echo '</td><td class="select">';
	/*Stock Maintenance Options */
	echo '<a href="' . $rootpath . '/Stocks.php?">' . _('Add Inventory Items') . '</a><br />';
	echo '</td></tr></table>';
} // end displaying item options if there is one and only one record
echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Inventory Items'). '</p>';
echo '<table class="selection"><tr>';
echo '<td>' . _('In Stock Category') . ':';
echo '<select name="StockCat">';
if (!isset($_POST['StockCat'])) {
	$_POST['StockCat'] = "";
}
if ($_POST['StockCat'] == 'All') {
	echo '<option selected="True" value="All">' . _('All').'</option>';
} else {
	echo '<option value="All">' . _('All').'</option>';
}
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected="True" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'].'</option>';
	} else {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'].'</option>';
	}
}
echo '</select></td>';
echo '<td>' . _('Enter partial') . '<b> ' . _('Description') . '</b>:</td><td>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
} else {
	echo '<input type="text" name="Keywords" size="20" maxlength="25" />';
}
echo '</td></tr><tr><td></td>';
echo '<td><font size="3"><b>' . _('OR') . ' ' . '</b></font>' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</td>';
echo '<td>';
if (isset($_POST['StockCode'])) {
	echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" />';
} else {
	echo '<input type="text" name="StockCode" size="15" maxlength="18" />';
}
echo '</td></tr></table><br />';
echo '<div class="centre"><input type="submit" name="Search" value="' . _('Search Now') . '" /></div><br />';
echo '<script  type="text/javascript">defaultControl(document.forms[0].StockCode);</script>';
echo '</form>';
// query for list of record(s)
if(isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	$_POST['Search']='Search';
}
if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg (_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							SUM(locstock.quantity) AS qoh,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.decimalplaces
						FROM stockmaster
						LEFT JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid,
							locstock
						WHERE stockmaster.stockid=locstock.stockid
						AND stockmaster.description " . LIKE . " '$SearchString'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.decimalplaces
						ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							SUM(locstock.quantity) AS qoh,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.decimalplaces
						FROM stockmaster,
							locstock
						WHERE stockmaster.stockid=locstock.stockid
						AND description " . LIKE . " '$SearchString'
						AND categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.decimalplaces
						ORDER BY stockmaster.stockid";
		}
	} elseif (isset($_POST['StockCode'])) {
		$_POST['StockCode'] = strtoupper($_POST['StockCode']);
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.mbflag,
							SUM(locstock.quantity) AS qoh,
							stockmaster.units,
							stockmaster.decimalplaces
						FROM stockmaster
						INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid,
							locstock
						WHERE stockmaster.stockid=locstock.stockid
						AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.decimalplaces
						ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.mbflag,
						sum(locstock.quantity) as qoh,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
					AND categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.decimalplaces
					ORDER BY stockmaster.stockid";
		}
	} elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.mbflag,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster
					LEFT JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.decimalplaces
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.mbflag,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.decimalplaces
					ORDER BY stockmaster.stockid";
		}
	}
	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL that returned an error was');
	$searchresult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
	if (DB_num_rows($searchresult) == 0) {
		prnMsg(_('No stock items were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_POST['Search']);
}
/* end query for list of records */
/* display list if there is more than one record */
if (isset($searchresult) AND !isset($_POST['Select'])) {
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$ListCount = DB_num_rows($searchresult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
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
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre"><p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
				<input type="submit" name="Go" value="' . _('Go') . '">
				<input type="submit" name="Previous" value="' . _('Previous') . '">
				<input type="submit" name="Next" value="' . _('Next') . '">';
			echo '<input type="hidden" name=Keywords value="'.$_POST['Keywords'].'">';
			echo '<input type="hidden" name=StockCat value="'.$_POST['StockCat'].'">';
			echo '<input type="hidden" name=StockCode value="'.$_POST['StockCode'].'">';
//			echo '<input type=hidden name=Search value="Search">';
			echo '<p></div>';
		}
		echo '<table cellpadding="2" colspan="7">';
		$tableheader = '<tr>
					<th>' . _('Code') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Total Qty On Hand') . '</th>
					<th>' . _('Units') . '</th>
					<th>' . _('Stock Status') . '</th>
				</tr>';
		echo $tableheader;
		$j = 1;
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
		if (DB_num_rows($searchresult) <> 0) {
			DB_data_seek($searchresult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($myrow = DB_fetch_array($searchresult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			if ($myrow['mbflag'] == 'D') {
				$qoh = _('N/A');
			} else {
				$qoh = number_format($myrow['qoh'], $myrow['decimalplaces']);
			}
			echo '<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
				<td>'.$myrow['description'].'</td>
				<td class="number">' . $qoh . '</td>
				<td>' . $myrow['units'] . '</td>
				<td><a target="_blank" href="' . $rootpath . '/StockStatus.php?StockID=' . $myrow['stockid'].'">' . _('View') . '</a></td>
				</tr>';
			$j++;
			if ($j == 20 AND ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $tableheader;
			}
			$RowIndex = $RowIndex + 1;
			//end of page full new headings if
		}
		//end of while loop
		echo '</table></form><br />';
	}
}
/* end display list if there is more than one record */
include ('includes/footer.inc');
?>