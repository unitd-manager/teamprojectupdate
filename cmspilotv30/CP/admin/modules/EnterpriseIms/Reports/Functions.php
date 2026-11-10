<?
class CP_Admin_Modules_enterpriseIms_Reports_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }
}