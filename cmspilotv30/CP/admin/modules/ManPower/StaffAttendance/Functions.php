<?
class CP_Admin_Modules_ManPower_StaffAttendance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('manPower_staffAttendance');
        $modules->registerModule($modObj, array(
            'tableName' => 'staff_attendance'
           ,'keyField'  => 'staff_attendance_id'
           ,'title'     => 'Staff Attendance'
           ,'actBtnsEdit' => array('save', 'apply', 'cancel')
        ));
    }
}