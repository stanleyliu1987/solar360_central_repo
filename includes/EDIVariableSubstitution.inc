<?php
/* $Id: EDIVariableSubstitution.inc 3242 2009-12-16 22:06:53Z tim_schofield $*/
/*Code to replace [Variables] in Message LineText with the value of the variable itself */

	$PositionPointer = 0;
	$NewLineText ='';
	/* now get each occurence of [ in the line */
	while (strpos ($MsgLineText,'[',$PositionPointer)!=False){
		$LastPositionPointer = $PositionPointer;
		$PositionPointer = strpos ($MsgLineText,'[',$PositionPointer);

		$NewLineText = $NewLineText .  substr($MsgLineText,$LastPositionPointer,$PositionPointer-$LastPositionPointer);

		$LastPositionPointer = $PositionPointer;
		$PositionPointer = strpos ($MsgLineText,']',$PositionPointer);

		$VariableName = substr($MsgLineText,$LastPositionPointer+1,$PositionPointer-$LastPositionPointer-1);

		$PositionPointer++;

		echo '<BR>' . _('The variable') . ' - ' . $VariableName . ' ' . _('is set to') . ' ' . $$VariableName;

		$NewLineText = $NewLineText . $$VariableName;

	}
	/* now add the text from the last ] to the end of the line */
	$LastPositionPointer = $PositionPointer;
	$NewLineText = $NewLineText .  substr($MsgLineText,$LastPositionPointer);

	echo "<BR>$NewLineText";

	fputs($fp, $NewLineText . "\n");
?>