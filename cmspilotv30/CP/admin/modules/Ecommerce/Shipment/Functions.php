<?
class CP_Admin_Modules_Ecommerce_Shipment_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_shipment');
        $modules->registerModule($modObj, array(
        ));
    }
    
    //==================================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    }