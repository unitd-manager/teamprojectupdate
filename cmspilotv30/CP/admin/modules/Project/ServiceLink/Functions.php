<?
class CP_Admin_Modules_Project_ServiceLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_serviceLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'service'
           ,'keyField'  => 'service_id'
        ));
    }
}
