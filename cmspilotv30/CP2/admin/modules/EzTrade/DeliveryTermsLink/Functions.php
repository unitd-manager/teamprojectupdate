<?
class CP_Admin_Modules_EzTrade_DeliveryTermsLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ezTrade_deliveryTermsLink');
        $modules->registerModule($modObj, array(
            'tableName'   => 'delivery_terms'
           ,'keyField'    => 'delivery_terms_id'
        ));
    }


}
