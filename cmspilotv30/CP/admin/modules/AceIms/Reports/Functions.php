<?
class CP_Admin_Modules_AceIms_Reports_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }
}