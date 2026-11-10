<?
class CP_Admin_Modules_Pos_Valuelist_Model extends CP_Common_Lib_ModuleModelAbstract
{
   function getSQL() {
        $SQL = "
        SELECT * 
        FROM valuelist v
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
        $searchVar->mainTableAlias = 'v';

        $valuelist_id = $fn->getReqParam('valuelist_id');
        $key_text     = $fn->getReqParam('key_text');

        if ($valuelist_id != '' ) {
            $searchVar->sqlSearchVar[] = "v.valuelist_id  = '{$valuelist_id}'";

        } else if ($tv['record_id'] != ''){
            $searchVar->sqlSearchVar[] = "v.valuelist_id  = '{$tv['record_id']}'";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'v.valuelist_id');

            if ($key_text != '' ) {
                $searchVar->sqlSearchVar[] = "v.key_text  = '{$key_text}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    v.key_text    LIKE '%{$tv['keyword']}%'  OR
                    v.value       LIKE '%{$tv['keyword']}%'
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

        $validate->validateData('key_text', 'Please choose the valuelist name');
        $validate->validateData('value', 'Please enter the value');

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
        $fa['sort_order'] = $fn->getNextSortOrder("valuelist");
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('key_text', 'Please choose the valuelist name');
        $validate->validateData('value', 'Please enter the value');

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

        $fa = $fn->addToFieldsArray($fa, 'key_text');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'is_group_heading');
        $fa = $fn->addToFieldsArray($fa, 'sort_order');
        $fa = $fn->addToFieldsArray($fa, 'value', '', true);

        return $fa;
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
