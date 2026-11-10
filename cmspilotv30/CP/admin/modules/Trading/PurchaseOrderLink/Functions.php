<?
class CP_Admin_Modules_Trading_PurchaseOrderLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_purchaseOrderLink');
        $modules->registerModule($modObj);
    }


}