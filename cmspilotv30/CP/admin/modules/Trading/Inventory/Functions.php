<?
class CP_Admin_Modules_Trading_Inventory_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_inventory');
        $modules->registerModule($modObj, array(
            'tableName' => 'inventory'
           ,'keyField' => 'inventory_id'
           ,'title' => 'Inventory'
           ,'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
        ));
    }

    /**
     *
     */
    function setMediaArray($inst) {
        getCPFnObj('trading_product')
        ->setMediaArray($inst);

    }

    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $linkObj = $inst->getLinksArrayObj('trading_inventory', 'trading_pricingTypeLink', array(
            'historyTableName' => 'product_pricing_type'
           ,'hasPortalDetail' => 0
           ,'hasPortalEdit' => 0
           ,'hasPortalNew' => 0
           ,'linkingType' => 'portal'
           ,'fieldlabel' => array('Pricing Type'
                                 ,'Price'
                                 ,''
                            )
            ,'fieldClassArray' => array(
                1 => 'al-right'
            )
        ));
        $inst->registerLinksArray($linkObj);
    }

}