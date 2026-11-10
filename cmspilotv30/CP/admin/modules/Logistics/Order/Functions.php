<?
class CP_Admin_Modules_Logistics_Order_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('logistics_order');
        $modules->registerModule($modObj, array(
        ));
    }

}