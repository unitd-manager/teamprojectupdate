<?
class CP_Admin_Modules_Pos_PurchaseOrder_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT po.*
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name_supplier
              ,s.staff_name
              ,sh.title AS shop_name
              ,w.name AS warehouse_name
              ,0 AS total_paid_amount

        FROM purchase_order po
        LEFT JOIN contact c ON po.contact_id_supplier = c.contact_id
        LEFT JOIN staff s ON po.staff_id = s.staff_id
        LEFT JOIN shop sh ON po.shop_id = sh.shop_id
        LEFT JOIN warehouse w ON po.warehouse_id = w.warehouse_id
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

        //$fa = array_merge($this->getFields(), $this->getDefaultValuesForAdd());

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);

        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $vendor_id = $fn->getReqParam('vendor_id');   
        $warehouse_id = $fn->getReqParam('warehouse_id');   
        $shop_id = $fn->getReqParam('shop_id');   
        $po_code = $fn->getReqParam('po_code');   

        $sqlVendor = $fn->getRecordByCondition('vendor', "code='{$vendor_id}'");
        $sqlWarehouse = $fn->getRecordByCondition('warehouse', "code='{$warehouse_id}'");
        $sqlShop = $fn->getRecordByCondition('shop', "shop_id='{$shop_id}'");

        $fa = $this->getFields();
        $fa['vendor_id'] = $sqlVendor['vendor_id'];
        $fa['vendor_name'] = $sqlVendor['title'];
        $fa['warehouse_id'] = $sqlWarehouse['warehouse_id'];

        $po_no = $fn->getSettingsRowByKey('pfxPurchaseOrder');

        $length = $po_no['length'] - $po_no['starting_no'];

        $i = 0;
        $extraNo = '';
        while ($i < $length) {
            $extraNo .= '0';
            $i++;
        } 

        if ($po_no['add_separator'] == 1){
            $poNoValue = $po_no['value'] . '_' . $sqlShop['code'] . '_' . $extraNo . $po_no['starting_no'];
        } else {
            $poNoValue = $po_no['value'] . $sqlShop['code'] . $extraNo . $po_no['starting_no'];
        }

        if ($po_no['auto_generate_no'] == 1) {
            $fa['po_code'] = $poNoValue;
        } else if ($po_no['auto_generate_no'] == 2) {
            if($po_code == ''){
                $fa['po_code'] = $poNoValue;
            } else {
                $fa['po_code'] = $po_code;
            }
        } else {
            $fa['po_code'] = $po_code;
        }

        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        //$row = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $id);
        //$prevStatus = $row['status'];
        //$newStatus = $fa['status'];
     
        //$this->getPurchaseOrderItemSubmit();

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
        //$fa = $fn->addToFieldsArray($fa, 'po_code');
        $fa = $fn->addToFieldsArray($fa, 'purchase_order_date');
        $fa = $fn->addToFieldsArray($fa, 'vendor_name');
        $fa = $fn->addToFieldsArray($fa, 'reference_no');
        $fa = $fn->addToFieldsArray($fa, 'payment_id');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'shipment_id');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'shop_id');
        $fa = $fn->addToFieldsArray($fa, 'delivery_address');
        $fa = $fn->addToFieldsArray($fa, 'address');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'contact_person');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'status');

        return $fa;
    }

    /**
     *
     */
    function getInsertPurchaseOrderItems(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $fa = array();
        $fa['purchase_order_id']     = $purchase_order_id;

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'purchase_order_items');
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getPurchaseOrderItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');
        $sku_no = $fn->getReqParam('sku_no');
        $vendor_sku_no = $fn->getReqParam('vendor_sku_no');
        $item_title = $fn->getReqParam('item_title');
        $qty = $fn->getReqParam('qty');
        $unit_price = $fn->getReqParam('unit_price');
        $discount = $fn->getReqParam('discount');
        
        if ($qty == ''){
            $qty = 0;
        }
        
        if (!$this->getPurchaseOrderItemValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $fa = array();
        $fa['sku_no']     = $sku_no;
        $fa['vendor_sku_no'] = $vendor_sku_no;
        $fa['item_title'] = $item_title;
        $fa['qty']        = $qty;
        $fa['unit_price'] = $unit_price;
        $fa['discount']   = $discount;

        $whereCondition = "WHERE purchase_order_items_id = '{$purchase_order_items_id}'";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "purchase_order_items", $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getPurchaseOrderItemValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
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
    function getDeletePurchaseOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');
        
        $SQL    = "DELETE FROM purchase_order_items WHERE purchase_order_items_id = {$purchase_order_items_id}";
        $result = $db->sql_query($SQL);

    }

    /**
     */
    function getUpdateSkuNoPurchaseOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');
        $sku_no = $fn->getReqParam('sku_no');

        $sqlProductItem = $fn->getRecordByCondition('product_item', "sku_no='{$sku_no}'");
        
        if($sqlProductItem['sku_no'] == ''){
            return 'Sku number does not exist in the database';
        }
        
        $SQL    = "
        UPDATE purchase_order_items 
        set sku_no = '{$sku_no}' 
        WHERE purchase_order_items_id = {$purchase_order_items_id}
        "; 
        $result = $db->sql_query($SQL);
    }

    /**
     */
    function getUpdateVendorSkuNoPurchaseOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');
        $vendor_sku_no = $fn->getReqParam('vendor_sku_no');
        
        $SQL    = "
        UPDATE purchase_order_items 
        set vendor_sku_no = {$vendor_sku_no} 
        WHERE purchase_order_items_id = {$purchase_order_items_id}
        "; 
        $result = $db->sql_query($SQL);
    }

    /**
     */
    function getUpdateQtyPurchaseOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');
        $qty = $fn->getReqParam('qty');
        
        $SQL    = "
        UPDATE purchase_order_items 
        set qty = {$qty} 
        WHERE purchase_order_items_id = {$purchase_order_items_id}
        "; 
        $result = $db->sql_query($SQL);

        $poi = $fn->getRecordRowByID('purchase_order_items', 'purchase_order_items_id', $purchase_order_items_id);
        $total = ($qty * $poi['unit_price']) - $poi['discount'];
        
        return $total;

    }

    /**
     */
    function getUpdateUnitPricePurchaseOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');
        $unit_price = $fn->getReqParam('unit_price');
        
        $SQL    = "
        UPDATE purchase_order_items 
        set unit_price = {$unit_price} 
        WHERE purchase_order_items_id = {$purchase_order_items_id}
        "; 
        $result = $db->sql_query($SQL);

        $poi = $fn->getRecordRowByID('purchase_order_items', 'purchase_order_items_id', $purchase_order_items_id);
        $total = ($poi['qty'] * $unit_price) - $poi['discount'];
        
        return $total;
    }

    /**
     */
    function getUpdateDiscountPurchaseOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');
        $discount = $fn->getReqParam('discount');
        
        $SQL    = "
        UPDATE purchase_order_items 
        set discount = {$discount} 
        WHERE purchase_order_items_id = {$purchase_order_items_id}
        "; 
        $result = $db->sql_query($SQL);

        $poi = $fn->getRecordRowByID('purchase_order_items', 'purchase_order_items_id', $purchase_order_items_id);
        $total = ($poi['qty'] * $poi['unit_price']) - $discount;
        
        return $total;
    }

    /**
     *
     */
    function getUpdateOverallDiscountPurchaseOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $overall_discount = $fn->getReqParam('overall_discount');
        
        $SQL    = "
        UPDATE purchase_order
        set overall_discount = {$overall_discount} 
        WHERE purchase_order_id = {$purchase_order_id}
        "; 
        $result = $db->sql_query($SQL);

    }

    /**
     *
     */
    function getTotalValues(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $purchase_order_id = isset($_SESSION['purchase_order_id']) ? $_SESSION['purchase_order_id']  : '';
        $discount_total= '';

        $arr = array('subTotal' => 0, 'discTotal' => 0, 'lessAmount' => 0, 'netTotal' => 0, 'overallDiscount' => 0);

        $decimal_length = $fn->getSettingsValueByKey('numDecimalLength');
        
        if($purchase_order_id != ''){

            $order = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $purchase_order_id);

            $SQL = "
            SELECT SUM(qty * unit_price) AS sub_total
                  ,SUM(discount) AS discount_total
            FROM purchase_order_items
            WHERE purchase_order_id = '{$purchase_order_id}'
            ";
            $result = $db->sql_query($SQL);
            while ($row = $db->sql_fetchrow($result)){
                $arr['subTotal']    = number_format($row['sub_total'] - $row['discount_total'], $decimal_length);
                $arr['discTotal']   = $row['discount_total'];
                $arr['lessAmount'] = $row['discount_total'];
                $netTotal  = $row['sub_total'] - $row['discount_total'];
            }
            if($order['overall_discount'] == ''){
                $arr['overallDiscount'] = 0;
            } else {
                $arr['overallDiscount'] =  ($netTotal *  $order['overall_discount'])/100;
            }
            $arr['netTotal']  = number_format($netTotal - $arr['overallDiscount'], $decimal_length);
        }
        
        //return json_encode($arr);
        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getPopulateVendorName() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $vendor_code = $fn->getReqParam('vendor_code');
        
        $SQL    = "SELECT title FROM vendor WHERE code = '{$vendor_code}'";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['title'];

    }

    /**
     *
     */
    function getPopulateStaffName() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $staff_id = $fn->getReqParam('staff_id');
        
        $SQL    = "
        SELECT staff_name
        FROM staff 
        WHERE staff_id = {$staff_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['staff_name'];

    }

    /**
     *
     */
    function getPopulateShopName() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $shop_id = $fn->getReqParam('shop_id');
        $arr = array('title' => '', 'address' => '', 'phone' => '');
        
        $SQL    = "SELECT * FROM shop WHERE shop_id = '{$shop_id}'";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $arr['title']   = $row['title'];
        $arr['address'] = $row['address'];
        $arr['phone'] = $row['telephone'];
        
        return $cpUtil->getJsonFromArray($arr);

    }

    /**
     *
     */
    function getPopulateWarehouseName() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $warehouse_code = $fn->getReqParam('warehouse_code');
        
        $SQL    = "SELECT name FROM warehouse WHERE code = '{$warehouse_code}'";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['name'];

    }

    /**
     *
     */
    function getDeliveryUpdate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rowId = $fn->getReqParam('row_id');
        $SQL    = "UPDATE purchase_order SET status = 'Delivery' WHERE purchase_order_id = {$rowId}";
        $result = $db->sql_query($SQL);

        return 'Delivery';
    }

    /**
     *
     */
    function getOrderNoLocation() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $arr = array('poCode' => '', 'shopTitle' => '');

        $rowId = $fn->getReqParam('row_id');
        
        $SQL    = "
        SELECT po.po_code
              ,po.shop_id
        FROM purchase_order po
        WHERE po.purchase_order_id = {$rowId}";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $arr['poCode']    = $row['po_code'];
        $arr['shopTitle'] = $row['shop_id'];

        return $cpUtil->getJsonFromArray($arr);
    }
}
