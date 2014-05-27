<?php
/* $Id: PO_UnsetFormVbls.php 3242 2009-12-16 22:06:53Z tim_schofield $*/
/*PO_UnsetFormVariable on the purchase order line items */
                    unset($_POST['StockID']);
                    unset($_POST['Qty']);
                    unset($_POST['Price']);
                    unset($_POST['ItemDescription']);
                    unset($_POST['GLCode']);
                    unset($_POST['GLAccountName']);
                    unset($_POST['ReqDelDate']);
                    unset($_POST['ShiptRef']);
                    unset($_POST['Jobref']);
?>