<?
class CP_Admin_Modules_Tradingsg_DiscountLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_discountLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'discount'
           ,'keyField'  => 'discount_id'
        ));
    }
}
