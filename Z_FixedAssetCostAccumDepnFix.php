<?php
/* $Id: $*/
/* Script to make cost and accumdepn = sum of transaction cost and accumdepn */


//$PageSecurity=15;
include ('includes/session.inc');
$title = _('Fixed Fixed Asset Records');
include('includes/header.inc');


echo '<br><br>' . _('This script repairs the fixedasset table cost and accum depn');

$AssetCostDepnResult = DB_query("SELECT assetid, SUM(CASE WHEN fixedassettranstype='cost' THEN amount ELSE 0 END) as cost, SUM(CASE WHEN fixedassettranstype='depn' THEN amount ELSE 0 END) AS accumdepn FROM fixedassettrans GROUP BY assetid", $db);
	
while ($AssetRow = DB_fetch_array($AssetCostDepnResult)) {
	$result = DB_query("UPDATE fixedassets SET cost='" . $AssetRow['cost'] . "', accumdepn='" . $AssetRow['accumdepn'] . "' WHERE assetid='" . $AssetRow['assetid'] . "'",$db);
	prnMsg(_('Updated asset') . ' ' . $AssetRow['assetid'] . ' ' . _('to have a cost of') . ' ' . $AssetRow['cost'] . ' ' . _('and accum depn of') . ' ' . $AssetRow['accumdepn'],'info');
}

echo '<p>';
prnMsg(_('Asset cost and accumulated depreciation has now been fixed to the transaction cost and depreciation'),'info');

include('includes/footer.inc');
?>
