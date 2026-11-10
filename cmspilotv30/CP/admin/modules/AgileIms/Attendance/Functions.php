<?
class CP_Admin_Modules_AgileIms_Attendance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_attendance');
        $modules->registerModule($modObj, array(
           'actBtnsEdit' => array('save', 'apply', 'delete')
        ));
    }
}