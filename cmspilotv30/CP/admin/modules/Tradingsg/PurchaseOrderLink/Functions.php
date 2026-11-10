<?
class CP_Admin_Modules_Tradingsg_PurchaseOrderLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_purchaseOrderLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'po_product'
           ,'keyField'  => 'po_product_id'
        ));
    }
}
