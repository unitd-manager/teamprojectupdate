<?
class CP_Admin_Modules_Ecommerce_SerialNos_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $SQL = "
        SELECT s.* 
              ,p.title AS product_title
        FROM `serial_nos` s
        LEFT JOIN product p ON (s.product_id = p.product_id)
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
        $searchVar->mainTableAlias = 's';

        $product_id = $fn->getReqParam('product_id');
        $product_code = $fn->getReqParam('product_code');
        $special_search  = $fn->getReqParam('special_search');
        $status = $fn->getReqParam('status');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.serial_nos_id = '{$tv['record_id']}'";
        } else {

            if ($product_id != '') {
                $searchVar->sqlSearchVar[] = "s.product_id = {$product_id}";
            }

            if ($product_code != '') {
                $searchVar->sqlSearchVar[] = "s.product_code = '{$product_code}'";
            }

            if ($status != '') {
                $searchVar->sqlSearchVar[] = "s.status = '{$status}'";
            }

            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(   
                    s.product_code   LIKE '%{$tv['keyword']}%'
                 OR s.product_serial LIKE '%{$tv['keyword']}%'
                )";
            }
            
            if ($special_search != '' ) {
                if ($special_search == 'Registered') {
                    $searchVar->sqlSearchVar[] = "s.registered = 1";
                }
    
                if ($special_search == 'Not-Registered' ) {
                    $searchVar->sqlSearchVar[] = "s.registered = 0 OR s.registered IS NULL OR s.registered = ''";
                }
    
            }
            
        }
        
        $searchVar->sortOrder = "s.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
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

        $fa = $fn->addToFieldsArray($fa, 'product_id');
        $fa = $fn->addToFieldsArray($fa, 'product_code');
        $fa = $fn->addToFieldsArray($fa, 'product_serial');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'registered');
        $fa = $fn->addToFieldsArray($fa, 'status');

        return $fa;
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_code'      => $phpExcel->getImportFldObj('Product Code')
             ,'product_id'             => $phpExcel->getImportFldObj('Product ID')
             ,'product_serial' => $phpExcel->getImportFldObj('Product Serial')
        );

        /****************************************/
        $config = array(
             'module'              => 'ecommerce_serialNos'
            ,'mandatoryFldsArr'    => array('product_code')
            ,'fldsArr'             => $fa
        );

        return $phpExcel->importData($config);
    }
}
