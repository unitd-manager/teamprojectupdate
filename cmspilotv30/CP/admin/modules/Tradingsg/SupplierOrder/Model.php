<?
class CP_Admin_Modules_Tradingsg_SupplierOrder_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT so.*
              ,c.company_name AS supplier_name
        FROM supplier_order so
        LEFT JOIN (company c) ON (so.supplier_id = c.company_id)
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
        $searchVar->mainTableAlias = 'so';

        $status 	   	   = $fn->getReqParam('status');
        $company_id 	   = $fn->getReqParam('company_id');
        $supplier_order_id 	   = $fn->getReqParam('supplier_order_id');

        if ($supplier_order_id != "") {
            $searchVar->sqlSearchVar[] = "so.supplier_order_id = '{$supplier_order_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "so.supplier_order_id = '{$tv['record_id']}'";
        } else {

            /*if ($status != "") {
                $searchVar->sqlSearchVar[] = "po.status = '{$status}'";
            }
            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "po.company_id_supplier = '{$company_id}'";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "po.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(po.flag != 1 OR po.flag IS null)";
            }
            */
            
            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "so.supplier_id = '{$company_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       so.so_code  LIKE '%{$tv['keyword']}%'
                    OR so.date LIKE '%{$tv['keyword']}%'
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
        $validate->validateData('supplier_id', 'Please choose the Supplier');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['so_code'] = $this->getUpdateSupplierOrderCode();
        $id = $fn->addRecord($fa);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getUpdateSupplierOrderCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Supplier Order Code */
        $nextSoCode = $fn->getSettingsValueByKey("nextSoCode");

        if($nextSoCode < 10){
            $soCode = $fn->getSettingsValueByKey('soCodePrefix') . $nextSoCode;
        }
        else if($nextSoCode < 99){
            $soCode = $fn->getSettingsValueByKey('soCodePrefix'). $nextSoCode;
        }
        else if($nextSoCode < 999){
            $soCode = $fn->getSettingsValueByKey('soCodePrefix') . $nextSoCode;
        }
        else{
            $soCode = $fn->getSettingsValueByKey('soCodePrefix')  . $nextSoCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextSoCode'";
        $result = $db->sql_query($SQL);

        return $soCode;
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
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'so_code');
        $fa = $fn->addToFieldsArray($fa, 'supplier_id');

        return $fa;
    }

    /**
     *
     */
    function getCreateSOHistoryRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $product_id = $fn->getReqParam('product_id');
        $supplier_order_id = $fn->getReqParam('supplier_order_id');
        $row = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $purchase_order_id);
        $histRow = $fn->getRecordRowByID('supplier_order_history', 'purchase_order_id', $purchase_order_id);
        
        $fa = array();
        $fa['purchase_order_id']   = $purchase_order_id;
        $fa['supplier_order_id']   = $supplier_order_id;
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'supplier_order_history');
        
        if ($histRow['purchase_order_id'] != $purchase_order_id && $histRow['supplier_order_id'] != $row['company_id_supplier']) {
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'supplier_order_history');
            $db->sql_query($SQL);
            $supplier_order_history_id = $db->sql_nextid();
        }

        
        $updateSQL = "
        UPDATE po_product
        set supplier_order_id = {$supplier_order_id}
        WHERE product_id = {$product_id}
            AND purchase_order_id = {$fa['purchase_order_id']}
        ";
        $result = $db->sql_query($updateSQL);
        
    }

    /**
     *
     */
    function getDeleteSupplierHistoryRecord(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
                
        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $product_id = $fn->getReqParam('product_id');
        
        $updateSQL = "
        UPDATE po_product
        set supplier_order_id = ''
        WHERE product_id = {$product_id}
            AND purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($updateSQL);

        $row = $fn->getRecordCount('po_product', "purchase_order_id = '{$purchase_order_id}' AND supplier_order_id > 0");

        if ($row > 0){
        } else {
            $deleteSQL = "
            DELETE FROM supplier_order_history
            WHERE purchase_order_id = {$purchase_order_id}            
            ";
            $result = $db->sql_query($deleteSQL);
        }
        
        
    }
}
