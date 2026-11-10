<?
class CP_Admin_Modules_Core_Translation_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT *
        FROM translation a
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
        $searchVar->mainTableAlias = 'a';

        $group_name = $fn->getReqParam('group_name');

        if ($tv['record_id'] != ''){
            $searchVar->sqlSearchVar[] = "a.translation_id  = '{$tv['record_id']}'";
        } else {
            if ($group_name != '' ) {
                $searchVar->sqlSearchVar[] = "a.group_name  = '{$group_name}'";
            }


            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    a.key_text    LIKE '%{$tv['keyword']}%'  OR
                    a.value       LIKE '%{$tv['keyword']}%'
                )";
            }
        }

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('key_text', 'Please enter the key');

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
        $validate->validateData('key_text', 'Please enter key text');

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

        include_once(CP_PATH . 'common/lib/SpecialAction.php');
        include_once(CP_PATH . 'admin/lib/SpecialAction.php');
        $spAction = new CP_Admin_Lib_SpecialAction();

        $spAction->getUpdateLangFiles();

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'key_text');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'value', '', true);

        return $fa;
    }

    /**
     *
     */
    function getUpdateValue(){
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $lnKey = $fn->getPostParam('langKey');
        $rec_id  = $fn->getPostParam('rec_id');
        $value   = $fn->getPostParam('value');

        if($rec_id == "") {
            return;
        }
        $fldName = ($lnKey == 'eng') ? "value" : $lnKey . "_value";

        $fa = array();
        $fa[$fldName]            = $value;
        $fa['modification_date'] = date("Y-m-d H:i:s");

        $whereCondition = "WHERE translation_id = {$rec_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "translation", $whereCondition);
        $result         = $db->sql_query($SQL);

        $displayVal    = substr(strip_tags($value) , 0, 100) . "";
        $value         = htmlspecialchars($value);

        include_once(CP_PATH . 'common/lib/SpecialAction.php');
        include_once(CP_PATH . 'admin/lib/SpecialAction.php');
        $spAction = new CP_Admin_Lib_SpecialAction();
        $spAction->getUpdateLangFiles();

        return json_encode(array("displayVal"=> $displayVal, "value"=> $value));
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'key_text' => $phpExcel->getFldObj('Key')
             ,'value' => $phpExcel->getFldObj('English')
             ,'chi_value' => $phpExcel->getFldObj('Traditional Chinese')
             ,'ch2_value' => $phpExcel->getFldObj('Simplified Chinese')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );
        return $phpExcel->exportData($config);
    }

    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'key_text' => $phpExcel->getImportFldObj('Key')
             ,'value' => $phpExcel->getImportFldObj('English')
             ,'chi_value' => $phpExcel->getImportFldObj('Traditional Chinese')
             ,'ch2_value' => $phpExcel->getImportFldObj('Simplified Chinese')
        );

        $config = array(
             'module' => 'core_translation'
            ,'matchFieldArr' => array('key_text')
            ,'fldsArr' => $fa
        );

        return $phpExcel->importData($config);
    }    
}
