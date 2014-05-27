<?php
/* $Id: DefineStockTransfer.php 3242 2009-12-16 22:06:53Z tim_schofield $*/

class StockTransfer {

        var $StockID;
        Var $StockLocationFrom;
        Var $StockLocationTo; /*Used in stock transfers only */
        var $Controlled;
        var $Serialised;
        var $ItemDescription;
        Var $PartUnit;
        Var $StandardCost;
        Var $DecimalPlaces;
        Var $Quantity;
        var $SerialItems; /*array to hold controlled items*/

        //Constructor
        function StockTransfer(){
                $this->SerialItems = array();
                $Quantity =0;
        }
}

?>