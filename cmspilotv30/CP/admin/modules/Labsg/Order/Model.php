<?
class CP_Admin_Modules_Labsg_Order_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT o.*
              ,gc.name AS country_name
        FROM `order` o
        LEFT JOIN (geo_country gc) ON (o.cust_address_country_code = gc.country_code)
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
        $bill_type    = $fn->getReqParam('bill_type');
        $order_status = $fn->getReqParam('order_status');
        $order_date_1 = $fn->getReqParam('order_date_1');
        $order_date_2 = $fn->getReqParam('order_date_2');

        if ($order_id != "") {
            $searchVar->sqlSearchVar[] = "o.order_id = '{$order_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "o.order_id = '{$tv['record_id']}'";
        } else {

            if ($order_date_1 != '' && $order_date_2 != '') {
                $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$order_date_1}' AND '{$order_date_2}'";
            }

            if ($order_status != '') {
                $searchVar->sqlSearchVar[] = "o.order_status = '{$order_status}'";
            }

            if ($order_type != '') {
                $searchVar->sqlSearchVar[] = "o.order_type = '{$order_type}'";
            }

            if ($bill_type != '') {
                $searchVar->sqlSearchVar[] = "o.bill_type = '{$bill_type}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    o.order_id     LIKE '%{$tv['keyword']}%'
                 OR o.company_name LIKE '%{$tv['keyword']}%'
                 OR o.first_name   LIKE '%{$tv['keyword']}%'
                 OR o.middle_name  LIKE '%{$tv['keyword']}%'
                 OR o.last_name    LIKE '%{$tv['keyword']}%'
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

        $invoice_code = $fn->getReqParam('invoice_code');
        $checkedVal   = $fn->getReqParam('checkedVal');

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
        SELECT SUM(invoice_amount) AS total_invoice_amount
              ,SUM(discount) AS total_discount
        FROM invoice
        WHERE invoice_code IN ({$invoice_code})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        $invoice_selected_sum = $rowPaid['total_invoice_amount'] - $rowPaid['total_discount'];

        $SQLPartialPayment = "
        SELECT SUM(irh.amount) AS invoice_partial_payment
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN receipt r ON (r.receipt_id = irh.receipt_id)
        WHERE i.invoice_code IN ({$invoice_code})
          AND r.receipt_status != 'Cancelled'
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

        if ($rowPartialPayment['invoice_partial_payment'] == 0){
            return $invoice_selected_sum;
        } else {
            return $invoice_selected_sum - $rowPartialPayment['invoice_partial_payment'];
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

        $invoice_code = $fn->getReqParam('invoice_code');
        $invoice_id   = $fn->getReqParam('invoice_id');

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

        if ($numRowsIrh == 0) {
            /* Update Patient Visit table (empty order id) - START */
            $sqlSelectOi = "
            SELECT DISTINCT patient_visit_id
            FROM order_item
            WHERE invoice_id = {$invoice_id}
            ";
            $resultSelectOi = $db->sql_query($sqlSelectOi);
            while($rowSelectOi  = $db->sql_fetchrow($resultSelectOi)){
                $sqlPatientVisit = "
                UPDATE patient_visit
                SET order_id = NULL
                WHERE patient_visit_id = {$rowSelectOi['patient_visit_id']}
                ";
                $resultPatientVisit = $db->sql_query($sqlPatientVisit);
            }
            /* Update Patient Visit table (empty order id) - STOP */

            /* Delete order item - START */
            $sqlOi = "
            DELETE FROM order_item
            WHERE invoice_id = '{$invoice_id}'
            ";
            $resultOi = $db->sql_query($sqlOi);
            /* Delete order item - STOP */

            /* Updating of invoice record */
            $sqlInv = "
            UPDATE invoice
            SET status = 'Cancelled'
            WHERE invoice_code = '{$invoice_code}'
            ";
            $resultInv = $db->sql_query($sqlInv);

            /*
            $sqlOI = "
            UPDATE order_item
            SET invoice_id = ''
            WHERE invoice_id = '{$invoice_id}'
            ";
            $resultOI = $db->sql_query($sqlOI);
            */

            //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $subSqlForPercentSum = "
            SELECT i.*
                  ,(SELECT  SUM(oi.unit_price) AS Amount
                    FROM order_item oi
                    WHERE oi.order_id = i.order_id
                    AND oi.record_type != ''
                    )AS order_amount
                  ,(SELECT SUM(inv.invoice_amount)
                    FROM invoice inv
                    WHERE inv.order_id = i.order_id AND inv.status = 'Paid'
                      ) as total_invoice_amount
                  ,(SELECT SUM(rcpt.amount)
                    FROM `receipt` rcpt
                    WHERE rcpt.order_id = i.order_id AND rcpt.receipt_status != 'Cancelled'
                    ) AS Receipt_Amount
            FROM `invoice` i
            WHERE i.invoice_id = {$invoice_id}
            ";
            $resultSubSql = $db->sql_query($subSqlForPercentSum);
            $rowSql       = $db->sql_fetchrow($resultSubSql);

            $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
            $order_amount = $rowSql['order_amount'] - $rowSql['discount'];
            $orderRec = $fn->getRecordRowByID('order', 'order_id', $rowSql['order_id']);
            $Receipt_Amount = $rowSql['Receipt_Amount'];

            //FOR AUTO UPDATING OF ORDER STATUS WHEN A RECEIPT IS PAID
            if($order_amount == $total_invoice_amount){
                $SQLUpdate = "UPDATE `order` SET order_status = 'Paid' WHERE order_id = {$order_id}";
                $resultUpdate = $db->sql_query($SQLUpdate);

                $SQLPVUpdate = "UPDATE patient_visit SET status = 'Complete' WHERE patient_visit_id = {$orderRec['patient_visit_id']}";
                $resultPVUpdate = $db->sql_query($SQLPVUpdate);
            } else if($Receipt_Amount > 0 && $Receipt_Amount < $total_invoice_amount) {
                $SQLUpdate = "UPDATE `order` SET order_status = 'Partial Receipt' WHERE order_id = {$order_id}";
                $resultUpdate = $db->sql_query($SQLUpdate);

                $SQLPVUpdate = "UPDATE patient_visit SET status = 'Partial Receipt' WHERE patient_visit_id = {$orderRec['patient_visit_id']}";
                $resultPVUpdate = $db->sql_query($SQLPVUpdate);
            }else if($total_invoice_amount == 0){
                $SQLUpdate = "UPDATE `order` SET order_status = 'Order Raised' WHERE order_id = {$order_id}";
                $resultUpdate = $db->sql_query($SQLUpdate);

                $SQLPVUpdate = "UPDATE patient_visit SET status = 'Order Raised' WHERE patient_visit_id = {$orderRec['patient_visit_id']}";
                $resultPVUpdate = $db->sql_query($SQLPVUpdate);
            }

        } else {
            return 'Cannot cancel';
        }

    }

    /**
     *
     */
    function getCancelReceiptOld() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $receipt_code = $fn->getReqParam('receipt_code');
        $order_id     = $fn->getReqParam('order_id');

        /* Updating of invoice record */
        //$row = $fn->getRecordRowByID('receipt', 'receipt_code', '$receipt_code');

        //to update the status of invoice to Due for related receipts.
        $sqlRec = "
        UPDATE invoice
        SET status = 'Due'
        WHERE invoice_id IN
        (SELECT invoice_id
         FROM invoice_receipt_history
         WHERE receipt_id = (SELECT receipt_id FROM receipt
            WHERE receipt_code = '{$receipt_code}')
         )
        ";
        $resultRec = $db->sql_query($sqlRec);

        $sqlRec = "
        UPDATE receipt
        SET receipt_status = 'Cancelled'
        WHERE receipt_code = '{$receipt_code}'
        ";
        $resultRec = $db->sql_query($sqlRec);

        //TO MAKE THE STATUS OF ORDER 'DUE' WHENEVER A RECEIPT IS CANCELLED
        $row = $fn->getRecordRowByID('invoice_receipt_history', 'acc_category_id', $id);

        $SQLUpdate = "UPDATE `order` SET order_status =
         CASE WHEN record_type = 'POS' THEN 'Cancelled' ELSE 'Due' END
         WHERE order_id = {$order_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForPercentSum = "
        SELECT o.*
              ,(SELECT  SUM(oi.unit_price) AS Amount
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                AND oi.record_type != ''
                )AS order_amount
              ,(SELECT SUM(inv.invoice_amount)
                FROM invoice inv
                WHERE inv.order_id = o.order_id AND inv.status = 'Paid'
                  ) as total_invoice_amount
              ,(SELECT SUM(rcpt.amount)
                FROM `receipt` rcpt
                WHERE rcpt.order_id = o.order_id AND rcpt.receipt_status != 'Cancelled'
                ) AS Receipt_Amount
        FROM `order`o
        WHERE o.order_id = {$order_id}
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);

        $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
        $order_amount = $rowSql['order_amount'] - $rowSql['discount'];
        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $Receipt_Amount = $rowSql['Receipt_Amount'];

        //FOR AUTO UPDATING OF ORDER STATUS WHEN A RECEIPT IS PAID
        if($order_amount == $total_invoice_amount){
            $SQLUpdate = "UPDATE `order` SET order_status = 'Paid' WHERE order_id = {$order_id}";
            $resultUpdate = $db->sql_query($SQLUpdate);

            $SQLPVUpdate = "UPDATE patient_visit SET status = 'Complete' WHERE patient_visit_id = {$orderRec['patient_visit_id']}";
            $resultPVUpdate = $db->sql_query($SQLPVUpdate);
        } else if($Receipt_Amount > 0 && $Receipt_Amount < $total_invoice_amount) {
            $SQLUpdate = "UPDATE `order` SET order_status = 'Partial Receipt' WHERE order_id = {$order_id}";
            $resultUpdate = $db->sql_query($SQLUpdate);

            $SQLPVUpdate = "UPDATE patient_visit SET status = 'Partial Receipt' WHERE patient_visit_id = {$orderRec['patient_visit_id']}";
            $resultPVUpdate = $db->sql_query($SQLPVUpdate);
        }else if($Receipt_Amount == 0){
            $SQLUpdate = "UPDATE `order` SET order_status = 'Invoiced' WHERE order_id = {$order_id}";
            $resultUpdate = $db->sql_query($SQLUpdate);

            $SQLPVUpdate = "UPDATE patient_visit SET status = 'Invoiced' WHERE patient_visit_id = {$orderRec['patient_visit_id']}";
            $resultPVUpdate = $db->sql_query($SQLPVUpdate);
        }

        /*$SQLUpdate = "UPDATE `order` SET order_status =
         CASE WHEN record_type = 'POS' THEN 'Cancelled' ELSE 'Due' END WHERE order_id =
        (SELECT order_id
         FROM invoice WHERE invoice_id IN
        (SELECT invoice_id
         FROM invoice_receipt_history
         WHERE receipt_id = (SELECT receipt_id FROM receipt
            WHERE receipt_code = '{$receipt_code}')
         )
         )";*/
    }

    /**
     *
     */
    function getCancelReceipt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $receipt_code = $fn->getReqParam('receipt_code');
        $order_id     = $fn->getReqParam('order_id');

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

            $resultInvHist  = $db->sql_query($SQLInvHist);
            $numRowsInvHist = $db->sql_numrows($resultInvHist);
            $rowInvHist     = $db->sql_fetchrow($resultInvHist);

            /* Updating status to due if one record in hist table for the invoice */
            if ($numRowsInvHist > 1) {
                $sqlIn = "
                UPDATE invoice
                SET status = 'Partial Payment'
                WHERE invoice_id = {$rowIrh['invoice_id']}
                ";
                $resultIn = $db->sql_query($sqlIn);
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

        $SQLUpdate = "
        UPDATE `order` SET order_status ='Due'
        WHERE order_id = {$order_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return;
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

    /**
     *
     */
    function getEmployeeSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEmployeeSubmitValidate()){
            return $validate->getErrorMessageXML();
        }

        $patientVisitIds     = $fn->getPostParam('patientVisitId', array());
        $order_id = $fn->getReqParam('order_id');

        foreach($patientVisitIds as $value){
            $fa = array();
            $fa['order_id']  = $order_id;

            $whereCondition = "
            WHERE patient_visit_id = {$value}
            ";
            $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa, 'patient_visit', $whereCondition);
            $db->sql_query($SQLInvoice);

            $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $value);
            $patientInfoRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id']);

            $SQLTreatment = "
            SELECT  t.title
                   ,tv.fees
                   ,tv.notes
                   ,tv.treatment_visit_id
            FROM treatment_visit tv
            LEFT JOIN treatment t ON (t.treatment_id = tv.treatment_id)
            WHERE tv.patient_visit_id = {$value}
            AND status = 'Current'
            ";
            $resultTreatment  = $db->sql_query($SQLTreatment);
            $numRowsTreatment = $db->sql_numrows($resultTreatment);

            if($numRowsTreatment > 0){

              while ($rowTreatment = $db->sql_fetchrow($resultTreatment)) {

                $fa3['record_id']       = $rowTreatment['treatment_visit_id'];
                $fa3['order_id']        = $order_id;
                $fa3['record_type']     = 'Treatment';
                $fa3['unit_price']      = $rowTreatment['fees'];
                $fa3['description']     = $rowTreatment['notes'];
                $fa3['item_title']      = $rowTreatment['title'];
                $fa3['patient_visit_id']       = $value;
                $fa3['patient_information_id'] = $patientInfoRec['patient_information_id'];
                $fa3['first_name']             = $patientInfoRec['first_name'];
                $fa3['middle_name']            = $patientInfoRec['middle_name'];
                $fa3['last_name']              = $patientInfoRec['last_name'];
                $fa3['nric']                   = $patientInfoRec['nric'];

                $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$rowTreatment['treatment_visit_id']}'
                                                                        AND order_id = {$order_id}
                                                                        AND record_type = 'Treatment'");

                if(is_array($orderItemRec)){
                    $fa3['modification_date']   = date('Y-m-d-H-i-s');

                    $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                    $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa3, "order_item", $whereCondition);
                    $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
                } else {
                    $fa3['creation_date']   = date('Y-m-d-H-i-s');

                    $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa3, 'order_item');
                    $resultOI = $db->sql_query($SQLOI);
                }

              }

            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEmployeeSubmitValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPatientInvoiceSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getPatientInvoiceSubmitValidate()){
            return $validate->getErrorMessageXML();
        }

        $patientVisitIds = $fn->getPostParam('patientVisitId', array());
        $order_id        = $fn->getReqParam('order_id');
        $receipt         = $fn->getReqParam('receipt');
        $site_id         = $fn->getSessionParam('cp_site_id');

        foreach($patientVisitIds as $value){
            $fa = array();
            $fa['order_id']  = $order_id;

            $whereCondition = "
            WHERE patient_visit_id = {$value}
            ";
            $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa, 'patient_visit', $whereCondition);
            $db->sql_query($SQLInvoice);

            $patientVisitRec = $fn->getRecordRowByID('patient_visit', 'patient_visit_id', $value);
            $patientInfoRec = $fn->getRecordRowByID('patient_information', 'patient_information_id', $patientVisitRec['patient_information_id'], array('globalForAllSites' => true));

            $SQLTreatment = "
            SELECT  t.title
                   ,tv.fees
                   ,tv.notes
                   ,tv.treatment_visit_id
            FROM treatment_visit tv
            LEFT JOIN treatment t ON (t.treatment_id = tv.treatment_id)
            WHERE tv.patient_visit_id = {$value}
            AND status = 'Current'
            ";
            $resultTreatment  = $db->sql_query($SQLTreatment);
            $numRowsTreatment = $db->sql_numrows($resultTreatment);

            if($numRowsTreatment > 0){

              while ($rowTreatment = $db->sql_fetchrow($resultTreatment)) {

                $fa3['record_id']              = $rowTreatment['treatment_visit_id'];
                $fa3['order_id']               = $order_id;
                $fa3['record_type']            = 'Treatment';
                $fa3['unit_price']             = $rowTreatment['fees'];
                $fa3['description']            = $rowTreatment['notes'];
                $fa3['item_title']             = $rowTreatment['title'];
                $fa3['patient_visit_id']       = $value;
                $fa3['patient_information_id'] = $patientInfoRec['patient_information_id'];
                $fa3['patient_name']           = $patientInfoRec['name'];
                $fa3['first_name']             = $patientInfoRec['first_name'];
                $fa3['middle_name']            = $patientInfoRec['middle_name'];
                $fa3['last_name']              = $patientInfoRec['last_name'];
                $fa3['nric']                   = $patientInfoRec['registration_no'];

                /*
                $orderItemRec = $fn->getRecordByCondition('order_item',"record_id = '{$rowTreatment['treatment_visit_id']}'
                                                                        AND order_id = {$order_id}
                                                                        AND record_type = 'Treatment'");
                */
                $sqlOiRec = "
                SELECT * FROM order_item
                WHERE record_id = '{$rowTreatment['treatment_visit_id']}'
                  AND order_id = {$order_id}
                  AND record_type = 'Treatment'
                ";
                $resultOiRec  = $db->sql_query($sqlOiRec);
                $numRowsOiRec = $db->sql_numrows($resultOiRec);

                if($numRowsOiRec > 0){
                //if(is_array($orderItemRec)){
                    $fa3['modification_date']   = date('Y-m-d-H-i-s');

                    $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                    $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa3, "order_item", $whereCondition);
                    $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
                } else {
                    $fa3['creation_date']   = date('Y-m-d-H-i-s');

                    $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa3, 'order_item');
                    $resultOI = $db->sql_query($SQLOI);
                }
              }
            }
        }
        //INVOICE and INVOICE ITEMS CREATION CODES
        $total_amount_payable = 0;
        $count = count($patientVisitIds);
        for ($i= 0; $i< $count; $i++) {
            $patient_id = $patientVisitIds[$i];

            $sqlOiSum = "
            SELECT SUM(unit_price) AS total_amount_payable
            FROM order_item
            WHERE order_id = {$order_id}
              AND patient_visit_id = {$patient_id}
            ";
            $resultOiSum = $db->sql_query($sqlOiSum);
            $rowOiSum    = $db->sql_fetchrow($resultOiSum);

            $total_amount_payable += $rowOiSum['total_amount_payable'];
        }

        $invoice_code_prefix = $fn->getSettingsValueByKey("invoiceCodePrefix");
        $invoice_code = $fn->getSettingsValueByKey("nextInvoiceCode");

        /* Creating a new invoice */
        $faInv = array();
        $faInv['invoice_date']     = date('Y-m-d');
        $faInv['invoice_due_date'] = date('Y-m-d', strtotime("+30 days"));
        $faInv['status']           = 'Due';
        $faInv['invoice_amount']   = $total_amount_payable;
        $faInv['created_by']       = $fn->getSessionParam('userName');
        $faInv['creation_date']    = date('Y-m-d H:i:s');
        $faInv['invoice_code']     = $invoice_code_prefix . $invoice_code;
        $faInv['inv_currency']     = 'SGD';
        $faInv['order_id']         = $order_id;

        $invoice_id                = $fn->addRecord($faInv, 'invoice');

        /* Increment of Invoice Code */
        if ($site_id) {
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' AND site_id = {$site_id}";
        } else {
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        }
        $resultUpdate = $db->sql_query($SQLUpdate);

        $selectContactIds = join(',', $patientVisitIds);

        $sqlOi = "
        SELECT * FROM order_item
        WHERE order_id = {$order_id}
          AND patient_visit_id IN ($selectContactIds)
        ";
        $resultOi = $db->sql_query($sqlOi);
        while ($rowOi = $db->sql_fetchrow($resultOi)) {
            /* Creating a invoice item records */
            $faInvItem = array();
            $faInvItem['record_id']         = $rowOi['record_id'];
            $faInvItem['qty']               = $rowOi['qty'];
            $faInvItem['unit_price']        = $rowOi['unit_price'];
            $faInvItem['item_title']        = $rowOi['item_title'];
            $faInvItem['invoice_id']        = $invoice_id;
            $faInvItem['order_item_id']     = $rowOi['order_item_id'];
            $faInvItem['record_type']       = $rowOi['record_type'];
            $invoice_item_id                = $fn->addRecord($faInvItem, 'invoice_item');

            /* Updating Invoice Id to Order Item Table */
            $faOi = array();
            $faOi['invoice_id'] = $invoice_id;
            $fn->saveRecord($faOi, 'order_item', 'order_item_id', $rowOi['order_item_id']);
        }

        //RECEIPT and HISTORY CREATION CODES
        if($receipt == 1){
            //To update receipt codes
            if ($site_id) {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode' AND site_id = {$site_id}";
            } else {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            }
            $resultUpdate = $db->sql_query($SQLUpdate);
            $receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");

            $fa = array();
            $fa['amount']         = $total_amount_payable;
            $fa['order_id']       = $order_id;
            $fa['receipt_code']   = 'RCPT - ' . $receipt_code;
            $fa['mode_of_payment']= 'Cash';
            $fa['date']           = date("Y-m-d H:i:s");
            $fa['receipt_status'] = 'Paid';
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');

            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();

            $receipt_amount     = $total_amount_payable;

            $faInv = array();
            $faInv['status'] = 'Paid';
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $receipt_amount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getPatientInvoiceSubmitValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

}
