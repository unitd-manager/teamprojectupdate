<?
class CP_Common_Modules_Directory_PreferenceLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_preferenceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'contact_preference'
           ,'keyField'  => 'contact_preference_id'
        ));
    }
}
