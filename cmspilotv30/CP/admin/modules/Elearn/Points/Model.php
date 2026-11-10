<?
class CP_Admin_Modules_ELearn_Points_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT p.*
              ,b.title As book 
              ,CONCAT_WS(' ', st.first_name, st.last_name ) AS student_name 
        FROM points p
        LEFT JOIN book b ON (b.book_id = p.book_id)
        LEFT JOIN student st ON (st.student_id = p.student_id)
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

        $book_id     = $fn->getReqParam('book_id');
        $record_type     = $fn->getReqParam('record_type');
        $student     = $fn->getReqParam('student_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.points_id = '{$tv['record_id']}'";
        }
        
        if ($book_id != '') {
            $searchVar->sqlSearchVar[] = "p.book_id = '{$book_id}'";
        }

        if ($record_type != '') {
            $searchVar->sqlSearchVar[] = "p.record_type = '{$record_type}'";
        }

        if ($student != '') {
            $searchVar->sqlSearchVar[] = "p.student_id = '{$student}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               a.first_name LIKE '%{$tv['keyword']}%'
            OR a.last_name LIKE '%{$tv['keyword']}%'
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

        $validate->validateData('student_id', 'Please select the name');

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

        $validate->validateData('student_id', 'Please select the name');

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

        $fa = $fn->addToFieldsArray($fa, 'student_id');
        $fa = $fn->addToFieldsArray($fa, 'book_id');
        $fa = $fn->addToFieldsArray($fa, 'points');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'record_type');
        $fa = $fn->addToFieldsArray($fa, 'completion_date');
        
        return $fa;
    }
}
