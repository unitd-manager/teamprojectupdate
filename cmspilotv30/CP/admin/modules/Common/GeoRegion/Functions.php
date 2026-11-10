<?
class CP_Admin_Modules_Common_GeoRegion_Functions extends CP_Common_Modules_Common_GeoRegion_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('common_geoRegion');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'title'      => 'Geo Region'
           ,'tableName'  => 'geo_region'
           ,'keyField'   => 'geo_region_id'
           ,'titleField' => 'name'
        ));
    }
}