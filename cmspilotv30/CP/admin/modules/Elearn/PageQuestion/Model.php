<?
class CP_Admin_Modules_ELearn_PageQuestion_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'question');
        $fa = $fn->addToFieldsArray($fa, 'cht_question');
        $fa = $fn->addToFieldsArray($fa, 'book_page_id');
        $fa = $fn->addToFieldsArray($fa, 'book_id');
        
        return $fa;
    }

    //========================================================//
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('question', 'Please enter the question');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    //==================================================================//
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    //========================================================//
    function getEditValidate() {
        return $this->getNewValidate();
    }

    //==================================================================//
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        return $validate->getSuccessMessageXML();
    }
}
