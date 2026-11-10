<?
class CP_Admin_Modules_Pms_Reports_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }
}