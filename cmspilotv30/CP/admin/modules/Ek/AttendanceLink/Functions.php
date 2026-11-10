<?
class CP_Admin_Modules_Ek_AttendanceLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ek_attendanceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'attendance'
           ,'keyField'  => 'attendance_id'
        ));
    }
}
