<?
class CP_Admin_Modules_EzTrade_Inventory_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ezTrade_inventory');
        $modules->registerModule($modObj, array(
            'tableName' => 'inventory'
           ,'keyField' => 'inventory_id'
           ,'title' => 'Inventory'
           ,'actBtnsList' => array('new')
        ));
    }

    /**
     *
     */
    function setMediaArray($inst) {
    }
    
    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
    }

}