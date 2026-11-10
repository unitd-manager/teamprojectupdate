<?
class CP_Admin_Modules_EnggCrm_Attendance_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('enggCrm_attendance');
        $modules->registerModule($modObj, array(
            'hasFlagInList'=> 0
           ,'moduleGroup'   => 'project'
           ,'actBtnsList'   => array('new', 'export')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'  => array('save', 'apply')
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