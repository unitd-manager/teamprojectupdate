<?
class CP_Admin_Modules_Tradingsg_Order_Model extends CP_Common_Lib_ModuleModelAbstract
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
              ,c.company_name AS companyName
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
              ,q.delivery_date
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
        $creation_date1 = $fn->getReqParam('creation_date_1');
        $creation_date2 = $fn->getReqParam('creation_date_2');
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

            if ($creation_date1 != "" && $creation_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(o.creation_date BETWEEN '{$creation_date1} 00:00:00' AND '{$creation_date2} 23:59:59')";
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
                    o.cust_first_name 		LIKE '%{$tv['keyword']}%'  OR
                    c.company_name 	  		LIKE '%{$tv['keyword']}%'  OR
                    o.order_id        		LIKE '%{$tv['keyword']}%'  OR
                    o.cust_last_name  		LIKE '%{$tv['keyword']}%'  OR
                    o.order_code      		LIKE '%{$tv['keyword']}%'  OR
                    o.memo            		LIKE '%{$tv['keyword']}%'  OR
                    o.shipping_first_name 	LIKE '%{$tv['keyword']}%'  OR
                    o.shipping_last_name 	LIKE '%{$tv['keyword']}%'
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
    function getTradingsgOrderEcommerceOrderItemLinkSQL($id) {
        $fn = Zend_Registry::get('fn');

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
              ,b.unit_price
              ,b.qty
              ,b.qty * b.unit_price
        FROM `order_item` b
        LEFT JOIN (invoice i) ON (b.invoice_id = i.invoice_id)
        WHERE b.order_id = {$id}
              {$whereSQL}
        ";

    }

    /**
     *
     */
    function getTradingsgOrderTradingsgInvoiceLinkSQL($id) {

        return "
        SELECT i.invoice_id
              ,i.invoice_code
              ,i.status
              ,DATE_FORMAT(i.invoice_date, '%d-%m-%Y') AS invoice_date
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
              ,i.invoice_amount
              ,(CONCAT_WS('', '<a href=\'index.php?module=tradingsg_order&_spAction=printInvoiceRecord&id=', i.invoice_id, '\' target=\'_blank\'>Print Invoice</a>'))
              ,(CONCAT_WS('', '<a href=\'index.php?module=tradingsg_order&_spAction=cancelInvoice&id=', i.invoice_id, '\' target=\'_self\'>Cancel Invoice</a>'))
        FROM `invoice` i
        LEFT JOIN (staff s) ON (i.staff_id = s.staff_id)
        WHERE i.order_id = {$id}
        ";

    }

    /**
     *
     */
    function getTradingsgOrderTradingsgReceiptLinkSQL($id) {

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
        AND status != 'Cancelled'
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

        /* Finding of receipt record */
        /*$SQLIrh = "
        SELECT it.*
              ,i.invoice_id
        FROM invoice_item it
        LEFT JOIN (invoice i) ON (it.invoice_id = i.invoice_id)
        WHERE i.invoice_id = {$invoice_id}
          AND it.amount > 0
        ";
        $resultIrh = $db->sql_query($SQLIrh);
        $numRowsIrh = $db->sql_numrows($resultIrh);
        $rowIrh = $db->sql_fetchrow($resultIrh);*/

        //if ($numRowsIrh == 0) {
            /* Updating of invoice record */
            $sqlInv = "
            UPDATE invoice
            SET status = 'Cancelled'
            WHERE invoice_code = '{$invoice_code}'
            ";
            $resultInv = $db->sql_query($sqlInv);

        //}

    }
    /**
     *
     */
    function getCancelReceipt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $receipt_code = $fn->getReqParam('receipt_code');

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

    }
}
