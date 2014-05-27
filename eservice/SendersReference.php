<?php 


echo '<form id="condetail" action="eservice/EnquireConsignmentDetails.php" method="post" target="form1">
 <p>To obtain details on one or more consignments, complete the following:
 <input type="hidden" name="accountNo" value="10128362"/>&nbsp;</p><br/>
 <table width="800" border="0">
  <tr>
  <th scope="row" align="left">Consignment IDs (separated by spaces)&nbsp;</th>
  <th align="left"><input type="text" name="consignmentId" />&nbsp;</th>
  <th><input type="button" value="Search By ID" onclick="popupConsignmentNotewindow(\'' . _('con') . '\')"/></th>
  </tr>
</table>
<input type=hidden name=theme value='.$theme.'>
<input type=hidden name=rootpath value='.$rootpath.'>
<br /><p></p>

</form>';

echo '<form id="refdetail" action="eservice/EnquireSendersReference.php" method="post" target="form2">
      <input type="hidden" name="accountNo" value="10128362"/>&nbsp;</p><br/>
  <table width="800" border="0">
  <tr>
  <th scope="row" align="left">Senders References (separated by spaces)&nbsp;</th>
  <th align="left"><input type="text" name="senderReferenceNumber" />&nbsp;</th>
  <th scope="row" align="left">Despatch Location Codes (separated by spaces)&nbsp;</th>
  <th align="left"><input type="text" name="despatchLocationCode" />&nbsp;</th>
  <th><input type="button" value="Search By Reference" onclick="popupConsignmentNotewindow(\'' . _('ref') . '\')"/></th>
  </tr>
  </table>
<input type=hidden name=theme value='.$theme.'>
<input type=hidden name=rootpath value='.$rootpath.'>
  <br /><p></p>

</form>';




    ?>