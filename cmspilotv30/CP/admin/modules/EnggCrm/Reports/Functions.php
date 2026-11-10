<?
class CP_Admin_Modules_EnggCrm_Reports_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }
}