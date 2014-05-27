
<?php
/*   <document_root>/MyWebSite/CostETACalculation.php
     Sample application invoking calculateCost, a Star Track Express eService operation
     Star Track Express
     25 May 2011
     Version 4.3 
*/
include('includes/DefineCartClass.php');

/* Session started in header.inc for password checking the session will contain the details of the order from the Cart class object. The details of the order come from SelectOrderItems.php 			*/

include('includes/session.inc');
$title = _('Order Delivery Details');
include('includes/header.inc');
include('includes/FreightCalculation.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['identifier'])) {
	$identifier=$_GET['identifier'];
}

if(isset($_GET['pre'])){
    $prepage=$_GET['pre'];
}

if(isset($_SESSION['ItemFreight'])){
    $backscript='SelectItemFreight.php';
}
else{
    $backscript='SelectOrderItems.php';
}
if (isset($_POST['BackToLineDetails']) and $_POST['BackToLineDetails']==_('Enter Freight Cost')){
    unset($_SESSION['TotalFreight']);
if($prepage=='InvoiceModification'){
        echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/Invoice_Modification.php?identifier='.$identifier   . '">';
}
elseif($prepage=='newsalesorder'){
	echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/'.$backscript.'?identifier='.$identifier   . '">';
}
prnMsg(_('You should automatically be forwarded to the entry of the order line details page') . '. ' . _('If this does not happen') . '(' . _('if the browser does not support META Refresh') . ') ' .'<a href="' . $rootpath . '/'.$backscript.'?identifier='.$identifier  . '">'. _('click here') .'</a> '. _('to continue'),'info');
        foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) { 
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->FreightCost=$_POST['FreightAmount_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemHeight=$_POST['ItemHeight_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemWidth=$_POST['ItemWidth_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemLength=$_POST['ItemLength_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemVolume=$_POST['ItemVolume_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemWeight=$_POST['ItemWeight_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemChargeWeight= $_POST['ItemChargeWeight_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemShipper=$_POST['ItemShipper_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemServiceType=$_POST['ItemServiceType_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemPrefSupp=$_POST['ItemPrefSupp_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemSuppWare=$_POST['ItemSuppWare_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemFreComment=$_POST['ItemFreComment_' . $OrderLine->LineNumber];
            
            $_SESSION['TotalFreight']+=$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->FreightCost;
        }
	include('includes/footer.inc');
	exit;

}
if(isset($_POST['SearchSuppliers'])){ 
foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) { 
   
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->FreightCost=$_POST['FreightAmount_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemHeight=$_POST['ItemHeight_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemWidth=$_POST['ItemWidth_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemLength=$_POST['ItemLength_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemVolume=$_POST['ItemVolume_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemWeight=$_POST['ItemWeight_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemChargeWeight= $_POST['ItemChargeWeight_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemShipper=$_POST['ItemShipper_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemServiceType=$_POST['ItemServiceType_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemPrefSupp=$_POST['ItemPrefSupp_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemSuppWare=$_POST['ItemSuppWare_' . $OrderLine->LineNumber];
            $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemFreComment=$_POST['ItemFreComment_' . $OrderLine->LineNumber];
        } 
    
}


echo '<form name="form1" action="' . $_SERVER['PHP_SELF'] . '?identifier='.$identifier  . '&pre='.$prepage.'" method=post  >';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input class="IdentifierId" type="hidden" name="IdentifierId" value="' . $identifier . '" />';
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Freight Cost') . '" alt="" />' . ' ';
echo _('Freight Cost Calculation Page') . ' ';	
if(isset($_SESSION['ItemFreight'])){ 
echo '<table  class=selection>
     <tr><td><b>'._('Deliver Address: ').'</b></td></tr>
     <tr><td>'._('City/Suburb:').'</td><td>'.$_SESSION['Items'.$identifier]->DelAdd2 . '</td></tr>
     <tr><td>'._('State:').'</td><td>'.$_SESSION['Items'.$identifier]->DelAdd3 . '</td></tr>
     <tr><td>'._('Postcode:').'</td><td>' .$_SESSION['Items'.$identifier]->DelAdd4 . '</td></tr>
     </table><br/><br/>';
}
else{
 echo '<table  class=selection>
     <tr><td><b>'._('Deliver Address: ').'</b></td></tr>
     <tr><td>'._('Deliver To:').'</td><td>'.$_SESSION['Items'.$identifier]->DeliverTo . '</td></tr>
     <tr><td>'._('Street:').'</td><td>'.$_SESSION['Items'.$identifier]->DelAdd1 . '</td></tr>
     <tr><td>'._('City/Suburb:').'</td><td>'.$_SESSION['Items'.$identifier]->DelAdd2 . '</td></tr>
     <tr><td>'._('State:').'</td><td>'.$_SESSION['Items'.$identifier]->DelAdd3 . '</td></tr>
     <tr><td>'._('Postcode:').'</td><td>' .$_SESSION['Items'.$identifier]->DelAdd4 . '</td></tr>
     </table><br/><br/>';   
}
echo '<table width="90%" cellpadding="2" colspan="7">
		<tr bgcolor=#800000>';
		echo '<th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Length (cm)') . '</th>
                                <th>' . _('Width (cm)') . '</th>
                                <th>' . _('Height (cm)') . '</th>
                                <th>' . _('Get Cube & Charge KG') . '</th>
                                <th>' . _('Cube (Meters Cubed )') . '</th>    
				<th>' . _('Dead Weight (KGs)') . '</th>
                                <th>' . _('Charge (KGs)') . '</th>
				<th>' . _('Carrier') . '</th>
                                <th>' . _('Service Type') . '</th>    
                                <th>' . _('Freight Amount') . '</th></tr>';
 $k=0;
 
foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
       if ($k==0){
	$RowStarter = '<tr bgcolor="#EEAABB">';
	$k=1;
	} else {
	$RowStarter = '<tr class="EvenTableRows">';
	$k=0;
	}
        if(!isset($_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemVolume)){
            $ItemVolume=$OrderLine->Quantity * $OrderLine->Volume;
        }
        else{
            $ItemVolume=$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemVolume;
        }
        if(!isset($_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemChargeWeight)){
            $ItemChargeWeight=0;
        }
        else{
            $ItemChargeWeight=$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemChargeWeight;
        }
        /* Re-calculate item cube value */
        if(isset($_POST['ItemCalculate_' . $OrderLine->LineNumber ]) or isset($_POST['SearchSuppliers'])){
            /* To Get Freight Company Rate */
           $sql = "SELECT shipper_id, shippername, shipperrate, surchargerate FROM shippers WHERE shipper_id='".$_POST['ItemShipper_' . $OrderLine->LineNumber]."'";
           $result = DB_query($sql, $db);
	   $myrow = DB_fetch_array($result);
           $Shipperrate= $myrow['shipperrate'];
           $ShipSurchargeRate= $myrow['surchargerate'];
           /* Set Volume Value */
           $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemLength=$_POST['ItemLength_' . $OrderLine->LineNumber];
           $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemWidth=$_POST['ItemWidth_' . $OrderLine->LineNumber];
           $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemHeight=$_POST['ItemHeight_' . $OrderLine->LineNumber];
           $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemChargeWeight=$_POST['ItemChargeWeight_' . $OrderLine->LineNumber];
           /* Use the calculate volume instead of input volume */
           $ItemVolume=round(($_POST['ItemLength_' . $OrderLine->LineNumber]/100)*($_POST['ItemWidth_' . $OrderLine->LineNumber]/100)*($_POST['ItemHeight_' . $OrderLine->LineNumber]/100),2); 
           
           /* Use the user input volume instead of the calculate volume */
//           if($ItemVolume!=$_POST['ItemVolume_' . $OrderLine->LineNumber] and isset($_POST['SearchSuppliers'])){
//           
//                    $ItemVolume=$_POST['ItemVolume_' . $OrderLine->LineNumber]; 
//               
//           }
           
           $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemVolume= $ItemVolume;
           
           /* Set Charge Weight Value */
           $ItemChargeWeight=round((($ItemVolume*$Shipperrate) > $_POST['ItemWeight_' . $OrderLine->LineNumber]) ? $ItemVolume*$Shipperrate : $_POST['ItemWeight_' . $OrderLine->LineNumber],2);
           $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemShipper=$_POST['ItemShipper_' . $OrderLine->LineNumber];
           $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemServiceType=$_POST['ItemServiceType_' . $OrderLine->LineNumber];
           $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemChargeWeight= $ItemChargeWeight;
           
           //$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemSuppWare=$_POST['ItemSuppWare_' . $OrderLine->LineNumber];
           /* Try to get Warehouse Postcode */
           $Warehousecode=$_POST['ItemSuppWare_' . $OrderLine->LineNumber];
           $Suppcode=$_POST['ItemPrefSupp_' . $OrderLine->LineNumber];
           
           $SuppWareResult = DB_query("SELECT whaddress1,whaddress2,whaddress3,whaddress4 FROM 
                                       supplierwarehouse where supplierid='".$Suppcode."' and  warehousecode='".$Warehousecode."'",$db);
           $SuppWareRow=DB_fetch_row($SuppWareResult);
           $WarehousePostCode=$SuppWareRow[3];
           $WarehouseArea=$SuppWareRow[1];
           $WarehouseState=$SuppWareRow[2];
           
           /* Set Freight Cost Value */
           $fcsql="SELECT fre_arearate.ratevalue,fre_areadelivery.commonrate, fre_areadelivery.minchargerate FROM fre_arearate INNER JOIN fre_weightrange ON fre_weightrange.id=fre_arearate.weightrangeref
                   INNER JOIN fre_areadelivery ON fre_areadelivery.id=fre_arearate.areadelref                        
                   WHERE fre_areadelivery.fromarea=(SELECT ratearea FROM fre_areamaster WHERE fre_areamaster.postcode='".$WarehousePostCode."' AND suburb ='".$WarehouseArea."' 
                         AND state='".$WarehouseState."' AND freightcompany='".$_POST['ItemShipper_' . $OrderLine->LineNumber]."') AND
                         fre_areadelivery.fromareasubcode=(SELECT rateareasubcode FROM fre_areamaster WHERE fre_areamaster.postcode='".$WarehousePostCode."' AND suburb='".$WarehouseArea."' 
                         AND state='".$WarehouseState."' AND freightcompany='".$_POST['ItemShipper_' . $OrderLine->LineNumber]."') AND
                         fre_areadelivery.toarea= (SELECT ratearea FROM fre_areamaster WHERE fre_areamaster.postcode='".$_SESSION['Items'.$identifier]->DelAdd4."' AND suburb='".$_SESSION['Items'.$identifier]->DelAdd2."' 
                         AND state='".$_SESSION['Items'.$identifier]->DelAdd3."' AND freightcompany='".$_POST['ItemShipper_' . $OrderLine->LineNumber]."') AND
                         fre_areadelivery.toareasubcode=(SELECT rateareasubcode FROM fre_areamaster WHERE fre_areamaster.postcode='".$_SESSION['Items'.$identifier]->DelAdd4."' AND suburb='".$_SESSION['Items'.$identifier]->DelAdd2."' 
                         AND state='".$_SESSION['Items'.$identifier]->DelAdd3."' AND freightcompany='".$_POST['ItemShipper_' . $OrderLine->LineNumber]."') AND 
                         fre_weightrange.minweight<'".$ItemChargeWeight."' AND
                         fre_weightrange.maxweight>'".$ItemChargeWeight."' AND
                         fre_areadelivery.servicetype= '".$_POST['ItemServiceType_' . $OrderLine->LineNumber]."'";
           $resultfc = DB_query($fcsql, $db);
           if(DB_num_rows($resultfc)!=0) {
	   $myrowfc = DB_fetch_array($resultfc);
           $FreightCostRate= $myrowfc['ratevalue']; 
           /* Add additional Rate value */
           $addsql = "SELECT additionalrate FROM fre_servicetype where id='".$_POST['ItemServiceType_' . $OrderLine->LineNumber]."'";
           $resultadd = DB_query($addsql,$db,'','');
           $addvaluerow=DB_fetch_row($resultadd);
           $Calculatecost=round((($FreightCostRate*$ItemChargeWeight)+$myrowfc['commonrate']+$addvaluerow[0])*(($ShipSurchargeRate/100)+1),2);
           $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->FreightCost=$Calculatecost>$myrowfc['minchargerate'] ? $Calculatecost:$myrowfc['minchargerate'];
           }
           else{
           $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->FreightCost='';    
           }
      
        }
        else{
            $ItemVolume=$OrderLine->Quantity * $OrderLine->Volume;
        }
            $ItemWeight=$OrderLine->Quantity * $OrderLine->Weight;
             
        echo $RowStarter;
        echo '<td><a target="_blank" href="' . $rootpath . '/StockStatus.php?identifier='.$identifier . '&StockID=' . $OrderLine->StockID . '&DebtorNo=' . $_SESSION['Items'.$identifier]->DebtorNo . '">' . $OrderLine->StockID . '</a></td>
	      <td>' . $OrderLine->ItemDescription . '</td>';
        echo '<td>' . $OrderLine->Quantity . '</td>';
        echo '<td><input class="number"  type=text name="ItemLength_' . $OrderLine->LineNumber . '" size=8 maxlength=8 value='.$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemLength.'></td>';
        echo '<td><input class="number"  type=text name="ItemWidth_' . $OrderLine->LineNumber . '" size=8 maxlength=8 value='.$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemWidth.' ></td>';
        echo '<td><input class="number"  type=text name="ItemHeight_' . $OrderLine->LineNumber . '" size=8 maxlength=8 value='.$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemHeight.'></td>';
        echo '<td><input class="number"  type=submit name="ItemCalculate_' . $OrderLine->LineNumber . '" size=8 maxlength=8 value=Calculate></td>';
  
 //       if(!isset($_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemWeight)){
           
//        }
//        else{
//            $ItemWeight=$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemWeight;
//        } 
 
        echo '<td><input class="number"  type=text name="ItemVolume_' . $OrderLine->LineNumber . '" size=8 maxlength=8 value=' . $ItemVolume. '></td>';
        echo '<td><input class="number"  type=text name="ItemWeight_' . $OrderLine->LineNumber . '" size=8 maxlength=8 value=' . $ItemWeight . '></td>';
        echo '<td><input class="number"  type=text name="ItemChargeWeight_' . $OrderLine->LineNumber . '" size=8 maxlength=8 value=' . $ItemChargeWeight . '></td>';
        echo '<td><select name="ItemShipper_' . $OrderLine->LineNumber . '" onChange="ReloadForm(form1.SearchSuppliers)">';
        $sql = "SELECT shipper_id, shippername FROM shippers order by shipperrate desc";
        $ErrMsg = _('The shipper could not be retrieved because');
        $DbgMsg = _('The SQL used to retrieve shipper and failed was');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

        while ($myrow=DB_fetch_array($result)){
	        if($myrow['shipper_id']==$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemShipper){
                   echo '<option value="'. $myrow['shipper_id'] . '" selected>' . $myrow['shippername'].'</option>'; 
                }
                else{
                   echo '<option value="'. $myrow['shipper_id'] . '">' . $myrow['shippername'].'</option>'; 
                }
		
	}
	echo '</td>';
        /* Retrieve the freight service type */
        echo '<td><select name="ItemServiceType_' . $OrderLine->LineNumber . '" onChange="ReloadForm(form1.SearchSuppliers)">';
        $sql = "SELECT id, typename FROM fre_servicetype order by id";
        $ErrMsg = _('The service type could not be retrieved because');
        $DbgMsg = _('The SQL used to retrieve service type and failed was');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

        while ($myrow=DB_fetch_array($result)){
	        if($myrow['id']==$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemServiceType){
                   echo '<option value="'. $myrow['id'] . '" selected>' . $myrow['typename'].'</option>'; 
                }
                else{
                   echo '<option value="'. $myrow['id'] . '">' . $myrow['typename'].'</option>'; 
                }
		
	}
	echo '</td>';
        
        echo '<td><input class="number"  type=text name="FreightAmount_' . $OrderLine->LineNumber . '" size=8 maxlength=8 value='.$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->FreightCost.'></td></tr>';
        
        /* Choose Prefreed Supplier */
        $PreferredSupplierResult = DB_query("SELECT suppliers.suppname, purchdata.supplierno FROM purchdata inner join suppliers on suppliers.supplierid=purchdata.supplierno
                                      where stockid='".$OrderLine->StockID."' and preferred=1 limit 1",$db);
        $myrowPS=DB_fetch_array($PreferredSupplierResult);
        
        /* Change Prefer supplier */
        if(isset($_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemPrefSupp) and $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemPrefSupp !=''){
           $myrowPS['supplierno']=$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemPrefSupp; 
        }

        /* End Change Prefer supplier */
        echo $RowStarter;
        echo '<td colspan=2>' . _('Preferred Supplier') . ':
              <select name="ItemPrefSupp_' . $OrderLine->LineNumber . '"   onChange="ReloadForm(form1.SearchSuppliers)">';
	$SuppCoResult = DB_query("SELECT supplierid, suppname FROM suppliers ORDER BY suppname",$db);
	while ( $SuppCoRow=DB_fetch_array($SuppCoResult)){
		if ($SuppCoRow['supplierid'] == $myrowPS['supplierno']) {
			echo '<option selected value="' . $SuppCoRow['supplierid'] . '">' . $SuppCoRow['suppname'] . '</option>';
		} else {
			echo '<option value="' . $SuppCoRow['supplierid'] . '">' . $SuppCoRow['suppname'] . '</option>';
		}
	}

	echo '</select></td><td colspan=5>
              <select name="ItemSuppWare_' . $OrderLine->LineNumber . '" onChange="ReloadForm(form1.SearchSuppliers)">
              <option value=>Please Choose a Warehouse</option>';
	$SuppWareResult = DB_query("SELECT warehousecode,warehousename,whaddress1,whaddress2,whaddress3,whaddress4 FROM supplierwarehouse where supplierid='".$myrowPS['supplierno']."' 
                                  ORDER BY warehousename",$db);
	while ( $SuppWareRow=DB_fetch_array($SuppWareResult)){
              if($SuppWareRow['warehousecode']==$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemSuppWare){
                  echo '<option value="'. $SuppWareRow['warehousecode'] . '" selected>' . $SuppWareRow['warehousename'].'-'.substr($SuppWareRow['whaddress1']
                                .' '.$SuppWareRow['whaddress2'].' '.$SuppWareRow['whaddress3'].' '.$SuppWareRow['whaddress4'],0,25) .'</option>';
                  $WarehousePostCode=$SuppWareRow['whaddress4'];
                  $WarehouseArea=$SuppWareRow['whaddress2'];
                  $WarehouseState=$SuppWareRow['whaddress3'];
                 }
             else{
	           echo '<option value="' . $SuppWareRow['warehousecode'] . '">' . $SuppWareRow['warehousename'].'-'.substr($SuppWareRow['whaddress1']
                                .' '.$SuppWareRow['whaddress2'].' '.$SuppWareRow['whaddress3'].' '.$SuppWareRow['whaddress4'],0,25). '</option>';
                }
	}
            
	echo '</select> '.$WarehousePostCode.'</td>';
        echo '<td colspan=4>' . _('Comment') . ':<textarea name="ItemFreComment_' . $OrderLine->LineNumber . '" cols="70%" rows="1">'.$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemFreComment.'</textarea>     </tr>';
        echo '</tr>';
       // unset($ItemChargeWeight);
    }

echo '</table>';

echo '<br /><div class="centre">
<input type=submit name="SearchSuppliers"  value="Update Records">    
<input type=submit name="BackToLineDetails" value="' . _('Enter Freight Cost') . '"><br />';
echo '</form>';


?>
