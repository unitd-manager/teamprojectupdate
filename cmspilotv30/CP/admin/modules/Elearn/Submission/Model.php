<?
class CP_Admin_Modules_ELearn_Submission_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT s.*
              ,k.title As class 
              ,b.title As book
              ,bp.page_no As page
              ,bp.english As book_page_title
              ,sc.school_name As school
              ,CONCAT_WS(' ', st.first_name, st.last_name ) AS student_name 
        FROM submission s
        LEFT JOIN klass k      ON k.klass_id = s.klass_id
        LEFT JOIN book b       ON b.book_id = s.book_id
        LEFT JOIN book_page bp ON bp.book_page_id = s.book_page_id
        LEFT JOIN student st   ON st.student_id = s.student_id
        LEFT JOIN school sc    ON sc.school_id = s.school_id
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

        $school_id  = $fn->getReqParam('school_id');
        $klass_id   = $fn->getReqParam('klass_id');
        $student_id = $fn->getReqParam('student_id');
        $book_id    = $fn->getReqParam('book_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.submission_id = '{$tv['record_id']}'";
        } else {
            
            if ($school_id != '') {
                $searchVar->sqlSearchVar[] = "s.school_id = '{$school_id}'";
            }

            if ($klass_id != '') {
                $searchVar->sqlSearchVar[] = "s.klass_id = '{$klass_id}'";
            }

            if ($student_id != '') {
                $searchVar->sqlSearchVar[] = "s.student_id = '{$student_id}'";
            }
    
            if ($book_id != '') {
                $searchVar->sqlSearchVar[] = "s.book_id = '{$book_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   a.first_name LIKE '%{$tv['keyword']}%'
                OR a.last_name LIKE '%{$tv['keyword']}%'
                )";
            }
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
        $fa = $fn->addToFieldsArray($fa, 'school_id');
        $fa = $fn->addToFieldsArray($fa, 'klass_id');
        $fa = $fn->addToFieldsArray($fa, 'book_id');
        $fa = $fn->addToFieldsArray($fa, 'book_page_id');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'completed');
        $fa = $fn->addToFieldsArray($fa, 'completion_date');
        $fa = $fn->addToFieldsArray($fa, 'answered');
        $fa = $fn->addToFieldsArray($fa, 'score');
        $fa = $fn->addToFieldsArray($fa, 'klass_id');
        
        return $fa;
    }
}
