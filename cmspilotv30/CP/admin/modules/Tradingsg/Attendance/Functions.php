<?
class CP_Admin_Modules_Tradingsg_Attendance_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_attendance');
        $modules->registerModule($modObj, array(
            'hasFlagInList'=> 0
           ,'moduleGroup'   => 'tradingsg'
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