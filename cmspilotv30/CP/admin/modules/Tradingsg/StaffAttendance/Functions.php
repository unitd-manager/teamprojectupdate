<?
class CP_Admin_Modules_Tradingsg_StaffAttendance_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_staffAttendance');
        $modules->registerModule($modObj, array(
            'hasFlagInList'=> 0
           ,'title'   => 'Staff Attendance'
           ,'tableName' => 'staff_attendance'
           ,'keyField' => 'staff_attendance_id'
        ));
    }

    /**
     *
     */
    function setLocalArrayValues(){
        $tv = Zend_Registry::get('tv');

        array_push($tv['protSiteSpActionExceptions'], 'sendAttendanceReportToPM');
    }
}