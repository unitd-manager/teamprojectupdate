<?
class CP_Admin_Modules_Pms_Grade_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $additionalFields = "";
        $additionalJoins = "";

        if ($cpCfg['cp.forAceIms']) {
            $SQL = "
            SELECT g.*
                  ,CONCAT_WS(' ', co.first_name, co.last_name ) AS contact_name
                  ,b.title AS batch_title
                  ,s.title AS subject_title
                  ,c.title AS class_name
            FROM grade g
            LEFT JOIN (contact co) ON (co.contact_id = g.contact_id)
            LEFT JOIN (batch b) ON (b.batch_id = g.batch_id)
            LEFT JOIN (subject s) ON (s.subject_id = g.subject_id)
            LEFT JOIN (class c) ON (c.class_id = g.class_id)
            ";
        } else {
            $SQL = "
            SELECT sg.*
                  ,CONCAT_WS(' ', co.first_name, co.last_name ) AS contact_name
                  ,b.title AS batch_title
            FROM student_grade sg
            LEFT JOIN (contact co) ON (co.contact_id = sg.contact_id)
            LEFT JOIN (batch b) ON (b.batch_id = sg.batch_id)
            ";
        }
        
        return $SQL;
    }

    /**
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');

        $student_grade_id   = $fn->getReqParam('student_grade_id');
        $grade_id   = $fn->getReqParam('grade_id');
        $exam_type = $fn->getReqParam('exam_type');
        $class_id  = $fn->getReqParam('class_id');
        $batch_id  = $fn->getReqParam('batch_id');
        
        if ($cpCfg['cp.forAceIms']) {
            $searchVar->mainTableAlias = 'g';
            $table_id = $grade_id;
            $table_id_with_prefix = "g.grade_id";
        } else {
            $searchVar->mainTableAlias = 'sg';
            $table_id = $student_grade_id;
            $table_id_with_prefix = "sg.student_grade_id";
        }
        
        if ($table_id != "") {
            $searchVar->sqlSearchVar[] = "{$table_id_with_prefix}  = '{$table_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "{$table_id_with_prefix}  = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, $table_id_with_prefix);
            
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
