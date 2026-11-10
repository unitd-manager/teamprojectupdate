<?
class CP_Admin_Modules_Pos_GlobalSettings_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT * 
        FROM setting s
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 's';

        $searchVar->sqlSearchVar[] = "s.mode = 'Global'";

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "s.setting_id  = '{$tv['record_id']}'";
        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    s.key_text LIKE '%{$tv['keyword']}%'
                    OR s.description LIKE '%{$tv['keyword']}%'  
                    OR s.value       LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        if ($fn->isDeveloper()){
            $validate->validateData('key_text', 'Please enter the key text');
        }

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
        $fn = Zend_Registry::get('fn');
        
        $validate->resetErrorArray();
        if ($fn->isDeveloper()){
            $validate->validateData('key_text', 'Please enter the key text');
        }

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
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'key_text');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'value');
        $fa = $fn->addToFieldsArray($fa, 'value_type');
        $fa = $fn->addToFieldsArray($fa, 'starting_no');
        $fa = $fn->addToFieldsArray($fa, 'length');
        $fa = $fn->addToFieldsArray($fa, 'add_shop_code');
        $fa = $fn->addToFieldsArray($fa, 'add_separator');
        $fa = $fn->addToFieldsArray($fa, 'reset_next_year');
        $fa = $fn->addToFieldsArray($fa, 'auto_generate_no');
        $fa = $fn->addToFieldsArray($fa, 'group_name');

        if ($tv['spAction'] == 'add'){
            $fa['mode'] = 'Global';
        }
        
        return $fa;
    }

    /**
     *
     */
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'key_text'         => $phpExcel->getImportFldObj('Key')
             ,'value'            => $phpExcel->getImportFldObj('Value')
             ,'description'      => $phpExcel->getImportFldObj('Description')
             ,'group_name'       => $phpExcel->getImportFldObj('Group')
             ,'value_type'       => $phpExcel->getImportFldObj('Value Type')
             ,'starting_no'      => $phpExcel->getImportFldObj('Starting No')
             ,'length'           => $phpExcel->getImportFldObj('Length')
             ,'add_shop_code'    => $phpExcel->getImportFldObj('Add Shop Code')
             ,'add_separator'    => $phpExcel->getImportFldObj('Add Seperator')
             ,'reset_next_year'  => $phpExcel->getImportFldObj('Reset Next Year')
             ,'auto_generate_no' => $phpExcel->getImportFldObj('Auto Generate No')
             ,'mode'             => $phpExcel->getImportFldObj('Mode')
        );

        $config = array(
             'module'          => 'pos_globalSettings'
            ,'matchFieldArr'   => array('key_text')
            ,'fldsArr'         => $fa
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function getSaveFromList(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }
}
