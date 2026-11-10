<?
class CP_Common_Modules_Common_GeoCountry_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('common_geoCountry');
        $modules->registerModule($modObj, array(
            'title'     => 'Geo Country'
           ,'tableName' => 'geo_country'
           ,'keyField'  => 'geo_country_id'
        ));
    }
}