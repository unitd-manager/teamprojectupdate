<?
class CP_Admin_Modules_Wine_DeliveryHolidayLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('wine_deliveryHolidayLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'delivery_holiday'
           ,'keyField'  => 'delivery_holiday_id'
        ));
    }
}
