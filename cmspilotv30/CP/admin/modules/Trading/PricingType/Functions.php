<?
class CP_Admin_Modules_Trading_PricingType_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_pricingType');
        $modules->registerModule($modObj, array(
            'tableName' => 'pricing_type'
           ,'keyField' => 'pricing_type_id'
           ,'title' => 'Type of Pricing'
           ,'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
           ,'titleField' => 'pricing_type'
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