<?
class CP_Admin_Modules_EnggCrm_TaskHistoryLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_taskHistoryLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'task_history'
           ,'keyField'  => 'task_history_id'
        ));
    }
}
