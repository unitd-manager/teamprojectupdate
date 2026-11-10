<?
class CP_Www_Modules_Edukloud_Staff_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_staff');
        $modules->registerModule($modObj, array(
        ));
    }
}