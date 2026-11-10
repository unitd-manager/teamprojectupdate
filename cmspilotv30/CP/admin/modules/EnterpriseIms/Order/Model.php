<?
class CP_Admin_Modules_EnterpriseIms_Order_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

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

        $searchVar->sqlSearchVar[] = "o.module = 'enterpriseIms_course'";
        
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
    function getGenerateReceiptFormSubmit() {
        /********************************* PROCESS ************************************/
        /*
        ACTION: CREATION OF RECEIPT RECORD FOR THE INVOICES CHOSEN
        STEP 1: UPDATION OF DISCOUNT AMOUNT IN COURSE CONTACT TABLE
        STEP 2: UPDATION OF DISCOUNT AMOUNT FOR ALL THE INVOICES (FOR UNPAID INVOICES)
        STEP 3: UPDATION OF DISCOUNT AMOUNT FOR THE SELECTED INVOICES ONLY
        STEP 4: SEPARATION OF INVOICE CODES
        STEP 5: FINDING TOTAL DISCOUNT FOR THE SELECTED INVOICES
        STEP 6: SELECTION OF NEXT RECEIPT CODE FROM SETTING TABLE
        STEP 7: CREATION OF RECEIPT RECORD
        STEP 8: UPDATION OF RECEIPT CODE IN SETTING TABLE
        STEP 9: UPDATING STATUS FOR THE INVOICE IN INVOICE TABLE
        STEP 10: CREATION OF RECORD IN INVOICE HISTORY TABLE FOR THE RECEIPT (ONE INVOICE CAN HAVE MULTIPLE RECEIPTS)
        STEP 11: UPDATION OF TOTAL DISCOUNT AMOUNT FOR THE RECEIPT
        */
        /******************************* END PROCESS **********************************/

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
        
        /********************************** STEP 1 **************************************/
        if ($discount_amount > 0) {
            $courseContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $order_id);
            
            if ($courseContactRec['discount'] != $discount_amount) {
                $faCc = array();
                $faCc['discount']          = $discount_amount;
                $faCc['modification_date'] = date("Y-m-d H:i:s");
        
                $whereCondition = "WHERE course_contact_id = {$courseContactRec['course_contact_id']}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($faCc, 'course_contact', $whereCondition);
                $db->sql_query($SQL);
            }
        }
        /********************************** STEP 1 ENDS HERE ****************************/

        if ($discount_for_all_months == 1) {
            /********************************** STEP 2 **************************************/
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
            /********************************** STEP 2 ENDS HERE ****************************/
        } else {
            /********************************** STEP 3 **************************************/
            foreach($invoiceCodes AS $invoice_code){
                $invoiceRec = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}' AND add_registration_fee IS NULL");
                
                $faDiscAmt = array();
                $faDiscAmt['discount_amount'] = $discount_amount;                
                $fn->saveRecord($faDiscAmt, 'invoice', 'invoice_id', $invoiceRec['invoice_id']);
            }
            /********************************** STEP 3 ENDS HERE ****************************/
        }

        /********************************** STEP 4 **************************************/
        $invoice_code = $this->getSeparationOfInvoiceCodes($invoiceCodes);
        /********************************** STEP 4 ENDS HERE ****************************/
        
        /********************************** STEP 5 **************************************/
        $sqlTotalDisc = "
        SELECT SUM(discount_amount) as total_discount_amount
        FROM invoice
        WHERE invoice_code IN ($invoice_code)
        ";
        $resultTotalDisc       = $db->sql_query($sqlTotalDisc);
        $rowTotalDisc          = $db->sql_fetchrow($resultTotalDisc);
        $total_discount_amount = $rowTotalDisc['total_discount_amount'];
        /********************************** STEP 5 ENDS HERE ****************************/
        
        /********************************** STEP 6 **************************************/
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
        /********************************** STEP 6 ENDS HERE ****************************/
        
        /********************************** STEP 7 **************************************/
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
        /********************************** STEP 7 ENDS HERE ****************************/
        
        /********************************** STEP 8 **************************************/
        if ($rowSiteId['site_id']) {
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode' AND site_id = '{$site_id}'";
        } else {
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
        }
        $resultUpdate = $db->sql_query($SQLUpdate);
        /********************************** STEP 8 ENDS HERE ****************************/

        $total_discount_amount = 0;
        foreach($invoiceCodes AS $invoice_code){
            $invoiceRec      = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}'");
            $invoice_amount  = $invoiceRec['invoice_amount'];
            $invoice_id      = $invoiceRec['invoice_id'];
            $discount_amount = $invoiceRec['discount_amount'];
            
            //$invoice_amount  = $invoice_amount - $discount_amount;
            
            //if ($invoiceRec['status'] == 'Paid' || $receipt_amount <= 0){
            if ($invoiceRec['status'] == 'Paid') {
                continue;
            }
            
            /********************************** STEP 9 **************************************/
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
            /********************************** STEP 9 ENDS HERE ****************************/
            
            /********************************** STEP 10 **************************************/
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $recpInvAmount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
            /********************************** STEP 10 ENDS HERE ****************************/

            $total_discount_amount += $discount_amount;
        }

        /********************************** STEP 11 **************************************/
        $faDisc = array();
        $faDisc['discount_amount'] = $total_discount_amount;                
        $fn->saveRecord($faDisc, 'receipt', 'receipt_id', $receipt_id);
        /********************************** STEP 11 ENDS HERE ****************************/
            
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
              AND oi.module = 'enterpriseIms_reg_fee'
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
                AND module      = 'enterpriseIms_subsidy'
                AND contact_id  = $contact_id
                ");
                $orderItemRecSubsidy   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemSubsidy);
                
                if (is_array($orderItemRecSubsidy)){
                    $subsidyAmount = $orderItemRecSubsidy['unit_price'];
                }
                
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

                $fa = array();
                $fa['order_id']             = $order_id;
                $fa['contact_id']           = $contact_id;
                $fa['invoice_code']         = $nextInvoiceCode;
                $fa['invoice_month']        = $month;
                $fa['invoice_date']         = $orderRec['year_of_enrollment'] . '-' . $month . '-' . $rowDate['value'];
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

            $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

            $fa = array();
            $fa['order_id']         = $order_id;
            $fa['contact_id']       = $contact_id;
            $fa['invoice_code']     = $nextInvoiceCode;
            $fa['invoice_month']    = $month;
            $fa['invoice_date']     = $orderRec['year_of_enrollment'] . '-' . $month . '-' . $rowDate['value'];
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
     * Invoice PDF in Fpdf for Enterprise edition of PMS
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
            $pdf->Image('images/logo-print.jpg', 153, 5, 45);

            /* Institute company address */
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY(60,1);
            $pdf->Cell(20, 20, $cpCfg['printCompanyName']);
            $pdf->SetXY(60,5);
            $pdf->Cell(24, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
            $pdf->SetXY(60,10);
            $pdf->Cell(28, 20, $cpCfg['printTelephoneAndFax']);
            $pdf->SetXY(60,15);
            $pdf->Cell(20, 20, $cpCfg['printEmailAndWebsite']);
            $pdf->SetXY(60,20);
            $pdf->Cell(20, 20, $cpCfg['printRegistrationNo']);
            $pdf->Ln(10);

            $pdf->SetFont('Arial','B',12);
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
            #$date = $fn->getCPDate($row['invoice_date'], 'dS F Y'); 23rd January 2013 format
            $date = $fn->getCPDate($row['invoice_date'], 'd F Y'); //23 January 2013 format
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

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 20, $cpCfg['printTermsHeaderForInvoice']);
        $pdf->Ln(12);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 5, $cpCfg['printTermsText1ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(130, 5, $cpCfg['printTermsText2ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(130, 5, $cpCfg['printTermsText3ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(72, 5, $cpCfg['printTermsText4ForInvoice']);
        $pdf->SetX(72);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(130, 5, $cpCfg['printTermsText5ForInvoice']);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 5, $cpCfg['printTermsText6ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(130, 5, $cpCfg['printTermsText7ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(130, 5, $cpCfg['printTermsText8ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(130, 5, $cpCfg['printTermsText9ForInvoice']);
        $pdf->Ln(4);

        $pdf->Output();
    }

    /**
     * Invoice PDF in Fpdf for more than one student for a parent of IPMS
     */
    function  getPrintSelectedInvoices(){
        /********************************* PROCESS ************************************/
        /*
        ACTION: PRINTING OF SELECTED INVOICES IN PAYMENT SUMMARY
        STEP 1: CHECKING WHETHER INVOICE IS CHECKED OR NOT
        STEP 2: SEPARATION OF INVOICE CODE
        STEP 3: FINDING WHETHER ONE OR MORE INVOICE SENT ALREADY TO THE PARENT
        STEP 4: FINDING WHETHER ONE OR MORE PARENTS ARE CHOSEN
        STEP 5: PRINTING OF INVOICE DATA
        */
        /******************************* END PROCESS **********************************/

        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

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
        
        /********************************** STEP 1 **************************************/
        if ($count == 0) {
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please select the invoices");
			$pdf->Output();
			return;
        }
        /********************************** STEP 1 ENDS HERE ****************************/
        
        /********************************** STEP 2 **************************************/
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
        /********************************** STEP 2 ENDS HERE ****************************/

        /********************************** STEP 3 **************************************/
        $SQL = "
        SELECT invoice_id
        FROM invoice
        WHERE invoice_code IN ($invoice_code)
          AND invoice_sent_out = 1
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        if ($numRows > 0) {
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Invoice sent already for the chosen invoice. You can send only send statement of account.");
			$pdf->Output();
			return;
        } else {
            $fa = array();
            $fa['invoice_sent_out']  = 1;
            $fa['modification_date'] = date("Y-m-d H:i:s");;
    
            $whereCondition = "WHERE invoice_code IN ($invoice_code)";
            $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "invoice", $whereCondition);
            $result         = $db->sql_query($SQL);
        }
        /********************************** STEP 3 ENDS HERE ****************************/

        /********************************** STEP 4 **************************************/
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
        /********************************** STEP 4 ENDS HERE ****************************/
        
        /********************************** STEP 5 **************************************/
        $this->getPrintGroupInvoiceInFpdf($invoice_code, $pdf);
        /********************************** STEP 5 ENDS HERE ****************************/
    }

    /**
     * Invoice PDF in Fpdf for more than one student for a parent of IPMS
     */
    function getPrintGroupInvoiceInFpdf($invoice_code, $pdf, $allDueInvoiceForAMonth = "") {
        /********************************* PROCESS ************************************/
        /*
        ACTION: EXTENSION OF THE FUNCTION getPrintSelectedInvoices
        STEP 1: SQL FOR FINDING THE SELECTED INVOICES
        STEP 2: PRINTING OF COMPANY LOGO
        STEP 3: PRINTING OF COMPANY ADDRESS
        STEP 4: PRINTING OF PARENT NAME
        STEP 5: PRINTING OF INVOICE DATE AND ADDRESS OF PARENT
        STEP 6: PRINTING OF INVOICE HEADER
        STEP 7: PRINTING OF INVOICE DATA
        STEP 8: PRINTING OF FOOTER ITEMS
        */
        /******************************* END PROCESS **********************************/

        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $year = $fn->getReqParam('year');

        /********************************** STEP 1 **************************************/
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
        /********************************** STEP 1 ENDS HERE ****************************/
        
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
                /********************************** STEP 2 **************************************/
                $pdf->Image('images/logo-print.jpg', 157, 5, 45);
                /********************************** STEP 2 ENDS HERE ****************************/

                /********************************** STEP 3 **************************************/
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetXY(60,1);
                $pdf->Cell(20, 20, $cpCfg['printCompanyName']);
                $pdf->SetXY(60,5);
                $pdf->Cell(24, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
                $pdf->SetXY(60,10);
                $pdf->Cell(28, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->SetXY(60,15);
                $pdf->Cell(20, 20, $cpCfg['printEmailAndWebsite']);
                $pdf->SetXY(60,20);
                $pdf->Cell(20, 20, $cpCfg['printRegistrationNo']);
                $pdf->Ln(10);
                /********************************** STEP 3 ENDS HERE ****************************/
    
                $pdf->SetFont('Arial','B',10);
                $pdf->SetXY(100, 35);
                $pdf->Cell(21, 20, "OFFICIAL INVOICE", 0, 0, 'C');                
                $pdf->Ln(10);
    
                /********************************** STEP 4 **************************************/
                $pdf->SetX(10);
                $parent_full_name = $parentRec['first_name'] . ' ' . $parentRec['last_name'];
                $parent_name = 'To: ' . $parent_full_name;
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $parent_name);
    
                /********************************** STEP 4 ENDS HERE ****************************/
    
                /********************************** STEP 5 **************************************/
                $pdf->SetX(157);
                #$date = $fn->getCPDate($today, 'dS F Y'); 23rd January 2013 format
                $date = $fn->getCPDate($today, 'd F Y'); //23 January 2013 format
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
                /********************************** STEP 5 ENDS HERE ****************************/
    
                /********************************** STEP 6 **************************************/
                $pdf->SetX(10);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(190, 8, 'Details of Payment for the year ' . $year, 1);
                $pdf->Ln();
                
                /* List of invoice items header */
                $pdf->SetFont('Arial','B',10);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(25, 8, "Invoice No", 1, 0, 'L', 1);
                $pdf->Cell(30, 8, "Invoice Month", 1, 0, 'L', 1);
                $pdf->Cell(100, 8, "Student Name", 1, 0, 'L', 1);
                $pdf->Cell(35, 8, "Sub Total (SGD)", 1, 0, 'R', 1);
                $pdf->Ln();
                /********************************** STEP 6 ENDS HERE ****************************/
            }
            
            /********************************** STEP 7 **************************************/
            $student_name = $contactRec['first_name'] . ' ' . $contactRec['last_name'];
            $invoice_amt_after_discount = $row['invoice_amount'] - $row['discount_amount'];
            $invoice_amount = number_format($invoice_amt_after_discount, 2);
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
            /********************************** STEP 7 ENDS HERE ****************************/

            $count++;
        }

        /********************************** STEP 8 **************************************/
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

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 20, $cpCfg['printTermsHeaderForInvoice']);
        $pdf->Ln(12);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 5, $cpCfg['printTermsText1ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(130, 5, $cpCfg['printTermsText2ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(130, 5, $cpCfg['printTermsText3ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(72, 5, $cpCfg['printTermsText4ForInvoice']);
        $pdf->SetX(72);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(130, 5, $cpCfg['printTermsText5ForInvoice']);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 5, $cpCfg['printTermsText6ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(130, 5, $cpCfg['printTermsText7ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(130, 5, $cpCfg['printTermsText8ForInvoice']);
        $pdf->Ln();
        $pdf->Cell(130, 5, $cpCfg['printTermsText9ForInvoice']);
        $pdf->Ln(4);
        /********************************** STEP 8 ENDS HERE ****************************/

        if($allDueInvoiceForAMonth == ''){
            $pdf->Output();
        }
    }

    /**
     * Receipt PDF in Fpdf for more than one student for a parent of IPMS
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

        $receiptId = isset($_SESSION['receiptIdForSummary']) ? $_SESSION['receiptIdForSummary'] : 0;
        $receipt_id = $this->getSeparationOfInvoiceCodes($receiptId);

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
        
            $mode_of_payment      = $row['mode_of_payment'];
            $cheque_no            = $row['cheque_no'];
            $cheque_date          = $row['cheque_date'];
            $bank                 = $row['bank_name'];
            $remarks              = $row['remarks'];

            $orderRec           = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $orderRec['order_id']);
            $contactRec         = $fn->getRecordRowByID('contact', 'contact_id', $row['contact_id']);
            $parContRec         = $fn->getRecordRowByID('parent_contact', 'contact_id', $row['contact_id']);
            $parentRec          = $fn->getRecordRowByID('parent', 'parent_id', $parContRec['parent_id']);
            $countryRec         = $fn->getRecordByCondition('geo_country', "country_code = '{$parentRec['address_country']}'");

            if ($count == 1) {
                /* Logo of the institution */
                $pdf->Image('images/logo-print.jpg', 157, 5, 45);
    
                /* Institute company address */
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetXY(60,1);
                $pdf->Cell(20, 20, $cpCfg['printCompanyName']);
                $pdf->SetXY(60,5);
                $pdf->Cell(24, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
                $pdf->SetXY(60,10);
                $pdf->Cell(28, 20, $cpCfg['printTelephoneAndFax']);
                $pdf->SetXY(60,15);
                $pdf->Cell(20, 20, $cpCfg['printEmailAndWebsite']);
                $pdf->SetXY(60,20);
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
                #$date = $fn->getCPDate($today, 'dS F Y'); //23rd January 2013 format
                $date = $fn->getCPDate($today, 'd F Y'); //23 January 2013 format
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
        $pdf->Cell(28, 8, 'Mode of Payment : ');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40, 8,  $mode_of_payment);
        $pdf->Ln(5);
        
        /* Cheque Details */
        if ($mode_of_payment == 'Cheque') {
            $pdf->SetFont('Arial','B',8);
            $pdf->Cell(28, 8, 'Cheque No : ');
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(40, 8,  $cheque_no);
            $pdf->Ln(5);
            
            $cheque_date_format = $fn->getCPDate($cheque_date, 'd F Y'); //23 January 2013 format
    
            $pdf->SetFont('Arial','B',8);
            $pdf->Cell(28, 8, 'Cheque Date : ');
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(40, 8,  $cheque_date_format);
            $pdf->Ln(5);

            $pdf->SetFont('Arial','B',8);
            $pdf->Cell(28, 8, 'Bank Name : ');
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(40, 8,  $bank);
            $pdf->Ln(5);
        }

        $pdf->Ln(2);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, $remarks);
        $pdf->Ln(4);

        $pdf->Output();        
    }

    /**
     * Receipt PDF for Enterprise edition of PMS
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
            $cheque_date          = $row['cheque_date'];
            $bank                 = $row['bank_name'];
            $issued_by            = $row['issued_by'];
            $coi_no               = $row['coi_no'];
            $approval_code        = $row['approval_code'];
            $receipt_code         = $row['receipt_code'];
            $discount_amount      = $row['discount_amount'];
            $total_receipt_amount = $row['amount'];
            $remarks              = $row['remarks'];
            
            $amount_paid          = $total_receipt_amount;

            $orderRec           = $fn->getRecordRowByID('order', 'order_id', $row['order_id']);
            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $orderRec['order_id']);
            $contactRec         = $fn->getRecordRowByID('contact', 'contact_id', $courseContactRec['contact_id']);
            $courseRec          = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
            $parContRec         = $fn->getRecordRowByID('parent_contact', 'contact_id', $contactRec['contact_id']);
            $parentRec          = $fn->getRecordRowByID('parent', 'parent_id', $parContRec['parent_id']);
            $countryRec         = $fn->getRecordByCondition('geo_country', "country_code = '{$parentRec['address_country']}'");
                        
            /* Logo of the institution */
            $pdf->Image('images/logo-print.jpg', 157, 5, 45);

            /* Institute company address */
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY(60,1);
            $pdf->Cell(20, 20, $cpCfg['printCompanyName']);
            $pdf->SetXY(60,5);
            $pdf->Cell(24, 20, $cpCfg['printAddressFlatAndStreet'] . ' ' . $cpCfg['printAddressCountryAndCode']);
            $pdf->SetXY(60,10);
            $pdf->Cell(28, 20, $cpCfg['printTelephoneAndFax']);
            $pdf->SetXY(60,15);
            $pdf->Cell(20, 20, $cpCfg['printEmailAndWebsite']);
            $pdf->SetXY(60,20);
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
            #$date = $fn->getCPDate($row['date'], 'dS F Y'); 23rd January 2013 format
            $date = $fn->getCPDate($row['date'], 'd F Y'); //23 January 2013 format
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

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(28, 8, 'Mode of Payment : ');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40, 8,  $mode_of_payment);
        $pdf->Ln(5);
        
        /* Cheque Details */
        if ($mode_of_payment == 'Cheque') {
            $pdf->SetFont('Arial','B',8);
            $pdf->Cell(28, 8, 'Cheque No : ');
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(40, 8,  $cheque_no);
            $pdf->Ln(5);
            
            $cheque_date_format = $fn->getCPDate($cheque_date, 'd F Y'); //23 January 2013 format
    
            $pdf->SetFont('Arial','B',8);
            $pdf->Cell(28, 8, 'Cheque Date : ');
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(40, 8,  $cheque_date_format);
            $pdf->Ln(5);

            $pdf->SetFont('Arial','B',8);
            $pdf->Cell(28, 8, 'Bank Name : ');
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(40, 8,  $bank);
            $pdf->Ln(5);
        }

        $pdf->Ln(2);
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(67, 5, 'Remarks:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',7);
        $pdf->Cell(130, 5, $remarks);
        $pdf->Ln(4);
       
        /*
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
        */

        $pdf->Output();
    }

    /**
     *
     */
    function getPopulateReceiptAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_code = $fn->getReqParam('invoice_code');
        $checkedVal = $fn->getReqParam('checkedVal');

        //Used in receipt Form
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
    function getCancelInvoice() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_code = $fn->getReqParam('invoice_code');
        
        //Cancel the Invoice
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
            WHERE invoice_code = '{$invoice_code}'
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

        //Cancel the receipt
        $receipt_code = $fn->getReqParam('receipt_code');

        $irh = $fn->getRecordByCondition('receipt', "receipt_code = '{$receipt_code}'");
        
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
        WHERE receipt_code = '{$receipt_code}'
        ";
        $resultRec = $db->sql_query($sqlRec);
        
        return;
    }
    /**
     *
     */
    function getCalculateAmountPayable() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        #AmountInReceiptForm 
        //Used in Receipt form to calculate the actual amount deducting discount if any
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
