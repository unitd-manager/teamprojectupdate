<?
class CP_Admin_Modules_Party_PartyReminder_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('party_partyReminder');
        $modObj['tableName'] = 'party_reminder';
        $modObj['keyField']  = 'party_reminder_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0,
            'hasEditInList' => 0,
            'title' => 'Party Reminders',
            'actBtnsList' => array(),
        ));
    }

    function setMediaArray($mediaArr) {
    }

    function setLinksArray($inst) {
    }

}