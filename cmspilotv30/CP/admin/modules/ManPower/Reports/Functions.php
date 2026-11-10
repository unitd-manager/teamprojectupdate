<?
class CP_Admin_Modules_ManPower_Reports_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }
}