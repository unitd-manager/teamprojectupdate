<?
class CPL_Admin_Modules_EnggCrm_Attendance_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT a.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,s.team AS staff_team
        FROM attendance a
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

        $attendance_id  	= $fn->getReqParam('attendance_id');
        $staff_id       	= $fn->getReqParam('staff_id');
        $special_search 	= $fn->getReqParam('special_search');

        $userGroupID    	= $fn->getSessionParam('userGroupID');
        $staffIDS       	= $fn->getSessionParam('staff_id');
        $attendanceDate1    = $fn->getReqParam('attendanceDate1');
        $attendanceDate2    = $fn->getReqParam('attendanceDate2');

        if ($attendance_id != '' ) {
            $searchVar->sqlSearchVar[] = "a.attendance_id  = '{$attendance_id}'";
        } else if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "a.attendance_id  = '{$tv['record_id']}'";
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

            if ($attendanceDate1 != "" && $attendanceDate1 != "From"
            && $attendanceDate2 != "" && $attendanceDate2 != "To" ) {
                $searchVar->sqlSearchVar[] = "(a.record_date BETWEEN '{$attendanceDate1}' AND '{$attendanceDate2}')";
            }

            if ($attendanceDate1 != "" && $attendanceDate1 != "From" && $attendanceDate2 == "To") {
                $searchVar->sqlSearchVar[] = "(a.record_date >= '{$attendanceDate1}')";
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
        $fa = $fn->addToFieldsArray($fa, 'task');

        /*$fa['time_in']    = $fn->getFullHourValueFromDD('time_in');
        $fa['leave_time'] = $fn->getFullHourValueFromDD('leave_time');*/
        
        return $fa;
    }

    function getAddNewValuelistFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        
        $valuelist_value = $fn->getPostParam('valuelist_value');
        $valuelist_name  = $fn->getReqParam('valuelist_name');

        /*if (!$this->getAddNewValuelistFormValidate($valuelist_name, $valuelist_value)){
            return $validate->getErrorMessageXML();
        }*/
        
        $fa = array();
        $fa['key_text']      = $valuelist_name;
        $fa['value']         = $valuelist_value;
        $fa['creation_date'] = date("Y-m-d H:i:s");
        
        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'valuelist');
        $result = $db->sql_query($insert);
        $id     = $db->sql_nextid();
            
        return $validate->getSuccessMessageXML('', $valuelist_value);
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        
            $fa = array(
                  'staff_name'    => $phpExcel->getFldObj('Staff Name')
                 ,'record_date'   => $phpExcel->getFldObj('Date')
                 ,'on_leave'   	  => $phpExcel->getFldObj('On Leave')
                 ,'time_in'   	  => $phpExcel->getFldObj('Time In')
                 ,'leave_time'    => $phpExcel->getFldObj('Time out')
            );

        $file_name = "Attendance_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

}
