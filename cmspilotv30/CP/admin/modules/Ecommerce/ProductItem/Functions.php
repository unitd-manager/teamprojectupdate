<?
class CP_Admin_Modules_Ecommerce_ProductItem_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_productItem');
        $modObj['keyField'] = 'product_item_id';
        $modules->registerModule($modObj, array(
            'tableName'     => 'product_item'
           ,'title'         => 'Product Item'
           ,'keyField'      => 'product_item_id'
           ,'hasFlagInList' => 0
        ));
    }
}