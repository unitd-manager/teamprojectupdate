<?
class CP_Admin_Modules_EzTrade_Product_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT p.*
              ,c.title AS category_title
              ,sc.title AS sub_category_title
        FROM product p
        LEFT JOIN category c      ON (c.category_id = p.category_id)
        LEFT JOIN sub_category sc ON (sc.sub_category_id = p.sub_category_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $product_id      = $fn->getReqParam('product_id');
        $category_id     = $fn->getReqParam('category_id');
        $sub_category_id = $fn->getReqParam('sub_category_id');
        $collection_name   = $fn->getReqParam('collection_name');
        $status = $fn->getReqParam('status');

        if ($product_id != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$product_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$tv['record_id']}";
        } else {
            if ($linkRecType != '') {
                // disable temporarily
                //only show the active ones
                //$searchVar->sqlSearchVar[] = "p.status = 'active'";
            }
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.product_id');
            if ($category_id != ''){
                $searchVar->sqlSearchVar[] = "p.category_id = {$category_id}";
            }
            if ($sub_category_id != ''){
                $searchVar->sqlSearchVar[] = "p.sub_category_id = {$sub_category_id}";
            }
            if ($collection_name != ''){
                $searchVar->sqlSearchVar[] = "p.collection_name = '{$collection_name}'";
            }
            if ($status != ''){
                $searchVar->sqlSearchVar[] = "p.status = '{$status}'";
            }
            if ($tv['keyword'] != ""){
                $searchVar->sqlSearchVar[] = "
                (  p.title        LIKE '%{$tv['keyword']}%'
                OR p.product_code LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the Item Name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('title', 'Please enter Item Name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'product_code');
        $fa = $fn->addToFieldsArray($fa, 'web_code');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'collection_name');
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'unit');
        $fa = $fn->addToFieldsArray($fa, 'origin');
        $fa = $fn->addToFieldsArray($fa, 'dynasty');
        $fa = $fn->addToFieldsArray($fa, 'circa');
        $fa = $fn->addToFieldsArray($fa, 'material');
        $fa = $fn->addToFieldsArray($fa, 'color');
        $fa = $fn->addToFieldsArray($fa, 'color_inside');
        $fa = $fn->addToFieldsArray($fa, 'cbm_per_pc');
        $fa = $fn->addToFieldsArray($fa, 'wholesale_price');
        $fa = $fn->addToFieldsArray($fa, 'trade_price');
        $fa = $fn->addToFieldsArray($fa, 'retail_price');
        $fa = $fn->addToFieldsArray($fa, 'contract_price');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'dimension_h');
        $fa = $fn->addToFieldsArray($fa, 'dimension_w');
        $fa = $fn->addToFieldsArray($fa, 'dimension_d');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_web');
        $fa = $fn->addToFieldsArray($fa, 'hardware');
        $fa = $fn->addToFieldsArray($fa, 'unit_qty');
        $fa = $fn->addToFieldsArray($fa, 'website_comments');

        return $fa;
    }

    /**
     *
     */
    function getSQLLink($linkRecType) {
        $tv = Zend_Registry::get('tv');

        if ($tv['module'] == 'enquiry1' && $linkRecType == 'linked') {
            $SQL = "
            SELECT p.*
                  ,c.title AS category_title
                  ,sc.title AS sub_category_title
            FROM product p
            LEFT JOIN enquiry_product ep ON (ep.product_id = p.product_id)
            LEFT JOIN category c         ON (c.category_id = p.category_id)
            LEFT JOIN sub_category sc    ON (sc.sub_category_id = p.sub_category_id)
            ";
        } else {
            $SQL = $this->getSQL();
        }

        return $SQL;
    }

    /**
     *
     */
    function getEzTradeProductEzTradeRfqItemsLinkSQL($id) {

        $SQL = "
        SELECT DISTINCT
               qri.quote_request_items_id
              ,qr.quote_request_id
              ,CONCAT_WS('-', qr.quote_request_code, qri.line_no) AS quote_request_line_no
              ,c.company_id
              ,c.company_name
              ,qri.quantity
              ,qr.quote_request_date
              ,qr.buy_currency
              ,qri.buy_unit_price
              ,qri.buy_unit_price_base
              ,qri.lead_time
              ,qri.country_of_origin
              ,qri.status
              ,qr.valid_until
        FROM quote_request_items qri
        JOIN quote_request qr   ON (qr.quote_request_id = qri.quote_request_id)
        JOIN company c          ON (c.company_id = qr.company_id_supplier)
        JOIN enquiry_product ep ON (ep.quote_request_items_id = qri.quote_request_items_id)
        WHERE qri.product_id = {$id}
        ORDER BY qr.valid_until DESC
        ";

        return $SQL;
    }


    /**
     *
     */
    function getCalculatedValuesRfq() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fnsModGrp = includeCPClass('ModGroup', 'EzTrade', 'Functions');
        $recalculate = $fn->getReqParam('recalculate');

        $arr = array();

        $currRate = getCPModelObj('ezTrade_currencyRate');
        $quantity       = $fn->getReqParam('quantity');
        $buy_unit_price = $fn->getReqParam('buy_unit_price');
        $buy_currency   = $fn->getReqParam('buy_currency');

        $exchange_rate = $currRate->getCurrencyExchageRate($buy_currency, $cpCfg['m.trading.companyCurrency']);

        $buy_price          = $quantity * $buy_unit_price;
        $buy_price_base      = $buy_price * $exchange_rate;
        $buy_unit_price_base = $buy_price_base / $quantity;

        $arr['buy_price']          = $fnsModGrp->getRoundedCurrencyValue($buy_price, 3);
        $arr['buy_price_base']      = $fnsModGrp->getRoundedCurrencyValue($buy_unit_price);
        $arr['buy_unit_price_base'] = $fnsModGrp->getRoundedCurrencyValue($buy_unit_price_base);


        return $cpUtil->getJsonFromArray($arr);

    }

    /**
     *
     */
    function getCalculatedValuesQuoteItems() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fnsModGrp = includeCPClass('ModGroup', 'EzTrade', 'Functions');
        $recalculate = $fn->getReqParam('recalculate');

        $arr = array();

        $currRate = getCPModelObj('ezTrade_currencyRate');
        $quantity       = $fn->getReqParam('quantity');
        $buy_unit_price = $fn->getReqParam('buy_unit_price');
        $buy_currency   = $fn->getReqParam('buy_currency');

        $sell_unit_price = $fn->getReqParam('sell_unit_price');
        $sell_currency   = $fn->getReqParam('sell_currency');

        $other_costs_1_curr = $fn->getReqParam('other_costs_1_curr');
        $other_costs_2_curr = $fn->getReqParam('other_costs_2_curr');
        $other_costs_3_curr = $fn->getReqParam('other_costs_3_curr');

        $other_costs_1 = $fn->getReqParam('other_costs_1');
        $other_costs_2 = $fn->getReqParam('other_costs_2');
        $other_costs_3 = $fn->getReqParam('other_costs_3');

        $other_costs_1_exchRateUSD = $currRate->getCurrencyExchageRate($other_costs_1_curr, $cpCfg['m.trading.companyCurrency']);
        $other_costs_2_exchRateUSD = $currRate->getCurrencyExchageRate($other_costs_2_curr, $cpCfg['m.trading.companyCurrency']);
        $other_costs_3_exchRateUSD = $currRate->getCurrencyExchageRate($other_costs_3_curr, $cpCfg['m.trading.companyCurrency']);

        $other_costs_1_base  = $other_costs_1 * $other_costs_1_exchRateUSD;
        $other_costs_2_base  = $other_costs_2 * $other_costs_2_exchRateUSD;
        $other_costs_3_base  = $other_costs_3 * $other_costs_3_exchRateUSD;

        $other_costs_base = $other_costs_1_base + $other_costs_2_base + $other_costs_3_base;

        $buy_price           = 0;
        $buy_price_base       = 0;
        $sell_price          = 0;
        $sell_price_base      = 0;
        $buy_unit_price_base  = 0;
        $sell_unit_price_base = 0;
        $markup              = 0;

        if ($quantity > 0) {
            $buy_price  = $buy_unit_price * $quantity;
            $sell_price = $sell_unit_price * $quantity;

            $exchange_rate_buyToUSD  = $currRate->getCurrencyExchageRate($buy_currency, $cpCfg['m.trading.companyCurrency']);
            $exchange_rate_sellToUSD = $currRate->getCurrencyExchageRate($sell_currency, $cpCfg['m.trading.companyCurrency']);
            $exchange_rate_buyToSell = $currRate->getCurrencyExchageRate($buy_currency, $sell_currency);
            $exchange_rate_USDToSell = $currRate->getCurrencyExchageRate($cpCfg['m.trading.companyCurrency'], $sell_currency);

            $buy_price_base  = $buy_price * $exchange_rate_buyToUSD;
            $sell_price_base = $sell_price * $exchange_rate_sellToUSD;

            $buy_unit_price_base  = $buy_price_base / $quantity;
            $sell_unit_price_base = $sell_price_base / $quantity;
            $markup = 100 * ($sell_price_base / ($buy_price_base + $other_costs_base)) - 100;

            //recalcuate to the set markup in settings
            if ($recalculate == 1) {
                $markup = $fn->getSettingsValueByKey('defaultMarkupPercent');

                $sell_price = $buy_price * $exchange_rate_buyToSell;
                $sell_price = $sell_price + ($sell_price * ($markup / 100));
                $other_costs_sell_curr = $other_costs_base * $exchange_rate_USDToSell;
                $sell_price = $sell_price + $other_costs_sell_curr;
                $sell_unit_price = $sell_price / $quantity;
            }
        }

        $arr['buy_price']          = $fnsModGrp->getRoundedCurrencyValue($buy_price, 3);
        $arr['buy_price_base']      = $fnsModGrp->getRoundedCurrencyValue($buy_unit_price_base, 3);
        $arr['buy_unit_price_base'] = $fnsModGrp->getRoundedCurrencyValue($buy_unit_price_base);

        $arr['sell_unit_price']     = $fnsModGrp->getRoundedCurrencyValue($sell_unit_price);
        $arr['sell_price']          = $fnsModGrp->getRoundedCurrencyValue($sell_price, 3);
        $arr['sell_price_base']      = $fnsModGrp->getRoundedCurrencyValue($sell_price_base, 3);
        $arr['sell_unit_price_base'] = $fnsModGrp->getRoundedCurrencyValue($sell_unit_price_base, 3);
        $arr['markup']              = $fnsModGrp->getRoundedCurrencyValue($markup, 2);

        $arr['other_costs_1_base'] = $fnsModGrp->getRoundedCurrencyValue($other_costs_1_base, 3);
        $arr['other_costs_2_base'] = $fnsModGrp->getRoundedCurrencyValue($other_costs_2_base, 3);
        $arr['other_costs_3_base'] = $fnsModGrp->getRoundedCurrencyValue($other_costs_3_base, 3);

        return $cpUtil->getJsonFromArray($arr);

    }

    /**
     *
     */
    function getCalculatedValuesSoItems() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fnsModGrp = includeCPClass('ModGroup', 'EzTrade', 'Functions');
        $recalculate = $fn->getReqParam('recalculate');

        $arr = array();

        $currRate = getCPModelObj('ezTrade_currencyRate');
        $quantity       = $fn->getReqParam('quantity');
        $buy_unit_price = $fn->getReqParam('buy_unit_price');
        $buy_currency   = $fn->getReqParam('buy_currency');

        $sell_unit_price = $fn->getReqParam('sell_unit_price');
        $sell_currency   = $fn->getReqParam('sell_currency');

        $other_costs_1_curr = $fn->getReqParam('other_costs_1_curr');
        $other_costs_2_curr = $fn->getReqParam('other_costs_2_curr');
        $other_costs_3_curr = $fn->getReqParam('other_costs_3_curr');

        $other_costs_1 = $fn->getReqParam('other_costs_1');
        $other_costs_2 = $fn->getReqParam('other_costs_2');
        $other_costs_3 = $fn->getReqParam('other_costs_3');

        $other_costs_1_exchRate = $currRate->getCurrencyExchageRate($other_costs_1_curr, $cpCfg['m.trading.companyCurrency']);
        $other_costs_2_exchRate = $currRate->getCurrencyExchageRate($other_costs_2_curr, $cpCfg['m.trading.companyCurrency']);
        $other_costs_3_exchRate = $currRate->getCurrencyExchageRate($other_costs_3_curr, $cpCfg['m.trading.companyCurrency']);

        $other_costs_1_base  = $other_costs_1 * $other_costs_1_exchRate;
        $other_costs_2_base  = $other_costs_2 * $other_costs_2_exchRate;
        $other_costs_3_base  = $other_costs_3 * $other_costs_3_exchRate;

        $other_costs_base = $other_costs_1_base + $other_costs_2_base + $other_costs_3_base;

        $buy_price           = 0;
        $buy_price_base       = 0;
        $sell_price          = 0;
        $sell_price_base      = 0;
        $buy_unit_price_base  = 0;
        $sell_unit_price_base = 0;
        $markup              = 0;

        if ($quantity > 0) {
            $buy_price  = $buy_unit_price * $quantity;
            $sell_price = $sell_unit_price * $quantity;

            $exchange_rate_buy  = $currRate->getCurrencyExchageRate($buy_currency, $cpCfg['m.trading.companyCurrency']);
            $exchange_rate_sell = $currRate->getCurrencyExchageRate($sell_currency, $cpCfg['m.trading.companyCurrency']);

            $buy_price_base  = $buy_price * $exchange_rate_buy;
            $sell_price_base = $sell_price * $exchange_rate_sell;

            $buy_unit_price_base  = $buy_price_base / $quantity;
            $sell_unit_price_base = $sell_price_base / $quantity;

            $markup = 100 * ($sell_price_base / ($buy_price_base + $other_costs_base)) - 100;

            if ($recalculate == 1) {
                $exchange_rate_base_to_sell = $currRate->getCurrencyExchageRate($cpCfg['m.trading.companyCurrency'], $sell_currency);

                $markup             = $fn->getSettingsValueByKey('defaultMarkupPercent');
                $buy_price_base_temp = $buy_price_base + $other_costs_base;

                $sell_price_base      = $buy_price_base_temp + ($buy_price_base_temp * ($markup / 100));
                $sell_unit_price_base = $sell_price_base / $quantity;

                $sell_price      = $sell_price_base * $exchange_rate_base_to_sell;
                $sell_unit_price = $sell_price / $quantity;
            }
        }

        $arr['buy_price']          = $fnsModGrp->getRoundedCurrencyValue($buy_price, 3);
        $arr['buy_price_base']      = $fnsModGrp->getRoundedCurrencyValue($buy_unit_price_base, 3);
        $arr['buy_unit_price_base'] = $fnsModGrp->getRoundedCurrencyValue($buy_unit_price_base);

        $arr['sell_unit_price']     = $fnsModGrp->getRoundedCurrencyValue($sell_unit_price);
        $arr['sell_price']          = $fnsModGrp->getRoundedCurrencyValue($sell_price, 3);
        $arr['sell_price_base']      = $fnsModGrp->getRoundedCurrencyValue($sell_price_base, 3);
        $arr['sell_unit_price_base'] = $fnsModGrp->getRoundedCurrencyValue($sell_unit_price_base, 3);
        $arr['markup']              = $fnsModGrp->getRoundedCurrencyValue($markup, 2);

        $arr['other_costs_1_base'] = $fnsModGrp->getRoundedCurrencyValue($other_costs_1_base, 3);
        $arr['other_costs_2_base'] = $fnsModGrp->getRoundedCurrencyValue($other_costs_2_base, 3);
        $arr['other_costs_3_base'] = $fnsModGrp->getRoundedCurrencyValue($other_costs_3_base, 3);

        return $cpUtil->getJsonFromArray($arr);

    }

    /**
     *
     */
    function getCalculatedValuesPoItems() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fnsModGrp = includeCPClass('ModGroup', 'EzTrade', 'Functions');

        $arr = array();

        $currRate = getCPModelObj('ezTrade_currencyRate');
        $quantity       = $fn->getReqParam('quantity');
        $buy_unit_price = $fn->getReqParam('buy_unit_price');
        $buy_currency   = $fn->getReqParam('buy_currency');

        $buy_price           = 0;
        $buy_price_base       = 0;
        $buy_unit_price_base  = 0;
        $markup              = 0;

        if ($quantity > 0) {
            $buy_price  = $buy_unit_price * $quantity;

            $exchange_rate_buy  = $currRate->getCurrencyExchageRate($buy_currency, $cpCfg['m.trading.companyCurrency']);
            $buy_price_base  = $buy_price * $exchange_rate_buy;
            $buy_unit_price_base  = $buy_price_base / $quantity;

        }
        $arr['buy_price']          = $fnsModGrp->getRoundedCurrencyValue($buy_price, 3);
        $arr['buy_price_base']      = $fnsModGrp->getRoundedCurrencyValue($buy_price_base, 3);
        $arr['buy_unit_price_base'] = $fnsModGrp->getRoundedCurrencyValue($buy_unit_price_base);

        return $cpUtil->getJsonFromArray($arr);

    }
}
