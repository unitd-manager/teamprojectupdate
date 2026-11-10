<?
class CP_Admin_Modules_AgileIms_Order_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
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

        $searchVar->sqlSearchVar[] = "o.module = 'agileIms_course'";

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
    function getTotalAmountFromOrderItem($order_id) {
        $db = Zend_Registry::get('db');

        $sqlOiSum = "
        SELECT SUM(unit_price) AS total_amount_payable
        FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultOiSum = $db->sql_query($sqlOiSum);
        $rowOiSum    = $db->sql_fetchrow($resultOiSum);
        
        return $rowOiSum['total_amount_payable'];
    }

    /**
     *
     */
    function getGenerateInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        
        $trainee_id_arr = $fn->getPostParam('traineeId', array());
        $order_id       = $fn->getReqParam('order_id');
        
        $total_amount_payable = 0;
        $count = count($trainee_id_arr);
        for ($i= 0; $i< $count; $i++) {
            $trainee_id = $trainee_id_arr[$i];
        
            $sqlOiSum = "
            SELECT SUM(unit_price) AS total_amount_payable FROM order_item
            WHERE order_id = {$order_id}
              AND contact_id = {$trainee_id}
            ";
            $resultOiSum = $db->sql_query($sqlOiSum);
            $rowOiSum    = $db->sql_fetchrow($resultOiSum);
            
            $total_amount_payable += $rowOiSum['total_amount_payable'];
        }
        
        $modObj = getCPModuleObj('agileIms_invoice');
        $invoice_code = $modObj->model->getFetchInvoiceCode();
        
        /* Creating a new invoice */
        $faInv = array();
        $faInv['invoice_date']     = date('Y-m-d');
        $faInv['invoice_due_date'] = date('Y-m-d', strtotime("+7 days"));
        $faInv['status']           = 'Due';
        $faInv['invoice_amount']   = $total_amount_payable;
        $faInv['created_by']       = $fn->getSessionParam('userName');
        $faInv['creation_date']    = date('Y-m-d H:i:s');
        $faInv['invoice_code']     = $invoice_code;
        $faInv['inv_currency']     = 'SGD';
        $faInv['order_id']         = $order_id;

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $faInv['company_contact_salutation'] = $orderRec['company_contact_salutation'];
        $faInv['company_contact_name']       = $orderRec['company_contact_name'];
        $faInv['cust_first_name']            = $orderRec['cust_first_name'];
        $faInv['cust_email']                 = $orderRec['cust_email'];
        $faInv['cust_address1']              = $orderRec['cust_address1'];
        $faInv['cust_address2']              = $orderRec['cust_address2'];
        $faInv['cust_address_po_code']       = $orderRec['cust_address_po_code'];
        $faInv['cust_address_country_code']  = $orderRec['cust_address_country_code'];
        $faInv['contact_reg_no']             = $orderRec['contact_reg_no'];

        $invoice_id                = $fn->addRecord($faInv, 'invoice');
        
        /* Increment of Invoice Code */
        $SQLUpdate    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        
        $selectContactIds = join(',', $trainee_id_arr);
        
        $sqlOi = "
        SELECT * FROM order_item
        WHERE order_id = {$order_id}
          AND contact_id IN ($selectContactIds)
        ";
        $resultOi = $db->sql_query($sqlOi);
        while ($rowOi = $db->sql_fetchrow($resultOi)) {
            /* Creating a invoice item records */
            $faInvItem = array();
            $faInvItem['record_id']         = $rowOi['record_id'];
            $faInvItem['qty']               = $rowOi['qty'];
            $faInvItem['unit_price']        = $rowOi['unit_price'];
            $faInvItem['item_title']        = $rowOi['item_title'];
            $faInvItem['module']            = $rowOi['module'];
            $faInvItem['contact_id']        = $rowOi['contact_id'];
            $faInvItem['subsidy_paid']      = $rowOi['subsidy_paid'];
            $faInvItem['invoice_id']        = $invoice_id;
            $faInvItem['course_start_date'] = $rowOi['course_start_date'];
            $faInvItem['course_end_date']   = $rowOi['course_end_date'];
            $faInvItem['course_code']       = $rowOi['course_code'];
            $invoice_item_id                = $fn->addRecord($faInvItem, 'invoice_item');
        
            /* Updating Invoice Id to Order Item Table */
            $faOi = array();
            $faOi['invoice_id'] = $invoice_id;
            $fn->saveRecord($faOi, 'order_item', 'order_item_id', $rowOi['order_item_id']);
        }

        return $validate->getSuccessMessageXML();
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
    function getGenerateReceiptFormValidatePvt($receipt_type) {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_amount = '';
        $invoice_prev_amount = '';
        $balance_amount = '';
        $amount        = $fn->getPostParam('amount');
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

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPopulateReceiptAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_code = $fn->getReqParam('invoice_code');
        $checkedVal   = $fn->getReqParam('checkedVal');

        if ($checkedVal == 1) {
            $_SESSION['selectedInvoiceIds'][] = $invoice_code;
        } else if($checkedVal == 0) {
            $s = &$_SESSION['selectedInvoiceIds'];
            if(($key = array_search($invoice_code, $s)) !== false){
                unset($s[$key]);
            }
        }
        
        if (count($_SESSION['selectedInvoiceIds']) == 0) {
            return 0;
        }
        
        $selectInvoiceIds = join(',', $_SESSION['selectedInvoiceIds']);
        $sessionExplode   = explode(',', $selectInvoiceIds);
        
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
        
        $SQLPartialPayment = "
        SELECT SUM(irh.amount) AS invoice_partial_payment
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
        WHERE i.invoice_code IN ({$invoice_code})
          AND r.receipt_status = 'Paid'
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);
        
        return $rowPaid['invoice_selected_sum'] - $rowPartialPayment['invoice_partial_payment'];
    }
}
