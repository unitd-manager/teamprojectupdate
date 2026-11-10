<?
class CP_Admin_Modules_Trading_PricingTypeLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_pricingTypeLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'pricing_type'
           ,'keyField' => 'pricing_type_id'
           ,'title' => 'Type of Pricing'
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