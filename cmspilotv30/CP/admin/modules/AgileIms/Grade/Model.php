<?
class CP_Admin_Modules_AgileIms_Grade_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     */
    function getSQL() {

        $SQL = "
        SELECT g.*
              ,co.first_name AS contact_name
              ,b.title AS batch_title
              ,s.title AS subject_title
              ,c.title AS class_name
        FROM grade g
        LEFT JOIN (contact co) ON (co.contact_id = g.contact_id)
        LEFT JOIN (batch b) ON (b.batch_id = g.batch_id)
        LEFT JOIN (subject s) ON (s.subject_id = g.subject_id)
        LEFT JOIN (class c) ON (c.class_id = g.class_id)
        ";
        
        return $SQL;
    }

    /**
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');

        $student_grade_id = $fn->getReqParam('student_grade_id');
        $grade_id   = $fn->getReqParam('grade_id');
        $exam_type = $fn->getReqParam('exam_type');
        $class_id  = $fn->getReqParam('class_id');
        $batch_id  = $fn->getReqParam('batch_id');
        
        if ($grade_id != "") {
            $searchVar->sqlSearchVar[] = "g.grade_id = '{$grade_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "g.grade_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, $grade_id);
            
            if ($batch_id != "") {
                $searchVar->sqlSearchVar[] = "b.batch_id = '{$batch_id}'";
            }
            
            if ($exam_type != "") {
                $searchVar->sqlSearchVar[] = "exam_type = '{$exam_type}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   b.title LIKE '%{$tv['keyword']}%'
                OR sg.exam_type LIKE '%{$tv['keyword']}%'
                )";
            }
        }        
    }

    /**
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('contact_id', 'Please select the contact');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
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
     */
    function getEditValidate() {
        return $this->getNewValidate();
    }

    /**
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
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'exam_type');
        $fa = $fn->addToFieldsArray($fa, 'subject_id');
        $fa = $fn->addToFieldsArray($fa, 'batch_id');
        $fa = $fn->addToFieldsArray($fa, 'marks');
        $fa = $fn->addToFieldsArray($fa, 'grade');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'class_id');
        
        return $fa;
    }
    
    /**
     *
     */
    function getStudentGradeSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        if (!$this->getStudentGradeValidate()){
            return $validate->getErrorMessageXML();
        }
                        
        $course_contact_arr = $fn->getPostParam('course_contact_id', array());
        $count = count($course_contact_arr);
        
        for ($i= 0; $i< $count; $i++){
            $course_contact_id = $course_contact_arr[$i];
            $pfx = $course_contact_id . '_' ;
            $marks          = $fn->getPostParam("{$pfx}marks");

            $grade          = $fn->getPostParam("{$pfx}grade");
            $student_result = $fn->getPostParam("{$pfx}student_result");
            $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

            $exam_type      = $fn->getPostParam("exam_type");
            $exam_date      = $fn->getPostParam("exam_date");
            $currentDate    = date("Y-m-d");
            
            $fa = array();
            $fa['contact_id']       = $row['contact_id'];
            $fa['batch_id']         = $row['batch_id'];
            $fa['marks']            = $marks;
            $fa['grade']            = $grade;
            $fa['student_result']   = $student_result;
            $fa['exam_type']        = $exam_type;
            $fa['exam_date']        = $exam_date;
            $fa['creation_date']    = $currentDate;
            $fn->addRecord($fa, 'student_grade');
        }
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getStudentGradeValidate() {
        $validate = Zend_Registry::get('validate');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('exam_type', 'Please choose assessment type');
        $validate->validateData('exam_date', 'Please select the date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     *
     */
    function getEditStudentGradeSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        /*if (!$this->getStudentGradeValidate()){
            return $validate->getErrorMessageXML();
        }*/
                        
        $student_grade_id_arr  = $fn->getPostParam('student_grade_id', array());        
        $count = count($student_grade_id_arr);
        
        for ($i= 0; $i< $count; $i++){
            $student_grade_id = $student_grade_id_arr[$i];            
            $pfx = $student_grade_id . '_' ;

            $marks          = $fn->getPostParam("{$pfx}marks");
            $grade          = $fn->getPostParam("{$pfx}grade");
            $student_result = $fn->getPostParam("{$pfx}student_result");
                        
            $fa = array();
            $fa['marks']   = $marks;
            $fa['grade']            = $grade;
            $fa['student_result']   = $student_result;

            $fn->saveRecord($fa, 'student_grade', 'student_grade_id', $student_grade_id);  
        }
        return $validate->getSuccessMessageXML();
    }
}
