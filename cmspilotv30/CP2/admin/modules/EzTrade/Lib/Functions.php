<?
class CP_Admin_Modules_EzTrade_Lib_Functions
{
    //==================================================================//
    function setActionsArray($actArray){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');

        //====================== Raise RFQ ================================//
        $actObj = $actArray->getActionObj('tradingRaiseRfqList');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise RFQ'
           ,'url' => "javascript:Enquiry.raiseRfqList();"
        ));

        //====================== Raise Quote ================================//
        $actObj = $actArray->getActionObj('tradingRaiseQuote');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise Quote'
           ,'url' => "javascript:Enquiry.raiseQuoteList();"
        ));

        //====================== Print RFQ ================================//
        $actObj = $actArray->getActionObj('tradingPrintRfq');
        $actArray->registerAction($actObj, array(
            'title' => 'Print RFQ'
           ,'url' => "javascript:Rfq.printRfq();"
        ));

        // //====================== RFQ Comparison ================================//
        // $actObj = $actArray->getActionObj('tradingRfqComparison');
        // $actArray->registerAction($actObj, array(
        //     'title' => 'RFQ Comparison'
        //    ,'url' => "javascript:Enquiry.rfqComparison();"
        // ));


        //====================== Print Quote ================================//
        $actObj = $actArray->getActionObj('tradingPrintQuote');
        $actArray->registerAction($actObj, array(
            'title' => 'Print Quote'
           ,'url' => "javascript:Quote.printQuote();"
        ));


        //====================== Raise SO ================================//
        $actObj = $actArray->getActionObj('tradingRaiseSO');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise SO'
           ,'url' => "javascript:Quote.raiseSOList();"
        ));

        //====================== Duplicate Quote ================================//
        $actObj = $actArray->getActionObj('tradingDuplicateQuote');
        $actArray->registerAction($actObj, array(
            'title' => 'Duplicate Quote'
           ,'url' => "javascript:Quote.duplicate();"
        ));

        //====================== Quote Product Markup ================================//
        $actObj = $actArray->getActionObj('tradingProductMarkupQuote');
        $actArray->registerAction($actObj, array(
            'title' => 'Product Markup'
           ,'url' => "javascript:Quote.productMarkup();"
        ));

        // //====================== Duplicate SO ================================//
        // $actObj = $actArray->getActionObj('tradingDuplicateSO');
        // $actArray->registerAction($actObj, array(
        //     'title' => 'Duplicate SO'
        //    ,'url' => "javascript:SO.duplicate();"
        // ));

        //====================== SO Product Markup ================================//
        $actObj = $actArray->getActionObj('tradingProductMarkupSO');
        $actArray->registerAction($actObj, array(
            'title' => 'Product Markup'
           ,'url' => "javascript:SO.productMarkup();"
        ));


        //====================== Print Sales Order ================================//
        $actObj = $actArray->getActionObj('tradingPrintSO');
        $actArray->registerAction($actObj, array(
            'title' => 'Print Sales Order'
           ,'url' => "javascript:SO.printSO();"
        ));


        //====================== Print Bank Invoice ================================//
        $actObj = $actArray->getActionObj('tradingPrintBankInvoice');
        $actArray->registerAction($actObj, array(
            'title' => 'Print Bank Invoice'
           ,'url' => "javascript:SO.printBankInvoice();"
        ));

        //====================== Raise Purchase Order ================================//
        $actObj = $actArray->getActionObj('tradingRaisePO');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise Purchase Order'
           ,'url' => "javascript:SO.raisePOList();"
        ));

        //====================== Raise Invoice ================================//
        $actObj = $actArray->getActionObj('tradingRaiseInvoice');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise Invoice'
           ,'url' => "javascript:SO.raiseInvoiceList();"
        ));

        //====================== Raise Shipment ================================//
        $actObj = $actArray->getActionObj('tradingRaiseShipment');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise Shipment'
           ,'url' => "javascript:SO.raiseShipmentList();"
        ));

        //====================== Print PO ================================//
        $actObj = $actArray->getActionObj('tradingPrintPO');
        $actArray->registerAction($actObj, array(
            'title' => 'Print PO'
           ,'url' => "javascript:PO.printPO();"
        ));


        //====================== Print Packing List ======================//
        $actObj = $actArray->getActionObj('tradingPrintPackingList');
        $actArray->registerAction($actObj, array(
            'title' => 'Print Packing List'
           ,'url' => "javascript:PL.printPL();"
        ));


        //====================== Print Invoice ======================//
        $actObj = $actArray->getActionObj('tradingPrintInvoice');
        $actArray->registerAction($actObj, array(
            'title' => 'Print Packing Invoice'
           ,'url' => "javascript:Invoice.printInvoice();"
        ));


        //====================== SO Product Markup Save ======================//
        $actObj = $actArray->getActionObj('tradingSoProductMarkupSave');
        $actArray->registerAction($actObj, array(
            'title' => 'Save'
           ,'url' => "javascript:SO.saveProductMarkup();"
        ));
    }

    /**
     * This is used when calculating item withing php application logic. ex: Raise Quote from Enquiry
     */
    function getCalculatedPrices($options = array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $arr = array();

        $calcType = $options['calcType'];

        $currRate = getCPModelObj('ezTrade_currencyRate');
        if ($calcType == 'quoteProduct') {
            $quantity       = $options['quantity'];
            $buy_unit_price = $options['buy_unit_price'];
            $buy_currency   = $options['buy_currency'];
            $sell_currency  = $options['sell_currency'];

            $buy_price           = 0;
            $buy_unit_price_base  = 0;
            $buy_price_base       = 0;
            $markup              = $fn->getSettingsValueByKey('defaultMarkupPercent');
            $sell_unit_price_base = 0;
            $sell_price_base      = 0;
            $sell_unit_price     = 0;
            $sell_price          = 0;

            $exchange_rate_buy  = $currRate->getCurrencyExchageRate($buy_currency, $cpCfg['m.trading.companyCurrency']);
            $exchange_rate_sell = $currRate->getCurrencyExchageRate($sell_currency, $cpCfg['m.trading.companyCurrency']);
            $exchange_rate_buy_to_sell = $currRate->getCurrencyExchageRate($buy_currency, $sell_currency);

            $buy_price     = $quantity * $buy_unit_price;
            $buy_price_base = $buy_price * $exchange_rate_buy;
            if ($quantity) {
                $buy_unit_price_base = $buy_price_base / $quantity;
                $sell_unit_price = $buy_unit_price * $exchange_rate_buy_to_sell;
                $sell_unit_price = $sell_unit_price + ($sell_unit_price * ($markup / 100));
                $sell_price = $sell_unit_price * $quantity;

                $sell_unit_price_base = $sell_unit_price * $exchange_rate_sell;
                $sell_price_base      = $sell_price * $exchange_rate_sell;
            }

            $arr['buy_unit_price']      = $buy_unit_price;
            $arr['buy_price']           = $buy_price;
            $arr['buy_unit_price_base']  = $buy_unit_price_base;
            $arr['buy_price_base']       = $buy_price_base;
            $arr['markup']              = $markup;
            $arr['sell_unit_price_base'] = $sell_unit_price_base;
            $arr['sell_price_base']      = $sell_price_base;
            $arr['sell_unit_price']     = $sell_unit_price;
            $arr['sell_price']          = $sell_price;

        }

        return $arr;
    }

    /**
     *
     */
    function getNextItemLineNo($main_table_key, $main_table_key_val, $history_table) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $SQL = "
        SELECT IF ( ISNULL( MAX(line_no)), 1, MAX(line_no) + 1 ) AS line_no
        FROM {$history_table}
        WHERE {$main_table_key} = {$main_table_key_val}
        ";
        $row = $fn->getRecordBySQL($SQL);

        return $row['line_no'];

    }

    /**
     *
     */
    function getUpdateHistoryTableLineNo($history_table, $history_table_key, $history_table_key_val, $line_no = 0) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        UPDATE {$history_table}
        SET line_no = {$line_no}
        WHERE {$history_table_key} = {$history_table_key_val}
        ";
        $db->sql_query($SQL);

    }

    /**
     *
     */
    function getFormattedPhoneField($country_code, $area_code, $phone) {
        $area_code = ($area_code != '') ? ' - ' . $area_code : '';
        $phone     = ($phone != '') ? ' - ' . $phone : '';
        $text = "{$country_code}{$area_code}{$phone}";

        return $text;
    }

    /**
     *
     */
    function getRoundedCurrencyValue($value, $roundLength = 0) {
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($roundLength == 0) {
            $roundLength = $cpCfg['m.trading.currencyDecimalPlaces'];
        }
        $value = round($value, $roundLength);

        return $value;
    }


}
