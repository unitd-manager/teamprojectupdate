<?
class CP_Admin_Modules_Ecommerce_ProductLink_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_productLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'product'
           ,'keyField'  => 'product_id'
        ));
    }
}