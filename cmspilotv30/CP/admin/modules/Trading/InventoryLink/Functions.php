<?
class CP_Admin_Modules_Trading_InventoryLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_inventoryLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'inventory'
           ,'keyField'  => 'inventory_id'
        ));
    }
}
