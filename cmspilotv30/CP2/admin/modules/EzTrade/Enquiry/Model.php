<?
class CP_Admin_Modules_EzTrade_Enquiry_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $fnsModDeliveryAddress = getCPFnObj('ezTrade_deliveryAddressLink');

        $SQL = "
        SELECT e.*
              ,c.company_name AS company_name
              ,cSa.company_name AS sales_agent
              ,CONCAT_WS(' ', con.first_name, con.last_name) AS contact_name
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,{$fnsModDeliveryAddress->getShipToLocationSQLFields('da')} AS ship_to_location
        FROM enquiry e
        LEFT JOIN company c ON e.company_id_customer = c.company_id
      	LEFT JOIN company cSa ON e.company_id_sales_agent = cSa.company_id
        LEFT JOIN contact con ON e.contact_id_customer = con.contact_id
        LEFT JOIN staff s ON e.staff_id = s.staff_id
        LEFT JOIN delivery_address da ON da.delivery_address_id = e.delivery_address_id
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

        $enquiry_id = $fn->getReqParam('enquiry_id');
        $status     = $fn->getReqParam('status');

        if ($enquiry_id != '') {
            $searchVar->sqlSearchVar[] = "e.enquiry_id = {$enquiry_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "e.enquiry_id = {$tv['record_id']}";
        } else {
            if ($status != "") {
                $searchVar->sqlSearchVar[] = "e.status = '{$status}'";
            }
        }

        $searchVar->sortOrder = "e.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('subject', 'Please enter the enquiry title');
        $validate->validateData('company_id_customer', 'Please choose customer name');

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

        $company_id = $fn->getReqParam('company_id_customer');
        $rowComp = $fn->getRecordRowByID('company', 'company_id', $company_id);

        $enquiry_code = 'E' . $fn->getSequenceFromSettings('m.trading.enquiry.nextCode');

        $enquiry_date  = strtotime(date('Y-m-d'));
        $followup_date = strtotime('+7 days', $enquiry_date);

        $fa = $this->getFields();
        $fa['enquiry_code']  = $enquiry_code;
        $fa['sell_currency'] = $rowComp['sell_currency'];
        $fa['status']        = 'new';
        $fa['enquiry_date']  = date('Y-m-d', $enquiry_date);
        $fa['followup_date'] = date('Y-m-d', $followup_date);
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('subject', 'Please enter enquiry title');

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

        $fa = $fn->addToFieldsArray($fa, 'company_id_customer');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_customer');
        $fa = $fn->addToFieldsArray($fa, 'subject');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'enquiry_date');
        $fa = $fn->addToFieldsArray($fa, 'followup_date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'shipping_method');
        $fa = $fn->addToFieldsArray($fa, 'target_shipping_date');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'sell_currency');
        $fa = $fn->addToFieldsArray($fa, 'delivery_address_id');
        $fa = $fn->addToFieldsArray($fa, 'customer_rfq_code');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'company_id_sales_agent');

        return $fa;
    }

    /**
     *
     */
    function getEzTradeEnquiryEzTradeProductLinkSQL($id) {

        $chooseRFQText = "
        CONCAT_WS('',
                  '<a href=\'javascript:Enquiry.chooseRFQForLine(',
                  ep.enquiry_product_id,
                  ')\'>Choose Prev RFQ</a>'
                  )
        ";

        $duplicateText = "
        CONCAT_WS('',
                  '<a href=\'javascript:Enquiry.duplicateLine(',
                  ep.enquiry_product_id,
                  ')\'>Duplicate</a>'
                  )
        ";
        $SQL = "
        SELECT DISTINCT
               ep.enquiry_product_id
              ,CONCAT_WS('-', e.enquiry_code, ep.line_no) AS line_no
              ,p.product_id
              ,p.product_code
              ,p.title AS product_name
              ,ep.quantity
              ,p.color
              ,p.color_inside
              ,ep.status
              ,{$chooseRFQText} AS chooseRFQ
              ,{$duplicateText} AS duplicate
        FROM product p
        JOIN enquiry_product ep ON (ep.product_id = p.product_id)
        JOIN enquiry e          ON (e.enquiry_id = ep.enquiry_id)
        LEFT JOIN company c     ON (c.company_id = ep.company_id_supplier)
        WHERE ep.enquiry_id = {$id}
        ORDER BY ep.line_no
        ";

        return $SQL;
    }

    /**
     *
     */
    function getRaiseRfqListValidation() {
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $enquiry_id = $fn->getReqParam('enquiry_id');

        $status = 'success';
        $errMsg = '';

        //Enquiry header status must NOT be "Closed", "On hold" or "Cancelled"
        $rowEnquiry = $fn->getRecordRowByID('enquiry', 'enquiry_id', $enquiry_id);
        if (   $rowEnquiry['status'] == 'closed'
            || $rowEnquiry['status'] == 'on hold'
            || $rowEnquiry['status'] == 'cancelled') {
            $status = 'error';
            $errMsg = "The Enquiry status is {$rowEnquiry['status']}. You cannot raise RFQ.";
        }

        if ($rowEnquiry['followup_date']) {
            $followup_date = strtotime($rowEnquiry['followup_date']);
            $today = strtotime(date('Y-m-d'));
            if ($followup_date < $today) {
                $status = 'error';
                $errMsg = "'Required Response Date' can't be before TODAY.";
            }

        }

        return $cpUtil->getJsonText($status, '', $errMsg);
    }

    /**
     *
     */
    function getRaiseRfqValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $enquiry_id          = $fn->getReqParam('enquiry_id');
        $company_id_supplier = $fn->getReqParam('company_id_supplier');
        $enquiry_product_ids = $fn->getReqParam('enquiry_product_ids', array());
        $enquiry_product_ids_str = $dbUtil->getArrayAsCommaSeperated($enquiry_product_ids);

        $status = 'success';
        $errMsg = '';

        if (!$company_id_supplier) {
            $status = 'error';
            $errMsg = 'Please choose supplier';
            return array($status, $errMsg);
        }
        if (count($enquiry_product_ids) == 0) {
            $status = 'error';
            $errMsg = 'Please choose some items';
            return array($status, $errMsg);
        }

        //Enquiry line status should not be "Quote Generated", "On hold" or "Cancelled"
        $SQL = "
        SELECT COUNT(*) AS count
        FROM enquiry_product ep
        WHERE ep.enquiry_product_id IN ($enquiry_product_ids_str)
          AND ep.status IN ('quote generated', 'on hold', 'cancelled')
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $status = 'error';
            $errMsg = "Please note the enquiry lines status " .
                      "should not be 'quote generated, on hold or cancelled'";
            return array($status, $errMsg);
        }

        //Request Quantity > 0
        $SQL = "
        SELECT COUNT(*) AS count
        FROM enquiry_product ep
        WHERE ep.enquiry_product_id IN ($enquiry_product_ids_str)
          AND (ep.quantity <= 0 OR ep.quantity IS NULL)
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $status = 'error';
            $errMsg = 'Please note that the Request Quantity must not be zero';
            return array($status, $errMsg);
        }

        //Request Delivery Date > today
        $SQL = "
        SELECT COUNT(*) AS count
        FROM enquiry_product ep
        WHERE ep.enquiry_product_id IN ($enquiry_product_ids_str)
          AND (ep.delivery_date < '{$fn->getISODate()}' OR ep.delivery_date IS NULL)
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $status = 'error';
            $errMsg = 'Please note that the Request Delivery Date should not be in the past or empty';
            return array($status, $errMsg);
        }

//        //UOM must NOT be BLANK
//        $SQL = "
//        SELECT COUNT(*) AS count
//        FROM enquiry_product ep
//        JOIN product p ON (p.product_id = ep.product_id)
//        WHERE ep.enquiry_product_id IN ($enquiry_product_ids_str)
//          AND (p.unit = '' OR p.unit IS NULL)
//        ";
//        $result = $db->sql_query($SQL);
//        $row = $db->sql_fetchrow($result);
//        if ($row['count'] > 0) {
//            $status = 'error';
//            $errMsg = 'Please note that the UOM must not be blank';
//            return array($status, $errMsg);
//        }

        return array($status, $errMsg);
    }

    /**
     *
     */
    function getRaiseRfq() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        list($status, $errMsg) = $this->getRaiseRfqValidation();
        if ($status != 'success') {
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        $deliveryTerms = getCPModuleObj('ezTrade_deliveryTermsLink');
        $enquiry_id          = $fn->getReqParam('enquiry_id');
        $company_id_supplier = $fn->getReqParam('company_id_supplier');
        $enquiry_product_ids = $fn->getReqParam('enquiry_product_ids', array());
        $enquiry_product_ids_str = $dbUtil->getArrayAsCommaSeperated($enquiry_product_ids);

        $rowEnquiry = $fn->getRecordRowByID('enquiry', 'enquiry_id', $enquiry_id);
        $rowSupplier = $fn->getRecordRowByID('company', 'company_id', $company_id_supplier);
        $SQL = $deliveryTerms->model->getDeliveryTermsSupplierSQL($company_id_supplier);
        $quote_request_code = 'R' . $fn->getSequenceFromSettings('m.trading.rfq.nextCode');

        $rowDeliveryTermsSupplier = $fn->getRecordBySQL($SQL);
        $quote_request_date = strtotime(date('Y-m-d'));
        $followup_date      = strtotime('+7 days', $quote_request_date);

        //create RFQ header
        $fa = array();
        $fa['enquiry_id']               = $enquiry_id;
        $fa['quote_request_code']       = $quote_request_code;
        $fa['company_id_supplier']      = $company_id_supplier;
        $fa['quote_request_date']       = date('Y-m-d', $quote_request_date);
        $fa['followup_date']            = date('Y-m-d', $followup_date);
        $fa['target_shipping_date']     = $rowEnquiry['target_shipping_date'];
        $fa['buy_currency']             = $rowSupplier['buy_currency'];
        $fa['title']                    = $rowEnquiry['subject'];
        $fa['shipping_method']          = '';
        $fa['required_shipping_method'] = $rowEnquiry['shipping_method'];
        $fa['status']                   = 'new';
        $fa['notes_to_supplier']        = $rowEnquiry['description'];
        $fa['followup_date']            = $rowEnquiry['followup_date'];
        $fa['delivery_terms_supplier']  = $rowEnquiry['delivery_terms'];
        $fa['required_delivery_terms']  = $rowEnquiry['delivery_terms'];
        $fa['delivery_address_id']      = $rowEnquiry['delivery_address_id'];
        $fa['notes_to_supplier']        = $rowEnquiry['description'];
        $fa['staff_id']                 = $_SESSION['staff_id'];
        $fa['creation_date']            = date('Y-m-d H:i:s');
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'quote_request');
        $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'quote_request');
        $db->sql_query($SQL);
        $quote_request_id = $db->sql_nextid();

        //create RFQ line items
        $SQL = "
        SELECT ep.*
        FROM enquiry_product ep
        WHERE ep.enquiry_id = {$enquiry_id}
          AND ep.enquiry_product_id IN ($enquiry_product_ids_str)
        ORDER BY ep.line_no
        ";
        $result = $db->sql_query($SQL);

        $fnsModGrp = includeCPClass('ModGroup', 'EzTrade', 'Functions');

        while ($rowEnqProd = $db->sql_fetchrow($result)) {
            $quantity = $rowEnqProd['quantity'];

            $line_no = $fnsModGrp->getNextItemLineNo(
                       'quote_request_id',
                       $quote_request_id,
                       'quote_request_items');
            $fa = array();
            $fa['quote_request_id']      = $quote_request_id;
            $fa['quantity']              = $quantity;
            $fa['product_id']            = $rowEnqProd['product_id'];
            $fa['enquiry_product_id']    = $rowEnqProd['enquiry_product_id'];
            $fa['packing_requirement']   = $rowEnqProd['packing_requirement'];
            $fa['notes_to_supplier']     = $rowEnqProd['remark'];
            $fa['request_delivery_date'] = $rowEnqProd['delivery_date'];
            $fa['line_no']               = $line_no;
            $fa['status']                = 'new';

            $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'quote_request_items');
            $db->sql_query($SQL);
            $quote_request_items_id = $db->sql_nextid();

            //add RFQ line to the short listed RFQs for the enquiry line
            $fa = array();
            $fa['quote_request_items_id'] = $quote_request_items_id;
            $fa['enquiry_id']            = $rowEnqProd['enquiry_id'];
            $fa['enquiry_product_id']    = $rowEnqProd['enquiry_product_id'];
            $fa['creation_date']         = date('Y-m-d H:i:s');
            $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'quote_request_items_selected');
            $db->sql_query($SQL);

        }

        $topRm = $fn->getTopRoomName('rfq');
        $url = "index.php?_topRm={$topRm}&module=ezTrade_rfq" .
               "&_action=edit&record_id={$quote_request_id}";

        $arr = array('status' => 'success', 'returnUrl' => $url);
        return $cpUtil->getJsonFromArray($arr);

    }

    /**
     *
     */
    function getRaiseQuoteListValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');

        $enquiry_id = $fn->getReqParam('enquiry_id');

        $status = 'success';
        $errMsg = '';

        $rowEnquiry = $fn->getRecordRowByID('enquiry', 'enquiry_id', $enquiry_id);

        //Enquiry header status must NOT be "Closed", "On hold" or "Cancelled"
        if (   $rowEnquiry['status'] == 'closed'
            || $rowEnquiry['status'] == 'on hold'
            || $rowEnquiry['status'] == 'cancelled') {
            $status = 'error';
            $errMsg = "The Enquiry status is {$rowEnquiry['status']}. You cannot raise Quote.";
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        //Enquiry header Sell Currency must NOT be BLANK
        if ($rowEnquiry['sell_currency'] == '') {
            $status = 'error';
            $errMsg = "The Sell Currency should not be empty.";
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        return $cpUtil->getJsonText($status, '', $errMsg);

    }

    /**
     *
     */
    function getRaiseQuoteValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $enquiry_id          = $fn->getReqParam('enquiry_id');
        $company_id_supplier = $fn->getReqParam('company_id_supplier');
        $ids                 = $fn->getReqParam('ids', array());
        list($product_ids, $enquiry_product_ids) =  $cpUtil->getSplitValuesFromArray($ids);

        $enquiry_product_ids_str = $dbUtil->getArrayAsCommaSeperated($enquiry_product_ids);

        $status = 'success';
        $errMsg = '';

        if (count($product_ids) == 0) {
            $status = 'error';
           $errMsg = 'Please choose some items';
            return array($status, $errMsg);
        }

        //Enquiry line status must be "RFQ selected" or "Quote Generated"
        $SQL = "
        SELECT COUNT(*) AS count
        FROM enquiry_product ep
        WHERE ep.enquiry_product_id IN ($enquiry_product_ids_str)
          AND ep.status NOT IN ('rfq selected', 'quote generated')
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $status = 'error';
            $errMsg = "Please note the enquiry lines status must be 'rfq selected' or 'quote generated'";
            return array($status, $errMsg);
        }

        //There must be one and ONLY one selected RFQ line linked to that enquiry line
        //The one and ONLY one selected RFQ line status must have status "Quote confirmed"
        //The one and ONLY one selected RFQ line must be still "Valid"
        $SQL = "
        SELECT COUNT(*) AS count
        FROM enquiry_product ep
        JOIN quote_request_items qri ON (qri.quote_request_items_id = ep.quote_request_items_id)
        JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        WHERE ep.enquiry_product_id IN ($enquiry_product_ids_str)
          AND qri.status = 'quote confirmed'
          AND qr.valid_until >= '{$fn->getISODate()}'
          AND qr.valid_until IS NOT NULL
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($enquiry_product_ids)) {
            $status = 'error';
            $errArr = array();
            $errArr[] = 'enquiry line must have a valid RFQ.';
            $errArr[] = "RFQ line must have status 'quote confirmed'.";
            $errMsg = "
            Please note the following:<br/>
            {$cpUtil->getArrayAsUl($errArr)}
            ";
            return array($status, $errMsg);
        }

        //Request Quantity > 0
        $SQL = "
        SELECT COUNT(*) AS count
        FROM enquiry_product ep
        WHERE ep.enquiry_product_id IN ($enquiry_product_ids_str)
          AND (ep.quantity <= 0 OR ep.quantity IS NULL)
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $status = 'error';
            $errMsg = 'Please note that the Request Quantity must not be zero';
            return array($status, $errMsg);
        }

//        //UOM must NOT be BLANK
//        $SQL = "
//        SELECT COUNT(*) AS count
//        FROM enquiry_product ep
//        JOIN product p ON (p.product_id = ep.product_id)
//        WHERE ep.enquiry_product_id IN ($enquiry_product_ids_str)
//          AND (p.unit = '' OR p.unit IS NULL)
//        ";
//        $result = $db->sql_query($SQL);
//        $row = $db->sql_fetchrow($result);
//        if ($row['count'] > 0) {
//            $status = 'error';
//            $errMsg = 'Please note that the UOM must not be blank';
//            return array($status, $errMsg);
//        }

        return array($status, $errMsg);
    }

    /**
     *
     */
    function getRaiseQuote() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        list($status, $errMsg) = $this->getRaiseQuoteValidation();
        if ($status != 'success') {
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        $enquiry_id = $fn->getReqParam('enquiry_id');
        $ids        = $fn->getReqParam('ids', array());
        list($product_ids, $enquiry_product_ids) =  $cpUtil->getSplitValuesFromArray($ids);

        $enquiry_product_ids_str = $dbUtil->getArrayAsCommaSeperated($enquiry_product_ids);

        $rowEnquiry  = $fn->getRecordRowByID('enquiry', 'enquiry_id', $enquiry_id);
        $rowCustomer = $fn->getRecordRowByID('company', 'company_id', $rowEnquiry['company_id_customer']);

        $quote_code = 'Q' . $fn->getSequenceFromSettings('m.trading.quote.nextCode');

        $quote_date    = strtotime(date('Y-m-d'));
        $followup_date = strtotime('+7 days', $quote_date);

        //create quote
        $fa = array();
        $fa['enquiry_id']           = $enquiry_id;
        $fa['quote_code']           = $quote_code;
        $fa['quote_date']           = date('Y-m-d', $quote_date);
        $fa['follow_up_date']       = date('Y-m-d', $followup_date);
        $fa['target_shipping_date'] = $rowEnquiry['target_shipping_date'];
        $fa['sell_currency']        = $rowEnquiry['sell_currency'];
        //$fa['delivery_address_id']  = $rowEnquiry['delivery_address_id'];
        $fa['status']               = 'new';
        $fa['contact_id_customer']  = $rowEnquiry['contact_id_customer'];
        $fa['staff_id']             = $rowEnquiry['staff_id'];
        $fa['description']          = $rowEnquiry['description'];
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'quote');
        $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'quote');
        $db->sql_query($SQL);
        $quote_id = $db->sql_nextid();

        $fnsModGrp = includeCPClass('ModGroup', 'EzTrade', 'Functions');

        //create Quote line items
        $SQL = "
        SELECT ep.*
        FROM enquiry_product ep
        WHERE ep.enquiry_id = {$enquiry_id}
          AND ep.enquiry_product_id IN ($enquiry_product_ids_str)
        ORDER BY ep.line_no
        ";
        $result = $db->sql_query($SQL);

        $rowEnquiryProduct = $db->sql_fetchrow($result);
        $rowQuoteReqItem = $fn->getRecordRowByID('quote_request_items',
                                                 'quote_request_items_id',
                                                 $rowEnquiryProduct['quote_request_items_id']);
        $rowQuoteReq = $fn->getRecordRowByID('quote_request',
                                             'quote_request_id',
                                             $rowQuoteReqItem['quote_request_id']);

        //update notes_customer in the quote header from first RFQ header
        $fa = array();
        $fa['notes_customer'] = $rowQuoteReq['notes_from_supplier'];

        $whereCondition = "WHERE quote_id = {$quote_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'quote', $whereCondition);
        $db->sql_query($SQL);

        $db->sql_rowseek(0, $result);
        while ($rowEnquiryProduct = $db->sql_fetchrow($result)) {
            $rowQuoteReqItem = $fn->getRecordRowByID('quote_request_items',
                                                     'quote_request_items_id',
                                                     $rowEnquiryProduct['quote_request_items_id']);

            $rowQuoteReq = $fn->getRecordRowByID('quote_request',
                                                 'quote_request_id',
                                                 $rowQuoteReqItem['quote_request_id']);

            $options['quantity']       = $rowEnquiryProduct['quantity'];
            $options['buy_unit_price'] = $rowQuoteReqItem['buy_unit_price'];
            $options['buy_currency']   = $rowQuoteReq['buy_currency'];
            $options['sell_currency']  = $rowEnquiry['sell_currency'];
            $options['calcType']       = 'quoteProduct';

            $priceArr = $fnsModGrp->getCalculatedPrices($options);

            $line_no = $fnsModGrp->getNextItemLineNo('quote_id', $quote_id, 'quote_items');
            $fa = array();
            $fa['quote_id']               = $quote_id;
            $fa['product_id']             = $rowEnquiryProduct['product_id'];
            $fa['enquiry_product_id']     = $rowEnquiryProduct['enquiry_product_id'];
            $fa['quote_request_id']       = $rowQuoteReq['quote_request_id'];
            $fa['quote_request_items_id'] = $rowQuoteReqItem['quote_request_items_id'];
            $fa['company_id_supplier']    = $rowEnquiryProduct['company_id_supplier'];
            $fa['line_no']                = $line_no;
            $fa['quantity']               = $rowEnquiryProduct['quantity'];
            $fa['buy_unit_price']         = $rowQuoteReqItem['buy_unit_price'];
            $fa['buy_unit_price_base']     = $priceArr['buy_unit_price_base'];
            $fa['markup']                 = $priceArr['markup'];
            $fa['sell_unit_price']        = $priceArr['sell_unit_price'];
            $fa['sell_unit_price_base']    = $priceArr['sell_unit_price_base'];
            $fa['status']                 = 'new';
            $fa['valid_until']            = $rowQuoteReq['valid_until'];
            $fa['country_of_origin']      = $rowQuoteReqItem['country_of_origin'];
            //$fa['shipping_method']        = $rowQuoteReq['shipping_method'];
            //$fa['delivery_terms']         = $rowQuoteReq['delivery_terms_supplier'];
            $fa['packing_details']        = $rowQuoteReqItem['packing_details'];
            $fa['carton_dimensions']      = $rowQuoteReqItem['carton_dimensions'];
            $fa['gross_weight']           = $rowQuoteReqItem['gross_weight'];
            $fa['net_weight']             = $rowQuoteReqItem['net_weight'];
            $fa['total_volume']           = $rowQuoteReqItem['total_volume'];
            $fa['lead_time']              = $rowQuoteReqItem['lead_time'];
            $fa['note_to_customer']       = $rowQuoteReqItem['notes_from_supplier'];

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
            $db->sql_query($SQL);
            $quote_items_id = $db->sql_nextid();

            $SQL = "
            UPDATE enquiry_product
            SET quote_items_id = {$quote_items_id}
            WHERE enquiry_product_id = {$rowEnquiryProduct['enquiry_product_id']}
            ";
            $db->sql_query($SQL);
        }

        $topRm = $fn->getTopRoomName('quote');
        $url = "index.php?_topRm={$topRm}&module=ezTrade_quote" .
               "&_action=detail&record_id={$quote_id}";
        $arr = array('status' => 'success', 'returnUrl' => $url);
        return $cpUtil->getJsonFromArray($arr);

    }

    /**
     *
     */
    function getDuplicateLine() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $enquiry_product_id_source = $fn->getReqParam('enquiry_product_id');
        $rowEnqLineSrc = $fn->getRecordRowByID('enquiry_product', 'enquiry_product_id', $enquiry_product_id_source);

        $fnsModGrp = includeCPClass('ModGroup', 'EzTrade', 'Functions');

        $fa = array();
        $fa['enquiry_id']          = $rowEnqLineSrc['enquiry_id'];
        $fa['product_id']          = $rowEnqLineSrc['product_id'];
        $fa['delivery_date']       = $rowEnqLineSrc['delivery_date'];
        $fa['status']              = $rowEnqLineSrc['status'];
        $fa['packing_requirement'] = $rowEnqLineSrc['packing_requirement'];
        $fa['remark']              = $rowEnqLineSrc['remark'];
        $fa['creation_date']       = date('Y-m-d H:i:s');
        $fa['modification_date']   = date('Y-m-d H:i:s');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'enquiry_product');
        $db->sql_query($SQL);
        $enquiry_product_id = $db->sql_nextid();

        $line_no = $fnsModGrp->getNextItemLineNo('enquiry_id', $rowEnqLineSrc['enquiry_id'], 'enquiry_product', $enquiry_product_id);
        $fnsModGrp->getUpdateHistoryTableLineNo('enquiry_product', 'enquiry_product_id', $enquiry_product_id, $line_no);

        $arr = array('status' => 'success');
        return $cpUtil->getJsonFromArray($arr);

    }

    /**
     *
     */
    function getChooseRFQFormForLine() {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        //global $db, $fn, $cpUtil, $dbUtil, $tv;

        $enquiry_product_id = $fn->getReqParam('enquiry_product_id');
        $rowEnqProd = $fn->getRecordRowByID('enquiry_product', 'enquiry_product_id', $enquiry_product_id);

        $keyField      = 'enquiry_id';
        $histTableName = 'enquiry_product';
        $id         = $rowEnqProd['enquiry_id'];
        $product_id = $rowEnqProd['product_id'];

        $SQL = "
        SELECT DISTINCT
               c.company_id AS company_id_supplier
              ,c.company_name AS supplier_name
              ,CONCAT_WS('-', qr.quote_request_code, qri.line_no) AS rfq_line_no
              ,qri.quote_request_items_id
              ,qri.min_order_quantity
              ,qri.lead_time
              ,qri.buy_unit_price
              ,qri.quantity
              ,qri.order_multiplier
              ,qri.buy_unit_price_base
              ,qri.status
              ,qr.buy_currency
              ,qr.quote_request_date
              ,qr.valid_until
              ,qr.delivery_terms_supplier
              ,qr.shipping_method

              ,(SELECT 1 FROM quote_request_items_selected qris
                WHERE qris.enquiry_product_id = {$enquiry_product_id}
                  AND qri.quote_request_items_id = qris.quote_request_items_id
                LIMIT 1) AS selected

              ,qri.buy_unit_price
              ,qri.lead_time
              ,qr.valid_until
        FROM quote_request_items qri
        JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        JOIN company c        ON (c.company_id = qr.company_id_supplier)
        WHERE qri.product_id = {$product_id}
          AND qr.valid_until >= '{$fn->getISODate()}'
        ORDER BY qr.valid_until DESC
        ";

        $result = $db->sql_query($SQL);

        $rows = '';

        $rowCounter = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $checked = $row['selected'] == 1 ? "checked='checked'" : '';
            $bgClass = ($rowCounter%2) != 0 ? 'portal-row1' : 'portal-row2';

            $rows .= "
            <tr class='{$bgClass}'>
            <td>{$row['rfq_line_no']}</td>
            <td>{$row['supplier_name']}</td>
            <td>{$row['min_order_quantity']}</td>
            <td>{$row['lead_time']}</td>
            <td>{$row['quantity']}</td>
            <td>{$row['buy_currency']}</td>
            <td>{$row['buy_unit_price']}</td>
            <td>{$row['order_multiplier']}</td>
            <td>{$row['quote_request_date']}</td>
            <td>{$row['buy_unit_price_base']}</td>
            <td>{$row['status']}</td>
            <td>{$row['valid_until']}</td>
            <td>{$row['delivery_terms_supplier']}</td>
            <td>{$row['shipping_method']}</td>
            <td>
            <input class='checkbox' type='checkbox'
                   name='quote_request_items_ids[]'
                   value='{$row['quote_request_items_id']}'
                   {$checked} />
            </td>
            </tr>
            ";
            $rowCounter++;
        }

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnChooseRFQCancel' />
            <input type='button' value='Save' id='btnChooseRFQSave' />
            </div>
        </form>
        ";
        $text = "
        {$raiseBtn}
        <div id='chooseRFQ' class='linkPortalWrapper'>
        <table>
        <tr>
        <th>RFQ Line</th>
        <th>Supplier</th>
        <th>MOQ</th>
        <th>Lead Time</th>
        <th>Quantity</th>
        <th>Buy Currency</th>
        <th>Unit Buy Price</th>
        <th>Order Multiplier</th>
        <th>RFQ Creation Date</th>
        <th>Unit Buy Price ({$cpCfg['m.trading.companyCurrency']})</th>
        <th>RFQ Line Status</th>
        <th>Valid Until</th>
        <th>Delivery Terms</th>
        <th>Shipping Method</th>
        <th>&nbsp;</td>
        </tr>
        {$rows}
        </table>
        <input type='hidden' id='enquiry_product_id' value='{$enquiry_product_id}' />
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getChooseRFQForLine() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $enquiry_product_id      = $fn->getReqParam('enquiry_product_id');
        $quote_request_items_ids = $fn->getReqParam('quote_request_items_ids', array());
        $quote_request_items_ids_str = $dbUtil->getArrayAsCommaSeperated($quote_request_items_ids, false, false, '0');

        $enqProdRow = $fn->getRecordRowByID('enquiry_product', 'enquiry_product_id', $enquiry_product_id);
        $enquiry_id = $enqProdRow['enquiry_id'];

        //delete the untagged quote_request_items_ids
        $SQL = "
        DELETE FROM quote_request_items_selected
        WHERE enquiry_product_id = {$enquiry_product_id}
          AND quote_request_items_id NOT IN ({$quote_request_items_ids_str})
        ";
        $db->sql_query($SQL);

        $dateText = date('Y-m-d H:i:s');

        //insert the selected quote_request_item_ids in quote_request_items_selected table
        //
        $SQL = "
        INSERT INTO quote_request_items_selected
                (enquiry_id, enquiry_product_id, quote_request_items_id, creation_date)
        SELECT {$enquiry_id}, {$enquiry_product_id}, quote_request_items_id, '{$dateText}'
        FROM quote_request_items
        WHERE quote_request_items_id IN ({$quote_request_items_ids_str})
        ";
        $db->sql_query($SQL);
    }

    /**
     *
     */
    function getChooseConfirmedRFQForLine() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $enquiry_product_id     = $fn->getReqParam('enquiry_product_id');
        $quote_request_items_id = $fn->getReqParam('quote_request_items_id');
        $checked = $fn->getReqParam('checked');

        // clear all selection as we can choose only one rfq line
        $SQL = "
        UPDATE enquiry_product
        SET quote_request_items_id = 0
        WHERE enquiry_product_id = {$enquiry_product_id}
        ";
        $db->sql_query($SQL);

        if ($checked == 1) { // if some rfq line clicked
            $SQL = "
            UPDATE enquiry_product
            SET quote_request_items_id = {$quote_request_items_id}
            WHERE enquiry_product_id = {$enquiry_product_id}
            ";
            $db->sql_query($SQL);
        }

    }

    /**
     *
     */
    function getChooseRFQForLine_old() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $enquiry_product_id     = $fn->getReqParam('enquiry_product_id');
        $quote_request_items_id = $fn->getReqParam('quote_request_items_id');

        if (!$quote_request_items_id) { //empty no record selected
            $SQL = "
            UPDATE enquiry_product
            SET quote_request_items_id = 0
            WHERE enquiry_product_id = {$enquiry_product_id}
            ";
            $db->sql_query($SQL);
        } else {

            //------------------------//
            $SQL = "
            UPDATE enquiry_product
            SET quote_request_items_id = {$quote_request_items_id}
            WHERE enquiry_product_id = {$enquiry_product_id}
            ";
            $db->sql_query($SQL);
        }

    }

}
