<?
class CP_Admin_Modules_Ecommerce_CityLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('ecommerce_cityLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'city'
           ,'keyField'  => 'city_id'
        ));
    }
}
