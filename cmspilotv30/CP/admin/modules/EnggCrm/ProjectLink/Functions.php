<?
class CP_Admin_Modules_EnggCrm_ProjectLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_projectLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'project'
           ,'keyField'  => 'project_id'
        ));
    }
}
