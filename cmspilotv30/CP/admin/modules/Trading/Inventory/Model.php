<?
class CP_Admin_Modules_Trading_Inventory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT DISTINCT
               i.*
              ,p.product_code
              ,p.web_code
              ,p.title AS product_name
              ,p.collection_name
              ,p.dimension_h
              ,p.dimension_w
              ,p.dimension_d
              ,p.cbm_per_pc
              ,p.hardware
              ,p.material
              ,p.color
              ,p.color_inside
              ,e.enquiry_code
              ,so.so_code
              ,so.sell_currency
              ,soInv.so_code AS so_code_inventory
              ,po.po_code
              ,po.buy_currency
              ,s.shipment_code
              ,c.company_name AS customer_name
              ,cInv.company_name AS customer_name_inventory
              ,cS.company_name AS supplier_name
              ,poi.buy_unit_price
              ,pt.pricing_type
              ,ppt.sell_unit_price_base AS retail_unit_price_ex_vat
              ,ppt2.sell_unit_price_base AS retail_unit_price_inc_vat
        FROM inventory i
        LEFT JOIN product p ON p.product_id = i.product_id
        LEFT JOIN enquiry e ON e.enquiry_id = i.enquiry_id
        LEFT JOIN sales_order so ON so.sales_order_id = i.sales_order_id
        LEFT JOIN sales_order soInv ON soInv.sales_order_id = i.sales_order_id_inventory
        LEFT JOIN sales_order_items soi ON soi.sales_order_items_id = i.sales_order_items_id
        LEFT JOIN purchase_order po ON po.purchase_order_id = i.purchase_order_id
        LEFT JOIN purchase_order_items poi ON poi.purchase_order_items_id = i.purchase_order_items_id
        LEFT JOIN shipment s ON s.shipment_id = i.shipment_id
        LEFT JOIN company c ON i.company_id_customer = c.company_id
        LEFT JOIN company cInv ON cInv.company_id = i.company_id_customer_inventory
        LEFT JOIN pricing_type pt ON pt.pricing_type_id = cInv.pricing_type_id
        LEFT JOIN company cS ON i.company_id_supplier = cS.company_id
        LEFT JOIN (pricing_type pt2, product_pricing_type ppt)
           ON ppt.product_id = p.product_id AND
              ppt.pricing_type_id = pt2.pricing_type_id AND
              pt2.record_type = 'no_tax'
        LEFT JOIN (pricing_type pt3, product_pricing_type ppt2)
           ON ppt2.product_id = p.product_id AND
              ppt2.pricing_type_id = pt3.pricing_type_id AND
              pt3.record_type = 'has_tax'

        ";
        return $SQL;
    }

    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $product_id      = $fn->getReqParam('product_id');
        $category_id     = $fn->getReqParam('category_id');
        $sub_category_id = $fn->getReqParam('sub_category_id');
        $collection_name = $fn->getReqParam('collection_name');
        $status = $fn->getReqParam('status');
        $location = $fn->getReqParam('location');
        $color = $fn->getReqParam('color');
        $sales_order_id = $fn->getReqParam('sales_order_id');
        $inventoryItems = $fn->getReqParam('inventoryItems');
        $on_sale = $fn->getReqParam('on_sale');
        $damaged = $fn->getReqParam('damaged');

        if ($product_id != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$product_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.inventory_id = {$tv['record_id']}";
        } else {
            if ($linkRecType != '') {
                $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.product_id');
            }
            if ($category_id != ''){
                $searchVar->sqlSearchVar[] = "p.category_id = {$category_id}";
            }
            if ($sub_category_id != ''){
                $searchVar->sqlSearchVar[] = "p.sub_category_id = {$sub_category_id}";
            }
            if ($collection_name != ''){
                $searchVar->sqlSearchVar[] = "p.collection_name = '{$collection_name}'";
            }
            if ($status != ''){
                $searchVar->sqlSearchVar[] = "i.status = '{$status}'";
            }
            if ($location != ''){
                $searchVar->sqlSearchVar[] = "i.location = '{$location}'";
            }
            if ($color != ''){
                $searchVar->sqlSearchVar[] = "p.color = '{$color}'";
            }
            if ($on_sale != ''){
                $searchVar->sqlSearchVar[] = "i.on_sale = '{$on_sale}'";
            }
            if ($damaged != ''){
                $searchVar->sqlSearchVar[] = "i.damaged = '{$damaged}'";
            }
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "i.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(i.flag != 1 OR i.flag IS null)";
            }
            if ($tv['special_search'] == "Flagged - Blue") {
                $searchVar->sqlSearchVar[] = "i.flag_blue = 1";
            }
            if ($tv['special_search'] == "Not-Flagged - Blue") {
                $searchVar->sqlSearchVar[] = "(i.flag_blue != 1 OR i.flag_blue IS null)";
            }
            if ($tv['special_search'] == "Flagged - Green") {
                $searchVar->sqlSearchVar[] = "i.flag_green = 1";
            }
            if ($tv['special_search'] == "Not-Flagged - Green") {
                $searchVar->sqlSearchVar[] = "(i.flag_green != 1 OR i.flag_green IS null)";
            }
            //show inventory items linked
            if ($inventoryItems == '1'){
                if ($sales_order_id != ''){
                    $searchVar->sqlSearchVar[] = "i.sales_order_id_inventory = {$sales_order_id}";
                }
            } else {
                if ($sales_order_id != ''){
                    $searchVar->sqlSearchVar[] = "i.sales_order_id = {$sales_order_id}";
                }
            }
            if ($tv['keyword'] != ""){
                $searchVar->sqlSearchVar[] = "
                (  p.title LIKE '%{$tv['keyword']}%'
                OR p.product_code LIKE '%{$tv['keyword']}%'
                OR p.web_code LIKE '%{$tv['keyword']}%'
                OR p.collection_name LIKE '%{$tv['keyword']}%'
                OR p.color LIKE '%{$tv['keyword']}%'
                OR p.material LIKE '%{$tv['keyword']}%'
                OR i.status LIKE '%{$tv['keyword']}%'
                OR so.so_code LIKE '%{$tv['keyword']}%'
                OR po.po_code LIKE '%{$tv['keyword']}%'
                OR c.company_name LIKE '%{$tv['keyword']}%'
                OR cS.company_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }


    /**
     *
     */
    function getNewValidate() {
        $db = Zend_Registry::get('db');
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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getEditValidate() {
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('status', 'Please choose Status');
        $validate->validateData('location', 'Please choose Location');
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
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'location');
        $fa = $fn->addToFieldsArray($fa, 'damaged');
        $fa = $fn->addToFieldsArray($fa, 'on_sale');
        $fa = $fn->addToFieldsArray($fa, 'retail_unit_price_discount');
        $fa = $fn->addToFieldsArray($fa, 'sell_unit_price_actual');

        return $fa;
    }

    function getAvailableStock($product_id){
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT COUNT(*) AS count
        FROM inventory i
        WHERE i.product_id = {$product_id}
          AND i.status IN ('available')
        ";

        $row = $fn->getRecordBySQL($SQL);

        return $row['count'];

    }

    function getRevertStockStatus($revertFromModule, $record_id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module = $modulesArr[$revertFromModule];
        $keyField = $module['keyField'];

        $SQL = "
        UPDATE inventory
        SET status = 'available'
        WHERE {$keyField} = {$record_id}
        ";
        $db->sql_query($SQL);
    }

    /**
     *
     * Update Inventory status for the quantity entered in
     * Enquiry line edit or Sales Order line edit
     */
    function getUpdateStockStatus($updateFromModule, $source_record_id, $product_id,
                                  $quantity, $status_new, $status_inventory = 'available') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module = $modulesArr[$updateFromModule];
        $keyField = $module['keyField'];

        // //default it to available
        // $SQL = "
        // UPDATE inventory
        // SET status = 'available'
        // WHERE product_id = {$product_id}
        //   AND {$keyField} = {$source_record_id}
        // ";
        // $db->sql_query($SQL);

        //get first inventory record to be updated
        $SQL = "
        SELECT *
        FROM inventory
        WHERE product_id = {$product_id}
          AND status IN ('{$status_inventory}')
        ORDER BY inventory_id
        LIMIT 1
        ";
        $rowInventory = $fn->getRecordBySQL($SQL);

        //update inventory status
        $SQL = "
        UPDATE inventory
        SET status = '{$status_new}'
           ,{$keyField} = {$source_record_id}
        WHERE product_id = {$product_id}
          AND status IN ('{$status_inventory}')
        ORDER BY serial_no
        LIMIT {$quantity}
        ";
        $db->sql_query($SQL);

        //return updated first inventory record
        return $rowInventory;

    }

    function getChangeStatusSubmit() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $inventory_id = $fn->getReqParam('inventory_id');
        $status = $fn->getReqParam('status');

        $SQL = "
        UPDATE inventory
        SET status = '{$status}'
        WHERE inventory_id = {$inventory_id}
        ";
        $db->sql_query($SQL);

        $returnText = $status;
        return $validate->getSuccessMessageXML('', $returnText);
    }

    function getCalculateSalePrice() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $inventory_id = $fn->getReqParam('inventory_id');

        $rowInv = $fn->getRecordRowByID('inventory', 'inventory_id', $inventory_id);
        $discountPercent = $fn->getSettingsValueByKey('m.trading.inventory.discountPercent');

        $sale_price = $rowInv['retail_unit_price'] -
                      $rowInv['retail_unit_price'] * ($discountPercent/100);

        return $cpUtil->getJsonFromArray(array('sale_price' => $sale_price));
    }

    function getUpdateSalePriceFromList() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $inventory_id = $fn->getReqParam('inventory_id');
        $on_sale = $fn->getReqParam('on_sale', 0);

        $rowInv = $fn->getRecordRowByID('inventory', 'inventory_id', $inventory_id);
        $discountPercent = $fn->getSettingsValueByKey('m.trading.inventory.discountPercent');

        $sale_price = 0;
        if ($on_sale) {
            $sale_price = $rowInv['retail_unit_price'] -
                          $rowInv['retail_unit_price'] * ($discountPercent/100);
        }

        $SQL = "
        UPDATE inventory
        SET retail_unit_price_discount = {$sale_price}
           ,on_sale = {$on_sale}
        WHERE inventory_id = {$inventory_id}
        ";
        $db->sql_query($SQL);

        return $cpUtil->getJsonFromArray(array('sale_price' => $sale_price));

    }

    function getActualSellPrice($inventory_id) {
        $fn = Zend_Registry::get('fn');

        $rowInv = $fn->getRecordRowByID('inventory', 'inventory_id', $inventory_id);
        $sell_unit_price_actual = $rowInv['retail_unit_price'];
        if ($rowInv['retail_unit_price_discount']) {
            $sell_unit_price_actual = $rowInv['retail_unit_price_discount'];
        }

        return $sell_unit_price_actual;
    }

    function getTradingInventoryTradingPricingTypeLinkSQL($inventory_id) {
        $fn = Zend_Registry::get('fn');

        $rowInv = $fn->getRecordRowByID('inventory', 'inventory_id', $inventory_id);

        $SQL = "
        SELECT DISTINCT
               ppt.product_pricing_type_id
              ,pt.pricing_type
              ,ppt.sell_unit_price_base
              ,'' AS empty
        FROM product_pricing_type ppt
        JOIN pricing_type pt ON pt.pricing_type_id = ppt.pricing_type_id
        WHERE ppt.product_id = {$rowInv['product_id']}
        ORDER BY pt.sort_order
        ";

        return $SQL;
    }
}