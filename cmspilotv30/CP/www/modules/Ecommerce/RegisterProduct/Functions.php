<?
class CP_Www_Modules_Ecommerce_RegisterProduct_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_registerProduct');
        $modules->registerModule($modObj, array(
        ));
    }
}
