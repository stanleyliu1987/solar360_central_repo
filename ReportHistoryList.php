<?php
include('includes/session.inc');
include('includes/DefineInvCartClass.php');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['transno']) AND $_GET['transno']!=''){
    
 $_SESSION['Items'.$identifier] = new cart;
/*Search Invoice History */
 $LineItemsSQL = "SELECT  mod_stockmoves.stkmoveno,
                                                mod_stockmoves.stockid,
					        stockmaster.description,
						-mod_stockmoves.qty AS quantity,
						mod_stockmoves.discountpercent,
                                                mod_stockmoves.trandate,
						mod_stockmoves.price,
                                                mod_stockmoves.discountpercent,
                                                mod_stockmoves.standardcost,
						mod_stockmoves.narrative,
						stockmaster.controlled,
						stockmaster.units,
						mod_stockmoves.stkmoveno,
                                                mod_stockmoves.debtranversion,
                                                mod_debtortrans.inputdate
					FROM mod_stockmoves inner join stockmaster on mod_stockmoves.stockid = stockmaster.stockid
                                                            inner join mod_debtortrans on mod_stockmoves.debtranversion=mod_debtortrans.id
					WHERE  mod_stockmoves.type=10
					AND mod_stockmoves.transno='" . $_GET['transno'] . "'
					AND mod_stockmoves.show_on_inv_crds=1
                                        order by debtranversion desc";
               

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg);
     
		if (db_num_rows($LineItemsResult)>0) {

			while ($myrow=db_fetch_array($LineItemsResult)) {
        
						$_SESSION['Items'.$identifier]->add_to_cart($myrow['stockid'],
											    $myrow['quantity'],
											    $myrow['description'],
                                                                                            $myrow['price'],
                                                                                            $myrow['discountpercent'],
                                                                                            $myrow['debtranversion'],
                                                     '','','','',$myrow['inputdate'],'','','','','','','No',-1,'','','','',$myrow['standardcost'],'','',''  );

					$LastLineNo = $myrow['orderlineno'];
			} /* line items from sales order details */
			 $_SESSION['Items'.$identifier]->LineCounter = $LastLineNo+1;
		}
                else{
                    echo 'There is no Invoice History Report'.'<br>';
                }
/*End of Invoice Search */
                
/*Display Invoice History Reports */
                echo '<table><tr>';
		echo '<div class="page_help_text">' . _('Invoices History Report') . '</div><br />';
		echo '<th>' . _('Transaction Group') . '</th>
                                <th>' . _('Modification Date') . '</th>
                                <th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Price') . '</th>
                                <th>' . _('Discount %') . '</th>
                                <th>' . _('Total Amount exc Tax') . '</th>
		                </tr>';

            $TempgroupId=0;     
            foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
 		        $LineTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
			$DisplayLineTotal = number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
			$DisplayDiscount = number_format(($OrderLine->DiscountPercent * 100),2);
                        if($TempgroupId!=$OrderLine->Stockmoveid and $TempgroupId!=0){
                        echo '<tr><td colspan="8"><hr></td></tr>';
                        }
                    
			echo '<tr class="OddTableRows">
                              <td>' . $OrderLine->Stockmoveid . '</td>
                              <td>' . $OrderLine->ActDispDate . '</td>
                              <td>'.  $OrderLine->StockID . '</td>
			      <td>' . $OrderLine->ItemDescription . '</td>
                              <td>' . $OrderLine->Quantity . '</td>  
                              <td>' . $OrderLine->Price . '</td> 
                              <td>' . $DisplayDiscount . '</td> 
                              <td>' . $DisplayLineTotal . '</td></tr>';
                        
                      
                        $TempgroupId=$OrderLine->Stockmoveid;

		} /* end of loop around items */    
               echo '</table>'; 
        }
 
        else{
            echo  _('Your do not choose a Invoice!');
        }
 
              
?>
