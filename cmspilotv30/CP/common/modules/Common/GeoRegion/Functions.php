<?
class CP_Common_Modules_Common_GeoRegion_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('common_geoRegion');
        $modules->registerModule($modObj, array(
            'title'      => 'Geo Region'
           ,'tableName'  => 'geo_region'
           ,'keyField'   => 'geo_region_id'
           ,'titleField' => 'name'
        ));
    }
}