<?
class CP_Admin_Modules_EnterpriseIms_Level_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL   = "
        SELECT l.*
        FROM level l
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
        $searchVar->mainTableAlias = 'l';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "l.level_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'l.level_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       l.title      LIKE '%{$tv['keyword']}%'
                    OR l.level_code LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "l.level_id DESC";
        }
    }

    /**
     * 
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('level_code', 'Please enter the code');

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
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('level_code', 'Please enter the code');

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

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'level_code');

        return $fa;
    }
}