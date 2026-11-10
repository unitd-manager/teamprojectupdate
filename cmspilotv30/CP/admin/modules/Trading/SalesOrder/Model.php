<?
class CP_Admin_Modules_Trading_SalesOrder_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT so.*
              ,e.subject AS enquiry_title
              ,e.enquiry_code
              ,q.quote_code
              ,com.company_name AS company_name_customer
              ,pt.pricing_type
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name_customer
              ,com2.company_name AS company_name_supplier
              ,CONCAT_WS(' ', c2.first_name, c2.last_name) AS contact_name_supplier
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name

              ,(SELECT SUM(soi.sell_unit_price * soi.quantity)
                FROM sales_order_items soi
                WHERE soi.sales_order_id = so.sales_order_id) AS order_value

              ,(SELECT SUM(soi.sell_unit_price * soi.quantity) * (1 + so.tax_percentage /100)
                FROM sales_order_items soi
                WHERE soi.sales_order_id = so.sales_order_id) AS order_value_tax

              ,(SELECT SUM(i.sell_unit_price_actual)
                FROM sales_order_inventory soi
                JOIN inventory i ON i.inventory_id = soi.inventory_id
                WHERE soi.sales_order_id = so.sales_order_id) AS order_value_inventory

              ,(SELECT SUM(i.sell_unit_price_actual) * (1 + so.tax_percentage /100)
                FROM sales_order_inventory soi
                JOIN inventory i ON i.inventory_id = soi.inventory_id
                WHERE soi.sales_order_id = so.sales_order_id) AS order_value_tax_inventory

              ,(SELECT SUM(i2.invoice_amount)
                FROM invoice i2
                WHERE i2.sales_order_id = so.sales_order_id) AS invoice_amount_sum

              ,(SELECT CEIL( SUM(soi.quantity / soi.unit_ctn) )
                FROM sales_order_items soi
                JOIN product p ON (soi.product_id = p.product_id)
                WHERE soi.sales_order_id = so.sales_order_id) AS total_cartons

              ,(SELECT SUM(soi.quantity * p.unit_net_weight)
                FROM sales_order_items soi
                JOIN product p ON (soi.product_id = p.product_id)
                WHERE soi.sales_order_id = so.sales_order_id) AS total_net_weight

              ,(SELECT CEIL( SUM(soi.quantity / soi.unit_ctn) ) * SUM(p.gross_weight)
                FROM sales_order_items soi
                JOIN product p ON (soi.product_id = p.product_id)
                WHERE soi.sales_order_id = so.sales_order_id) AS total_gross_weight

              ,(SELECT CEIL( SUM(soi.quantity / soi.unit_ctn) ) * SUM(p.carton_volume)
                FROM sales_order_items soi
                JOIN product p ON (soi.product_id = p.product_id)
                WHERE soi.sales_order_id = so.sales_order_id) AS total_volume

              ,CONCAT_WS(', '
                        ,da.address_flat
                        ,da.address_street
                        ,da.address_town
                        ,da.address_state
                        ,da.address_country
                        ,da.address_po_code
               ) AS ship_to_location

        FROM sales_order so
        LEFT JOIN company com ON so.company_id_customer = com.company_id
        LEFT JOIN contact c ON so.contact_id_customer = c.contact_id
        LEFT JOIN company com2 ON so.company_id_supplier = com2.company_id
        LEFT JOIN contact c2 ON so.contact_id_supplier = c2.contact_id
        LEFT JOIN staff s ON so.staff_id = s.staff_id
        LEFT JOIN delivery_address da ON da.delivery_address_id = so.delivery_address_id
        LEFT JOIN enquiry e ON e.enquiry_id = so.enquiry_id
        LEFT JOIN quote q ON q.quote_id = so.quote_id
        LEFT JOIN pricing_type pt ON pt.pricing_type_id = com.pricing_type_id
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
        $order_type = $fn->getReqParam('order_type');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "so.sales_order_id = {$tv['record_id']}";
        } else {
            if ($status != "") {
                $searchVar->sqlSearchVar[] = "so.status = '{$status}'";
            }
            if ($order_type != "") {
                $searchVar->sqlSearchVar[] = "so.order_type = '{$order_type}'";
            }
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "so.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(so.flag != 1 OR so.flag IS null)";
            }
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       so.so_code LIKE '%{$tv['keyword']}%'
                    OR com.company_name LIKE '%{$tv['keyword']}%'
                    OR so.client_so_no LIKE '%{$tv['keyword']}%'
                    OR so.status LIKE '%{$tv['keyword']}%'
                    OR e.enquiry_code LIKE '%{$tv['keyword']}%'
                    OR q.quote_code LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "so.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_id_customer', 'Please choose the customer name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getDefaultValuesForAdd(){
        $fn = Zend_Registry::get('fn');

        $so_code = 'SO' . $fn->getSequenceFromSettings('m.trading.salesOrder.nextCode');

        $company_id_customer = $fn->getReqParam('company_id_customer');
        $rowCustomer = $fn->getRecordRowByID('company', 'company_id', $company_id_customer);

        $order_type = $fn->getReqParam('order_type', 'general');

        $fa = array();
        $fa['so_code']          = $so_code;
        $fa['sales_order_date'] = date('Y-m-d');
        $fa['status']           = 'quote';
        $fa['order_type']       = $order_type;
        $fa['tax_percentage']               = $rowCustomer['tax_percentage'];

        $fa['consignee_name']               = $rowCustomer['consignee_name'];
        $fa['consignee_address']            = $rowCustomer['consignee_address'];
        $fa['consignee_phone_country_code'] = $rowCustomer['consignee_phone_country_code'];
        $fa['consignee_phone_area_code']    = $rowCustomer['consignee_phone_area_code'];
        $fa['consignee_phone']              = $rowCustomer['consignee_phone'];
        $fa['consignee_contact_person']     = $rowCustomer['consignee_contact_person'];
        $fa['sell_currency']                = $rowCustomer['sell_currency'];
        $fa['tax_percentage']               = $rowCustomer['tax_percentage'];

        return $fa;
    }

    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array_merge($this->getFields(), $this->getDefaultValuesForAdd());

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('status', 'Please choose a status');
        $validate->validateData('company_id_customer', 'Please choose the customer name');
        $validate->validateData('payment_terms', 'Please enter payment terms');
        $validate->validateData('delivery_terms', 'Please enter delivery terms');
        $validate->validateData('sell_currency', 'Please choose sell currency');

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
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $sales_order_id = $fn->getReqParam('sales_order_id');
        $row = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);

        $fa = $this->getFields();

        $order_type = $row['order_type'];

        //update inventory status
        $inventoryStatus = '';
        $prevStatus = $row['status'];
        $newStatus = $fa['status'];
        if ($prevStatus != $newStatus && $order_type != 'SOR') {
            if ($newStatus == 'new') {
                $inventoryStatus = 'on enquiry';
            } else if ($newStatus == 'confirmed') {
                $inventoryStatus = 'sold';
            } else if ($newStatus == 'on hold') {
                $inventoryStatus = 'on enquiry';
            } else if ($newStatus == 'cancelled') {
                $inventoryStatus = 'available';
            }
            $SQL = "
            UPDATE inventory
            SET status = '{$inventoryStatus}'
            WHERE sales_order_id = {$sales_order_id}
            ";
            $db->sql_query($SQL);

            $SQL = "
            UPDATE inventory
            SET status = '{$inventoryStatus}'
            WHERE sales_order_id_inventory = {$sales_order_id}
            ";
            $db->sql_query($SQL);
        }

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

        $fa = $fn->addToFieldsArray($fa, 'so_code');
        $fa = $fn->addToFieldsArray($fa, 'client_so_no');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'order_type');
        $fa = $fn->addToFieldsArray($fa, 'company_id_customer');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_customer');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'notes_customer');
        $fa = $fn->addToFieldsArray($fa, 'sales_order_date');
        $fa = $fn->addToFieldsArray($fa, 'sell_currency');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'delivery_address_id');
        $fa = $fn->addToFieldsArray($fa, 'consignee_name');
        $fa = $fn->addToFieldsArray($fa, 'consignee_address');
        $fa = $fn->addToFieldsArray($fa, 'consignee_phone_country_code');
        $fa = $fn->addToFieldsArray($fa, 'consignee_phone_area_code');
        $fa = $fn->addToFieldsArray($fa, 'consignee_phone');
        $fa = $fn->addToFieldsArray($fa, 'consignee_contact_person');
        $fa = $fn->addToFieldsArray($fa, 'tax_percentage');

        return $fa;
    }

    /**
     *
     */
    function getTradingSalesOrderTradingProductLinkSQL($id) {

        $chooseRFQText = "
        CASE
        WHEN soi.quote_items_id = 0 OR soi.quote_items_id IS NULL
        THEN CONCAT_WS('',
                      '<a href=\"javascript:cpm.trading.salesOrder.chooseRFQForLine(\'',
                      soi.sales_order_items_id,
                      '\',\'',
                      soi.quote_items_id,
                      '\')\">Choose Prev RFQ</a>'
                      )
        ELSE ''
        END
        ";

        $SQL = "
        SELECT soi.sales_order_items_id
              ,CONCAT_WS('-', so.so_code, soi.line_no) AS line_no
              ,p.product_id
              ,p.product_code
              ,p.web_code
              ,p.title AS product_name
              ,p.color
              ,soi.quantity AS quantity_ordered
              ,p.unit
              ,so.sell_currency
              ,soi.sell_unit_price
              ,soi.sell_unit_price * soi.quantity AS sell_price
              ,soi.request_date
              ,soi.promised_delivery_date
              ,(SELECT SUM(quantity) FROM sales_order_items WHERE sales_order_id = {$id}) AS quantity_ordered_sum
              ,(SELECT SUM(sell_unit_price * quantity) FROM sales_order_items WHERE sales_order_id = {$id}) AS sell_price_sum
              ,{$chooseRFQText} AS chooseRFQ
        FROM sales_order_items soi
        JOIN sales_order so ON (so.sales_order_id = soi.sales_order_id)
        JOIN product p ON (p.product_id = soi.product_id)
        WHERE soi.sales_order_id = {$id}
          AND soi.record_type = 'product'
        ORDER BY p.web_code
                ,soi.line_no
        ";

        return $SQL;
    }

    function getTradingSalesOrderTradingInventoryLinkSQL($id) {
        $SQL = "
        SELECT soi.sales_order_inventory_id
              ,i.inventory_id
              ,p.product_id
              ,p.product_code
              ,p.web_code
              ,i.serial_no
              ,p.title AS product_name
              ,i.status
              ,i.location
              ,p.unit
              ,so.sell_currency
              ,i.sell_unit_price_actual
        FROM sales_order_inventory soi
        JOIN sales_order so ON so.sales_order_id = soi.sales_order_id
        JOIN inventory i ON i.inventory_id = soi.inventory_id
        JOIN product p ON p.product_id = i.product_id
        WHERE soi.sales_order_id = {$id}
        ORDER BY p.web_code
                ,i.serial_no
        ";
        return $SQL;
    }

    function getTradingSalesOrderTradingInvoiceLinkSQL($id) {
        $SQL = "
        SELECT i.invoice_id
              ,i.invoice_code
              ,i.invoice_type
              ,i.invoice_date
              ,i.invoice_amount

              ,(SELECT SUM(i2.invoice_amount)
                FROM invoice i2
                WHERE i2.sales_order_id = {$id}
                ) AS invoice_amount_sum
              ,i.status
        FROM invoice i
        WHERE i.sales_order_id = {$id}
        ORDER BY i.invoice_date
        ";
        return $SQL;
    }

    function getTradingSalesOrderTradingPurchaseOrderLinkSQL($id) {
        $SQL = "
        SELECT po.purchase_order_id
              ,po.po_code
              ,po.purchase_order_date
              ,po.company_id_supplier
              ,c.company_name AS supplier_name
              ,(SELECT SUM(buy_unit_price * quantity)
                FROM purchase_order_items
                WHERE purchase_order_id = po.purchase_order_id) AS buy_price_sum
              ,po.status
        FROM purchase_order po
        JOIN company c ON c.company_id = po.company_id_supplier
        WHERE po.sales_order_id = {$id}
        ORDER BY po.purchase_order_date
        ";
        return $SQL;
    }

    /**
     *
     */
    function getChooseRFQFormForLine() {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $sales_order_items_id = $fn->getReqParam('sales_order_items_id');
        $rowSOI = $fn->getRecordRowByID('sales_order_items', 'sales_order_items_id', $sales_order_items_id);
        $product_id = $rowSOI['product_id'];

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

              ,(SELECT 1 FROM sales_order_items soi
                WHERE soi.sales_order_items_id = {$sales_order_items_id}
                  AND qri.quote_request_items_id = soi.quote_request_items_id
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
                   name='quote_request_items_id'
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
        <th>Select</td>
        </tr>
        {$rows}
        </table>
        <input type='hidden' id='sales_order_items_id' value='{$sales_order_items_id}' />
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

        $sales_order_items_id   = $fn->getReqParam('sales_order_items_id');
        $quote_request_items_id = $fn->getReqParam('quote_request_items_id');

        $rfqRow = $fn->getRecordRowByID('quote_request_items',
                                        'quote_request_items_id',
                                        $quote_request_items_id);
        $fa = array();
        $fa['quote_request_items_id'] = $quote_request_items_id;
        $fa['buy_unit_price']         = $rfqRow['buy_unit_price'];
        $fa['buy_unit_price_base']    = $rfqRow['buy_unit_price_base'];
        $fa['quantity']               = $rfqRow['quantity'];

        $whereCondition = "
        WHERE sales_order_items_id = {$sales_order_items_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'sales_order_items', $whereCondition);
        $db->sql_query($SQL);

    }

    /**
     *
     */
    function getRaiseInvoiceListValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $sales_order_id = $fn->getReqParam('sales_order_id');
        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);

        $status = 'success';
        $errMsg = '';

        $errArr = array();

        //Sales Order header status must NOT be "Cancelled"
        if ($rowSO['status'] == 'closed' || $rowSO['status'] == 'cancelled') {
            $errArr[] = "The Sales order status should not be 'closed', or 'cancelled'";
        }

        //status must be confirmed to raise invoice
        if ($rowSO['status'] != 'confirmed') {
            $status = 'error';
            $errArr[] = "Status must be confirmed to Raise Invoice";
        }

        //Payment Terms should NOT be BLANK
        if ($rowSO['payment_terms'] == '') {
            $status = 'error';
            $errArr[] = "Payment Terms should not be blank";
        }

        //Sell Currency should NOT be BLANK
        if ($rowSO['sell_currency'] == '') {
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
    function getRaiseInvoiceValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $sales_order_id = $fn->getReqParam('sales_order_id');
        $sales_order_items_ids = $fn->getReqParam('sales_order_items_ids', array());
        $sales_order_items_ids_str = $dbUtil->getArrayAsCommaSeperated($sales_order_items_ids);

        $status = 'success';
        $errMsg = '';

        if (count($sales_order_items_ids) == 0) {
            $status = 'error';
           $errMsg = 'Please choose some items';
            return array($status, $errMsg);
        }

        $errArr = array();

        //Sales Order line status must NOT be "Cancelled"
        $SQL = "
        SELECT COUNT(*) AS count
        FROM sales_order_items soi
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
          AND (soi.status = 'cancelled' OR soi.status = '' OR soi.status IS NULL)
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $errArr[] = "Please note the Sales Order Lines status should not be 'cancelled' or blank";
        }

        /*
        //Sell Total Price > 0
        $SQL = "
        SELECT COUNT(*) AS count
        FROM sales_order_items soi
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
          AND soi.sell_unit_price > 0
          AND soi.sell_unit_price IS NOT NULL
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($sales_order_items_ids_str)) {
            $errArr[] = "Sell price must be greater than 0";
        }


        //"Invoiced Amount"+"Amount to Invoice Now" MUST NOT EXCEED "Total Sell Price"
        $SQL = "
        SELECT soi.sales_order_items_id
              ,soi.sell_unit_price * soi.quantity AS sell_price
              ,soi.quantity
              ,(SELECT SUM(ii.sell_price)
                FROM invoice_items ii
                WHERE ii.sales_order_items_id = soi.sales_order_items_id) AS total_invoiced_amount
        FROM sales_order_items soi
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
        ";
        $result = $db->sql_query($SQL);

        $lessThan = new Zend_Validate_LessThan(1);
        while ($rowSOI = $db->sql_fetchrow($result)) {
            $sell_price_entered = $fn->getReqParam("sell_price_to_invoice_{$rowSOI['sales_order_items_id']}");
            if ($lessThan->isValid($sell_price_entered)) {
                $errArr[] = "Please enter a valid amount";
                break;
            }
            if ($sell_price_entered + $rowSOI['total_invoiced_amount'] > $rowSOI['sell_price']) {
                $errArr[] = "<b>Invoiced Amount</b> + <b>Amount to Invoice Now</b> must not exceed <b>Total Sell Price</b>";
                break;
            }
        }
        */

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
    function getRaiseInvoice() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $currRate = getCPModelObj('trading_currencyRate');

        list($status, $errMsg) = $this->getRaiseInvoiceValidation();
        if ($status != 'success') {
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        $sales_order_id  = $fn->getReqParam('sales_order_id');
        $sales_order_items_ids     = $fn->getReqParam('sales_order_items_ids', array());
        $sales_order_items_ids_str = $dbUtil->getArrayAsCommaSeperated($sales_order_items_ids);
        $quantity                  = $fn->getReqParam('quantity_to_ship', array());

        $rowSalesOrder = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);
        $rowCustomer = $fn->getRecordRowByID('company', 'company_id', $rowSalesOrder['company_id_customer']);

        $invoice_code = 'I' . $fn->getSequenceFromSettings('m.trading.invoice.nextCode');

        //create invoice
        $fa = array();
        $fa['sales_order_id']      = $sales_order_id;
        $fa['invoice_code']        = $invoice_code;
        $fa['invoice_date']        = date('Y-m-d');
        $fa['company_id_customer'] = $rowSalesOrder['company_id_customer'];
        $fa['contact_id_customer'] = $rowSalesOrder['contact_id_customer'];
        $fa['staff_id']            = $rowSalesOrder['staff_id'];
        $fa['sell_currency']       = $rowCustomer['sell_currency'];
        $fa['payment_terms']       = $rowSalesOrder['payment_terms'];
        $fa['delivery_terms']      = $rowSalesOrder['delivery_terms'];
        $fa['status']              = 'due';
        $fa['invoice_type']        = 'Invoice';
        $fa['notes']               = $fn->getSettingsValueByKey('m.trading.invoice.defaultNote');
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'invoice');
        $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
        $db->sql_query($SQL);
        $invoice_id = $db->sql_nextid();

        //create line items
        $SQL = "
        SELECT soi.*
        FROM sales_order_items soi
        WHERE soi.sales_order_id = {$sales_order_id}
          AND soi.sales_order_items_id IN ({$sales_order_items_ids_str})
        ORDER BY soi.line_no
        ";
        $result = $db->sql_query($SQL);

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        while ($rowSOI = $db->sql_fetchrow($result)) {
            $sell_price = $fn->getReqParam("sell_price_to_invoice_{$rowSOI['sales_order_items_id']}");
            $line_no = $fnsModGrp->getNextItemLineNo('invoice_id', $invoice_id, 'invoice_items');

            $sell_price_base = $currRate->getConvertedCurrencyValue($rowSalesOrder['buy_currency'], $cpCfg['m.trading.companyCurrency'], $sell_price);

            $fa = array();
            $fa['invoice_id']           = $invoice_id;
            $fa['product_id']           = $rowSOI['product_id'];
            $fa['sales_order_items_id'] = $rowSOI['sales_order_items_id'];
            $fa['quantity']             = $rowSOI['quantity'];
            $fa['line_no']              = $line_no;
            $fa['sell_price']           = $sell_price;
            $fa['sell_price_base']      = $sell_price_base;
            $fa['status']               = 'new';

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice_items');
            $db->sql_query($SQL);
            $shipment_items_id = $db->sql_nextid();
        }

        //change the inventory location to delivered
        $fa = array();
        $fa['location'] = 'delivered';

        $whereCondition = "
        WHERE sales_order_id = {$sales_order_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory', $whereCondition);
        $db->sql_query($SQL);

        $topRm = $fn->getTopRoomName('invoice');
        $url = "index.php?_topRm={$topRm}&module=trading_invoice" .
               "&_action=detail&record_id={$invoice_id}";
        $arr = array('status' => 'success', 'returnUrl' => $url);
        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getRaiseInvoiceListInventoryValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $sales_order_id = $fn->getReqParam('sales_order_id');
        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);

        $status = 'success';
        $errMsg = '';

        $errArr = array();

        //Sales Order header status must NOT be "Cancelled"
        if ($rowSO['status'] == 'closed' || $rowSO['status'] == 'cancelled') {
            $errArr[] = "The Sales order status should not be 'closed', or 'cancelled'";
        }

        //status must be confirmed to raise invoice
        if ($rowSO['status'] != 'confirmed') {
            $status = 'error';
            $errArr[] = "Status must be confirmed to Raise Invoice";
        }

        //Payment Terms should NOT be BLANK
        if ($rowSO['payment_terms'] == '') {
            $status = 'error';
            $errArr[] = "Payment Terms should not be blank";
        }

        //Sell Currency should NOT be BLANK
        if ($rowSO['sell_currency'] == '') {
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
    function getRaiseInvoiceInventoryValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $sales_order_id = $fn->getReqParam('sales_order_id');
        $sales_order_inventory_ids = $fn->getReqParam('sales_order_inventory_ids', array());
        $sales_order_inventory_ids_str = $dbUtil->getArrayAsCommaSeperated($sales_order_inventory_ids);

        $status = 'success';
        $errMsg = '';

        /*
        if (count($sales_order_inventory_ids) == 0) {
            $status = 'error';
           $errMsg = 'Please choose some items';
            return array($status, $errMsg);
        }
        */

        return array($status, $errMsg);
    }

    /**
     *
     */
    function getRaiseInvoiceInventory() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $currRate = getCPModelObj('trading_currencyRate');

        list($status, $errMsg) = $this->getRaiseInvoiceInventoryValidation();
        if ($status != 'success') {
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        $sales_order_id  = $fn->getReqParam('sales_order_id');
        $sales_order_inventory_ids     = $fn->getReqParam('sales_order_inventory_ids', array());
        $sales_order_inventory_ids_str = $dbUtil->getArrayAsCommaSeperated($sales_order_inventory_ids);
        if (trim($sales_order_inventory_ids_str == '')) {
            $sales_order_inventory_ids_str = '0';
        }

        $rowSalesOrder = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);
        $rowCustomer = $fn->getRecordRowByID('company', 'company_id', $rowSalesOrder['company_id_customer']);

        $invoice_code = 'I' . $fn->getSequenceFromSettings('m.trading.invoice.nextCode');

        //create invoice
        $fa = array();
        $fa['sales_order_id']      = $sales_order_id;
        $fa['invoice_code']        = $invoice_code;
        $fa['invoice_date']        = date('Y-m-d');
        $fa['company_id_customer'] = $rowSalesOrder['company_id_customer'];
        $fa['contact_id_customer'] = $rowSalesOrder['contact_id_customer'];
        $fa['staff_id']            = $rowSalesOrder['staff_id'];
        $fa['sell_currency']       = $rowCustomer['sell_currency'];
        $fa['payment_terms']       = $rowSalesOrder['payment_terms'];
        $fa['delivery_terms']      = $rowSalesOrder['delivery_terms'];
        $fa['status']              = 'due';
        $fa['invoice_type']        = 'Invoice';
        $fa['notes']               = $fn->getSettingsValueByKey('m.trading.invoice.defaultNote');

        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'invoice');
        $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
        $db->sql_query($SQL);
        $invoice_id = $db->sql_nextid();

        //create line items
        $SQL = "
        SELECT soi.sales_order_inventory_id
              ,soi.inventory_id
              ,i.product_id
              ,i.retail_unit_price
              ,i.retail_unit_price_discount
        FROM sales_order_inventory soi
        JOIN inventory i ON i.inventory_id = soi.inventory_id
        WHERE soi.sales_order_id = {$sales_order_id}
          AND soi.sales_order_inventory_id IN ({$sales_order_inventory_ids_str})
        ";
        $result = $db->sql_query($SQL);

        while ($rowSOI = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['invoice_id']   = $invoice_id;
            $fa['inventory_id'] = $rowSOI['inventory_id'];
            $fa['product_id']   = $rowSOI['product_id'];

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice_inventory');
            $db->sql_query($SQL);
            $shipment_items_id = $db->sql_nextid();
        }

        // //change the inventory location to delivered
        // $fa = array();
        // $fa['location'] = 'delivered';

        // $whereCondition = "
        // WHERE sales_order_id = {$sales_order_id}
        // ";
        // $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory', $whereCondition);
        // $db->sql_query($SQL);

        $topRm = $fn->getTopRoomName('invoice');
        $url = "index.php?_topRm={$topRm}&module=trading_invoice" .
               "&_action=detail&record_id={$invoice_id}";
        $arr = array('status' => 'success', 'returnUrl' => $url);
        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getRaisePOListValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $sales_order_id = $fn->getReqParam('sales_order_id');
        $status = 'success';
        $errMsg = '';

        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);

        $status = 'success';
        $errMsg = '';

        $errArr = array();

        //Sales Order header status must NOT be "Closed", "On hold" or "Cancelled"
        if ($rowSO['status'] == 'closed' || $rowSO['status'] == 'on hold' || $rowSO['status'] == 'cancelled') {
            $errArr[] = "The Sales Order status should not be 'closed', 'on hold' or 'cancelled'";
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
    function getRaisePOValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $sales_order_id      = $fn->getReqParam('sales_order_id');
        $company_id_supplier = $fn->getReqParam('company_id_supplier');
        $sales_order_items_ids = $fn->getReqParam('sales_order_items_ids', array());
        $sales_order_items_ids_str = $dbUtil->getArrayAsCommaSeperated($sales_order_items_ids);
        $quantities = $fn->getReqParam('quantities');

        $status = 'success';
        $errMsg = '';

        if (count($sales_order_items_ids) == 0) {
            $status = 'error';
            $errMsg = 'Please choose some items';
            return array($status, $errMsg);
        }

        $errArr = array();

        $lessThan = new Zend_Validate_LessThan(1);
        //Should not enter zero quantity
        foreach ($sales_order_items_ids as $sales_order_items_id) {
            if ($lessThan->isValid($quantities[$sales_order_items_id])) {
                $errArr[] = "Please enter a valid quantity";
                break;
            }
        }

        //Sales Order line status must be "New" or "Customer confirmed"
        $SQL = "
        SELECT COUNT(*) AS count
        FROM sales_order_items soi
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
          AND (soi.status = 'new' OR soi.status = 'customer confirmed')
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($sales_order_items_ids_str)) {
            $errArr[] = "Sales order lines status must be new";
        }

        //must choose items from one supplier only
        $SQL = "
        SELECT COUNT(DISTINCT qr.company_id_supplier) AS supplier_count
        FROM sales_order_items soi
        JOIN quote_request_items qri ON (qri.quote_request_items_id = soi.quote_request_items_id)
        JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        WHERE soi.sales_order_id = {$sales_order_id}
          AND soi.sales_order_items_id IN ({$sales_order_items_ids_str})
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        if ($row['supplier_count'] > 1) {
            $errArr[] = 'Please choose lines from one supplier only';
        }

        //Request Date > today
        $SQL = "
        SELECT COUNT(*) AS count
        FROM sales_order_items soi
        WHERE soi.sales_order_id = {$sales_order_id}
          AND soi.sales_order_items_id IN ({$sales_order_items_ids_str})
          AND soi.request_date >= '{$fn->getISODate()}'
          AND soi.request_date IS NOT NULL
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($sales_order_items_ids_str)) {
            $errArr[] = 'Request date should be valid';
        }

        //Buy Unit Price > 0  (needs to trace back to the selected RFQ)
        $SQL = "
        SELECT COUNT(*) AS count
        FROM sales_order_items soi
        WHERE soi.sales_order_id = {$sales_order_id}
          AND soi.sales_order_items_id IN ({$sales_order_items_ids_str})
          AND soi.buy_unit_price > 0
          AND soi.buy_unit_price IS NOT NULL
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($sales_order_items_ids_str)) {
            $errArr[] = 'Buy unit price must be greater than 0';
        }

        //UOM must NOT be BLANK
        $SQL = "
        SELECT COUNT(*) AS count
        FROM sales_order_items soi
        JOIN product p ON (p.product_id = soi.product_id)
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
          AND (p.unit = '' OR p.unit IS NULL)
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $errArr[] = 'UOM must not be blank';
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
    function getRaisePO() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        list($status, $errMsg) = $this->getRaisePOValidation();
        if ($status != 'success') {
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        $currRate = getCPModelObj('trading_currencyRate');
        $fnsModDeliveryAddress = getCPFnObj('trading_deliveryAddressLink');

        $sales_order_id      = $fn->getReqParam('sales_order_id');
        $company_id_supplier = $fn->getReqParam('company_id_supplier');
        $sales_order_items_ids     = $fn->getReqParam('sales_order_items_ids', array());
        $sales_order_items_ids_str = $dbUtil->getArrayAsCommaSeperated($sales_order_items_ids);
        $quantities = $fn->getReqParam('quantities');

        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);

        if ($rowSO['order_type'] == 'Internal SO') {
            //get company_id_supplier
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
            JOIN sales_order so ON so.sales_order_id = soi.sales_order_id
            JOIN product p ON p.product_id = soi.product_id
            JOIN quote_request_items qri ON qri.quote_request_items_id = p.quote_request_items_id
            JOIN quote_request qr ON qr.quote_request_id = qri.quote_request_id
            LEFT JOIN delivery_address da ON da.delivery_address_id = qr.delivery_address_id
            WHERE soi.sales_order_id = {$sales_order_id}
              AND soi.sales_order_items_id IN ({$sales_order_items_ids_str})
            ";
        } else {
            //get company_id_supplier
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
              AND soi.sales_order_items_id IN ({$sales_order_items_ids_str})
            ";
        }
        $result = $db->sql_query($SQL);
        $rowQR = $db->sql_fetchrow($result);
        $company_id_supplier = $rowQR['company_id_supplier'];

        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);
        $rowSupplier = $fn->getRecordRowByID('company', 'company_id', $rowQR['company_id_supplier']);

        //create RFQ header
        $po_code = 'PO' . $fn->getSequenceFromSettings('m.trading.purchaseOrder.nextCode');
        $purchase_order_date = strtotime(date('Y-m-d'));

        $fa = array();
        $fa['sales_order_id']       = $sales_order_id;
        $fa['po_code']              = $po_code;
        $fa['company_id_supplier']  = $rowQR['company_id_supplier'];
        $fa['contact_id_supplier']  = $rowQR['contact_id_supplier'];
        $fa['payment_terms']        = $rowQR['payment_terms'];
        $fa['notes']                = $rowQR['notes_from_customer'];
        $fa['delivery_address']     = $rowQR['delivery_address'];
        $fa['purchase_order_date']  = date('Y-m-d', $purchase_order_date);
        $fa['buy_currency']         = $rowQR['buy_currency'];
        $fa['status']               = 'new';
        $fa['staff_id']             = $_SESSION['staff_id'];
        $fa['creation_date']        = date('Y-m-d H:i:s');
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
          AND soi.sales_order_items_id IN ($sales_order_items_ids_str)
        ORDER BY soi.line_no
        ";
        $result = $db->sql_query($SQL);

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        while ($rowSOI = $db->sql_fetchrow($result)) {
            $quantity       = $quantities[$rowSOI['sales_order_items_id']];
            $buy_unit_price = $rowSOI['buy_unit_price'];

            $line_no = $fnsModGrp->getNextItemLineNo(
                       'purchase_order_id',
                       $purchase_order_id,
                       'purchase_order_items');

            $exchange_rate_buy = $currRate->getCurrencyExchageRate($rowSupplier['buy_currency'], $cpCfg['m.trading.companyCurrency']);
            $buy_unit_price_base = $buy_unit_price * $exchange_rate_buy;

            $rowQRI = $fn->getRecordRowByID('quote_request_items', 'quote_request_items_id', $rowSOI['quote_request_items_id']);

            $fa = array();
            $fa['purchase_order_id']      = $purchase_order_id;
            $fa['quantity']               = $quantity;
            $fa['product_id']             = $rowSOI['product_id'];
            $fa['sales_order_items_id']   = $rowSOI['sales_order_items_id'];
            $fa['quote_request_items_id'] = $rowSOI['quote_request_items_id'];
            $fa['buy_unit_price']         = $buy_unit_price;
            $fa['buy_unit_price_base']    = $buy_unit_price_base;
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

            $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'purchase_order_items');
            $db->sql_query($SQL);
        }

        $topRm = $fn->getTopRoomName('purchaseOrder');
        $url = "/admin/index.php?_topRm={$topRm}&module=trading_purchaseOrder" .
               "&_action=edit&record_id={$purchase_order_id}";

        $arr = array('status' => 'success', 'returnUrl' => $url);
        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getRaiseShipmentListValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $sales_order_id = $fn->getReqParam('sales_order_id');
        $status = 'success';
        $errMsg = '';

        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);

        $status = 'success';
        $errMsg = '';

        $errArr = array();

        //Sales Order header status must NOT be "Closed", "On hold" or "Cancelled"
        if ($rowSO['status'] == 'closed' || $rowSO['status'] == 'on hold' || $rowSO['status'] == 'cancelled') {
            $errArr[] = "The Sales Order status should not be 'closed', 'on hold' or 'cancelled'";
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
    function getRaiseShipmentValidation() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $sales_order_id        = $fn->getReqParam('sales_order_id');
        $sales_order_items_ids = $fn->getReqParam('sales_order_items_ids', array());
        $sales_order_items_ids_str = $dbUtil->getArrayAsCommaSeperated($sales_order_items_ids);
        $quantities_to_ship        = $fn->getReqParam('quantities_to_ship', array());

        $status = 'success';
        $errMsg = '';

        if (count($sales_order_items_ids) == 0) {
            $status = 'error';
            $errMsg = 'Please choose some items';
            return array($status, $errMsg);
        }

        $errArr = array();

        $lessThan = new Zend_Validate_LessThan(1);
        //Should not enter zero quantity
        foreach ($sales_order_items_ids as $sales_order_items_id) {
            if ($lessThan->isValid($quantities_to_ship[$sales_order_items_id])) {
                $errArr[] = "Please enter a valid quantity";
                break;
            }
        }

        //Sales Order line status must be "Order acknowledged" or "Partially Shipped"
        $SQL = "
        SELECT COUNT(*) AS count
        FROM sales_order_items soi
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
          AND (soi.status = 'order acknowledged' OR soi.status = 'partially shipped')
        ";

        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] < count($sales_order_items_ids)) {
            $errArr[] = "Sales Order line status must be 'order acknowledged' or 'partially shipped'";
        }

        //Shipped Quantity < Ordered Quantity
        //"Shipped Qty"+"Quantity To Ship Now" MUST NOT EXCEED "Order Quantity"
        $SQL = "
        SELECT soi.sales_order_items_id
              ,soi.quantity
              ,(SELECT SUM(si.quantity_shipped)
                FROM shipment_items si
                WHERE si.sales_order_items_id = soi.sales_order_items_id) AS total_shipped_quantity
        FROM sales_order_items soi
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
        ";
        $result = $db->sql_query($SQL);
        while ($rowSOI = $db->sql_fetchrow($result)) {
            $quantity_to_ship = $quantities_to_ship[$rowSOI['sales_order_items_id']];
            if ($quantity_to_ship + $rowSOI['total_shipped_quantity'] > $rowSOI['quantity']) {
                $errArr[] = "<b>Shipped Qty</b> + <b>Quantity To Ship Now</b> must not exceed <b>Order Quantity</b>";
                break;
            }
        }

        //Delivery Terms should NOT be BLANK
        $SQL = "
        SELECT COUNT(*) AS count
        FROM sales_order_items soi
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
          AND (soi.delivery_terms = '' OR soi.delivery_terms IS NULL)
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $errArr[] = "Delivery terms should not be blank";
        }

        //All the selected Delivery Terms should be the same
        $SQL = "
        SELECT DISTINCT delivery_terms
        FROM sales_order_items soi
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $numRows = $db->sql_numrows($result);
        if ($numRows > 1) {
            $errArr[] = "All selected Delivery terms should not be the same";
        }

        //Shipping Method should NOT be BLANK
        $SQL = "
        SELECT COUNT(*) AS count
        FROM sales_order_items soi
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
          AND (soi.shipping_method = '' OR soi.shipping_method IS NULL)
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $errArr[] = "Shipping Method should not be blank";
        }

        //All the selected Shipping Method should be the same
        $SQL = "
        SELECT DISTINCT shipping_method
        FROM sales_order_items soi
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $numRows = $db->sql_numrows($result);
        if ($numRows > 1) {
            $errArr[] = "All selected Shipping Method should not be the same";
        }

        //UOM must NOT be BLANK
        $SQL = "
        SELECT COUNT(*) AS count
        FROM sales_order_items soi
        JOIN product p ON (p.product_id = soi.product_id)
        WHERE soi.sales_order_items_id IN ($sales_order_items_ids_str)
          AND (p.unit = '' OR p.unit IS NULL)
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $errArr[] = 'UOM must not be blank';
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
    function getRaiseShipment() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        list($status, $errMsg) = $this->getRaiseShipmentValidation();
        if ($status != 'success') {
            return $cpUtil->getJsonText($status, '', $errMsg);
        }

        $sales_order_id            = $fn->getReqParam('sales_order_id');
        $sales_order_items_ids     = $fn->getReqParam('sales_order_items_ids', array());
        $sales_order_items_ids_str = $dbUtil->getArrayAsCommaSeperated($sales_order_items_ids);
        $quantities_to_ship        = $fn->getReqParam('quantities_to_ship', array());

        $rowSalesOrder = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);
        $rowCustomer = $fn->getRecordRowByID('company', 'company_id', $rowSalesOrder['company_id_customer']);

        $shipment_code = 'S' . $fn->getSequenceFromSettings('m.trading.shipment.nextCode');

        //create shipment
        $fa = array();
        $fa['sales_order_id']      = $sales_order_id;
        $fa['shipment_code']       = $shipment_code;
        $fa['shipment_date']       = date('Y-m-d');
        $fa['company_id']          = $rowSalesOrder['company_id_customer'];
        $fa['contact_id']          = $rowSalesOrder['contact_id_customer'];
        $fa['delivery_address_id'] = $rowSalesOrder['delivery_address_id'];
        $fa['shipping_method']     = $rowSalesOrder['shipping_method'];

        $fa['delivery_terms']           = $rowSalesOrder['delivery_terms'];
        $fa['consignee_name']           = $rowSalesOrder['consignee_name'];
        $fa['consignee_address']        = $rowSalesOrder['consignee_address'];
        $fa['consignee_phone']          = $rowSalesOrder['consignee_phone'];
        $fa['consignee_contact_person'] = $rowSalesOrder['consignee_contact_person'];

        $fa['sell_currency']       = $rowSalesOrder['sell_currency'];
        $fa['status']              = 'new';
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'shipment');
        $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'shipment');
        $db->sql_query($SQL);
        $shipment_id = $db->sql_nextid();

        //create Quote line items
        $SQL = "
        SELECT soi.*
        FROM sales_order_items soi
        WHERE soi.sales_order_id = {$sales_order_id}
          AND soi.sales_order_items_id IN ($sales_order_items_ids_str)
        ORDER BY soi.line_no
        ";
        $result = $db->sql_query($SQL);
        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        //update delivery terms & shippping method in the shipment header from
        //first SO line
        $rowSOI = $db->sql_fetchrow($result);
        $fa = array();
        $fa['delivery_terms']  = $rowSOI['delivery_terms'];
        $fa['shipping_method'] = $rowSOI['shipping_method'];

        $whereCondition = "WHERE shipment_id = {$shipment_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'shipment', $whereCondition);
        $db->sql_query($SQL);

        $db->sql_rowseek(0, $result);
        while ($rowSOI = $db->sql_fetchrow($result)) {
            $quantity_to_ship = $quantities_to_ship[$rowSOI['sales_order_items_id']];

            $line_no = $fnsModGrp->getNextItemLineNo('shipment_id', $shipment_id, 'shipment_items');
            $fa = array();
            $fa['shipment_id']          = $shipment_id;
            $fa['product_id']           = $rowSOI['product_id'];
            $fa['sales_order_items_id'] = $rowSOI['sales_order_items_id'];
            $fa['line_no']              = $line_no;
            $fa['quantity_shipped']     = $quantity_to_ship;
            $fa['sell_unit_price']      = $rowSOI['sell_unit_price'];
            $fa['status']               = 'new';
            $fa['country_of_origin']    = $rowSOI['country_of_origin'];
            $fa['packing_details']      = $rowSOI['packing_details'];
            $fa['carton_dimensions']    = $rowSOI['carton_dimensions'];
            $fa['gross_weight']         = $rowSOI['gross_weight'];
            $fa['net_weight']           = $rowSOI['net_weight'];
            $fa['total_volume']         = $rowSOI['total_volume'];

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'shipment_items');
            $db->sql_query($SQL);
            $shipment_items_id = $db->sql_nextid();
        }

        $topRm = $fn->getTopRoomName('shipment');
        $url = "index.php?_topRm={$topRm}&module=trading_shipment" .
               "&_action=detail&record_id={$shipment_id}";
        $arr = array('status' => 'success', 'returnUrl' => $url);
        return $cpUtil->getJsonFromArray($arr);

    }

    /**
     *
     * This is supposed to be in the inventory file.
     * Because of a trouble I have put it here temporarily. Need to fix.
     */
    function getActualSellPrice($inventory_id) {
        $rowInv = $fn->getRecordRowByID('inventory', 'inventory_id', $inventory_id);
        $sell_unit_price_actual = $rowInv['retail_unit_price'];
        if ($rowInv['retail_unit_price_discount']) {
            $sell_unit_price_actual = $rowInv['retail_unit_price_discount'];
        }

        return $sell_unit_price_actual;
    }


    function getSaveInventory() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');
        $statusArr = $fn->getReqParam('inv_status', array());
        $locationArr = $fn->getReqParam('location', array());

        foreach ($locationArr as $inventory_id => $location) {
            $status = $statusArr[$inventory_id];
            $SQL = "
            UPDATE inventory
            SET status = '{$status}'
               ,location = '{$location}'
            WHERE inventory_id = {$inventory_id}
            ";
            $db->sql_query($SQL);
        }
        return $cpUtil->getJsonText('success', 'Saved');
    }

    function getUpdateSellPriceFromQuote() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sales_order_id = $fn->getReqParam('sales_order_id');

        $SQL = "
        UPDATE sales_order_items soi
        JOIN quote_items qi ON qi.quote_items_id = soi.quote_items_id
        SET soi.sell_unit_price = qi.sell_unit_price
           ,soi.sell_unit_price_base = qi.sell_unit_price_base
        WHERE soi.sales_order_id = {$sales_order_id}
        ";
        $db->sql_query($SQL);

        return $cpUtil->getJsonText('success', 'Price updated');
    }

    function getValidateEditProductItemLink() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $sales_order_items_id = $fn->getReqParam('sales_order_items_id');

        $SQL = "
        SELECT 1
        FROM inventory i
        WHERE i.sales_order_items_id = {$sales_order_items_id}
        ";
        $row = $fn->getRecordBySQL($SQL);
        $status = 'success';
        $errorMsg = '';
        if ($row) {
            $status = 'error';
            $errorMsg = 'Inventory records already created you cannot edit this any more';
        }

        return $cpUtil->getJsonText($status, '', $errorMsg);
    }
}
