<?
class CP_Www_Modules_Edukloud_Parent_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_parent');
        $modules->registerModule($modObj, array(
        ));
    }
}