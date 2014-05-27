
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


?>

<body onload="afterLoad();">
<p>To obtain a cost estimate for a freight item, complete the following details:</p>

<form id= "form1" action="EnquireCostETA.php" method="post">
<table width="600" border="0">
  <tr>
    <th align="left">Sender Location&nbsp;</th>
    <th align="left">Postcode&nbsp;</th>
    <th align="left">State&nbsp;</th>
  </tr>
  <tr>
    <td align="left"><input type="text" name="suburbSender" autocomplete="off" onkeyup="senderSuburbKeyUp();" onblur="senderSuburbBlur();" />&nbsp;</td>
    <td align="left"><input type="text" name="postCodeSender" disabled />&nbsp;</td>
    <td align="left"><input type="text" name="stateSender" disabled />&nbsp;</td>
  </tr>
  <tr>
        <td align="left"><select id="selectionSender" size="10" onclick="senderSelected();" />&nbsp;</td>
  </tr>
  <tr>
    <th align="left">Receiver Location&nbsp;</th>
    <th align="left">Postcode&nbsp;</th>
    <th align="left">State&nbsp;</th>
  </tr>
  <tr>
   <td align="left"><input type="text" name="suburbReceiver" autocomplete="off" onkeyup="receiverSuburbKeyUp();" onblur="receiverSuburbBlur();" />&nbsp;</td>
   <td align="left"><input type="text" name="postCodeReceiver" disabled />&nbsp;</td>
   <td align="left"><input type="text" name="stateReceiver" disabled />&nbsp;</td>
  </tr>
  <tr>
        <td align="left"><select id="selectionReceiver" size="10" onclick="receiverSelected();" />&nbsp;</td>
  </tr>
 
  <!--<tr>
    <th scope="row" align="left">Account No&nbsp;</th>
    <td align="left"><input type="text" name="accountNo" />&nbsp;</td>
  </tr>-->
  <!--<tr>
    <th scope="row" align="left">Service Code&nbsp;</th>
    <td align="left"><input type="hidden" name="serviceCode" value="EXP"/>&nbsp;</td>
  </tr>-->
    <!--Account number and service Code -->
    <input type="hidden" name="accountNo" value="10128362"/>
    <input type="hidden" name="serviceCode" value="EXP"/>
  <tr>
    <th scope="row" align="left">Item Count&nbsp;</th>
    <td align="left"><input type="text" name="noOfItems" />&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">Weight (kg)&nbsp;</th>
    <td align="left"><input type="text" name="weight" />&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">Volume (m3)&nbsp;</th>
    <td align="left"><input type="text" name="volume" />&nbsp;</td>
  </tr>
 <tr>
    <th scope="row" align="left">Despatch Date&nbsp;</th>
    <td align="left"><input type="text" class="date" name="despatchDate" value="2011-06-25" alt="Y-m-d"/>&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">Include Risk Warranty?&nbsp;</th>
    <td align="left"><input id="includeRiskWarranty" type="checkbox" name="includeRiskWarranty" value="y"  onclick="showMe('whatever', this)"> &nbsp;<br></td>
  </tr>
 
</table>
     <div id="whatever" style="display:none" align="center"> 
      <h4>Risk Warranty Value: &nbsp;
          <input type="text" name="riskWarrantyValue" />
      </h4>
     </div>
<br />
<div align="center">
<input type="submit" value="Get Cost and ETA Estimate" />
</div>
</form>
</body>
<?php
require_once("footer.inc");
?>