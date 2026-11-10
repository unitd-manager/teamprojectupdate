<?
class CP_Admin_Modules_Common_GeoCountry_Functions extends CP_Common_Modules_Common_GeoCountry_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('common_geoCountry');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'title'     => 'Geo Country'
           ,'tableName' => 'geo_country'
           ,'keyField'  => 'geo_country_id'
           ,'hasFlagInList'  => 0
        ));
    }
}