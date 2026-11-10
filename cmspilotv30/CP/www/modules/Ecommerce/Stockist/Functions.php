<?
class CP_Www_Modules_Ecommerce_Stockist_Functions extends CP_Common_Modules_Ecommerce_Stockist_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_stockist');
        $modules->registerModule($modObj, array(
        ));
    }
}
