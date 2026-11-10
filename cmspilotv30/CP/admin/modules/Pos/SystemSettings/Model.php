<?
class CP_Admin_Modules_Pos_SystemSettings_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        return getCPModuleObj('pos_globalSettings')->model->getSQL();
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 's';

        $searchVar->sqlSearchVar[] = "s.mode = 'System'";

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
        return getCPModuleObj('pos_globalSettings')->model->getSave($tableName, $keyField, $keyFieldVal);
    }

    /**
     *
     */
    function getFields(){
        $tv = Zend_Registry::get('tv');
        $fa = getCPModuleObj('pos_globalSettings')->model->getFields();
        
        if ($tv['spAction'] == 'add'){
            $fa['mode'] = 'System';
        }
        
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
