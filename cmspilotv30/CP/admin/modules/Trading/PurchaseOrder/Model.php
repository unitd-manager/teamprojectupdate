<?
class CP_Admin_Modules_Trading_PurchaseOrder_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT po.*
              ,com.company_name AS supplier_name
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name_supplier
              ,so.so_code
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name

              ,(SELECT SUM(poi.buy_unit_price * poi.quantity)
                FROM purchase_order_items poi
                WHERE poi.purchase_order_id = po.purchase_order_id) AS po_sum_buy_price

              ,(SELECT SUM(poi.buy_unit_price_base * poi.quantity)
                FROM purchase_order_items poi
                WHERE poi.purchase_order_id = po.purchase_order_id) AS po_sum_base

              ,0 AS total_paid_amount

        FROM purchase_order po
        LEFT JOIN sales_order so ON po.sales_order_id = so.sales_order_id
        LEFT JOIN company com ON po.company_id_supplier = com.company_id
        LEFT JOIN contact c ON po.contact_id_supplier = c.contact_id
        LEFT JOIN staff s ON po.staff_id = s.staff_id
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
            $searchVar->sqlSearchVar[] = "po.purchase_order_id = {$tv['record_id']}";
        } else {
            if ($status != "") {
                $searchVar->sqlSearchVar[] = "po.status = '{$status}'";
            }
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "po.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(po.flag != 1 OR po.flag IS null)";
            }
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       po.po_code  LIKE '%{$tv['keyword']}%'
                    OR so.so_code  LIKE '%{$tv['keyword']}%'
                    OR com.company_name LIKE '%{$tv['keyword']}%'
                    OR s.first_name LIKE '%{$tv['keyword']}%'
                    OR s.last_name LIKE '%{$tv['keyword']}%'
                    OR po.status LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "po.creation_date DESC";

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_id_supplier', 'Please choose the Supplier');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getDefaultValuesForAdd(){
        $fn = Zend_Registry::get('fn');
        $po_code = 'PO-' . $fn->getSequenceFromSettings('m.trading.purchaseOrder.nextCode');

        $fa['po_code'] = $po_code;
        $fa['purchase_order_date'] = date('Y-m-d');

        return $fa;
    }

    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

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
        $validate->validateData('buy_currency', 'Please choose buy currency');

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

        $row = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $id);
        $prevStatus = $row['status'];
        $newStatus = $fa['status'];
        $createInventoryRecords = false;
        if ($prevStatus != $newStatus && $newStatus == 'confirmed') {
            $createInventoryRecords = true;
        }
     
        if ($createInventoryRecords) {
            $this->createInventoryRecords($id);
        }

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
        $fa = $fn->addToFieldsArray($fa, 'po_code');
        $fa = $fn->addToFieldsArray($fa, 'company_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'purchase_order_date');
        $fa = $fn->addToFieldsArray($fa, 'buy_currency');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'delivery_address');
        $fa = $fn->addToFieldsArray($fa, 'consignee_name');
        $fa = $fn->addToFieldsArray($fa, 'consignee_address');
        $fa = $fn->addToFieldsArray($fa, 'consignee_phone');
        $fa = $fn->addToFieldsArray($fa, 'consignee_contact_person');
        $fa = $fn->addToFieldsArray($fa, 'deposit_paid');
        $fa = $fn->addToFieldsArray($fa, 'port_of_origin');
        $fa = $fn->addToFieldsArray($fa, 'deposit_note');
        $fa = $fn->addToFieldsArray($fa, 'shipment_no');
        $fa = $fn->addToFieldsArray($fa, 'required_delivery_date');

        return $fa;
    }

    /**
     *
     */
    function getTradingPurchaseOrderTradingProductLinkSQL($id) {
        $editInventorySQL = "
        CONCAT_WS('',
                  '<a href=\'javascript:cpm.trading.purchaseOrder.editInventoryForm(',
                  poi.purchase_order_items_id,
                  ')\'>Edit Inventory</a>'
                  )
        ";

        $SQL = "
        SELECT poi.purchase_order_items_id
              ,p.product_id
              ,CONCAT_WS('-', po.po_code, poi.line_no) AS line_no
              ,p.product_code
              ,p.web_code
              ,p.title AS product_name
              ,p.unit
              ,poi.quantity
              ,po.buy_currency
              ,poi.buy_unit_price
              ,poi.buy_unit_price * poi.quantity AS buy_price
              ,poi.quantity_delivered
              ,poi.status
              ,{$editInventorySQL}

              ,(SELECT SUM(quantity) FROM purchase_order_items WHERE purchase_order_id = {$id}) AS quantity_sum
              ,(SELECT SUM(buy_unit_price * quantity) FROM purchase_order_items WHERE purchase_order_id = {$id}) AS buy_price_sum

        FROM purchase_order_items poi
        JOIN purchase_order po ON (po.purchase_order_id = poi.purchase_order_id)
        JOIN product p ON (p.product_id = poi.product_id)
        WHERE poi.purchase_order_id = {$id}
        ORDER BY p.web_code
                ,poi.line_no
        ";

        return $SQL;
    }

    /**
     *
     */
    function createInventoryRecords($purchase_order_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //create inventory records
        $SQL = "
        SELECT poi.sales_order_items_id
              ,so.sales_order_id
              ,poi.purchase_order_id
              ,poi.purchase_order_items_id
              ,poi.quote_items_id
              ,poi.product_id
              ,poi.quantity
              ,so.company_id_customer
              ,po.company_id_supplier
              ,soi.sell_unit_price
        FROM purchase_order_items poi
        JOIN purchase_order po ON po.purchase_order_id = poi.purchase_order_id
        JOIN sales_order so ON so.sales_order_id = po.sales_order_id
        JOIN sales_order_items soi ON soi.sales_order_items_id = poi.sales_order_items_id
        JOIN company c ON c.company_id = so.company_id_customer
        WHERE poi.purchase_order_id = {$purchase_order_id}
        ORDER BY poi.line_no
        ";
        $result = $db->sql_query($SQL);

        while ($rowPOI = $db->sql_fetchrow($result)) {
            $quantity = $rowPOI['quantity'];
            $count = 1;

            $SQL = "
            SELECT MAX(serial_no) AS max_serial_no
            FROM inventory
            WHERE product_id = {$rowPOI['product_id']}
            ";
            $row = $fn->getRecordBySQL($SQL);

            $serial = $row['max_serial_no'] + 1;
            while ($count <= $quantity) {
                $status   = 'available';
                $location = 'in production';

                $fa = array();
                $fa['purchase_order_id']       = $rowPOI['purchase_order_id'];
                $fa['product_id']              = $rowPOI['product_id'];
                $fa['sales_order_id']          = $rowPOI['sales_order_id'];
                $fa['sales_order_items_id']    = $rowPOI['sales_order_items_id'];
                $fa['purchase_order_id']       = $rowPOI['purchase_order_id'];
                $fa['purchase_order_items_id'] = $rowPOI['purchase_order_items_id'];
                $fa['company_id_customer']     = $rowPOI['company_id_customer'];
                $fa['company_id_supplier']     = $rowPOI['company_id_supplier'];
                $fa['serial_no']               = str_pad($serial, 5, '0', STR_PAD_LEFT);
                $fa['status']                  = $status;
                $fa['location']                = $location;
                $fa['retail_unit_price']       = $rowPOI['sell_unit_price'];
                $fa['creation_date']           = date('Y-m-d H:i:s');
                $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'inventory');
                $db->sql_query($SQL);
                $count++;
                $serial++;
            } //while 2
        } //while 1

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
        $locationArr = $fn->getReqParam('inv_location', array());

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

    function getValidateEditProductItemLink() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');

        $SQL = "
        SELECT 1
        FROM inventory i
        WHERE i.purchase_order_items_id = {$purchase_order_items_id}
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
