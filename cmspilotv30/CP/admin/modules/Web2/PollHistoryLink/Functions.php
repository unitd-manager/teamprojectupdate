<?
class CP_Admin_Modules_Web2_PollHistoryLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('web2_pollHistoryLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'poll_history'
           ,'keyField'      => 'poll_history_id'
        ));
    }
}
