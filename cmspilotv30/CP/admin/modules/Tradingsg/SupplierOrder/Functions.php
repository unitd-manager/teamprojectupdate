<?
class CP_Admin_Modules_Tradingsg_SupplierOrder_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_supplierOrder');
        $modules->registerModule($modObj, array(
            'tableName' => 'supplier_order'
           ,'keyField' => 'supplier_order_id'
           ,'title' => 'Supplier Order'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

    }

}