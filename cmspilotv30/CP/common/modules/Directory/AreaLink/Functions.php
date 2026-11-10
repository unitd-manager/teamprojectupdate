<?
class CP_Common_Modules_Directory_AreaLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_areaLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'contact_area'
           ,'keyField'  => 'contact_area_id'
        ));
    }
}
