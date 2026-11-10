<?
class CP_Admin_Modules_Edukite_TaskHistory_Functions
{

    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_taskHistory');
        $modules->registerModule($modObj, array(
            'title'       => 'Task History'
           ,'tableName'   => 'task_history'
           ,'keyField'    => 'task_history_id'
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
}