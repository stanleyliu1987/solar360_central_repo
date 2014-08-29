
        <?php
       include('includes/session.inc');
       include('includes/header.inc');


	/*if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */

		echo '<form id="ordertrackguide" action="index.php" method="POST">';
                echo '<div id="guide">';
                echo '<img src="image/ordertrackingguide.png" alt="Order Tracking Instruction" />';
                echo '</div>';
       include('includes/footer.inc');
        ?>


