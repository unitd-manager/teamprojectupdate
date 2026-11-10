<?
class CP_Admin_Modules_Hms_Order_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT o.*
              ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
              ,p.bill_type As billtype
        FROM `order` o
        LEFT JOIN (patient_information p) ON (p.patient_information_id = o.patient_information_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'o';

        $order_id     = $fn->getReqParam('order_id');
        $order_type   = $fn->getReqParam('order_type');
        $billType   = $fn->getReqParam('bill_type');

        if ($order_id != "") {
            $searchVar->sqlSearchVar[] = "o.order_id = '{$order_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "o.order_id = '{$tv['record_id']}'";
        } else {

            if ($order_type != '') {
                $searchVar->sqlSearchVar[] = "o.order_type = '{$order_type}'";
            }

            if ($billType != "") {
                $searchVar->sqlSearchVar[] = "p.bill_type = '{$billType}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    o.order_id  LIKE '%{$tv['keyword']}%'
                 OR o.nric LIKE '%{$tv['keyword']}%'
                 OR o.first_name LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "o.creation_date DESC,o.order_id DESC";
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
        $fa = $fn->addToFieldsArray($fa, 'invoice_terms');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'organization_id');
        $fa = $fn->addToFieldsArray($fa, 'ok_to_ship');

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
        $fa = $fn->addToFieldsArray($fa, 'shipment_status');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_country');
        //$fa = $fn->addToFieldsArray($fa, 'delivery_to_text');

        return $fa;
    }


    /**
     *
     */
    function getPreviousInvoiceAmount($order_id) {
        $db = Zend_Registry::get('db');

        $sqlInvoice = "
        SELECT SUM(invoice_amount) AS total_invoice_amount FROM invoice
        WHERE order_id = {$order_id}
        ";
        $resultInvoice = $db->sql_query($sqlInvoice);
        $rowInvoice = $db->sql_fetchrow($resultInvoice);

        return $rowInvoice['total_invoice_amount'];
    }

    /**
     *
     */
    function getTotalInvoiceAmount($order_id) {
        $db = Zend_Registry::get('db');

        $total_amount = 0;

        $sqlSum = "
        SELECT SUM(unit_price * qty) AS total_item_amount FROM order_item
        WHERE order_id = {$order_id}
        ";
        $resultSum = $db->sql_query($sqlSum);
        $rowSum = $db->sql_fetchrow($resultSum);

        return $rowSum['total_item_amount'];
    }

    /**
     *
     */
    function getTradinginOrderEcommerceOrderItemLinkSQL($id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_id = $fn->getReqParam('invoice_id');
        $extraFields = "";

        if ($invoice_id != "") {
            $whereSQL = " AND i.invoice_id = '{$invoice_id}'";
        } else {
            $whereSQL = "";
        }

        return "
        SELECT b.order_item_id
              ,b.item_title
              ,b.cost_price
              ,b.qty
              ,round(b.qty * b.cost_price,2)
              ,(SELECT CASE WHEN b.discount_type = '%' then
                  (SELECT round(((oi.cost_price * oi.discount_percentage )/100)* oi.qty,2)
                    FROM order_item oi
                    WHERE oi.order_item_id = b.order_item_id
                      AND oi.discount_type = '%'
                   )
                    WHEN b.discount_type = 'Value' then
                   (SELECT round(oi.discount_percentage * oi.qty,2)
                    FROM order_item oi
                    WHERE oi.order_item_id = b.order_item_id
                      AND oi.discount_type = 'Value'
                   )
                    ELSE 0
               END)
               as discount_percentage_amount_sum
              ,(SELECT CASE WHEN b.discount_type = '%' then
                  (SELECT round((oi.cost_price - ((oi.cost_price * oi.discount_percentage )/100)) * oi.qty,2)
                    FROM order_item oi
                    WHERE oi.order_item_id = b.order_item_id
                      AND oi.discount_type = '%'
                   )
                    WHEN b.discount_type = 'Value' then
                   (SELECT round((oi.cost_price - oi.discount_percentage) * oi.qty,2)
                    FROM order_item oi
                    WHERE oi.order_item_id = b.order_item_id
                      AND oi.discount_type = 'Value'
                   )
                    ELSE round(b.qty * b.cost_price,2)
               END)
               as total_amount_sum
        FROM `order_item` b
        LEFT JOIN (invoice i) ON (b.invoice_id = i.invoice_id)
        WHERE b.order_id = {$id}
              {$whereSQL}
        ";

    }

    /**
     *
     */
    function getTradinginOrderTradingsgInvoiceLinkSQL($id) {

        return "
        SELECT i.invoice_id
              ,i.invoice_code
              ,i.status
              ,DATE_FORMAT(i.invoice_date, '%d-%m-%Y') AS invoice_date
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
              ,i.invoice_amount
              ,(CONCAT_WS('', '<a href=\'index.php?module=tradingin_order&_spAction=printInvoiceRecord&id=', i.invoice_id, '\' target=\'_blank\'>Print Invoice</a>'))
              ,(CONCAT_WS('', '<a href=\'index.php?module=tradingin_order&_spAction=cancelInvoice&id=', i.invoice_id, '\' target=\'_self\'>Cancel Invoice</a>'))
        FROM `invoice` i
        LEFT JOIN (staff s) ON (i.staff_id = s.staff_id)
        WHERE i.order_id = {$id}
        ";

    }

    /**
     *
     */
    function getTradinginOrderTradingsgReceiptLinkSQL($id) {

        return "
        SELECT r.receipt_id
              ,r.receipt_code
              ,DATE_FORMAT(r.date, '%d-%m-%Y') AS invoice_date
              ,r.mode_of_payment
              ,r.amount
        FROM `receipt` r
        WHERE r.order_id = {$id}
        ";

    }

    /**
     *
     */
    function getPopulateReceiptAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $invoice_code = $fn->getReqParam('invoice_code');
        $checkedVal = $fn->getReqParam('checkedVal');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');


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

        $appendSqlPaid = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlPaid = " AND site_id = {$cpSiteIdSession}";
        }

        $SQLPaid = "
        SELECT SUM(invoice_amount - discount) AS invoice_selected_sum
        FROM invoice
        WHERE invoice_code IN ({$invoice_code})
        {$appendSqlPaid}
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        $appendSqlPartialPayment = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlPartialPayment = " AND i.site_id = {$cpSiteIdSession}
                                         AND r.site_id = {$cpSiteIdSession}
                                       ";
        }

        $SQLPartialPayment = "
        SELECT SUM(irh.amount) AS invoice_partial_payment
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.related_invoice_id = i.invoice_id)
        LEFT JOIN receipt r ON (r.receipt_id = irh.receipt_id)
        WHERE i.invoice_code IN ({$invoice_code})
          AND r.receipt_status != 'Cancelled'
        {$appendSqlPartialPayment}
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

        if($rowPartialPayment['invoice_partial_payment'] == ''){
            $SQLPartialPayment = "
            SELECT SUM(invHist.amount) AS invoice_partial_payment
            FROM invoice_receipt_history invHist
            LEFT JOIN (invoice i) ON (invHist.related_invoice_id = i.invoice_id)
            LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
            WHERE i.invoice_code IN ({$invoice_code})
            AND r.receipt_status != 'Cancelled'
            {$appendSqlPartialPayment}
            ";
            $resultPartialPayment = $db->sql_query($SQLPartialPayment);
            $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);
        }

        if ($rowPartialPayment['invoice_partial_payment'] == 0){
            return $rowPaid['invoice_selected_sum'];
        } else {
            return $rowPaid['invoice_selected_sum'] - $rowPartialPayment['invoice_partial_payment'];
        }

    }

    /**
     *
     */
    function getPopulateInvoiceAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_item_id = $fn->getReqParam('order_item_id');
        $checkedVal = $fn->getReqParam('checkedVal');
        $qty = $fn->getReqParam('qty');

        $SQL = "
        UPDATE `order_item`
        SET qty_for_invoice = '{$qty}'
        WHERE order_item_id = {$order_item_id}
        ";
        $result = $db->sql_query($SQL);

        //if($checkedVal == 1){
            $_SESSION['selectedOrderItemIds'][] = $order_item_id;
        /*}
        else if($checkedVal == 0){
            $s = &$_SESSION['selectedOrderItemIds'];
            if(($key = array_search($order_item_id, $s)) !== false){
                unset($s[$key]);
            }
        }*/

        if(count($_SESSION['selectedOrderItemIds']) == 0){
            return 0;
        }
        $selectedOrderItemIds = join(',', $_SESSION['selectedOrderItemIds']);

        $SQLPaid = "
        SELECT SUM(qty_for_invoice * unit_price) AS invoice_selected_sum
        FROM order_item
        WHERE order_item_id IN ({$selectedOrderItemIds})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        return $rowPaid['invoice_selected_sum'];

    }

    /**
     *
     */
    function getCancelInvoice() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $invoice_code = $fn->getReqParam('invoice_code');
        $invoice_id = $fn->getReqParam('invoice_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        /* Finding of receipt record */
        $SQLIrh = "
        SELECT irh.*
        FROM invoice_receipt_history irh
        LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
        WHERE irh.invoice_id = {$invoice_id}
          AND r.receipt_status =  'Paid'
        ";
        $resultIrh = $db->sql_query($SQLIrh);
        $numRowsIrh = $db->sql_numrows($resultIrh);
        $rowIrh = $db->sql_fetchrow($resultIrh);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = " AND site_id = {$cpSiteIdSession}";
        }

        if ($numRowsIrh == 0) {
            /* Updating of invoice record */
            $sqlInv = "
            UPDATE invoice
            SET status = 'Cancelled'
            WHERE invoice_code = '{$invoice_code}'
            {$appendSql}            
            ";
            $resultInv = $db->sql_query($sqlInv);
        } else {
            return 'Cannot cancel';
        }

        $invoiceRec = $fn->getRecordRowByID('invoice', 'invoice_id', $invoice_id);
        $orderRec = $fn->getRecordRowByID('order', 'order_id', $invoiceRec['order_id']);

        $SQL ="
        SELECT *
        FROM invoice
        WHERE order_id = {$orderRec['order_id']}
          AND status != 'Cancelled'
          {$appendSql}
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);
        if($numRows > 0){
          $SQLPVUpdate = "UPDATE patient_visit SET status = 'Invoiced' WHERE patient_visit_id = {$orderRec['patient_visit_id']} {$appendSql}";
          $resultPVUpdate = $db->sql_query($SQLPVUpdate);
        } else{
          $SQLPVUpdate = "UPDATE patient_visit SET status = 'Order Raised' WHERE patient_visit_id = {$orderRec['patient_visit_id']} {$appendSql}";
          $resultPVUpdate = $db->sql_query($SQLPVUpdate);
        }
    }
    /**
     *
     */
    function getCancelReceipt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $receipt_code    = $fn->getReqParam('receipt_code');
        $order_id        = $fn->getReqParam('order_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        /* Updating of invoice record */
        //$row = $fn->getRecordRowByID('receipt', 'receipt_code', '$receipt_code');

        //to update the status of invoice to Due for related receipts.
        $appendSqlRec = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlRec = " AND site_id = {$cpSiteIdSession}";
        }

        $sqlRec = "
        UPDATE invoice
        SET status = 'Due'
        WHERE invoice_id IN
        (SELECT related_invoice_id
         FROM invoice_receipt_history
         WHERE receipt_id = (SELECT receipt_id FROM receipt
            WHERE receipt_code = '{$receipt_code}' {$appendSqlRec})
         )
        ";
        $resultRec = $db->sql_query($sqlRec);

        $sqlRec = "
        UPDATE receipt
        SET receipt_status = 'Cancelled'
        WHERE receipt_code = '{$receipt_code}'
        {$appendSqlRec}
        ";
        $resultRec = $db->sql_query($sqlRec);

        //TO MAKE THE STATUS OF ORDER 'DUE' WHENEVER A RECEIPT IS CANCELLED
        //$row = $fn->getRecordRowByID('invoice_receipt_history', 'acc_category_id', $id);

        $SQLUpdate = "
         UPDATE `order` SET order_status =
         CASE WHEN record_type = 'POS' THEN 'Cancelled' ELSE 'Due' END
         WHERE order_id = {$order_id}
         {$appendSqlRec}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        $SQL ="
        SELECT *
        FROM receipt
        WHERE order_id = {$order_id}
          AND receipt_status = 'Paid'
          {$appendSqlRec}
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);
        if($numRows > 0){
          $SQLPVUpdate = "UPDATE patient_visit SET status = 'Partial Receipt' WHERE patient_visit_id = {$orderRec['patient_visit_id']} {$appendSqlRec}";
          $resultPVUpdate = $db->sql_query($SQLPVUpdate);
        } else{
          $SQLPVUpdate = "UPDATE patient_visit SET status = 'Invoiced' WHERE patient_visit_id = {$orderRec['patient_visit_id']} {$appendSqlRec}";
          $resultPVUpdate = $db->sql_query($SQLPVUpdate);
        }
    }

    /**
     *
     */
    function getGenerateSalesReturnFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getGenerateSalesReturnFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $invoiceRowItem       = $fn->getPostParam('invoiceRowItem', array());
        $invoiceItemIds       = $fn->getPostParam('invoiceItemId', array());
        $sales_return_amount  = $fn->getPostParam('sales_return_amount');
        $sales_return_date    = $fn->getPostParam('sales_return_date');
        $notes              = $fn->getPostParam('notes');
        $invoice_id           = $fn->getReqParam('invoice_id');
        $order_id           = $fn->getReqParam('order_id');
        $order_id           = $fn->getReqParam('order_id');

        $count = count($invoiceItemIds);
        $recCount = 0;
        foreach ($invoiceItemIds as $key=>$value){
            $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $value);
            $pfx  = $value . '_' ;
            $qty_return  = $fn->getPostParam("{$pfx}qty_return");

            $sqlInvoiceItem = "
            SELECT ii.*
                  ,p.carton_no
                  ,o.record_type
                  ,o.order_id
            FROM invoice_item ii
            LEFT JOIN (product p) ON (p.product_id = ii.record_id)
            LEFT JOIN (`invoice` i) ON (i.invoice_id = ii.invoice_id)
            LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
            WHERE ii.invoice_id = {$invoice_id}
            AND ii.invoice_item_id = {$value}
            ";
            $resultInvoiceItem = $db->sql_query($sqlInvoiceItem);
            $rowII = $db->sql_fetchrow($resultInvoiceItem);

            if($rowII['record_type'] == 'POS'){
                $discount_value_for_one_qty = '';
                $discountValue = 0;
                $discountPrice = 0;

                if($rowII['discount_type'] == '%'){
                    $discount_value_for_one_qty  =  $rowII['unit_price'] * ($rowII['discount_percentage']/100);
                    $discountValue = $discount_value_for_one_qty;
                    $discountPrice = $rowII['unit_price'] - $discountValue;
                }
                else if($rowII['discount_type']  == 'Value'){
                    $discount_value_for_one_qty  =  $rowII['discount_percentage'];
                    $discountValue = $discount_value_for_one_qty;
                    $discountPrice = $rowII['unit_price'] - $discountValue;
                }
                $product_Price = $discountPrice;
            }
            else{
                $product_Price = $rowII['unit_price'];
            }

            if ($invoice_id > 0){
                $fa = array();
                $fa['invoice_id']   = $invoice_id;
                $fa['order_id']   = $order_id;
                $fa['invoice_item_id'] = $invoiceItemRec['invoice_item_id'];
                $fa['qty_return']   = $qty_return;
                $fa['date']   = $sales_return_date;
                $fa['notes']   = $notes;
                $fa['price']   = $product_Price;

                $sales_return_history_id = $fn->addRecord($fa, 'sales_return_history');
                //print_r ($fa);
                $recCount++;

                /*if($rowII['record_type'] == 'POS'){
                    $fa = array();
                    $fa1['receipt_status']   = 'Cancelled';
                    $whereCondition = "WHERE order_id = {$rowII['order_id']}";
                    $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, "receipt", $whereCondition);
                    $resultOiUpdate      = $db->sql_query($sqlOiUpdate);

                    $fa2['order_status']   = 'Pending';
                    $whereCondition = "WHERE order_id = {$rowII['order_id']}";
                    $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa2, "order", $whereCondition);
                    $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
                }*/
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateSalesReturnFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoiceItemIds  = $fn->getPostParam('invoiceItemId', array());
        $order_id        = $fn->getPostParam('order_id');

		    $rowOrderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        $validate->resetErrorArray();
        //$validate->validateData('qty_return', 'Please enter the qty');

        if(!empty($invoiceItemIds)){
        } else {
            $msg = 'Please check the Products below before adding Sales Return Qty.';
            $validate->validateData('error_box', $msg);
        }
        $item_title = '';
        $total_sales_return_sum = '';

        foreach ($invoiceItemIds as $key=>$value){
            $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $value);

            $sqlQty = "
            SELECT SUM(srh.qty_return) AS qty_return
            FROM sales_return_history srh
            WHERE srh.invoice_id = {$invoiceItemRec['invoice_id']}
             AND srh.invoice_item_id = {$invoiceItemRec['invoice_item_id']}
             AND srh.status IS NULL
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);

            $pfx  = $value . '_' ;
            $qty_return  = $fn->getPostParam("{$pfx}qty_return");
            $qty_balance = $invoiceItemRec['qty'] - $rowQty['qty_return'];

            if($qty_balance < $qty_return){
                $item_title .= $invoiceItemRec['item_title'] . ', ';
                $itemTitle = rtrim($item_title,', ');
                $msg = 'Please make sure the qty entered for '. $itemTitle .' should not be greater than the actual qty.';
                $validate->validateData('error_box', $msg);
            } else if($qty_return == 0 || $qty_return == ''){
                $msg = 'Please enter the qty';
                $validate->validateData('error_box', $msg);
            }
            //to check the total sum of sales return
             $total_sales_return_sum += $invoiceItemRec['unit_price'] *  $qty_return;
        }

        //validating previous sum code starts here
        $invRec    = $fn->getRecordRowByID('invoice', 'invoice_id', $invoiceItemRec['invoice_id']);
        $invAmount = $invRec['invoice_amount'];

        $SQLPaid = "
        SELECT SUM(invHist.amount) AS prev_sum
        FROM invoice_receipt_history invHist
        LEFT JOIN (receipt rec) ON (invHist.receipt_id = rec.receipt_id)
        WHERE invHist.invoice_id =  {$invoiceItemRec['invoice_id']} and rec.receipt_status = 'Paid'
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);
	        if($total_sales_return_sum > ($invAmount - $rowPaid['prev_sum'])){
	            $msg = 'The overall amount for the selected quantity must be less than the Total Invoice Amount['. $invAmount .'] - Previous Receipt Amounts['.$rowPaid['prev_sum'].']' .'<br>If you want to proceed as such please cancel the earlier receipts.';
				if ($rowOrderRec['record_type'] != 'POS') {
		            $validate->validateData('error_box', $msg);
	        	}
		}
        //validating previous sum code ends here

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
