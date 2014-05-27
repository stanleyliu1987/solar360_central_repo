<?php

/* $Id: geo_displaymap_customers.php 4555 2011-04-19 10:18:49Z daintree $*/
//$PageSecurity = 3;


include ('includes/session.inc');
include ('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$title = _('Geocoded Customer Branches Report');
$sql="SELECT * FROM geocode_param WHERE 1";
$ErrMsg = _('An error occurred in retrieving the currency information');;
$result = DB_query($sql, $db, $ErrMsg);
$myrow = DB_fetch_array($result);

$api_key = $myrow['geocode_key'];
$center_long = $myrow['center_long'];
$center_lat = $myrow['center_lat'];
$map_height = $myrow['map_height'];
$map_width = $myrow['map_width'];
$map_host = $myrow['map_host'];
//echo '<script src="http://' . $map_host . '/maps?file=api&v=2&key=' . $api_key . '"';
//echo ' type="text/javascript"></script>';
echo '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>';
echo '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&sensor=true"></script>';
echo '<script type="text/javascript" src= "/javascripts/util.js"></script>';
echo ' <script type="text/javascript">';
echo '

  function load() {
  
   $(".custstate").change(function() {
       location.href="geo_displaymap_customers.php?custstate=" + $(this).val();
     });
     
     var Selectcustcode = $(".customer").val();
     var Selectcuststate = $(".custstate").val();
     
    var myLatlng = new google.maps.LatLng('.$center_lat.',' . $center_long . ');
    var myOptions = {
      zoom: 3,
      center: myLatlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    var map = new google.maps.Map(document.getElementById("map"), myOptions);

    downloadUrl("geocode_genxml_customers.php?custcode="+Selectcustcode+"&custstate="+Selectcuststate, function(data) {  
      var markers = data.documentElement.getElementsByTagName("marker"); 
      for (var i = 0; i < markers.length; i++) { 
        var latlng = new google.maps.LatLng(parseFloat(markers[i].getAttribute("lat")),
                                    parseFloat(markers[i].getAttribute("lng")));              
        var marker = new google.maps.Marker({position: latlng, map: map});
         
        var infoWindow = new google.maps.InfoWindow();
       
        google.maps.event.addListener(marker, "click", (function(marker, i) {
        return function() {
        var name = markers[i].getAttribute("name");
        var address = markers[i].getAttribute("address");
	var type = markers[i].getAttribute("type");
        var branchcontact = markers[i].getAttribute("branchcontact");
        var phoneno = markers[i].getAttribute("phoneno");
        var custcode = markers[i].getAttribute("custcode");
        var custstate = markers[i].getAttribute("custstate");
        var html = "<b><a href=SelectCustomer.php?CustCode="+custcode+">" + name + "</a></b> <br/>" + address+"<br/> Contact: " +branchcontact+"<br/> Phone : " +phoneno;
        infoWindow.setContent(html);
        infoWindow.open(map,marker); 
        }
      })(marker, i));
            
       }
     });
     
  }

  </script>
  </head>

  <body onload="load()" onunload="GUnload()">';

      if (isset($_POST['ShowReport'])){
          if(isset($_POST['lastmonth']) and $_POST['lastmonth'] != ''){
              if($_POST['lastmonth']==1){ 
                  $Mindate= date("Y-m-d",strtotime(date("Y-m-d") . " -1 month"));
              }
              elseif($_POST['lastmonth']==2){
                  $Mindate= date("Y-m-d",strtotime(date("Y-m-d") . " -2 month"));
              }
              elseif($_POST['lastmonth']==3){
                  $Mindate= date("Y-m-d",strtotime(date("Y-m-d") . " -3 month"));
              }
              elseif($_POST['lastmonth']==6){
                  $Mindate= date("Y-m-d",strtotime(date("Y-m-d") . " -6 month"));
              }
              elseif($_POST['lastmonth']==12){
                  $Mindate= date("Y-m-d",strtotime(date("Y-m-d") . " -12 month"));
              } 
          }
   
          /* Retrieve latest month stock and quantity */
          if(isset($_POST['customer']) and isset($_POST['custstate'])){
              $SqlProduct="SELECT ABS(SUM(stockmoves.qty)) as qty,stockmoves.stockid,stockmaster.description FROM stockmoves inner join custbranch on stockmoves. debtorno=custbranch.debtorno
                                                   inner join stockmaster on stockmaster.stockid=stockmoves.stockid
                                                   WHERE stockmoves.debtorno='".$_POST['customer']."' 
                                                         and custbranch.braddress3= '".$_POST['custstate']."'
                                                         and stockmoves.trandate > '".$Mindate."'    
                                                   GROUP BY stockmoves.stockid ORDER BY qty desc limit 5";
              $resultTopProduct = DB_query($SqlProduct, $db);
         /* Retrieve Monthly Sales data */
              $SqlMonthSale="SELECT FORMAT(ABS((SUM(price*(1-discountpercent)* (-qty)))),2) AS SalesValue,YEAR(stockmoves.trandate) AS SaleYear,MONTH(stockmoves.trandate) AS SaleMonth  FROM stockmoves INNER JOIN custbranch ON stockmoves. debtorno=custbranch.debtorno
                                                  WHERE stockmoves.debtorno='".$_POST['customer']."' 
                                                        and custbranch.braddress3= '".$_POST['custstate']."'
                                                  GROUP BY MONTH(stockmoves.trandate),YEAR(stockmoves.trandate) ORDER BY stockmoves.trandate DESC";
              $resultMonthSales = DB_query($SqlMonthSale, $db);
          }

      }
         
	echo '<table class="callout_main" cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<td colspan="2" rowspan="2">';

	echo '<table class="main_page" cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<td>';
	echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" >';
	echo '<tr>';
	echo '<td>';

	if (isset($title)) {
		echo '<table cellpadding="0" cellspacing="0" border="0" id="quick_menu" class="quick_menu">';
		echo '<tr>';
		echo '<td align="left" style="width:100%;" class="quick_menu_left">';
// Use icons for company and user data, saves screen realestate, use ALT tag in case theme icon not avail.
		echo '<img src="'.$rootpath.'/css/'.$theme.'/images/company.png" title="' . _('Company') . '" alt="' . _('Company') . '"></img>';
		echo ' ' . stripslashes($_SESSION['CompanyRecord']['coyname']) . '  <a href="' .  $rootpath . '/UserSettings.php"><img src="'.$rootpath.'/css/'.$theme.'/images/user.png" title="User" alt="' . _('User') . '"> </img>' . stripslashes($_SESSION['UsersRealName']) . '</a>';
// Make the title text a class, can be set to display:none is some themes
		echo '<br /><font class="header_title"> ' . $title . '</font></td>';
		echo '<td class="quick_menu_tabs">';
		echo '<table cellpadding="0" cellspacing="0" class="quick_menu_tabs"><tr>';
		echo '<td class="quick_menu_tab" align="center"><a accesskey="1" href="' .  $rootpath . '/index.php"><span style="text-decoration:underline;">1</span> ' . _('Main Menu') . '</a></td>';

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){

			echo '<td class="quick_menu_tab" align="center"><a accesskey="2" href="' .  $rootpath . '/SelectCustomer.php"><span style="text-decoration:underline;">2</span> ' . _('Customers') . '</a></td>';

			echo '<td class="quick_menu_tab" align="center"><a accesskey="3" href="' .  $rootpath . '/SelectProduct.php"><span style="text-decoration:underline;">3</span> ' . _('Items') . '</a></td>';

			echo '<td class="quick_menu_tab" align="center"><a accesskey="4" href="' .  $rootpath . '/SelectSupplier.php"><span style="text-decoration:underline;">4</span> ' . _('Suppliers') . '</a></td>';

			$DefaultManualLink = '<td class="quick_menu_tab" align="center"><a rel="external" accesskey="8" href="' .  $rootpath . '/doc/Manual/ManualContents.php"><span style="text-decoration:underline;">8</span> ' . _('Manual') . '</a></td>';

			if (substr($_SESSION['Language'],0,2) !='en'){
				if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')){
					echo '<td class="quick_menu_tab" align="center"><a target="_blank" accesskey="8" href="' .  $rootpath . '/locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php"><span style="text-decoration:underline;">8</span> ' . _('Manual') . '</a></td>';
				} else {
					echo $DefaultManualLink;
				}
			} else {
					echo $DefaultManualLink;
			}
		}

		echo '<td class="quick_menu_tab" align="center"><a accesskey="0" href="' . $rootpath . '/Logout.php" onclick="return confirm(\'' . _('Are you sure you wish to logout?') . '\');"><span style="text-decoration:underline;">0</span> '  . _('Logout') . '</a></td>';

		echo '</tr></table>';
		echo '</td></tr></table>';

	}

echo '</td>';
echo '</tr>';
echo '</table>';  
?>
<p>
<?php echo '<div class="centre" id="map" style="width: ' . $map_width . 'px; height: ' . $map_height . 'px"></div>'; ?>
</p>


<?php 
      echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
      echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
      echo '<div class="centre" id="showcustomer">';
      if(isset($_GET['custstate']) and $_GET['custstate']!=''){
        $resultCustomer = DB_query("SELECT debtorno, name FROM debtorsmaster where address3='".$_GET['custstate']."' order by name", $db);   
      }
      elseif(isset($_POST['custstate']) and $_POST['custstate']!=''){
        $resultCustomer = DB_query("SELECT debtorno, name FROM debtorsmaster where address3='".$_POST['custstate']."' order by name", $db);  
      }
      else{
        $resultCustomer = DB_query('SELECT debtorno, name FROM debtorsmaster order by name', $db);
      }
      $resultCuststate = DB_query('SELECT braddress3 FROM custbranch where braddress3 <> " " group by braddress3', $db);
      echo '<table class=selection>
      <tr><td>'._('Choose a State:').'</td>';
      echo '<td><select name=custstate class=custstate >';
      echo '<option  value="">' . _('Choose a State') . '</option>';
		while ($myrow = DB_fetch_array($resultCuststate)) { 
			if (($_GET['custstate'] == $myrow['braddress3']) or ($_POST['custstate'] == $myrow['braddress3'])) {
				echo '<option  selected value=' . $myrow['braddress3'] . '>' . $myrow['braddress3']  . '</option>';
			} else {
				echo '<option value=' . $myrow['braddress3'] . '>' . $myrow['braddress3']  . '</option>';
			}
		} //end while loop
		
      echo '</select></td></tr>';   
      
      echo '<tr><td>'._('Choose a Customer:').'</td>';
      echo '<td><select name=customer class=customer>';
      echo '<option  value="">' . _('Choose a Customer') . '</option>';
		while ($myrow = DB_fetch_array($resultCustomer)) { 
			if (($_GET['customer'] == $myrow['debtorno']) or ($_POST['customer'] == $myrow['debtorno'])) {
				echo '<option  selected value=' . $myrow['debtorno'] . '>' . $myrow['name']  . '</option>';
			} else {
				echo '<option value=' . $myrow['debtorno'] . '>' . $myrow['name']  . '</option>';
			}
		} //end while loop
		
      echo '</select></td></tr>';
      
      echo '<tr><td>'._('Last Month Period:').'</td>';
      echo '<td><select name=lastmonth class=lastmonth>';
      foreach ($LastMonthArray as $key=>$value) {
            if (($_GET['lastmonth'] == $value) or ($_POST['lastmonth'] == $value)) {
		echo '<option  selected value=' . $value . '>' . $key  . '</option>';
	     } 
            else {echo '<option value=' . $value . '>' . $key  . '</option>';}
      }
      echo '</select></td></tr>'; 
       
      echo '<tr><td><input type=submit name="ShowReport" value="' . _('Show Report') . '"></td></tr></table><br/>';
        
     /* Retrieve the Customer Top 5 Products */
      echo '<table class=selection><tr><td>Top Sales Product and Qty: </td></tr>';
      echo '<tr><th>Stock Code</th><th>Stock Description</th><th>Quantity</th></tr>'; 
      while ($myrow = DB_fetch_array($resultTopProduct)) { 
	echo '<tr><td>'.$myrow['stockid'].'</td><td>'.$myrow['description'].'</td><td>'.$myrow['qty'].'</td></tr>';
        
       } //end while loop
  
        echo '</table><br/>';
       
      
     /* Retrieve Customer Month Sales Report */
        
        echo '<table class=selection>
              <tr><td>Monthly Sales Report: </td></tr>';
        
        $reportheader ='<tr><th>Month</th>';
        $salesyear='';
        $salesmonth='';
        $monthsalevalue= array();
        $j=-1;
     /* Retrieve the Customer Top 5 Products */
      while ($myrow = DB_fetch_array($resultMonthSales)) {
          if($myrow['SaleYear']!=$salesyear){
              $reportheader.= '<th>'.$myrow['SaleYear'].'</th>';
              $j++;
          }
          $formatmonth=date("M", mktime(0, 0, 0, $myrow['SaleMonth']));
          if($salesmonth!= $formatmonth){
             if($j>0 and $monthsalevalue[$formatmonth][$j-1]==null){
                 $monthsalevalue[$formatmonth][$j-1]='';
             }
            $monthsalevalue[$formatmonth][$j]=  $myrow['SalesValue'];
          }
          $salesyear=  $myrow['SaleYear'];
          $salesmonth= $formatmonth;
       } //end while loop
       $reportheader.='</tr>';
       echo $reportheader;
   
       foreach($monthsalevalue as $month=>$values){
            $reportcontent.='<tr><td>'.$month.'</td>';
           foreach($values as $value){
            $reportcontent.='<td>'.$value.'</td>';  
          }
            $reportcontent.='</tr>';
       }
       echo $reportcontent;
       
       echo '</table></div><br/>'; ?>
  </body>
<?php
echo '<div class="centre"><a href="' . $rootpath . '/GeocodeSetup.php">' . _('Go to Geocode Setup') . '</a></div></p>';
echo '</form>';
include ('includes/footer.inc');
?>
</html>
