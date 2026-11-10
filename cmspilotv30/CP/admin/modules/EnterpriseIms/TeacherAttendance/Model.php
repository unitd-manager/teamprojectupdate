<?
class CP_Admin_Modules_EnterpriseIms_TeacherAttendance_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT t.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS teacher_name
        FROM teacher_attendance t
        LEFT JOIN (teacher c) ON (t.teacher_id = c.teacher_id)
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

        $teacher_attendance_id = $fn->getReqParam('teacher_attendance_id');

        if ($teacher_attendance_id != "") {
            $searchVar->sqlSearchVar[] = "t.teacher_attendance_id = '{$teacher_attendance_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.teacher_attendance_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 't.teacher_attendance_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
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
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'teacher_id');
        $fa = $fn->addToFieldsArray($fa, 'time_in');
        $fa = $fn->addToFieldsArray($fa, 'time_out');
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'hours');
        $fa = $fn->addToFieldsArray($fa, 'teacher_attendance_id');
        
        return $fa;
    }
}
