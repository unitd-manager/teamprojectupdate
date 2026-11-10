<?
class CP_Admin_Modules_Project_ProjectLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_projectLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'project'
           ,'keyField'  => 'project_id'
        ));
    }
}
