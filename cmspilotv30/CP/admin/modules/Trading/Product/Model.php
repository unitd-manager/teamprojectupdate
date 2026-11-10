<?php
class CP_Admin_Modules_Trading_Product_Model extends CP_Common_Lib_ModuleModelAbstract
{
    static $import_no;

    /**
     *
     *
     */
    function getSQL() {
        $stockSQL = "
        SELECT COUNT(*)
        FROM inventory i
        WHERE i.product_id = p.product_id
          AND i.status NOT IN ('sold', 'cancelled')
        ";

        $SQL = "
        SELECT p.*
              ,c.title AS category_title
              ,sc.title AS sub_category_title
              ,({$stockSQL}) AS stock_qty
              ,(CASE
                WHEN ({$stockSQL}) > 0
                THEN 'available'
                ELSE 'not available'
                END) AS status_stock

        FROM product p
        LEFT JOIN category c ON (c.category_id = p.category_id)
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
        $collection_name = $fn->getReqParam('collection_name');
        $status = $fn->getReqParam('status');
        $color = $fn->getReqParam('color');

        if ($product_id != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$product_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$tv['record_id']}";
        } else {
            if ($linkRecType != '') {
                if ($tv['srcRoom'] == 'trading_enquiry' && $linkRecType == 'linked') {
                    $searchVar->sqlSearchVar[] = "ep.record_type = 'product'";

                } else if ($tv['srcRoom'] == 'trading_salesOrder') {
                    if ($linkRecType == 'linked') {
                        $searchVar->sqlSearchVar[] = "soi.record_type = 'product'";

                    } else { //not linked
                        $sales_order_id = $tv['linkMasterTableID'];
                        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);

                        if ($rowSO['order_type'] == 'Internal SO') {
                            $searchVar->sqlSearchVar[] = "qr.valid_until >= '{$fn->getISODate()}'";
                        } else {
                        }

                    }
                }
                $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.product_id');
            }
            if ($category_id != ''){
                $searchVar->sqlSearchVar[] = "p.category_id = {$category_id}";
            }
            if ($sub_category_id != ''){
                $searchVar->sqlSearchVar[] = "p.sub_category_id = {$sub_category_id}";
            }
            if ($collection_name != ''){
                $searchVar->sqlSearchVar[] = "p.collection_name = '{$collection_name}'";
            }
            if ($color != ''){
                $searchVar->sqlSearchVar[] = "p.color = '{$color}'";
            }
            if ($status != ''){
                $stockSQL = "
                SELECT COUNT(*)
                FROM inventory i
                WHERE i.product_id = p.product_id
                  AND i.status NOT IN ('sold', 'cancelled')
                ";
                $statusText = '';
                if ($status == 'available'){
                    $statusText = '>';
                } else if ($status == 'not available'){
                    $statusText = '<=';
                }
                $searchVar->sqlSearchVar[] = "({$stockSQL}) {$statusText} 0";

            }
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "p.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(p.flag != 1 OR p.flag IS null)";
            }

            if ($tv['keyword'] != ""){
                $searchVar->sqlSearchVar[] = "
                (  p.title LIKE '%{$tv['keyword']}%'
                OR p.product_code LIKE '%{$tv['keyword']}%'
                OR p.web_code LIKE '%{$tv['keyword']}%'
                OR p.collection_name LIKE '%{$tv['keyword']}%'
                OR p.color LIKE '%{$tv['keyword']}%'
                OR p.material LIKE '%{$tv['keyword']}%'
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
        $validate->validateData('title', 'Please enter the Product Name');

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
        $fa['pt_tax_percentage']            = $fn->getSettingsValueByKey('cp.defaultTaxPercent');
        $fa['pt_qc_comm_percentage']        = $fn->getSettingsValueByKey('cp.defaultQCPercent');
        $fa['pt_agent_comm_percentage']     = $fn->getSettingsValueByKey('cp.defaultAgentPercent');
        $fa['pt_local_charges_percentage']  = $fn->getSettingsValueByKey('cp.defaultLocalChargesPercent');
        $fa['pt_shipping_cost_percentage']  = $fn->getSettingsValueByKey('cp.defaultShippingPercent');
        $fa['pt_insurance_cost_percentage'] = $fn->getSettingsValueByKey('cp.defaultInsurancePercent');
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('title', 'Please enter Product Name');

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
            LEFT JOIN category c ON (c.category_id = p.category_id)
            LEFT JOIN sub_category sc ON (sc.sub_category_id = p.sub_category_id)
            ";
        } else {
            $SQL = $this->getSQL();
        }

        return $SQL;
    }

    /**
     *
     */
    function getTradingProductTradingRfqItemsLinkSQL($id) {
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $checkImage = "<img src='{$cpCfg['cp.masterImagesPathAlias']}icons/checkbox_checked.gif' />";

        $chooseRFQText = '';
        if ($tv['action'] == 'edit') {
            $chooseRFQText = "
            CONCAT_WS('',
                      \"<input type='checkbox' \",
                      'class=\'select-rfq\' ',
                      'product-selected=\'', qri.product_selected, '\' ',
                      'quote_request_items_id=\'', qri.quote_request_items_id, '\' ',
                      IF(qri.product_selected=1, 'checked', ''),
                      '>'
                      )
            ";
        } else {
            $chooseRFQText = "
            CASE WHEN qri.product_selected = 1 THEN \"{$checkImage}\"
                 ELSE ''
            END
            ";
        }

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
              ,{$chooseRFQText} AS chooseRFQ

        FROM quote_request_items qri
        JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        JOIN company c ON (c.company_id = qr.company_id_supplier)
        JOIN enquiry_product ep ON (ep.quote_request_items_id = qri.quote_request_items_id)
        WHERE qri.product_id = {$id}
        ORDER BY qr.valid_until DESC
        ";

        return $SQL;
    }

    /**
     *
     */
    function getTradingProductTradingPricingTypeLinkSQL($product_id) {
        $SQL = "
        SELECT DISTINCT
               ppt.product_pricing_type_id
              ,pt.pricing_type
              ,ppt.sell_unit_price_base
              ,'' AS empty
        FROM product_pricing_type ppt
        JOIN pricing_type pt ON pt.pricing_type_id = ppt.pricing_type_id
        WHERE ppt.product_id = {$product_id}
        ORDER BY pt.sort_order
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

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');
        $recalculate = $fn->getReqParam('recalculate');

        $arr = array();

        $currRate = getCPModelObj('trading_currencyRate');
        $quantity       = $fn->getReqParam('quantity');
        $buy_unit_price = $fn->getReqParam('buy_unit_price');
        $buy_currency   = $fn->getReqParam('buy_currency');

        $exchange_rate = $currRate->getCurrencyExchageRate($buy_currency, $cpCfg['m.trading.companyCurrency']);

        $buy_price           = $quantity * $buy_unit_price;
        $buy_price_base      = $buy_price * $exchange_rate;
        $buy_unit_price_base = $buy_price_base / $quantity;

        $arr['buy_price']           = $fnsModGrp->getRoundAmount($buy_price, 3);
        $arr['buy_price_base']      = $fnsModGrp->getRoundAmount($buy_unit_price);
        $arr['buy_unit_price_base'] = $fnsModGrp->getRoundAmount($buy_unit_price_base);


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

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');
        $currRate = getCPModelObj('trading_currencyRate');

        $arr = $this->getCalculateProductCosting('');

        $sell_unit_price_base = $fn->getPostParam('sell_unit_price_base');
        $tax_percentage = $fn->getPostParam('tax_percentage');
        $sell_currency = $fn->getPostParam('sell_currency');
        $quantity = $fn->getPostParam('quantity');

        $sell_unit_price_ex_fact_base = $arr['sell_unit_price_ex_fact_base'];
        $sell_unit_price_fob_base = $arr['sell_unit_price_fob_base'];
        $sell_unit_price_cif_base = $arr['sell_unit_price_cif_base'];

        $ex_fact_markup = (($sell_unit_price_base / $sell_unit_price_ex_fact_base) - 1) * 100;
        $fob_markup = (($sell_unit_price_base / $sell_unit_price_fob_base) - 1) * 100;
        $cif_markup = (($sell_unit_price_base / $sell_unit_price_cif_base) - 1) * 100;
        $ex_fact_markup = $fnsModGrp->getRoundAmount($ex_fact_markup);
        $fob_markup = $fnsModGrp->getRoundAmount($fob_markup);
        $cif_markup = $fnsModGrp->getRoundAmount($cif_markup);

        $ex_fact_markup_amount = $sell_unit_price_base - $sell_unit_price_ex_fact_base;
        $fob_markup_amount = $sell_unit_price_base - $sell_unit_price_fob_base;
        $cif_markup_amount = $sell_unit_price_base - $sell_unit_price_cif_base;
        $ex_fact_markup_amount = $fnsModGrp->getRoundAmount($ex_fact_markup_amount);
        $fob_markup_amount = $fnsModGrp->getRoundAmount($fob_markup_amount);
        $cif_markup_amount = $fnsModGrp->getRoundAmount($cif_markup_amount);

        $tax_amount_base = $sell_unit_price_base * ($tax_percentage / 100);
        $tax_amount_base = $fnsModGrp->getRoundAmount($tax_amount_base);

        $exchange_rate = $currRate->getCurrencyExchageRate
                         ($cpCfg['m.trading.companyCurrency'], $sell_currency);

        $sell_unit_price = $sell_unit_price_base * $exchange_rate;
        $arr['companyCurrency']          = $cpCfg['m.trading.companyCurrency'];
        $arr['ex_fact_markup']           = $ex_fact_markup;
        $arr['fob_markup']               = $fob_markup;
        $arr['cif_markup']               = $cif_markup;
        $arr['ex_fact_markup_amount']    = $ex_fact_markup_amount;
        $arr['fob_markup_amount']        = $fob_markup_amount;
        $arr['cif_markup_amount']        = $cif_markup_amount;
        $arr['tax_amount_base']          = $tax_amount_base;
        $arr['sell_unit_price']          = $fnsModGrp->getFormattedNumber($sell_unit_price);
        $arr['sell_unit_price_base_vat'] = $fnsModGrp->getFormattedNumber($sell_unit_price_base + $tax_amount_base);
        $arr['sell_price_base']          = $fnsModGrp->getFormattedNumber($sell_unit_price_base * $quantity);

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

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');
        $recalculate = $fn->getReqParam('recalculate');

        $arr = array();

        $currRate = getCPModelObj('trading_currencyRate');
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

        $buy_price            = 0;
        $buy_price_base       = 0;
        $sell_price           = 0;
        $sell_price_base      = 0;
        $buy_unit_price_base  = 0;
        $sell_unit_price_base = 0;
        $markup               = 0;

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

                $markup              = $fn->getSettingsValueByKey('defaultMarkupPercent');
                $buy_price_base_temp = $buy_price_base + $other_costs_base;

                $sell_price_base      = $buy_price_base_temp + ($buy_price_base_temp * ($markup / 100));
                $sell_unit_price_base = $sell_price_base / $quantity;

                $sell_price      = $sell_price_base * $exchange_rate_base_to_sell;
                $sell_unit_price = $sell_price / $quantity;
            }
        }

        $arr['buy_price']           = $fnsModGrp->getRoundAmount($buy_price, 3, true);
        $arr['buy_price_base']      = $fnsModGrp->getRoundAmount($buy_unit_price_base, 3, true);
        $arr['buy_unit_price_base'] = $fnsModGrp->getRoundAmount($buy_unit_price_base);

        $arr['sell_unit_price']      = $fnsModGrp->getRoundAmount($sell_unit_price);
        $arr['sell_price']           = $fnsModGrp->getRoundAmount($sell_price, 3, true);
        $arr['sell_price_base']      = $fnsModGrp->getRoundAmount($sell_price_base, 3, true);
        $arr['sell_unit_price_base'] = $fnsModGrp->getRoundAmount($sell_unit_price_base, 3, true);
        $arr['markup']               = $fnsModGrp->getRoundAmount($markup, 2);

        $arr['other_costs_1_base'] = $fnsModGrp->getRoundAmount($other_costs_1_base, 3, true);
        $arr['other_costs_2_base'] = $fnsModGrp->getRoundAmount($other_costs_2_base, 3, true);
        $arr['other_costs_3_base'] = $fnsModGrp->getRoundAmount($other_costs_3_base, 3, true);

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

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $arr = array();

        $currRate = getCPModelObj('trading_currencyRate');
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
        $arr['buy_price']          = $fnsModGrp->getRoundAmount($buy_price, 3);
        $arr['buy_price_base']      = $fnsModGrp->getRoundAmount($buy_price_base, 3);
        $arr['buy_unit_price_base'] = $fnsModGrp->getRoundAmount($buy_unit_price_base);

        return $cpUtil->getJsonFromArray($arr);

    }

    function getColorSQL() {
        $SQL = "
        SELECT DISTINCT color
        FROM product
        WHERE color IS NOT NULL
          AND color != ''
        ORDER BY color
        ";

        return $SQL;
    }

    /**
     *
     */
    function getImportData(){
        $db = Zend_Registry::get('db');
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');


        $fa = array(
              'product_code'     => $phpExcel->getImportFldObj('Product Code')
             ,'web_code'         => $phpExcel->getImportFldObj('Web Code')
             ,'title'            => $phpExcel->getImportFldObj('Product Name')
             ,'collection_name'  => $phpExcel->getImportFldObj('Collection Name')
             ,'category_id'      => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'  => $phpExcel->getImportFldObj('Sub Category')
             ,'unit'             => $phpExcel->getImportFldObj('UOM')
             ,'unit_qty'         => $phpExcel->getImportFldObj('UOM Quantity')
             ,'dimension_h'      => $phpExcel->getImportFldObj('H')
             ,'dimension_w'      => $phpExcel->getImportFldObj('W')
             ,'dimension_d'      => $phpExcel->getImportFldObj('D')
             ,'cbm_per_pc'       => $phpExcel->getImportFldObj('CBM per pc')
             ,'hardware'         => $phpExcel->getImportFldObj('Hardware')
             ,'material'         => $phpExcel->getImportFldObj('Material')
             ,'color'            => $phpExcel->getImportFldObj('Color')
             ,'color_inside'     => $phpExcel->getImportFldObj('Color Inside')
             ,'status'           => $phpExcel->getImportFldObj('Status')
             ,'status'           => $phpExcel->getImportFldObj('Description')
             ,'origin'           => $phpExcel->getImportFldObj('Origin')
             ,'dynasty'          => $phpExcel->getImportFldObj('Dynasty')
             ,'circa'            => $phpExcel->getImportFldObj('Circa')
             ,'website_comments' => $phpExcel->getImportFldObj('Website Comments')
        );

        /******** SPECIAL MANIPULATIONS ********/
        $fa['collection_name']['specialType'] = 'valuelist';
        $fa['collection_name']['exp'] = array(
             'keyText' => 'collection'
        );

        $fa['unit']['specialType'] = 'valuelist';
        $fa['unit']['exp'] = array(
             'keyText' => 'productUnit'
        );

        $fa['hardware']['specialType'] = 'valuelist';
        $fa['hardware']['exp'] = array(
             'keyText' => 'hardware'
        );

        $fa['category_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['category_id']['exp']['refModule'] = 'webBasic_category';

        /****************************************/
        $config = array(
             'module' => 'trading_product'
            ,'matchFieldArr' => array('product_code')
            ,'mandatoryFldsArr' => array('product_code')
            ,'fldsArr' => $fa
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function getStartDataMigration(){
        set_time_limit(20000);
        $db = Zend_Registry::get('db');
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $text = '';

        $fa = array(
              'product_code'     => $phpExcel->getImportFldObj('Product Code')
             ,'web_code'         => $phpExcel->getImportFldObj('Web Code')
             ,'title'            => $phpExcel->getImportFldObj('Product Name')
             ,'collection_name'  => $phpExcel->getImportFldObj('Collection Name')
             ,'category_id'      => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'  => $phpExcel->getImportFldObj('Sub Category')
             ,'unit'             => $phpExcel->getImportFldObj('UOM')
             ,'hardware'         => $phpExcel->getImportFldObj('Hardware')
             ,'unit_qty'         => $phpExcel->getImportFldObj('UOM Quantity')
             ,'stock_qty'        => $phpExcel->getImportFldObj('Stock Quantity')
             ,'origin'           => $phpExcel->getImportFldObj('Origin')
             ,'dynasty'          => $phpExcel->getImportFldObj('Dynasty')
             ,'circa'            => $phpExcel->getImportFldObj('Circa')
             ,'material'         => $phpExcel->getImportFldObj('Material')
             ,'color'            => $phpExcel->getImportFldObj('Color')
             ,'color_inside'     => $phpExcel->getImportFldObj('Color Inside')
             ,'dimension_h'      => $phpExcel->getImportFldObj('Dimension H')
             ,'dimension_w'      => $phpExcel->getImportFldObj('Dimension W')
             ,'dimension_d'      => $phpExcel->getImportFldObj('Dimension D')
             ,'cbm_per_pc'       => $phpExcel->getImportFldObj('CBM per pc')
             ,'status'           => $phpExcel->getImportFldObj('Status')
             ,'location'         => $phpExcel->getImportFldObj('Location')
             ,'wholesale_price'  => $phpExcel->getImportFldObj('Wholesale Price')
             ,'trade_price'      => $phpExcel->getImportFldObj('Trade Price')
             ,'retail_price'     => $phpExcel->getImportFldObj('Retail Price')
             ,'contract_price'   => $phpExcel->getImportFldObj('Contract Price')
             ,'website_comments' => $phpExcel->getImportFldObj('Website Comments')
             ,'client_name'      => $phpExcel->getImportFldObj('Client')
             ,'supplier_name'    => $phpExcel->getImportFldObj('Supplier')
             ,'quantity'         => $phpExcel->getImportFldObj('Quantity')
             ,'buy_currency'     => $phpExcel->getImportFldObj('Buy Currency')
             ,'buy_unit_price'   => $phpExcel->getImportFldObj('Buy Unit Price')
        );

        /******** SPECIAL MANIPULATIONS ********/
        $fa['collection_name']['specialType'] = 'valuelist';
        $fa['collection_name']['exp'] = array(
             'keyText' => 'collection'
        );
        $fa['stock_qty']['refOnly'] = true;

        $fa['unit']['specialType'] = 'valuelist';
        $fa['unit']['exp'] = array(
             'keyText' => 'productUnit'
        );

        $fa['hardware']['specialType'] = 'valuelist';
        $fa['hardware']['exp'] = array(
             'keyText' => 'hardware'
        );

        $fa['category_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['category_id']['exp']['refModule'] = 'webBasic_category';

        $config = array(
             'module' => 'trading_product'
            ,'mandatoryFldsArr' => array('product_code')
            ,'fldsArr' => $fa
            ,'tableName' => 'product_temp'
            ,'keyField' => 'product_temp_id'
            ,'callbackBeforeInsert' => 'callbackBeforeInsertDataMigration'
            ,'callbackAfterInsert' => 'callbackAfterInsertDataMigration'
            ,'callbackAfterImport' => 'callbackAfterDataMigrationImport'
        );

//        $filePath = '../../resources/data/temp.xls';
//        $filePath = realpath($filePath);
//        self::$import_no = 1;
//        $config['excelFilePath'] = $filePath;
//        $text = $phpExcel->importData($config);

        $filePath = '../../resources/data/Updated Product - Populated - In Stock & SOR - Updated 29.11.11.xls';
        $filePath = realpath($filePath);
        self::$import_no = 1;
        $config['excelFilePath'] = $filePath;
        $text = $phpExcel->importData($config);

        $filePath = '../../resources/data/product - populated - QDUK044 - New Shipment - Updated 22.11.11.xls';
        $filePath = realpath($filePath);
        self::$import_no = 2;
        $config['excelFilePath'] = $filePath;
        $text = $phpExcel->importData($config);

        $filePath = '../../resources/data/Updated Product - Yin Yang - In China & On Order - Updated 14.11.11.xls';
        $filePath = realpath($filePath);
        self::$import_no = 3;
        $config['excelFilePath'] = $filePath;
        $text = $phpExcel->importData($config);

        $filePath = '../../resources/data/Updated - In China - Antiques - Updated 14.11.11.xls';
        $filePath = realpath($filePath);
        self::$import_no = 4;
        $config['excelFilePath'] = $filePath;
        $text = $phpExcel->importData($config);

        return $text;

    }

    function callbackBeforeInsertDataMigration(&$row) {
        $row['status'] = strtolower($row['status']);
        if ($row['status'] == 'on reserve') {
            $row['status'] = 'on enquiry';
        }
        if ($row['location'] != 'SOR') {
            $row['location'] = strtolower($row['location']);
        }

    }

    function callbackAfterInsertDataMigration($product_id_temp, $row) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        //create supplier record
        $SQL = "
        SELECT company_name
        FROM company
        WHERE company_name = '{$row['supplier_name']}'
        LIMIT 1
        ";
        $rowComp = $fn->getRecordBySQL($SQL);
        if (!$rowComp) {
            $company_code = 'C' . $fn->getSequenceFromSettings('m.trading.company.nextCode');

            $fa = array();
            $fa['company_code'] = $company_code;
            $fa['company_name'] = $row['supplier_name'];
            $fa['category']     = 'Supplier';
            $fa['status']       = 'active';
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'company');
            $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'company');
            $db->sql_query($SQL);
        }

        $SQL = "
        UPDATE company
        SET buy_currency = '{$row['buy_currency']}'
        WHERE company_name = '{$row['supplier_name']}'
        ";
        $db->sql_query($SQL);

        //create customer record
        $SQL = "
        SELECT company_name
        FROM company
        WHERE company_name = '{$row['client_name']}'
        LIMIT 1
        ";
        $rowComp = $fn->getRecordBySQL($SQL);
        if (!$rowComp) {
            $company_code = 'C' . $fn->getSequenceFromSettings('m.trading.company.nextCode');

            $fa = array();
            $fa['company_code'] = $company_code;
            $fa['company_name'] = $row['client_name'];
            $fa['category']     = 'Customer';
            $fa['status']       = 'active';
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'company');
            $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'company');
            $db->sql_query($SQL);
        }

        $SQL = "
        UPDATE company
        SET sell_currency = 'GBP'
        WHERE company_name = '{$row['client_name']}'
        ";
        $db->sql_query($SQL);

        //update product_temp
        $exchange_rate = getCPModelObj('trading_currencyRate')
                         ->getCurrencyExchageRate($row['buy_currency'], $cpCfg['m.trading.companyCurrency']);

        $buy_unit_price_base = $exchange_rate * $row['buy_unit_price'];
        $retail_price = $row['retail_price'] / 1.2; //take 20% VAT off to make it retail_price ex. VAT

        $SQL = "
        UPDATE product_temp
        SET buy_unit_price_base = {$buy_unit_price_base}
           ,retail_price = {$retail_price}
        WHERE product_temp_id = {$product_id_temp}
        ";
        $db->sql_query($SQL);


    }

    function callbackAfterDataMigrationImport() {
        $db = Zend_Registry::get('db');

        $import_no = self::$import_no;

        include_once 'ProductImport.php';
        $SQL = "
        UPDATE product_temp
        SET import_no = {$import_no}
        WHERE import_no IS NULL
        ";
        $db->sql_query($SQL);

        $prodImport = new CP_Admin_Modules_Trading_Product_ProductImport();
        $prodImport->importData($import_no);

    }

    /**
     * /admin/index.php?_topRm=inventory&module=trading_product&_spAction=tempImportProductImages
     */
    function getTempImportProductImages(){
        set_time_limit(20000);

        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        //$SQL = "SELECT * FROM product";
        $SQL = "
        SELECT * FROM product
        ";
        $imgFolder = realpath('../../resources/product images/large');

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $web_code = $row['web_code'];

            $fileName = "{$web_code}.jpg";
            $sourceFilePath = $imgFolder . "/{$fileName}";

            $product_id = $row['product_id'];

            $hasMediaRec = $media->model->hasMediaRecord('trading_product', 'picture', $product_id);
            if (!$hasMediaRec) {
                $this->getTempImportCreateImages($sourceFilePath, $fileName, $product_id);

                for ($i = 1; $i <= 10; $i++) {
                    $fileName = "{$web_code}-{$i}.jpg";
                    $sourceFilePath = $imgFolder . "/{$fileName}";
                    //print $sourceFilePath . "<Br>";
                    $this->getTempImportCreateImages($sourceFilePath, $fileName, $product_id);
                }
            }

        } //while

        print "Images Imported<br>";
    }

    function getTempImportCreateImages($sourceFilePath, $fileName, $product_id){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        if (file_exists($sourceFilePath)) {
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $fileName
                ,'resizeImageCallback' => array($this, 'getTempImportImageCallback')
            );


            $media->model->createMedia('trading_product', 'picture', $product_id, $exp);
            print $fileName . "<br>";
        }

    }

    function getTempImportImageCallback($media_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        $rowMedia = $fn->getRecordRowByID('media', 'media_id', $media_id);

        $fileName = $rowMedia['actual_file_name'];

        $folderSource = '/www-disk/inetpub/Apache/qingdao/resources/product images/media/';
        $folderDest = '/www-disk/inetpub/Apache/qingdao/httpdocs/media/';

        $media_id = $rowMedia['media_id'];
        $sourceFile = $folderSource . 'large/' . $fileName;
        $destFile   = "{$folderDest}large/{$media_id}_{$fileName}";
        copy ($sourceFile, $destFile);

        $sourceFile = $folderSource . 'normal/' . $fileName;
        $destFile   = "{$folderDest}normal/{$media_id}_{$fileName}";
        copy ($sourceFile, $destFile);

        $sourceFile = $folderSource . 'thumb/' . $fileName;
        $destFile   = "{$folderDest}thumb/{$media_id}_{$fileName}";
        copy ($sourceFile, $destFile);


    }

    function getTradingProductTradingInventoryLinkSQL($id) {
        $SQL = "
        SELECT p.product_id
              ,p.product_code
              ,i.inventory_id
              ,i.serial_no
              ,p.title AS product_name
              ,i.status
              ,i.location
              ,p.unit
        FROM inventory i
        JOIN product p ON p.product_id = i.product_id
        WHERE p.product_id = {$id}
        ORDER BY i.serial_no
        ";
        return $SQL;
    }

    function getExportData(){
        $fn = Zend_Registry::get('fn');
        $cpPaths = Zend_Registry::get('cpPaths');

        $tbsExcel = includeCPClass('Lib', 'TBSExcelExportWrapper', 'TBSExcelExportWrapper');

        $output_file_name = 'Product_' . date('d-m-Y') . '.xlsx';
        //$template = __DIR__ . '/assets/product.xlsx';
        $template = $cpPaths->getTheLastFile('modules', 'trading_product', 'assets/product.xlsx');

        $config = array(
             'output_file_name' => $output_file_name
            ,'dataArray' => $this->dataArray
            ,'template' => $template
        );

        return $tbsExcel->exportData($config);
    }

    function getExportForWeb(){
        $cpPaths = Zend_Registry::get('cpPaths');
        $fn = Zend_Registry::get('fn');
        $modelHelper = Zend_Registry::get('modelHelper');

        $modelHelper->setModuleDataArray();

        $tbsExcel = includeCPClass('Lib', 'TBSExcelExportWrapper', 'TBSExcelExportWrapper');

        $output_file_name = 'Product_Web_' . date('d-m-Y') . '.xlsx';
        //$template = __DIR__ . '/assets/product_web.xlsx';
        $template = $cpPaths->getTheLastFile('modules', 'trading_product', 'assets/product_web.xlsx');

        $config = array(
             'output_file_name' => $output_file_name
            ,'dataArray' => $this->dataArray
            ,'template' => $template
        );

        return $tbsExcel->exportData($config);
    }


    /**
     *
     */
    function getCalculateProductCosting($pref = 'pt_') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fmg = includeCPClass('ModGroup', 'Trading', 'Functions');

        $arr = array();

        $currRate = getCPModelObj('trading_currencyRate');
        $buy_currency   = $fn->getPostParam("{$pref}buy_currency");
        $buy_unit_price = $fn->getPostParam("{$pref}buy_unit_price");
        $other_costs_1_curr = $fn->getPostParam("{$pref}other_costs_1_curr");
        $other_costs_1      = $fn->getPostParam("{$pref}other_costs_1");
        $other_costs_2_curr = $fn->getPostParam("{$pref}other_costs_2_curr");
        $other_costs_2      = $fn->getPostParam("{$pref}other_costs_2");
        $other_costs_3_curr = $fn->getPostParam("{$pref}other_costs_3_curr");
        $other_costs_3      = $fn->getPostParam("{$pref}other_costs_3");
        $agent_comm_percentage = (float)$fn->getPostParam("{$pref}agent_comm_percentage");
        $qc_comm_percentage    = (float)$fn->getPostParam("{$pref}qc_comm_percentage");
        $local_charges_percentage  = $fn->getPostParam("{$pref}local_charges_percentage");
        $shipping_cost_percentage  = $fn->getPostParam("{$pref}shipping_cost_percentage");
        $insurance_cost_percentage = $fn->getPostParam("{$pref}insurance_cost_percentage");
        $tax_percentage = $fn->getPostParam("{$pref}tax_percentage");

        $exchange_rate = $currRate->getCurrencyExchageRate(
                                            $buy_currency,
                                            $cpCfg['m.trading.companyCurrency']);
        $buy_unit_price_base = $buy_unit_price * $exchange_rate;

        //-------//
        $exchange_rate_other_costs_1 = $currRate->getCurrencyExchageRate(
                                            $other_costs_1_curr,
                                            $cpCfg['m.trading.companyCurrency']);
        $other_costs_1_base = $other_costs_1 * $exchange_rate_other_costs_1;

        //-------//
        $exchange_rate_other_costs_2 = $currRate->getCurrencyExchageRate(
                                            $other_costs_2_curr,
                                            $cpCfg['m.trading.companyCurrency']);
        $other_costs_2_base = $other_costs_2 * $exchange_rate_other_costs_2;

        //-------//
        $exchange_rate_other_costs_3 = $currRate->getCurrencyExchageRate(
                                            $other_costs_3_curr,
                                            $cpCfg['m.trading.companyCurrency']);
        $other_costs_3_base = $other_costs_3 * $exchange_rate_other_costs_3;

        //-------//
        $sell_unit_price_total_net_cost_base = $buy_unit_price_base
                                                + $other_costs_1_base
                                                + $other_costs_2_base
                                                + $other_costs_3_base;
        //-------//
        $agent_comm_base = $sell_unit_price_total_net_cost_base *
                              ($agent_comm_percentage / 100);

        //-------//
        $qc_comm_base = $sell_unit_price_total_net_cost_base *
                           ($qc_comm_percentage / 100);

        //-------//
        $sell_unit_price_ex_fact_base = $sell_unit_price_total_net_cost_base
                                         + $agent_comm_base
                                         + $qc_comm_base;

        //-------//
        $local_charges_base = $sell_unit_price_ex_fact_base *
                                 ($local_charges_percentage / 100);

        //-------//
        $sell_unit_price_fob_base = $sell_unit_price_ex_fact_base
                                     + $local_charges_base;

        //-------//
        $shipping_cost_base = $sell_unit_price_fob_base *
                                 ($shipping_cost_percentage / 100);

        //-------//
        $insurance_cost_base = $sell_unit_price_fob_base *
                                 ($insurance_cost_percentage / 100);

        //-------//
        $sell_unit_price_cif_base = $sell_unit_price_fob_base
                                     + $shipping_cost_base
                                     + $insurance_cost_base;

        //-------//
        $tax_amount_base = $sell_unit_price_cif_base *
                              ($tax_percentage / 100);

//        $arr["{$pref}buy_unit_price_base"] = $fmg->getRoundAmount($buy_unit_price_base);
//        $arr["{$pref}other_costs_1_base"] = $fmg->getRoundAmount($other_costs_1_base);
//        $arr["{$pref}other_costs_2_base"] = $fmg->getRoundAmount($other_costs_2_base);
//        $arr["{$pref}other_costs_3_base"] = $fmg->getRoundAmount($other_costs_3_base);
//        $arr["{$pref}sell_unit_price_total_net_cost_base"] =
//        $fmg->getRoundAmount($sell_unit_price_total_net_cost_base);
//        $arr["{$pref}agent_comm_base"] = $fmg->getRoundAmount($agent_comm_base);
//        $arr["{$pref}qc_comm_base"] = $fmg->getRoundAmount($qc_comm_base);
//        $arr["{$pref}sell_unit_price_ex_fact_base"] =
//        $fmg->getRoundAmount($sell_unit_price_ex_fact_base);
//        $arr["{$pref}local_charges_base"] = $fmg->getRoundAmount($local_charges_base);
//        $arr["{$pref}sell_unit_price_fob_base"] = $fmg->getRoundAmount($sell_unit_price_fob_base);
//        $arr["{$pref}shipping_cost_base"] = $fmg->getRoundAmount($shipping_cost_base);
//        $arr["{$pref}insurance_cost_base"] = $fmg->getRoundAmount($insurance_cost_base);
//        $arr["{$pref}sell_unit_price_cif_base"] = $fmg->getRoundAmount($sell_unit_price_cif_base);
//        $arr["{$pref}tax_amount_base"] = $fmg->getRoundAmount($tax_amount_base);


        $arr["{$pref}buy_unit_price_base"] = $fmg->getFormattedNumber($buy_unit_price_base);
        $arr["{$pref}other_costs_1_base"]  = $fmg->getFormattedNumber($other_costs_1_base);
        $arr["{$pref}other_costs_2_base"]  = $fmg->getFormattedNumber($other_costs_2_base);
        $arr["{$pref}other_costs_3_base"]  = $fmg->getFormattedNumber($other_costs_3_base);
        $arr["{$pref}sell_unit_price_total_net_cost_base"] = $fmg->getFormattedNumber($sell_unit_price_total_net_cost_base);
        $arr["{$pref}agent_comm_base"] = $fmg->getFormattedNumber($agent_comm_base);
        $arr["{$pref}qc_comm_base"] = $fmg->getFormattedNumber($qc_comm_base);
        $arr["{$pref}sell_unit_price_ex_fact_base"] =
                            $fmg->getFormattedNumber($sell_unit_price_ex_fact_base);
        $arr["{$pref}local_charges_base"] = $fmg->getFormattedNumber($local_charges_base);
        $arr["{$pref}sell_unit_price_fob_base"] = $fmg->getFormattedNumber($sell_unit_price_fob_base);
        $arr["{$pref}shipping_cost_base"] = $fmg->getFormattedNumber($shipping_cost_base);
        $arr["{$pref}insurance_cost_base"] = $fmg->getFormattedNumber($insurance_cost_base);
        $arr["{$pref}sell_unit_price_cif_base"] = $fmg->getFormattedNumber($sell_unit_price_cif_base);
        $arr["{$pref}tax_amount_base"] = $fmg->getFormattedNumber($tax_amount_base);

        return $arr;
    }

    /**
     *
     */
    function getCalculateProductMarkup($product_id = null) {
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $fmg = includeCPClass('ModGroup', 'Trading', 'Functions');

        if (!$product_id) {
            $product_id = $fn->getReqParam('product_id');
        }

        $costingArr = $this->getCalculateProductCosting();
        $sell_unit_price_base_arr = $fn->getPostParam('sell_unit_price_base', array());

        $pt_sell_unit_price_ex_fact_base = $costingArr['pt_sell_unit_price_ex_fact_base'];
        $pt_sell_unit_price_fob_base     = $costingArr['pt_sell_unit_price_fob_base'];
        $pt_sell_unit_price_cif_base     = $costingArr['pt_sell_unit_price_cif_base'];

        $pt_tax_percentage = $fn->getPostParam('pt_tax_percentage');

        $sell_unit_price_base_no_tax = 0;

        //get $sell_unit_price_base_no_tax
        foreach ($sell_unit_price_base_arr as $pricing_type_id => $sell_unit_price_base) {
            $rowPT = $fn->getRecordRowByID('pricing_type', 'pricing_type_id', $pricing_type_id);
            if ($rowPT['record_type'] == 'no_tax') {
                $sell_unit_price_base_no_tax = $sell_unit_price_base;
                break;
            }
        }


        //calculate markup and calculated cost
        $markupArr = array();
        foreach ($sell_unit_price_base_arr as $pricing_type_id => $sell_unit_price_base) {
            $rowPT = $fn->getRecordRowByID('pricing_type', 'pricing_type_id', $pricing_type_id);

            $arr1 = array();
            $ex_fact_markup = 0;
            $fob_markup = 0;
            $cif_markup = 0;
            $calculated_cost = 0;

            if ($rowPT['record_type'] == 'no_tax') {
                $calculated_cost = '';
            } else if ($rowPT['record_type'] == 'has_tax') {
                $calculated_cost = $sell_unit_price_base_no_tax +
                                   ($sell_unit_price_base_no_tax * ($pt_tax_percentage/100));
            } else {
                $calculated_cost = $sell_unit_price_base_no_tax -
                                   ($sell_unit_price_base_no_tax * ($rowPT['discount_percent']/100));
            }

            if ($sell_unit_price_base > 0) {
                if ($pt_sell_unit_price_ex_fact_base) {
                    $ex_fact_markup = (($sell_unit_price_base - $pt_sell_unit_price_ex_fact_base) /
                                        $sell_unit_price_base) * 100;
                }
                if ($pt_sell_unit_price_fob_base) {
                    $fob_markup = (($sell_unit_price_base - $pt_sell_unit_price_fob_base) /
                                    $sell_unit_price_base) * 100;
                }
                if ($pt_sell_unit_price_cif_base) {
                    $cif_markup = (($sell_unit_price_base - $pt_sell_unit_price_cif_base) /
                                    $sell_unit_price_base) * 100;
                }
            }
            $arr1['record_type']     = $rowPT['record_type'];
            $arr1['ex_fact_markup']  = $fmg->getRoundAmount($ex_fact_markup);
            $arr1['fob_markup']      = $fmg->getRoundAmount($fob_markup);
            $arr1['cif_markup']      = $fmg->getRoundAmount($cif_markup);
            $arr1['calculated_cost'] = $fmg->getRoundAmount($calculated_cost);

            $markupArr[$pricing_type_id] = $arr1;
        }

        return $markupArr;

    }

    function getCreateProductPricingTypeHistory($product_id){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT pt.*
              ,ppt.product_id
        FROM pricing_type pt
        LEFT JOIN product_pricing_type ppt
          ON ppt.pricing_type_id = pt.pricing_type_id AND ppt.product_id = {$product_id}
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            if (!$row['product_id']) {
                $fa = array();
                $fa['product_id']      = $product_id;
                $fa['pricing_type_id'] = $row['pricing_type_id'];
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_pricing_type');
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_pricing_type');
                $db->sql_query($SQL);
            }
        }
    }


    function getPricingTypesArr($product_id, $costingArr = array()) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $pt_sell_unit_price_ex_fact_base = $fn->getVariable($costingArr['pt_sell_unit_price_ex_fact_base'], 0);
        $pt_sell_unit_price_fob_base     = $fn->getVariable($costingArr['pt_sell_unit_price_fob_base'], 0);
        $pt_sell_unit_price_cif_base     = $fn->getVariable($costingArr['pt_sell_unit_price_cif_base'], 0);

        $pt_tax_percentage  = $fn->getVariable($costingArr['pt_tax_percentage'], 0);

        $SQL = "
        SELECT ppt.sell_unit_price_base AS sell_unit_price_base_no_tax
        FROM product_pricing_type ppt
        JOIN pricing_type pt ON pt.pricing_type_id = ppt.pricing_type_id
        WHERE ppt.product_id = {$product_id}
          AND pt.record_type = 'no_tax'
        ";
        $row = $fn->getRecordBySQL($SQL);

        $sell_unit_price_base_no_tax = $fn->getVariable($row['sell_unit_price_base_no_tax'], 0);

        //,((ppt.sell_unit_price_base / {$pt_sell_unit_price_ex_fact_base}) - 1) * 100
        $SQL = "
        SELECT pt.pricing_type_id
              ,pt.pricing_type
              ,pt.discount_percent
              ,pt.record_type
              ,ppt.sell_unit_price_base
              ,((ppt.sell_unit_price_base - {$pt_sell_unit_price_ex_fact_base}) /
                 ppt.sell_unit_price_base) * 100  AS ex_fact_markup
              ,((ppt.sell_unit_price_base - {$pt_sell_unit_price_fob_base}) /
                 ppt.sell_unit_price_base) * 100 AS fob_markup
              ,((ppt.sell_unit_price_base - {$pt_sell_unit_price_cif_base}) /
                 ppt.sell_unit_price_base) * 100 AS cif_markup
              ,CASE
               WHEN pt.record_type = 'no_tax' THEN ''
               WHEN pt.record_type = 'has_tax' THEN
                   {$sell_unit_price_base_no_tax} +
                   ({$sell_unit_price_base_no_tax} * ({$pt_tax_percentage} / 100))
               ELSE
                   {$sell_unit_price_base_no_tax} -
                   ({$sell_unit_price_base_no_tax} * (pt.discount_percent / 100))
               END AS calculated_cost
        FROM pricing_type pt
        JOIN product_pricing_type ppt ON (ppt.pricing_type_id = pt.pricing_type_id)
        WHERE ppt.product_id = {$product_id}
        ORDER BY pt.sort_order
        ";
        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        return $dataArray;

    }

    function getSaveCostBreakdown(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $product_id = $fn->getReqParam('product_id');

        $fa = array();
        $fa['pt_other_costs_1_label']       = $fn->getPostParam('pt_other_costs_1_label');
        $fa['pt_other_costs_1_curr']        = $fn->getPostParam('pt_other_costs_1_curr');
        $fa['pt_other_costs_1']             = $fn->getPostParam('pt_other_costs_1');
        $fa['pt_other_costs_1_base']        = $fn->getPostParam('pt_other_costs_1_base');
        $fa['pt_other_costs_2_label']       = $fn->getPostParam('pt_other_costs_2_label');
        $fa['pt_other_costs_2_curr']        = $fn->getPostParam('pt_other_costs_2_curr');
        $fa['pt_other_costs_2']             = $fn->getPostParam('pt_other_costs_2');
        $fa['pt_other_costs_2_base']        = $fn->getPostParam('pt_other_costs_2_base');
        $fa['pt_other_costs_3_label']       = $fn->getPostParam('pt_other_costs_3_label');
        $fa['pt_other_costs_3_curr']        = $fn->getPostParam('pt_other_costs_3_curr');
        $fa['pt_other_costs_3']             = $fn->getPostParam('pt_other_costs_3');
        $fa['pt_other_costs_3_base']        = $fn->getPostParam('pt_other_costs_3_base');
        $fa['pt_sell_unit_price_total_net_cost_base'] =
        $fn->getPostParam('pt_sell_unit_price_total_net_cost_base');
        $fa['pt_agent_comm_percentage']        = $fn->getPostParam('pt_agent_comm_percentage');
        $fa['pt_agent_comm_base']              = $fn->getPostParam('pt_agent_comm_base');
        $fa['pt_qc_comm_percentage']           = $fn->getPostParam('pt_qc_comm_percentage');
        $fa['pt_qc_comm_base']                 = $fn->getPostParam('pt_qc_comm_base');
        $fa['pt_sell_unit_price_ex_fact_base'] = $fn->getPostParam('pt_sell_unit_price_ex_fact_base');
        $fa['pt_local_charges_percentage']  = $fn->getPostParam('pt_local_charges_percentage');
        $fa['pt_local_charges_base']        = $fn->getPostParam('pt_local_charges_base');
        $fa['pt_sell_unit_price_fob_base']  = $fn->getPostParam('pt_sell_unit_price_fob_base');
        $fa['pt_shipping_cost_percentage']  = $fn->getPostParam('pt_shipping_cost_percentage');
        $fa['pt_shipping_cost_base']        = $fn->getPostParam('pt_shipping_cost_base');
        $fa['pt_insurance_cost_percentage'] = $fn->getPostParam('pt_insurance_cost_percentage');
        $fa['pt_insurance_cost_base']       = $fn->getPostParam('pt_insurance_cost_base');
        $fa['pt_tax_percentage']            = $fn->getPostParam('pt_tax_percentage');
        $fa['pt_sell_unit_price_cif_base']  = $fn->getPostParam('pt_sell_unit_price_cif_base');
        $fa['pt_tax_amount_base']           = $fn->getPostParam('pt_tax_amount_base');

        $whereCondition = "
        WHERE product_id = {$product_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'product', $whereCondition);
        $db->sql_query($SQL);

        //save sell unit price for each pricing type
        $sell_unit_price_base_arr = $fn->getPostParam('sell_unit_price_base', array());

        foreach ($sell_unit_price_base_arr as $pricing_type_id => $sell_unit_price_base) {
            $fa = array();
            $fa['sell_unit_price_base'] = $sell_unit_price_base;
            $whereCondition = "
            WHERE product_id = {$product_id}
              AND pricing_type_id = {$pricing_type_id}
            ";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'product_pricing_type', $whereCondition);
            $db->sql_query($SQL);
        }

        return $cpUtil->getJsonText('success', 'Costing has been saved');

    }

    /**
     *
     */
    function getChooseConfirmedRFQForProduct() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $product_id = $fn->getReqParam('product_id');
        $quote_request_items_id = $fn->getReqParam('quote_request_items_id');
        $checked = $fn->getReqParam('checked');

        // clear all selection as we can choose only one rfq line
        $SQL = "
        UPDATE quote_request_items
        SET product_selected = 0
        WHERE product_id = {$product_id}
        ";
        $db->sql_query($SQL);

        if ($checked == 1) { // if some rfq line clicked
            $SQL = "
            UPDATE quote_request_items
            SET product_selected = 1
            WHERE product_id = {$product_id}
              AND quote_request_items_id = {$quote_request_items_id}
            ";
            $db->sql_query($SQL);

            $SQL = "
            UPDATE product
            SET quote_request_items_id = {$quote_request_items_id}
            WHERE product_id = {$product_id}
            ";
            $db->sql_query($SQL);
        }

        return $cpUtil->getJsonFromArray(array('status' => 'success'));

    }


}
