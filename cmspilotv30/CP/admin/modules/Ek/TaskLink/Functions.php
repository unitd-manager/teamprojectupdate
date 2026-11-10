<?
class CP_Admin_Modules_Ek_TaskLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('common_taskLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'task'
           ,'keyField'  => 'task_id'
        ));
    }
}
