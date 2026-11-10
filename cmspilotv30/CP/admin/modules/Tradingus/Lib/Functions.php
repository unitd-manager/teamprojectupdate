<?
class CP_Admin_Modules_Tradingus_Lib_Functions
{
    //==================================================================//
    function setActionsArray($actArray){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');
        $cpUrl = Zend_Registry::get('cpUrl');

        $searchQueryString = $pager->searchQueryString;
        $searchQueryString = preg_replace('/&_action=[a-zA-Z0-9\. _,]+&?/', "&", $searchQueryString);
        if (substr($searchQueryString, -1) == "&") {
           $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString)-1);
        }
        $searchQueryString .= $cpUrl->getQnMarkForUrl($searchQueryString);

        //====================== Product ================================//
        $actObj = $actArray->getActionObj('tradingProductExportForWeb');
        $actArray->registerAction($actObj, array(
             'title' => 'Export For Web'
            ,'url' => "{$searchQueryString}&_spAction=exportForWeb&showHTML=0&export=1&hasDB=1"
        ));

        //====================== Raise RFQ ================================//
        $actObj = $actArray->getActionObj('tradingRaiseRfqList');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise RFQ'
           ,'url' => "javascript:cpm.trading.enquiry.raiseRfqList();"
        ));

        //====================== Raise Quote ================================//
        $actObj = $actArray->getActionObj('tradingRaiseQuote');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise Quote'
           ,'url' => "javascript:cpm.trading.enquiry.raiseQuoteList();"
        ));

        //====================== Print RFQ ================================//
        $actObj = $actArray->getActionObj('tradingPrintRfq');
        $actArray->registerAction($actObj, array(
            'title' => 'Print RFQ'
           ,'url' => "javascript:cpm.trading.rfq.printRfq();"
        ));

        // //====================== RFQ Comparison ================================//
        // $actObj = $actArray->getActionObj('tradingRfqComparison');
        // $actArray->registerAction($actObj, array(
        //     'title' => 'RFQ Comparison'
        //    ,'url' => "javascript:cpm.trading.enquiry.rfqComparison();"
        // ));


        //====================== Print Quote ================================//
        $actObj = $actArray->getActionObj('tradingPrintQuote');
        $actArray->registerAction($actObj, array(
            'title' => 'Print Quote'
           ,'url' => "javascript:cpm.trading.quote.printQuote();"
        ));


        //====================== Raise SO ================================//
        $actObj = $actArray->getActionObj('tradingRaiseSO');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise SO'
           ,'url' => "javascript:cpm.trading.quote.raiseSOList();"
        ));

        //====================== Duplicate Quote ================================//
        $actObj = $actArray->getActionObj('tradingDuplicateQuote');
        $actArray->registerAction($actObj, array(
            'title' => 'Duplicate Quote'
           ,'url' => "javascript:cpm.tradingus.quote.duplicate();"
        ));

        //====================== Quote Product Markup ================================//
        $actObj = $actArray->getActionObj('tradingProductMarkupQuote');
        $actArray->registerAction($actObj, array(
            'title' => 'Product Markup'
           ,'url' => "javascript:cpm.trading.quote.productMarkup();"
        ));

        //====================== SALES ORDER ================================//
        $actObj = $actArray->getActionObj('tradingProductMarkupSO');
        $actArray->registerAction($actObj, array(
            'title' => 'Product Markup'
           ,'url' => "javascript:cpm.trading.salesOrder.productMarkup();"
        ));
        $actObj = $actArray->getActionObj('tradingNewSO');
        $actArray->registerAction($actObj, array(
            'title' => 'New Sales Order'
           ,'url' => $actArray->actionsArr['new']['url'] . '&order_type=general'
        ));
        $actObj = $actArray->getActionObj('tradingNewSOR');
        $actArray->registerAction($actObj, array(
            'title' => 'New SOR'
           ,'url' => $actArray->actionsArr['new']['url'] . '&order_type=SOR'
        ));
        $actObj = $actArray->getActionObj('tradingNewInternalSO');
        $actArray->registerAction($actObj, array(
            'title' => 'New Internal SO'
           ,'url' => $actArray->actionsArr['new']['url'] . '&order_type=Internal SO'
        ));

        $actObj = $actArray->getActionObj('tradingRaiseInvoice');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise Invoice'
           ,'url' => "javascript:cpm.trading.salesOrder.raiseInvoiceList();"
        ));

        $actObj = $actArray->getActionObj('tradingPrintSO');
        $actArray->registerAction($actObj, array(
            'title' => 'Print SO'
           ,'url' => "javascript:cpm.trading.salesOrder.printSO();"
        ));

        $actObj = $actArray->getActionObj('tradingPrintDeliveryNoteSO');
        $actArray->registerAction($actObj, array(
            'title' => 'Delivery Note'
           ,'url' => "javascript:cpm.trading.salesOrder.printDeliveryNote();"
        ));

        $actObj = $actArray->getActionObj('tradingRaiseShipment');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise Shipment'
           ,'url' => "javascript:cpm.trading.salesOrder.raiseShipmentList();"
        ));


        //====================== PURCHASE ORDER ================================//
        $actObj = $actArray->getActionObj('tradingRaisePO');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise Purchase Order'
           ,'url' => "javascript:cpm.trading.salesOrder.raisePOList();"
        ));

        $actObj = $actArray->getActionObj('tradingPrintPO');
        $actArray->registerAction($actObj, array(
            'title' => 'Print PO'
           ,'url' => "javascript:cpm.trading.purchaseOrder.printPO();"
        ));

        //====================== INVOICE ================================//

        $actObj = $actArray->getActionObj('tradingPrintInvoice');
        $actArray->registerAction($actObj, array(
            'title' => 'Print Invoice'
           ,'url' => "javascript:cpm.trading.invoice.printInvoice();"
        ));

        $actObj = $actArray->getActionObj('tradingPrintDeliveryNote');
        $actArray->registerAction($actObj, array(
            'title' => 'Delivery Note'
           ,'url' => "javascript:cpm.trading.invoice.printDeliveryNote();"
        ));

        //====================== SHIPMENT ================================//
        $actObj = $actArray->getActionObj('tradingShipmentReceived');
        $actArray->registerAction($actObj, array(
            'title' => 'Received'
           ,'url' => "javascript:cpm.trading.shipment.shipmentReceived();"
        ));

        $actObj = $actArray->getActionObj('tradingPrintProductLabel');
        $actArray->registerAction($actObj, array(
            'title' => 'Print Labels'
           ,'url' => "javascript:cpm.trading.shipment.printLabels();"
        ));

        //====================== Print Packing List ======================//
        $actObj = $actArray->getActionObj('tradingPrintPackingList');
        $actArray->registerAction($actObj, array(
            'title' => 'Print Packing List'
           ,'url' => "javascript:PL.printPL();"
        ));

        //====================== SO Product Markup Save ======================//
        $actObj = $actArray->getActionObj('tradingSoProductMarkupSave');
        $actArray->registerAction($actObj, array(
            'title' => 'Save'
           ,'url' => "javascript:cpm.trading.salesOrder.saveProductMarkup();"
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

        $currRate = getCPModelObj('trading_currencyRate');
        if ($calcType == 'quoteProduct') {
            $quantity       = $options['quantity'];
            $buy_unit_price = $options['buy_unit_price'];
            $buy_currency   = $options['buy_currency'];
            $sell_currency  = $options['sell_currency'];

            $buy_price            = 0;
            $buy_unit_price_base  = 0;
            $buy_price_base       = 0;
            $markup               = $fn->getSettingsValueByKey('defaultMarkupPercent');
            $sell_unit_price_base = 0;
            $sell_price_base      = 0;
            $sell_unit_price      = 0;
            $sell_price           = 0;

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

            $arr['buy_unit_price']       = $buy_unit_price;
            $arr['buy_price']            = $buy_price;
            $arr['buy_unit_price_base']  = $buy_unit_price_base;
            $arr['buy_price_base']       = $buy_price_base;
            $arr['markup']               = $markup;
            $arr['sell_unit_price_base'] = $sell_unit_price_base;
            $arr['sell_price_base']      = $sell_price_base;
            $arr['sell_unit_price']      = $sell_unit_price;
            $arr['sell_price']           = $sell_price;

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
    function getUpdateHistoryTableLineNo($history_table, $history_table_key,
                                         $history_table_key_val, $line_no = 0) {
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
    function getRoundAmount($value, $roundLength = 0, $format = false) {
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($value == '') {
            return '';
        }

        if ($roundLength == 0) {
            $roundLength = $cpCfg['m.trading.currencyDecimalPlaces'];
        }
        $value = round($value, $roundLength);

        if ($format){
            $value = $this->getFormattedNumber($value);
        }

        return $value;
    }


    function getCostingRowArr($par1 = '', $par2 = '', $par3 = '', $par4 = '', $par5 = ''){
        //array index would 0, 1, 2, 3, 4
        $arr = array($par1, $par2, $par3, $par4, $par5);

        return $arr;
    }

    /**
     *
     * @param type $cell. The param value can be td/th
     * @return string
     */
    function getCostingTableRow($arr, $cell = 'td'){
        $cells = '';

        for ($i = 0; $i < count($arr); $i++) {
            $cellT = $i == 0 ? 'th' : $cell;
            $value = $arr[$i];
            $class = 'col' . $i;
            $cells .= "<{$cellT} class='{$class}'>{$value}</{$cellT}>\n";
        }

        $text = "
        <tr>
        {$cells}
        </tr>
        ";

        return $text;
    }

    function getCostingTable($rowsText){
        $text = "
        <table class='costing'>
        {$rowsText}
        </table>
        ";
        return $text;
    }

    function getTermsParamArr($module, $company_id, $field_name){
        $formObj = Zend_Registry::get('formObj');

        $termsUrl = "index.php?_topRm=main&module={$module}" .
                    "&_spAction=chooseValues&record_id={$company_id}" .
                    "&field_name={$field_name}&showHTML=0";
        $expTerms = array();
        if ($formObj->mode == 'edit'){
            $expTerms = array(
                 'rowCls' => 'hasNotesRight terms'
                ,'notesRight' => "<input type='button' value='Choose' class='w50' " .
                                 "link='{$termsUrl}' class='choose-value' />"
            );
        }

        return $expTerms;
    }

    function getTermsSelectionList($SQL, $field_name){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();

        $rows = '';
        $rowCounter = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
			<tr class='even'>
                <td class='value'>{$row['description']}</td>
                <td><input type='button' value='Set' class='w50'/></td>
			</tr>
            ";

        }

        $text = "
        <div class='term-selection'>
            <table class='list'>
            {$rows}
            </table>
            <input type='hidden' id='field_name' value='{$field_name}'>
		</div>
        ";

        return $text;
    }

    function getFormattedNumber($number){
        return number_format($number, 2);
    }
}
