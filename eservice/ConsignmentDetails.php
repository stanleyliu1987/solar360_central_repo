<?php 
echo '<p>To obtain details on one or more consignments, complete the following:</p>';

echo '<form action="eservice/EnquireConsignmentDetails.php" method="post">
<table width="1000" border="0">
  <tr>
  <th scope="row" align="left">Account No&nbsp;</th>
  <td align="left"><input type="text" name="accountNo" />&nbsp;</td>
  </tr>
  <tr>
  <th scope="row" align="left">Consignment IDs (separated by spaces)&nbsp;</th>
  <td align="left"><input type="text" name="consignmentId" />&nbsp;</td>
  </tr>
</table>
<br />
<input type="submit" value="Get Consignment Details" />
</form>';

    ?>