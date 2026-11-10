<?
class CP_Admin_Modules_AgileIms_Reports_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }
}