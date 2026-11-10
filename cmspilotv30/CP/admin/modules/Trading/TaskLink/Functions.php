<?
class CP_Admin_Modules_Trading_TaskLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_taskLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'task'
           ,'keyField'  => 'task_id'
        ));
    }
}
