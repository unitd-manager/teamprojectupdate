<?
class CP_Admin_Modules_AgileIms_Attendance_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT a.*
              ,b.title AS batch_title
              ,c.first_name AS contact_name
        FROM attendance a
        LEFT JOIN (batch b) ON (a.batch_id = b.batch_id)
        LEFT JOIN (contact c) ON (a.contact_id = c.contact_id)
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
        
        $searchVar->mainTableAlias = 'a';

        $attendance_id = $fn->getReqParam('attendance_id');

        if ($attendance_id != "") {
            $searchVar->sqlSearchVar[] = "a.attendance_id = '{$attendance_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "a.attendance_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.attendance_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
											   c.first_name      LIKE '%{$tv['keyword']}%' OR
											   b.title           LIKE '%{$tv['keyword']}%' 
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
        $validate->validateData('date', 'Please select the date');

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
        $validate->validateData('contact_id', 'Please select the student');
        $validate->validateData('batch_id', 'Please select the batch');
        $validate->validateData('date', 'Please select the date');
        $validate->validateData('status', 'Please check the status');

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

        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'batch_id');
        
        return $fa;
    }

    /**
     */
    function getTakeAttendanceSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        if (!$this->getTakeAttendanceValidate()){
            return $validate->getErrorMessageXML();
        }
                        
        $course_contact_arr  = $fn->getPostParam('course_contact_id', array());        
        $count = count($course_contact_arr);
        
        for ($i= 0; $i< $count; $i++){
            $course_contact_id = $course_contact_arr[$i];            
            $pfx = $course_contact_id . '_' ;
            $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);
            
            $status      = $fn->getPostParam("{$pfx}status");
            $currentDate = date("Y-m-d");
            
            $fa = array();
            $fa['contact_id'] = $row['contact_id'];
            $fa['batch_id']   = $row['batch_id'];
            $fa['status']     = $status;
            $fa['date']       = $currentDate;
            $fn->addRecord($fa, 'attendance');
        }
        return $validate->getSuccessMessageXML();
    }
    
    /**
     *
     */
    function getTakeAttendanceValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData("status" , $ln->gd("cp.form.fld.status.err") , "status");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditAttendanceSubmit() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        
        if (!$this->getTakeAttendanceValidate()){
            return $validate->getErrorMessageXML();
        }
                        
        $attendance_id_arr = $fn->getPostParam('attendance_id', array());        
        $count = count($attendance_id_arr);
        
        for ($i= 0; $i< $count; $i++){
            $attendance_id = $attendance_id_arr[$i];            
            $pfx = $attendance_id . '_' ;
            $status  = $fn->getPostParam("{$pfx}status");
                        
            $fa = array();
            $fa['status']     = $status;
            $fn->saveRecord($fa, 'attendance', 'attendance_id', $attendance_id);
        }
        return $validate->getSuccessMessageXML();
    }
}
