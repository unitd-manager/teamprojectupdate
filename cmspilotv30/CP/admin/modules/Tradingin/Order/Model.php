<?
class CP_Admin_Modules_Tradingin_Order_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $orgName = '';
        $orgTable = '';
        if ($cpCfg['m.ecommerce.order.showOrganization']) {
            $orgName = ",org.name AS organization_name";
            $orgTable = "LEFT JOIN organization org ON (o.organization_id = org.organization_id)";
        }

        $sumTxt = '';
        if ($cpCfg['m.ecommerce.order.hasDiscount']){
            $sumTxt = "SUM(oi.unit_price * oi.qty) + o.shipping_charge - o.discount";
        } else {
            $sumTxt = "SUM(oi.unit_price * oi.qty) + o.shipping_charge";
        }

        $SQL = "
        SELECT o.*
              ,gc1.name AS cust_country_name
              ,gc2.name AS shipping_country_name
              ,c.company_name AS company_name
              ,c.website AS company_website
              ,c.fax AS company_fax
              ,c.phone AS company_phone
              ,c.address_flat AS company_address_flat
              ,c.address_street AS company_address_street
              ,c.address_town AS company_address_town
              ,c.address_state AS company_address_state
              ,gc3.name AS company_country_name
              ,(SELECT ($sumTxt)
               FROM order_item oi
               WHERE oi.order_id = o.order_id
               ) AS order_amount
              ,q.quote_code
              {$orgName}
        FROM `order` o
        LEFT JOIN geo_country gc1 ON (o.cust_address_country_code = gc1.country_code)
        LEFT JOIN geo_country gc2 ON (o.shipping_address_country_code = gc2.country_code)
        LEFT JOIN company c ON (o.company_id = c.company_id)
        LEFT JOIN geo_country gc3 ON (c.address_country = gc3.country_code)
        LEFT JOIN quote q ON o.quote_id = q.quote_id
        {$orgTable}
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

        $business_id = $fn->getReqParam('business_id');
        $organization_id = $fn->getReqParam('organization_id');
        $business_contact_id = $fn->getReqParam('business_contact_id');
        $order_date1 = $fn->getReqParam('order_date_1');
        $order_date2 = $fn->getReqParam('order_date_2');
        $order_status = $fn->getReqParam('order_status');
        $shipment_status = $fn->getReqParam('shipment_status');
        $order_type   = $fn->getReqParam('order_type');
        $ok_to_ship   = $fn->getReqParam('ok_to_ship');
        $shipping_address_country_code = $fn->getReqParam('shipping_address_country_code');
        $order_id   = $fn->getReqParam('order_id');

        if ($order_id != "") {
            $searchVar->sqlSearchVar[] = "o.order_id = '{$order_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "o.order_id = '{$tv['record_id']}'";
        } else {

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

            if ($shipment_status != '') {
                $searchVar->sqlSearchVar[] = "o.shipment_status = '{$shipment_status}'";
            }

            if ($order_type != '') {
                $searchVar->sqlSearchVar[] = "o.order_type = '{$order_type}'";
            }

            if ($shipping_address_country_code != '') {
                $searchVar->sqlSearchVar[] = "o.shipping_address_country_code = '{$shipping_address_country_code}'";
            }

            if ($ok_to_ship != '') {
                $searchVar->sqlSearchVar[] = "o.ok_to_ship = '{$ok_to_ship}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    o.cust_first_name LIKE '%{$tv['keyword']}%'  OR
                    o.order_id        LIKE '%{$tv['keyword']}%'  OR
                    c.company_name    LIKE '%{$tv['keyword']}%'  OR
                    o.name_of_company LIKE '%{$tv['keyword']}%'  OR
                    o.cust_last_name  LIKE '%{$tv['keyword']}%'  OR
                    o.order_code      LIKE '%{$tv['keyword']}%'  OR
                    o.memo            LIKE '%{$tv['keyword']}%'  OR
                    o.shipping_first_name LIKE '%{$tv['keyword']}%'  OR
                    o.shipping_last_name LIKE '%{$tv['keyword']}%'
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
        $fa = $fn->addToFieldsArray($fa, 'other_state');

        return $fa;
    }


    //==================================================================//
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'order_id'        => $phpExcel->getFldObj('Order ID')
             ,'shipping_first_name'           => $phpExcel->getFldObj('First Name')
             ,'shipping_last_name'            => $phpExcel->getFldObj('Last Name')
             ,'shipping_email'                => $phpExcel->getFldObj('Email')
             ,'shipping_phone'                => $phpExcel->getFldObj('Phone')
             ,'shipping_address1'             => $phpExcel->getFldObj('Address 1')
             ,'shipping_address2'             => $phpExcel->getFldObj('Address 2')
             ,'shipping_address_city'         => $phpExcel->getFldObj('City')
             ,'shipping_address_area'         => $phpExcel->getFldObj('Area')
             ,'shipping_address_state'        => $phpExcel->getFldObj('State')
             ,'shipping_address_country_code' => $phpExcel->getFldObj('Country')
             ,'shipping_address_po_code'      => $phpExcel->getFldObj('Zip Code')
             ,'payment_method'                => $phpExcel->getFldObj('Payment Method')
             ,'order_amount'                  => $phpExcel->getFldObj('Amount')
             ,'order_status'                  => $phpExcel->getFldObj('Payment Status')
             ,'creation_date'                 => $phpExcel->getFldObj('Order Date')
        );

        $file_name = "Order_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
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

        $invoice_code = $fn->getReqParam('invoice_code');
        $invoice_id = $fn->getReqParam('invoice_id');

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
            /* Updating of invoice record */
            $sqlInv = "
            UPDATE invoice
            SET status = 'Cancelled'
            WHERE invoice_code = '{$invoice_code}'
            ";
            $resultInv = $db->sql_query($sqlInv);
        } else {
            return 'Cannot cancel';
        }

    }
    /**
     *
     */
    function getCancelReceipt() {
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
        $resultUpdate = $db->sql_query($SQLUpdate);

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
