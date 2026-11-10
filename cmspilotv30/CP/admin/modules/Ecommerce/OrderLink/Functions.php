<?
class CP_Admin_Modules_Ecommerce_OrderLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('ecommerce_orderLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'order'
           ,'keyField'  => 'order_id'
           ,'mainModuleName' => 'ecommerce_order'
        ));
    }
}