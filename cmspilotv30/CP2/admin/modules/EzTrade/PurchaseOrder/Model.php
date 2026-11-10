<?
class CP_Admin_Modules_EzTrade_PurchaseOrder_Model extends CP_Common_Lib_ModuleModelAbstract
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
        LEFT JOIN (sales_order so) ON (po.sales_order_id = so.sales_order_id)
        LEFT JOIN (company com)    ON (po.company_id_supplier = com.company_id)
        LEFT JOIN (contact c) ON (po.contact_id_supplier = c.contact_id)
        LEFT JOIN staff s ON (po.staff_id = s.staff_id)
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

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $po_code = 'PO-' . $fn->getSequenceFromSettings('m.trading.purchaseOrder.nextCode');

        $fa = $this->getFields();
        $fa['po_code'] = $po_code;
        $fa['purchase_order_date'] = date('Y-m-d');
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
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

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        if ($fa['status'] == 'confirmed') {
            $this->createInventoryRecords($id);
        }

        $fn->returnAfterNewSave($id);
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

        return $fa;
    }

    /**
     *
     */
    function getEzTradePurchaseOrderEzTradeProductLinkSQL($id) {
        $SQL = "
        SELECT poi.purchase_order_items_id
              ,p.product_id
              ,CONCAT_WS('-', po.po_code, poi.line_no) AS line_no
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,poi.quantity
              ,po.buy_currency
              ,poi.buy_unit_price
              ,poi.buy_unit_price * poi.quantity AS buy_price
              ,poi.quantity_delivered
              ,poi.status
              ,(SELECT SUM(quantity) FROM purchase_order_items WHERE purchase_order_id = {$id}) AS quantity_sum
              ,(SELECT SUM(buy_unit_price * quantity) FROM purchase_order_items WHERE purchase_order_id = {$id}) AS buy_price_sum
        FROM purchase_order_items poi
        JOIN purchase_order po ON (po.purchase_order_id = poi.purchase_order_id)
        JOIN product p ON (p.product_id = poi.product_id)
        WHERE poi.purchase_order_id = {$id}
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

//        $sales_order_id      = $fn->getReqParam('sales_order_id');
//        $company_id_supplier = $fn->getReqParam('company_id_supplier');
//        $sales_order_items_ids     = $fn->getReqParam('sales_order_items_ids', array());
//        $sales_order_items_ids_str = $dbUtil->getArrayAsCommaSeperated($sales_order_items_ids);
//        $quantities = $fn->getReqParam('quantities');

        //create inventory records
        $SQL = "
        SELECT poi.sales_order_items_id
              ,so.sales_order_id
              ,poi.purchase_order_id
              ,poi.purchase_order_items_id
              ,poi.quote_items_id
              ,poi.product_id
              ,poi.quantity
        FROM purchase_order_items poi
        JOIN purchase_order po ON po.purchase_order_id = poi.purchase_order_id
        JOIN sales_order so ON so.sales_order_id = po.sales_order_id
        WHERE poi.purchase_order_items_id = {$purchase_order_id}
        ORDER BY poi.line_no
        ";
        $result = $db->sql_query($SQL);

        while ($rowPOI = $db->sql_fetchrow($result)) {
            $quantity = $rowPOI['quantity'];
            $count = 1;
            while ($count <= $quantity) {
                $fa = array();
                $fa['purchase_order_id']       = $rowPOI['purchase_order_id'];
                $fa['product_id']              = $rowPOI['product_id'];
                $fa['sales_order_id']          = $rowPOI['sales_order_id'];
                $fa['sales_order_items_id']    = $rowPOI['sales_order_items_id'];
                $fa['purchase_order_id']       = $rowPOI['purchase_order_id'];
                $fa['purchase_order_items_id'] = $rowPOI['purchase_order_id'];
                //$fa['enquiry_id']              = $rowPOI['enquiry_id'];
                //$fa['enquiry_product_id']      = $rowPOI['enquiry_product_id'];
                //$fa['quote_id']                = $rowPOI['quote_id'];
                //$fa['quote_items_id']          = $rowPOI['quote_items_id'];
                $fa['creation_date']           = date('Y-m-d H:i:s');
                $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'inventory');
                $db->sql_query($SQL);
                $count++;
            }
        }

    }


}
