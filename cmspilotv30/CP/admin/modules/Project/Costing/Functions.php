<?
class CP_Admin_Modules_Project_Costing_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_costing');
        $modules->registerModule($modObj, array(
        ));
    }
}
