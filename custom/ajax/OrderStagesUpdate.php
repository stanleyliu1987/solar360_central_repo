<?php

/* $Id: OrderStagesUpdate.php 19052014
 * Return Email Message to openwysiwyg textarea
 * editor  by Stan $ */

$PathPrefix = '../';
include($PathPrefix . 'includes/FileIncludes.php');

$TransferNumber = GetNextTransNo(16,$db);
$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
$SQLTransferDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

/* Update debtortrans status by Stan */
DB_query("UPDATE debtortrans SET debtortrans.order_stages='" . $_POST["OrderStages"] . "'
        WHERE  debtortrans.id ='" . $_POST["TransID"] . "'", $db);

/* 16062014 Update Order stage message by Stan */
DB_query("INSERT INTO order_stages_messages (debtortran_fk,order_stage_change,userid,changedatetime)
          VALUES ('" . $_POST["TransID"] ."','" . $_POST["OrderStages"] ."', '" . $_POST["UserID"] ."','" . date('Y-m-d H:i:s')."')",$db);

/* Auto stock movements if the order stage changed to Stock Dispatched by Stan 28112014 */
if($_POST["OrderStages"]==3){ 
/* 1. Retrieve the stock details, ID, Warehouse, Transfer Quantity */
                $SQL="SELECT sod.`stkcode` AS stockid, 
                      sod.`qtyinvoiced` AS qty, 
                      sod.`fromstkloc` AS location, 
                      deb.`order_`  AS orderno,
                      stk.`mbflag` AS stocktype
                      FROM `debtortrans` AS deb LEFT JOIN salesorderdetails AS sod ON deb.`order_`=sod.`orderno`
                      LEFT JOIN `stockmaster` AS stk ON stk.`stockid`=sod.`stkcode`
                      WHERE deb.id='".$_POST["TransID"] ."'";
                $ErrMsg =  _('Could not retrieve the Stock Details from the invoice because');
		$DbgMsg =  _('The SQL that failed was');
		$StockResultList = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                
                while ($stock = DB_fetch_array($StockResultList)) { 
                /* Retrieve Location Name */
                $LocationName=GetLocationName($stock["location"],$db);
/* 1.1 Retrieve the Sub-items if the item is Assemble product */
                if($stock["stocktype"]=="A"){
                    $SQLComponent="Select bom.component as code, bom.quantity as qty
                                   from bom where parent='".$stock["stockid"]."' 
                                   and effectiveafter <'" . Date('Y-m-d') . "'
			           and effectiveto >='" . Date('Y-m-d') . "'";
                    $ErrMsg =  _('Could not retrieve the Stock Component Details from the invoice because');
		    $DbgMsg =  _('The SQL that failed was');
		    $StockComponentResultList = DB_query($SQLComponent, $db, $ErrMsg, $DbgMsg, true);
                    while ($com = DB_fetch_array($StockComponentResultList)) { 
                /*  Need to get the current location quantity will need it later for the stock movement */       
                $QtyOnHandPrior=GetLocationQOHPrior($com["code"], $stock["location"], $db);
                /* Retrieve Virtual Consolidation quantity */
                $QtyOnHandPrior_VC=GetLocationQOHPrior($com["code"], '001', $db);
               /* Insert the stock movement for the stock going out / coming into of the from location */
                $UpdateTag=UpdateStockMovementDetails($stock["orderno"],$com["code"],$stock["location"],$TransferNumber,$SQLTransferDate,$PeriodNo,$stock["qty"]*$com["qty"],$LocationName,$QtyOnHandPrior,$QtyOnHandPrior_VC,$db);
                     }
                     
                }    
                else{
/* 2. Need to get the current location quantity will need it later for the stock movement */
                $QtyOnHandPrior=GetLocationQOHPrior($stock["stockid"], $stock["location"], $db);
                /* Retrieve Virtual Consolidation quantity */
                $QtyOnHandPrior_VC=GetLocationQOHPrior($stock["stockid"], '001', $db);
/* 3. Insert the stock movement for the stock going out / coming into of the from location */
                $UpdateTag=UpdateStockMovementDetails($stock["orderno"],$stock["stockid"],$stock["location"],$TransferNumber,$SQLTransferDate,$PeriodNo,$stock["qty"],$LocationName,$QtyOnHandPrior,$QtyOnHandPrior_VC,$db);
                   }
/* 4. Update the salesorderdetails dispatch stock */ 
                UpdateActualDispatch($stock["qty"],$stock["stockid"],$stock["orderno"],$db);
         
        }
   }   


function GetLocationQOHPrior($stock, $loccode, $db){
    	        $SQL="SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $stock . "'
				AND loccode= '" . $loccode . "'";

		$ErrMsg =  _('Could not retrieve the QOH at the sending location because');
		$DbgMsg =  _('The SQL that failed was');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}
                return $QtyOnHandPrior;
    
}

function GetLocationName($loccode,$db){
                    $SQL="SELECT locationname
				FROM locations
				WHERE loccode='" . $loccode . "'";

		$ErrMsg =  _('Could not retrieve the location name because');
		$DbgMsg =  _('The SQL that failed was');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                $FromLocName = DB_fetch_row($Result);
                return $FromLocName[0];
}

function UpdateStockMovementDetails($orderno,$stockid,$location,$tranno,$trandate,$period,$qty,$locname,$qoh,$qoh_vc,$db){
                $ref="Order Number: ".$orderno ." Move To Virtual Consolidation";
		$SQL = "INSERT INTO stockmoves (stockid,
					type,
					transno,
					loccode,
					trandate,
					prd,
					reference,
					qty,
					newqoh)
			VALUES (
					'" . $stockid . "',
					16,
					'" . $tranno . "',
					'" . $location . "',
					'" . $trandate . "',
					'" . $period . "',
					'" . $ref ."',
					'" . round(-$qty ,2)  . "',
					'" . ($qoh - round($qty,2)) ."'
					)";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg =  _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                
                $ref="Order Number: ".$orderno." Move From ".$locname;
		$SQL = "INSERT INTO stockmoves (stockid,
						type,
						transno,
						loccode,
						trandate,
						prd,
						reference,
						qty,
						newqoh)
			VALUES ('" . $stockid. "',
					16,
					'" . $tranno . "',
					'001',
					'" . $trandate . "',
					'" . $period . "',
					'" . $ref ."',
					'" . $qty . "',
					'" . ($qoh_vc + $qty) . "'
					)";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                           
/* 4. Update the locstock for the stock going out / coming into of the location */                
          	$SQL = "UPDATE locstock SET quantity = quantity - '" . $qty . "'
				WHERE stockid='" . $stockid . "'
				AND loccode='" . $location . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$SQL = "UPDATE locstock
				SET quantity = quantity + '" . $qty . "'
				WHERE stockid='" . $stockid. "'
				AND loccode=001";


		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

		$Result = DB_Txn_Commit($db);
}
function UpdateActualDispatch($qty,$stockid,$orderno,$db){
    
/* 5. Update the dispatch stock quantity in the salesorderdetails table */
                $SQL = "UPDATE salesorderdetails
		SET qtydispatched =  '" . $qty . "'
		WHERE stkcode='" . $stockid. "'
		AND orderno='" . $orderno. "'";
                
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the salesorder details record was used');
		$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true); 
                $Result = DB_Txn_Commit($db);
}
?>