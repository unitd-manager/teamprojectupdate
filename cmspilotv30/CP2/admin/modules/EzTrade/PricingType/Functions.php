<?
class CP_Admin_Modules_EzTrade_PricingType_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ezTrade_pricingType');
        $modules->registerModule($modObj, array(
            'tableName' => 'pricing_type'
           ,'keyField' => 'pricing_type_id'
           ,'title' => 'Type of Pricing'
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