<?
class CP_Admin_Modules_Directory_CityLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_cityLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'city'
           ,'keyField'  => 'city_id'
           ,'hasFlagInList'  => false
        ));
    }
}