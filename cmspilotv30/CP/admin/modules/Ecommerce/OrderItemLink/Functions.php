<?
class CP_Admin_Modules_Ecommerce_OrderItemLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('ecommerce_orderItemLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'order_item'
           ,'keyField'  => 'order_item_id'
           ,'titleField'=> 'item_title'
        ));
    }
}
