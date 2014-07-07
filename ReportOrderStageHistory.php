<?php

include('includes/session.inc');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


/* 17062014 Search Order Stage Message History by Stan */
$OrderStages = new OrderStagesModel($db);
$EmailLog=new EmailAuditLogModel($db);
$OrderStageMessage = new OrderStagesMessageModel($db);
$OrderStageMessagResult = $OrderStageMessage->SelectAllOrderStagesMessage($_GET['transfk']);
$EmailAuditLogResult = $EmailLog->SelectEmailAuditLogByOrderNumber($_GET['orderno']);
$StatusArray=array();
/* End of Order Stage Message Search */

/* Display Order Stage History Reports */
echo '<form><table><tr>';
echo '<div class="page_help_text">' . _('Order Stage History Report') . ' '.$_GET['invoiceno'].'</div><br />';
echo '<th>' . _('Order Stage') . '</th>
                                <th>' . _('Modified by') . '</th>
				<th>' . _('Modified date') . '</th>
		                </tr>';
/* Retrieve Order stage message*/
while ($myrow=DB_fetch_array($OrderStageMessagResult)){
    $StatusArray[]= array('Status'=>$OrderStages->SelectOrderStagesNameById($myrow['order_stage_change']),'UserID'=>$myrow['userid'],'ChangeDate'=>$myrow['changedatetime']);
    //echo '<tr><td>'.$OrderStages->SelectOrderStagesNameById($myrow['order_stage_change']).'</td><td>'.$myrow['userid'].'</td><td>'.$myrow['changedatetime'].'</td></tr>';
}
/* Retrieve Email audit log message*/
while ($myrow=  DB_fetch_array($EmailAuditLogResult)){
    $Emailtype = $EmailLog->RetrieveEmailAuditLogType($myrow['emailtemplateid']);
    $StatusArray[]= array('Status'=>$Emailtype." Email",'UserID'=>$myrow['userid'],'ChangeDate'=>$myrow['senddate']);
}
usort($StatusArray, function($a, $b) {return strtotime($b['ChangeDate']) - strtotime($a['ChangeDate']);});
/* Output the result of Status Array */
foreach($StatusArray as $st){ 
    echo '<tr><td>'.$st['Status'].'</td><td>'.$st['UserID'].'</td><td>'.$st['ChangeDate'].'</td></tr>';
}
echo '</table></form>';

include('includes/footer.inc');
?>
