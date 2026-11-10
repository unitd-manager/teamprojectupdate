<?
class CP_Admin_Modules_EzTrade_DeliveryAddressLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ezTrade_deliveryAddressLink');
        $modules->registerModule($modObj, array(
            'tableName'   => 'delivery_address'
           ,'keyField'    => 'delivery_address_id'
        ));
    }

    /**
     *
     */
    function getShipToLocationSQLFields($alias = '') {
        if ($alias) {
            $alias = "{$alias}.";
        }
        $text = "
        CONCAT_WS(', '
                  ,NULLIF({$alias}address_flat, '')
                  ,NULLIF({$alias}address_street, '')
                  ,NULLIF({$alias}address_town, '')
                  ,NULLIF({$alias}address_state, '')
                  ,NULLIF({$alias}address_country, '')
                  ,NULLIF({$alias}address_po_code, '')
                 )
        ";
        return $text;
    }
}