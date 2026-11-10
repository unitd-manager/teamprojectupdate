<?
class CPL_Admin_Modules_Common_Dashboard_Model extends CP_Admin_Modules_Common_Dashboard_Model
{
    /**
     *
     */
    function getMarkAttendanceFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!$this->getMarkAttendanceFormValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $staff_id   = $fn->getPostParam('staff_id');
        $from_date  = $fn->getPostParam('from_date');
        $to_date    = $fn->getPostParam('to_date');
        
        $begin = new DateTime($from_date);
        $end   = new DateTime($to_date);

        //Create array with all dates within date span
    	while($begin < $end) {
    		$interval[] = $begin->format('Y-m-d');
    		$begin->modify('+1 day');
    	}

        foreach($interval as $date){
            $timestamp   = strtotime($date);
            $record_day  = date("D", $timestamp);

            // Inserting attendance record excluding Sunday
            if ($record_day != 'Sun') {    
                $sqlAtt = "
                SELECT record_date FROM attendance
                WHERE staff_id = {$staff_id}
                  AND record_date = '{$date}'
                ";
                $resultAtt  = $db->sql_query($sqlAtt);
                $numRowsAtt = $db->sql_numrows($resultAtt);
                
                if ($numRowsAtt == 0) {
                    $fa = array();
                    $fa['staff_id']         = $staff_id;
                    $fa['record_date']      = $date;
                    $fa['on_leave']         = 1;
                    $fa['creation_date']    = date('Y-m-d H:i:s');
                    $fa['created_by']       = $_SESSION['userFullName'];
                
                    $SQLInsertStaffAtt      = $dbUtil->getInsertSQLStringFromArray($fa, "attendance");
                    $resultInsertStaffAtt   = $db->sql_query($SQLInsertStaffAtt);
                    $attendance_id          = $db->sql_nextid();
                }
            }
        }
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getMarkAttendanceFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('staff_id'  , 'Please select the staff');
        $validate->validateData('from_date' , 'Please select from date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
