<?
class CP_Admin_Modules_Common_GeoCity_Functions extends CP_Common_Modules_Common_GeoCity_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('common_geoCity');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'title'      => 'Geo City'
           ,'tableName'  => 'geo_city'
           ,'keyField'   => 'geo_city_id'
           ,'titleField' => 'name'
        ));
    }
}