<?
class CPL_Admin_Modules_Tradingin_Inventory_Model extends CP_Admin_Modules_Tradingin_Inventory_Model
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT i.*
              ,p.product_id AS productId
              ,p.product_type
              ,c.company_name
              ,p.title AS product_name
              ,p.item_code
              ,p.unit
              ,p.product_code
              ,i.actual_stock AS stock
        FROM inventory i
        LEFT JOIN (product p) ON (p.product_id = i.product_id)
        LEFT JOIN (product_company pc) ON (pc.product_id = p.product_id)
        LEFT JOIN (supplier c) ON (c.supplier_id = pc.company_id)
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
        $searchVar->mainTableAlias = 'i';

        $inventory_id   = $fn->getReqParam('inventory_id');
        $supplier_id   = $fn->getReqParam('supplier_id');
        $category         = $fn->getReqParam('category');
        $minimum_order_level         = $fn->getReqParam('minimum_order_level');
        $product_type         = $fn->getReqParam('product_type');

        if ($inventory_id != "") {
            $searchVar->sqlSearchVar[] = "i.inventory_id = '{$inventory_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.inventory_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'i.inventory_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.title LIKE '%{$tv['keyword']}%'
                )";
            } /*else if ($supplier_id != '' ) {
            } else {
                $searchVar->sqlSearchVar[] = "i.actual_stock > 0";
            }*/

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($supplier_id != '' ) {
                $searchVar->sqlSearchVar[] = "pc.company_id = '{$supplier_id}'";
            }

            if ($minimum_order_level != '' ) {
                $searchVar->sqlSearchVar[] = "i.minimum_order_level > 0";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            if ($product_type != '' ) {
                $searchVar->sqlSearchVar[] = "p.product_type = '{$product_type}'";
            }


            $searchVar->sortOrder = "stock DESC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('company_name', 'Please enter the company name');

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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
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
        $fa = $fn->addToFieldsArray($fa, 'product_id');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'changed_stock');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'minimum_order_level');

        return $fa;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $db      = Zend_Registry::get('db');
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fa = array(
              'item_code'        => $phpExcel->getFldObj('ITEM CODE')
             ,'product_name'        => $phpExcel->getFldObj('PRODUCT')
             ,'inventory_code'        => $phpExcel->getFldObj('Inventory Code')
             ,'inventory_id'        => $phpExcel->getFldObj('Inventory Id')
             ,'code'              => $phpExcel->getFldObj('Code')
             ,'color'        => $phpExcel->getFldObj('Color')
             ,'size'        => $phpExcel->getFldObj('Size')
             ,'model'        => $phpExcel->getFldObj('Model')
             ,'product_id'        => $phpExcel->getFldObj('Product Id')
             ,'actual_stock'        => $phpExcel->getFldObj('Stock')
        );

        //$dataArray = $dbUtil->getResultsetAsArray($result);

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getImportData1(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_code'      => $phpExcel->getImportFldObj('Product Code')
             ,'title'             => $phpExcel->getImportFldObj('Title')
             ,'description_short' => $phpExcel->getImportFldObj('Short Description')
             ,'description'       => $phpExcel->getImportFldObj('Description')
             ,'picture'           => $phpExcel->getImportFldObj('Picture Ref')
             ,'published'         => $phpExcel->getImportFldObj('Published')
             ,'category_id'       => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'   => $phpExcel->getImportFldObj('Sub Category')
        );

        $fa['published']['defaultValue'] = 1;
        $fa['picture']['refOnly'] = true;

        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Product');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        /****************************************/
        $config = array(
             'module'              => 'trading_company'
            ,'matchFieldArr'       => array('product_code')
            ,'mandatoryFldsArr'    => array('product_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert($product_id, $fa) {
        $media = Zend_Registry::get('media');

        if ($fa['picture'] != ''){
            $sourceFilePath = realpath('../media_import') . "/{$picture}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            $media->model->createMedia('ecommerce_product', 'picture', $product_id, $exp);
        }
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');

        /*
        STOCK - qty_in_stock - qty
        PRODUCT - title
        Item Code - item_code
        Category - category_id
        FC from China - fc_price
        Purchase Cost from BLOSSOMS - price
        Product Weight - product_weight
        VAT% - vat_percentage
        Price per KG - weight_per_kg
        Product Display Price - selling_price
        Add Shipping Cost - logistics
        Comission Calculation Price (Less VAT & Logistics) - agent_price
        TC Comsn % ( 5% - 20%) - commission
        */

        $fa = array(
              'title' => $phpExcel->getImportFldObj('PRODUCT')
             //,'purchase_order_date' => $phpExcel->getImportFldObj('Purchase Date')
             ,'item_code' => $phpExcel->getImportFldObj('ITEM CODE')
             ,'inventory_code' => $phpExcel->getImportFldObj('Inventory Code')
             ,'inventory_id' => $phpExcel->getImportFldObj('Inventory Id')
             ,'code' => $phpExcel->getImportFldObj('Code')
             ,'color' => $phpExcel->getImportFldObj('Color')
             ,'size'  => $phpExcel->getImportFldObj('Size')
             ,'model'  => $phpExcel->getImportFldObj('Model')
             ,'stock' => $phpExcel->getImportFldObj('Stock')
             ,'product_id' => $phpExcel->getImportFldObj('Product Id')
        );

        $fa['title']['refOnly'] = true;
        $fa['item_code']['refOnly'] = true;
        $fa['inventory_code']['refOnly'] = true;
        //$fa['inventory_id']['refOnly'] = true;
        $fa['color']['refOnly'] = true;
        $fa['size']['refOnly'] = true;
        $fa['model']['refOnly'] = true;
        $fa['stock']['refOnly'] = true;
        $fa['product_id']['refOnly'] = true;
        $fa['code']['refOnly'] = true;

        /****************************************/
        $config = array(
             'module'              => 'tradingin_inventory'
            ,'matchFieldArr'       => array('inventory_id')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallbackForStock'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallbackForStock($inventory_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $stock = $fa['stock'];
        $item_code = $fa['item_code'];
        $title = $fa['title'];
        $inventory_code = $fa['inventory_code'];
        $code = $fa['code'];
        $color = $fa['color'];
        $size = $fa['size'];
        $model = $fa['model'];
        $product_id = $fa['product_id'];
        $inventory_id = $fa['inventory_id'];

        //$productRec  = $fn->getRecordRowByID('product', 'product_id', $product_id);
        $fa2 = array();
        $fa2['purchase_order_date']  = date('Y-m-d');
        $fa2['po_code'] = $this->getUpdatePOCode();

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'purchase_order');
        $result = $db->sql_query($SQL);
        $purchase_order_id  = $db->sql_nextid();

        $fa3 = array();
        $fa3['product_id'] = $product_id;
        //$fa3['inventory_id'] = $inventory_id;
        $fa3['purchase_order_id']  = $purchase_order_id;
        $fa3['qty']  = $stock;
        //$fa3['qty_requested']  = $stock;
        $fa3['color_size_code']  = $code;
        $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
        $result = $db->sql_query($SQL);

        if($color != ''){
            $productColorChk = $fn->getRecordByCondition('product_color', "color = '{$color}' AND product_id = '{$product_id}'");
            if($productColorChk){
                //$productColorRec = $fn->getRecordByCondition('product_color', "code = '{$code}'");
                $qty = $productColorChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_color SET qty = {$qty}
                WHERE product_color_id = {$productColorChk['product_color_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $product_color_id = $productColorChk['product_color_id'];
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['color'] = $color;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_color');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_color');
                $result = $db->sql_query($SQL);
                $product_color_id = $db->sql_nextid();
            }
        }

        if($size != ''){
            $productSizeChk = $fn->getRecordByCondition('product_size', "size = '{$size}' AND product_id = '{$product_id}'");
            if($productSizeChk){
                //$productSizeRec = $fn->getRecordByCondition('product_size', "code = '{$code}'");
                $qty = $productSizeChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_size SET qty = {$qty}
                WHERE product_size_id = {$productSizeChk['product_size_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $product_size_id = $productSizeChk['product_size_id'];
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['size'] = $size;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_size');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_size');
                $result = $db->sql_query($SQL);
                $product_size_id = $db->sql_nextid();
            }

        }

        if($model != ''){
            $productModelChk = $fn->getRecordByCondition('product_model', "model = '{$model}' AND product_id = '{$product_id}'");
            if($productModelChk){
                $qty = $productModelChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_model SET qty = {$qty}
                WHERE product_model_id = {$productModelChk['product_model_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
                $product_model_id = $productModelChk['product_model_id'];
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['model'] = $model;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_model');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_model');
                $result = $db->sql_query($SQL);
                $product_model_id = $db->sql_nextid();
            }

        }

        if($color != '' && $size != ''){
            $productColorSizeChk = $fn->getRecordByCondition('product_color_by_size', "product_color_id = '{$product_color_id}' AND product_size_id = '{$product_size_id}' AND product_id = '{$product_id}'");
            if($productColorSizeChk){
                //$productColorSizeRec = $fn->getRecordByCondition('product_color_by_size', "code = '{$code}'");
                $qty = $productColorSizeChk['qty'] + $stock;
                $SQLUpdate ="
                UPDATE product_color_by_size SET qty = {$qty}
                WHERE product_color_by_size_id = {$productColorSizeChk['product_color_by_size_id']}";
                $resultUpdate = $db->sql_query($SQLUpdate);
            } else {
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['product_color_id'] = $product_color_id;
                $fa['product_size_id']  = $product_size_id;
                $fa['qty']  = $stock;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_color_by_size');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_color_by_size');
                $result = $db->sql_query($SQL);
            }
        }
            $invRec = $fn->getRecordByCondition('inventory', "product_id = {$product_id}");
            $fa4 = array();
            $fa4['product_id'] = $product_id;
            $fa4['actual_stock'] = $invRec['actual_stock'] + $stock;
            //$fa4 = $fn->addCreationDetailsToFieldsArray($fa4, 'inventory');

            $whereCondition = "WHERE product_id = {$product_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa4, 'inventory', $whereCondition);
            $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getUpdatePOCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Purchase order Code */
        $poCode = $fn->getSettingsValueByKey("poCode");

        $POCode = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'poCode'";
        $result = $db->sql_query($SQL);

        return $POCode;
    }

    /**
     *
     */
    function getUpdateCurrentStockInventoryBatchRecordList() {
        $cpCfg  = Zend_Registry::get('cpCfg');
        $fn     = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db     = Zend_Registry::get('db');

        $inventory_id  = $fn->getReqParam('inventory_id');
        $product_id    = $fn->getReqParam('product_id');
        $current_stock = $fn->getReqParam('current_stock');

        $SQLInventory = "
        SELECT product_id
              ,actual_stock
        FROM inventory
        WHERE inventory_id = '{$inventory_id}'
          AND product_id   = '{$product_id}'
        ";
        $resultInventory = $db->sql_query($SQLInventory);
        $rowInventory    = $db->sql_fetchrow($resultInventory);

        $fa2 = array();
        $fa2['product_id']    = $product_id;
        $fa2['inventory_id']  = $inventory_id;
        $fa2['adjust_stock']  = $current_stock;
        $fa2['current_stock'] = $rowInventory['actual_stock'];
        $fa2       = $fn->addCreationDetailsToFieldsArray($fa2, 'adjust_stock_log');
        $SQLLog    = $dbUtil->getInsertSQLStringFromArray($fa2, 'adjust_stock_log');
        $resultLog = $db->sql_query($SQLLog);
        
        $SQLUpdateInventory = "
        UPDATE inventory SET actual_stock = {$current_stock}
        WHERE product_id = '{$product_id}'
        ";            
        $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);

        $invRec = $fn->getRecordRowByID('inventory', 'inventory_id', $inventory_id);

        return $invRec['actual_stock'];
    }
}
