<?
class CP_Admin_Modules_EnterpriseIms_Message_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enterpriseIms_message');
        $modObj['tableName'] = 'message';
        $modObj['keyField']  = 'message_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
        ));
    }
}