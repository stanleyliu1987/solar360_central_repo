
        <?php
       include('includes/session.inc');
       include('includes/header.inc');


	/*if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */

		echo '<form id="trackform" action="TrackOrderResult.php" method="POST">';
               
                echo '<div id="wrapper">';
                echo '<div id="logo"><a href="http://www.solar360.com.au"><img src="image/logo.png" width="175" height="117"></a></div>';
                
                
                echo '<div id="content"><br><div id="legend">Order Status:</div>';
                echo '<p><div id=searchbox>';
                echo '<table class="search">';
                echo '<tr><td></td><td></td><td><a href="Instruction.php" target="_blank"><font color="#439638" size="2px"> How to track your order</font></a></td></tr>';
              	echo '<tr><td>' . _('*Invoice Number (e.g.: Wxxxx-xxxx)') . '<p>
				<input id="number" class="number" type="text" max="26" size="27" name="InvoiceNumber"></td>
                                  <td class="searchdesc">  (The invoice number was provided to you in an email sent after the order was placed.)</td></tr>';

		echo '<tr><td>' . _('*Email') . '<p>
				<input id="email" class="email" type="text" max="26" size="27" name="CustEmail"></td>
                               <td class="searchdesc"> (The one used when the order was placed)</td>
                               <td><a  href="#" class="checkstatus"><img src="image/CheckStatus.jpg" width="123" height="36"></img></a></td></tr>
			</table></div><br>';
       
                
                echo '<div id=result></div>';
                echo '</div></div>';
       include('includes/footer.inc');
        ?>


