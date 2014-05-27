<?php
/* $Id: PDFLowGPPageHeader.inc 3686 2010-08-07 16:27:37Z tim_schofield $*/
/*PDF page header for inventory valuation report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

$YPos -=$line_height;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('Low GP Sales Between') . ' ' . $_POST['FromDate'] . ' ' . _('and') . ' ' . $_POST['ToDate'] . ' ' . _('less than') . ' ' . $_POST['GPMin'] . '%');
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,_('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '    ' . _('Page') . ' ' . $PageNumber);

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $Bottom_Margin);
$pdf->line($Left_Margin, $Bottom_Margin, $Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $Bottom_Margin);


$pdf->line(80, $YPos+$line_height, 80, $Bottom_Margin);
$pdf->line(218, $YPos+$line_height, 218, $Bottom_Margin);
$pdf->line(332, $YPos+$line_height, 332, $Bottom_Margin);
$pdf->line(388, $YPos+$line_height, 388, $Bottom_Margin);
$pdf->line(448, $YPos+$line_height, 448, $Bottom_Margin);
$pdf->line(503, $YPos+$line_height, 503, $Bottom_Margin);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,100-$Left_Margin,$FontSize,_('Invoice'), 'centre');
$LeftOvers = $pdf->addTextWrap(85,$YPos,50,$FontSize,_('Date/Item/Unit'), 'centre');

$LeftOvers = $pdf->addTextWrap(220,$YPos,130,$FontSize,_('Customer'), 'centre');
$LeftOvers = $pdf->addTextWrap(330,$YPos,50,$FontSize,_('Sell Price'), 'right');
$LeftOvers = $pdf->addTextWrap(380,$YPos,62,$FontSize,_('Cost'), 'right');
$LeftOvers = $pdf->addTextWrap(440,$YPos,60,$FontSize,_('GP'), 'right');
$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,_('GP') . ' %', 'right');

$FontSize=8;
$YPos =$YPos - (2*$line_height);

$PageNumber++;

?>