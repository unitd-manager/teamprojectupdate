<?
class CP_Admin_Modules_Party_MessageLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('party_messageLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'message'
           ,'keyField'  => 'message_id'
           ,'mainModuleName' => 'party_message'
        ));
    }
}