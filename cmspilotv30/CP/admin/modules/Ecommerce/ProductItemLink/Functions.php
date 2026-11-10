<?
class CP_Admin_Modules_Ecommerce_ProductItemLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_productItemLink');
        $modObj['keyField'] = 'product_item_id';
        $modules->registerModule($modObj, array(
            'tableName'     => 'product_item'
           ,'title'         => 'Product Item'
           ,'keyField'      => 'product_item_id'
           ,'hasFlagInList' => 0
        ));
    }

}