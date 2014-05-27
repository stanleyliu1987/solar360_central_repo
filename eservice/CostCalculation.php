<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php
/*   <document_root>/MyWebSite/CostCalculation.php
     Sample application invoking calculateCost, a Star Track Express eService operation
     Star Track Express
     25 May 2011
     Version 4.3 
*/
require_once("eServices.php");					// Import the Star Track Express PHP API - do not modify this file
require_once("CustomerConnect.php");			// Import ConnectDetails class - customer to modify this file as required
?>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Cost Calculation Sample</title>

<script language="javascript">	
// Globals
<?php
// Populate parallel arrays containing all suburbs, postcodes and states

	// Initiate target arrays for suburb, postcode, state
//	$suburbs = "var suburbs = new Array(";
//	$postCodes = "var postCodes = new Array(";
//	$states = "var states = new Array(";
//	$oC = new STEeService();
//	$addresses = $oC->suburbs();					// Get associative array mapping suburb to postcode and state
//	$q = "\"";										// Quote character
//	// Step through associative array, appending elements to target arrays
//	foreach ($addresses as $suburb => $params)		
//	{
//		$suburbs .= $q . $suburb . $q . ",";
//		$postCodes .= $q . $params[0] . $q . ",";
//		$states .= $q . $params[1] . $q . ",";
//	}
//	// Remove last commma
//	$suburbs = substr($suburbs, 0, strlen($suburbs)-1);
//	$postCodes = substr($postCodes, 0, strlen($postCodes)-1);
//	$states = substr($states, 0, strlen($states)-1);
//	
//	// Terminate arrays
//	echo $suburbs . ");\n";
//	echo $postCodes . ");\n";
//	echo $states . ");\n";
?>

// Functions
function partialSuburb(partSuburb, suburbsArr)
// Returns an array of indexes of any and all suburbs of which partSuburb is the initial part, or false if no match
// suburbsArr = array of all suburbs in alphabetical order
{
	var matches = [];
	var ucPartialSuburb = partSuburb.toUpperCase();
	for (var i =0; i < suburbsArr.length; i++)
	{
		if (suburbsArr[i].indexOf(ucPartialSuburb) != 0) continue;
		for (var j = i; j< suburbsArr.length; j++)
		{
			if (suburbsArr[j].indexOf(ucPartialSuburb) != 0) break;
			matches.push(j);
		}
		return matches;
		break;
	}
		return false;								// No match
}

function fullSuburb(srb, suburbsArr)
// Returns array index if srb exactly matches an element of suburbsArr
// otherwise returns false
{
	ucSuburb = srb.toUpperCase();
	for (var i = 0; i < suburbsArr.length; i++)
	{
		if(ucSuburb == suburbsArr[i]) return i;
	}
	return false;
}

// DOM utility functions
function hideElement(elt)
// Hides an element
{
	elt.style.display = 'none';
}

function showElement(elt)
// Shows an element
{
	elt.style.display = 'inline';
}

function appendOptionList(selElt,textArray, valueArray)
// Appends a list of OPTIONS to a SELECT element
// selElt = SELECT element
// textArray = array of OPTION text values
// valueArray = parallel array of OPTION values
{
	var i;
	for (i=0; i<textArray.length; i++)
	{
		var opt = document.createElement('option');
		opt.text = textArray[i];
		opt.value = valueArray[i];

		// Append OPTION to list of OPTIONS
		selElt.add(opt, null);
	}
}

function removeOptions(selElt)
// Removes all OPTION elements from a SELECT element
// selElt = SELECT element
{
	while (selElt.length > 0) selElt.remove(0);
}

// Event handlers
function afterLoad()
// Here when page loaded
// Hides suburb selection listboxes
{
	// Define globals for sender elements
	suburbSender = document.getElementsByName("suburbSender").item(0);
	selectionSender = document.getElementById("selectionSender");
	postCodeSender = document.getElementsByName("postCodeSender").item(0);
	stateSender = document.getElementsByName("stateSender").item(0);
	
	// Define globals for receiver elements
	suburbReceiver = document.getElementsByName("suburbReceiver").item(0);
	selectionReceiver = document.getElementById("selectionReceiver");
	postCodeReceiver = document.getElementsByName("postCodeReceiver").item(0);
	stateReceiver = document.getElementsByName("stateReceiver").item(0);
	
	// Hide selection listboxes
	hideElement(selectionSender); 
	hideElement(selectionReceiver);
        
        initial();
}

function changed(suburb, selection, pc, state)
// Common event handler for change on sender or receiver suburb, e.g. keystroke
// suburb = sender or receiver suburb element
// selection = sender or receiver selection element
// Uses global variable suburbs = array of full suburb names in alphabetical order
{
	pc.value = "";											// In case user changed previous selection
	state.value = "";
	if (suburb.value.length < 3)
	{
		// clear OPTIONS list, hide it and return
		removeOptions(selection);
		hideElement(selection);
		return;
	}
	var matches = partialSuburb(suburb.value, suburbs);		// Get array of suburb indexes for matching suburbs
	if (!matches)											// If there were no matches
	{
		removeOptions(selection);
		hideElement(selection);
		
		return;
	}
	// Get array of suburb names
	var textArr = [];
	for (var k = 0; k < matches.length; k++)	
	{
		textArr.push(suburbs[matches[k]]);
	}
	// Refresh OPTIONS list
	removeOptions(selection);
	appendOptionList(selection, textArr, matches);
	showElement(selection);
}

function blurred(suburb, selection, pc, state)
// Common event handler for leaving sender or receiver suburb
// suburb = suburb element
// pc = postcode element
// state = state element
// Uses global variable suburbs
{
	if(suburb.value == "") return;					// Empty suburb
	var idx = fullSuburb(suburb.value, suburbs);	// Get index of suburb in suburbs array
	if (!idx) return;								// No match
	suburb.value = suburbs[idx];					// Set suburb
	pc.value = postCodes[idx];						// Set postcode
	state.value = states[idx];						// Set state
	removeOptions(selection);						// Clear OPTIONS list
	hideElement(selection);							// Hide the listbox
}

function selected(suburb, selection, pc, state)
// Common event handler for click on sender/receiver suburb option in listbox OR
// user leaving sender/receiver suburb
// suburb = sender or receiver suburb element
// selection = sender or receiver selection element
// pc = postcode element for the suburb
// state = state element for the suburb
// Uses global variables suburbs, postCodes and states
{
	var idx = selection.value;					// Array index for the suburb
	suburb.value = suburbs[idx];				// Set suburb
	pc.value = postCodes[idx];					// Set postcode
	state.value = states[idx];					// Set state
	removeOptions(selection);					// Clear OPTIONS list
	hideElement(selection);						// Hide the listbox
}

function senderSuburbKeyUp()
// Here on key up for sender suburb
{
	changed(suburbSender, selectionSender, postCodeSender, stateSender);
}

function receiverSuburbKeyUp()
// Here on keyup for receiver suburb
{
	changed(suburbReceiver, selectionReceiver, postCodeReceiver, stateReceiver);
}

function senderSuburbBlur()
// Here when user leaves sender suburb
{
	blurred(suburbSender, selectionSender, postCodeSender, stateSender);
}

function receiverSuburbBlur()
// Here when user leaves receiver suburb
{
	blurred(suburbReceiver, selectionReceiver, postCodeReceiver, stateReceiver);
}

function senderSelected()
// Here when a sender suburb is selected from listbox
{
	selected(suburbSender, selectionSender, postCodeSender, stateSender);
}

function receiverSelected()
// Here when a receiver suburb is selected from listbox
{
	selected(suburbReceiver, selectionReceiver, postCodeReceiver, stateReceiver);
}
</script>

</head>

<body onload="afterLoad();">
<p>To obtain a cost estimate for a freight item, complete the following details:</p>

<form id= "form1" action="EnquireCost.php" method="post">
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
  <tr>
    <th scope="row" align="left">&nbsp;</th>
    <td align="left">&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">Account No&nbsp;</th>
    <td align="left"><input type="text" name="accountNo" />&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">Service Code&nbsp;</th>
    <td align="left"><input type="text" name="serviceCode" />&nbsp;</td>
  </tr>
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
    <th scope="row" align="left">Include Risk Warranty?&nbsp;</th>
    <td align="left"><input id="includeRiskWarranty" type="text" name="includeRiskWarranty" />&nbsp;</td>
  </tr>
  <tr>
    <th scope="row" align="left">Risk Warranty Value&nbsp;</th>
    <td align="left"><input type="text" name="riskWarrantyValue" />&nbsp;</td>
  </tr>
</table>
<br />
<input type="submit" value="Get Cost Estimate" />
</form>

</body>
</html>