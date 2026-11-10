<?
class CP_Admin_Modules_AceIms_Attendance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_attendance');
        $modules->registerModule($modObj, array(
        ));
    }
}