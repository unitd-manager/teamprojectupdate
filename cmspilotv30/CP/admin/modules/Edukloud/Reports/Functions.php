<?
class CP_Admin_Modules_Edukloud_Reports_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }
}