<?
class CP_Admin_Modules_Pos_Uom_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT * 
        FROM uom u
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'u';

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "u.uom_id  = '{$tv['record_id']}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                u.code LIKE '%{$tv['keyword']}%'
                OR u.description LIKE '%{$tv['keyword']}%'  
                OR u.title       LIKE '%{$tv['keyword']}%'
            )";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('code', 'Please enter the code');

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
        $validate->validateData('code', 'Please enter the code');

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

        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'use_exchange_rate');
        $fa = $fn->addToFieldsArray($fa, 'exchange_other_uom_code');
        $fa = $fn->addToFieldsArray($fa, 'exchange_rage_to');
        
        return $fa;
    }

    function getUomCodeSQL() {
        $SQL = "
        SELECT code 
        FROM uom u
        ";
        
        return $SQL;
    }

}
