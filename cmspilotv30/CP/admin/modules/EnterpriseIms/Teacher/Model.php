<?
class CP_Admin_Modules_EnterpriseIms_Teacher_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        
        $course_id = $fn->getReqParam('course_id');

        $extraFieldNames = '';
        $extraTableNames = '';

        if ($course_id != "") {
            $extraFieldNames .= "tc.teacher_course_id";
            $extraTableNames .= "JOIN teacher_course tc ON (t.teacher_id = tc.teacher_id)";
        }

        $SQL = "
        SELECT t.*
              ,gc.name AS country
        FROM teacher t
        LEFT JOIN geo_country gc ON (t.country_code = gc.country_code)
        {$extraTableNames}
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
        $searchVar->mainTableAlias = 't';

        $teacher_id   = $fn->getReqParam('teacher_id');
        $course_id    = $fn->getReqParam('course_id');
        $subject_id   = $fn->getReqParam('subject_id');
        $trainer_type = $fn->getReqParam('trainer_type');

        /*if ($subject_id != "") {
            $searchVar->sqlSearchVar[] = "t.subject_id = '{$subject_id}'";
        }*/
        if ($teacher_id != "") {
            $searchVar->sqlSearchVar[] = "t.teacher_id = '{$teacher_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.teacher_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 't.teacher_id');
            
            if ($course_id != '' ) {
                $searchVar->sqlSearchVar[] = "tc.course_id = {$course_id}";
            }

            if ($trainer_type != '' ) {
                $searchVar->sqlSearchVar[] = "t.trainer_type = '{$trainer_type}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   t.first_name LIKE '%{$tv['keyword']}%'
                OR t.last_name LIKE '%{$tv['keyword']}%'
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
        $validate->validateData('first_name', 'Please enter the first name');

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

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the first name');

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'teacher_code');
        $fa = $fn->addToFieldsArray($fa, 'subjects');
        $fa = $fn->addToFieldsArray($fa, 'subject_major');
        $fa = $fn->addToFieldsArray($fa, 'qualification');
        $fa = $fn->addToFieldsArray($fa, 'university');
        $fa = $fn->addToFieldsArray($fa, 'experience');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'login_enabled');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'city');
        $fa = $fn->addToFieldsArray($fa, 'state');
        $fa = $fn->addToFieldsArray($fa, 'zip_code');
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'remarks');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        //$fa = $fn->addToFieldsArray($fa, 'assessor');
        $fa = $fn->addToFieldsArray($fa, 'trainer_type');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_working');
        $fa = $fn->addToFieldsArray($fa, 'work_availability');
        $fa = $fn->addToFieldsArray($fa, 'payment_type');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'bank_details');

        return $fa;
    }

    /**
     *
     */
    function getEnterpriseImsTeacherEnterpriseImsCourseLinkSQL($id) {
        return "
        SELECT tc.teacher_course_id
              ,c.title
        FROM teacher_course tc 
        LEFT JOIN course c ON (c.course_id = tc.course_id)
        WHERE tc.teacher_id = '{$id}'
        ORDER BY tc.teacher_course_id
        ";
    }

    /**
     *
     */
    function getTeacherSQL() {
        return "
        SELECT t.teacher_id
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
        FROM teacher t 
        ORDER BY t.teacher_id
        ";
    }
}