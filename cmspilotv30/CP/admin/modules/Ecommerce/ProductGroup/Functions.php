<?
class CP_Admin_Modules_Ecommerce_ProductGroup_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_productGroup');
        $modObj['tableName'] = 'product_group';
        $modObj['keyField']  = 'product_group_id';
        $modules->registerModule($modObj, array(
            'title'         => 'Product Group'
           ,'hasFlagInList' => 0
        ));
    }
    
    //==================================================================//
    //==================================================================//
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');

    }

}