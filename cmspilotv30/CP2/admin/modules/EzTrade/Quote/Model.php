<?
class CP_Admin_Modules_EzTrade_Quote_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fnsModDeliveryAddress = getCPFnObj('ezTrade_deliveryAddressLink');

        $SQL = "
      	SELECT q.*
      	      ,e.subject
      	      ,e.enquiry_code
      	      ,e.customer_rfq_code
      	      ,c.company_id AS company_id_customer
      	      ,c.company_name AS customer_company_name
      	      ,cSa.company_id AS company_id_sales_agent
      	      ,cSa.company_name AS sales_agent
      	      ,CONCAT_WS(' ', con.first_name, con.last_name) AS customer_contact
      	      ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,{$fnsModDeliveryAddress->getShipToLocationSQLFields('da')} AS ship_to_location
      	FROM quote q
      	JOIN enquiry e ON q.enquiry_id = e.enquiry_id
      	JOIN company c ON e.company_id_customer = c.company_id
      	LEFT JOIN company cSa ON e.company_id_sales_agent = cSa.company_id
      	JOIN contact con ON q.contact_id_customer = con.contact_id
      	LEFT JOIN staff s ON e.staff_id = s.staff_id
        LEFT JOIN delivery_address da ON da.delivery_address_id = q.delivery_address_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');

        $status = $fn->getReqParam('status');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "q.quote_id = {$tv['record_id']}";
        } else {
            if ($status != "") {
                $searchVar->sqlSearchVar[] = "q.status = '{$status}'";
            }
        }

    }

    /**
     *
     */
    function getEditValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        //$validate->validateData('subject', 'Please enter enquiry title');

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
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'quote_date');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_customer');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'notes_customer');
        $fa = $fn->addToFieldsArray($fa, 'sell_currency');
        $fa = $fn->addToFieldsArray($fa, 'delivery_address_id');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms_customer');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');

        return $fa;
    }

    /**
     *
     */
    function getEzTradeQuoteEzTradeProductLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT qi.quote_items_id
              ,CONCAT_WS('-', q.quote_code, qi.line_no) AS line_no
              ,p.product_code
              ,p.product_id
              ,p.title AS product_name
              ,qi.quantity
              ,p.unit
              ,qr.buy_currency
              ,qi.buy_unit_price
              ,qi.buy_unit_price * qi.quantity AS buy_price
              ,q.sell_currency
              ,qi.sell_unit_price
              ,qi.sell_unit_price * qi.quantity AS sell_price
              ,qi.markup
              ,qi.valid_until
              ,qi.status
              ,(SELECT SUM(quantity) FROM quote_items WHERE quote_id = {$id}) AS quantity_sum
              ,(SELECT SUM(buy_unit_price * quantity) FROM quote_items WHERE quote_id = {$id}) AS buy_price_sum
              ,(SELECT SUM(sell_unit_price * quantity) FROM quote_items WHERE quote_id = {$id}) AS sell_price_sum

        FROM quote_items qi
        JOIN quote q          ON (q.quote_id = qi.quote_id)
        JOIN product p        ON (p.product_id = qi.product_id)
        JOIN quote_request qr ON (qr.quote_request_id = qi.quote_request_id)
        WHERE qi.quote_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getRaiseSOListValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');

        $quote_id = $fn->getReqParam('quote_id');
        $rowQuote  = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);

        $status = 'success';
        $errMsg = '';

        $errArr = array();

        //Quote header status should not be closed, "On hold" or "Cancelled"
        if ($rowQuote['status'] == 'closed' || $rowQuote['status'] == 'on hold' || $rowQuote['status'] == 'cancelled') {
            $errArr[] = "The quote status should not be 'closed', 'on hold' or 'cancelled'";
        }

        //Payment Terms should NOT be BLANK
        if ($rowQuote['payment_terms_customer'] == '') {
            $status = 'error';
            $errArr[] = "Payment Terms should not be blank";
        }

        //Sell Currency should NOT be BLANK
        if ($rowQuote['sell_currency'] == '') {
            $status = 'error';
            $errArr[] = "Sell currency should not be blank";
        }

        //-----------------------//
        if (count($errArr) > 0) {
            $status = 'error';
            $errMsg = "
            Please note the following:<br/>
            {$cpUtil->getArrayAsUl($errArr)}
            ";
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        return $cpUtil->getJsonText($status, '', $errMsg);

    }

    /**
     *
     */
    function getRaiseSO() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        list($status, $errMsg) = $this->getRaiseSOValidation();
        if ($status != 'success') {
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        $quote_id        = $fn->getReqParam('quote_id');
        $quote_items_ids = $fn->getReqParam('quote_items_ids', array());
        $quote_items_ids_str = $dbUtil->getArrayAsCommaSeperated($quote_items_ids);

        $SQL = "
        SELECT q.*
              ,e.company_id_customer
              ,e.customer_rfq_code
        FROM quote q
        JOIN enquiry e ON (e.enquiry_id = q.enquiry_id)
        WHERE q.quote_id = {$quote_id}
        ";
        $rowQuote = $fn->getRecordBySQL($SQL);

        $rowCustomer = $fn->getRecordRowByID('company', 'company_id', $rowQuote['company_id_customer']);

        $so_code = 'SO' . $fn->getSequenceFromSettings('m.trading.salesOrder.nextCode');

        $quote_date    = strtotime(date('Y-m-d'));
        $followup_date = strtotime('+7 days', $quote_date);

        //*** create sales order record
        $fa = array();
        $fa['so_code']             = $so_code;
        $fa['quote_id']            = $quote_id;
        $fa['enquiry_id']          = $rowQuote['enquiry_id'];
        $fa['company_id_customer'] = $rowQuote['company_id_customer'];
        $fa['contact_id_customer'] = $rowQuote['contact_id_customer'];
        $fa['title']               = $rowQuote['title'];
        $fa['description']         = $rowQuote['description'];
        $fa['delivery_address_id'] = $rowQuote['delivery_address_id'];
        $fa['sell_currency']       = $rowQuote['sell_currency'];
        $fa['payment_terms']       = $rowQuote['payment_terms_customer'];
        $fa['status']              = 'new';
        $fa['staff_id']            = $rowQuote['staff_id'];
        $fa['sales_order_date']    = date('Y-m-d');
        $fa['consignee_name']           = $rowCustomer['consignee_name'];
        $fa['consignee_address']        = $rowCustomer['consignee_address'];
        $fa['consignee_phone']          = $rowCustomer['consignee_phone'];
        $fa['consignee_contact_person'] = $rowCustomer['consignee_contact_person'];

        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'sales_order');
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'sales_order');

        $result = $db->sql_query($SQL);
        $sales_order_id = $db->sql_nextid();

        //create sales order line items
        $SQL = "
        SELECT qi.*
        FROM quote_items qi
        WHERE qi.quote_id = {$quote_id}
          AND qi.quote_items_id IN ($quote_items_ids_str)
        ORDER BY qi.line_no
        ";
        $result = $db->sql_query($SQL);

        $fnsModGrp = includeCPClass('ModGroup', 'EzTrade', 'Functions');

        while ($rowQI = $db->sql_fetchrow($result)) {
            $line_no = $fnsModGrp->getNextItemLineNo('sales_order_id', $sales_order_id, 'sales_order_items');

            $quantity = $fn->getPostParam("quantity__{$rowQI['quote_items_id']}");

            //get RFQ Details
            $rowQR = $fn->getRecordRowByID('quote_request', 'quote_request_id', $rowQI['quote_request_id']);
            $rowQRI = $fn->getRecordRowByID('quote_request_items', 'quote_request_items_id', $rowQI['quote_request_items_id']);

            $fa = array();
            $fa['sales_order_id']      = $sales_order_id;
            $fa['quote_items_id']      = $rowQI['quote_items_id'];
            $fa['company_id_supplier'] = $rowQI['company_id_supplier'];
            $fa['product_id']          = $rowQI['product_id'];
            $fa['line_no']             = $line_no;
            $fa['quantity']            = $quantity;
            $fa['buy_unit_price']      = $rowQI['buy_unit_price'];
            $fa['markup']              = $rowQI['markup'];
            $fa['sell_unit_price']     = $rowQI['sell_unit_price'];
            $fa['buy_unit_price_base']  = $rowQI['buy_unit_price_base'];
            $fa['sell_unit_price_base'] = $rowQI['sell_unit_price_base'];
            $fa['other_costs_1_label'] = $rowQI['other_costs_1_label'];
            $fa['other_costs_2_label'] = $rowQI['other_costs_2_label'];
            $fa['other_costs_3_label'] = $rowQI['other_costs_3_label'];
            $fa['other_costs_1_curr']  = $rowQI['other_costs_1_curr'];
            $fa['other_costs_2_curr']  = $rowQI['other_costs_2_curr'];
            $fa['other_costs_3_curr']  = $rowQI['other_costs_3_curr'];
            $fa['other_costs_1']       = $rowQI['other_costs_1'];
            $fa['other_costs_2']       = $rowQI['other_costs_2'];
            $fa['other_costs_3']       = $rowQI['other_costs_3'];
            $fa['other_costs_1_base']   = $rowQI['other_costs_1_base'];
            $fa['other_costs_2_base']   = $rowQI['other_costs_2_base'];
            $fa['other_costs_3_base']   = $rowQI['other_costs_3_base'];
            $fa['country_of_origin']   = $rowQI['country_of_origin'];
            $fa['shipping_method']     = $rowQI['shipping_method'];
            $fa['delivery_terms']      = $rowQI['delivery_terms'];

            $fa['packing_details']     = $rowQI['packing_details'];
            $fa['carton_dimensions']   = $rowQI['carton_dimensions'];
            $fa['gross_weight']        = $rowQI['gross_weight'];
            $fa['net_weight']          = $rowQI['net_weight'];
            $fa['total_volume']        = $rowQI['total_volume'];

            $fa['status']              = 'new';

            $fa['creation_date']       = date('Y-m-d H:i:s');
            $fa['modification_date']   = date('Y-m-d H:i:s');

            $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'sales_order_items');
            $db->sql_query($SQL);
        }

        $topRm = $fn->getTopRoomName('salesOrder');
        $url = "index.php?_topRm={$topRm}&module=ezTrade_salesOrder" .
               "&_action=detail&record_id={$sales_order_id}";
        $arr = array('status' => 'success', 'returnUrl' => $url);
        return $cpUtil->getJsonFromArray($arr);

    }

    /**
     *
     */
    function getRaiseSOValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $quote_id            = $fn->getReqParam('quote_id');
        $company_id_supplier = $fn->getReqParam('company_id_supplier');
        $quote_items_ids     = $fn->getReqParam('quote_items_ids', array());
        $quote_items_ids_str = $dbUtil->getArrayAsCommaSeperated($quote_items_ids);

        $status = 'success';
        $errMsg = '';

        if (count($quote_items_ids) == 0) {
            $status = 'error';
            $errMsg = 'Please choose some items';
            return array($status, $errMsg);
        }

        $errArr = array();

        //"Valid Until" date >= today
        $SQL = "
        SELECT COUNT(*) AS count
        FROM quote_items qi
        WHERE qi.quote_items_id IN ($quote_items_ids_str)
          AND qi.valid_until >= '{$fn->getISODate()}'
          AND qi.valid_until IS NOT NULL
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($quote_items_ids)) {
            $errArr[] = 'Quote line should have a valid date';
        }

        //Quote line status should be "Customer confirmed"
        $SQL = "
        SELECT COUNT(*) AS count
        FROM quote_items qi
        WHERE qi.quote_items_id IN ($quote_items_ids_str)
          AND qi.status = 'customer confirmed'
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($quote_items_ids)) {
            $errArr[] = "Quote line should have status 'customer confirmed'";
        }

        //Sell Unit Price > 0
        $SQL = "
        SELECT COUNT(*) AS count
        FROM quote_items qi
        WHERE qi.quote_items_id IN ($quote_items_ids_str)
          AND qi.sell_unit_price > 0
          AND qi.sell_unit_price IS NOT NULL
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($quote_items_ids)) {
            $errArr[] = "Sell unit price should have a valid value";
        }

        //Sales Order Quantity > 0
        $SQL = "
        SELECT COUNT(*) AS count
        FROM quote_items qi
        WHERE qi.quote_items_id IN ($quote_items_ids_str)
          AND qi.quantity > 0
          AND qi.quantity IS NOT NULL
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($quote_items_ids)) {
            $errArr[] = "Quantity should be > 0";
        }

//        //UOM must NOT be BLANK
//        $SQL = "
//        SELECT COUNT(*) AS count
//        FROM quote_items qi
//        JOIN product p ON (p.product_id = qi.product_id)
//        WHERE qi.quote_items_id IN ($quote_items_ids_str)
//          AND (p.unit = '' OR p.unit IS NULL)
//        ";
//        $result = $db->sql_query($SQL);
//        $row = $db->sql_fetchrow($result);
//        if ($row['count'] > 0) {
//            $errArr[] = 'Please note that the UOM must not be blank';
//        }

        //Delivery Terms should NOT be BLANK
        $SQL = "
        SELECT COUNT(*) AS count
        FROM quote_items qi
        WHERE qi.quote_items_id IN ($quote_items_ids_str)
          AND qi.delivery_terms != ''
          AND qi.delivery_terms IS NOT NULL
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($quote_items_ids)) {
            $errArr[] = "Delivery terms should not be blank";
        }

        //Shipping Method should NOT be BLANK
        $SQL = "
        SELECT COUNT(*) AS count
        FROM quote_items qi
        WHERE qi.quote_items_id IN ($quote_items_ids_str)
          AND qi.shipping_method != ''
          AND qi.shipping_method IS NOT NULL
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($quote_items_ids)) {
            $errArr[] = "Shipping method should not be blank";
        }

        //-----------------------//
        if (count($errArr) > 0) {
            $status = 'error';
            $errMsg = "
            Please note the following:<br/>
            {$cpUtil->getArrayAsUl($errArr)}
            ";
            return array($status, $errMsg);
        }

        return array($status, $errMsg);
    }

    /**
     *
     */
    function getDuplicateQuote() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $quote_id_source = $fn->getReqParam('quote_id');

        $rowQuoteSrc = $fn->getRecordRowByID('quote', 'quote_id', $quote_id_source);

        //create header
        $quote_code = 'Q' . $fn->getSequenceFromSettings('m.trading.quote.nextCode');
        $quote_date    = strtotime(date('Y-m-d'));
        $followup_date = strtotime('+7 days', $quote_date);

        $fa = array();
        $fa['quote_code']              = $quote_code;
        $fa['quote_date']              = date('Y-m-d', $quote_date);
        $fa['follow_up_date']          = date('Y-m-d', $followup_date);
        $fa['status']                  = $rowQuoteSrc['status'];
        $fa['creation_date']           = date('Y-m-d');
        $fa['currency_item']           = $rowQuoteSrc['currency_item'];
        $fa['note']                    = $rowQuoteSrc['note'];
        $fa['title']                   = $rowQuoteSrc['title'];
        $fa['description']             = $rowQuoteSrc['description'];
        $fa['enquiry_id']              = $rowQuoteSrc['enquiry_id'];
        $fa['notes_customer']          = $rowQuoteSrc['notes_customer'];
        $fa['target_shipping_date']    = $rowQuoteSrc['target_shipping_date'];
        $fa['supplier_type']           = $rowQuoteSrc['supplier_type'];
        $fa['sell_currency']           = $rowQuoteSrc['sell_currency'];
        $fa['delivery_address_id']     = $rowQuoteSrc['delivery_address_id'];
        $fa['payment_terms_customer']  = $rowQuoteSrc['payment_terms_customer'];
        $fa['shipping_method']         = $rowQuoteSrc['shipping_method'];
        $fa['staff_id']                = $rowQuoteSrc['staff_id'];
        $fa['contact_id_customer']     = $rowQuoteSrc['contact_id_customer'];
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'quote');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'quote');
        $db->sql_query($SQL);
        $quote_id = $db->sql_nextid();

        //create line items
        $SQL = "
        SELECT qi.*
        FROM quote_items qi
        WHERE qi.quote_id = {$quote_id_source}
        ORDER BY qi.line_no
        ";
        $result = $db->sql_query($SQL);

        while ($rowQI = $db->sql_fetchrow($result)) {
            $quantity = $rowQI['quantity'];

            $fa = array();
            $fa['sort_order']               = $rowQI['sort_order'];
            $fa['creation_date']            = date('Y-m-d');
            $fa['title']                    = $rowQI['title'];
            $fa['quote_id']                 = $quote_id;
            $fa['quantity']                 = $rowQI['quantity'];
            $fa['markup']                   = $rowQI['markup'];
            $fa['status']                   = $rowQI['status'];
            $fa['product_id']               = $rowQI['product_id'];
            $fa['other_costs_1_base']        = $rowQI['other_costs_1_base'];
            $fa['other_costs_2_base']        = $rowQI['other_costs_2_base'];
            $fa['other_costs_3_base']        = $rowQI['other_costs_3_base'];
            $fa['buy_unit_price']           = $rowQI['buy_unit_price'];
            $fa['sell_unit_price']          = $rowQI['sell_unit_price'];
            $fa['delivery_terms']           = $rowQI['delivery_terms'];
            $fa['buy_unit_price_base']       = $rowQI['buy_unit_price_base'];
            $fa['sell_unit_price_base']      = $rowQI['sell_unit_price_base'];
            $fa['other_costs_1_label']      = $rowQI['other_costs_1_label'];
            $fa['other_costs_2_label']      = $rowQI['other_costs_2_label'];
            $fa['other_costs_3_label']      = $rowQI['other_costs_3_label'];
            $fa['enquiry_product_id']       = $rowQI['enquiry_product_id'];
            $fa['quote_request_id']         = $rowQI['quote_request_id'];
            $fa['quote_request_items_id']   = $rowQI['quote_request_items_id'];
            $fa['other_costs_1_curr']       = $rowQI['other_costs_1_curr'];
            $fa['other_costs_2_curr']       = $rowQI['other_costs_2_curr'];
            $fa['other_costs_3_curr']       = $rowQI['other_costs_3_curr'];
            $fa['other_costs_1']            = $rowQI['other_costs_1'];
            $fa['other_costs_2']            = $rowQI['other_costs_2'];
            $fa['other_costs_3']            = $rowQI['other_costs_3'];
            $fa['line_no']                  = $rowQI['line_no'];
            $fa['company_id_supplier']      = $rowQI['company_id_supplier'];
            $fa['margin_percent']           = $rowQI['margin_percent'];
            $fa['valid_until']              = $rowQI['valid_until'];
            $fa['note_to_customer']         = $rowQI['note_to_customer'];
            $fa['packing_details']          = $rowQI['packing_details'];
            $fa['carton_dimensions']        = $rowQI['carton_dimensions'];
            $fa['gross_weight']             = $rowQI['gross_weight'];
            $fa['country_of_origin']        = $rowQI['country_of_origin'];
            $fa['delivery_terms']           = $rowQI['delivery_terms'];
            $fa['shipping_method']          = $rowQI['shipping_method'];
            $fa['net_weight']               = $rowQI['net_weight'];
            $fa['total_volume']             = $rowQI['total_volume'];
            $fa['unit_ctn']                 = $rowQI['unit_ctn'];
            $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
            $db->sql_query($SQL);
        }

        $topRm = $fn->getTopRoomName('quote');
        $url = "/admin/index.php?_topRm={$topRm}&module=ezTrade_quote" .
               "&_action=edit&record_id={$quote_id}";

        $arr = array('status' => 'success', 'returnUrl' => $url);
        return $cpUtil->getJsonFromArray($arr);

    }
}
