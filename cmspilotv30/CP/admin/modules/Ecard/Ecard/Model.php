<?
class CP_Admin_Modules_Ecard_Ecard_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT e.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
        FROM ecard e
        LEFT JOIN (contact c)      ON (e.contact_id       = c.contact_id)
        ";

        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                e.language       LIKE '%{$tv['keyword']}%'
            )";
        }

        $searchVar->sortOrder = "e.ecard_id DESC";
        
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

        $validate->validateData('sender_name', 'Please enter the sender name');
        $validate->validateData('sender_dept', 'Please enter the sender dept');

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
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();

        $validate->validateData('sender_name', 'Please enter the sender name');
        $validate->validateData('sender_dept', 'Please enter the sender dept');

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
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'message', '', true);

        $fa = $fn->addToFieldsArray($fa, 'sender_name');
        $fa = $fn->addToFieldsArray($fa, 'sender_dept');
        $fa = $fn->addToFieldsArray($fa, 'language');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        
        return $fa;
    }
}
