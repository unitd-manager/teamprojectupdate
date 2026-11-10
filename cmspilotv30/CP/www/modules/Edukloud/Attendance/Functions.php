<?
class CP_Www_Modules_Edukloud_Attendance_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_attendance');
        $modules->registerModule($modObj, array(
        ));
    }
}
