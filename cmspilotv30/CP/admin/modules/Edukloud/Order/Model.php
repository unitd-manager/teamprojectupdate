<?
class CP_Admin_Modules_Edukloud_Order_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        if($cpCfg['m.edukloud.ecommerce.order.orderSqlForEnt']){
            $SQL = "
            SELECT o.*
                  ,gc1.name AS cust_country_name
                  ,gc2.name AS shipping_country_name
                  ,IF(o.contact_id > 0, 'Indvidual', 'Parent') AS contact_type
                  ,(SELECT (SUM(i.invoice_amount))
                   FROM invoice i
                   WHERE i.order_id = o.order_id
                   ) AS order_amount
                  ,(SELECT (SUM(i.invoice_amount))
                   FROM invoice i
                   WHERE i.order_id = o.order_id
                   AND i.status = 'Paid'
                   ) AS order_amount_paid
                   ,(SELECT GROUP_CONCAT(i.invoice_month ORDER BY i.invoice_month SEPARATOR ', ')
                   FROM invoice i
                   WHERE i.order_id = o.order_id
                   AND i.status != 'Paid'
                   AND MONTH(invoice_date) <= MONTH(DATE_ADD(NOW(), INTERVAL 0 MONTH))
                   ) AS invoice_paid_month
            FROM `order` o
            LEFT JOIN geo_country gc1 ON (o.cust_address_country_code = gc1.country_code)
            LEFT JOIN geo_country gc2 ON (o.shipping_address_country_code = gc2.country_code)
            ";
            
            $SQL = "
            SELECT o.*
                  ,gc1.name AS cust_country_name
                  ,gc2.name AS shipping_country_name
                  
                  ,IF(o.contact_id > 0, 'Indvidual', 'Parent') AS contact_type
                  
                  ,(SELECT (SUM(i.invoice_amount))
                   FROM invoice i
                   WHERE i.order_id = o.order_id
                   ) AS order_amount
                   
                  ,(SELECT (SUM(i.invoice_amount))
                   FROM invoice i
                   WHERE i.order_id = o.order_id
                   AND i.status = 'Paid'
                   ) AS order_amount_paid
                   
                   ,(SELECT GROUP_CONCAT(i.invoice_date ORDER BY i.invoice_date SEPARATOR ', ')
                     FROM invoice i
                     WHERE i.order_id = o.order_id
                     AND i.status != 'Paid'
                     AND i.invoice_date <= CURDATE()
                   ) AS invoice_paid_month
                   
            FROM `order` o
            LEFT JOIN geo_country gc1 ON (o.cust_address_country_code = gc1.country_code)
            LEFT JOIN geo_country gc2 ON (o.shipping_address_country_code = gc2.country_code)
            ";
            /* SQL for knowing Sum of paid and Balance */
            $SQL = "
            SELECT o.*
                  ,p.dda
                  ,gc1.name AS cust_country_name
                  ,gc2.name AS shipping_country_name
                  
                  ,IF(o.contact_id > 0, 'Indvidual', 'Parent') AS contact_type
                  
                  ,(SELECT (SUM(i.invoice_amount))
                   FROM invoice i
                   WHERE i.order_id = o.order_id
                   ) AS order_amount
                   
                  ,(SELECT (SUM(i.invoice_amount))
                   FROM invoice i
                   WHERE i.order_id = o.order_id
                   AND i.status = 'Paid'
                   ) AS order_amount_paid
                   
                   ,(SELECT GROUP_CONCAT(i.invoice_date ORDER BY i.invoice_date SEPARATOR ', ')
                     FROM invoice i
                     WHERE i.order_id = o.order_id
                     AND i.status != 'Paid'
                     AND i.invoice_date <= CURDATE()
                   ) AS invoice_paid_month
                   
            FROM `order` o
            LEFT JOIN parent p ON (o.parent_id = p.parent_id)
            LEFT JOIN geo_country gc1 ON (o.cust_address_country_code = gc1.country_code)
            LEFT JOIN geo_country gc2 ON (o.shipping_address_country_code = gc2.country_code)
            ";

            $SQL = "
            SELECT o.*
                  ,p.dda
                  ,p.parent_code
                  ,gc1.name AS cust_country_name
                  ,gc2.name AS shipping_country_name                  
                  ,IF(o.contact_id > 0, 'Indvidual', 'Parent') AS contact_type
            FROM `order` o
            LEFT JOIN parent p ON (o.parent_id = p.parent_id)
            LEFT JOIN geo_country gc1 ON (o.cust_address_country_code = gc1.country_code)
            LEFT JOIN geo_country gc2 ON (o.shipping_address_country_code = gc2.country_code)
            ";
        }
        else{
            $SQL = "
            SELECT o.*
                  ,gc1.name AS cust_country_name
                  ,gc2.name AS shipping_country_name
                  ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
                  ,(SELECT (SUM(i.invoice_amount))
                   FROM invoice i
                   WHERE i.order_id = o.order_id
                   ) AS order_amount
            FROM `order` o
            LEFT JOIN geo_country gc1 ON (o.cust_address_country_code = gc1.country_code)
            LEFT JOIN geo_country gc2 ON (o.shipping_address_country_code = gc2.country_code)
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        #$searchVar->mainTableAlias = 'o';

        $business_id            = $fn->getReqParam('business_id');
	    $organization_id        = $fn->getReqParam('organization_id');
        $business_contact_id    = $fn->getReqParam('business_contact_id');
        $order_date1            = $fn->getReqParam('order_date_1');
        $order_date2            = $fn->getReqParam('order_date_2');
        $order_status           = $fn->getReqParam('order_status');
        $order_type             = $fn->getReqParam('order_type');
        $order_id               = $fn->getReqParam('order_id');
        $payment_method         = $fn->getReqParam('payment_method');

        $searchVar->sqlSearchVar[] = "o.module = 'edukloud_course'";
        
        if ($_SESSION['userGroupType'] != 'Super Administrator') {
            $searchVar->sqlSearchVar[] = "o.site_id = {$_SESSION['cp_site_id']}";
        }

        if ($order_id != "") {
            $searchVar->sqlSearchVar[] = "o.order_id = '{$order_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "o.order_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'o.order_id');

            if ($order_id != '') {
                $searchVar->sqlSearchVar[] = "o.order_id = '{$order_id}'";
            }
            
            if ($payment_method != '') {
                $searchVar->sqlSearchVar[] = "o.payment_method = '{$payment_method}'";
            }
            
            if ($business_id != '') {
                $searchVar->sqlSearchVar[] = "o.business_id = '{$business_id}'";
            }
            
            if ($organization_id != '') {
                $searchVar->sqlSearchVar[] = "org.organization_id = '{$organization_id}'";
            }
            
            if ($business_contact_id != '') {
                $searchVar->sqlSearchVar[] = "o.business_contact_id = '{$business_contact_id}'";
            }
            
            if ($order_date1 != "" && $order_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(o.order_date BETWEEN '{$order_date1}' AND '{$order_date2}')";
            }
            
            if ($order_status != '') {
                $searchVar->sqlSearchVar[] = "o.order_status = '{$order_status}'";
            }
            
            if ($order_type != '') {
                $searchVar->sqlSearchVar[] = "o.order_type = '{$order_type}'";
            }
            
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    o.cust_first_name       LIKE '%{$tv['keyword']}%'  OR
                    o.order_id              LIKE '%{$tv['keyword']}%'  OR
                    o.cust_last_name        LIKE '%{$tv['keyword']}%'  OR
                    o.order_code            LIKE '%{$tv['keyword']}%'  OR
                    o.memo                  LIKE '%{$tv['keyword']}%'  OR
                    o.shipping_first_name   LIKE '%{$tv['keyword']}%'  OR
                    o.shipping_last_name    LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "o.creation_date DESC";
        }

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $validate->resetErrorArray();
        $validate->validateData('order_date', 'Please enter the order date');

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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['order_status'] = 'New';
        $id = $fn->addRecord($fa);

        $order_code = $cpCfg['m.ecommerce.order.codePrefix'] . $id;

        $SQL = "
        UPDATE `order`
        SET order_code = '{$order_code}'
        WHERE order_id = {$id}
        ";
        $result = $db->sql_query($SQL);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('order_id', 'Please enter the title');

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
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'order_date');
        $fa = $fn->addToFieldsArray($fa, 'order_status');
        $fa = $fn->addToFieldsArray($fa, 'order_code');
        $fa = $fn->addToFieldsArray($fa, 'memo');
        $fa = $fn->addToFieldsArray($fa, 'shipping_charge');
        $fa = $fn->addToFieldsArray($fa, 'organization_id');
        $fa = $fn->addToFieldsArray($fa, 'payment_method');

        $fa = $fn->addToFieldsArray($fa, 'cust_first_name');
        $fa = $fn->addToFieldsArray($fa, 'cust_last_name');
        $fa = $fn->addToFieldsArray($fa, 'cust_email');
        $fa = $fn->addToFieldsArray($fa, 'cust_phone');
        $fa = $fn->addToFieldsArray($fa, 'cust_address1');
        $fa = $fn->addToFieldsArray($fa, 'cust_address2');
        $fa = $fn->addToFieldsArray($fa, 'cust_address_city');
        $fa = $fn->addToFieldsArray($fa, 'cust_address_area');
        $fa = $fn->addToFieldsArray($fa, 'cust_address_state');
        $fa = $fn->addToFieldsArray($fa, 'cust_po_code');
        $fa = $fn->addToFieldsArray($fa, 'cust_country_code');

        $fa = $fn->addToFieldsArray($fa, 'shipping_first_name');
        $fa = $fn->addToFieldsArray($fa, 'shipping_last_name');
        $fa = $fn->addToFieldsArray($fa, 'shipping_email');
        $fa = $fn->addToFieldsArray($fa, 'shipping_phone');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address1');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address2');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_area');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_city');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_state');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_country_code');

        return $fa;
    }

    /**
     *
     */
    function getEdukloudOrderEdukloudInsuranceLinkSQL($id) {

        $SQL = "
        SELECT si.student_insurance_id
              ,co.title AS course_title
              ,i.title AS insurance_title
              ,si.code
              ,si.premium_amount
              ,si.insurance_start_date
              ,si.insurance_end_date
        FROM student_insurance si 
        LEFT JOIN `order` o ON (o.order_id = si.order_id)
        LEFT JOIN course co ON (co.course_id = si.course_id)
        LEFT JOIN insurance i ON (i.insurance_id = si.insurance_id)
        WHERE si.order_id = '{$id}'
        ORDER BY si.student_insurance_id
        ";

        return $SQL;
    }
  
    /**
     *
     */
    function getUpdateSubsidyStatus() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $order_item_id = $fn->getReqParam('order_item_id');
        $subsidy_paid_status = $fn->getReqParam('subsidy_paid_status');

        $SQL = "
        UPDATE `order_item`
        SET subdidy_paid = {$subsidy_paid_status}
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);
        
        return $text;
    }

    /**
     *
     */
    function getUpdateInvoiceStatus() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $invoice_id = $fn->getReqParam('invoice_id');
        $invoice_status = $fn->getReqParam('invoice_status');

        $SQL = "
        UPDATE invoice
        SET status = '{$invoice_status}'
        WHERE invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);
        
        return $text;
    }

    /**
     *
     */
    function getEdukloudOrderEdukloudPaymentLinkSQL($id) {

        return "
        SELECT p.payment_id
              ,DATE_FORMAT(p.payment_date, '%d-%M-%Y') AS payment_date
              ,p.amount
        FROM payment p
        JOIN `order` o
        WHERE p.order_id = o.order_id
          AND p.order_id = '{$id}'
        ";

    }
    
    /**
     *
     */
    function getGenerateInvoice() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        set_time_limit(50000);

        $site_id = $fn->getSessionParam('cp_site_id');
        $order_id = $fn->getPostParam('order_id');
        $order_item_ids = $fn->getPostParam('orderItemId');
        //print_r($order_item_ids);
        if (!is_array($order_item_ids)){
            $cpUtil->redirect("index.php?_topRm=finance&module=edukloud_order&order_id={$order_id}&_action=edit");
            return;
        }

        $order_item_id_values = '';
        $total= '';

        foreach($order_item_ids AS $order_item_id){
            $order_item_id_values .= $order_item_id . ',';
        }
        $order_item_id_values = substr($order_item_id_values,0,-1);
        
        //to create invoice
        $SQL = "
        SELECT SUM(qty * unit_price) AS total_amount
        FROM order_item oi
        WHERE oi.order_id = {$order_id}
          AND oi.contact_id IN(
             SELECT contact_id
             FROM order_item
             WHERE order_item_id IN ($order_item_id_values)
          )
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        if ($site_id) {
            $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' AND site_id = '{$site_id}'";
        } else {
            $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        }
        $resultUpdate = $db->sql_query($SQLUpdate);
        $nextInvoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");

        $fa = array();
        $fa['order_id']       = $order_id;
        $fa['invoice_amount'] = $row['total_amount'];
        $fa['invoice_code']   = $nextInvoiceCode;
        $fa['status']         = 'Due';
        $fa['invoice_date']   = date("Y-m-d H:i:s");
        $fa['creation_date']  = date("Y-m-d H:i:s");
        
        $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
        $resultSQL          = $db->sql_query($insertInvoiceSQL);
        $invoice_id         = $db->sql_nextid();
        
        //to create invoice item
        $SQL = "
        SELECT oi.*
        FROM order_item oi
        WHERE oi.order_item_id IN ($order_item_id_values)
        ";
        $result = $db->sql_query($SQL);
         
        while ($row = $db->sql_fetchrow($result)) {
            // To get subsidy and discount for related contacts
            
            $expSubsidy = array('condn' => " AND module='edukloud_subsidy' AND order_id = {$order_id}");
            $orderItemRecSubsidy = $fn->getRecordRowByID('order_item', 'contact_id', 
            $row['contact_id'], $expSubsidy);
            
            $subsidy_cost = $orderItemRecSubsidy['unit_price'];

            $expDiscount = array('condn' => " AND module='edukloud_discount' AND order_id = {$order_id}");
            $orderItemRecSubsidy = $fn->getRecordRowByID('order_item', 'contact_id', $row['contact_id'], $expDiscount);
            
            $discount_cost = $orderItemRecSubsidy['unit_price'];

            $fa = array();
            $fa['invoice_id']     = $invoice_id;
            $fa['qty']            = $row['qty'];
            $fa['unit_price']     = $row['unit_price'];
            $fa['item_title']     = $row['item_title'];
            $fa['contact_id']     = $row['contact_id'];
            $fa['subsidy']        = $subsidy_cost;
            $fa['discount']       = $discount_cost;
            
            $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice_item');
            $resultInsert       = $db->sql_query($insertInvoiceSQL);
        }
        //to update order_item with invoice id
        foreach($order_item_ids AS $order_item_id){
            $fa = array();
            $fa['invoice_id'] = $nextInvoiceCode;
            $fa['invoice_clear_status'] = '';
            
            $whereCondition = "
            WHERE order_item_id = {$order_item_id}
            ";

            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'order_item', $whereCondition);
            $db->sql_query($SQL);
        }
        
        /* To generate media record */
        $SQLOrder = "
        SELECT o.*
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
        FROM `order` o
        WHERE o.order_id = {$order_id}
        ";
        $resultOrder = $db->sql_query($SQLOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);
        
        if ($rowOrder['contact_type'] == 'Company') {
            $this->getGenerateInvoiceForMedia($invoice_id);
        } else {
            $this->getGenerateInvoiceIndividualForMedia($invoice_id);
        }
        
        $cpUtil->redirect("index.php?_topRm=finance&module=edukloud_order&order_id={$order_id}&_action=edit");
    }
    
    /**
     *
     */
    function getGenerateReceiptFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        if (!$this->getGenerateReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $invoiceCodes    = $fn->getPostParam('invoiceCode', array());
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $remarks         = $fn->getPostParam('remarks');
        $amount          = $fn->getPostParam('amount');
        $date            = $fn->getPostParam('date');
        $order_id        = $fn->getReqParam('order_id');
        $bank_name       = $fn->getPostParam('bank_name');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $bank_name       = $fn->getPostParam('bank_name');
        $discount_amount = $fn->getPostParam('discount_amount');
        $discount_for_all_months = $fn->getPostParam('discount_for_all_months');
        
        if ($discount_amount == '') {
            $discount_amount = 0;
        }
        
        // Updating Discount Amount to Invoice records
        if ($discount_for_all_months == 1) {
            $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
    
            $sqlInvoice = "
            SELECT DISTINCT contact_id FROM invoice
            WHERE order_id = ($order_id)
            ";
            $resultInvoice = $db->sql_query($sqlInvoice);
            while($rowInvoice = $db->sql_fetchrow($resultInvoice)) {
                $subSql = "
                SELECT MIN(i.invoice_month) AS starting_month FROM invoice i
                LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
                WHERE i.contact_id = {$rowInvoice['contact_id']}
                  AND i.order_id = {$order_id}
                  AND o.year_of_enrollment = {$orderRec['year_of_enrollment']}
                  AND i.status = 'Due'
                   OR i.status = 'Partial Payment'
                ";
                $resultsubSql = $db->sql_query($subSql);
                $rowsubSql    = $db->sql_fetchrow($resultsubSql);
                
                $sqlUpdate = "
                UPDATE invoice SET discount_amount = {$discount_amount}
                WHERE contact_id = {$rowInvoice['contact_id']}
                  AND order_id = {$order_id}
                  AND invoice_month >= {$rowsubSql['starting_month']}
                ";
                $resultUpdate = $db->sql_query($sqlUpdate);
            }
        } else {
            foreach($invoiceCodes AS $invoice_code){
                $invoiceRec = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}' AND add_registration_fee IS NULL");
                
                $faDiscAmt = array();
                $faDiscAmt['discount_amount'] = $discount_amount;                
                $fn->saveRecord($faDiscAmt, 'invoice', 'invoice_id', $invoiceRec['invoice_id']);
            }
        }

        /* Finding total discount for all the selected invoices */
        $invoice_code = $this->getSeparationOfInvoiceCodes($invoiceCodes);
        
        $sqlTotalDisc = "
        SELECT SUM(discount_amount) as total_discount_amount
        FROM invoice
        WHERE invoice_code IN ($invoice_code)
        ";
        $resultTotalDisc       = $db->sql_query($sqlTotalDisc);
        $rowTotalDisc          = $db->sql_fetchrow($resultTotalDisc);
        $total_discount_amount = $rowTotalDisc['total_discount_amount'];
        
        $sqlSiteId = "
        SELECT site_id FROM invoice
        WHERE invoice_code IN ($invoice_code)
        ";
        $resultSiteId = $db->sql_query($sqlSiteId);
        $rowSiteId    = $db->sql_fetchrow($resultSiteId);
        
        $receiptCode    = $this->getFindReceiptCodeWithSite($rowSiteId['site_id']);
        $receiptCodePfx = $this->getReceiptCodePrefixWithSite($rowSiteId['site_id']);

        //To update receipt codes
        $receiptCode = $fn->getSettingsValueByKey("nextReceiptCode");

        if($receiptCode < 10) {
            $receipt_code = $receiptCodePfx . '000' . $receiptCode;
        } else if($receiptCode < 99) {
            $receipt_code = $receiptCodePfx . '00' . $receiptCode;
        } else if($receiptCode < 999) {
            $receipt_code = $receiptCodePfx . '0' . $receiptCode;
        } else {
            $receipt_code = $receiptCodePfx . $receiptCode;
        }
        
        $fa = array();
        if ($rowSiteId['site_id']) {
            $site_id        = $rowSiteId['site_id'];
            $fa['site_id']  = $site_id;
        }

        $fa['amount']         = $amount;
        //$fa['discount_amount']= $total_discount_amount;
        $fa['order_id']       = $order_id;
        $fa['receipt_code']   = $receipt_code;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['cheque_no']      = $cheque_no;
        $fa['cheque_date']    = $cheque_date;
        $fa['bank_name']      = $bank_name;
        $fa['remarks']        = $remarks;
        $fa['date']           = $date;
        $fa['receipt_status'] = 'Paid';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');
        
        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();
        $receipt_amount     = $amount;
        $invoice_status_due = '';
        $count = 0;
        
        if ($site_id) {
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode' AND site_id = '{$site_id}'";
        } else {
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
        }
        $resultUpdate = $db->sql_query($SQLUpdate);

        $total_discount_amount = 0;
        foreach($invoiceCodes AS $invoice_code){
            $invoiceRec      = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}'");
            $invoice_amount  = $invoiceRec['invoice_amount'];
            $invoice_id      = $invoiceRec['invoice_id'];
            $discount_amount = $invoiceRec['discount_amount'];
            
            //$invoice_amount  = $invoice_amount - $discount_amount;
            
            if ($invoiceRec['status'] == 'Paid' || $receipt_amount <= 0){
                continue;
            }
            
            $SQLPaid = "
            SELECT SUM(amount) AS prev_sum
            FROM invoice_receipt_history
            WHERE invoice_id = '{$invoice_id}'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            
            $invoice_amount = $invoice_amount - $rowPaid['prev_sum'] - $discount_amount; 

            $faInv = array();
            $recpInvAmount = 0;
            if ($invoice_amount <= $receipt_amount){
                $recpInvAmount = $invoice_amount;
                $faInv['status'] = 'Paid';
            } else if ($invoice_amount > $receipt_amount){
                $recpInvAmount = $receipt_amount;
                $faInv['status'] = 'Partial Payment';
            }
            
            $receipt_amount = $receipt_amount - $recpInvAmount;
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            
            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $recpInvAmount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
            
            $total_discount_amount += $discount_amount;
            
        }

        // Updation of Discount amount for receipt
        $faDisc = array();
        $faDisc['discount_amount'] = $total_discount_amount;                
        $fn->saveRecord($faDisc, 'receipt', 'receipt_id', $receipt_id);
            

        /* To generate media record */
        /*$SQLOrder = "
        SELECT o.*
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
        FROM `order` o
        WHERE o.order_id = {$order_id}
        ";
        $resultOrder = $db->sql_query($SQLOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);
        
        
        if ($cpCfg['m.edukloud.order.receiptForEnt']) {
            $this->getGenerateReceiptForEntMedia($receipt_id);
        } else if ($rowOrder['contact_type'] == 'Company') {
            $this->getGenerateReceiptForMedia($receipt_id);
        } else {
            $this->getGenerateReceiptIndividualForMedia($receipt_id);
        }
        */
        
        //$cpUtil->redirect("index.php?_topRm=finance&module=edukloud_order&order_id={$order_id}&_action=edit");
        
        return $validate->getSuccessMessageXML();
    }
    
    /**
     * Finding Receipt Code according to Site Id
     */
    function getFindReceiptCodeWithSite($site_id = '') {
        $db = Zend_Registry::get('db');

        $appendSql = '';
        if ($site_id) {
            $appendSql .= "AND site_id = {$site_id}";
        }
        
        $sqlSetting = "
        SELECT value
        FROM setting
        WHERE key_text = 'nextReceiptCode'
        {$appendSql}    
        ";
        $resultSetting  = $db->sql_query($sqlSetting);
        $rowSetting     = $db->sql_fetchrow($resultSetting);            
        $receiptCode    = $rowSetting['value'];
        
        return $receiptCode;
    }

    /**
     * Updation of Receipt Code according to Site Id
     */
    function getReceiptCodePrefixWithSite($site_id = '') {
        $db = Zend_Registry::get('db');

        $appendSql = '';
        if ($site_id) {
            $appendSql .= "AND site_id = {$site_id}";
        }
        
        $sqlRecCode = "
        SELECT value
        FROM setting
        WHERE key_text = 'receiptCodePrefix'
        {$appendSql}    
        ";
        $resultRecCode  = $db->sql_query($sqlRecCode);
        $rowRecCode     = $db->sql_fetchrow($resultRecCode);
        $receiptCodePfx = $rowRecCode['value'];

        return $receiptCodePfx;
    }

    /**
     * Separation of Invoice Codes
     */
    function getSeparationOfInvoiceCodes($invoiceCodes) {

        $invoiceCodesArr = join(',', $invoiceCodes);
        $sessionExplode  = explode(',', $invoiceCodesArr);
        
        $counter = 1;
        $count = count($sessionExplode);
        
        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }
        
        return $invoice_code;
    }
    
    /**
     *
     */
    function getGenerateReceiptFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_amount = '';
        $invoice_prev_amount = '';
        $balance_amount = '';
        $amount          = $fn->getPostParam('amount');
        $discount_amount = $fn->getPostParam('discount_amount');
        $invoiceCodes    = $fn->getPostParam('invoiceCode', array());

        $validate->resetErrorArray();
        if(count($invoiceCodes) == 0){
            $validate->validateData('amount' , 'Please choose the invoice(s) to be paid');
        }
        //==================================================================
        $invoice_code = $this->getSeparationOfInvoiceCodes($invoiceCodes);

        if ($invoice_code != ''){
            /* Finding total invoice amount of selected invoices */
            $SQL = "
            SELECT SUM(invoice_amount) as invoice_sum
            FROM invoice
            WHERE invoice_code IN ($invoice_code)
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $total_invoice_amount = $rowPaid['invoice_sum'];

            /* Finding total amount paid earlier of selected invoices */
            $SQLPaid = "
            SELECT SUM(amount) as prev_sum
            FROM invoice_receipt_history
            WHERE invoice_id IN (
                SELECT invoice_id
                FROM invoice
                WHERE invoice_code IN ($invoice_code)
                )
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];
            
            /* Calculating new discount amount for selected invoices */
            if ($discount_amount == '') {
                $discount_amount = 0;
            }

            if ($discount_amount > 0) {
                
                $sqlMaxInvAmt = "
                SELECT MAX(invoice_amount) AS max_invoice_amount FROM invoice
                WHERE invoice_code IN ($invoice_code)
                ";
                $resultMaxInvAmt = $db->sql_query($sqlMaxInvAmt);
                $rowMaxInvAmt    = $db->sql_fetchrow($resultMaxInvAmt);
                
                if ($discount_amount > $rowMaxInvAmt['max_invoice_amount']) {
                    $validate->errorArray['discount_amount']['name'] = "discount_amount";
                    $validate->errorArray['discount_amount']['msg']  = 'You cannot input the discount amount more than ' . $rowMaxInvAmt['max_invoice_amount'];
                }
                
                $sqlDiscount = "
                SELECT invoice_code FROM invoice
                WHERE invoice_code IN ($invoice_code)
                  AND add_registration_fee IS NULL
                ";
                $resultDiscount  = $db->sql_query($sqlDiscount);
                $numRowsDiscount = $db->sql_numrows($resultDiscount);
                
                $discount_amount = $numRowsDiscount * $discount_amount;                
            }
                

            $balance_amount = $total_invoice_amount - $prev_sum;
            $invoice_amount = $balance_amount - $discount_amount;

            $total_receipt_amount = $amount + $discount_amount;
            if($total_receipt_amount > $balance_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'You can input a maximum of ' . $invoice_amount . ' in amount for chosen invoices';
            }
        }
        
        $validate->validateData('invoiceCode' , 'Please check invoice code');
        $validate->validateData('amount' , 'Please enter the amount');
        $validate->validateData('mode_of_payment' , 'Please select mode of payment');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     *
     */
    function getGenerateMonthlyInvoiceForEntFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $site_id = $fn->getSessionParam('cp_site_id');

        $current_year = date('Y');
        $rowDate      = $fn->getRecordByCondition('setting', "key_text = 'invoiceDate'");

        foreach($_POST AS $key => $val){
            $inv_arr    = explode('__', $key);
            $order_id   = $inv_arr[1];
            $contact_id = $inv_arr[2];
            $total      = $inv_arr[3];
            $month      = $val;
            $subsidyAmount = '';
            
            /* Checking for record if there is registration fee record in order item */
            $SQLOrderItem = "
            SELECT oi.*
            FROM order_item oi
            WHERE oi.order_id = {$order_id}
              AND oi.contact_id = {$contact_id}
              AND oi.module = 'edukloud_reg_fee'
            ";
            $resultOrderItem    = $db->sql_query($SQLOrderItem);
            $numRowsOrderItem   = $db->sql_numrows($resultOrderItem);
            $rowOrderItem       = $db->sql_fetchrow($resultOrderItem);
                     
            /* Checking whether invoice record is created earlier */
            $SQLInv = "
            SELECT i.*
            FROM invoice i
            WHERE i.order_id = {$order_id}
              AND i.contact_id = {$contact_id}
              AND i.add_registration_fee = 1
            ";
            $resultInv    = $db->sql_query($SQLInv);
            $numRowsInv   = $db->sql_numrows($resultInv);

            if ($numRowsOrderItem > 0 && $numRowsInv == 0) {

                $nextInvoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");

                if($nextInvoiceCode < 10) {
                    $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '000' . $nextInvoiceCode;
                } else if($nextInvoiceCode < 99) {
                    $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '00' . $nextInvoiceCode;
                } else if($nextInvoiceCode < 999) {
                    $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '0' . $nextInvoiceCode;
                } else {
                    $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . $nextInvoiceCode;
                }
                
                $expOrderItemSubsidy = array('condn' => "
                AND module      = 'edukloud_subsidy'
                AND contact_id  = $contact_id
                ");
                $orderItemRecSubsidy   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemSubsidy);
                
                if (is_array($orderItemRecSubsidy)){
                    $subsidyAmount = $orderItemRecSubsidy['unit_price'];
                }
                
                $fa = array();
                $fa['order_id']             = $order_id;
                $fa['contact_id']           = $contact_id;
                $fa['invoice_code']         = $nextInvoiceCode;
                $fa['invoice_month']        = $month;
                $fa['invoice_date']         = $current_year . '-' . $month . '-' . $rowDate['value'];
                $fa['invoice_amount']       = $rowOrderItem['unit_price'] + $subsidyAmount;
                $fa['status']               = 'Due';
                $fa['creation_date']        = date("Y-m-d H:i:s");
                $fa['add_registration_fee'] = 1;
                $invoice_id                 = $fn->addRecord($fa, 'invoice');

                if ($site_id) {
                    $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' AND site_id = '{$site_id}'";
                } else {
                    $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
                }
                $resultUpdate    = $db->sql_query($SQLUpdate);
    
            }

            $nextInvoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");
            
            if($nextInvoiceCode < 10) {
                $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '000' . $nextInvoiceCode;
            } else if($nextInvoiceCode < 99) {
                $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '00' . $nextInvoiceCode;
            } else if($nextInvoiceCode < 999) {
                $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '0' . $nextInvoiceCode;
            } else {
                $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . $nextInvoiceCode;
            }

            $fa = array();
            $fa['order_id']         = $order_id;
            $fa['contact_id']       = $contact_id;
            $fa['invoice_code']     = $nextInvoiceCode;
            $fa['invoice_month']    = $month;
            $fa['invoice_date']     = $current_year . '-' . $month . '-' . $rowDate['value'];
            $fa['invoice_amount']   = $total + $subsidyAmount;;
            $fa['status']           = 'Due';
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $invoice_id             = $fn->addRecord($fa, 'invoice');

            if ($site_id) {
                $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' AND site_id = '{$site_id}'";
            } else {
                $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
            }
            $resultUpdate    = $db->sql_query($SQLUpdate);

            #$this->getGenerateInvoiceForEntMedia($invoice_id);
        }
        
        return $validate->getSuccessMessageXML();
    }
    
    /**
     *
     */
    function getGenerateMonthlyInvoiceForEntFormSubmitValidate($order_id, $start_month, $end_month) {
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        
        $month_present = '';
        
        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('start_month' , 'Please select the start month');
		
		if ($start_month != '') {
		    for ($i = $start_month; $i <= $end_month; $i++) {
                $SQLInv = "
                SELECT * FROM invoice
                WHERE order_id = {$order_id} AND invoice_month = {$i}
                AND status != 'Cancelled'
                ";
                $resultInv  = $db->sql_query($SQLInv);
                $numRowsInv = $db->sql_numrows($resultInv);
                if($numRowsInv > 0){
                    
                    $appendComma = '';
                    if ($i != $end_month) {
                        $appendComma = ', ';
                    }
                    
    	    	    if ($i == 1) {
                        $month_present = $month_present . 'January' . $appendComma;
    	    	    } else if ($i == 2) {
                        $month_present = $month_present . 'February' . $appendComma;
    	    	    } else if ($i == 3) {
                        $month_present = $month_present . 'March' . $appendComma;
    	    	    } else if ($i == 4) {
                        $month_present = $month_present . 'April' . $appendComma;
    	    	    } else if ($i == 5) {
                        $month_present = $month_present . 'May' . $appendComma;
    	    	    } else if ($i == 6) {
                        $month_present = $month_present . 'June' . $appendComma;
    	    	    } else if ($i == 7) {
                        $month_present = $month_present . 'July' . $appendComma;
    	    	    } else if ($i == 8) {
                        $month_present = $month_present . 'August' . $appendComma;
    	    	    } else if ($i == 9) {
                        $month_present = $month_present . 'September' . $appendComma;
    	    	    } else if ($i == 10) {
                        $month_present = $month_present . 'October' . $appendComma;
    	    	    } else if ($i == 11) {
                        $month_present = $month_present . 'November' . $appendComma;
    	    	    } else if ($i == 12) {
                        $month_present = $month_present . 'December' . $appendComma;
    	    	    }
                }
		    }
		}
		
		if($month_present != ''){
            $msg = 'Invoice already created for the month(s) ' . $month_present;
            $validate->validateData('error_box', $msg);
		}

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Invoice PDF in Fpdf for Enterprise edition of edukloud
     */
    function getPrintInvoiceInFpdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        $invoice_id = $fn->getReqParam('record_id');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT i.*
        FROM invoice i
        WHERE i.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
        
            $invoice_code    = $row['invoice_code'];
            $discount_amount = $row['discount_amount'];

            $orderRec           = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $orderRec['order_id']);
            $contactRec         = $fn->getRecordRowByID('contact', 'contact_id', $row['contact_id']);
            $parContRec         = $fn->getRecordRowByID('parent_contact', 'contact_id', $row['contact_id']);
            $parentRec          = $fn->getRecordRowByID('parent', 'parent_id', $parContRec['parent_id']);
            $countryRec         = $fn->getRecordByCondition('geo_country', "country_code = '{$parentRec['address_country']}'");

            /* Logo of the institution */
            $pdf->Image('images/logo-print.jpg', 10, 5, 45);

            /* Institute company address */
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetXY(80,1);
            $pdf->Cell(20, 20, $cpCfg['printCompanyName']);
            $pdf->SetXY(80,5);
            $pdf->Cell(24, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
            $pdf->SetXY(80,10);
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(28, 20, $cpCfg['printTelephoneAndFax']);
            $pdf->SetXY(80,15);
            $pdf->Cell(20, 20, $cpCfg['printEmailAndWebsite']);
            $pdf->SetXY(80,20);
            $pdf->Cell(20, 20, $cpCfg['printRegistrationNo']);
            $pdf->Ln(10);

            $pdf->SetFont('Arial','B',10);
            $pdf->SetXY(100, 35);
            $pdf->Cell(21, 20, "OFFICIAL INVOICE", 0, 0, 'C');                
            $pdf->Ln(10);

            /* Parent Name */
            $pdf->SetX(10);
            $parent_full_name = $parentRec['first_name'] . ' ' . $parentRec['last_name'];
            $parent_name = 'To: ' . $parent_full_name;
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(50, 20, $parent_name);

            /* Invoice code*/
            $pdf->SetFont('Arial', '', 10);
            $code = 'Invoice No : '. $row['invoice_code'];
            $pdf->SetXY(157, 45);
            $pdf->Cell(50, 20, $code);                
            $pdf->Ln(6);
            
            /* Student Name */
            $pdf->SetX(10);
            $student_full_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
            $student_name = 'Student Name: ' . $student_full_name;
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(50, 20, $student_name);

            /* Course Title 
            $pdf->SetX(10);
            $pdf->Cell(27, 20, 'Course Enrolled: ');
            $pdf->Cell(100, 20, $courseRec['title']);*/

            /* Invoice Date */
            $pdf->SetX(157);
            $date = $fn->getCPDate($row['invoice_date'], 'dS F Y');
            $invoice_date = 'Date: ' . $date;
            $pdf->Cell(50, 20, $invoice_date);

            $pdf->SetXY(10, 60);
            $pdf->Cell(50, 20, 'Address :');

            $pdf->SetXY(10, 65);
            $pdf->Cell(150, 20, $parentRec['address_flat']);
            
            $y_property = 70;
            if ($parentRec['address_street']) {
                $pdf->SetXY(10, 70);
                $pdf->Cell(150, 20, $parentRec['address_street']);
                
                $y_property = 75;
            }
            
            $country_name = $countryRec['name'];
            if ($country_name == '') {
                $country_name = 'Singapore';    
            }
            
            $country = $country_name . ' - ' . $parentRec['address_po_code'];
            $pdf->SetXY(10, $y_property);
            $pdf->Cell(250, 20, $country);                

            $pdf->Ln(16);

            //===================================FIRST TABLE============================= //
            $pdf->SetX(10);
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(190, 8, 'Details of Payment', 1);
            $pdf->Ln();
            
		    if ($row['invoice_month'] == 1) {
                $month = 'January';
		    } else if ($row['invoice_month'] == 2) {
                $month = 'February';
		    } else if ($row['invoice_month'] == 3) {
                $month = 'March';
		    } else if ($row['invoice_month'] == 4) {
                $month = 'April';
		    } else if ($row['invoice_month'] == 5) {
                $month = 'May';
		    } else if ($row['invoice_month'] == 6) {
                $month = 'June';
		    } else if ($row['invoice_month'] == 7) {
                $month = 'July';
		    } else if ($row['invoice_month'] == 8) {
                $month = 'August';
		    } else if ($row['invoice_month'] == 9) {
                $month = 'September';
		    } else if ($row['invoice_month'] == 10) {
                $month = 'October';
		    } else if ($row['invoice_month'] == 11) {
                $month = 'November';
		    } else if ($row['invoice_month'] == 12) {
                $month = 'December';
		    }

            /* Months for the receipt */
            $pdf->SetFont('Arial','B',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(45, 8, "Month of invoice", 'LR');
            $pdf->Cell(145, 8, $month, 'LR');
            $pdf->Ln();

            /* List of invoice items header */
            $pdf->SetFont('Arial','B',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(135, 8, "Description", 1, 0, 'L', 1);
            $pdf->Cell(55, 8, "Sub Total (SGD)", 1, 0, 'R', 1);
            $pdf->Ln();

            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(135, 8, "School Fees", 'LTR', 0, 'L', 1);
            $pdf->Cell(55, 8, number_format($row['invoice_amount'], 2), 'LTR', 0, 'R', 1);
            $pdf->Ln();

            $pdf->Cell(135, 8, "Registration Fees", 'LR', 0, 'L', 1);
            $pdf->Cell(55, 8, "0.00", 'LR', 0, 'R', 1);
            $pdf->Ln();
                
            $pdf->Cell(135, 8, "Other Fees", 'LR', 0, 'L', 1);
            $pdf->Cell(55, 8, "0.00", 'LR', 0, 'R', 1);
            $pdf->Ln();

            $pdf->Cell(135, 8, "Discount", 1, 0, 'L', 1);
            $pdf->Cell(55, 8, number_format($discount_amount, 2), 1, 0, 'R', 1);
            $pdf->Ln();
                
            $invoice_amount = $row['invoice_amount'] - $discount_amount;
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(135, 8, "Total", 1, 0, 'L', 1);
            $pdf->Cell(55, 8, number_format($invoice_amount, 2), 1, 0, 'R', 1);
            $pdf->Ln();
        }

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, 'Simply Islam issues receipts to acknowledge fees are paid accordingly and kindly check all details above are correct');
        $pdf->Ln(4);

        $pdf->Output();
    }

    /**
     * Invoice PDF in Fpdf for more than one student for a parent of Iedukloud
     */
    function  getPrintSelectedInvoices(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $invoiceCodes = isset($_SESSION['selectedInvoicesForSummary']) ? $_SESSION['selectedInvoicesForSummary'] : 0;
        $count = count($invoiceCodes);
        
        /*
        if ($_SESSION['selectedInvoiceCodes'] > 0) {
            print $count . "no of invoice";    
        } else {
            print $count . "no of invoice";    
        }
        */
        
        //print_r($invoiceCodes);
        
        if ($count == 0) {
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please select the invoices");
			$pdf->Output();
			return;
        }
        
        $invoiceCodes   = join(',', $invoiceCodes);
        $sessionExplode = explode(',', $invoiceCodes);
        
        $counter = 1;
        $count   = count($sessionExplode);
        
        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }

        $SQL = "
        SELECT DISTINCT p.parent_id
        FROM parent p
        LEFT JOIN (parent_contact pc) ON (p.parent_id   = pc.parent_id)
        JOIN (invoice i)              ON (pc.contact_id = i.contact_id)
        WHERE i.invoice_code IN ($invoice_code)
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        
        if ($numRows > 1) {
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please select one parent");
			$pdf->Output();
			return;
        }
        
        $this->getPrintGroupInvoiceInFpdf($invoice_code, $pdf);
        
    }

    /**
     * Invoice PDF in Fpdf for more than one student for a parent of Iedukloud
     */
    function getPrintGroupInvoiceInFpdf($invoice_code, $pdf, $allDueInvoiceForAMonth = "") {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT i.*
        FROM invoice i
        WHERE i.invoice_code IN ($invoice_code)
        ORDER BY i.invoice_month ASC
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
            $pdf->ln();
            $pdf->Cell(50, 20, $SQL);
			$pdf->Output();
			return;
		}
        
        $count = 1;
        $total = 0;
        $total_discount = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
        
            $orderRec           = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $orderRec['order_id']);
            $contactRec         = $fn->getRecordRowByID('contact', 'contact_id', $row['contact_id']);
            $parContRec         = $fn->getRecordRowByID('parent_contact', 'contact_id', $row['contact_id']);
            $parentRec          = $fn->getRecordRowByID('parent', 'parent_id', $parContRec['parent_id']);
            $countryRec         = $fn->getRecordByCondition('geo_country', "country_code = '{$parentRec['address_country']}'");

            if ($count == 1) {
                /* Logo of the institution */
                $pdf->Image('images/logo-print.jpg', 10, 5, 45);
    
                /* Institute company address */
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetXY(80,1);
                $pdf->Cell(20, 20, $cpCfg['printCompanyName']);
                $pdf->SetXY(80,5);
                $pdf->Cell(24, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
                $pdf->SetXY(80,10);
                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(28, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->SetXY(80,15);
                $pdf->Cell(20, 20, $cpCfg['printEmailAndWebsite']);
                $pdf->SetXY(80,20);
                $pdf->Cell(20, 20, $cpCfg['printRegistrationNo']);
                $pdf->Ln(10);
    
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(100, 35);
                $pdf->Cell(21, 20, "OFFICIAL INVOICE", 0, 0, 'C');                
                $pdf->Ln(10);
    
                /* Parent Name */
                $pdf->SetX(10);
                $parent_full_name = $parentRec['first_name'] . ' ' . $parentRec['last_name'];
                $parent_name = 'To: ' . $parent_full_name;
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $parent_name);
    
                /* Invoice Date */
                $pdf->SetX(157);
                $date = $fn->getCPDate($today, 'dS F Y');
                $invoice_date = 'Date: ' . $date;
                $pdf->Cell(50, 20, $invoice_date);

                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, 'Address:');

                $pdf->SetXY(10, 55);
                $pdf->Cell(150, 20, $parentRec['address_flat']);
                
                $y_property = 60;
                if ($parentRec['address_street']) {
                    $pdf->SetXY(10, 60);
                    $pdf->Cell(150, 20, $parentRec['address_street']);
                    
                    $y_property = 65;
                }
                
                $country_name = $countryRec['name'];
                if ($country_name == '') {
                    $country_name = 'Singapore';    
                }
                
                $country = $country_name . ' - ' . $parentRec['address_po_code'];
                $pdf->SetXY(10, $y_property);
                $pdf->Cell(250, 20, $country);                
                $pdf->Ln(25);
    
                //===================================FIRST TABLE============================= //
                $pdf->SetX(10);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(190, 8, 'Details of Payment', 1);
                $pdf->Ln();
                
                /* Months for the receipt 
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(35, 8, "Month of invoice", 'LR');
                $pdf->Cell(155, 8, $month, 'LR');
                $pdf->Ln();*/
    
                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(25, 8, "Invoice No", 1, 0, 'L', 1);
                $pdf->Cell(30, 8, "Invoice Month", 1, 0, 'L', 1);
                $pdf->Cell(100, 8, "Student Name", 1, 0, 'L', 1);
                $pdf->Cell(35, 8, "Sub Total (SGD)", 1, 0, 'R', 1);
                $pdf->Ln();
            }
            
            $student_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
            $invoice_amount = number_format($row['invoice_amount'], 2);
            $total += $row['invoice_amount'];
            $total_discount += $row['discount_amount'];

		    if ($row['invoice_month'] == 1) {
                $month = 'January';
		    } else if ($row['invoice_month'] == 2) {
                $month = 'February';
		    } else if ($row['invoice_month'] == 3) {
                $month = 'March';
		    } else if ($row['invoice_month'] == 4) {
                $month = 'April';
		    } else if ($row['invoice_month'] == 5) {
                $month = 'May';
		    } else if ($row['invoice_month'] == 6) {
                $month = 'June';
		    } else if ($row['invoice_month'] == 7) {
                $month = 'July';
		    } else if ($row['invoice_month'] == 8) {
                $month = 'August';
		    } else if ($row['invoice_month'] == 9) {
                $month = 'September';
		    } else if ($row['invoice_month'] == 10) {
                $month = 'October';
		    } else if ($row['invoice_month'] == 11) {
                $month = 'November';
		    } else if ($row['invoice_month'] == 12) {
                $month = 'December';
		    }
    
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(25, 8, $row['invoice_code'], 1, 0, 'L', 1);
            $pdf->Cell(30, 8, $month, 1, 0, 'L', 1);
            $pdf->Cell(100, 8, $student_name, 1, 0, 'L', 1);
            $pdf->Cell(35, 8, $invoice_amount, 1, 0, 'R', 1);
            $pdf->Ln();

            $count++;
        }

        $total_discount = number_format($total_discount, 2);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(155, 8, "Total Discount", 1, 0, 'L', 1);
        $pdf->Cell(35, 8, $total_discount, 1, 0, 'R', 1);
        $pdf->Ln();

        $balance_amount = $total - $total_discount;
        $total = number_format($balance_amount, 2);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(155, 8, "Total", 1, 0, 'L', 1);
        $pdf->Cell(35, 8, $total, 1, 0, 'R', 1);
        $pdf->Ln(15);

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, 'Simply Islam issues receipts to acknowledge fees are paid accordingly and kindly check all details above are correct');
        $pdf->Ln(4);

        if($allDueInvoiceForAMonth == ''){
            $pdf->Output();
        }
    }

    /**
     * Receipt PDF in Fpdf for more than one student for a parent of Iedukloud
     */
    function getPrintGroupReceiptInFpdf(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        /*$invoiceCodes = isset($_SESSION['selectedInvoicesForSummary']) ? $_SESSION['selectedInvoicesForSummary'] : 0;
        $count = count($invoiceCodes);
        $invoice_code = $this->getSeparationOfInvoiceCodes($invoiceCodes);*/

        $receiptId = isset($_SESSION['receiptIdForSummary']) ? $_SESSION['receiptIdForSummary'] : 0;
        $receipt_id = $this->getSeparationOfInvoiceCodes($receiptId);

        /*$SQL = "
        SELECT DISTINCT p.parent_id
        FROM parent p
        LEFT JOIN (parent_contact pc) ON (p.parent_id   = pc.parent_id)
        JOIN (invoice i)              ON (pc.contact_id = i.contact_id)
        WHERE i.invoice_code IN ($invoice_code)
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);*/
        
        $SQL = "
        SELECT r.*
              ,i.invoice_amount
              ,i.invoice_month
              ,i.discount_amount
              ,i.contact_id
              ,i.invoice_id
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        WHERE r.receipt_id IN ($receipt_id)
        ORDER BY i.invoice_month ASC
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");

        $count = 1;
        $total = 0;
        $total_amount = 0;
        $discount_price = 0;
        $amount_already_paid = 0;
        $total_discount_amount = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
        
            $orderRec           = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $orderRec['order_id']);
            $contactRec         = $fn->getRecordRowByID('contact', 'contact_id', $row['contact_id']);
            $parContRec         = $fn->getRecordRowByID('parent_contact', 'contact_id', $row['contact_id']);
            $parentRec          = $fn->getRecordRowByID('parent', 'parent_id', $parContRec['parent_id']);
            $countryRec         = $fn->getRecordByCondition('geo_country', "country_code = '{$parentRec['address_country']}'");

            if ($count == 1) {
                /* Logo of the institution */
                $pdf->Image('images/logo-print.jpg', 10, 5, 45);
    
                /* Institute company address */
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetXY(80,1);
                $pdf->Cell(20, 20, $cpCfg['printCompanyName']);
                $pdf->SetXY(80,5);
                $pdf->Cell(24, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
                $pdf->SetXY(80,10);
                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(28, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->SetXY(80,15);
                $pdf->Cell(20, 20, $cpCfg['printEmailAndWebsite']);
                $pdf->SetXY(80,20);
                $pdf->Cell(20, 20, $cpCfg['printRegistrationNo']);
                $pdf->Ln(10);
    
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(100, 35);
                $pdf->Cell(21, 20, "OFFICIAL RECEIPT", 0, 0, 'C');                
                $pdf->Ln(10);
    
                /* Parent Name */
                $pdf->SetX(10);
                $parent_full_name = $parentRec['first_name'] . ' ' . $parentRec['last_name'];
                $parent_name = 'To: ' . $parent_full_name;
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $parent_name);
    
                /* Invoice Date */
                $pdf->SetX(157);
                $date = $fn->getCPDate($today, 'dS F Y');
                $invoice_date = 'Date: ' . $date;
                $pdf->Cell(50, 20, $invoice_date);

                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, 'Address:');

                $pdf->SetXY(10, 55);
                $pdf->Cell(150, 20, $parentRec['address_flat']);
                
                $y_property = 60;
                if ($parentRec['address_street']) {
                    $pdf->SetXY(10, 60);
                    $pdf->Cell(150, 20, $parentRec['address_street']);
                    
                    $y_property = 65;
                }
                
                $country_name = $countryRec['name'];
                if ($country_name == '') {
                    $country_name = 'Singapore';    
                }
                
                $country = $country_name . ' - ' . $parentRec['address_po_code'];
                $pdf->SetXY(10, $y_property);
                $pdf->Cell(250, 20, $country);                
                $pdf->Ln(25);
    
                //===================================FIRST TABLE============================= //
                $pdf->SetX(10);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(190, 8, 'Details of Payment', 1);
                $pdf->Ln();
                
                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(25, 8, "Receipt No", 1, 0, 'L', 1);
                $pdf->Cell(30, 8, "Payment Month", 1, 0, 'L', 1);
                $pdf->Cell(100, 8, "Student Name", 1, 0, 'L', 1);
                $pdf->Cell(35, 8, "Sub Total (SGD)", 1, 0, 'R', 1);
                $pdf->Ln();
            }
            
            $student_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
            $receipt_amount = number_format($row['amount'], 2);
            $total += $row['amount'];

		    if ($row['invoice_month'] == 1) {
                $month = 'January';
		    } else if ($row['invoice_month'] == 2) {
                $month = 'February';
		    } else if ($row['invoice_month'] == 3) {
                $month = 'March';
		    } else if ($row['invoice_month'] == 4) {
                $month = 'April';
		    } else if ($row['invoice_month'] == 5) {
                $month = 'May';
		    } else if ($row['invoice_month'] == 6) {
                $month = 'June';
		    } else if ($row['invoice_month'] == 7) {
                $month = 'July';
		    } else if ($row['invoice_month'] == 8) {
                $month = 'August';
		    } else if ($row['invoice_month'] == 9) {
                $month = 'September';
		    } else if ($row['invoice_month'] == 10) {
                $month = 'October';
		    } else if ($row['invoice_month'] == 11) {
                $month = 'November';
		    } else if ($row['invoice_month'] == 12) {
                $month = 'December';
		    }
    
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(25, 8, $row['receipt_code'], 1, 0, 'L', 1);
            $pdf->Cell(30, 8, $month, 1, 0, 'L', 1);
            $pdf->Cell(100, 8, $student_name, 1, 0, 'L', 1);
            $pdf->Cell(35, 8, number_format($row['invoice_amount'], 2), 1, 0, 'R', 1);
            $pdf->Ln();

            /* Previous payment calculation */
            $sqlPreviousPayment = "
            SELECT SUM(amount) AS total_amount_paid
            FROM invoice_receipt_history
            WHERE invoice_id = {$row['invoice_id']}
              AND receipt_id != {$row['receipt_id']}
              AND receipt_id < {$row['receipt_id']}
            ";
            $resultPreviousPayment = $db->sql_query($sqlPreviousPayment);
            $rowPreviousPayment    = $db->sql_fetchrow($resultPreviousPayment);
            
            $amount_already_paid += $rowPreviousPayment['total_amount_paid'];
            
            $total_discount_amount += $row['discount_amount'];
            $total_amount += $row['invoice_amount'];

            $count++;
        }

        /* Total amount paid earlier */
        $pdf->Cell(155, 8, "Amount already Paid", 1, 0, 'L', 1);
        $pdf->Cell(35, 8, number_format($amount_already_paid, 2), 1, 0, 'R', 1);
        $pdf->Ln();

        /* Total amount paid */
        $total = number_format($total, 2);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(155, 8, "Amount Received Now", 1, 0, 'L', 1);
        $pdf->Cell(35, 8, $total, 1, 0, 'R', 1);
        $pdf->Ln();

        /* Discount Offered */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(155, 8, "Discount", 1, 0, 'L', 1);
        $pdf->Cell(35, 8, number_format($total_discount_amount, 2), 1, 0, 'R', 1);
        $pdf->Ln();

        /* Balance amount to be paid */
        $balance_amount = $total_amount - $total - $amount_already_paid - $total_discount_amount;
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(155, 8, "Balance Amount to be Paid", 1, 0, 'L', 1);
        $pdf->Cell(35, 8, number_format($balance_amount, 2), 1, 0, 'R', 1);
        $pdf->Ln(15);

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, 'Simply Islam issues receipts to acknowledge fees are paid accordingly and kindly check all details above are correct');
        $pdf->Ln(4);

        $pdf->Output();        
    }

    /**
     * Invoice PDF for Enterprise edition of edukloud
     */
    function getGenerateInvoiceForEntMedia($invoice_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT i.*
        FROM invoice i
        WHERE i.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
        
            $invoice_code = $row['invoice_code'];

            $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
            $courseContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $orderRec['order_id']);
            $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $row['contact_id']);
            $parContRec = $fn->getRecordRowByID('parent_contact', 'contact_id', $row['contact_id']);
            $parentRec = $fn->getRecordRowByID('parent', 'parent_id', $parContRec['parent_id']);

            /*$sqlCourseRec = "
            SELECT c.*
            FROM course c
            LEFT JOIN (course_contact cc) ON (c.course_id = cc.course_id)
            WHERE c.course_id = {$courseContactRec['course_id']}
              AND cc.contact_id = {$contactRec['contact_id']}
            ";
            $resultCourseRec  = $db->sql_query($sqlCourseRec);
            $courseRec = $db->sql_fetchrow($resultCourseRec);*/
                        
            /* Logo of the institution */
            $pdf->Image('images/logo-print.jpg', 10, 5, 45);

            /* Institute company address */
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetXY(80,1);
            $pdf->Cell(20, 20, $cpCfg['printCompanyName']);
            $pdf->SetXY(80,5);
            $pdf->Cell(24, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
            $pdf->SetXY(80,10);
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(28, 20, $cpCfg['printTelephoneAndFax']);
            $pdf->SetXY(80,15);
            $pdf->Cell(20, 20, $cpCfg['printEmailAndWebsite']);
            $pdf->SetXY(80,20);
            $pdf->Cell(20, 20, $cpCfg['printRegistrationNo']);
            $pdf->Ln(10);

            $pdf->SetFont('Arial','B',10);
            $pdf->SetXY(100, 35);
            $pdf->Cell(21, 20, "OFFICIAL INVOICE", 0, 0, 'C');                
            $pdf->Ln(10);

            /* Parent Name */
            $pdf->SetX(10);
            $parent_full_name = $parentRec['first_name'] . ' ' . $parentRec['last_name'];
            $parent_name = 'TO: ' . $parent_full_name;
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(50, 20, $parent_name);

            /* Invoice code*/
            $pdf->SetFont('Arial', '', 10);
            $code = 'Invoice No : '. $row['invoice_code'];
            $pdf->SetXY(157, 45);
            $pdf->Cell(50, 20, $code);                
            $pdf->Ln(6);
            
            /* Student Name */
            $pdf->SetX(10);
            $student_full_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
            $student_name = 'Student Name: ' . $student_full_name;
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(50, 20, $student_name);

            /* Course Title 
            $pdf->SetX(10);
            $pdf->Cell(27, 20, 'Course Enrolled: ');
            $pdf->Cell(100, 20, $courseRec['title']);*/

            /* Invoice Date */
            $pdf->SetX(157);
            $date = $fn->getCPDate($row['invoice_date'], 'dS F Y');
            $invoice_date = 'Date: ' . $date;
            $pdf->Cell(50, 20, $invoice_date);
            $pdf->Ln(16);

            //===================================FIRST TABLE============================= //
            $pdf->SetX(10);
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(190, 8, 'Details of Payment', 1);
            $pdf->Ln();
            
		    if ($row['invoice_month'] == 1) {
                $month = 'January';
		    } else if ($row['invoice_month'] == 2) {
                $month = 'February';
		    } else if ($row['invoice_month'] == 3) {
                $month = 'March';
		    } else if ($row['invoice_month'] == 4) {
                $month = 'April';
		    } else if ($row['invoice_month'] == 5) {
                $month = 'May';
		    } else if ($row['invoice_month'] == 6) {
                $month = 'June';
		    } else if ($row['invoice_month'] == 7) {
                $month = 'July';
		    } else if ($row['invoice_month'] == 8) {
                $month = 'August';
		    } else if ($row['invoice_month'] == 9) {
                $month = 'September';
		    } else if ($row['invoice_month'] == 10) {
                $month = 'October';
		    } else if ($row['invoice_month'] == 11) {
                $month = 'November';
		    } else if ($row['invoice_month'] == 12) {
                $month = 'December';
		    }

            /* Months for the receipt */
            $pdf->SetFont('Arial','B',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(45, 8, "Month of invoice", 'LR');
            $pdf->Cell(145, 8, $month, 'LR');
            $pdf->Ln();

            /* List of invoice items header */
            $pdf->SetFont('Arial','B',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(135, 8, "Description", 1, 0, 'L', 1);
            $pdf->Cell(55, 8, "Sub Total (SGD)", 1, 0, 'R', 1);
            $pdf->Ln();

            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(135, 8, "School Fees", 'LTR', 0, 'L', 1);
            $pdf->Cell(55, 8, $row['invoice_amount'], 'LTR', 0, 'R', 1);
            $pdf->Ln();

            $pdf->Cell(135, 8, "Registration Fees", 'LR', 0, 'L', 1);
            $pdf->Cell(55, 8, "0", 'LR', 0, 'R', 1);
            $pdf->Ln();
                
            $pdf->Cell(135, 8, "Other Fees", 'LR', 0, 'L', 1);
            $pdf->Cell(55, 8, "0", 'LR', 0, 'R', 1);
            $pdf->Ln();

            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(135, 8, "Total", 1, 0, 'L', 1);
            $pdf->Cell(55, 8, $row['invoice_amount'], 1, 0, 'R', 1);
            $pdf->Ln();
        }

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, 'Simply Islam issues receipts to acknowledge fees are paid accordingly and kindly check all details above are correct');
        $pdf->Ln(4);

        /* Creation of media record of the invoice */
        $file_name = 'Invoice_INV_' . $invoice_code . '_' . date('Y-m-d') .'.pdf';

        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $invoice_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_invoice';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }*/
        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getGenerateCreditNoteFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!$this->getGenerateCreditNoteFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $invoice_id      = $fn->getPostParam('invoice_id');
        $remarks         = $fn->getPostParam('remarks');
        $amount          = $fn->getPostParam('amount');
        $order_id        = $fn->getReqParam('order_id');
        
        //To update credit note codes
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextCreditNoteCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $credit_note_code = $fn->getSettingsValueByKey("nextCreditNoteCode");
        
        $fa = array();
        $fa['amount']           = $amount;
        $fa['order_id']         = $order_id;
        $fa['invoice_id']       = $invoice_id;
        $fa['credit_note_code'] = $credit_note_code;
        $fa['remarks']          = $remarks;
        $fa['date']             = date("Y-m-d H:i:s");
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');
        
        $insertCreditNoteSQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'credit_note');
        $resultSQL              = $db->sql_query($insertCreditNoteSQL);
        $credit_note_id         = $db->sql_nextid();
        
        /* To generate media record */
        $SQLOrder = "
        SELECT o.*
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
        FROM `order` o
        WHERE o.order_id = {$order_id}
        ";
        $resultOrder = $db->sql_query($SQLOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);
        
        if ($rowOrder['contact_type'] == 'Company') {
            $this->getGenerateCreditNoteForMedia($credit_note_id);
        } else {
            $this->getGenerateCreditNoteForMedia($credit_note_id);
        }
        
        return $validate->getSuccessMessageXML();
    }
    
    /**
     *
     */
    function getGenerateCreditNoteFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        
        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('invoice_id' , 'Please select the invoice');
        $validate->validateData('amount' , 'Please enter the amount');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     *
     */
    function getClearInvoice() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $order_item_id = $fn->getReqParam('order_item_id');

        $SQL = "
        UPDATE `order_item`
        SET invoice_clear_status = 'Cancelled'
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);

        //To change invoice status to cancelled
        $orderRec = $fn->getRecordRowByID('order_item', 'order_item_id', $order_item_id);
        
        $invoice_code = $orderRec['invoice_id'];
        
        if($invoice_code){
            $SQL = "
            UPDATE `invoice`
            SET status = 'Cancelled'
            WHERE invoice_code = {$invoice_code}
            ";
            $result = $db->sql_query($SQL);

            $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_code', $invoice_code);
        
            $invoice_id   = $invoiceRec['invoice_id'];
            
            $SQLReceipt = "
            SELECT receipt_id
            FROM invoice_receipt_history 
            WHERE invoice_id = {$invoice_id}
            ";
            $resultReceipt = $db->sql_query($SQLReceipt);
            while ($rowReceipt = $db->sql_fetchrow($resultReceipt)) {
                $UpdateSQL = "
                UPDATE `receipt`
                SET receipt_status = 'Cancelled'
                WHERE receipt_id = {$rowReceipt['receipt_id']}
                ";
                $result = $db->sql_query($UpdateSQL);
            }
            //delete records from invoice_receipt_history
            /*
            $SQL = "
            DELETE FROM invoice_receipt_history
            WHERE invoice_id = {$invoice_id}
            ";
            $result = $db->sql_query($SQL);
            */
        }
        return $text;
    }
    
    /**
     *
     */
    function getPrintInvoiceTBS() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
        
        $invoice_code = $fn->getReqParam('invoice_code');
        $invoice_code = '10004';
        
        $template = 'printInvoice.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'EDUKLOUD_Invoice' . $invoice_code . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);
        
        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');
        
        $SQL = "
        SELECT i.*
        FROM invoice i
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        WHERE i.invoice_code = '{$invoice_code}'
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $valArr = array();
        $valArr['invoicecode']  = $row['invoice_code'] ;
        $valArr['amount']       = $row['invoice_amount'] ;
       
        $blkMain   = array();
        $blkMain[] = $valArr;

        $TBS->MergeBlock('blkMain', $blkMain);
        
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
        /*$TBS->Show(OPENTBS_FILE, $file_name_save);

        $sourceFilePath = $file_name_save;

        $exp = array(
             'srcFile'        => $file_name_save
            ,'actualFileName' => $file_name
        );

        $media->model->createMedia('ecommerce_basket', 'attachment', $order_id, $exp);

        $SQLUpdate = "
        UPDATE media SET room_name = 'ecommerce_order' 
        WHERE record_id = {$order_id}
            AND actual_file_name = '{$file_name}'
        ";
        $result = $db->sql_query($SQLUpdate);
        */
    }
    
    /**
     *
     */
    function getPrintInvoiceOld() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');


        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $company_id      = $fn->getReqParam('company_id');
        $order_id        = $fn->getReqParam('order_id');
        $invoice_code    = $fn->getReqParam('invoice_code');
        $company_id = 5;
        $order_id = 3;

		$invoice_terms = '';
		$notes  = '';
        $total = '';

        $SQL = "
        SELECT o.*
              ,gc1.name AS cust_country_name
              ,c.title AS company_name
              ,c.address1 
              ,c.address2 
              ,c.address_po_code
              ,CONCAT_WS(' ', co.first_name, co.last_name) AS contact_name
              ,oi.qty
              ,oi.item_title
              ,oi.unit_price
              ,oi.module
        FROM `order` o
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN order_item oi ON (o.order_id = oi.order_id)
        LEFT JOIN contact co ON (co.contact_id = oi.contact_id)
        LEFT JOIN geo_country gc1 ON (c.address_country_code = gc1.country_code)
        WHERE O.company_id = {$company_id}
            AND O.order_id = {$order_id}
        ";
        
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                
                $pdf->SetXY(10,1);
                //$pdf->SetFillColor(142,182,212);
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10 , 5, 80, 38, 'D');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, 'EDUQUEST INTERNATIONAL INSTITUTE');
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, '1 Sophia Road, #07-13');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Peace Centre');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Singapore 228149');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Tel : +65 6338 7151');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Fax : +65 6338 7151');
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                $pdf->SetFont('Arial','B',14);
                $pdf->SetXY(165, 35);
                $pdf->Cell(40, 20, "Invoice");
                $date = $fn->getCPDate($today, 'd M Y');
                $code = 'Invoice # : '. $row['order_code'];
                $company_name = strtoupper ($cpCfg['cp.companyName']);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(165, 40);
                $pdf->Cell(50, 20, $code );                
                $pdf->Ln(5);
                $pdf->SetX(165);
                $pdf->Cell(50, 20, "Date : " . $date);

                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10 , 45, 75, 38, 'D');
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(10, 40);
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'Attn : ' . $row['company_name']);
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $row['address1']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $row['address2']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(60, 20, $row['cust_country_name'] . ' ' . $row['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(35);
                /*
                $pdf->Image('images/logo-print.jpg',145,5,45);
                $pdf->SetX(100);
                $pdf->SetFont('Arial','B',14);
                $pdf->Cell(40, 20, "Invoice");

                $date = $fn->getCPDate($today, 'd M Y');
                $code = 'Invoice # : '. $row['order_code'];
                $company_name = strtoupper ($cpCfg['cp.companyName']);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(165, 5);
                $pdf->Cell(50, 20, $code );                
                $pdf->Ln(5);
                $pdf->SetX(165);
                $pdf->Cell(50, 20, "Date : " . $date);

                $pdf->SetXY(10, 25);
                //$pdf->SetFillColor(142,182,212);
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10 , 28, 80, 38, 'DF');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, 'Institute INTERNATIONAL INSTITUTE');
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, '1 Sophia Road, #07-13');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Peace Centre');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Singapore 228149');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Tel : +65 6338 7151');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Fax : +65 6338 7151');
                $pdf->Ln(15);
                
                $pdf->SetXY(127, 25);
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(125 , 28, 75, 38, 'DF');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->SetXY(127, 30);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'Attn : ' . $row['company_name']);
                $pdf->SetXY(127, 35);
                $pdf->Cell(50, 20, $row['address1']);
                $pdf->SetXY(127, 40);
                $pdf->Cell(50, 20, $row['address2']);
                $pdf->SetXY(127, 45);
                $pdf->Cell(60, 20, $row['cust_country_name'] . ' ' . $row['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(35);
                */
                /*
                $pdf->SetFont('Arial','B',10);
                $pdf->Rect(10 , 70, 80, 35, 'FD');
                $pdf->SetX(10);
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->Ln(7);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, "Attn : " . $row['company_name']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['address1']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['address2']);
                $pdf->Ln(5);
                $pdf->Cell(20, 20, $row['cust_country_name'] . ' ' . $row['address_po_code']);
                $pdf->Ln(25);
                */
                
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(142,182,212);
                $pdf->Cell(80, 8, "Program",1 ,0, 'C', 1);
                $pdf->Cell(30, 8, "Program Code",1 ,0, 'C', 1);
                $pdf->Cell(40, 8, "Training Date(s)",1 ,0, 'C', 1);
                $pdf->Cell(40, 8, "Term",1 ,0, 'C', 1);

                $pdf->Ln();
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(80,10,$row['item_title'],1);
                $pdf->Cell(30,10,"Program Code",1);
                $pdf->Cell(40,10,"Training Date(s)",1);
                $pdf->Cell(40,10,"Immediate",1);
                $pdf->Ln(20);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(142,182,212);
                $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                //$pdf->Cell(35,8,"Price",1,0, 'C', 1);
                $pdf->Cell(35,8,"Amount(S$)",1,0, 'C', 1);
                $pdf->Ln();
            }

            $pdf->SetFont('Arial','',10);
            
            if($row['module'] == 'edukloud_course'){
                $courseTxt = 'Programme Fee :' . '[ ' . $row['contact_name'] .' ]';
            }
            else if($row['module'] == 'edukloud_subsidy'){
                $courseTxt = 'Rebate for Tuition Fee :' . '[ ' . $row['contact_name'] .' ]';
            }
            else if($row['module'] == 'edukloud_discount'){
                $courseTxt = 'Discount';
                $discount_price = $row['unit_price'];
            }
            else{
                $courseTxt = ''; 
            }
            
            $unit_price = $row['unit_price'];
            
            if($row['module'] != 'edukloud_discount'){
                $pdf->SetFillColor(224,235,255);
                $pdf->Cell(20, 10, $row['qty'],1, 0, 'L', 1);
                $pdf->Cell(135, 10, $courseTxt,1, 0, 'L', 1);
                $pdf->Cell(35, 10, $unit_price, 1, 0, 'R', 1);
                //$pdf->Cell(35, 10, $unit_price, 1, 0, 'R', 1);
            }
            $total += $row['unit_price'];

            if($row['module'] != 'edukloud_discount'){
                $pdf->Ln();
            }
            $count++;
        } 

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }

        $pdf->SetFillColor(255,191,161);
        $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
        $pdf->Cell(35,8,$total,1,  0, 'R', 1);

        $pdf->Ln(40);
        $pdf->Cell(70, 8, 'Cheque should be Crossed and Issued to :');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, 'INSTITUTE INTERNATIONAL INSTITUTE');
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        
        //$pdf->Output('Invoice.pdf','D');
        $pdf->Output();
    }

    /**
     *
     */
    function getPrintInvoice() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $invoice_code = $fn->getReqParam('invoice_code');

        $SQL = "
        SELECT i.*
        FROM invoice i
        WHERE i.invoice_code = {$invoice_code}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $numrowsInvoiceItem = 1;

        $unitPriceAmt = 0;
        $invoiceItemQty = 0;
        $subsidyAmt = 0;
        $contactPerson = '';
        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $SQLInvoiceItem = "
            SELECT it.*
            FROM invoice_item it
            WHERE it.invoice_id = {$row['invoice_id']}
            ";
            $resultInvoiceItem = $db->sql_query($SQLInvoiceItem);
            $numRowsInvoiceItem  = $db->sql_numrows($resultInvoiceItem);

            while ($invoiceItemRec = $db->sql_fetchrow($resultInvoiceItem)) {
                if ($count == 0){
                    
                    $orderRec       = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                    $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $row['order_id']);
                    $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                    $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                    $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                    
                    $pdf->SetXY(10,1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Rect(10 , 5, 80, 38, 'F');
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                    $pdf->SetFont('Arial','',7);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                    $pdf->Ln(15);
                
                    $pdf->Image('images/logo-print.jpg',157,5,45);
                
                    $pdf->SetFont('Arial', 'B', 22);
                    $pdf->SetXY(157, 35);
                    $pdf->Cell(40, 20, "Invoice");
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetXY(157, 45);
                    $pdf->Cell(21, 20, "Invoice No : ");                
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 20, $row['invoice_code']);                
                    $pdf->Ln(5);
                
                    $pdf->SetX(157);
                    $pdf->SetFont('Arial','B',10);
                    $date = $fn->getCPDate($row['invoice_date'], 'd-M-Y');
                    $pdf->Cell(11, 20, "Date : ");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 20, $date);
                
                    $pdf->SetXY(10, 40);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(50, 20, "Bill To :");
                    $pdf->SetFillColor(224,235,255);
                    $pdf->Rect(10, 53, 75, 30, 'D');
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetXY(10, 45);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                    $pdf->SetXY(10, 50);
                    $pdf->Cell(50, 20, $companyRec['title']);
                    $pdf->SetXY(10, 55);
                    $pdf->Cell(50, 20, $companyRec['address1']);
                    $pdf->SetXY(10, 60);
                    $pdf->Cell(50, 20, $companyRec['address2']);
                    $pdf->SetXY(10, 65);
                    $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                    $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                    $pdf->Ln(35);
                
                    $pdf->SetXY(105, 85);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Telephone :",1 ,0, 'R', 1);
                    $pdf->SetFont('Arial','',10);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Cell(69, 5, $companyRec['phone'],1 ,0, 'L', 1);
                    $pdf->Ln(4);
                
                    $pdf->SetXY(10, 90);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Attention : ",1 ,0, 'L', 1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(70, 5, 'ACCOUNTS DEPARTMENT',1 ,0, 'L', 1);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Fax :",1 ,0, 'R', 1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(69, 5, $companyRec['fax'],1 ,0, 'L', 1);
                    $pdf->Ln(10);
                
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(80, 8, "Program",1 ,0, 'C', 1);
                    $pdf->Cell(30, 8, "Program Code",1 ,0, 'C', 1);
                    $pdf->Cell(45, 8, "Training Date(s)",1 ,0, 'C', 1);
                    $pdf->Cell(35, 8, "Term",1 ,0, 'C', 1);
                
                    $pdf->Ln();
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(80,10,$courseRec['course_code'] . ' ' . $courseRec['title'],1 ,0, 'C', 0);
                    $pdf->Cell(30,10,$courseRec['course_code'],1 ,0, 'C', 0);
                
                    $date_from = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                    $date_to = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                    $date = $date_from . ' - ' . $date_to;
                    $pdf->Cell(45,10, $date,1 ,0, 'C', 0);
                    $pdf->Cell(35,10,"Immediate",1 ,0, 'C', 0);
                    $pdf->Ln(14);
                
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                    $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                    $pdf->Cell(35,8,"Amount(S$)",1,0, 'R', 1);
                    $pdf->Ln();
                }
                
                $invoiceItemQty += $invoiceItemRec['qty'];

                $unitPriceAmt += $invoiceItemRec['unit_price'];
                $unitPrice = number_format($unitPriceAmt, 2);

                $subsidyAmt += $invoiceItemRec['subsidy'];
                $subsidy = number_format($subsidyAmt, 2);
                
                if ($numrowsInvoiceItem == $numRowsInvoiceItem) {

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Cell(20, 10, $invoiceItemQty,1, 0, 'C', 1);
                    $pdf->Cell(135, 10, $invoiceItemRec['item_title'],1, 0, 'L', 1);
                    $pdf->Cell(35, 10, $unitPrice, 1, 0, 'R', 1);
                    $pdf->Ln(7);
                    
                    $pdf->Cell(20, 10, $invoiceItemQty, 'L', 0, 'C', 1);
                    $pdf->Cell(135, 10, 'Rebate', 'L', 0, 'L', 1);
                    $pdf->Cell(35, 10, $subsidy, 'LR', 0, 'R', 1);
                    $pdf->Ln(10); 
                }
                    
                /* SQL for more than one contact for the invoice*/
                $SQLContact = "
                SELECT CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
                      ,c.id_card_no
                FROM contact c
                WHERE c.contact_id = {$invoiceItemRec['contact_id']}
                ";
                $resultContact = $db->sql_query($SQLContact);
                /*while ($rowContact = $db->sql_fetchrow($resultContact)) {
                    $contactPerson .= $rowContact['contact_name'] . ' ' . $rowContact['id_card_no'];

            $pdf->Cell(20, 10, '', 'L', 0, 'L', 1);
            $pdf->Cell(135, 10, $contactPerson, 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, '', 'LR', 0, 'L', 1);
            $pdf->Ln();
                }*/

                $numrowsInvoiceItem++;  
            }
            $YVariable = 145;
            /*$pdf->SetXY(45, $YVariable);
            $pdf->Cell(35,10,'123',1 ,0, 'L', 0);
            $YVariable = $YVariable + 5;
            $pdf->SetXY(45, $YVariable);
            $pdf->Cell(35,10,'456','LR' ,0, 'L', 0);
            $pdf->Ln();*/

            /*if ($count == 0){
                $pdf->Cell(20, 10, '', 'L', 0, 'L', 1);
                $pdf->Cell(135, 10, 'Name of Trainee(s):', 'L', 0, 'L', 1);
                $pdf->Cell(35, 10, '', 'LR', 0, 'L', 1);
            }

            $pdf->Ln(7);
            $pdf->Cell(20, 10, '', 'L', 0, 'L', 1);
            $pdf->Cell(135, 10, $contactPerson, 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, '', 'LR', 0, 'L', 1);
            $pdf->Ln();*/
            
            $total = $unitPrice + $subsidy;
            $count++;  
        } 

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }

        $total = number_format($total, 2);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,8,$total,1,  0, 'R', 1);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(150, 8, 'Remarks');
        $pdf->Ln(4);

        /*$pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();*/

        $pdf->Ln(40);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(67, 8, 'Cheque should be Crossed and Issued to');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, $cpCfg['printCompanyName']);
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        
        //$pdf->Output('Invoice.pdf','D');
        $pdf->Output();
    }

    /**
     *
     */
    function getGenerateInvoiceForMedia($invoice_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT i.*
        FROM invoice i
        WHERE i.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $numrowsInvoiceItem = 1;

        $unitPriceAmt = 0;
        $invoiceItemQty = 0;
        $subsidyAmt = 0;
        $contactPerson = '';
        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $SQLInvoiceItem = "
            SELECT it.*
            FROM invoice_item it
            WHERE it.invoice_id = {$row['invoice_id']}
            ";
            $resultInvoiceItem = $db->sql_query($SQLInvoiceItem);
            $numRowsInvoiceItem  = $db->sql_numrows($resultInvoiceItem);

            while ($invoiceItemRec = $db->sql_fetchrow($resultInvoiceItem)) {
                if ($count == 0){
                    
                    $orderRec       = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                    $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $row['order_id']);
                    $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                    $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                    $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                    
                    $pdf->SetXY(10,1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Rect(10 , 5, 80, 38, 'F');
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                    $pdf->SetFont('Arial','',7);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                    $pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                    /*$pdf->Ln(5);
                    $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);*/
                    $pdf->Ln(15);
                
                    $pdf->Image('images/logo-print.jpg',157,5,45);
                
                    $pdf->SetFont('Arial', 'B', 22);
                    $pdf->SetXY(157, 35);
                    $pdf->Cell(40, 20, "Invoice");
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetXY(157, 45);
                    $pdf->Cell(21, 30, "Invoice No : ");                
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 30, $row['invoice_code']);                
                    $pdf->Ln(5);
                
                    $pdf->SetX(157);
                    $pdf->SetFont('Arial','B',10);
                    $date = $fn->getCPDate($row['invoice_date'], 'd-M-Y');
                    $pdf->Cell(11, 30, "Date : ");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 30, $date);
                    $pdf->Ln(5);
                
                    $pdf->SetX(157);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(11, 30, "Term : ");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 30, ' Immediate');
                    $pdf->Ln(5);
                
                    $pdf->SetX(157);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(11, 30, "Pages ");
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 30, '1/1');

                    $pdf->SetXY(10, 40);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->Cell(50, 20, "Bill To :");
                    $pdf->SetFillColor(224,235,255);
                    $pdf->Rect(10, 53, 75, 30, 'D');
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetXY(10, 45);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                    $pdf->SetXY(10, 50);
                    $pdf->Cell(50, 20, $companyRec['title']);
                    $pdf->SetXY(10, 55);
                    $pdf->Cell(50, 20, $companyRec['address1']);
                    $pdf->SetXY(10, 60);
                    $pdf->Cell(50, 20, $companyRec['address2']);
                    $pdf->SetXY(10, 65);
                    $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                    $pdf->SetXY(10, 70);
                    $pdf->SetFont('Arial','',8);
                    $pdf->Cell(60, 20, $companyRec['phone']);
                    $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                    $pdf->Ln(35);
                
                    /*$pdf->SetXY(105, 85);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Telephone :",1 ,0, 'R', 1);
                    $pdf->SetFont('Arial','',10);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Cell(69, 5, $companyRec['phone'],1 ,0, 'L', 1);
                    $pdf->Ln(4);
                
                    $pdf->SetXY(10, 90);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Attention : ",1 ,0, 'L', 1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(70, 5, 'ACCOUNTS DEPARTMENT',1 ,0, 'L', 1);
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(25, 5, "Fax :",1 ,0, 'R', 1);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(69, 5, $companyRec['fax'],1 ,0, 'L', 1);
                    $pdf->Ln(10);*/
                
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(115, 8, "Program",1 ,0, 'C', 1);
                    $pdf->Cell(30, 8, "Batch",1 ,0, 'C', 1);
                    $pdf->Cell(50, 8, "Training Date(s)",1 ,0, 'C', 1);
                
                    $pdf->Ln();
                    $pdf->SetFont('Arial','',10);
                    $pdf->Cell(115,10,$courseRec['course_code'] . ' ' . $courseRec['title'],1 ,0, 'C', 0);
                    $pdf->Cell(30,10,$courseRec['course_code'],1 ,0, 'C', 0);
                
                    $date_from = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                    $date_to = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                    $date = $date_from . ' - ' . $date_to;
                    $pdf->Cell(50,10, $date,1 ,0, 'C', 0);
                    $pdf->Ln(14);
                
                    $pdf->SetFont('Arial','B',10);
                    $pdf->SetFillColor(254,203,156);
                    $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                    $pdf->Cell(115,8,"Description",1,0, 'C', 1);
                    $pdf->Cell(25,8,"Price",1,0, 'C', 1);
                    $pdf->Cell(35,8,"Amount(S$)",1,0, 'C', 1);
                    $pdf->Ln();
                    
                    $singleUnitPrice = $invoiceItemRec['unit_price'];
                    $singleSubsidyVal = $invoiceItemRec['subsidy'];
                }
                
                $discount_price += $invoiceItemRec['discount'];
                $invoiceItemQty += $invoiceItemRec['qty'];

                $unitPriceAmt += $invoiceItemRec['unit_price'];
                $unitPrice = number_format($unitPriceAmt, 2);

                $subsidyAmt += $invoiceItemRec['subsidy'];
                $subsidy = number_format($subsidyAmt, 2);
                
                if ($numrowsInvoiceItem == $numRowsInvoiceItem) {

                    $pdf->SetFont('Arial','',10);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Cell(20, 10, $invoiceItemQty,1, 0, 'C', 1);
                    $pdf->Cell(115, 10, $invoiceItemRec['item_title'],1, 0, 'L', 1);
                    $pdf->Cell(25, 10, $singleUnitPrice,1, 0, 'C', 1);
                    $pdf->Cell(35, 10, $unitPrice, 1, 0, 'R', 1);
                    $pdf->Ln(7);
                    
                    $pdf->Cell(20, 10, $invoiceItemQty, 'L', 0, 'C', 1);
                    $pdf->Cell(115, 10, 'Rebate', 'L', 0, 'L', 1);
                    $pdf->Cell(25, 10, $singleSubsidyVal, 'L', 0, 'C', 1);
                    $pdf->Cell(35, 10, $subsidy, 'LR', 0, 'R', 1);
                    $pdf->Ln(10); 
                }
                    
                /* SQL for more than one contact for the invoice*/
                //$contactPerson = '';
                $SQLContact = "
                SELECT CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
                      ,c.id_card_no
                FROM contact c
                WHERE c.contact_id = {$invoiceItemRec['contact_id']}
                ";
                $resultContact = $db->sql_query($SQLContact);
                $numRowsContact  = $db->sql_numrows($resultContact);
                while ($rowContact = $db->sql_fetchrow($resultContact)) {
                    $contactPerson .= $rowContact['contact_name'] . ' (' . $rowContact['id_card_no'] . ') , ';

            /*$pdf->Cell(20, 10, '', 'L', 0, 'L', 1);
            $pdf->Cell(135, 10, $contactPerson, 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, '', 'LR', 0, 'L', 1);
            $pdf->Ln();*/
                }

                $numrowsInvoiceItem++;  
            }
            $YVariable = 145;
            /*$pdf->SetXY(45, $YVariable);
            $pdf->Cell(35,10,'123',1 ,0, 'L', 0);
            $YVariable = $YVariable + 5;
            $pdf->SetXY(45, $YVariable);
            $pdf->Cell(35,10,'456','LR' ,0, 'L', 0);
            $pdf->Ln();*/

            /*if ($count == 0){
                $pdf->Cell(20, 10, '', 'L', 0, 'L', 1);
                $pdf->Cell(135, 10, 'Name of Trainee(s):', 'L', 0, 'L', 1);
                $pdf->Cell(35, 10, '', 'LR', 0, 'L', 1);
            }

            $pdf->Ln(7);
            $pdf->Cell(20, 10, '', 'L', 0, 'L', 1);
            $pdf->Cell(135, 10, $contactPerson, 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, '', 'LR', 0, 'L', 1);
            $pdf->Ln();*/
            
            $total = $unitPrice + $subsidy;
            $count++;
            $order_id = $row['order_id'];
            $invoice_code = $row['invoice_code'];
        } 

        if($discount_price){
            $discount_price = number_format($discount_price, 2);

            $pdf->Cell(20, 10, "1", 'L', 0, 'C', 1);
            $pdf->Cell(115, 10, 'Discount', 'L', 0, 'L', 1);
            $pdf->Cell(25, 10, '', 'L', 0, 'C', 1);
            $pdf->Cell(35, 10, $discount_price, 'LR', 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }

        $total = $total + $discount_price;
        $total = number_format($total, 2);
        //$pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 10, '', 'LB', 0, 'C', 1);
        $pdf->Cell(115, 10, '', 'LB', 0, 'L', 1);
        $pdf->Cell(25, 10, '', 'L', 0, 'C', 1);
        $pdf->Cell(35, 10, '', 'LR', 0, 'R', 1);
        $pdf->Ln(10);

        /*
        $pdf->Cell(155, 8,$contactPerson ,1, 0, 'L', 1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,8,'',1,  0, 'R', 1);
        $pdf->Ln(10);
        */

        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 8,'');
        $pdf->Cell(115, 8,'');
        $pdf->Cell(25, 8,'Total',1, 0, 'C', 1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,8,$total,1,  0, 'R', 1);
        $pdf->Ln(10);
        $contactPerson = substr($contactPerson, 0,-2);
        $contactPerson .=  $contactPerson;
        $contactPerson .=  $contactPerson;
        $contactPerson .=  $contactPerson;
        //function drawTextBox($strText, $w, $h, $align='L', $valign='T', $border=true)
        //$pdf->Line(0,0,100,100);
        //$pdf->Cell(0, 0, "Name of Trainee(s) : ". $contactPerson, 'TB');
        $pdf->drawTextBox("List of Trainees : ". $contactPerson, 0, 0, 'L', 'T', 'TB');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(150, 8, 'Remarks');
        $pdf->Ln(4);

        /*$pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();*/

        $pdf->Ln(30);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(67, 8, 'Cheque should be Crossed and Issued to');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, $cpCfg['printCompanyName']);
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        
        /* Creation of media record of the invoice */
        $file_name = 'Invoice_INV_' . $invoice_code . '_' . date('Y-m-d') .'.pdf';
        $pdf->Output($file_name,'D');
        return;

        /* Condition for folder path with regards to local and other sites */
        if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }
        
        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $invoice_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_invoice';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        /* Condition for folder path with regards to local and other sites */
        if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getPrintReceipt() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $receipt_code = $fn->getReqParam('receipt_code');

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_code = {$receipt_code}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                
                $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $row['order_id']);
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $companyRec = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                $orderItemRec = $fn->getRecordRowByID('order_item', 'order_id', $row['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_id', $invoiceRec['invoice_id']);

                /* Institute company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Receipt");
                $code = 'Receipt No : '. $row['receipt_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(50, 20, $code );                
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Company */
                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 20, "Received from");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $companyRec['title']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $companyRec['address1']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(50, 20, $companyRec['address2']);
                $pdf->SetXY(10, 65);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)",1,0, 'R', 1);
                $pdf->Ln();
                
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(180, 10, $courseRec['title']);
                $pdf->Ln(7);

                $pdf->Cell(180, 10, "Intake : ");
                $pdf->Ln(7);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $course_date = $from_date . ' to ' . $to_date;
                $pdf->Cell(180, 10, "From : " . $course_date);
                $pdf->Ln(7);
            }

            /* List of invoice items for the invoice */
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, $invoiceItemRec['item_title']);
            $pdf->Cell(35, 10, $invoiceItemRec['unit_price'], 0, 0, 'R', 1);
            $pdf->Ln(7);
            
            $lineItemNumber++; // To increment the line item in receipt

            $subsidy = number_format($invoiceItemRec['subsidy'], 2);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate');
            $pdf->Cell(35, 10, $subsidy, 0, 0, 'R', 1);
            $pdf->Ln();
            
            $lineItemNumber++; // To increment the line item in receipt
                
            if ($count == 0){
                $pdf->Cell(135, 10, 'Name of Trainee(s):');
                $pdf->Cell(35, 10, '');
            }
            
            /* SQL for more than one contact for the receipt*/
            $SQLContact = "
            SELECT CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
                  ,c.id_card_no
            FROM contact c
            WHERE c.contact_id = {$invoiceItemRec['contact_id']}
            ";
            $resultContact = $db->sql_query($SQLContact);
            
            while ($rowContact = $db->sql_fetchrow($resultContact)) {
                $pdf->Ln(6);
                $contactPerson = $rowContact['contact_name'] . ' ' . $rowContact['id_card_no'];
                $pdf->Cell(135, 10, $contactPerson);
                $pdf->Cell(35, 10, '');
                $pdf->Ln();
            }

            $total += $invoiceItemRec['unit_price'] + $subsidy;
            $amount_paid = $row['amount'];
            $invoice_code = $invoiceRec['invoice_code'];
            $remarks = $row['remarks'];
                
            $count++;
        } 

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }
        
        /* Total amount to be paid */
        $total = number_format($total, 2);
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        //$pdf->Cell(50, 8,'Total Amount Payable',1, 0, 'L', 1);
        $pdf->Cell(50, 8,'Total Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Total amount paid */
        /*$pdf->SetX(115);
        $pdf->SetFillColor(255,191,161);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Tender Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $amount_paid, 0, 0, 'R');
        $pdf->Ln();*/

        /* Balance to be given */
        /*$change = $amount_paid - $total;
        $change = number_format($change, 2);
        $pdf->SetX(115);
        $pdf->SetFillColor(255,191,161);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Change',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $change, 0, 0, 'R');
        $pdf->Ln(10);*/

        /* Cheque Details */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 8, 'Payment Method');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        $pdf->Ln(5);
        
        /* Notes */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 8, 'Notes:');
        $pdf->Ln(4);
        
        $value = 'Invoice No: ' . $invoice_code . ', Total Invoice Amount: SGD$ ' . $total;
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $value);
        $pdf->Ln(4);

        $outstanding = 'Total outstanding payable amount exclude payment advices/invoices issued: Amount: SGD$ ';
        $pdf->Cell(150, 8, $outstanding);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();

        //$pdf->Output('Invoice.pdf','D');
        $pdf->Output();
    }

    /**
     *
     */
    function getGenerateReceiptForMedia($receipt_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                
                $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $row['order_id']);
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $companyRec = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                $orderItemRec = $fn->getRecordRowByID('order_item', 'order_id', $row['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_id', $invoiceRec['invoice_id']);
                
                /*$SqlTotalUnitPrice = "
                SELECT SUM(unit_price) AS total_price FROM invoice_item
                WHERE invoice_id = {$invoiceRec['invoice_id']}
                ";
                $resultTotalUnitPrice = $db->sql_query($SqlTotalUnitPrice);
                $rowTotalUnitPrice = $db->sql_fetchrow($resultTotalUnitPrice);

                $SqlTotalSubsidy = "
                SELECT SUM(subsidy) AS total_subsidy FROM invoice_item
                WHERE invoice_id = {$invoiceRec['invoice_id']}
                ";
                $resultTotalSubsidy = $db->sql_query($SqlTotalSubsidy);
                $rowTotalSubsidy = $db->sql_fetchrow($resultTotalSubsidy);

                $SqlTotalDisc = "
                SELECT SUM(discount) AS total_discount FROM invoice_item
                WHERE invoice_id = {$invoiceRec['invoice_id']}
                ";
                $resultTotalDisc = $db->sql_query($SqlTotalDisc);
                $rowTotalDisc = $db->sql_fetchrow($resultTotalDisc);
                
                $total = number_format($rowTotalUnitPrice['total_price'] + $rowTotalSubsidy['total_subsidy'] + $rowTotalDisc['total_discount'], 2);
                */
                
                /* Institute company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Receipt");
                $code = 'Receipt No : '. $row['receipt_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(50, 20, $code );                
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Company */
                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 20, "Received from");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $companyRec['title']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $companyRec['address1']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(50, 20, $companyRec['address2']);
                $pdf->SetXY(10, 65);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No", 0,0, 'C', 1);
                $pdf->Cell(135,8,"Description", 0,0, 'L', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)", 0,0, 'R', 1);
                $pdf->Ln();
                
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(180, 10, $courseRec['title']);
                $pdf->Ln(7);

                $pdf->Cell(180, 10, "Batch : ");
                $pdf->Ln(7);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $course_date = $from_date . ' to ' . $to_date;
                $pdf->Cell(180, 10, "From : " . $course_date);
                $pdf->Ln(7);
            }

            /* List of invoice items for the invoice */
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, $invoiceItemRec['item_title']);
            $pdf->Cell(35, 10, $invoiceItemRec['unit_price'], 0, 0, 'R', 1);
            $pdf->Ln(7);
            
            $lineItemNumber++; // To increment the line item in receipt

            $subsidy = number_format($invoiceItemRec['subsidy'], 2);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate');
            $pdf->Cell(35, 10, $subsidy, 0, 0, 'R', 1);
            $pdf->Ln();
            
            $lineItemNumber++; // To increment the line item in receipt
                
            /*if ($count == 0){
                $pdf->Cell(135, 10, 'Name of Trainee(s):');
                $pdf->Cell(35, 10, '');
            }*/
            
            /* SQL for more than one contact for the receipt*/
            //$contactPerson = '';
            $SQLContact = "
            SELECT CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
                  ,c.id_card_no
            FROM contact c
            WHERE c.contact_id = {$invoiceItemRec['contact_id']}
            ";
            $resultContact = $db->sql_query($SQLContact);
            $numRowsContact  = $db->sql_numrows($resultContact);
            
            while ($rowContact = $db->sql_fetchrow($resultContact)) {
                $pdf->Ln(6);
                $contactPerson .= $rowContact['contact_name'] . ' ' . $rowContact['id_card_no'] . ' ';
            }
                /*$pdf->Cell(135, 10, $contactPerson);
                $pdf->Cell(35, 10, '');
                $pdf->Ln();*/

            $total += $invoiceItemRec['unit_price'] + $subsidy;
            $amount_paid = $row['amount'];
            $invoice_code = $invoiceRec['invoice_code'];
            $invoice_amt = $invoiceRec['invoice_amount'];
            $invoice_amt = number_format($invoice_amt, 2);
            $remarks = $row['remarks'];
            $order_id = $row['order_id'];
            $receipt_code = $row['receipt_code'];
                
            $count++;
        } 

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }
        
        $pdf->Ln(10);
        
        $contactPerson = substr($contactPerson, 0,-2);
        $contactPerson .=  $contactPerson;
        $contactPerson .=  $contactPerson;
        $contactPerson .=  $contactPerson;
        //function drawTextBox($strText, $w, $h, $align='L', $valign='T', $border=true)
        //$pdf->Line(0,0,100,100);
        $pdf->Cell(0, 0, "Name of Trainee(s) : ". $contactPerson, 'TB');
        //$pdf->drawTextBox("Name of Trainee(s) : ". $contactPerson, 0, 0, 'L', 'T', 0);
        $pdf->Ln(5);

        /* Total amount to be paid */
        $total = number_format($total, 2);
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        //$pdf->Cell(50, 8,'Total Amount Payable',1, 0, 'L', 1);
        $pdf->Cell(50, 8,'Total Amount', 0, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Total amount paid */
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Tender Amount', 0, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $amount_paid, 0, 0, 'R');
        $pdf->Ln();

        /* Balance to be given */
        $change = $amount_paid - $total;
        $change = number_format($change, 2);
        $change = 0;
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Change', 0, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $change, 0, 0, 'R');
        $pdf->Ln(10);

        /* Cheque Details */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 8, 'Payment Method');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        $pdf->Ln(5);
        
        /* Notes */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 8, 'Notes:');
        $pdf->Ln(4);
        
        $value = 'Invoice No: ' . $invoice_code . ', Total Invoice Amount: SGD$ ' . $invoice_amt;
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $value);
        $pdf->Ln(4);

        $outstanding = 'Total outstanding payable amount exclude payment advices/invoices issued: Amount: SGD$ ';
        $pdf->Cell(150, 8, $outstanding);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();

        /* Creation of media record of the invoice */
        $file_name = 'Receipt_REC_' . $receipt_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }*/
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_receipt';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }*/
        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     * Receipt PDF for Enterprise edition of edukloud
     */
    function getPrintReceiptInFpdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $receipt_id = $fn->getReqParam('record_id');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $mode_of_payment      = $row['mode_of_payment'];
            $cheque_no            = $row['cheque_no'];
            $bank                 = $row['bank_name'];
            $issued_by            = $row['issued_by'];
            $coi_no               = $row['coi_no'];
            $approval_code        = $row['approval_code'];
            $receipt_code         = $row['receipt_code'];
            $discount_amount      = $row['discount_amount'];
            $total_receipt_amount = $row['amount'];
            
            $amount_paid          = $total_receipt_amount;

            $orderRec           = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $orderRec['order_id']);
            $contactRec         = $fn->getRecordRowByID('contact', 'contact_id', $courseContactRec['contact_id']);
            $courseRec          = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
            $parContRec         = $fn->getRecordRowByID('parent_contact', 'contact_id', $contactRec['contact_id']);
            $parentRec          = $fn->getRecordRowByID('parent', 'parent_id', $parContRec['parent_id']);
            $countryRec         = $fn->getRecordByCondition('geo_country', "country_code = '{$parentRec['address_country']}'");
                        
            /* Logo of the institution */
            $pdf->Image('images/logo-print.jpg', 10, 5, 45);

            /* Institute company address */
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetXY(80,1);
            $pdf->Cell(20, 20, $cpCfg['printCompanyName']);
            $pdf->SetXY(80,5);
            $pdf->Cell(24, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
            $pdf->SetXY(80,10);
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(28, 20, $cpCfg['printTelephoneAndFax']);
            $pdf->SetXY(80,15);
            $pdf->Cell(20, 20, $cpCfg['printEmailAndWebsite']);
            $pdf->SetXY(80,20);
            $pdf->Cell(20, 20, $cpCfg['printRegistrationNo']);
            $pdf->Ln(10);

            $pdf->SetFont('Arial','B',10);
            $pdf->SetXY(100, 35);
            $pdf->Cell(21, 20, "OFFICIAL RECEIPT", 0, 0, 'C');                
            $pdf->Ln(10);

            /* Parent Name */
            $pdf->SetFont('Arial','',10);
            $pdf->SetX(10);
            $parent_full_name = $parentRec['first_name'] . ' ' . $parentRec['last_name'];
            $parent_name = 'To: ' . $parent_full_name;
            $pdf->Cell(50, 20, $parent_name);
            $pdf->Ln(6);
            
            /* Recepit code*/
            $pdf->SetX(10);
            $code = 'Receipt No : '. $row['receipt_code'];
            $pdf->Cell(50, 20, $code);                
            
            /* Receipt Date */
            $pdf->SetX(157);
            $date = $fn->getCPDate($row['date'], 'dS F Y');
            $receipt_date = 'Date: ' . $date;
            $pdf->Cell(50, 20, $receipt_date);

            $pdf->SetXY(10, 60);
            $pdf->Cell(50, 20, 'Address :');

            $pdf->SetXY(10, 65);
            $pdf->Cell(150, 20, $parentRec['address_flat']);
            
            $y_property = 70;
            if ($parentRec['address_street']) {
                $pdf->SetXY(10, 70);
                $pdf->Cell(150, 20, $parentRec['address_street']);
                
                $y_property = 75;
            }
            
            $country_name = $countryRec['name'];
            if ($country_name == '') {
                $country_name = 'Singapore';    
            }
            
            $country = $country_name . ' - ' . $parentRec['address_po_code'];
            $pdf->SetXY(10, $y_property);
            $pdf->Cell(250, 20, $country);                

            $pdf->Ln(16);

            //===================================FIRST TABLE============================= //
            $pdf->SetX(10);
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(190, 8, 'Details of Payment', 1);
            $pdf->Ln();
            
            /* List of invoice items header */
            $pdf->SetFont('Arial','B',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(114, 8, "Description", 1, 0, 'L', 1);
            $pdf->Cell(50, 8, "Month", 1, 0, 'L', 1);
            $pdf->Cell(26, 8, "Amount (SGD)", 1, 0, 'R', 1);
            $pdf->Ln();
            
            /* SQL to find one or more contact details for the receipt */
            $SQLContDet = "
            SELECT irh.*
                  ,c.contact_id
                  ,c.first_name
                  ,c.last_name
                  ,o.year_of_enrollment
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (contact c) ON (i.contact_id = c.contact_id)
            LEFT JOIN (`order` o) ON (i.order_id   = o.order_id)
            WHERE irh.receipt_id = {$row['receipt_id']}
            GROUP BY c.contact_id
            ";
            $resultContDet = $db->sql_query($SQLContDet);
            $countIrh            = 1;
            $total_amount        = 0;
            $total_reg_fee       = 0;
            $reg_fee             = 0;
            $amount_already_paid = 0;
            
            while ($rowContDet = $db->sql_fetchrow($resultContDet)) {
                $pdf->SetFont('Arial','',10);
                $pdf->SetFillColor(255,255,255);
                
                #$current_year   = date('Y');
                $current_year   = $rowContDet['year_of_enrollment'];
                $reg_fee_amount = 0;
                //To get the relevant record of student from invoice, who has registration fee as 1 with status paid.
                $sqlRegFee = "
                SELECT i.add_registration_fee
                      ,i.invoice_amount
                FROM invoice i
                LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
                LEFT JOIN (invoice_receipt_history irh) ON (i.invoice_id = irh.invoice_id)
                WHERE o.year_of_enrollment >= '{$current_year}'
                  AND i.contact_id = {$rowContDet['contact_id']}
                  AND irh.receipt_id = {$row['receipt_id']}
                ";
                $resultRegFee = $db->sql_query($sqlRegFee);
                $rowRegFee    = $db->sql_fetchrow($resultRegFee);
                
                if ($rowRegFee['add_registration_fee'] > 0) {
                    $site_id        = $fn->getSessionParam('cp_site_id');
                    $rowFeeforReg   = $fn->getRecordByCondition('setting', "key_text = 'registrationFeeEnt' AND site_id = {$site_id}");
                    $reg_fee_amount = $rowFeeforReg['value'];
                    
                    $reg_fee_amount = $rowRegFee['invoice_amount'];
                }
                                      
                $header = "School Fees for " . $rowContDet['first_name'] . ' ' . $rowContDet['last_name'];
                
                /* Border for the top row and not for more than two contacts */
                $border = 'LTR';
                if ($countIrh > 1) {
                    $border = 'LR';
                }
                $pdf->Cell(114, 8, $header, $border, 0, 'L', 1);
                
                /* SQL to find the paid months and amount paid for the receipt with respect to previous SQL contact */
                $rows_month = '';
                $countMonth = 1;
                $SQLMonth = "
                SELECT i.invoice_month
                      ,i.invoice_amount
                      ,i.invoice_id
                      ,irh.amount
                FROM invoice i
                LEFT JOIN (invoice_receipt_history irh) ON (i.invoice_id = irh.invoice_id)
                WHERE irh.receipt_id = {$row['receipt_id']}
                  AND i.contact_id = {$rowContDet['contact_id']}
                  AND i.add_registration_fee IS NULL
                ";
                $resultMonth = $db->sql_query($SQLMonth);
                $numRowsMonth  = $db->sql_numrows($resultMonth);
                $individual_amount = 0;
                while ($rowMonth = $db->sql_fetchrow($resultMonth)) {
                    
                    /* Previous payment calculation */
                    $sqlPreviousPayment = "
                    SELECT SUM(amount) AS total_amount_paid
                    FROM invoice_receipt_history
                    WHERE invoice_id = {$rowMonth['invoice_id']}
                      AND receipt_id != {$row['receipt_id']}
                      AND receipt_id < {$row['receipt_id']}
                    ";
                    $resultPreviousPayment = $db->sql_query($sqlPreviousPayment);
                    $rowPreviousPayment    = $db->sql_fetchrow($resultPreviousPayment);
                    
                    $amount_already_paid += $rowPreviousPayment['total_amount_paid'];

                    /* Individual total sum */
                    $individual_amount += $rowMonth['invoice_amount'];
                    
                    $appendComma = '';
                    if ($numRowsMonth > 1 && $countMonth != $numRowsMonth) {
                        $appendComma = ', ';
                    }
    		        
    		        if ($rowMonth['invoice_month'] == 1) {
                        $rows_month = $rows_month .'Jan' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 2) {
                        $rows_month = $rows_month . 'Feb' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 3) {
                        $rows_month = $rows_month . 'Mar' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 4) {
                        $rows_month = $rows_month . 'Apr' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 5) {
                        $rows_month = $rows_month . 'May' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 6) {
                        $rows_month = $rows_month . 'Jun' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 7) {
                        $rows_month = $rows_month . 'Jul' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 8) {
                        $rows_month = $rows_month . 'Aug' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 9) {
                        $rows_month = $rows_month . 'Sep' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 10) {
                        $rows_month = $rows_month . 'Oct' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 11) {
                        $rows_month = $rows_month . 'Nov' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 12) {
                        $rows_month = $rows_month . 'Dec' . $appendComma;
    		        }
    		        
    		        $countMonth++;
                    
                    /* Reg Fee for printing in media */
                    $SQLRegFee = "
                    SELECT irh.amount
                    FROM invoice_receipt_history irh
                    LEFT JOIN (invoice i) ON (i.invoice_id = irh.invoice_id)
                    WHERE irh.receipt_id = {$row['receipt_id']}
                      AND i.add_registration_fee IS NOT NULL
                    ";
                    $resultRegFee = $db->sql_query($SQLRegFee);

                    while ($rowRegFee = $db->sql_fetchrow($resultRegFee)) {
                        //$reg_fee += $rowRegFee['amount'];
                        //reg fee should be added only once(Syed)
                        $reg_fee = $rowRegFee['amount'];
                    }
                    
                }
                
                $pdf->Cell(50, 8, $rows_month, $border, 0, 'L', 1);
                $pdf->Cell(26, 8, number_format($individual_amount, 2), $border, 0, 'R', 1);
                $pdf->Ln();
                
                if ($reg_fee > 0) {
                    $reg_fee_header = "Registration Fees for " . $rowContDet['first_name'] . ' ' . $rowContDet['last_name'];
                    /* Registration Fees */
                    $pdf->Cell(114, 8, $reg_fee_header, 'LR', 0, 'L', 1);
                    $pdf->Cell(50, 8, "", 'LR', 0, 'L', 1);
                    $pdf->Cell(26, 8, number_format($reg_fee, 2), 'LR', 0, 'R', 1);
                    $pdf->Ln();
                }
                
                $total_amount  += $individual_amount;
                $total_reg_fee += $reg_fee;
                $months_paid    = $rows_month;
                
                $countIrh++;
            }
            
            $total_amount = $total_amount + $total_reg_fee;

            /* Total amount paid earlier */
            $pdf->Cell(114, 8,'Amount already Paid', 1, 0, 'L', 1);
            $pdf->Cell(50, 8, "", 1, 0, 'L', 1);
            $pdf->Cell(26, 8, number_format($amount_already_paid, 2), 1, 0, 'R', 1);
            $pdf->Ln();

            /* Total amount paid */
            $total_amount_paid = $amount_paid;
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(114, 8,'Amount Received Now', 1, 0, 'L', 1);
            $pdf->Cell(50, 8, "", 1, 0, 'L', 1);
            $pdf->Cell(26, 8, number_format($total_amount_paid, 2), 1, 0, 'R', 1);
            $pdf->Ln();

            /* Discount Offered */
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(114, 8, "Discount", 1, 0, 'L', 1);
            $pdf->Cell(50, 8, "", 1, 0, 'L', 1);
            $pdf->Cell(26, 8, number_format($discount_amount, 2), 1, 0, 'R', 1);
            $pdf->Ln();
                
            /* Total amount paid */
            $balance_amount = $total_amount - $total_amount_paid - $amount_already_paid - $discount_amount;
            $pdf->Cell(114, 8,'Balance Amount to be Paid', 1, 0, 'L', 1);
            $pdf->Cell(50, 8, "", 1, 0, 'L', 1);
            $pdf->Cell(26, 8, number_format($balance_amount, 2), 1, 0, 'R', 1);
            $pdf->Ln();
        }

        /* Cheque Details */
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(28, 8, 'Mode of Payment : ');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40, 8,  $mode_of_payment);
        $pdf->Ln(5);
       
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'Issued By : ' . $issued_by);
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'Signature:');
        $pdf->Ln(7);

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, 'Simply Islam issues receipts to acknowledge fees are paid accordingly and kindly check all details above are correct');
        $pdf->Ln(4);

        $pdf->Output();
    }

    /**
     * Receipt PDF for Enterprise edition of Edukloud
     */
    function getGenerateReceiptForEntMedia($receipt_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $mode_of_payment = $row['mode_of_payment'];
            $cheque_no       = $row['cheque_no'];
            $bank            = $row['bank_name'];
            $issued_by       = $row['issued_by'];
            $coi_no          = $row['coi_no'];
            $approval_code   = $row['approval_code'];
            $receipt_code    = $row['receipt_code'];

            $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
            $courseContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $orderRec['order_id']);
            $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $courseContactRec['contact_id']);
            $courseRec = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
            $parContRec = $fn->getRecordRowByID('parent_contact', 'contact_id', $contactRec['contact_id']);
            $parentRec = $fn->getRecordRowByID('parent', 'parent_id', $parContRec['parent_id']);
                        
            /* Logo of the institution */
            $pdf->Image('images/logo-print.jpg', 10, 5, 45);

            /* Institute company address */
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetXY(80,1);
            $pdf->Cell(20, 20, $cpCfg['printCompanyName']);
            $pdf->SetXY(80,5);
            $pdf->Cell(24, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
            $pdf->SetXY(80,10);
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(28, 20, $cpCfg['printTelephoneAndFax']);
            $pdf->SetXY(80,15);
            $pdf->Cell(20, 20, $cpCfg['printEmailAndWebsite']);
            $pdf->SetXY(80,20);
            $pdf->Cell(20, 20, $cpCfg['printRegistrationNo']);
            $pdf->Ln(10);

            $pdf->SetFont('Arial','B',10);
            $pdf->SetXY(100, 35);
            $pdf->Cell(21, 20, "OFFICIAL RECEIPT", 0, 0, 'C');                
            $pdf->Ln(10);

            /* Parent Name */
            $pdf->SetX(10);
            $parent_full_name = $parentRec['first_name'] . ' ' . $parentRec['last_name'];
            $parent_name = 'TO: ' . $parent_full_name;
            $pdf->Cell(50, 20, $parent_name);
            $pdf->Ln(6);
            
            /* Recepit code*/
            $pdf->SetX(10);
            $code = 'Receipt No : '. $row['receipt_code'];
            $pdf->Cell(50, 20, $code);                
            
            /* Receipt Date */
            $pdf->SetX(157);
            $date = $fn->getCPDate($row['date'], 'dS F Y');
            $receipt_date = 'Date: ' . $date;
            $pdf->Cell(50, 20, $receipt_date);
            $pdf->Ln(16);

            //===================================FIRST TABLE============================= //
            $pdf->SetX(10);
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(190, 8, 'Details of Payment', 1);
            $pdf->Ln();
            
            /* List of invoice items header */
            $pdf->SetFont('Arial','B',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(70, 8, "Description", 1, 0, 'L', 1);
            $pdf->Cell(94, 8, "Month", 1, 0, 'L', 1);
            $pdf->Cell(26, 8, "Amount (SGD)", 1, 0, 'R', 1);
            $pdf->Ln();
            
            /* SQL to find one or more contact details for the receipt */
            $SQLContDet = "
            SELECT irh.*
                  ,c.contact_id
                  ,c.first_name
                  ,c.last_name
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (contact c) ON (i.contact_id = c.contact_id)
            WHERE irh.receipt_id = {$row['receipt_id']}
            GROUP BY c.contact_id
            ";
            $resultContDet = $db->sql_query($SQLContDet);
            $countIrh = 1;
            $total_amount = 0;
            $reg_fee = 0;
            while ($rowContDet = $db->sql_fetchrow($resultContDet)) {
                $pdf->SetFont('Arial','',10);
                $pdf->SetFillColor(255,255,255);
                
                $header = "School Fees for " . $rowContDet['first_name'] . ' ' . $rowContDet['last_name'];
                
                /* Border for the top row and not for more than two contacts */
                $border = 'LTR';
                if ($countIrh > 1) {
                    $border = 'LR';
                }
                $pdf->Cell(70, 8, $header, $border, 0, 'L', 1);
                
                /* SQL to find the paid months and amount paid for the receipt with respect to previous SQL contact */
                $rows_month = '';
                $countMonth = 1;
                $SQLMonth = "
                SELECT i.invoice_month
                      ,irh.amount
                FROM invoice i
                LEFT JOIN (invoice_receipt_history irh) ON (i.invoice_id = irh.invoice_id)
                WHERE irh.receipt_id = {$row['receipt_id']}
                  AND i.contact_id = {$rowContDet['contact_id']}
                  AND i.add_registration_fee IS NULL
                ";
                $resultMonth = $db->sql_query($SQLMonth);
                $numRowsMonth  = $db->sql_numrows($resultMonth);
                $individual_amount = 0;
                while ($rowMonth = $db->sql_fetchrow($resultMonth)) {
                    
                    /* Individual total sum */
                    $individual_amount += $rowMonth['amount'];
                    /* Total sum for the receipt */
                    $total_amount += $rowMonth['amount'];
                    
                    $appendComma = '';
                    if ($numRowsMonth > 1 && $countMonth != $numRowsMonth) {
                        $appendComma = ', ';
                    }
    		        
    		        if ($rowMonth['invoice_month'] == 1) {
                        $rows_month = $rows_month .'Jan' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 2) {
                        $rows_month = $rows_month . 'Feb' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 3) {
                        $rows_month = $rows_month . 'Mar' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 4) {
                        $rows_month = $rows_month . 'Apr' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 5) {
                        $rows_month = $rows_month . 'May' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 6) {
                        $rows_month = $rows_month . 'Jun' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 7) {
                        $rows_month = $rows_month . 'Jul' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 8) {
                        $rows_month = $rows_month . 'Aug' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 9) {
                        $rows_month = $rows_month . 'Sep' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 10) {
                        $rows_month = $rows_month . 'Oct' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 11) {
                        $rows_month = $rows_month . 'Nov' . $appendComma;
    		        } else if ($rowMonth['invoice_month'] == 12) {
                        $rows_month = $rows_month . 'Dec' . $appendComma;
    		        }
    		        
    		        $countMonth++;
                    
                    /* Reg Fee for printing in media */
                    $SQLRegFee = "
                    SELECT irh.amount
                    FROM invoice_receipt_history irh
                    LEFT JOIN (invoice i) ON (i.invoice_id = irh.invoice_id)
                    WHERE irh.receipt_id = {$row['receipt_id']}
                      AND i.add_registration_fee IS NOT NULL
                    ";
                    $resultRegFee = $db->sql_query($SQLRegFee);

                    while ($rowRegFee = $db->sql_fetchrow($resultRegFee)) {
                        //$reg_fee += $rowRegFee['amount'];
                        //reg fee should be added only once(Syed)
                        $reg_fee = $rowRegFee['amount'];
                    }
                    
                }
                
                $pdf->Cell(94, 8, $rows_month, $border, 0, 'L', 1);
                $pdf->Cell(26, 8, $individual_amount, $border, 0, 'R', 1);
                $pdf->Ln();
                
                $countIrh++;
            }
            $total_amount += $reg_fee;

            $pdf->Cell(70, 8, "Registration Fees", 'LR', 0, 'L', 1);
            $pdf->Cell(94, 8, "", 'LR', 0, 'L', 1);
            $pdf->Cell(26, 8, $reg_fee, 'LR', 0, 'R', 1);
            $pdf->Ln();
                
            /*
            $pdf->Cell(70, 8, "Other Fees", 'LR', 0, 'L', 1);
            $pdf->Cell(94, 8, "", 'LR', 0, 'L', 1);
            $pdf->Cell(26, 8, "0", 'LR', 0, 'R', 1);
            $pdf->Ln();
            */
            $total_sum = '';
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(70, 8, "Total", 1, 0, 'L', 1);
            $pdf->Cell(94, 8, "", 1, 0, 'L', 1);
            $pdf->Cell(26, 8, $total_amount, 1, 0, 'R', 1);
            $pdf->Ln();
        }

        /* Cheque Details */
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(28, 8, 'Mode of Payment : ');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40, 8,  $mode_of_payment);
        $pdf->Ln(5);
       
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'Issued By : ' . $issued_by);
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'Signature:');
        $pdf->Ln(7);

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, 'Simply Islam issues receipts to acknowledge fees are paid accordingly and kindly check all details above are correct');
        $pdf->Ln(4);

        /* Creation of media record of the invoice */
        $file_name = 'Receipt_REC_' . $receipt_code . '_' . date('Y-m-d') .'.pdf';

        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_receipt';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }*/
        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getPrintInvoiceIndividual() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $invoice_code = $fn->getReqParam('invoice_code');

        $SQL = "
        SELECT it.*
              ,i.order_id
              ,i.invoice_date
              ,i.invoice_code
              ,ot.record_id
              ,o.contact_id
        FROM invoice_item it
        LEFT JOIN (invoice i) ON (it.invoice_id = i.invoice_id)
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (order_item ot) ON (o.order_id = ot.order_id)
        WHERE i.invoice_code = {$invoice_code}
          AND ot.invoice_id = {$invoice_code}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $row['record_id']);
                $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $row['contact_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$contactRec['address_country']}'");
                
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Invoice");
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(21, 20, "Invoice No : ");                
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $row['invoice_code']);                
                $pdf->Ln(5);

                $pdf->SetX(157);
                $pdf->SetFont('Arial','B',10);
                $date = $fn->getCPDate($row['invoice_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $date);
                
                $contact_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];

                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $contact_name);
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $contactRec['address_flat']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $contactRec['address_street']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' .  $contactRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(35);

                $pdf->SetXY(105, 85);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Telephone :",1 ,0, 'R', 1);
                $pdf->SetFont('Arial','',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(69, 5, $contactRec['phone'],1 ,0, 'L', 1);
                $pdf->Ln(4);

                $pdf->SetXY(10, 90);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Attention : ",1 ,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(70, 5, $contact_name,1 ,0, 'L', 1);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Fax :",1 ,0, 'R', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',10);
                /*$pdf->Cell(69, 5, $row['company_fax'],1 ,0, 'L', 1);*/
                $pdf->Ln(10);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(80, 8, "Program",1 ,0, 'C', 1);
                $pdf->Cell(30, 8, "Program Code",1 ,0, 'C', 1);
                $pdf->Cell(45, 8, "Training Date(s)",1 ,0, 'C', 1);
                $pdf->Cell(35, 8, "Term",1 ,0, 'C', 1);

                $pdf->Ln();
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(80,10,$courseRec['title'],1 ,0, 'C', 0);
                $pdf->Cell(30,10,$courseRec['course_code'],1 ,0, 'C', 0);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $date = $from_date . ' to ' . $to_date;
                $pdf->Cell(45,10, $date,1 ,0, 'C', 0);
                $pdf->Cell(35,10,"Immediate",1 ,0, 'C', 0);
                $pdf->Ln(12);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Amount(S$)",1,0, 'R', 1);
                $pdf->Ln();
            }

            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $row['qty'],1, 0, 'C', 1);
            $pdf->Cell(135, 10, $row['item_title'],1, 0, 'L', 1);
            $pdf->Cell(35, 10, $row['unit_price'], 1, 0, 'R', 1);
            $pdf->Ln(7);
            
            $subsidy = number_format($row['subsidy'], 2);
            $pdf->Cell(20, 10, $row['qty'], 'L', 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate', 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, $subsidy, 'LR', 0, 'R', 1);
            $pdf->Ln(10);
                
            $total += $row['unit_price'] + $subsidy;
            $count++;
        } 

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }

        $total = number_format($total, 2);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,8,$total,1,  0, 'R', 1);
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, 'Remarks');

        $pdf->Ln(40);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(66, 8, 'Cheque should be Crossed and Issued to');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, $cpCfg['printCompanyName']);
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        
        //$pdf->Output('Invoice.pdf','D');
        $pdf->Output();
    }

    /**
     *
     */
    function getGenerateInvoiceIndividualForMedia($invoice_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);
        $invoice_code = $invoiceRec['invoice_code'];

        $SQL = "
        SELECT it.*
              ,i.order_id
              ,i.invoice_date
              ,i.invoice_code
              ,ot.record_id
              ,o.contact_id
        FROM invoice_item it
        LEFT JOIN (invoice i) ON (it.invoice_id = i.invoice_id)
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (order_item ot) ON (o.order_id = ot.order_id)
        WHERE i.invoice_code = {$invoice_code}
          AND ot.invoice_id = {$invoice_code}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $row['record_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$orderRec['cust_address_country_code']}'");
                
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Invoice");
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(21, 20, "Invoice No : ");                
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $row['invoice_code']);                
                $pdf->Ln(5);

                $pdf->SetX(157);
                $pdf->SetFont('Arial','B',10);
                $date = $fn->getCPDate($row['invoice_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $date);
                
                $contact_name = $orderRec['cust_first_name'] . ' ' . $orderRec['cust_last_name'];

                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $contact_name);
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $orderRec['cust_address1']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $orderRec['cust_address2']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' .  $orderRec['cust_address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(35);

                $pdf->SetXY(105, 85);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Telephone :",1 ,0, 'R', 1);
                $pdf->SetFont('Arial','',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(69, 5, $orderRec['cust_phone'],1 ,0, 'L', 1);
                $pdf->Ln(4);

                $pdf->SetXY(10, 90);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Attention : ",1 ,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(70, 5, $contact_name,1 ,0, 'L', 1);
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(25, 5, "Fax :",1 ,0, 'R', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',10);
                /*$pdf->Cell(69, 5, $row['company_fax'],1 ,0, 'L', 1);*/
                $pdf->Ln(10);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(80, 8, "Program",1 ,0, 'C', 1);
                $pdf->Cell(30, 8, "Program Code",1 ,0, 'C', 1);
                $pdf->Cell(45, 8, "Training Date(s)",1 ,0, 'C', 1);
                $pdf->Cell(35, 8, "Term",1 ,0, 'C', 1);

                $pdf->Ln();
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(80,10,$courseRec['title'],1 ,0, 'C', 0);
                $pdf->Cell(30,10,$courseRec['course_code'],1 ,0, 'C', 0);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $date = $from_date . ' to ' . $to_date;
                $pdf->Cell(45,10, $date,1 ,0, 'C', 0);
                $pdf->Cell(35,10,"Immediate",1 ,0, 'C', 0);
                $pdf->Ln(12);

                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Qty",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Amount(S$)",1,0, 'R', 1);
                $pdf->Ln();
            }

            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $row['qty'],1, 0, 'C', 1);
            $pdf->Cell(135, 10, $row['item_title'],1, 0, 'L', 1);
            $pdf->Cell(35, 10, $row['unit_price'], 1, 0, 'R', 1);
            $pdf->Ln(7);
            
            $subsidy = number_format($row['subsidy'], 2);
            $pdf->Cell(20, 10, $row['qty'], 'L', 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate', 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, $subsidy, 'LR', 0, 'R', 1);
            $pdf->Ln(10);
                
            $discount_price = $row['discount'];
            $total += $row['unit_price'] + $subsidy + $discount_price;
            $count++;
            $order_id = $row['order_id'];
            $invoice_code = $row['invoice_code'];
        } 

        if($discount_price){
            $discount_price = number_format($discount_price, 2);

            $pdf->Cell(20, 10, "1", 'L', 0, 'C', 1);
            $pdf->Cell(135, 10, 'Discount', 'L', 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 'LR', 0, 'R', 1);
            $pdf->Ln();
        }

        $total = number_format($total, 2);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,8,$total,1,  0, 'R', 1);
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, 'Remarks');

        $pdf->Ln(40);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(66, 8, 'Cheque should be Crossed and Issued to');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30, 8, $cpCfg['printCompanyName']);
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        
        /* Creation of media record of the invoice */
        $file_name = 'Invoice_INV_' . $invoice_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $invoice_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_invoice';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        /* Condition for folder path with regards to local and other sites */
        if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getPrintReceiptIndividual() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $receipt_code = $fn->getReqParam('receipt_code');

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_code = {$receipt_code}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                
                $orderRec       = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $orderRec['order_id']);
                $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                $invoiceRec     = $fn->getRecordRowByID('invoice', 'order_id', $orderRec['order_id']);
                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_id', $invoiceRec['invoice_id']);
                $contactRec     = $fn->getRecordRowByID('contact', 'contact_id', $invoiceItemRec['contact_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$contactRec['address_country']}'");

                /* Institute company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Receipt");
                $code = 'Receipt No : '. $row['receipt_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(147, 45);
                $pdf->Cell(50, 20, $code);                
                $pdf->Ln(5);
                
                $pdf->SetX(157);
                $date = $fn->getCPDate($row['date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Individual */
                $contact_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];

                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, "Received from");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $contact_name);
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $contactRec['address_flat']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $contactRec['address_street']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $contactRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)",1,0, 'R', 1);
                $pdf->Ln();
                
                $pdf->Cell(180, 10, 'General');
                $pdf->Ln(5);

                $pdf->SetFont('Arial','',10);
                $pdf->Cell(180, 10, $contact_name);
                $pdf->Ln(5);

                $pdf->Cell(180, 10, 'Course Code: ' . $courseRec['course_code']);
                $pdf->Ln(5);

                $pdf->Cell(180, 10, $courseRec['title']);
                $pdf->Ln(5);
                
                $pdf->Cell(180, 10, "Intake : ");
                $pdf->Ln(5);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $course_date = $from_date . ' to ' . $to_date;
                $pdf->Cell(180, 10, "From : ". $course_date);
                $pdf->Ln(7);
            }

            /* List of invoice items for the invoice */
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, $invoiceItemRec['item_title']);
            $pdf->Cell(35, 10, $invoiceItemRec['unit_price'], 0, 0, 'R', 1);
            $pdf->Ln(7);
            
            $lineItemNumber++; // To increment the line item in receipt

            $subsidy = number_format($invoiceItemRec['subsidy'], 2);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate');
            $pdf->Cell(35, 10, $subsidy, 0, 0, 'R', 1);
            $pdf->Ln();
            
            $lineItemNumber++; // To increment the line item in receipt

            $total += $invoiceItemRec['unit_price'] + $subsidy;
            $amount_paid = $row['amount'];
            $invoice_code = $invoiceRec['invoice_code'];
            $remarks = $row['remarks'];
                
            $count++;
        } 

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }
        
        /* Total amount to be paid */
        $total = number_format($total, 2);
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        //$pdf->Cell(50, 8,'Total Amount Payable',1, 0, 'L', 1);
        $pdf->Cell(50, 8,'Total Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Total amount paid */
        /*$pdf->SetX(115);
        $pdf->SetFillColor(255,191,161);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Tender Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $amount_paid, 0, 0, 'R');
        $pdf->Ln();*/

        /* Balance to be given */
        /*$change = $amount_paid - $total;
        $change = number_format($change, 2);
        $pdf->SetX(115);
        $pdf->SetFillColor(255,191,161);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Change',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $change, 0, 0, 'R');
        $pdf->Ln(10);*/

        /* Cheque Details */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 8, 'Payment Method');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        $pdf->Ln(5);
        
        /* Notes */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 8, 'Notes:');
        $pdf->Ln(4);
        
        $value = 'Invoice No: ' . $invoice_code . ', Total Invoice Amount: SGD$ ' . $total;
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $value);
        $pdf->Ln(4);

        $outstanding = 'Total outstanding payable amount exclude payment advices/invoices issued: Amount: SGD$ ';
        $pdf->Cell(150, 8, $outstanding);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();

        //$pdf->Output('Invoice.pdf','D');
        $pdf->Output();
    }

    /**
     *
     */
    function getGenerateReceiptIndividualForMedia($receipt_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $receipt_code = $fn->getReqParam('receipt_code');

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                
                $orderRec       = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $orderRec['order_id']);
                $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                $invoiceRec     = $fn->getRecordRowByID('invoice', 'order_id', $orderRec['order_id']);
                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_id', $invoiceRec['invoice_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$orderRec['cust_address_country_code']}'");

                /* Institute company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Receipt");
                $code = 'Receipt No : '. $row['receipt_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(147, 45);
                $pdf->Cell(50, 20, $code);                
                $pdf->Ln(5);
                
                $pdf->SetX(157);
                $date = $fn->getCPDate($row['date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Individual */
                $contact_name = $orderRec['cust_first_name'] . ' ' . $orderRec['cust_last_name'];

                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, "Received from");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $contact_name);
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $orderRec['cust_address1']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $orderRec['cust_address2']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $orderRec['cust_address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)",1,0, 'R', 1);
                $pdf->Ln();
                
                $pdf->Cell(180, 10, 'General');
                $pdf->Ln(5);

                $pdf->SetFont('Arial','',10);
                $pdf->Cell(180, 10, $contact_name);
                $pdf->Ln(5);

                $pdf->Cell(180, 10, 'Course Code: ' . $courseRec['course_code']);
                $pdf->Ln(5);

                $pdf->Cell(180, 10, $courseRec['title']);
                $pdf->Ln(5);
                
                $pdf->Cell(180, 10, "Intake : ");
                $pdf->Ln(5);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $course_date = $from_date . ' to ' . $to_date;
                $pdf->Cell(180, 10, "From : ". $course_date);
                $pdf->Ln(7);
            }

            /* List of invoice items for the invoice */
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, $invoiceItemRec['item_title']);
            $pdf->Cell(35, 10, $invoiceItemRec['unit_price'], 0, 0, 'R', 1);
            $pdf->Ln(7);
            
            $lineItemNumber++; // To increment the line item in receipt

            $subsidy = number_format($invoiceItemRec['subsidy'], 2);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate');
            $pdf->Cell(35, 10, $subsidy, 0, 0, 'R', 1);
            $pdf->Ln();
            
            $lineItemNumber++; // To increment the line item in receipt

            $discount_price = $invoiceItemRec['discount'];
            $total += $invoiceItemRec['unit_price'] + $subsidy + $discount_price;
            $amount_paid = $row['amount'];
            $invoice_code = $invoiceRec['invoice_code'];
            $invoice_amt = $invoiceRec['invoice_amount'];
            $invoice_amt = number_format($invoice_amt, 2);
            $remarks = $row['remarks'];
            $order_id = $row['order_id'];
            $receipt_code = $row['receipt_code'];
                
            $count++;
        } 

        if($discount_price){
            $discount_price = number_format($discount_price, 2);

            $pdf->Cell(20, 10, "3", 0, 0, 'C', 1);
            $pdf->Cell(135, 10, 'Discount');
            $pdf->Cell(35, 10, $discount_price, 0, 0, 'R', 1);

            $pdf->Ln();
        }
        
        /* Total amount to be paid */
        $total = number_format($total, 2);
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        //$pdf->Cell(50, 8,'Total Amount Payable',1, 0, 'L', 1);
        $pdf->Cell(50, 8,'Total Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Total amount paid */
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Tender Amount',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $amount_paid, 0, 0, 'R');
        $pdf->Ln();

        /* Balance to be given */
        $change = $amount_paid - $total;
        $change = number_format($change, 2);
        $change = 0;
        $pdf->SetX(115);
        $pdf->SetFillColor(254,203,156);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50, 8,'Change',1, 0, 'L', 1);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(35, 8, $change, 0, 0, 'R');
        $pdf->Ln(10);

        /* Cheque Details */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 8, 'Payment Method');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 8, '**This is a computer generated invoice. No signature is required**');
        $pdf->Ln(5);
        
        /* Notes */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(20, 8, 'Notes:');
        $pdf->Ln(4);
        
        $value = 'Invoice No: ' . $invoice_code . ', Total Invoice Amount: SGD$ ' . $invoice_amt;
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $value);
        $pdf->Ln(4);

        $outstanding = 'Total outstanding payable amount exclude payment advices/invoices issued: Amount: SGD$ ';
        $pdf->Cell(150, 8, $outstanding);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();

        /* Creation of media record of the invoice */
        $file_name = 'Receipt_REC_' . $receipt_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }*/
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_receipt';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }*/

        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getGenerateRefundFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        if (!$this->getGenerateRefundFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $receiptCodes    = $fn->getPostParam('receiptCode', array());
        $remarks         = $fn->getPostParam('remarks');
        $amount          = $fn->getPostParam('amount');
        $receipt_id      = $fn->getReqParam('receipt_id');
        
        $count = count($receiptCodes);

        //To update refund code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextRefundCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $refund_code  = $fn->getSettingsValueByKey("nextRefundCode");
        
        foreach($receiptCodes AS $receipt_code){
            
            $receiptRec = $fn->getRecordRowByID('receipt', 'receipt_code', $receipt_code);

            $fa = array();
            $fa['amount']         = $amount;
            $fa['receipt_id']     = $receiptRec['receipt_id'];
            $fa['refund_code']    = $refund_code;
            $fa['remarks']        = $remarks;
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');;
            
            $insertRefundSQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'refund');
            $resultSQL          = $db->sql_query($insertRefundSQL);
            $refund_id          = $db->sql_nextid();
        }

        $receiptRecord = $fn->getRecordRowByID('receipt', 'receipt_code', $receipt_code);
        $orderRec = $fn->getRecordRowByID('order', 'order_id', $receiptRecord['order_id']);
        /* To generate media record */
        $SQLOrder = "
        SELECT o.*
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
        FROM `order` o
        WHERE o.order_id = {$receiptRecord['order_id']}
        ";
        $resultOrder = $db->sql_query($SQLOrder);
        $rowOrder = $db->sql_fetchrow($resultOrder);
        
        if ($rowOrder['contact_type'] == 'Company') {
            $this->getGenerateRefundForMedia($refund_id);
        } else {
            $this->getGenerateRefundForMedia($refund_id);
        }
        
        /*foreach($receiptCodes AS $receipt_code){
            $fa = array();
            $fa['receipt_code'] = $receipt_code;
            $fa['status']       = 'Paid';
            
            $whereCondition = "
            WHERE invoice_code = {$invoice_code}
            ";

            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'invoice', $whereCondition);
            $db->sql_query($SQL);
        }*/
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateRefundFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('receiptCode' , 'Please check receipt code');
        $validate->validateData('amount' , 'Please enter the amount');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintRefund() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $refund_code = $fn->getReqParam('refund_code');

        $SQL = "
        SELECT r.*
        FROM refund r
        WHERE r.refund_code = {$refund_code}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $receiptRec = $fn->getRecordRowByID('receipt', 'receipt_id', $row['receipt_id']);
                $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $receiptRec['order_id']);
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $receiptRec['order_id']);
                $companyRec = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                $orderItemRec = $fn->getRecordRowByID('order_item', 'order_id', $receiptRec['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);

                /* Institute company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Refund Slip");
                $code = 'Refund No : '. $row['refund_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(50, 20, $code );                
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['creation_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Company */
                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 20, "Payer");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $companyRec['title']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $companyRec['address1']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(50, 20, $companyRec['address2']);
                $pdf->SetXY(10, 65);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No",1,0, 'C', 1);
                $pdf->Cell(135,8,"Description",1,0, 'C', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)",1,0, 'R', 1);
                $pdf->Ln(10);
            }

            /* List of invoice items for the refund */
            $invoiceItemSQL ="
            SELECT * FROM invoice_item
            WHERE invoice_id = {$invoiceRec['invoice_id']}
            ";
            $invoiceItemresult  = $db->sql_query($invoiceItemSQL);
            $invoiceItemNumRows = $db->sql_numrows($invoiceItemresult);
            $lineItemNumber = 1;
            while ($rowInvoiceItem = $db->sql_fetchrow($invoiceItemresult)) {

                $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $rowInvoiceItem['contact_id']);
                
                $contact_details = $contactRec['first_name'] . ' ' . $contactRec['last_name'] . '(ID Card NO: ' . $contactRec['id_card_no'] . '), ';
                $program_details = 'Programme Fee, Paid: $' . $rowInvoiceItem['unit_price'];
                $receipt_details = 'Receipt No.:' . $receiptRec['receipt_code'] . ' (Valid)';
                $description = $contact_details . $program_details;
                
                $pdf->SetFont('Arial','',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
                $pdf->Cell(135, 10, $description);
                $pdf->Cell(35, 10, $rowInvoiceItem['unit_price'], 0, 0, 'R', 1);
                $pdf->Ln(5); 

                $pdf->Cell(20, 10, '');
                $pdf->Cell(135, 10, $receipt_details);
                $pdf->Cell(35, 10, '');
                $pdf->Ln(7); 

                $lineItemNumber++; // To increment the line item in receipt
                
                $total += $rowInvoiceItem['unit_price'];
            }
            
            $amount_paid = $row['amount'];
            $remarks = $row['remarks'];
                
            $count++;
        } 

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }
        
        /* Sub Total */
        $pdf->Ln(7);
        $total = number_format($total, 2);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(50, 8,'All prices stated are inclusive of 7% GST', 'T');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(105, 8, 'Sub Total', 'T', 0, 'R');
        $pdf->Cell(35, 8, $total, 'T', 0, 'R');
        $pdf->Ln();

        /* Refund Amount */
        $pdf->SetX(115);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(50, 8,'TOTAL REFUND AMOUNT',0, 0, 'R');
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Remarks */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln(25);

        /* Signature */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(60, 8, $cpCfg['printRefundSignatureName'], 'T', 0 , 'C');
        $pdf->Ln();

        //$pdf->Output('Invoice.pdf','D');
        $pdf->Output();
    }

    /**
     *
     */
    function getGenerateRefundForMedia($refund_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $refund_code = $fn->getReqParam('refund_code');

        $SQL = "
        SELECT r.*
        FROM refund r
        WHERE r.refund_id = {$refund_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $receiptRec = $fn->getRecordRowByID('receipt', 'receipt_id', $row['receipt_id']);
                $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $receiptRec['order_id']);
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $receiptRec['order_id']);
                $companyRec = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                $orderItemRec = $fn->getRecordRowByID('order_item', 'order_id', $receiptRec['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);

                /* Institute company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetXY(157, 35);
                $pdf->Cell(40, 20, "Refund Slip");
                $code = 'Refund No : '. $row['refund_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 45);
                $pdf->Cell(50, 20, $code );                
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['creation_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Company */
                $pdf->SetXY(10, 40);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 20, "Payer");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 53, 75, 30, 'D');
                $pdf->SetXY(10, 45);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                $pdf->SetXY(10, 50);
                $pdf->Cell(50, 20, $companyRec['title']);
                $pdf->SetXY(10, 55);
                $pdf->Cell(50, 20, $companyRec['address1']);
                $pdf->SetXY(10, 60);
                $pdf->Cell(50, 20, $companyRec['address2']);
                $pdf->SetXY(10, 65);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);
                $pdf->Ln(20);

                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No", 0,0, 'C', 1);
                $pdf->Cell(135,8,"Description", 0,0, 'L', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)", 0,0, 'R', 1);
                $pdf->Ln(10);
            }

            /* List of invoice items for the refund */
            $invoiceItemSQL ="
            SELECT * FROM invoice_item
            WHERE invoice_id = {$invoiceRec['invoice_id']}
            ";
            $invoiceItemresult  = $db->sql_query($invoiceItemSQL);
            $invoiceItemNumRows = $db->sql_numrows($invoiceItemresult);
            $lineItemNumber = 1;
            while ($rowInvoiceItem = $db->sql_fetchrow($invoiceItemresult)) {

                $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $rowInvoiceItem['contact_id']);
                
                $contact_details = $contactRec['first_name'] . ' ' . $contactRec['last_name'] . '(ID Card NO: ' . $contactRec['id_card_no'] . '), ';
                $program_details = 'Programme Fee, Paid: $' . $rowInvoiceItem['unit_price'] . ', ';
                $receipt_details = 'Receipt No.: ' . $receiptRec['receipt_code'] . ' (Valid)';
                //$description = $contact_details . $program_details;
                $description = $program_details . $receipt_details;
                
                $pdf->SetFont('Arial','',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
                $pdf->Cell(135, 10, $description);
                $pdf->Cell(35, 10, $rowInvoiceItem['unit_price'], 0, 0, 'R', 1);
                $pdf->Ln(5); 

                /*$pdf->Cell(20, 10, '');
                $pdf->Cell(135, 10, $receipt_details);
                $pdf->Cell(35, 10, '');
                $pdf->Ln(7);*/

                $lineItemNumber++; // To increment the line item in receipt
                
                $total += $rowInvoiceItem['unit_price'];
            }
            
            $amount_paid = $row['amount'];
            $remarks = $row['remarks'];
            $order_id = $receiptRec['order_id'];
            $refund_code = $row['refund_code'];
                
            $count++;
        } 

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }
        
        /* Sub Total */
        $pdf->Ln(7);
        $total = number_format($total, 2);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(50, 8,'', 'T');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(105, 8, 'Sub Total', 'T', 0, 'R');
        $pdf->Cell(35, 8, $total, 'T', 0, 'R');
        $pdf->Ln();

        /* Refund Amount */
        $pdf->SetX(115);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(50, 8,'TOTAL REFUND AMOUNT',0, 0, 'R');
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Remarks */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(150, 8, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln(25);

        /* Signature */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(60, 8, '__________________________________', 0, 0 , 'L');
        $pdf->Ln();

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(60, 8, $cpCfg['printRefundNameInPdf'], 0, 0 , 'C');
        $pdf->Ln();

        /* Creation of media record of the invoice */
        $file_name = 'Refund_REF_' . $refund_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }*/
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $refund_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_refund';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }*/
        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getGenerateCreditNoteForMedia($credit_note_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);
		
        $SQL = "
        SELECT cn.*
        FROM credit_note cn
        WHERE cn.credit_note_id = {$credit_note_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){

                $invoiceRec = $fn->getRecordRowByID('invoice', 'order_id', $row['order_id']);
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
                $companyRec = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
                $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
                $orderItemRec = $fn->getRecordRowByID('order_item', 'order_id', $row['order_id']);
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
                $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_id', $invoiceRec['invoice_id']);

                /* Institute company address */
                $pdf->SetXY(10,1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Rect(10 , 5, 80, 38, 'F');
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
                $pdf->SetFont('Arial','',10);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->Ln(15);

                $pdf->Image('images/logo-print.jpg',157,5,45);

                /* Recepit code and date */
                $pdf->SetFont('Arial', 'B', 15);
                $pdf->SetXY(157, 25);
                $pdf->Cell(40, 20, "CREDIT NOTE");
                $code = 'CR No. : '. $row['credit_note_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(157, 35);
                $pdf->Cell(50, 20, $code );                
                $pdf->Ln(5);

                $pdf->SetX(157);
                $date = $fn->getCPDate($row['creation_date'], 'd-M-Y');
                $pdf->Cell(11, 20, "Date : ");
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(50, 20, $date);

                /* Address of the Company */
                $pdf->SetXY(10, 49);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 20, "Payer");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(10, 63, 75, 30, 'D');
                $pdf->SetXY(10, 55);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                $pdf->SetXY(10, 60);
                $pdf->Cell(50, 20, $companyRec['title']);
                $pdf->SetXY(10, 65);
                $pdf->Cell(50, 20, $companyRec['address1']);
                $pdf->SetXY(10, 70);
                $pdf->Cell(50, 20, $companyRec['address2']);
                $pdf->SetXY(10, 75);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);

                /* Account */
                /*$pdf->SetXY(125, 49);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 20, "Account");
                $pdf->SetFillColor(224,235,255);
                $pdf->Rect(125, 63, 75, 30, 'D');
                $pdf->SetXY(125, 55);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, 'ACCOUNTS DEPARTMENT');
                $pdf->SetXY(125, 60);
                $pdf->Cell(50, 20, $companyRec['title']);
                $pdf->SetXY(125, 65);
                $pdf->Cell(50, 20, $companyRec['address1']);
                $pdf->SetXY(125, 70);
                $pdf->Cell(50, 20, $companyRec['address2']);
                $pdf->SetXY(125, 75);
                $pdf->Cell(60, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
                $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);*/
                $pdf->Ln(20);

                $refund_details = 'Invoice/Refund No : ' . $invoiceRec['invoice_code'];
                $receipt_details = 'Receipt : ' . $invoiceRec['invoice_code'];
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(100, 8, $refund_details);
                $pdf->Cell(150, 8, $receipt_details);
                $pdf->Ln(10);
                
                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(20,8,"Item No", 0, 0, 'C', 1);
                $pdf->Cell(135,8,"Description", 0, 0, 'L', 1);
                $pdf->Cell(35,8,"Sub Total (SGD)", 0, 0, 'R', 1);
                $pdf->Ln();
                
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(180, 10, $courseRec['title']);
                $pdf->Ln(7);

                $pdf->Cell(180, 10, "Intake: 0");
                $pdf->Ln(7);

                $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'd-M-Y');
                $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'd-M-Y');
                $course_date = $from_date . ' to ' . $to_date;
                $pdf->Cell(180, 10, "From: " . $course_date);
                $pdf->Ln(7);
            }

            /* List of invoice items for the invoice */
            $pdf->SetFont('Arial','',10);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, $invoiceItemRec['item_title']);
            $pdf->Cell(35, 10, '', 0, 0, 'R', 1);
            $pdf->Ln(7);
            
            $lineItemNumber++; // To increment the line item in receipt

            $subsidy = number_format($invoiceItemRec['subsidy'], 2);
            $pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            $pdf->Cell(135, 10, 'Rebate');
            $pdf->Cell(35, 10, '', 0, 0, 'R', 1);
            $pdf->Ln();
            
            $lineItemNumber++; // To increment the line item in receipt
                
            if ($count == 0){
                $pdf->Cell(135, 10, 'Name of Trainee(s):');
                $pdf->Cell(35, 10, '');
            }
            
            /* SQL for more than one contact for the receipt*/
            $contactPerson = '';
            $SQLContact = "
            SELECT CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
                  ,c.id_card_no
            FROM contact c
            WHERE c.contact_id = {$invoiceItemRec['contact_id']}
            ";
            $resultContact = $db->sql_query($SQLContact);
            
            while ($rowContact = $db->sql_fetchrow($resultContact)) {
                $pdf->Ln(6);
                $contactPerson .= $rowContact['contact_name'] . ' ' . $rowContact['id_card_no'] . ' ';
            }

            $pdf->Cell(135, 10, $contactPerson);
            $pdf->Cell(35, 10, '');
            $pdf->Ln();

            $amount_paid = $row['amount'];
            $remarks = $row['remarks'];
            $credit_note_code = $row['credit_note_code'];
                
            $count++;
        } 

        if($discount_price){
            $pdf->SetFillColor(224,235,255);
            $pdf->Cell(20, 10,"1",1, 0, 'L', 1);
            $pdf->Cell(135, 10, 'Discount',1, 0, 'L', 1);
            $pdf->Cell(35, 10, $discount_price, 1, 0, 'R', 1);
            //$pdf->Cell(35, 10, $discount, 1, 0, 'R', 1);
            $pdf->Ln();
        }
        
        /* Sub Total */
        $pdf->Ln(7);
        $total = number_format($amount_paid, 2);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(50, 8, '', 'T');
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(105, 8, 'Sub Total', 'T', 0, 'R');
        $pdf->Cell(35, 8, $total, 'T', 0, 'R');
        $pdf->Ln();

        /* Credit Note Amount */
        $pdf->SetX(115);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(50, 8,'TOTAL CREDIT NOTE AMOUNT',0, 0, 'R');
        $pdf->Cell(35, 8, $total, 0, 0, 'R');
        $pdf->Ln();

        /* Remarks */
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(150, 8, 'Remarks');
        $pdf->Ln(4);

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln(25);

        $pdf->Ln(25);

        /* Signature */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(60, 8, '__________________________________', 0, 0 , 'L');
        $pdf->Ln();

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(60, 8, $cpCfg['printCreditNoteNameInPdf'], 0, 0 , 'C');
        $pdf->Ln();

        //$pdf->Output('Invoice.pdf','D');
        //$pdf->Output();

        /* Creation of media record of the invoice */
        $file_name = 'CreditNote_CN_' . $credit_note_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }*/
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $credit_note_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_creditNote';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }*/
        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getPopulateReceiptAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_code = $fn->getReqParam('invoice_code');
        $checkedVal = $fn->getReqParam('checkedVal');

        if($checkedVal == 1){
            $_SESSION['selectedInvoiceIds'][] = $invoice_code;
        }
        else if($checkedVal == 0){
            $s = &$_SESSION['selectedInvoiceIds'];
            if(($key = array_search($invoice_code, $s)) !== false){
                unset($s[$key]);
            }
        }
        
        if(count($_SESSION['selectedInvoiceIds']) == 0){
            return 0;
        }
        
        $selectInvoiceIds = join(',', $_SESSION['selectedInvoiceIds']);
        $sessionExplode = explode(',', $selectInvoiceIds);
        
        $counter = 1;
        $count = count($sessionExplode);
        
        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }
        
        $SQLPaid = "
        SELECT SUM(invoice_amount) AS invoice_selected_sum
        FROM invoice
        WHERE invoice_code IN ({$invoice_code})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);
        
        $SQLDiscount = "
        SELECT SUM(discount_amount) AS discount_selected_sum
        FROM invoice
        WHERE invoice_code IN ({$invoice_code})
        ";
        $resultDiscount = $db->sql_query($SQLDiscount);
        $rowDiscount    = $db->sql_fetchrow($resultDiscount);
        
        $SQLPartialPayment = "
        SELECT SUM(irh.amount) AS invoice_partial_payment
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        WHERE i.invoice_code IN ({$invoice_code})
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);
        
        return $rowPaid['invoice_selected_sum']- $rowDiscount['discount_selected_sum'] - $rowPartialPayment['invoice_partial_payment'];
        /*
        if ($rowPartialPayment['invoice_partial_payment'] == 0){
            return $rowPaid['invoice_selected_sum'] - $rowDiscount['discount_selected_sum'];
        } else {
            return $rowPaid['invoice_selected_sum']- $rowDiscount['discount_selected_sum'] - $rowPartialPayment['invoice_partial_payment'];
        }
        */
        
    }

    /**
     *
     */
    function getPopulateReceiptAmountPvt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_receipt_history_id = $fn->getReqParam('invoice_hist_id');
        $checkedVal = $fn->getReqParam('checkedVal');

        if($checkedVal == 1){
            $_SESSION['selectedInvoiceIds'][] = $invoice_receipt_history_id;
        }
        else if($checkedVal == 0){
            $s = &$_SESSION['selectedInvoiceIds'];
            if(($key = array_search($invoice_receipt_history_id, $s)) !== false){
                unset($s[$key]);
            }
        }
        if(count($_SESSION['selectedInvoiceIds']) == 0){
            return 0;
        }
        $selectInvoiceIds = join(',', $_SESSION['selectedInvoiceIds']);
        
        $SQLPaid = "
        SELECT SUM(amount) AS invoice_selected_sum
        FROM installment
        WHERE installment_id IN ({$selectInvoiceIds})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);
        
        $SQLPartialPayment = "
        SELECT SUM(irh.amount) AS invoice_partial_payment
        FROM invoice_receipt_history irh
        LEFT JOIN (installment i) ON (irh.installment_id = i.installment_id)
        WHERE i.installment_id IN ({$selectInvoiceIds})
          AND i.invoice_paid_status = 'Partial Payment'
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);
        
        if ($rowPartialPayment['invoice_partial_payment'] == 0){
            return $rowPaid['invoice_selected_sum'];
        } else {
            return $rowPaid['invoice_selected_sum'] - $rowPartialPayment['invoice_partial_payment'];
        }
        
    }

    /**
     * Amount population when invoice is clicked for misc receipt. Called from Jquery
     */
    function getPopulateReceiptAmountMiscPvt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_receipt_history_id = $fn->getReqParam('invoice_hist_id');
        $checkedVal = $fn->getReqParam('checkedVal');

        if($checkedVal == 1){
            $_SESSION['selectedInvoiceIds'][] = $invoice_receipt_history_id;
        }
        else if($checkedVal == 0){
            $s = &$_SESSION['selectedInvoiceIds'];
            if(($key = array_search($invoice_receipt_history_id, $s)) !== false){
                unset($s[$key]);
            }
        }
        if(count($_SESSION['selectedInvoiceIds']) == 0){
            return 0;
        }
        $selectInvoiceIds = join(',', $_SESSION['selectedInvoiceIds']);
        
        $SQLPaid = "
        SELECT SUM(amount) AS invoice_selected_sum
        FROM installment
        WHERE installment_id IN ({$selectInvoiceIds})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);
        
        return $rowPaid['invoice_selected_sum'];
        
    }

    /**
     *
     */
    function getPopulateRefundAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $receipt_code = $fn->getReqParam('receipt_code');
        
        $SQL = "
        SELECT amount
        FROM receipt
        WHERE receipt_code = {$receipt_code}
        ";
        $result  = $db->sql_query($SQL);
        $row     = $db->sql_fetchrow($result);
        
        return $row['amount'];
    }

    /**
     *
     */
    function getPopulateCreditNoteAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_id = $fn->getReqParam('invoice_id');
        
        $SQL = "
        SELECT invoice_amount
        FROM invoice
        WHERE invoice_id = {$invoice_id}
        ";
        $result  = $db->sql_query($SQL);
        $row     = $db->sql_fetchrow($result);
        
        return $row['invoice_amount'];
    }
    
    /* New functionality for Private Institutions */
    /**
     *
     */
     function getGenerateInvoiceFormPvt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        
        $order_id= $fn->getReqParam('order_id');

        $numRows = 0;
        $rows = '';
         
        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $no_of_installment = $orderRec['no_of_installment'];
        $registration_type = $orderRec['registration_type'];
        
        $SQL = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              ,o.registration_type 
              ,o.medical_insurance
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $SQL = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,o.registration_type 
              ,o.medical_insurance
              ,o.add_registration_fee
              ,o.full_time
              ,cc.no_of_months
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";        
        $resultForPvt = $db->sql_query($SQL);
        $total_invoice_amount = $this->view->getTotalForPvtInst($resultForPvt);

        if($registration_type == 'Only Registration'){
            $pfx = 1 . '_' ;
            $invoice_installment_amount = $fn->getSettingsValueByKey("registrationFee");
            $rows .= "
            <div class='floatbox'>
                <div class='float_right'>
                {$formObj->getTBRow('Invoice Amount', "{$pfx}invoice_amount", $invoice_installment_amount)}
                </div>
                <div>
                {$formObj->getDateRow('Date', "{$pfx}invoice_date")}
                </div>
            </div>
            ";
        }
        else if($registration_type == 'Registration & Enrollment'){
            if($no_of_installment == ''){
                $no_of_installment = 1;
            }
            
            $invoice_installment_amount = $total_invoice_amount/$no_of_installment;
            //$invoice_installment_amount  = number_format($invoice_installment_amount, 2);
            
            for($i=$no_of_installment; $i>0; $i--){
                $pfx = $i . '_' ;
                $rows .= "
                <div class='floatbox'>
                    <div class='float_right'>{$formObj->getTBRow('Invoice Amount', "{$pfx}invoice_amount", $invoice_installment_amount)}</div>
                    <div>{$formObj->getDateRow('Date', "{$pfx}invoice_date")}</div>
                </div>
                ";
            }
        }

        $formAction = "index.php?_topRm=finance&module=edukloud_order&_spAction=generateInvoiceFormSubmitPvt&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar invoiceFormPvt' method='post' action='{$formAction}'>
            {$rows}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getGenerateInvoiceFormSubmitPvt() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $site_id = $fn->getSessionParam('cp_site_id');

        if (!$this->getInvoiceFormSubmitPvtValidate()){
            return $validate->getErrorMessageXML();
        }

        $order_id = $fn->getPostParam('order_id');
        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $no_of_installment = $orderRec['no_of_installment'];
        $registration_type = $orderRec['registration_type'];
        $medical_insurance = $orderRec['medical_insurance'];
        $add_registration_fee = $orderRec['add_registration_fee'];
        
        //$invoice_id = 1;
        //$this->getGenerateInvoiceForMediaPvt($invoice_id, $order_id);
        //return $validate->getSuccessMessageXML();

        $SQL = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,o.registration_type 
              ,o.medical_insurance
              ,o.add_registration_fee
              ,o.full_time
              ,cc.no_of_months
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $resultForPvt  = $db->sql_query($SQL);  
        $total    = $this->view->getTotalForPvtInst($resultForPvt);

        if($no_of_installment == ''){
            $no_of_installment = 1;
        }
        
        //to create invoice item
        $rowPrefix    = $fn->getRecordByCondition('setting', "key_text = 'invoiceCodePrefix'");
        $current_year = date('Y');
    
        if ($site_id) {
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' AND site_id = '{$site_id}'";
        } else {
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        }
        
        $resultUpdate    = $db->sql_query($SQLUpdate);
        $nextInvoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");
        if($nextInvoiceCode < 10){
            $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextInvoiceCode;
        }
        else if($nextInvoiceCode < 100){
            $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextInvoiceCode;
        }
        else if($nextInvoiceCode > 99 && $nextInvoiceCode < 1000){
            $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextInvoiceCode;
        }
        else if($nextInvoiceCode > 999 && $nextInvoiceCode < 10000){
            $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-0' . $nextInvoiceCode;
        }
        else{
            $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-' . $nextInvoiceCode;
        }
        
        $pfx = $no_of_installment . '_' ;
        $invoice_date_main   = $fn->getPostParam("{$pfx}invoice_date");

        $fa = array();
        $fa['order_id']         = $order_id;
        $fa['invoice_code']     = $nextInvoiceCode;
        $fa['invoice_date']     = $invoice_date_main;
        $fa['invoice_amount']   = $total;
        $fa['registration_type']= $registration_type;
        $fa['medical_insurance']= $medical_insurance;
        $fa['add_registration_fee']= $add_registration_fee;
        $fa['status']           = 'Due';
        $fa['creation_date']    = date("Y-m-d H:i:s");

        $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
        $resultSQL          = $db->sql_query($insertInvoiceSQL);
        $invoice_id         = $db->sql_nextid();

        if($add_registration_fee == 1){
            $today = date("Y-m-d");
            $fa = array();
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $fn->getSettingsValueByKey("registrationFee");;
            $fa['invoice_date']  = $invoice_date_main;
            $fa['title']         = 'Registration';
            //$histId              = $fn->addRecord($fa, 'invoice_receipt_history');
            $histId              = $fn->addRecord($fa, 'installment');
        }


        $count = 1;
        // To add Installment
        if($registration_type != 'Only Registration'){
            for($i=$no_of_installment; $i>0; $i--){
                $pfx = $i . '_' ;
                $invoice_amount = $fn->getPostParam("{$pfx}invoice_amount");
                $invoice_date   = $fn->getPostParam("{$pfx}invoice_date");
                //Inserting installments to history table 
                $fa = array();
                $fa['invoice_id']    = $invoice_id;
                $fa['amount']        = $invoice_amount;
                $fa['invoice_date']  = $invoice_date;
                $fa['title']         = 'Installment' . $count;
                //$histId              = $fn->addRecord($fa, 'invoice_receipt_history');
                $histId              = $fn->addRecord($fa, 'installment');
                $count++;
            }
        }
        
        $SQL = "
        SELECT oi.*
        FROM order_item oi
        WHERE oi.order_id = ($order_id)
        ";
        $result = $db->sql_query($SQL);
         
        while ($row = $db->sql_fetchrow($result)) {
            // To get discount for related contacts
        
            $expDiscount = array('condn' => " AND module='edukloud_discount' AND order_id = {$order_id}");
            $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'contact_id', $row['contact_id'], $expDiscount);
            
            $discount_cost = $orderItemRecDiscount['unit_price'];
        
            $fa = array();
            $fa['invoice_id']     = $invoice_id;
            $fa['qty']            = $row['qty'];
            $fa['unit_price']     = $row['unit_price'];
            $fa['item_title']     = $row['item_title'];
            $fa['contact_id']     = $row['contact_id'];
            $fa['discount']       = $discount_cost;
            
            $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice_item');
            $resultInsert       = $db->sql_query($insertInvoiceSQL);
        }
            
        $this->getGenerateInvoiceForMediaPvt($invoice_id, $order_id);

        return $validate->getSuccessMessageXML();
        //$cpUtil->redirect("index.php?_topRm=finance&module=edukloud_order&order_id={$order_id}&_action=edit");
    }

    /**
     *
     */
    function getInvoiceFormSubmitPvtValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');

        //==================================================================//
        $validate->resetErrorArray();
        
        foreach($_POST as $fldName => $value){
            if ($fldName != 'order_id'){
                $actualFld = substr($fldName, strpos($fldName, '_') + 1 , strlen($fldName));
                if ($value == '' && $actualFld == 'invoice_amount'){
                    $validate->validateData($fldName, 'Please enter the amount');
                } else if ($value == '' && $actualFld == 'invoice_date'){
                    $validate->validateData($fldName, 'Please select the date');
                }
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
   
    /**
     *
     */
    function getEditInvoiceFormSubmitPvt() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $order_id   = $fn->getReqParam('order_id');
        $invoice_id = $fn->getReqParam('invoice_id');

        if (!$this->getEditInvoiceFormSubmitPvtValidate($invoice_id)){
            return $validate->getErrorMessageXML();
        }
        
        /*$total    = $this->view->getTotalForPvtInst($resultForPvt);*/
        
        $fa = array();
        $fa['status']           = 'Due';
        $fa['modification_date']= date("Y-m-d H:i:s");

        $histId = $fn->saveRecord($fa, 'invoice', 'invoice_id', $invoice_id);
        
        foreach($_POST AS $fldName => $value) {
            $fldArr = explode('__', $fldName);
            if($fldArr[0] == 'invoice_amount') {
                $invoice_amount = $value;
                $installment_id = $fldArr[1];
                $dateFldName = "invoice_date__{$installment_id}";
                $invoice_date   = $_POST[$dateFldName];

                $fa = array();
                $fa['amount']        = $invoice_amount;
                $fa['invoice_date']  = $invoice_date;
                
                $instId = $fn->saveRecord($fa, 'installment', 'installment_id', $installment_id);
            }
        }
        
        $SqlMediaDelete = "DELETE FROM media 
        WHERE room_name = 'edukloud_invoice'
          AND record_id = {$invoice_id}
          AND record_type = 'attachment'
        ";
        $resultMediaDelete = $db->sql_query($SqlMediaDelete);
        
        $this->getGenerateInvoiceForMediaPvt($invoice_id, $order_id);

        return $validate->getSuccessMessageXML();
        //$cpUtil->redirect("index.php?_topRm=finance&module=edukloud_order&order_id={$order_id}&_action=edit");
    }

    /**
     *
     */
    function getEditInvoiceFormSubmitPvtValidate($invoice_id) {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        //==================================================================//
        $validate->resetErrorArray();
        
        $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);
        
        $invoice_amount = 0;
        foreach($_POST as $fldName => $value){
            $fldArr = explode('__', $fldName);

            if ($value == '' && $fldArr[0] == 'invoice_amount'){
                $validate->validateData($fldName, 'Please enter the amount');
            } else if ($value == '' && $fldArr[0] == 'invoice_date'){
                $validate->validateData($fldName, 'Please select the date');
            }

            if($fldArr[0] == 'invoice_amount') {
                $installment_id = $fldArr[1];
                $invoice_amount += $value;
            }
        }
        
        $invoice_amount = round($invoice_amount, 2);
        if($invoiceRec['add_registration_fee'] == 1){
            $invoiceRec['invoice_amount'] = $invoiceRec['invoice_amount'] +
            $fn->getSettingsValueByKey("registrationFee");;            
        }
            
        if ($invoice_amount > $invoiceRec['invoice_amount']) {
            $msg = 'Total entered amount exceeds total invoice amount.<br> Invoice amount entered =  ' . $invoice_amount . '. Total invoice amount ' . $invoiceRec['invoice_amount'];
            $validate->validateData('error_box', $msg);
        } else if ($invoice_amount < $invoiceRec['invoice_amount']) {
            $msg = 'Total entered amount is below total invoice amount.<br> Invoice amount entered =  ' . $invoice_amount . '. Total invoice amount ' . $invoiceRec['invoice_amount'];
            $validate->validateData('error_box', $msg);
        }
            
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
   
    /**
     *
     */
    function getGenerateInvoiceForMediaPvt($invoice_id, $order_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);
        
        $sqlAppend = '';
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $numrowsInvoiceItem = 1;

        $unitPriceAmt = 0;
        $invoiceItemQty = 0;
        $subsidyAmt = 0;
        $contactPerson = '';

        /* To be amended later */
        $registration_fee = '';
        if($cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForPvt'] == 1){
            $sqlAppend = '
            ,o.registration_type 
            ,o.medical_insurance
            ,o.add_registration_fee
            ,o.full_time
            ,cc.no_of_months
            ';
        }
        
        $SQLPvt = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              {$sqlAppend}
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $SQLPvt = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              {$sqlAppend}
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $resultForPvt  = $db->sql_query($SQLPvt);  
        $resultForPvt1 = $db->sql_query($SQLPvt);  
        $total    = $this->view->getTotalForPvtInst($resultForPvt);
        $netTotal = $this->view->getTotalForPvtInst($resultForPvt1, 'getTotalOnly');

        $expDiscount = array('condn' => " AND module='edukloud_discount'");
        $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'order_id', 
        $order_id, $expDiscount);
        $discountTotal = '';
        $discountPer   = '';
        
        $orderRec       = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id);
        $contactRec     = $fn->getRecordRowByID('contact', 'contact_id', $orderItemRec['contact_id']);
        $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
        $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
        $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
            
        $registration_type    = $orderRec['registration_type'];
        $add_registration_fee = $orderRec['add_registration_fee'];

        if($orderRec['medical_insurance'] == 1){
            $medical_insurance_value = $fn->getSettingsValueByKey("medicalInsuranceFeePvt");                
            $netTotal = $netTotal + $medical_insurance_value;
        }

        if($orderItemRecDiscount['unit_price'] > 0){
            $discountPer   = $orderItemRecDiscount['unit_price'];
            $discountTotal = ($netTotal *  $discountPer)/100;
            $discountTotal = round($discountTotal, 2);
        }            
                
        if($add_registration_fee == 1){
            $add_registration_fee = $fn->getSettingsValueByKey("registrationFee");
        }

        $SQL = "
        SELECT i.*
        FROM invoice i
        WHERE i.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {

            $pdf->Image('images/logo-print.jpg',10,5,45);

            $pdf->SetFont('Arial','',8);
            $pdf->SetXY(80,1);
            $pdf->Cell(30, 20, $cpCfg['addressFlatPvt']);
            $pdf->Cell(30, 20, $cpCfg['addressStreetPvt']);
            $pdf->Cell(20, 20, $cpCfg['addressCountryAndCodePvt']);
            $pdf->Ln(5);
            $pdf->SetX(94);
            $pdf->Cell(28, 20, 'Tel : ' . $cpCfg['contactNoPvt']);
            $pdf->Cell(20, 20, 'Fax : ' . $cpCfg['printCompanyFaxPvt']);
            $pdf->Ln(5);
            $pdf->SetX(104);
            $pdf->Cell(50, 20, 'www.mass.edu.sg');
            $pdf->Ln(15);
                    
            /* Amendment */
            $pdf->SetFont('Arial','B',10);
            $pdf->SetXY(100, 35);
            $pdf->Cell(21, 20, "OFFICIAL INVOICE", 0, 0, 'C');                
            $pdf->Ln(20);
            
            $pdf->SetX(10);
            $registration_data = 'Registration No: ' . $contactRec['registration_no'];
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(25, 5, $registration_data);
            $pdf->SetX(125);
            $invoice_data = 'Invoice Number: ' . $row['invoice_code'];
            $pdf->Cell(175, 5, $invoice_data);
            $pdf->Ln();

            $pdf->SetXY(10, 54);
            $date = $fn->getCPDate($row['invoice_date'], 'dS F Y');
            $invoice_date = 'Date: ' . $date;
            $pdf->Cell(50, 20, $invoice_date);
            $pdf->Ln(6);
            
            $pdf->SetX(10);
            $student_full_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
            $student_name = 'Student Name: ' . $student_full_name;
            $pdf->Cell(50, 20, $student_name);
            $pdf->Ln(6);
            
            $pdf->SetX(10);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(27, 20, 'Course Enrolled: ');
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(100, 20, $courseRec['title']);
            $pdf->SetX(125);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(23, 20, 'Course Code: ');
            $pdf->Cell(50, 20, $courseRec['course_code']);
            $pdf->Ln(6);
            
            $pdf->SetXY(10, 80);
            $pdf->SetFont('Arial','',10);
            $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'dS F Y');
            $from_date_format = 'Commencement Date: ' . $from_date;
            $pdf->Cell(25, 5, $from_date_format);
            $pdf->SetXY(125, 80);
            $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'dS F Y');
            $to_date_format = 'End Date: ' . $to_date;
            $pdf->Cell(175, 5, $to_date_format);
            $pdf->Ln();

            $pdf->SetXY(10, 79);
            $pdf->SetFont('Arial','',10);
            $course_title = 'Course Awarded by: ' . $courseRec['award_course'];
            $pdf->Cell(50, 20, $course_title);
            $pdf->Ln();
            
            $pdf->SetX(10);
            $pdf->SetFont('Arial','B', 13);
            $pdf->Cell(190, 7, 'Details of Payment', 1);
            $pdf->Ln(7);
            
            $pdf->SetFont('Arial','B', 10);
            $pdf->Cell(100, 8, "Description of Items",1);
            $pdf->Cell(55, 8, "No of Subject / Scheme",1);
            $pdf->Cell(35, 8, "S$ Amount",1, 0, 'R');
            $pdf->Ln(8);
            
            if ($add_registration_fee == '' || $add_registration_fee == 0 ){
                $add_registration_fee = '-';
            }

            $pdf->SetY(114);
            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '1. Registration fees', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(35, 8, $add_registration_fee , 'R', 0, 'R');
            $pdf->Ln(8);
            
            /* To show no of subjects */
            $SQLSubject = "SELECT count(module) AS no_of_subjects
            FROM order_item
            WHERE order_id = {$row['order_id']}
            AND module = 'edukloud_subject'
            AND item_title != 'Science Lab'
            ";
            $resultSubject = $db->sql_query($SQLSubject);
            $rowSubject = $db->sql_fetchrow($resultSubject);
            
            $no_of_subjects = '';
            if ($rowSubject['no_of_subjects'] == 1){
                $no_of_subjects = '[ ' . $rowSubject['no_of_subjects'] . ' Subject ]';
            } else if ($rowSubject['no_of_subjects'] > 1){
                $no_of_subjects = '[ ' . $rowSubject['no_of_subjects'] . ' Subjects ]';
            }

            $rowSubjectTotal = '-';
            if ($netTotal > 1){
                $rowSubjectTotal = $netTotal;
            }

            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '2. Course fees', 'LR');
            $pdf->Cell(55, 8, $no_of_subjects, 'LR', 0, 'C');
            $pdf->Cell(35, 8, number_format($rowSubjectTotal,2), 'R', 0, 'R');
            $pdf->Ln(4);

            $pdf->SetFont('Arial','', 7);
            $courseFeesInc = '(inclusive of material fees, internal exam fees & science practical lab fees)';
            $courseFeesInc ='';
            $pdf->Cell(100, 8, $courseFeesInc, 'LR');
            $pdf->Cell(55, 8, '', 'LR');
            $pdf->Cell(35, 8, '', 'R', 0, 'R');
            $pdf->Ln(5);

            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, 'Less', 'LR');
            $pdf->Cell(55, 8, '', 'LR');
            $pdf->Cell(35, 8, '', 'R');
            $pdf->Ln(8);

            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '3. Discount/School Grant', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(35, 8, $discountTotal, 'R', 0, 'R');
            $pdf->Ln(8);

            if($add_registration_fee != '-'){
                //$total += $add_registration_fee;
            }
            
            $pdf->Cell(100, 8, 'Total course fees payable(2-3)', 'LRB');
            $pdf->Cell(55, 8, '', 'LRB', 0, 'C');
            $pdf->Cell(35, 8, number_format($total,2), 'RB', 0, 'R');
            $pdf->Ln(40);

            $total = '';
            $count++;
            $order_id = $row['order_id'];
            $invoice_code = $row['invoice_code'];
        } 

        //=========================INSTALLMENT======================================== //
        $SQL = "
        SELECT i.*
        FROM invoice_receipt_history i
        WHERE i.invoice_id = {$invoice_id}
        ";
        $SQL = "
        SELECT i.*
        FROM installment i
        WHERE i.invoice_id = {$invoice_id}
        ORDER BY i.installment_id
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            if($count == 1){
                $pdf->SetXY(10, 165);
                $pdf->SetFont('Arial','B', 13);
                $pdf->Cell(190, 7, 'Payment Advice', 1);
                $pdf->Ln(7);
                
                $pdf->SetFont('Arial','B', 10);
                $pdf->Cell(100, 8, "Installment Schedules",1);
                $pdf->Cell(55, 8, "Date Due" ,1);
                $pdf->Cell(35, 8, "S$ Amount" ,1, 0, 'R');
                $pdf->Ln(8);
            }
            
            if ($registration_fee == ''){
                $registration_fee = '-';
            }
            
            $invoice_date = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
            if($row['title'] == 'Registration'){
                $invoice_date = '';
            }
            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8,$row['title'] ,1);
            $pdf->Cell(55, 8, $invoice_date ,1);
            $pdf->Cell(35, 8, number_format($row['amount'],2),1 ,0, 'R');
            $pdf->Ln(8);

            $count++;
        }        
        //===================END OF TABLE========================================== //
        
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(190, 8, 'This is a computer-generated Invoice. No signature is required');
        $pdf->Ln(15);

        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 5, 'Kindly make the above mentioned payment by due date to avoid late fee charges.');
        $pdf->Ln(5);
        
        $pdf->Cell(130, 5, 'Please refer to our student handbook and website for refund/withdrawal/transfer policies');
        $pdf->Ln(5);

        $pdf->Cell(130, 5, 'There is no GST charged');
        $pdf->Ln(5);

        /* Creation of media record of the invoice */
        $file_name = $invoice_code .'.pdf';
        
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/' .'temp';        
        $outputFileName = $outputPath . '/' . $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $invoice_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_invoice';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);
        
        $sqlAppend = '';
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $numrowsInvoiceItem = 1;
        $material_fee = '';
        $assesment_fee = '';
        $science_lab_fee = '';
        $medical_insurance_value = '';
        
        $unitPriceAmt = 0;
        $invoiceItemQty = 0;
        $subsidyAmt = 0;
        $contactPerson = '';

        /* To be amended later */
        $registration_fee = '';
        if($cpCfg['m.edukloud.ecommerce.order.orderItemDisplayForPvt'] == 1){
            $sqlAppend = '
            ,o.registration_type 
            ,o.medical_insurance
            ,o.add_registration_fee
            ,o.full_time
            ,cc.no_of_months
            ';
        }

        $SQLPvt = "
        SELECT oi.*
              ,o.order_id
              ,o.contact_module
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) as contact_name
              {$sqlAppend}
        FROM order_item oi 
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN contact cont ON (cont.contact_id = oi.contact_id)
        LEFT JOIN course_contact cc ON (cc.order_id = o.order_id)
        WHERE oi.order_id = {$order_id}
        ORDER BY oi.order_item_id
        ";
        $resultForPvt  = $db->sql_query($SQLPvt);  
        $resultForPvt1 = $db->sql_query($SQLPvt);  
        $total    = $this->view->getTotalForPvtInst($resultForPvt);
        $netTotal = $this->view->getTotalForPvtInst($resultForPvt1, 'getTotalOnly');

        $expDiscount = array('condn' => " AND module='edukloud_discount'");
        $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'order_id', 
        $order_id, $expDiscount);
        $discountTotal = '';
        $discountPer   = '';

        $orderRec       = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id);
        $contactRec     = $fn->getRecordRowByID('contact', 'contact_id', $orderItemRec['contact_id']);
        $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
        $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
        $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
            
        $registration_type    = $orderRec['registration_type'];
        $add_registration_fee = number_format($orderRec['add_registration_fee'], 2);
        $no_of_installment    = $orderRec['no_of_installment'];
        $full_time            = $orderRec['full_time'];

        if($orderRec['medical_insurance'] == 1){
            $medical_insurance_value = $fn->getSettingsValueByKey("medicalInsuranceFeePvt");                
            $netTotal = $netTotal + $medical_insurance_value;
        }

        if($orderItemRecDiscount['unit_price'] > 0){
            //$discountPer   = number_format($orderItemRecDiscount['unit_price']);
            //$discountTotal = ($netTotal *  $discountPer)/100;
            $discountTotal  = ($netTotal * $orderItemRecDiscount['unit_price'])/100;
            $discountTotal  = round($discountTotal, 2);
        }            
                
        $unitPrice = '-';
        if($registration_type == 'Only Registration'){
            $registration_fee = $fn->getSettingsValueByKey("registrationFee");
            //$netTotal = '-';
            $discountTotal = '-';
            $total = $registration_fee;
        }

        if($add_registration_fee == 1){
            $add_registration_fee = $fn->getSettingsValueByKey("registrationFee");
            $add_registration_fee = number_format($add_registration_fee, 2);
        }

        /* To show no of subjects */
        $SQLSubject = "
        SELECT count(module) AS no_of_subjects
        FROM order_item
        WHERE order_id = {$order_id}
        AND module = 'edukloud_subject'
        AND item_title != 'Science Lab'
        ";
        $resultSubject  = $db->sql_query($SQLSubject);
        $rowSubject     = $db->sql_fetchrow($resultSubject);
        $no_of_subjects = $rowSubject['no_of_subjects'];
        if($no_of_subjects > 4){
            $no_of_subjects = '[' . $no_of_subjects . ' Subjects' .']';
        }   

        if($no_of_subjects == 1){
            $no_of_subjects = '[' . $no_of_subjects . ' Subject' .']';
        }


        $SQLInstlmt = "
        SELECT count(amount) as first_installment_paid
        FROM installment
        WHERE invoice_id = {$invoice_id}
            AND invoice_paid_status = 'Paid'
        ";
        $resultInstlmt          = $db->sql_query($SQLInstlmt);
        $rowInstlmt             = $db->sql_fetchrow($resultInstlmt);
        $first_installment_paid = $rowInstlmt['first_installment_paid'];
            
        $SQLPaidHistory = "
        SELECT 
        (SELECT SUM(amount) FROM invoice_receipt_history
            WHERE invoice_id = {$invoice_id}
            AND invoice_paid_status IS NULL
            ) as invoice_balance_amount
        ,(SELECT SUM(amount) FROM invoice_receipt_history
            WHERE receipt_id = {$receipt_id}
              AND title != 'Registration'
            ) as last_invoice_paid
        ,(SELECT unit_price FROM invoice_item
            WHERE invoice_id = {$invoice_id}
            AND item_title = 'Science Lab'
            ) as science_lab_fees
        ";
        $SQLPaidHistory = "
        SELECT 
        (SELECT SUM(amount) FROM installment
            WHERE invoice_id = {$invoice_id}
            ) as total_invoice_amount
        ,(SELECT SUM(amount) FROM invoice_receipt_history
            WHERE invoice_id = {$invoice_id}
            ) as total_invoice_paid
        ,(SELECT SUM(amount) FROM invoice_receipt_history
            WHERE receipt_id = {$receipt_id}
            ) as last_invoice_paid
        ,(SELECT unit_price FROM invoice_item
            WHERE invoice_id = {$invoice_id}
            AND item_title = 'Science Lab'
            ) as science_lab_fees
        ";
        $resultPaidHistory    = $db->sql_query($SQLPaidHistory);
        $rowPaidHistory       = $db->sql_fetchrow($resultPaidHistory);
        $last_invoice_paid    = $rowPaidHistory['last_invoice_paid'];
        $science_lab_fees     = $rowPaidHistory['science_lab_fees'];
        //$invoice_balance_amount  = $rowPaidHistory['invoice_balance_amount'];
        //$invoice_balance_amount_before_receipt = $invoice_balance_amount +  $last_invoice_paid ;
        $invoice_balance_amount_before_receipt = 
        $rowPaidHistory['total_invoice_amount'] -
        $rowPaidHistory['total_invoice_paid'] +
        $rowPaidHistory['last_invoice_paid'];
        
        $amount_outstanding = $rowPaidHistory['total_invoice_amount'] -
        $rowPaidHistory['total_invoice_paid'];

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        //===================================HEADER=================================== //
        $pdf->SetFont('Arial','',10);
        $row = $db->sql_fetchrow($result);
        $mode_of_payment = $row['mode_of_payment'];
        $cheque_no       = $row['cheque_no'];
        $bank            = $row['bank_name'];
        $issued_by       = $row['issued_by'];
        $coi_no          = $row['coi_no'];
        $approval_code   = $row['approval_code'];
        if($full_time == 1){
            $material_fee   = 200;
            $assesment_fee  = 200;
        }
        else{
            $material_fee   = 50;
            $assesment_fee  = 50;
        }
        if ($courseRec['course_type'] == 'Short Term') {
            $material_fee   = '';
            $assesment_fee  = '';
        }
        $science_lab_fee = $science_lab_fees;
        
        $netTotal = $netTotal - $science_lab_fee - $material_fee - $assesment_fee - $medical_insurance_value;
        
        if($science_lab_fee != ''){
            $science_lab_fee = number_format($science_lab_fee);
        }
        
        $pdf->Image('images/logo-print.jpg',10,5,45);

        $pdf->SetFont('Arial','',8);
        $pdf->SetXY(80,1);
        $pdf->Cell(30, 20, $cpCfg['addressFlatPvt']);
        $pdf->Cell(30, 20, $cpCfg['addressStreetPvt']);
        $pdf->Cell(20, 20, $cpCfg['addressCountryAndCodePvt']);
        $pdf->Ln(5);
        $pdf->SetX(94);
        $pdf->Cell(28, 20, 'Tel : (65)' . $cpCfg['contactNoPvt']);
        $pdf->Cell(20, 20, 'Fax : ' . $cpCfg['printCompanyFaxPvt']);
        $pdf->Ln(5);
        $pdf->SetX(104);
        $pdf->Cell(50, 20, 'www.mass.edu.sg');
        $pdf->Ln(15);
        
        $pdf->Line(10,33,195,33);

        /* Amendment */
        $pdf->SetFont('Arial','B',10);
        $pdf->SetXY(100, 35);
        $pdf->Cell(21, 20, "OFFICIAL RECEIPT", 0, 0, 'C');                
        $pdf->Ln(17);
        
        $pdf->SetX(10);
        $registration_data = 'Registration No: ' . $contactRec['registration_no'];
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(25, 5, $registration_data); 
        
        $pdf->SetFont('Arial','',10);
        $pdf->SetX(129);
        $receipt_code = 'Receipt Number: ' . $row['receipt_code'];
        $pdf->Cell(175, 5, $receipt_code);
        $pdf->Ln();

        $pdf->SetXY(10, 50);
        $date = $fn->getCPDate($row['date'], 'dS F Y');
        $receipt_date = 'Date: ' . $date;
        $pdf->Cell(50, 20, $receipt_date);
        $pdf->Ln(6);
        
        $pdf->SetX(10);
        $student_full_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
        $student_name = 'Student Name: ' . $student_full_name;
        $pdf->Cell(50, 20, $student_name);
        $pdf->Ln(6);
        
        $pdf->SetX(10);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(27, 20, 'Course Enrolled: ');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(100, 20, $courseRec['title']);
        $pdf->SetX(129);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(23, 20, 'Course Code: ');
        $pdf->Cell(50, 20, $courseRec['course_code']);
        $pdf->Ln(6);
        
        $pdf->SetXY(10, 75);
        $pdf->SetFont('Arial','',10);
        $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'dS F Y');
        $from_date_format = 'Commencement Date: ' . $from_date;
        $pdf->Cell(25, 5, $from_date_format);
        $pdf->SetXY(129, 75);
        $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'dS F Y');
        $to_date_format = 'End Date: ' . $to_date;
        $pdf->Cell(175, 5, $to_date_format);
        $pdf->Ln();

        $pdf->SetXY(10, 78);
        $pdf->SetFont('Arial','',10);
        $course_title = 'Course Awarded by: ' . $courseRec['award_course'];
        $pdf->Cell(50, 10, $course_title);
        $pdf->Ln();
        //===================================FIRST TABLE============================= //
        
        $pdf->SetX(10);
        $pdf->SetFont('Arial','B', 13);
        $pdf->Cell(190, 7, 'Details of Payment', 1);
        $pdf->Ln(7);
        
        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(100, 8, "Description of Items",1);
        $pdf->Cell(55, 8, "No of Subject / Scheme",1);
        $pdf->Cell(15, 8, 'S$', 'LB', 0, '');
        $pdf->Cell(20, 8, 'Amount', 'RB', 0, 'R');
        //$pdf->Cell(35, 8, "S$ Amount",1, 0, 'R');
        $pdf->Ln(8);
        
        if ($add_registration_fee == '' || $add_registration_fee == 0 ){
            $add_registration_fee = '-';
        }
        //if there is first receipt already generated then we need to make all the values to (-) for the first table in pdf.
        if($first_receipt_present > 0){
            $material_fee   = '-';
            $assesment_fee  = '-';
            $science_lab_fee = '-';
            $add_registration_fee = '-';
            $medical_insurance_value = '-';
            $discountTotal = '-';
            $netTotal = $invoice_balance_amount_before_receipt;
            $total    = $invoice_balance_amount_before_receipt;
        }
        else{
            if($material_fee){
                $material_fee = number_format($material_fee,2);
            }
            if($assesment_fee){
                $assesment_fee = number_format($assesment_fee,2);
            }
            
            if($science_lab_fee != ''){
                $science_lab_fee = number_format($science_lab_fee,2);
            }
            else{
                $science_lab_fee = '-';
            }
            
            if($medical_insurance_value != '' && $medical_insurance_value != '-'){
                $medical_insurance_value = number_format($medical_insurance_value,2);
            }
            else{
                $medical_insurance_value = '-';
            }
            
            if($discountTotal != '' && $discountTotal != '-' ){
                $discountTotal = number_format($discountTotal,2);
            }
            else{
                $discountTotal = '-';
            }
            
        }
        
        $pdf->SetFont('Arial','', 10);
        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->Cell(100, 8, '1. Registration fees', 'LR');
        } else {
            $pdf->Cell(100, 8, '1. Application fees', 'LR');
        }
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $add_registration_fee, 'R', 0, 'R');
        //$pdf->Cell(35, 8, $add_registration_fee, 'R', 0, 'R');
        $pdf->Ln(6);
        
        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '2. Course fees', 'LR');
        $pdf->Cell(55, 8, $no_of_subjects, 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, number_format($netTotal,2), 'R', 0, 'R');
        $pdf->Ln(6);

        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '3. Material fees', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(15, 8, '$', 'L', 0, '');
            $pdf->Cell(20, 8, $material_fee, 'R', 0, 'R');
            //$pdf->Cell(35, 8, $material_fee, 'R', 0, 'R');
            $pdf->Ln(6);
            
            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '4. Continual assessment & internal Exam fees', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(15, 8, '$', 'L', 0, '');
            $pdf->Cell(20, 8, $assesment_fee, 'R', 0, 'R');
            //$pdf->Cell(35, 8,  $assesment_fee , 'R', 0, 'R');
            $pdf->Ln(6);
            
            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '5. Science Lab fees', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(15, 8, '$', 'L', 0, '');
            $pdf->Cell(20, 8, $science_lab_fee, 'R', 0, 'R');
            //$pdf->Cell(35, 8, $science_lab_fee, 'R', 0, 'R');
            $pdf->Ln(6);
            
            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(100, 8, '6. Medical Insurance fees', 'LR');
            $pdf->Cell(55, 8, '', 'LR', 0, 'C');
            $pdf->Cell(15, 8, '$', 'L', 0, '');
            $pdf->Cell(20, 8, $medical_insurance_value, 'R', 0, 'R');
            //$pdf->Cell(35, 8, $medical_insurance_value, 'R', 0, 'R');
            $pdf->Ln(6);
        }

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, 'Less', 'LR');
        $pdf->Cell(55, 8, '', 'LR');
        $pdf->Cell(35, 8, '', 'R');
        $pdf->Ln(6);
        
        $pdf->SetFont('Arial','', 10);
        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->Cell(100, 8, '7. School Grant', 'LR');
        } else {
            $pdf->Cell(100, 8, '3. School Grant / Discount', 'LR');
        }
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $discountTotal, 'R', 0, 'R');
        //$pdf->Cell(35, 8, $discountTotal, 'R', 0, 'R');
        $pdf->Ln(6); 

        if($add_registration_fee != '-'){
            //$total += $add_registration_fee;
        }
        $total = number_format($total, 2);
        if($registration_type == 'Only Registration'){
            $courseFeetext = 'Total Application fees payable';
        }
        else{
            $courseFeetext = 'Total course fees payable';
        }
        
        if($no_of_installment == 1){
            $installmenttext = '';
        }
        else{
            $installmenttext =  '(' . $no_of_installment . ' installments)';
        }
        
        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->Cell(100, 8, 'Total course fees payable(item 2 to 6)', 'LRB');
        } else {
            $pdf->Cell(100, 8, $courseFeetext, 'LRB');
        }
        
        $pdf->Cell(55, 8, $installmenttext, 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, $total, 'RB', 0, 'R');
        //$pdf->Cell(35, 8, $total, 'RB', 0, 'R');
        $pdf->Ln(12);

        $count++;
        $receipt_code = $row['receipt_code'];

        //=========================SECOND TABLE=================================== //

        $next_invoice_date = '';
        $expInvoice = array('condn' => " AND invoice_paid_status IS NULL");
        $invoiceHist = $fn->getRecordRowByID('installment', 'invoice_id', 
        $invoice_id, $expInvoice);

        $next_invoice_date = $fn->getCPDate($invoiceHist['invoice_date'], 'd/m/Y');

        $SQL = "
        SELECT i.*
        
        ,(SELECT COUNT(amount) FROM installment
            WHERE invoice_id = {$invoice_id}
            AND (invoice_paid_status = 'Paid' || invoice_paid_status = 'Partial Payment')
            AND title != 'Registration'
            ) as total_no_paid_installment
            
        ,(SELECT COUNT(amount) FROM installment
            WHERE invoice_id = {$invoice_id}
            AND title != 'Registration'
            ) as no_of_installment
            
        ,(SELECT amount FROM installment
            WHERE invoice_id = {$invoice_id}
            AND title = 'Installment1'
            ) as course_total_after_installment

        ,(SELECT SUM(ir.amount) FROM invoice_receipt_history ir
            LEFT JOIN `installment` ins ON (ir.installment_id = ins.installment_id)
            WHERE ir.receipt_id = {$receipt_id}
            AND ins.title != 'Registration'
            ) as invoice_amount_paid
            
        ,(SELECT ir.amount FROM invoice_receipt_history ir
            LEFT JOIN `installment` ins ON (ir.installment_id = ins.installment_id)
            WHERE ir.receipt_id = {$receipt_id}
            AND ins.title = 'Registration'
            ) as registration_paid_now
            
        ,(SELECT amount FROM installment
            WHERE invoice_id = {$invoice_id}
            AND title = 'Registration'
            ) as registration_applicable
            
        FROM invoice_receipt_history i
        WHERE i.invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQL);
        $rowInvoice = $db->sql_fetchrow($result);
        
        $invoice_amount_paid       = $rowInvoice['invoice_amount_paid'];
        //$invoice_amount_paid = round($invoice_amount_paid, 3);

        $total_no_paid_installment = $rowInvoice['total_no_paid_installment'];
        $installment_no            = $rowInvoice['total_no_paid_installment'] ;
        //$installment_no            += 1;
        $registration_paid_now     = $rowInvoice['registration_paid_now'] ;
        $registration_applicable   = $rowInvoice['registration_applicable'] ;

        $pdf->SetFont('Arial','',10);
        $count = 1;
        //$row = $db->sql_fetchrow($result);
        $pdf->SetXY(10, 168);
        $pdf->SetFont('Arial','B', 13);
        $pdf->Cell(190, 7, 'Payment Received', 1);
        $pdf->Ln(7);
        
        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(100, 8, "Description of Items",1);
        $pdf->Cell(55, 8, "Installments/due date" ,1);
        $pdf->Cell(15, 8, 'S$', 'LB', 0, '');
        $pdf->Cell(20, 8, 'Amount', 'RB', 0, 'R');
        //$pdf->Cell(35, 8, "S$ Amount" ,1, 0, 'R');
        $pdf->Ln(8);
        
        if ($registration_paid_now == ''){
            $registration_paid_now = '-';
        }
        else{
            $registration_paid_now     = round($registration_paid_now, 3);
            $registration_paid_now     = number_format($registration_paid_now, 2);
        }
        $registration_waived = '';
        if ($registration_applicable == ''){
            $registration_waived = 'Waived';
        }

        if ($registration_paid_now != '' && $invoice_amount_paid == ''){
            $invoice_amount_paid = $registration_paid_now;
            $invoice_amount_paid = '';
        }
        
        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, 'Total course fees', 'LRB');
        $pdf->Cell(55, 8, '[' . $installment_no . '/' . $rowInvoice['no_of_installment'] .']', 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        if($invoice_amount_paid != ''){
            $pdf->Cell(20, 8, number_format($invoice_amount_paid,2), 'RB', 0, 'R');
        }
        else{
            $pdf->Cell(20, 8, '', 'RB', 0, 'R');
        }
        $pdf->Ln(8);

        $pdf->Cell(100, 8, 'Application fees', 'LR');
        $pdf->Cell(55, 8, $registration_waived, 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $registration_paid_now, 'R', 0, 'R');
        $pdf->Ln(4);        
        
        $pdf->SetFont('Arial','', 7);
        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->Cell(100, 8, '(Non-refundable & not protected under FPS)', 'LRB');
        } else {
            $pdf->Cell(100, 8, '', 'LRB');
        }
        $pdf->Cell(55, 8,'', 'LRB', 0, 'C');
        $pdf->Cell(35, 8, '', 'RB', 0, 'R');
        $pdf->Ln(8);        
        
        $total_paid = $invoice_amount_paid + $registration_paid_now;
        $total_paid = number_format($total_paid, 2);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, 'Total Amount Paid', 'LRB');
        $pdf->Cell(55, 8, '', 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, $total_paid, 'RB', 0, 'R');
        //$pdf->Cell(35, 8, $total_paid, 'RB', 0, 'R');
        $pdf->Ln(8);        

        if($amount_outstanding != ''){
            $next_payment = 'Next payment on ' . $next_invoice_date;
            $amount_outstanding = number_format($amount_outstanding,2);
        }
        else{
            $next_payment = '';
            $amount_outstanding = '-';
        }
        
        $pdf->Cell(100, 8, 'Amount Outstanding', 'LRB');
        $pdf->Cell(55, 8, $next_payment , 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, $amount_outstanding, 'RB', 0, 'R');
        //$pdf->Cell(35, 8, $amount_outstanding, 'RB', 0, 'R');
        $pdf->Ln(8);        
        
        if ($courseRec['course_type'] == 'Long Term') {
            $pdf->Cell(100, 8, 'Certificate of Insurance(COI) NO:', 'LRB');
            $pdf->Cell(55, 8, $coi_no, 'LRB', 0, 'C');
            $pdf->Cell(35, 8, '', 'RB', 0, 'R');
            $pdf->Ln(8); 
        }       

        //================================ END OF TABLE===========================    
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(50, 8, 'Mode of Payment : ');
        $pdf->SetX(110);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40, 8,  $mode_of_payment);
        //$pdf->Cell(13, 8, '[] Nets');
        //$pdf->Cell(20, 8, '[] Cheque');
        $pdf->SetX(157);
        $pdf->Cell(50, 8, 'Cheque No : ' . $bank .' '. $cheque_no);
        $pdf->Ln(5);
        $pdf->SetX(110);
        $pdf->Cell(13, 8, '[] Visa');
        $pdf->Cell(20, 8, '[] Mastercard');
        $pdf->SetX(157);
        $pdf->Cell(50, 8, 'Approval Code : ' . $approval_code);
        $pdf->Ln(5);
        
       
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'Issued By : ' . $issued_by);
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'This is a computer-generated Receipt. No signature is required');
        $pdf->Ln(7);

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, 'MASS Education issues receipts to acknowledge fees are paid accordingly and kindly check all details above are correct');
        $pdf->Ln(4);
        
        $pdf->Cell(130, 5, 'Please refer to our student handbook and website for refund/withdrawal/transfer policies');
        $pdf->Ln(4);

        $pdf->Cell(130, 5, 'There is no GST charged');
        $pdf->Ln(4);

        /* Creation of media record of the invoice */
        $file_name = $receipt_code .'.pdf';
        
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/' .'temp';        
        $outputFileName = $outputPath . '/'. $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        
        /* Code for deleting old receipt during edit of receipt */
        $SQLReceipt = "
        SELECT * FROM media
        WHERE record_id = {$receipt_id}
        ";
        $resultReceipt  = $db->sql_query($SQLReceipt);
        $numRowsReceipt = $db->sql_numrows($resultReceipt);
		if ($numRows > 0){
            $SQLDelete = "DELETE FROM media WHERE record_id = {$receipt_id}";
            $resultDelete = $db->sql_query($SQLDelete);
		}
        
        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_receipt';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }

    /**
     *
     */
    function getGenerateMiscReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $late_fee, $change_fee, $review_fee, $deferment_fees, $service_fees, $other_charges) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);
        
        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $numrowsInvoiceItem = 1;
        $material_fee = '';
        $assesment_fee = '';
        $science_lab_fee = '';
        $medical_insurance_value = '';
        
        $unitPriceAmt = 0;
        $invoiceItemQty = 0;
        $subsidyAmt = 0;
        $contactPerson = '';

        $orderRec       = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id);
        $contactRec     = $fn->getRecordRowByID('contact', 'contact_id', $orderItemRec['contact_id']);
        $courseRec      = $fn->getRecordRowByID('course', 'course_id', $orderItemRec['record_id']);
        $companyRec     = $fn->getRecordRowByID('company', 'company_id', $orderRec['company_id']);
        $countryNameRec = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}
        //===================================HEADER=================================== //
        $pdf->SetFont('Arial','',10);
        $row = $db->sql_fetchrow($result);
        $mode_of_payment = $row['mode_of_payment'];
        $cheque_no       = $row['cheque_no'];
        $bank            = $row['bank_name'];
        $issued_by       = $row['issued_by'];
        $coi_no          = $row['coi_no'];
        $approval_code   = $row['approval_code'];

        $pdf->Image('images/logo-print.jpg',10,5,45);

        $pdf->SetFont('Arial','',8);
        $pdf->SetXY(80,1);
        $pdf->Cell(24, 20, $cpCfg['addressFlatPvt']);
        $pdf->Cell(28, 20, $cpCfg['addressStreetPvt']);
        $pdf->Cell(20, 20, $cpCfg['addressCountryAndCodePvt']);
        $pdf->Ln(5);
        $pdf->SetX(94);
        $pdf->Cell(26, 20, 'Tel : (65)' . $cpCfg['contactNoPvt']);
        $pdf->Cell(20, 20, 'Fax : ' . $cpCfg['printCompanyFaxPvt']);
        $pdf->Ln(5);
        $pdf->SetX(104);
        $pdf->Cell(50, 20, 'www.mass.edu.sg');
        $pdf->Ln(15);
        
        $pdf->Line(10,33,195,33);

        /* Amendment */
        $pdf->SetFont('Arial','B',10);
        $pdf->SetXY(100, 35);
        $pdf->Cell(21, 20, "OFFICIAL RECEIPT", 0, 0, 'C');                
        $pdf->Ln(17);
        
        $pdf->SetX(10);
        $registration_data = 'Registration No: ' . $contactRec['registration_no'];
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(25, 5, $registration_data); 
        
        $pdf->SetFont('Arial','',10);
        $pdf->SetX(129);
        $receipt_code = 'Receipt Number: ' . $row['receipt_code'];
        $pdf->Cell(175, 5, $receipt_code);
        $pdf->Ln();

        $pdf->SetXY(10, 50);
        $date = $fn->getCPDate($row['date'], 'dS F Y');
        $receipt_date = 'Date: ' . $date;
        $pdf->Cell(50, 20, $receipt_date);
        $pdf->Ln(6);
        
        $pdf->SetX(10);
        $student_full_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
        $student_name = 'Student Name: ' . $student_full_name;
        $pdf->Cell(50, 20, $student_name);
        $pdf->Ln(6);
        
        $pdf->SetX(10);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(27, 20, 'Course Enrolled: ');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(100, 20, $courseRec['title']);
        $pdf->SetX(129);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(23, 20, 'Course Code: ');
        $pdf->Cell(50, 20, $courseRec['course_code']);
        $pdf->Ln(6);
        
        $pdf->SetXY(10, 75);
        $pdf->SetFont('Arial','',10);
        $from_date = $fn->getCPDate($courseRec['valid_date_from'], 'dS F Y');
        $from_date_format = 'Commencement Date: ' . $from_date;
        $pdf->Cell(25, 5, $from_date_format);
        $pdf->SetXY(129, 75);
        $to_date = $fn->getCPDate($courseRec['valid_date_to'], 'dS F Y');
        $to_date_format = 'End Date: ' . $to_date;
        $pdf->Cell(175, 5, $to_date_format);
        $pdf->Ln();

        $pdf->SetXY(10, 78);
        $pdf->SetFont('Arial','',10);
        $course_title = 'Course Awarded by: ' . $courseRec['award_course'];
        $pdf->Cell(50, 10, $course_title);
        $pdf->Ln();
        //===================================FIRST TABLE============================= //
        
        $pdf->SetX(10);
        $pdf->SetFont('Arial','B', 13);
        $pdf->Cell(190, 7, 'Details of Payment', 1);
        $pdf->Ln(7);
        
        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(100, 8, "Description of Miscellaneous Items",1);
        $pdf->Cell(55, 8, "No of Subject / Scheme",1);
        $pdf->Cell(15, 8, 'S$', 'LB', 0, '');
        $pdf->Cell(20, 8, 'Amount', 'RB', 0, 'R');
        $pdf->Ln(8);
        
        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '1. ' . $cpCfg['printLateChargeFeeTxt'], 'LR');
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $late_fee, 'R', 0, 'R');
        $pdf->Ln(6);
        
        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '2. ' . $cpCfg['printModuleSubjectChangeFeeTxt'], 'LR');
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $change_fee, 'R', 0, 'R');
        $pdf->Ln(6);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '3. ' . $cpCfg['printReviewExamResultFeeTxt'], 'LR');
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $review_fee, 'R', 0, 'R');
        $pdf->Ln(6);
        
        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '4. ' . $cpCfg['printNSDefermentFeeTxt'], 'LR');
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $deferment_fees, 'R', 0, 'R');
        $pdf->Ln(6);
        
        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '5. ' . $cpCfg['printCreditCardChargeTxt'], 'LR');
        $pdf->Cell(55, 8, '', 'LR', 0, 'C');
        $pdf->Cell(15, 8, '$', 'L', 0, '');
        $pdf->Cell(20, 8, $service_fees, 'R', 0, 'R');
        $pdf->Ln(6);
        
        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, '6. ' . $cpCfg['printOtherFeeTxt'], 'LRB');
        $pdf->Cell(55, 8, '', 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, $other_charges, 'RB', 0, 'R');
        $pdf->Ln(6);

        $count++;
        $receipt_code = $row['receipt_code'];

        //=========================SECOND TABLE=================================== //
        $pdf->SetFont('Arial','',10);
        $count = 1;
        $pdf->SetXY(10, 168);
        $pdf->SetFont('Arial','B', 13);
        $pdf->Cell(190, 7, 'Payment Received', 1);
        $pdf->Ln(7);
        
        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(100, 8, "Description of Items",1);
        $pdf->Cell(55, 8, "Installments/due date" ,1);
        $pdf->Cell(15, 8, 'S$', 'LB', 0, '');
        $pdf->Cell(20, 8, 'Amount', 'RB', 0, 'R');
        $pdf->Ln(8);
        
        $total_fees = $late_fee + $change_fee + $review_fee + $deferment_fees + $service_fees + $other_charges;
        
        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, 'Total fees', 'LRB');
        $pdf->Cell(55, 8, '', 'LRB');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, number_format($total_fees,2), 'RB', 0, 'R');
        $pdf->Ln(8);

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(100, 8, 'Total Amount Paid', 'LRB');
        $pdf->Cell(55, 8, '', 'LRB', 0, 'C');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, number_format($total_fees,2), 'RB', 0, 'R');
        $pdf->Ln(8);        

        $pdf->Cell(100, 8, 'Amount Outstanding', 'LRB');
        $pdf->Cell(55, 8, '', 'LRB');
        $pdf->Cell(15, 8, '$', 'LB', 0, '');
        $pdf->Cell(20, 8, '-', 'RB', 0, 'R');
        $pdf->Ln(8);        

        //================================ END OF TABLE===========================    
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(50, 8, 'Mode of Payment : ');
        $pdf->SetX(110);
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40, 8,  $mode_of_payment);
        //$pdf->Cell(13, 8, '[] Nets');
        //$pdf->Cell(20, 8, '[] Cheque');
        $pdf->SetX(157);
        $pdf->Cell(50, 8, 'Cheque No : ' . $bank .' '. $cheque_no);
        $pdf->Ln(5);
        $pdf->SetX(110);
        $pdf->Cell(13, 8, '[] Visa');
        $pdf->Cell(20, 8, '[] Mastercard');
        $pdf->SetX(157);
        $pdf->Cell(50, 8, 'Approval Code : ' . $approval_code);
        $pdf->Ln(5);
        
       
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'Issued By : ' . $issued_by);
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(190, 8, 'Signature:');
        $pdf->Ln(7);

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Note:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, 'MASS Education issues receipts to acknowledge fees are paid accordingly and kindly check all details above are correct');
        $pdf->Ln(4);
        
        $pdf->Cell(130, 5, 'There is no GST charged');
        $pdf->Ln(4);

        /* Creation of media record of the invoice */
        $file_name = $receipt_code .'.pdf';
        
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/' .'temp';        
        $outputFileName = $outputPath . '/'. $file_name;
        $pdf->Output($outputFileName , "F");
        
        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'edukloud_receipt';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);

        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
    }
    /**
     */
    function getGenerateReceiptFormSubmitPvt() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $receipt_type    = $fn->getPostParam('receipt_type');

        if (!$this->getGenerateReceiptFormValidatePvt($receipt_type)){
            return $validate->getErrorMessageXML();
        }

        //$invoiceCodes    = $fn->getPostParam('invoiceCode', array());
        $invoiceHistId   = $fn->getPostParam('invoiceHistId', array());
        $invoice_id      = $fn->getPostParam('invoice_id');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $remarks         = $fn->getPostParam('remarks');
        $amount          = $fn->getPostParam('amount');
        $order_id        = $fn->getReqParam('order_id');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $bank_name       = $fn->getPostParam('bank_name');
        $receipt_date    = $fn->getPostParam('date');
        $receipt_code    = $fn->getPostParam('receipt_code');
        $issued_by       = $fn->getPostParam('issued_by');
        $coi_no          = $fn->getPostParam('coi_no');
        $approval_code   = $fn->getPostParam('approval_code');
        $course_type     = $fn->getPostParam('course_type');

        if ($receipt_type == 'misc receipt') {
            $late_fee       = $fn->getPostParam('late_fees');
            $change_fee     = $fn->getPostParam('module_subject_change_fee');
            $review_fee     = $fn->getPostParam('exam_result_review_fee');
            $deferment_fees = $fn->getPostParam('ns_deferment_fees');
            $service_fees   = $fn->getPostParam('credit_card_service_fees');
            $other_charges  = $fn->getPostParam('other_charges');

            $fa = array();
            $fa['amount']         = $amount;
            $fa['order_id']       = $order_id;
            
            if ($course_type == 'Short Term') {
                
                $rowPrefix = $fn->getRecordByCondition('setting', "key_text = 'miscReceiptCodePrefix'");
                $current_year = date('Y');
    
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $nextReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");
    
                if($nextReceiptCode < 10){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextReceiptCode;
                }
                else if($nextReceiptCode < 100){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextReceiptCode;
                }
                else if($nextReceiptCode > 99){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
                }
                else if($nextReceiptCode > 99 && $nextReceiptCode < 1000){
                    $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
                }
                else if($nextReceiptCode > 999 && $nextReceiptCode < 10000){
                    $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-0' . $nextReceiptCode;
                }
                else{
                    $nextReceiptCode = $rowPrefix['value'] . '-' . $current_year . '-' . $nextReceiptCode;
                }
            } else {
                $fa['receipt_code']   = $receipt_code;
            }
            
            $fa['mode_of_payment']= $mode_of_payment;
            $fa['cheque_date']    = $cheque_date;
            $fa['cheque_no']      = $cheque_no;
            $fa['bank_name']      = $bank_name;
            $fa['remarks']        = $remarks;
            $fa['date']           = $receipt_date;
            $fa['issued_by']      = $issued_by;
            $fa['coi_no']         = $coi_no;
            $fa['approval_code']  = $approval_code;
            $fa['receipt_status'] = 'Paid';
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');
            
            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();
            
            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $amount;
            $fa['invoice_date']  = $receipt_date;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $fa['created_by']    = $fn->getSessionParam('userName');
            $fa['receipt_type']  = 'misc receipt';
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
    
            $this->getGenerateMiscReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $late_fee, $change_fee, $review_fee, $deferment_fees, $service_fees, $other_charges);
        } else {
            $count = count($invoiceHistId);
            // To check if there is any receipt paid earlier
            $SQLRecptPaid = "
            SELECT count(amount) AS first_receipt
            FROM installment
            WHERE invoice_paid_status = 'Paid'
            AND invoice_id = {$invoice_id}
            ";
            $resultRecptPaid  = $db->sql_query($SQLRecptPaid);
            $rowRecptPaid     = $db->sql_fetchrow($resultRecptPaid);
            $first_receipt_present = $rowRecptPaid['first_receipt']; 
    
            /*
            $receipt_id =  151;
            $this->getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present);
            return $validate->getSuccessMessageXML();
            */
               
    
            //To update receipt codes
            //$receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");
            
            $fa = array();
            $fa['amount']         = $amount;
            $fa['order_id']       = $order_id;
            
            if ($course_type == 'Short Term') {
                //To update receipt code
                
                if ($receipt_type == 'misc receipt') {
                    $rowPrefix = $fn->getRecordByCondition('setting', "key_text = 'miscReceiptCodePrefix'");
                } else {
                    $rowPrefix = $fn->getRecordByCondition('setting', "key_text = 'receiptCodePrefix'");
                }
                
                $current_year = date('Y');
    
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $nextReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");
    
                if($nextReceiptCode < 10){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextReceiptCode;
                }
                else if($nextReceiptCode < 100){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextReceiptCode;
                }
                else if($nextReceiptCode > 99){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
                }
                else{
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextReceiptCode;
                }
            } else {
                $fa['receipt_code']   = $receipt_code;
            }
            
            $fa['mode_of_payment']= $mode_of_payment;
            $fa['cheque_date']    = $cheque_date;
            $fa['cheque_no']      = $cheque_no;
            $fa['bank_name']      = $bank_name;
            $fa['remarks']        = $remarks;
            $fa['date']           = $receipt_date;
            $fa['issued_by']      = $issued_by;
            $fa['coi_no']         = $coi_no;
            $fa['approval_code']  = $approval_code;
            $fa['receipt_status'] = 'Paid';
            
            if ($receipt_type == 'misc receipt') {
                $fa['receipt_type']   = $receipt_type;
            }
            
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');
            
            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();
            $receipt_amount     = $amount;
            $invoice_status_due = '';
            $count = 0;
            
            // TO Change for Installment
            foreach($invoiceHistId AS $invoiceHistId){
                $invoiceRec = $fn->getRecordRowByID('installment', 'installment_id', $invoiceHistId);
                $invoice_amount = $invoiceRec['amount'];
                
                if ($invoiceRec['invoice_paid_status'] == 'Paid' || 
                $receipt_amount <= 0){
                    continue;
                }
                
                $SQLPaid = "
                SELECT SUM(amount) AS prev_sum
                FROM invoice_receipt_history
                WHERE installment_id = '{$invoiceHistId}'
                ";
                $resultPaid = $db->sql_query($SQLPaid);
                $rowPaid    = $db->sql_fetchrow($resultPaid);
                
                $invoice_amount = $invoice_amount - $rowPaid['prev_sum']; 
    
                $faInv = array();
                $recpInvAmount = 0;
                if ($invoice_amount <= $receipt_amount){
                    $recpInvAmount = $invoice_amount;
                    $faInv['invoice_paid_status'] = 'Paid';
                } else if ($invoice_amount > $receipt_amount){
                    $recpInvAmount = $receipt_amount;
                    $faInv['invoice_paid_status'] = 'Partial Payment';
                }
                
                $receipt_amount = $receipt_amount - $recpInvAmount;
                $fn->saveRecord($faInv, 'installment', 'installment_id', $invoiceHistId);
                
                //Inserting receipt id in to history table ( one invoice can have multiple receipts)
                $fa = array();
                $fa['receipt_id']    = $receipt_id;
                $fa['invoice_id']    = $invoice_id;
                $fa['installment_id']= $invoiceHistId;
                $fa['amount']        = $recpInvAmount;
                $fa['invoice_date'] =  $receipt_date;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by']     = $fn->getSessionParam('userName');
                $histId = $fn->addRecord($fa, 'invoice_receipt_history');
             }
    
             
            $SQL = "
            SELECT i.*
            FROM installment i
            WHERE i.invoice_id = {$invoice_id} 
            AND (i.invoice_paid_status != 'Paid' 
                || i.invoice_paid_status = ''
                || i.invoice_paid_status IS NULL
                )
            ";
            $result = $db->sql_query($SQL);
            $numRows  = $db->sql_numrows($result);
            $faInv = array();
            if ($numRows > 0){
                $faInv['status'] = 'Partial Payment';
                $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            }
            else{
                $faInv['status'] = 'Paid';
                $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            }
            //To create PDF related to receipt and save it in media
            $this->getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present);
        }
        
        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getGenerateReceiptFormValidatePvt($receipt_type) {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_amount = '';
        $invoice_prev_amount = '';
        $balance_amount = '';
        $amount       = $fn->getPostParam('amount');
        $invoiceHistId = $fn->getPostParam('invoiceHistId', array());

        $validate->resetErrorArray();
        /*
        if(count($invoiceCodesArr) == 0){
            $validate->validateData('amount' , 'Please choose the invoice(s) to be paid');
        }

        $invoiceCodes = join(",", $invoiceCodesArr);
        
        if ($invoiceCodes != ''){
            $SQL = "
                SELECT SUM(invoice_amount) as invoice_sum
                FROM invoice
                WHERE invoice_code IN ($invoiceCodes)
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $total_invoice_amount = $rowPaid['invoice_sum'];

            $SQLPaid = "
                SELECT SUM(amount) as prev_sum
                FROM invoice_receipt_history
                WHERE invoice_id IN (
                    SELECT invoice_id
                    FROM invoice
                    WHERE invoice_code IN ($invoiceCodes)
                    )
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];

            $balance_amount = $total_invoice_amount - $prev_sum;

            if($amount > $balance_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'Please enter amount less than the balance amount';
            }
        }
        */
        
       if ($receipt_type != 'misc receipt') {
            $validate->validateData('invoiceHistId' , 'Please check any one of the invoice');
       }
       $validate->validateData('mode_of_payment' , 'Please choose mode of payment');
       // $validate->validateData('amount' , 'Please enter the amount');
        //$validate->validateData('mode_of_payment' , 'Please select mode of payment');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPopulateMiscTotalAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $late_fees          = $fn->getReqParam('late_fees');
        $change_fees        = $fn->getReqParam('change_fees');
        $review_fees        = $fn->getReqParam('review_fees');
        $deferment_fees     = $fn->getReqParam('deferment_fees');
        $credit_card_fees   = $fn->getReqParam('credit_card_fees');
        $other_fees         = $fn->getReqParam('other_fees');
        
        return $total_misc_amount = $late_fees + $change_fees + $review_fees + $deferment_fees + $credit_card_fees + $other_fees;
    }

    /**
     */
    function getEditReceiptFormSubmitPvt() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        /*if (!$this->getGenerateReceiptFormValidatePvt()){
            return $validate->getErrorMessageXML();
        }*/

        $amount          = $fn->getPostParam('amount');
        $receipt_date    = $fn->getPostParam('date');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $bank_name       = $fn->getPostParam('bank_name');
        $issued_by       = $fn->getPostParam('issued_by');
        $approval_code   = $fn->getPostParam('approval_code');
        $remarks         = $fn->getPostParam('remarks');
        $receipt_id      = $fn->getReqParam('receipt_id');
        $order_id        = $fn->getReqParam('order_id');
        $invoice_id      = $fn->getReqParam('invoice_id');

        /* How to save invoice id 
        $invoiceHistId   = $fn->getPostParam('invoiceHistId', array());

        $count = count($invoiceHistId);*/
        
        //To update the existing receipt record with input values made in edit receipt.
        $fa = array();
        $fa['amount']         = $amount;
        $fa['date']           = $receipt_date;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['cheque_no']      = $cheque_no;
        $fa['cheque_date']    = $cheque_date;
        $fa['bank_name']      = $bank_name;
        $fa['issued_by']      = $issued_by;
        $fa['approval_code']  = $approval_code;
        $fa['remarks']        = $remarks;
        $fa['modification_date']  = date("Y-m-d H:i:s");
        $fa['modified_by']     = $fn->getSessionParam('userName');
        
        $whereCondition = "WHERE receipt_id = {$receipt_id}";
        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'receipt', $whereCondition);
        $resultSQL =$db->sql_query($updateSQL);

        $receipt_amount = $amount;

        /* Deleting the previously paid records in invoice receipt history related to this receipt 
        $SQLDelete = "
        DELETE FROM invoice_receipt_history WHERE receipt_id = {$receipt_id}";
        $resultDelete = $db->sql_query($SQLDelete);*/
        
        /* Setting the amount & date empty for the previously paid records in invoice receipt history related to this receipt */
        $SQLIRHUpdate = "
        UPDATE invoice_receipt_history 
        SET amount = '' 
        AND invoice_date = ''
        WHERE receipt_id = {$receipt_id}";
        $resultIRHUpdate = $db->sql_query($SQLIRHUpdate);
        
        /* Setting the status to empty for all installments related to this receipt */
        $SQLUpdate = "
        UPDATE  installment i
        LEFT JOIN (invoice_receipt_history irh) ON (i.installment_id = irh.installment_id)
        SET i.invoice_paid_status = '' 
        WHERE i.invoice_id = {$invoice_id}
          AND irh.receipt_id = {$receipt_id}";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $SQLOverall = "
        SELECT i.amount
              ,i.installment_id
              ,irh.installment_id
              ,irh.invoice_receipt_history_id
        FROM installment i
        LEFT JOIN (invoice_receipt_history irh) ON (i.installment_id = irh.installment_id)
        WHERE i.invoice_id = {$invoice_id}
          AND irh.receipt_id = {$receipt_id}
          ORDER BY i.installment_id
        ";
        $resultOverall = $db->sql_query($SQLOverall);

        while ($rowOverall = $db->sql_fetchrow($resultOverall)) {
            if ($receipt_amount > 0){
                
                //To get the previously paid amount for the isntallment from inv hisotry table
                $SQLInstPayment = "
                SELECT SUM(irechist.amount) AS previous_paid_amount
                FROM invoice_receipt_history irechist
                WHERE irechist.installment_id = {$rowOverall['installment_id']}
                ";
                $resultInstPayment = $db->sql_query($SQLInstPayment);
                $rowInstPayment = $db->sql_fetchrow($resultInstPayment);
                
                if ($rowInstPayment['previous_paid_amount'] > 0) {
                    $installment_amount = $rowOverall['amount'] - $rowInstPayment['previous_paid_amount'];
                } else {
                    $installment_amount = $rowOverall['amount'];
                }
        
                if ($receipt_amount >= $installment_amount) {
                    $faInst['invoice_paid_status'] = 'Paid';
                    $fn->saveRecord($faInst, 'installment', 'installment_id', $rowOverall['installment_id']);
                } else if ($receipt_amount > 0) {
                    $installment_amount = $receipt_amount;
                    $faInst['invoice_paid_status'] = 'Partial Payment';
                    $fn->saveRecord($faInst, 'installment', 'installment_id', $rowOverall['installment_id']);
                }
                
                if ($receipt_amount > 0) {
                    $fa = array();
                    $fa['amount']            = $installment_amount;
                    $fa['invoice_date']      = $receipt_date;
                    $fa['modification_date'] = date("Y-m-d H:i:s");
                    $fa['modified_by']       = $fn->getSessionParam('userName');
                    
                    $histId = $fn->saveRecord($fa, 'invoice_receipt_history', 'invoice_receipt_history_id', $rowOverall['invoice_receipt_history_id']);
                }

                $receipt_amount = $receipt_amount - $installment_amount;
            }
        }
        
        //If balance amount($receipt_amount) is greater than 0 and there is no invoice history record,
        // we are running the below code to create inv history record.
        $SQLInst = "
        SELECT i.*
        FROM installment i
        WHERE i.invoice_id = {$invoice_id}
          AND i.invoice_paid_status IS NULL OR i.invoice_paid_status = ''
        ORDER BY i.installment_id ASC
        ";
        $resultInst = $db->sql_query($SQLInst);

        while ($rowInst = $db->sql_fetchrow($resultInst)) {
        
            if ($receipt_amount) {
                $installment_amount = $rowInst['amount'];
                if ($receipt_amount >= $installment_amount) {
                    $faInst['invoice_paid_status'] = 'Paid';
                    $fn->saveRecord($faInst, 'installment', 'installment_id', $rowInst['installment_id']);
                } else if ($receipt_amount > 0) {
                    $installment_amount = $receipt_amount;
                    $faInst['invoice_paid_status'] = 'Partial Payment';
                    $fn->saveRecord($faInst, 'installment', 'installment_id', $rowInst['installment_id']);
                }

                $fa = array();
                $fa['receipt_id']    = $receipt_id;
                $fa['invoice_id']    = $invoice_id;
                $fa['installment_id']= $rowInst['installment_id'];
                $fa['amount']        = $installment_amount;
                $fa['invoice_date']  = $receipt_date;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by']    = $fn->getSessionParam('userName');
                
                $histId = $fn->addRecord($fa, 'invoice_receipt_history');

                $receipt_amount = $receipt_amount - $installment_amount;
            }
        }
        
        //Updating the invoice record status.
        $SQL = "
        SELECT i.*
        FROM installment i
        WHERE i.invoice_id = {$invoice_id} 
        AND (i.invoice_paid_status != 'Paid' 
            || i.invoice_paid_status = ''
            || i.invoice_paid_status IS NULL
            )
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);
        $faInv = array();
        if ($numRows > 0){
            $faInv['status'] = 'Partial Payment';
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
        } else{
            $faInv['status'] = 'Paid';
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
        }

        // To check if there is any receipt paid earlier for the installment
        /*$SQLRecptPaid = "
        SELECT count(amount) AS first_receipt
        FROM installment
        WHERE invoice_paid_status = 'Paid'
        AND invoice_id = {$invoice_id}
        ";
        $resultRecptPaid  = $db->sql_query($SQLRecptPaid);
        $rowRecptPaid     = $db->sql_fetchrow($resultRecptPaid);
        $first_receipt_present = $rowRecptPaid['first_receipt'];*/ 

        // To check if there is any receipt paid earlier for the installment    
        /*$SQLRecpt = "
        SELECT count(*)
        FROM invoice_receipt_history
        WHERE invoice_id = {$invoice_id}
        ";*/
        //Check the number of record in receipt related to the order id, to find if the receipt is first receipt or not
        $SQLRecpt = "
        SELECT count(*)
        FROM receipt
        WHERE order_id = {$order_id}
        ";
        $resultRecpt  = $db->sql_query($SQLRecpt);
        $numRows  = $db->sql_numrows($resultRecpt);
        if($numRows == 1) {
            $first_receipt_present = 0;
        } else {
            $first_receipt_present = 1;
        }

        $this->getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present);
        
        return $validate->getSuccessMessageXML();
        //check
    }

    /**
     *
     */
    function getDeleteInvoiceFormPvt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $invoice_id = $fn->getReqParam('invoice_id');
        $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);

        /* Invoice Records */
        $SQLMediaInvoice = "
        DELETE FROM media
        WHERE record_id = {$invoice_id}
          AND room_name = 'edukloud_invoice'
          AND record_type = 'attachment'
        ";
        $resultMediaInvoice = $db->sql_query($SQLMediaInvoice);
        
        $SQLInvoice = "
        DELETE FROM invoice
        WHERE invoice_id = {$invoice_id}
        ";
        $resultInvoice = $db->sql_query($SQLInvoice);

        $SQLInvoiceItem = "
        DELETE FROM invoice_item
        WHERE invoice_id = {$invoice_id}
        ";
        $resultInvoiceItem = $db->sql_query($SQLInvoiceItem);

        $SQLInstallment = "
        DELETE FROM installment
        WHERE invoice_id = {$invoice_id}
        ";
        $resultInstallment = $db->sql_query($SQLInstallment);

        /* Receipt Records */
        $SQLMediaReceipt = "
        DELETE FROM media
        WHERE record_id IN (
           SELECT receipt_id FROM receipt
           WHERE order_id = {$invoiceRec['order_id']}
          )
          AND room_name = 'edukloud_receipt'
          AND record_type = 'attachment'
        ";
        $resultMediaReceipt = $db->sql_query($SQLMediaReceipt);
        
        $SQLReceipt = "
        DELETE FROM receipt
        WHERE order_id = {$invoiceRec['order_id']}
        ";
        $resultReceipt = $db->sql_query($SQLReceipt);

        $SQLInvReceiptHist = "
        DELETE FROM invoice_receipt_history
        WHERE invoice_id = {$invoice_id}
        ";
        $resultInvReceiptHist = $db->sql_query($SQLInvReceiptHist);

        return $cpUtil->redirect("index.php?_topRm=finance&module=edukloud_order&_action=edit");
    }

    /**
     *
     */
    function getCancelInvoice() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_code = $fn->getReqParam('invoice_code');
        
        /* Finding of receipt record */
        $SQLIrh = "
        SELECT irh.*
              ,i.invoice_code
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        WHERE i.invoice_code = {$invoice_code}
          AND irh.amount > 0
        ";
        $resultIrh = $db->sql_query($SQLIrh);
        $numRowsIrh = $db->sql_numrows($resultIrh);
        $rowIrh = $db->sql_fetchrow($resultIrh);
        
        if ($numRowsIrh == 0) {
            /* Updating of invoice record */
            $sqlInv = "
            UPDATE invoice
            SET status = 'Cancelled'
            WHERE invoice_code = {$invoice_code}
            ";
            $resultInv = $db->sql_query($sqlInv);
        
        } else {
            return "Cannot cancel"; // Passing the value to jquery to give alert message
        }

        return;
    }

    /**
     *
     */
    function getCancelReceipt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $receipt_code = $fn->getReqParam('receipt_code');

        $irh = $fn->getRecordByCondition('receipt', "receipt_code = {$receipt_code}");
        
        $sqlIrh = "
        SELECT invoice_id FROM invoice_receipt_history
        WHERE receipt_id = {$irh['receipt_id']}
        ";
        $resultIrh = $db->sql_query($sqlIrh);
        while ($rowIrh = $db->sql_fetchrow($resultIrh)) {

            /* Updating of status in invoice table */
            $SQLInvHist = "
            SELECT *
            FROM invoice_receipt_history
            WHERE invoice_id = {$rowIrh['invoice_id']}
              AND amount > 0
            ";
            
            $resultInvHist = $db->sql_query($SQLInvHist);
            $numRowsInvHist = $db->sql_numrows($resultInvHist);
            $rowInvHist = $db->sql_fetchrow($resultInvHist);
            
            /* Updating status to due if one record in hist table for the invoice */
            if ($numRowsInvHist > 1) {
            } else {
                $sqlIn = "
                UPDATE invoice
                SET status = 'Due'
                WHERE invoice_id = {$rowIrh['invoice_id']}
                ";
                $resultIn = $db->sql_query($sqlIn);
            }
            
            /* Setting of amount to 0 in history table */
            $SqlInvrec = "
            UPDATE invoice_receipt_history
            SET amount = 0
            WHERE receipt_id = {$irh['receipt_id']}
            ";
            $resultInvrec = $db->sql_query($SqlInvrec);

        }

        /* Updating the status of the receipt in receipt table */
        $sqlRec = "
        UPDATE receipt
        SET receipt_status = 'Cancelled'
        WHERE receipt_code = {$receipt_code}
        ";
        $resultRec = $db->sql_query($sqlRec);
        
        return;
    }
    /**
     */
    function getGenerateBookReceiptFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $receipt_type    = $fn->getPostParam('receipt_type');
        return;
        //These codes are copied from reciptformsubmitpvt and not changed anything according to book reciept, so those who want to use change the code and use..
        if (!$this->getGenerateReceiptFormValidatePvt($receipt_type)){
            return $validate->getErrorMessageXML();
        }

        //$invoiceCodes    = $fn->getPostParam('invoiceCode', array());
        $invoiceHistId   = $fn->getPostParam('invoiceHistId', array());
        $invoice_id      = $fn->getPostParam('invoice_id');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $remarks         = $fn->getPostParam('remarks');
        $amount          = $fn->getPostParam('amount');
        $order_id        = $fn->getReqParam('order_id');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $bank_name       = $fn->getPostParam('bank_name');
        $receipt_date    = $fn->getPostParam('date');
        $receipt_code    = $fn->getPostParam('receipt_code');
        $issued_by       = $fn->getPostParam('issued_by');
        $coi_no          = $fn->getPostParam('coi_no');
        $approval_code   = $fn->getPostParam('approval_code');
        $course_type     = $fn->getPostParam('course_type');

        if ($receipt_type == 'misc receipt') {
            $late_fee       = $fn->getPostParam('late_fees');
            $change_fee     = $fn->getPostParam('module_subject_change_fee');
            $review_fee     = $fn->getPostParam('exam_result_review_fee');
            $deferment_fees = $fn->getPostParam('ns_deferment_fees');
            $service_fees   = $fn->getPostParam('credit_card_service_fees');
            $other_charges  = $fn->getPostParam('other_charges');

            $fa = array();
            $fa['amount']         = $amount;
            $fa['order_id']       = $order_id;
            
            if ($course_type == 'Short Term') {
                
                $rowPrefix = $fn->getRecordByCondition('setting', "key_text = 'miscReceiptCodePrefix'");
                $current_year = date('Y');
    
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $nextReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");
    
                if($nextReceiptCode < 10){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextReceiptCode;
                }
                else if($nextReceiptCode < 100){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextReceiptCode;
                }
                else if($nextReceiptCode > 99){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
                }
                else if($nextReceiptCode > 99 && $nextReceiptCode < 1000){
                    $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
                }
                else if($nextReceiptCode > 999 && $nextReceiptCode < 10000){
                    $nextInvoiceCode = $rowPrefix['value'] . '-' . $current_year . '-0' . $nextReceiptCode;
                }
                else{
                    $nextReceiptCode = $rowPrefix['value'] . '-' . $current_year . '-' . $nextReceiptCode;
                }
            } else {
                $fa['receipt_code']   = $receipt_code;
            }
            
            $fa['mode_of_payment']= $mode_of_payment;
            $fa['cheque_date']    = $cheque_date;
            $fa['cheque_no']      = $cheque_no;
            $fa['bank_name']      = $bank_name;
            $fa['remarks']        = $remarks;
            $fa['date']           = $receipt_date;
            $fa['issued_by']      = $issued_by;
            $fa['coi_no']         = $coi_no;
            $fa['approval_code']  = $approval_code;
            $fa['receipt_status'] = 'Paid';
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');
            
            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();
            
            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $amount;
            $fa['invoice_date']  = $receipt_date;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $fa['created_by']    = $fn->getSessionParam('userName');
            $fa['receipt_type']  = 'misc receipt';
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
    
            $this->getGenerateMiscReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $late_fee, $change_fee, $review_fee, $deferment_fees, $service_fees, $other_charges);
        } else {
            $count = count($invoiceHistId);
            // To check if there is any receipt paid earlier
            $SQLRecptPaid = "
            SELECT count(amount) AS first_receipt
            FROM installment
            WHERE invoice_paid_status = 'Paid'
            AND invoice_id = {$invoice_id}
            ";
            $resultRecptPaid  = $db->sql_query($SQLRecptPaid);
            $rowRecptPaid     = $db->sql_fetchrow($resultRecptPaid);
            $first_receipt_present = $rowRecptPaid['first_receipt']; 
    
            /*
            $receipt_id =  151;
            $this->getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present);
            return $validate->getSuccessMessageXML();
            */
               
    
            //To update receipt codes
            //$receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");
            
            $fa = array();
            $fa['amount']         = $amount;
            $fa['order_id']       = $order_id;
            
            if ($course_type == 'Short Term') {
                //To update receipt code
                
                if ($receipt_type == 'misc receipt') {
                    $rowPrefix = $fn->getRecordByCondition('setting', "key_text = 'miscReceiptCodePrefix'");
                } else {
                    $rowPrefix = $fn->getRecordByCondition('setting', "key_text = 'receiptCodePrefix'");
                }
                
                $current_year = date('Y');
    
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $nextReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");
    
                if($nextReceiptCode < 10){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextReceiptCode;
                }
                else if($nextReceiptCode < 100){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextReceiptCode;
                }
                else if($nextReceiptCode > 99){
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
                }
                else{
                    $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextReceiptCode;
                }
            } else {
                $fa['receipt_code']   = $receipt_code;
            }
            
            $fa['mode_of_payment']= $mode_of_payment;
            $fa['cheque_date']    = $cheque_date;
            $fa['cheque_no']      = $cheque_no;
            $fa['bank_name']      = $bank_name;
            $fa['remarks']        = $remarks;
            $fa['date']           = $receipt_date;
            $fa['issued_by']      = $issued_by;
            $fa['coi_no']         = $coi_no;
            $fa['approval_code']  = $approval_code;
            $fa['receipt_status'] = 'Paid';
            
            if ($receipt_type == 'misc receipt') {
                $fa['receipt_type']   = $receipt_type;
            }
            
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');
            
            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();
            $receipt_amount     = $amount;
            $invoice_status_due = '';
            $count = 0;
            
            // TO Change for Installment
            foreach($invoiceHistId AS $invoiceHistId){
                $invoiceRec = $fn->getRecordRowByID('installment', 'installment_id', $invoiceHistId);
                $invoice_amount = $invoiceRec['amount'];
                
                if ($invoiceRec['invoice_paid_status'] == 'Paid' || 
                $receipt_amount <= 0){
                    continue;
                }
                
                $SQLPaid = "
                SELECT SUM(amount) AS prev_sum
                FROM invoice_receipt_history
                WHERE installment_id = '{$invoiceHistId}'
                ";
                $resultPaid = $db->sql_query($SQLPaid);
                $rowPaid    = $db->sql_fetchrow($resultPaid);
                
                $invoice_amount = $invoice_amount - $rowPaid['prev_sum']; 
    
                $faInv = array();
                $recpInvAmount = 0;
                if ($invoice_amount <= $receipt_amount){
                    $recpInvAmount = $invoice_amount;
                    $faInv['invoice_paid_status'] = 'Paid';
                } else if ($invoice_amount > $receipt_amount){
                    $recpInvAmount = $receipt_amount;
                    $faInv['invoice_paid_status'] = 'Partial Payment';
                }
                
                $receipt_amount = $receipt_amount - $recpInvAmount;
                $fn->saveRecord($faInv, 'installment', 'installment_id', $invoiceHistId);
                
                //Inserting receipt id in to history table ( one invoice can have multiple receipts)
                $fa = array();
                $fa['receipt_id']    = $receipt_id;
                $fa['invoice_id']    = $invoice_id;
                $fa['installment_id']= $invoiceHistId;
                $fa['amount']        = $recpInvAmount;
                $fa['invoice_date'] =  $receipt_date;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by']     = $fn->getSessionParam('userName');
                $histId = $fn->addRecord($fa, 'invoice_receipt_history');
             }
    
             
            $SQL = "
            SELECT i.*
            FROM installment i
            WHERE i.invoice_id = {$invoice_id} 
            AND (i.invoice_paid_status != 'Paid' 
                || i.invoice_paid_status = ''
                || i.invoice_paid_status IS NULL
                )
            ";
            $result = $db->sql_query($SQL);
            $numRows  = $db->sql_numrows($result);
            $faInv = array();
            if ($numRows > 0){
                $faInv['status'] = 'Partial Payment';
                $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            }
            else{
                $faInv['status'] = 'Paid';
                $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            }
            //To create PDF related to receipt and save it in media
            $this->getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present);
        }
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCalculateAmountPayable() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $discount_amount = $fn->getReqParam('discount_amount');
        $form_name       = $fn->getReqParam('formName');
        
        if ($form_name == 'receiptForm') {
            $invoiceCodes = isset($_SESSION['selectedInvoiceIds']) ? $_SESSION['selectedInvoiceIds'] : 0;
        } else if ($form_name == 'receiptFormForParent') {
            $invoiceCodes = isset($_SESSION['selectedInvoicesForSummary']) ? $_SESSION['selectedInvoicesForSummary'] : 0;
        }
        
        if ($discount_amount == '' || $invoiceCodes == 0) {
            return;
        }
        
        $invoiceCodesArr = join(',', $invoiceCodes);
        $sessionExplode  = explode(',', $invoiceCodesArr);
        
        $counter = 1;
        $count = count($sessionExplode);

        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }
        
        /* Finding of total amount for selected invoices */
        $SQLInv = "
        SELECT SUM(invoice_amount) AS total_invoice_amount
        FROM invoice i
        WHERE invoice_code IN ({$invoice_code})
          AND add_registration_fee IS NULL
        ";
        $resultInv  = $db->sql_query($SQLInv);
        $rowInv     = $db->sql_fetchrow($resultInv);
        
        $total_discount_amount = $discount_amount * $count;        
        $amount_payable = $rowInv['total_invoice_amount'] - $total_discount_amount;
                
        return $amount_payable;
    }

    /**
     *
     */
    function getPopulateDiscountAmount() {
        $db = Zend_Registry::get('db');

        $selectInvoiceIds = join(',', $_SESSION['selectedInvoiceIds']);
        $sessionExplode = explode(',', $selectInvoiceIds);
        
        $counter = 1;
        $count = count($sessionExplode);
        
        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }

        $SQLDiscAmt = "
        SELECT MAX(i.discount_amount) AS max_discount_amount
        FROM invoice i
        WHERE i.invoice_code IN ({$invoice_code})
        ";
        $resultDiscAmt = $db->sql_query($SQLDiscAmt);
        $rowDiscAmt    = $db->sql_fetchrow($resultDiscAmt);
        
        return $rowDiscAmt['max_discount_amount'];
    }
}
