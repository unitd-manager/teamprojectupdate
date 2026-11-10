<?
class CP_Admin_Modules_Party_PartySetupLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('party_partySetupLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'party_setup'
           ,'keyField'  => 'party_setup_id'
        ));
    }
}