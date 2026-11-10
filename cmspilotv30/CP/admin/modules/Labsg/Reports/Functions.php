<?
class CP_Admin_Modules_Labsg_Reports_Functions
{
    function setModuleArray($modules){
        //$cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('labsg_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }

}