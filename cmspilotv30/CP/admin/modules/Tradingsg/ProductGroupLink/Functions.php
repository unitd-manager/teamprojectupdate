<?
class CP_Admin_Modules_Tradingsg_ProductGroupLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_productGroupLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'product_group'
           ,'keyField'  => 'product_group_id'
           ,'hasFlagInList'   => 0
        ));
    }
}
