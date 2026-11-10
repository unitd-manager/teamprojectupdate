<?
class CP_Common_Modules_Directory_AmbianceLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_ambianceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_ambiance'
           ,'keyField'  => 'business_ambiance_id'
        ));
    }
}
