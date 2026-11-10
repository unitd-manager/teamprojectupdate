<?
class CP_Admin_Modules_Trading_ProductLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_productLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'product'
           ,'keyField'  => 'product_id'
        ));
    }
}
