<?
class CP_Admin_Modules_ELearn_QuestionAnswer_ModelLink
{
    //========================================================//
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('answer', 'Please enter the answer');

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
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    //==================================================================//
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'right_answer');
        $fa = $fn->addToFieldsArray($fa, 'answer');
        $fa = $fn->addToFieldsArray($fa, 'cht_answer');
        $fa = $fn->addToFieldsArray($fa, 'page_question_id');
        $fa = $fn->addToFieldsArray($fa, 'book_page_id');
        $fa = $fn->addToFieldsArray($fa, 'book_id');
        
        return $fa;
    }
}
