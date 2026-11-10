<?
class CP_Admin_Modules_ELearn_BookPage_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT a.*
              ,b.title AS book_title
        FROM book_page a
        LEFT JOIN book b ON b.book_id = a.book_id
        ";

        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $book_id = $fn->getReqParam('book_id');
        $color     = $fn->getReqParam('color');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.book_page_id = '{$tv['record_id']}'";
        }
        
        if ($color != '') {
            $searchVar->sqlSearchVar[] = "a.color = '{$color}'";
        }
        
        if ($book_id != '') {
            $searchVar->sqlSearchVar[] = "a.book_id = '{$book_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "( 
               a.english LIKE '%{$tv['keyword']}%'
            )";
        }
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

        $validate->validateData('english', 'Please enter the title');
        $validate->validateData('color', 'Please select the color');
        $validate->validateData('book_id', 'Please select the book');

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

        $validate->validateData('english', 'Please enter the title');

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

        $fa = $fn->addToFieldsArray($fa, 'book_id');
        $fa = $fn->addToFieldsArray($fa, 'page_no');
        $fa = $fn->addToFieldsArray($fa, 'color');
        $fa = $fn->addToFieldsArray($fa, 'english');
        $fa = $fn->addToFieldsArray($fa, 'chinese');
        $fa = $fn->addToFieldsArray($fa, 'chinese_traditional');
        $fa = $fn->addToFieldsArray($fa, 'pinyin');
        $fa = $fn->addToFieldsArray($fa, 'vocabulary');
        
        return $fa;
    }
}
