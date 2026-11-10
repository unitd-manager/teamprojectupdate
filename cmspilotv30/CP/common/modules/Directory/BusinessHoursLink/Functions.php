<?
class CP_Common_Modules_Directory_BusinessHoursLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_businessHoursLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_hours'
           ,'keyField'  => 'business_hours_id'
        ));
    }
}
