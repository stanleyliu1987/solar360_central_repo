<?php

include('includes/session.inc');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


/* 17062014 Search Order Stage Message History by Stan */
$OrderStages = new OrderStagesModel($db);
$OrderStageMessage = new OrderStagesMessageModel($db);
$OrderStageMessagResult = $OrderStageMessage->SelectAllOrderStagesMessage($_GET['transfk']);
/* End of Order Stage Message Search */

/* Display Order Stage History Reports */
echo '<form><table><tr>';
echo '<div class="page_help_text">' . _('Order Stage History Report') . ' '.$_GET['invoiceno'].'</div><br />';
echo '<th>' . _('Order Stage') . '</th>
                                <th>' . _('Modified by') . '</th>
				<th>' . _('Modified date') . '</th>
		                </tr>';
while ($myrow=DB_fetch_array($OrderStageMessagResult)){
    echo '<tr><td>'.$OrderStages->SelectOrderStagesNameById($myrow['order_stage_change']).'</td><td>'.$myrow['userid'].'</td><td>'.$myrow['changedatetime'].'</td></tr>';
}
echo '</table>';
echo '</form>';
include('includes/footer.inc');
?>
