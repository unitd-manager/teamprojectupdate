<?
class CP_Admin_Modules_Directory_ExternalSourceLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_externalSourceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_external_source'
           ,'keyField'  => 'business_external_source_id'
        ));
    }
}
