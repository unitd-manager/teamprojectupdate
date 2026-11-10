<?
class CP_Admin_Modules_Tradingsg_StaffAttendance_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT a.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,s.team AS staff_team
        FROM staff_attendance a
        LEFT JOIN (staff s) ON (a.staff_id = s.staff_id) 
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'a';

        $staff_attendance_id  = $fn->getReqParam('staff_attendance_id');
        $staff_id       = $fn->getReqParam('staff_id');
        $special_search = $fn->getReqParam('special_search');

        $userGroupID    = $fn->getSessionParam('userGroupID');
        $staffIDS       = $fn->getSessionParam('staff_id');

        if ($staff_attendance_id != '' ) {
            $searchVar->sqlSearchVar[] = "a.staff_attendance_id  = '{$staff_attendance_id}'";
        } else if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "a.staff_attendance_id  = '{$tv['record_id']}'";
        } else {
            if ($staff_id != '' ) {
                $searchVar->sqlSearchVar[] = "a.staff_id  = '{$staff_id}'";
            }

            if ($tv['special_search'] == "Yes") {
                $searchVar->sqlSearchVar[] = "a.on_leave = 1";
            }

            if ($tv['special_search'] == "No") {
                $searchVar->sqlSearchVar[] = "a.on_leave = 0";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    s.first_name LIKE '%{$tv['keyword']}%'
                 OR s.last_name  LIKE '%{$tv['keyword']}%'
                 OR a.notes  LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($userGroupID != $cpCfg['cp.superAdminUGId']){
                $searchVar->sqlSearchVar[] = "a.staff_id= '{$staffIDS}'";
            }

            $searchVar->sortOrder = "a.record_date DESC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('staff_id', 'Please select the staff');

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
        $fn = Zend_Registry::get('fn');
        
        $hour = $fn->getReqParam('leave_time_dd_hour');
        $min  = $fn->getReqParam('leave_time_dd_minute');

        $validate->resetErrorArray();

        $validate->validateData('staff_id', 'Please select the staff');
        $validate->validateData('record_date', 'Please enter the date');
        
        /*if ($hour == '' || $min == ''){
            $validate->errorArray['leave_time']['name'] = "leave_time";
            $validate->errorArray['leave_time']['msg']  = 'Please enter the time';
        }*/

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
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'record_date');
        $fa = $fn->addToFieldsArray($fa, 'on_leave');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'time_in');
        $fa = $fn->addToFieldsArray($fa, 'leave_time');
        $fa = $fn->addToFieldsArray($fa, 'type_of_leave');

        $fa = $fn->addToFieldsArray($fa, 'time_in_morning');
        $fa = $fn->addToFieldsArray($fa, 'leave_time_morning');
        $fa = $fn->addToFieldsArray($fa, 'time_in_evening');
        $fa = $fn->addToFieldsArray($fa, 'leave_time_evening');

        /*$fa['time_in']    = $fn->getFullHourValueFromDD('time_in');
        $fa['leave_time'] = $fn->getFullHourValueFromDD('leave_time');*/
        
        return $fa;
    }
}
