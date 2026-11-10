<?
class CP_Admin_Modules_Tradingsg_ProductGroup_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_productGroup');
        $modules->registerModule($modObj, array(
            'title'         => 'Product Group'
           ,'tableName' => 'product_group'
           ,'keyField'  => 'product_group_id'
           ,'hasMultiLang' => 1
           ,'hasFlagInList' => 0
        ));
    }
    
}