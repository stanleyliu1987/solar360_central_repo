<?php

/* $Id: geo_displaymap_suppliers.php 4555 2011-04-19 10:18:49Z daintree $*/
//$PageSecurity = 3;


include ('includes/session.inc');
include ('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$title = _('Geocoded Supplier Report');
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

echo '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>';
echo '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&sensor=true"></script>';
echo '<script type="text/javascript" src= "/javascripts/util.js"></script>';
echo ' <script type="text/javascript">';
echo '

  function load() {
    var myLatlng = new google.maps.LatLng('.$center_lat.',' . $center_long . ');
    var myOptions = {
      zoom: 3,
      center: myLatlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    var map = new google.maps.Map(document.getElementById("map"), myOptions);

    downloadUrl("geocode_genxml_suppliers.php", function(data) {  
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
        var html = "<b>" + name + "</b> <br/>" + address;
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
  </body>
<?php
echo '<div class="centre"><a href="' . $rootpath . '/GeocodeSetup.php">' . _('Go to Geocode Setup') . '</a></div></p>';
include ('includes/footer.inc');
?>
</html>
