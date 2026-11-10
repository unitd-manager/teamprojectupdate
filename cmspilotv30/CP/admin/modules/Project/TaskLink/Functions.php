<?
class CP_Admin_Modules_Project_TaskLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_taskLink');
        $modObj['tableName'] = 'task';
        $modObj['keyField']  = 'task_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
        ));
    }
}
