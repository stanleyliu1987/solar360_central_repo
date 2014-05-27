
<?php
/*   <document_root>/MyWebSite/CostETACalculation.php
     Sample application invoking calculateCost, a Star Track Express eService operation
     Star Track Express
     25 May 2011
     Version 4.3 
*/
require_once('ESsession.inc');
require_once("eServices.php");					// Import the Star Track Express PHP API - do not modify this file
require_once("CustomerConnect.php");			// Import ConnectDetails class - customer to modify this file as required
require_once("header.inc");

session_start();
?>

<body onload="afterLoad();">
<table width="600" border="0">
  <tr>
    <th align="left">Sender Location&nbsp;</th>
    <th align="left">Postcode&nbsp;</th>
    <th align="left">State&nbsp;</th>
  </tr>
  <tr>
    <td align="left"><input type="text" name="suburbSender" autocomplete="off" onkeyup="senderSuburbKeyUp();" onblur="senderSuburbBlur();"  value="<?php echo $_SESSION['Items'.$identifier]->DelAdd2;?>" />&nbsp;</td>
    <td align="left"><input type="text" name="postCodeSender"  value="<?php echo $_SESSION['Items'.$identifier]->DelAdd4;?>"  />&nbsp;</td>
    <td align="left"><input type="text" name="stateSender"  value="<?php echo $_SESSION['Items'.$identifier]->DelAdd3;?>"  />&nbsp;</td>
  </tr>
  <tr>
        <td align="left"><select id="selectionSender" size="10" onclick="senderSelected();" />&nbsp;</td>
  </tr>
 
 
</table>
</body>
<?php
require_once("footer.inc");
?>