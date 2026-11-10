<?
class CP_Admin_Modules_Hms_Reports_Functions
{
    function setModuleArray($modules){
        //$cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('hms_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }

}