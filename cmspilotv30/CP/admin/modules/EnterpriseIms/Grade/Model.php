<?
class CP_Admin_Modules_EnterpriseIms_Grade_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT sg.*
              ,CONCAT_WS(' ', co.first_name, co.last_name ) AS contact_name
              ,b.title AS batch_title
        FROM student_grade sg
        LEFT JOIN (contact co) ON (co.contact_id = sg.contact_id)
        LEFT JOIN (batch b) ON (b.batch_id = sg.batch_id)
        ";
        
        return $SQL;
    }

    /**
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'sg';

        $student_grade_id   = $fn->getReqParam('student_grade_id ');
        $exam_type = $fn->getReqParam('exam_type');
        $class_id  = $fn->getReqParam('class_id');
        $batch_id  = $fn->getReqParam('batch_id');

        if ($student_grade_id != "") {
            $searchVar->sqlSearchVar[] = "sg.student_grade_id  = '{$student_grade_id }'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "sg.student_grade_id  = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'sg.student_grade_id ');
            
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
}
