<?
class CP_Common_Modules_Directory_DeliveryLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_deliveryLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_delivery'
           ,'keyField'  => 'business_delivery_id'
        ));
    }
}
