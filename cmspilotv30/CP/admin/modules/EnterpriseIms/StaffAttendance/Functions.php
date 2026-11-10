<?
class CP_Admin_Modules_EnterpriseIms_StaffAttendance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_staffAttendance');
        $modules->registerModule($modObj, array(
            'tableName' => 'staff_attendance'
           ,'keyField'  => 'staff_attendance_id'
           ,'title'     => 'Staff Attendance'
        ));
    }
}