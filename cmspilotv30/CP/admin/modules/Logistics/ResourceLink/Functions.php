<?
class CP_Admin_Modules_Logistics_ResourceLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('logistics_resourceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'resource'
           ,'keyField'  => 'resource_id'
        ));
    }
}
