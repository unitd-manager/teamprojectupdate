<?
class CP_Www_Modules_EdukiteWeb_Notice_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukiteWeb_notice');
        $modules->registerModule($modObj, array(
        ));
    }
}