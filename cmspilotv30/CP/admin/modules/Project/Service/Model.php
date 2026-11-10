<?
class CP_Admin_Modules_Project_Service_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT a.* 
        FROM service a
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

        $service_id          = $fn->getReqParam('service_id');
        $service_category_id = $fn->getReqParam('service_category_id');

        if ($service_id != "") {
            $searchVar->sqlSearchVar[] = "a.service_id   = {$service_id}";
        }

        if ($service_category_id != "") {
            $searchVar->sqlSearchVar[] = "a.service_category_id   = {$service_category_id}";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                a.title   LIKE '%{$tv['keyword']}%'
            )";
        }

        $searchVar->sortOrder = "a.title";
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

        $this->setFields();
        $fa = &$this->fieldsArray;

        $fa['creation_date'] =  date("Y-m-d H:i:s");

        $id = $fn->addRecord($this->fieldsArray);

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

        $this->setFields();

        $id = $fn->saveRecord($this->fieldsArray, $tableName, $keyField, $keyFieldVal);

        $fn->returnAfterNewSave($id);
    }
}
