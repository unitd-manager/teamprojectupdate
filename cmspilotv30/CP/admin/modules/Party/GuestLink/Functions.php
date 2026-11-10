<?
class CP_Admin_Modules_Party_GuestLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('party_guestLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'guest_list'
           ,'keyField'  => 'guest_list_id'
        ));
    }
}