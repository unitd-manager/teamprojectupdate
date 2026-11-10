<?
class CP_Admin_Modules_Project_TaskHistoryLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_taskHistoryLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'task_history'
           ,'keyField'  => 'task_history_id'
        ));
    }
}
