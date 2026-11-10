<?
class CP_Admin_Modules_Trading_QuoteLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_quoteLink');
        $modules->registerModule($modObj);
    }


}