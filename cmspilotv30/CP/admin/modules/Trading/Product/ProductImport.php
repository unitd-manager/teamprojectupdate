<?php
class CP_Admin_Modules_Trading_Product_ProductImport
{

    private $company_id_internal_customer;
    private $staff_id;
    private $sell_currency = 'GBP';
    private $delivery_terms = 'FOB';
    private $payment_terms = 'DP';
    private $import_no;

    function __construct() {
        $fn = Zend_Registry::get('fn');

        $this->staff_id = $fn->getSessionParam('staff_id');
    }

    /**
     *
     */
    public function importData($import_no){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        $this->import_no = $import_no;

        $this->setInternalCustomerID();
        $this->createProducts();
        $this->createEnquiries();
        $this->createRFQs();
        $this->createQuotes();
        $this->createSalesOrders();

        //inventory records created through purchase order
        $this->createPurchaseOrders();
        $this->createSalesOrdersForClient();

    }

    private function setInternalCustomerID() {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT * FROM company
        WHERE internal_customer = 1
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $this->company_id_internal_customer = $row['company_id'];
    }

    private function createProducts() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT * FROM product_temp
        WHERE import_no = {$this->import_no}
        ORDER BY product_temp_id
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['product_code']     = $row['product_code'];
            $fa['web_code']         = $row['web_code'];
            $fa['title']            = $row['title'];
            $fa['collection_name']  = $row['collection_name'];
            $fa['category_id']      = $row['category_id'];
            $fa['sub_category_id']  = $row['sub_category_id'];
            $fa['unit']             = $row['unit'];
            $fa['origin']           = $row['origin'];
            $fa['dynasty']          = $row['dynasty'];
            $fa['circa']            = $row['circa'];
            $fa['material']         = $row['material'];
            $fa['color']            = $row['color'];
            $fa['color_inside']     = $row['color_inside'];
            $fa['cbm_per_pc']       = $row['cbm_per_pc'];
            $fa['description']      = $row['description'];
            $fa['dimension_h']      = $row['dimension_h'];
            $fa['dimension_w']      = $row['dimension_w'];
            $fa['dimension_d']      = $row['dimension_d'];
            $fa['cbm_per_pc']       = $row['dimension_h'] * $row['dimension_w'] * $row['dimension_d'];
            $fa['status']           = $row['status'];
            $fa['ok_for_web']       = $row['ok_for_web'];
            $fa['hardware']         = $row['hardware'];
            $fa['unit_qty']         = $row['unit_qty'];
            $fa['website_comments'] = $row['website_comments'];
            $fa['pt_tax_percentage']            = $fn->getSettingsValueByKey('cp.defaultTaxPercent');
            $fa['pt_qc_comm_percentage']        = $fn->getSettingsValueByKey('cp.defaultQCPercent');
            $fa['pt_agent_comm_percentage']     = $fn->getSettingsValueByKey('cp.defaultAgentPercent');
            $fa['pt_local_charges_percentage']  = $fn->getSettingsValueByKey('cp.defaultLocalChargesPercent');
            $fa['pt_shipping_cost_percentage']  = $fn->getSettingsValueByKey('cp.defaultShippingPercent');
            $fa['pt_insurance_cost_percentage'] = $fn->getSettingsValueByKey('cp.defaultInsurancePercent');

            $SQL = "
            SELECT product_id
            FROM product
            WHERE product_code = '{$row['product_code']}'
            ";
            $rowTemp = $fn->getRecordBySQL($SQL);
            if ($rowTemp) { // already exists. update record
                $product_id = $rowTemp['product_id'];
                $whereCondition = "
                WHERE product_id = {$product_id}
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'product', $whereCondition);
                $db->sql_query($SQL);

            } else { // new record
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product');
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product');
                $db->sql_query($SQL);
                $product_id = $db->sql_nextid();

                $prodObj = getCPModelObj('trading_product');
                $prodObj->getCreateProductPricingTypeHistory($product_id);

                //update retail price
                $pricing_type_id_rrp_has_vat = 5; //RRP (+VAT)
                $pricing_type_id_rrp_no_vat = 3; //RRP (-VAT)
                $pricing_type_id_wholesale = 1;
                $pricing_type_id_trade = 2;
                $pricing_type_id_contract = 4;

                $retail_price_no_vat  = $row['retail_price'];
                $retail_price_has_vat = $retail_price_no_vat * 1.2; //add VAT 20%
                $retail_price_wholesale = $row['wholesale_price'];
                $retail_price_trade = $row['trade_price'];
                $retail_price_contract = $row['contract_price'];

                $SQL = "
                UPDATE product_pricing_type
                SET sell_unit_price_base = {$retail_price_has_vat}
                WHERE product_id = {$product_id}
                  AND pricing_type_id = {$pricing_type_id_rrp_has_vat}
                ";
                $db->sql_query($SQL);

                $SQL = "
                UPDATE product_pricing_type
                SET sell_unit_price_base = {$retail_price_no_vat}
                WHERE product_id = {$product_id}
                  AND pricing_type_id = {$pricing_type_id_rrp_no_vat}
                ";
                $db->sql_query($SQL);

                $SQL = "
                UPDATE product_pricing_type
                SET sell_unit_price_base = {$retail_price_wholesale}
                WHERE product_id = {$product_id}
                  AND pricing_type_id = {$pricing_type_id_wholesale}
                ";
                $db->sql_query($SQL);

                $SQL = "
                UPDATE product_pricing_type
                SET sell_unit_price_base = {$retail_price_trade}
                WHERE product_id = {$product_id}
                  AND pricing_type_id = {$pricing_type_id_trade}
                ";
                $db->sql_query($SQL);

                $SQL = "
                UPDATE product_pricing_type
                SET sell_unit_price_base = {$retail_price_contract}
                WHERE product_id = {$product_id}
                  AND pricing_type_id = {$pricing_type_id_contract}
                ";
                $db->sql_query($SQL);
            }

            //update product_temp with product_id
            $SQL = "
            UPDATE product_temp
            SET product_id = {$product_id}
            WHERE product_temp_id = {$row['product_temp_id']}
            ";
            $db->sql_query($SQL);

            $this->updateProductCosting($product_id);

        } //while
    }

    /**
     *
     */
    function updateProductCosting($product_id) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fmg = includeCPClass('ModGroup', 'Trading', 'Functions');

        $rowProduct = $fn->getRecordRowByID('product', 'product_id', $product_id);

        $arr = array();

        $currRate = getCPModelObj('trading_currencyRate');
        $buy_currency       = $rowProduct['pt_buy_currency'];
        $buy_unit_price     = $rowProduct['pt_buy_unit_price'];
        $other_costs_1_curr = $rowProduct['pt_other_costs_1_curr'];
        $other_costs_1      = $rowProduct['pt_other_costs_1'];
        $other_costs_2_curr = $rowProduct['pt_other_costs_2_curr'];
        $other_costs_2      = $rowProduct['pt_other_costs_2'];
        $other_costs_3_curr = $rowProduct['pt_other_costs_3_curr'];
        $other_costs_3      = $rowProduct['pt_other_costs_3'];
        $agent_comm_percentage = (float) $rowProduct['pt_agent_comm_percentage'];
        $qc_comm_percentage    = (float) $rowProduct['pt_qc_comm_percentage'];
        $local_charges_percentage  =  $rowProduct['pt_local_charges_percentage'];
        $shipping_cost_percentage  =  $rowProduct['pt_shipping_cost_percentage'];
        $insurance_cost_percentage =  $rowProduct['pt_insurance_cost_percentage'];
        $tax_percentage =  $rowProduct['pt_tax_percentage'];

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

        //update values
        $fa = array();
        $fa["pt_buy_unit_price_base"] = $buy_unit_price_base;
        $fa["pt_other_costs_1_base"]  = $other_costs_1_base;
        $fa["pt_other_costs_2_base"]  = $other_costs_2_base;
        $fa["pt_other_costs_3_base"]  = $other_costs_3_base;
        $fa["pt_sell_unit_price_total_net_cost_base"] = $sell_unit_price_total_net_cost_base;
        $fa["pt_agent_comm_base"]              = $agent_comm_base;
        $fa["pt_qc_comm_base"]                 = $qc_comm_base;
        $fa["pt_sell_unit_price_ex_fact_base"] = $sell_unit_price_ex_fact_base;
        $fa["pt_local_charges_base"]           = $local_charges_base;
        $fa["pt_sell_unit_price_fob_base"]     = $sell_unit_price_fob_base;
        $fa["pt_shipping_cost_base"]           = $shipping_cost_base;
        $fa["pt_insurance_cost_base"]          = $insurance_cost_base;
        $fa["pt_sell_unit_price_cif_base"]     = $sell_unit_price_cif_base;
        $fa["pt_tax_amount_base"]              = $tax_amount_base;

        $whereCondition = "
        WHERE product_id = {$product_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'product', $whereCondition);
        $db->sql_query($SQL);
    }


    private function createEnquiries() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT * FROM product_temp
        WHERE import_no = {$this->import_no}
          AND quantity IS NOT NULL
          AND quantity > 0
        ORDER BY supplier_name
        ";
        $result = $db->sql_query($SQL);
        $supplier_name = '';

        $enquiry_id = 0;
        while ($row = $db->sql_fetchrow($result)) {
            if ($supplier_name != $row['supplier_name']) {
                $enquiry_code = 'E' . $fn->getSequenceFromSettings('m.trading.enquiry.nextCode');
                $subject = 'Stock from supplier: ' . $row['supplier_name'];

                $fa = array();
                $fa['company_id_customer'] = $this->company_id_internal_customer;
                $fa['enquiry_code']    = $enquiry_code;
                $fa['subject']         = $subject;
                $fa['enquiry_date']    = date('Y-m-d');
                $fa['status']          = 'open';
                $fa['staff_id']        = $this->staff_id;
                $fa['shipping_method'] = 'sea';
                $fa['sell_currency']   = $this->sell_currency;
                $fa['delivery_terms']  = $this->delivery_terms;
                $fa['payment_terms']   = $this->payment_terms;
                $fa['tax_percentage']  = $fn->getSettingsValueByKey('cp.defaultTaxPercent');
                $fa['import_no']       = $this->import_no;

                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'enquiry');
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'enquiry');
                $db->sql_query($SQL);
                $enquiry_id = $db->sql_nextid();
                $supplier_name = $row['supplier_name'];
            }

            //create history records
            $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');
            $line_no = $fnsModGrp->getNextItemLineNo(
                'enquiry_id',
                $enquiry_id,
                'enquiry_product'
            );

            $fa = array();
            $fa['enquiry_id'] = $enquiry_id;
            $fa['product_id']  = $row['product_id'];
            $fa['status']      = 'RFQ selected';
            $fa['quantity']    = $row['quantity'];
            $fa['line_no']     = $line_no;
            $fa['record_type'] = 'product';
            $fa['product_temp_id'] = $row['product_temp_id'];

            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'enquiry_product');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'enquiry_product');
            $db->sql_query($SQL);
        } //while
    }

    private function createRFQs() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT * FROM enquiry e
        WHERE e.import_no = {$this->import_no}
        ORDER BY e.enquiry_id
        ";
        $result = $db->sql_query($SQL);
        $supplier_name = '';

        $enquiry_id = 0;
        while ($row = $db->sql_fetchrow($result)) { //while 1
            $deliveryTerms = getCPModuleObj('trading_deliveryTermsLink');
            $enquiry_id = $row['enquiry_id'];

            $SQL = "
            SELECT pt.supplier_name
            FROM enquiry_product ep
            JOIN product p ON p.product_id = ep.product_id
            JOIN product_temp pt ON pt.product_id = p.product_id
            WHERE ep.enquiry_id = {$enquiry_id}
            LIMIT 1
            ";
            $rowTemp = $fn->getRecordBySQL($SQL);

            $company_id_supplier = $this->getCompanyIdByName($rowTemp['supplier_name']);

            $rowEnquiry = $fn->getRecordRowByID('enquiry', 'enquiry_id', $enquiry_id);
            $rowSupplier = $fn->getRecordRowByID('company', 'company_id', $company_id_supplier);
            $SQL = $deliveryTerms->model->getDeliveryTermsSQL($company_id_supplier);
            $quote_request_code = 'R' . $fn->getSequenceFromSettings('m.trading.rfq.nextCode');

            $rowDeliveryTermsSupplier = $fn->getRecordBySQL($SQL);
            $quote_request_date = strtotime(date('Y-m-d'));
            $followup_date      = strtotime('+7 days', $quote_request_date);

            //create RFQ header
            $valid_until = '2012-12-31';
            $fa = array();
            $fa['enquiry_id']               = $enquiry_id;
            $fa['quote_request_code']       = $quote_request_code;
            $fa['company_id_supplier']      = $company_id_supplier;
            $fa['quote_request_date']       = date('Y-m-d');
            $fa['buy_currency']             = $rowSupplier['buy_currency'];
            $fa['title']                    = $rowEnquiry['subject'];
            $fa['required_shipping_method'] = $rowEnquiry['shipping_method'];
            $fa['status']                   = 'open';
            $fa['notes_to_supplier']        = $rowEnquiry['description'];
            $fa['followup_date']            = $rowEnquiry['followup_date'];
            $fa['delivery_terms_supplier']  = $rowEnquiry['delivery_terms'];
            $fa['required_delivery_terms']  = $rowEnquiry['delivery_terms'];
            $fa['staff_id']                 = $this->staff_id;
            $fa['valid_until']              = $valid_until;
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'quote_request');
            $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'quote_request');
            $db->sql_query($SQL);
            $quote_request_id = $db->sql_nextid();

            //create RFQ line items
            $SQL = "
            SELECT ep.*
                  ,p.cbm_per_pc
            FROM enquiry_product ep
            JOIN product p ON p.product_id = ep.product_id
            WHERE ep.enquiry_id = {$enquiry_id}
            ORDER BY ep.line_no
            ";
            $resultHist = $db->sql_query($SQL);

            $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

            while ($rowEnqProd = $db->sql_fetchrow($resultHist)) {
                $quantity = $rowEnqProd['quantity'];

                $line_no = $fnsModGrp->getNextItemLineNo(
                           'quote_request_id',
                           $quote_request_id,
                           'quote_request_items');

                $SQL = "
                SELECT pt.buy_unit_price
                      ,pt.buy_unit_price_base
                FROM enquiry_product ep
                JOIN product p ON p.product_id = ep.product_id
                JOIN product_temp pt ON pt.product_temp_id = ep.product_temp_id
                WHERE ep.enquiry_id = {$enquiry_id}
                  AND ep.enquiry_product_id = {$rowEnqProd['enquiry_product_id']}
                LIMIT 1
                ";
                $rowTemp = $fn->getRecordBySQL($SQL);

                $fa = array();
                $fa['quote_request_id']      = $quote_request_id;
                $fa['quantity']              = $quantity;
                $fa['product_id']            = $rowEnqProd['product_id'];
                $fa['enquiry_product_id']    = $rowEnqProd['enquiry_product_id'];
                $fa['packing_requirement']   = $rowEnqProd['packing_requirement'];
                $fa['notes_to_supplier']     = $rowEnqProd['remark'];
                $fa['request_delivery_date'] = $rowEnqProd['delivery_date'];
                $fa['buy_unit_price']        = $rowTemp['buy_unit_price'];
                $fa['buy_unit_price_base']   = $rowTemp['buy_unit_price_base'];
                $fa['line_no']               = $line_no;
                $fa['status']                = 'new';
                $fa['total_volume']          = $rowEnqProd['cbm_per_pc'] * $quantity;

                $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'quote_request_items');
                $db->sql_query($SQL);
                $quote_request_items_id = $db->sql_nextid();

                //add RFQ line to the short listed RFQs for the enquiry line
                $fa = array();
                $fa['quote_request_items_id'] = $quote_request_items_id;
                $fa['enquiry_id']             = $rowEnqProd['enquiry_id'];
                $fa['enquiry_product_id']     = $rowEnqProd['enquiry_product_id'];
                $fa['creation_date']          = date('Y-m-d H:i:s');
                $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'quote_request_items_selected');
                $db->sql_query($SQL);

                //set the selected RFQ
                $fa = array();
                $fa['quote_request_items_id'] = $quote_request_items_id;

                $whereCondition = "
                WHERE enquiry_product_id = {$rowEnqProd['enquiry_product_id']}
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'enquiry_product', $whereCondition);
                $db->sql_query($SQL);

            }

        } //while 1
    }

    private function createQuotes() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT * FROM enquiry e
        WHERE e.import_no = {$this->import_no}
        ORDER BY e.enquiry_id
        ";
        $result = $db->sql_query($SQL);
        $supplier_name = '';

        $enquiry_id = 0;
        while ($row = $db->sql_fetchrow($result)) { //while 1
            $enquiry_id = $row['enquiry_id'];

            $rowEnquiry = $fn->getRecordRowByID('enquiry', 'enquiry_id', $enquiry_id);
            $rowCustomer = $fn->getRecordRowByID('company', 'company_id', $rowEnquiry['company_id_customer']);
            $quote_code = 'Q' . $fn->getSequenceFromSettings('m.trading.quote.nextCode');

            $quote_date    = strtotime(date('Y-m-d'));
            $followup_date = strtotime('+7 days', $quote_date);

            //create Quote header
            $fa = array();
            $fa['enquiry_id']           = $enquiry_id;
            $fa['quote_code']           = $quote_code;
            $fa['quote_date']           = date('Y-m-d', $quote_date);
            $fa['target_shipping_date'] = $rowEnquiry['target_shipping_date'];
            $fa['sell_currency']        = $rowEnquiry['sell_currency'];
            $fa['delivery_address_id']  = $rowEnquiry['delivery_address_id'];
            $fa['status']               = 'open';
            $fa['contact_id_customer']  = $rowEnquiry['contact_id_customer'];
            $fa['staff_id']             = $this->staff_id;
            $fa['description']          = $rowEnquiry['description'];
            $fa['tax_percentage']       = $rowEnquiry['tax_percentage'];
            $fa['shipping_method']      = $rowEnquiry['shipping_method'];
            $fa['payment_terms_customer'] = $rowEnquiry['payment_terms'];
            $fa['delivery_terms']         = $rowEnquiry['delivery_terms'];

            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'quote');
            $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'quote');
            $db->sql_query($SQL);
            $quote_id = $db->sql_nextid();

            $SQL = "
            SELECT ep.*
            FROM enquiry_product ep
            WHERE ep.enquiry_id = {$enquiry_id}
            ORDER BY ep.line_no
            ";
            $resultHist = $db->sql_query($SQL);

            $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

            while ($rowEnqProd = $db->sql_fetchrow($resultHist)) {
                $quantity = $rowEnqProd['quantity'];

                $line_no = $fnsModGrp->getNextItemLineNo('quote_id', $quote_id, 'quote_items');

                $rowQuoteReqItem = $fn->getRecordRowByID('quote_request_items',
                                                         'quote_request_items_id',
                                                         $rowEnqProd['quote_request_items_id']);
                $rowQuoteReq = $fn->getRecordRowByID('quote_request',
                                                     'quote_request_id',
                                                     $rowQuoteReqItem['quote_request_id']);
                $product_id = $rowEnqProd['product_id'];
                $rowProdTemp = $this->getProductTempRow($product_id);

                $fa = array();
                $fa['quote_id']               = $quote_id;
                $fa['product_id']             = $product_id;
                $fa['enquiry_product_id']     = $rowEnqProd['enquiry_product_id'];
                $fa['quote_request_id']       = $rowQuoteReq['quote_request_id'];
                $fa['quote_request_items_id'] = $rowQuoteReqItem['quote_request_items_id'];
                $fa['company_id_supplier']    = $rowEnqProd['company_id_supplier'];
                $fa['line_no']                = $line_no;
                $fa['quantity']               = $quantity;
                $fa['buy_unit_price']         = $rowQuoteReqItem['buy_unit_price'];
                $fa['buy_unit_price_base']    = $rowQuoteReqItem['buy_unit_price_base'];
                $fa['sell_unit_price']        = $rowProdTemp['retail_price'];
                $fa['sell_unit_price_base']   = $rowProdTemp['retail_price_base'];
                $fa['status']                 = 'customer confirmed';
                $fa['valid_until']            = $rowQuoteReq['valid_until'];
                $fa['country_of_origin']      = $rowQuoteReqItem['country_of_origin'];
                $fa['packing_details']        = $rowQuoteReqItem['packing_details'];
                $fa['carton_dimensions']      = $rowQuoteReqItem['carton_dimensions'];
                $fa['gross_weight']           = $rowQuoteReqItem['gross_weight'];
                $fa['net_weight']             = $rowQuoteReqItem['net_weight'];
                $fa['total_volume']           = $rowQuoteReqItem['total_volume'];
                $fa['lead_time']              = $rowQuoteReqItem['lead_time'];
                $fa['note_to_customer']       = $rowQuoteReqItem['notes_from_supplier'];
                $fa['record_type']            = $rowEnqProd['record_type'];
                $fa['purchase_order_items_id']= $rowEnqProd['purchase_order_items_id'];
                $fa['tax_percentage']            = $fn->getSettingsValueByKey('cp.defaultTaxPercent');
                $fa['agent_comm_percentage']     = $fn->getSettingsValueByKey('cp.defaultAgentPercent');
                $fa['qc_comm_percentage']        = $fn->getSettingsValueByKey('cp.defaultQCPercent');
                $fa['local_charges_percentage']  = $fn->getSettingsValueByKey('cp.defaultLocalChargesPercent');
                $fa['shipping_cost_percentage']  = $fn->getSettingsValueByKey('cp.defaultShippingPercent');
                $fa['insurance_cost_percentage'] = $fn->getSettingsValueByKey('cp.defaultInsurancePercent');
                $fa['product_temp_id'] = $rowEnqProd['product_temp_id'];

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
                $db->sql_query($SQL);
                $quote_items_id = $db->sql_nextid();
            }

        } //while 1
    }

    private function createSalesOrders() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT q.*
              ,e.company_id_customer
              ,e.customer_rfq_code
        FROM quote q
        JOIN enquiry e ON (e.enquiry_id = q.enquiry_id)
        WHERE e.import_no = {$this->import_no}
        ";
        $result = $db->sql_query($SQL);

        while ($rowQuote = $db->sql_fetchrow($result)) { //while 1
            $quote_id = $rowQuote['quote_id'];

            $rowCustomer = $fn->getRecordRowByID('company', 'company_id', $rowQuote['company_id_customer']);

            $so_code = 'SO' . $fn->getSequenceFromSettings('m.trading.salesOrder.nextCode');

            //*** create sales order record
            $fa = array();
            $fa['so_code']             = $so_code;
            $fa['order_type']          = 'general';
            $fa['quote_id']            = $quote_id;
            $fa['enquiry_id']          = $rowQuote['enquiry_id'];
            $fa['company_id_customer'] = $rowQuote['company_id_customer'];
            $fa['contact_id_customer'] = $rowQuote['contact_id_customer'];
            $fa['title']               = $rowQuote['title'];
            $fa['description']         = $rowQuote['description'];
            $fa['delivery_address_id'] = $rowQuote['delivery_address_id'];
            $fa['sell_currency']       = $rowQuote['sell_currency'];
            $fa['payment_terms']       = $rowQuote['payment_terms_customer'];
            $fa['delivery_terms']      = $rowQuote['delivery_terms'];
            $fa['status']              = 'confirmed';
            $fa['staff_id']            = $this->staff_id;
            $fa['tax_percentage']      = $rowQuote['tax_percentage'];
            $fa['sales_order_date']    = date('Y-m-d');
            $fa['consignee_name']               = $rowCustomer['consignee_name'];
            $fa['consignee_address']            = $rowCustomer['consignee_address'];
            $fa['consignee_phone_country_code'] = $rowCustomer['consignee_phone_country_code'];
            $fa['consignee_phone_area_code']    = $rowCustomer['consignee_phone_area_code'];
            $fa['consignee_phone']              = $rowCustomer['consignee_phone'];
            $fa['consignee_contact_person']     = $rowCustomer['consignee_contact_person'];
            $fa['import_no'] = $this->import_no;

            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'sales_order');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'sales_order');
            $db->sql_query($SQL);
            $sales_order_id = $db->sql_nextid();

            //create sales order line items
            $SQL = "
            SELECT qi.*
            FROM quote_items qi
            WHERE qi.quote_id = {$quote_id}
            ORDER BY qi.line_no
            ";
            $resultHist = $db->sql_query($SQL);

            $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

            while ($rowQI = $db->sql_fetchrow($resultHist)) {
                $line_no = $fnsModGrp->getNextItemLineNo('sales_order_id', $sales_order_id, 'sales_order_items');

                //get RFQ Details
                $rowQR = $fn->getRecordRowByID('quote_request', 'quote_request_id', $rowQI['quote_request_id']);
                $rowQRI = $fn->getRecordRowByID('quote_request_items', 'quote_request_items_id', $rowQI['quote_request_items_id']);

                $sell_unit_price      = $rowQI['sell_unit_price'];
                $sell_unit_price_base = $rowQI['sell_unit_price_base'];

                $fa = array();
                $fa['sales_order_id']      = $sales_order_id;
                $fa['quote_items_id']      = $rowQI['quote_items_id'];
                if ($rowQI['record_type'] == 'product') {
                    $fa['quote_request_items_id'] = $rowQRI['quote_request_items_id'];
                } else {
                    $fa['purchase_order_items_id'] = $rowQI['purchase_order_items_id'];
                }

                $fa['company_id_supplier'] = $rowQI['company_id_supplier'];
                $fa['product_id']          = $rowQI['product_id'];
                $fa['line_no']             = $line_no;
                $fa['quantity']            = $rowQI['quantity'];
                $fa['buy_unit_price']      = $rowQI['buy_unit_price'];
                $fa['buy_unit_price_base'] = $rowQI['buy_unit_price_base'];
                $fa['sell_unit_price']      = $sell_unit_price;
                $fa['sell_unit_price_base'] = $sell_unit_price_base;
                $fa['other_costs_1_label'] = $rowQI['other_costs_1_label'];
                $fa['other_costs_2_label'] = $rowQI['other_costs_2_label'];
                $fa['other_costs_3_label'] = $rowQI['other_costs_3_label'];
                $fa['other_costs_1_curr']  = $rowQI['other_costs_1_curr'];
                $fa['other_costs_2_curr']  = $rowQI['other_costs_2_curr'];
                $fa['other_costs_3_curr']  = $rowQI['other_costs_3_curr'];
                $fa['other_costs_1']       = $rowQI['other_costs_1'];
                $fa['other_costs_2']       = $rowQI['other_costs_2'];
                $fa['other_costs_3']       = $rowQI['other_costs_3'];
                $fa['other_costs_1_base']  = $rowQI['other_costs_1_base'];
                $fa['other_costs_2_base']  = $rowQI['other_costs_2_base'];
                $fa['other_costs_3_base']  = $rowQI['other_costs_3_base'];
                $fa['country_of_origin']   = $rowQI['country_of_origin'];

                $fa['packing_details']     = $rowQI['packing_details'];
                $fa['carton_dimensions']   = $rowQI['carton_dimensions'];
                $fa['gross_weight']        = $rowQI['gross_weight'];
                $fa['net_weight']          = $rowQI['net_weight'];
                $fa['total_volume']        = $rowQI['total_volume'];

                $fa['status']              = 'po generated';
                $fa['record_type']         = $rowQI['record_type'];
                $fa['product_temp_id']     = $rowQI['product_temp_id'];

                $fa['creation_date']       = date('Y-m-d H:i:s');
                $fa['modification_date']   = date('Y-m-d H:i:s');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'sales_order_items');
                $db->sql_query($SQL);

            }
        } //while 1
    }

    private function createPurchaseOrders() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT *
        FROM sales_order so
        WHERE so.import_no = {$this->import_no}
        ";
        $result = $db->sql_query($SQL);

        while ($rowSO = $db->sql_fetchrow($result)) { //while 1
            $fnsModDeliveryAddress = getCPFnObj('trading_deliveryAddressLink');

            //get company_id_supplier
            $sales_order_id = $rowSO['sales_order_id'];
            $SQL = "
            SELECT pt.supplier_name
            FROM sales_order_items soi
            JOIN product p ON p.product_id = soi.product_id
            JOIN product_temp pt ON pt.product_id = p.product_id
            WHERE soi.sales_order_id = {$sales_order_id}
            LIMIT 1
            ";
            $rowTemp = $fn->getRecordBySQL($SQL);

            $company_id_supplier = $this->getCompanyIdByName($rowTemp['supplier_name']);

            //get RFQ record
            $SQL = "
            SELECT DISTINCT
                   qr.company_id_supplier
                  ,qr.contact_id_supplier
                  ,qr.payment_terms
                  ,qr.buy_currency
                  ,qr.delivery_address_id
                  ,qr.delivery_terms_supplier
                  ,qr.required_shipping_method
                  ,so.notes_customer AS notes_from_customer
                  ,{$fnsModDeliveryAddress->getShipToLocationSQLFields('da')} AS delivery_address
            FROM sales_order_items soi
            JOIN sales_order so ON (so.sales_order_id = soi.sales_order_id)
            JOIN quote_items qi ON (qi.quote_items_id = soi.quote_items_id)
            JOIN quote_request_items qri ON (qri.quote_request_items_id = qi.quote_request_items_id)
            JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
            LEFT JOIN delivery_address da ON (da.delivery_address_id = qr.delivery_address_id)
            WHERE soi.sales_order_id = {$sales_order_id}
            ";
            $resultTemp = $db->sql_query($SQL);
            $rowQR = $db->sql_fetchrow($resultTemp);
            $company_id_supplier = $rowQR['company_id_supplier'];

            $rowSupplier = $fn->getRecordRowByID('company', 'company_id', $rowQR['company_id_supplier']);

            //create RFQ header
            $po_code = 'PO' . $fn->getSequenceFromSettings('m.trading.purchaseOrder.nextCode');
            $purchase_order_date = strtotime(date('Y-m-d'));

            $fa = array();
            $fa['sales_order_id']      = $sales_order_id;
            $fa['po_code']             = $po_code;
            $fa['company_id_supplier'] = $rowQR['company_id_supplier'];
            $fa['contact_id_supplier'] = $rowQR['contact_id_supplier'];
            $fa['payment_terms']       = $rowQR['payment_terms'];
            $fa['notes']               = $rowQR['notes_from_customer'];
            $fa['delivery_address']    = $rowQR['delivery_address'];
            $fa['purchase_order_date'] = date('Y-m-d', $purchase_order_date);
            $fa['buy_currency']        = $rowQR['buy_currency'];
            $fa['status']              = 'confirmed';
            $fa['staff_id']            = $this->staff_id;
            $fa['creation_date']       = date('Y-m-d H:i:s');
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'purchase_order');
            $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'purchase_order');
            $db->sql_query($SQL);
            $purchase_order_id = $db->sql_nextid();

            //create purchase order line items
            $SQL = "
            SELECT soi.*
                  ,(SELECT qri.quote_request_items_id
                    FROM quote_request_items qri
                    JOIN quote_items qi ON (qi.quote_request_items_id = qri.quote_request_items_id)
                    WHERE qi.quote_items_id = soi.quote_items_id LIMIT 1) AS quote_request_items_id
            FROM sales_order_items soi
            WHERE soi.sales_order_id = {$sales_order_id}
            ORDER BY soi.line_no
            ";
            $resultHist = $db->sql_query($SQL);

            $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

            while ($rowSOI = $db->sql_fetchrow($resultHist)) { //while 2
                $line_no = $fnsModGrp->getNextItemLineNo(
                           'purchase_order_id',
                           $purchase_order_id,
                           'purchase_order_items');

                $rowQRI = $fn->getRecordRowByID('quote_request_items', 'quote_request_items_id', $rowSOI['quote_request_items_id']);

                $fa = array();
                $fa['purchase_order_id']      = $purchase_order_id;
                $fa['quantity']               = $rowSOI['quantity'];
                $fa['product_id']             = $rowSOI['product_id'];
                $fa['sales_order_items_id']   = $rowSOI['sales_order_items_id'];
                $fa['quote_request_items_id'] = $rowSOI['quote_request_items_id'];
                $fa['buy_unit_price']         = $rowSOI['buy_unit_price'];
                $fa['buy_unit_price_base']    = $rowSOI['buy_unit_price_base'];
                $fa['line_no']                = $line_no;
                $fa['status']                 = 'new';
                $fa['notes_to_supplier']      = $rowSOI['note_from_customer'];
                $fa['request_date']           = $rowSOI['request_date'];
                $fa['shipping_method']        = $rowQR['required_shipping_method'];
                $fa['delivery_terms_supplier']= $rowQR['delivery_terms_supplier'];
                $fa['packing_details']        = $rowQRI['packing_details'];
                $fa['carton_dimensions']      = $rowQRI['carton_dimensions'];
                $fa['gross_weight']           = $rowQRI['gross_weight'];
                $fa['net_weight']             = $rowQRI['net_weight'];
                $fa['total_volume']           = $rowQRI['total_volume'];

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'purchase_order_items');
                $db->sql_query($SQL);

            } //while 2

            $this->createInventoryRecords($purchase_order_id);

        } //while 1
    }

    private function createInventoryRecords($purchase_order_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //create inventory records
        $SQL = "
        SELECT poi.sales_order_items_id
              ,so.sales_order_id
              ,poi.purchase_order_id
              ,poi.purchase_order_items_id
              ,poi.quote_items_id
              ,poi.product_id
              ,poi.quantity
              ,so.company_id_customer
              ,po.company_id_supplier
              ,soi.sell_unit_price
              ,soi.product_temp_id
              ,pt.status
              ,pt.location
        FROM purchase_order_items poi
        JOIN purchase_order po ON po.purchase_order_id = poi.purchase_order_id
        JOIN sales_order so ON so.sales_order_id = po.sales_order_id
        JOIN sales_order_items soi ON soi.sales_order_items_id = poi.sales_order_items_id
        JOIN company c ON c.company_id = so.company_id_customer
        JOIN product_temp pt ON pt.product_temp_id = soi.product_temp_id
        WHERE poi.purchase_order_id = {$purchase_order_id}
        ORDER BY poi.line_no
        ";
        $result = $db->sql_query($SQL);

        while ($rowPOI = $db->sql_fetchrow($result)) {
            $quantity = $rowPOI['quantity'];
            $count = 1;

            $SQL = "
            SELECT MAX(serial_no) AS max_serial_no
            FROM inventory
            WHERE product_id = {$rowPOI['product_id']}
            ";
            $row = $fn->getRecordBySQL($SQL);

            $serial = $row['max_serial_no'] + 1;
            while ($count <= $quantity) {
                $fa = array();
                $fa['purchase_order_id']       = $rowPOI['purchase_order_id'];
                $fa['product_id']              = $rowPOI['product_id'];
                $fa['sales_order_id']          = $rowPOI['sales_order_id'];
                $fa['sales_order_items_id']    = $rowPOI['sales_order_items_id'];
                $fa['purchase_order_id']       = $rowPOI['purchase_order_id'];
                $fa['purchase_order_items_id'] = $rowPOI['purchase_order_items_id'];
                $fa['company_id_customer']     = $rowPOI['company_id_customer'];
                $fa['company_id_supplier']     = $rowPOI['company_id_supplier'];
                $fa['serial_no']               = str_pad($serial, 5, '0', STR_PAD_LEFT);
                $fa['status']                  = $rowPOI['status'];
                $fa['location']                = $rowPOI['location'];
                $fa['retail_unit_price']       = $rowPOI['sell_unit_price'];
                $fa['product_temp_id']         = $rowPOI['product_temp_id'];
                $fa['creation_date']           = date('Y-m-d H:i:s');
                $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'inventory');
                $db->sql_query($SQL);
                $count++;
                $serial++;
            } //while 2
        } //while 1

    }

    private function createSalesOrdersForClient() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT client_name
              ,CASE WHEN location = 'SOR' THEN 'SOR'
                    ELSE 'general'
               END  AS order_type
        FROM product_temp
        WHERE client_name != '' AND client_name IS NOT NULL
          AND import_no = {$this->import_no}
          AND quantity IS NOT NULL
          AND quantity > 0
        GROUP BY client_name
        ";
        $result = $db->sql_query($SQL);

        while ($rowSOTemp = $db->sql_fetchrow($result)) { //while 1
            $company_id = $this->getCompanyIdByName($rowSOTemp['client_name']);
            $rowCustomer = $fn->getRecordRowByID('company', 'company_id', $company_id);

            $so_code = 'SO' . $fn->getSequenceFromSettings('m.trading.salesOrder.nextCode');

            $title = 'Sales to client: ' . $rowSOTemp['client_name'];
            //*** create sales order record
            $fa = array();
            $fa['so_code']             = $so_code;
            $fa['order_type']          = $rowSOTemp['order_type'];
            $fa['company_id_customer'] = $company_id;
            $fa['title']               = $title;
            $fa['sell_currency']       = $this->sell_currency;
            $fa['payment_terms']       = $this->payment_terms;
            $fa['delivery_terms']      = $this->delivery_terms;
            $fa['status']              = 'confirmed';
            $fa['staff_id']            = $this->staff_id;
            $fa['tax_percentage']      = $fn->getSettingsValueByKey('cp.defaultTaxPercent');
            $fa['sales_order_date']    = date('Y-m-d');
            $fa['consignee_name']               = $rowCustomer['consignee_name'];
            $fa['consignee_address']            = $rowCustomer['consignee_address'];
            $fa['consignee_phone_country_code'] = $rowCustomer['consignee_phone_country_code'];
            $fa['consignee_phone_area_code']    = $rowCustomer['consignee_phone_area_code'];
            $fa['consignee_phone']              = $rowCustomer['consignee_phone'];
            $fa['consignee_contact_person']     = $rowCustomer['consignee_contact_person'];

            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'sales_order');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'sales_order');
            $db->sql_query($SQL);
            $sales_order_id = $db->sql_nextid();

            $rowPriceType = $fn->getRecordRowByID('pricing_type', 'pricing_type_id', $rowCustomer['pricing_type_id']);

            //create sales order line items
            $SQL = "
            SELECT *
            FROM product_temp
            WHERE client_name = '{$rowSOTemp['client_name']}'
            ORDER BY title
            ";
            $resultHist = $db->sql_query($SQL);

            $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');
            while ($rowPT = $db->sql_fetchrow($resultHist)) {
                $quantity_actual = $rowPT['quantity'];

                //if pricing type is not set the no discount
                $discount_percent = $fn->getIssetParam($rowPriceType, 'discount_percent', 0);
                $sell_unit_price_actual = $rowPT['retail_price'] -
                                          $rowPT['retail_price'] * ($discount_percent / 100);

                for ($i = 1; $i <= $quantity_actual; $i++) {
                    $fa = array();
                    if ($rowSOTemp['order_type'] == 'SOR') {
                        $fa['status']   = 'available';
                        $fa['location'] = 'SOR';
                    } else {
                        $fa['status'] = 'sold';
                    }
                    $fa['sales_order_id_inventory']      = $sales_order_id;
                    $fa['company_id_customer_inventory'] = $company_id;
                    $fa['sell_unit_price_actual']        = $sell_unit_price_actual;
                    $fa['creation_date']       = date('Y-m-d H:i:s');
                    $fa['modification_date']   = date('Y-m-d H:i:s');


                    //get inventory id
                    $SQL = "
                    SELECT i.inventory_id
                    FROM inventory i
                    WHERE i.inventory_id NOT IN (
                        SELECT inventory_id
                        FROM sales_order_inventory
                        WHERE sales_order_id = {$sales_order_id}
                    )
                    AND i.product_temp_id = {$rowPT['product_temp_id']}
                    LIMIT 1
                    ";
                    $rowInventory = $fn->getRecordBySQL($SQL);

                    $whereCondition = "
                    WHERE inventory_id = {$rowInventory['inventory_id']}
                    ";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory', $whereCondition);
                    $db->sql_query($SQL);

                    //create sales_order_inventory history records
                    $fa = array();
                    $fa['sales_order_id'] = $sales_order_id;
                    $fa['inventory_id']   = $rowInventory['inventory_id'];
                    $fa['creation_date']  = date('Y-m-d H:i:s');

                    $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'sales_order_inventory');
                    $db->sql_query($SQL);
                }

            }
        } //while 1
    }

    private function getCompanyIdByName($company_name) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT company_id
        FROM company
        WHERE company_name = '{$company_name}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row) {
            return $row['company_id'];
        } else {
            print "Company: {$company_name} not found";
            print_r(debug_backtrace());
            die();
        }
    }

    private function getProductTempRow($product_id) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT *
        FROM product_temp
        WHERE product_id = {$product_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row) {
            return $row;
        } else {
            die("product temp record: error: not found. Product Id: {$product_id}");
        }
    }

}
