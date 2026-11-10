<?
class CP_Admin_Modules_Project_QuoteItem_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_quoteItem');
        $modules->registerModule($modObj, array(
        ));
    }
}
