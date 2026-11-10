<?
class CP_Admin_Modules_Project_TaskHistory_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_taskHistory');
        $modules->registerModule($modObj, array(
            'tableName'     => 'task_history'
           ,'keyField'      => 'task_history_id'
        ));
    }
}
