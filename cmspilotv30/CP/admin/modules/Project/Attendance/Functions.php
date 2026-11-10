<?
class CP_Admin_Modules_Project_Attendance_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_attendance');
        $modules->registerModule($modObj, array(
            'hasFlagInList'=> 0
           ,'moduleGroup'   => 'project'
           ,'actBtnsList'   => array('new', 'export')
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