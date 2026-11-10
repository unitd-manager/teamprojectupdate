<?
class CP_Www_Modules_EdukiteWeb_Task_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukiteWeb_task');
        $modules->registerModule($modObj, array(
        	'tableName' => 'notice',
        	'keyField' => 'notice_id'
        ));
    }
}