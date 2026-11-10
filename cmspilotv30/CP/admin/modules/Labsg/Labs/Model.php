<?
class CP_Admin_Modules_Labsg_Labs_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $appendSQL = '';

        $joinTable = '';
        if ($_SESSION['userGroupType'] == 'User'){
            $joinTable = "
                LEFT JOIN (product_group_staff pgs) ON (p.product_group_id = pgs.product_group_id)
            ";
        }

        $SQL = "
        SELECT l.*
              ,com.company_name AS supplier_name
              ,clnt.company_name
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name_supplier
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name

        FROM labs l
        LEFT JOIN company com ON l.company_id_supplier = com.company_id
        LEFT JOIN contact c ON l.contact_id_supplier = c.contact_id
        LEFT JOIN staff s ON l.staff_id = s.staff_id
        LEFT JOIN company clnt ON clnt.company_id = c.company_id
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
        $searchVar->mainTableAlias = 'l';

        $status            = $fn->getReqParam('status');
        $company_id        = $fn->getReqParam('company_id');
        $labs_id     = $fn->getReqParam('labs_id');

        if ($labs_id != "") {
            $searchVar->sqlSearchVar[] = "l.labs_id = '{$labs_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "l.labs_id = '{$tv['record_id']}'";
        } else {

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "l.status = '{$status}'";
            }
            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "l.company_id_supplier = '{$company_id}'";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "l.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(l.flag != 1 OR l.flag IS null)";
            }
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       po.po_code  LIKE '%{$tv['keyword']}%'
                    OR com.company_name LIKE '%{$tv['keyword']}%'
                    OR q.title LIKE '%{$tv['keyword']}%'
                    OR s.first_name LIKE '%{$tv['keyword']}%'
                    OR s.last_name LIKE '%{$tv['keyword']}%'
                    OR po.status LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "l.creation_date DESC";

    }
 
    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
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
        $db = Zend_Registry::get('db');
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
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();

        //$validate->validateData('buy_currency', 'Please choose buy currency');


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
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

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
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'labs_code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'company_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'labs_date');
        $fa = $fn->addToFieldsArray($fa, 'buy_currency');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'priority');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');


        return $fa;
    }

    /**
     *
     */
    function getAddMultipleLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');


        $labs_id            = $fn->getPostParam('labs_id');
        $product_arr        = $fn->getPostParam('product_id', array());
        $supplier_name_arr  = $fn->getPostParam('company_id_supplier', array());
        $price_arr          = $fn->getPostParam('price', array());
        $qty_arr            = $fn->getPostParam('qty', array());
        $qty_delivered_arr  = $fn->getPostParam('qty_delivered', array());
        $statusprod_arr     = $fn->getPostParam('prod_status', array());

        $count = count($price_arr);
        for ($i= 0; $i < $count; $i++) {
            $product_id         = $product_arr[$i];
            $supplier_id        = $supplier_name_arr[$i];
            $price              = $price_arr[$i];
            $qty                = $qty_arr[$i];
            $qty_delivered      = $qty_delivered_arr[$i];
            $prod_status        = $statusprod_arr[$i];

            if ($price) {
                $fa = array();

                $fa['product_id']       = $product_id;
                $fa['supplier_id']      = $supplier_id;
                $fa['price']            = $price;
                $fa['qty']              = $qty;
                $fa['qty_delivered']    = $qty_delivered;
                $fa['status']           = $prod_status;
                $fa['labs_id']          = $labs_id;
                $fa['creation_date']    = date("Y-m-d H:i:s");

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'labs_product');
                $result = $db->sql_query($insert);
                $labs_product_id = $db->sql_nextid();
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */

    function getlabsglabslabsgProductLinkSQL($id) {

        $SQL = "
        SELECT lp.labs_product_id
              ,p.title AS product_name
              ,lp.price
              ,lp.qty
              ,lp.qty_delivered
              ,lp.status
        FROM labs_product lp
        JOIN product p ON (p.product_id = lp.product_id)
        WHERE lp.labs_id = {$id}
        ";

        return $SQL;
    }
    /**
     *
     */

    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT max(batch_import) as batch_import
        FROM purchase_order
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $fa = array(
              'purchase_order_date' => $phpExcel->getImportFldObj('Purchase Date')
             ,'company_id_supplier' => $phpExcel->getImportFldObj('Supplier')
             ,'qty'                 => $phpExcel->getImportFldObj('Qty')
             ,'price'               => $phpExcel->getImportFldObj('Price')
             ,'category'            => $phpExcel->getImportFldObj('Main Category')
             ,'sub_category'        => $phpExcel->getImportFldObj('Sub Category')
             ,'title'               => $phpExcel->getImportFldObj('Product Title')
             ,'unit'                => $phpExcel->getImportFldObj('Unit')
             ,'item_code'           => $phpExcel->getImportFldObj('Item Code')
        );
        $fa['qty']['refOnly'] = true;
        $fa['price']['refOnly'] = true;
        $fa['category']['refOnly'] = true;
        $fa['sub_category']['refOnly'] = true;
        $fa['title']['refOnly'] = true;
        $fa['unit']['refOnly'] = true;
        $fa['item_code']['refOnly'] = true;

        $fa['batch_import']['defaultValue'] = $row['batch_import'] + 1;
        $fa['po_code']['defaultValue'] = $this->getUpdatePOCode();

        $fa['company_id_supplier']['specialType'] = 'fetchIdFromRefModule';
        $fa['company_id_supplier']['exp']['refModule'] = 'tradingsg_supplier';

        /****************************************/
        $config = array(
             'module'              => 'tradingsg_purchaseOrder'
            ,'matchFieldArr'       => array('purchase_order_date')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallback($purchase_order_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $qty = $fa['qty'];
        $price = $fa['price'];
        $supplier_id = $fa['company_id_supplier'];
        $batch_import = $fa['batch_import'];
        $category = $fa['category'];
        $sub_category = $fa['sub_category'];

        $title = $fa['title'];
        $unit = $fa['unit'];
        $item_code = $fa['item_code'];

        $sqlcount1 = "
                SELECT COUNT(*)
                FROM `category`
                WHERE title = '{$category}'
                ";
            $resultcount1 = $db->sql_query($sqlcount1);
            $catRecCount    = $db->sql_fetchrow($resultcount1);

            //$catRecCount = $fn->getRecordCount('category', "title = '{$category}'");

        if ($catRecCount == 0 && $category != '') {
            $fa1 = array();
            $fa1['title'] = $category;
            $fa1['section_id'] = 13;
            $fa1['published'] = 1;
            $fa1['category_type'] = 'Content';

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'category');
            $result = $db->sql_query($SQL);
            $category_id  = $db->sql_nextid();
        } else {
            $sqlcat = "
            SELECT category_id
                   ,title FROM category
                   WHERE title = '{$category}'
            ";
            $resultcat = $db->sql_query($sqlcat);
            $catRec    = $db->sql_fetchrow($resultcat);

            //$catRec = $fn->getRecordByCondition('category', "title = '{$category}'");
            $category_id  = $catRec['category_id'];
        }

        $sqlcount2 = "
                SELECT COUNT(*)
                FROM `sub_category`
                WHERE title = '{$sub_category}'
                ";
            $resultcount2 = $db->sql_query($sqlcount2);
            $subCatRecCount    = $db->sql_fetchrow($resultcount2);

            //$subCatRecCount = $fn->getRecordCount('sub_category', "title = '{$sub_category}'");

        if ($subCatRecCount == 0 && $sub_category != '') {
            $fa1 = array();
            $fa1['title'] = $sub_category;
            $fa1['category_id'] = $category_id;
            $fa1['published'] = 1;
            $fa1['sub_category_type'] = 'Content';

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'sub_category');
            $result = $db->sql_query($SQL);
            $sub_category_id  = $db->sql_nextid();
        } else {
            $sqlcat1 = "
            SELECT sub_category_id
                   ,title FROM sub_category
                   WHERE title = '{$sub_category}'
            ";
            $resultcat1 = $db->sql_query($sqlcat1);
            $subCatRec    = $db->sql_fetchrow($resultcat1);

            //$subCatRec = $fn->getRecordByCondition('sub_category', "title = '{$sub_category}'");
            $sub_category_id  = $subCatRec['sub_category_id'];
        }

        $fa2 = array();
        $fa2['title']  = $title;
        $fa2['unit']  = $unit;
        $fa2['price']  = $price;
        $fa2['published']  = 1;
        $fa2['category_id']  = $category_id;
        $fa2['sub_category_id']  = $sub_category_id;
        $fa2['batch_import']  = $batch_import;
        $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'product');

        if($item_code == ''){
            $fa2['item_code'] = $this->getUpdateProductCode();

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'product');
            $result = $db->sql_query($SQL);
            $product_id  = $db->sql_nextid();
        } else {
            $whereCondition = "WHERE item_code = '{$item_code}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa2, 'product', $whereCondition);
            $result = $db->sql_query($SQL);

            $rowProduct = $fn->getRecordRowByID('product', 'item_code', $item_code);
            $product_id  = $rowProduct['product_id'];
        }

        $fa3 = array();
        $fa3['product_id'] = $product_id;
        $fa3['purchase_order_id']  = $purchase_order_id;
        $fa3['supplier_id']  = $supplier_id;
        $fa3['qty']  = $qty;
        $fa3['price']  = $price;
        $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */

}
