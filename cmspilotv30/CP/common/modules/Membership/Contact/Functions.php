<?
class CP_Common_Modules_Membership_Contact_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('membership_contact');
        $modules->registerModule($modObj, array(
        ));
    }
}
