<?
class CP_Www_Modules_Edukloud_Class_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_class');
        $modules->registerModule($modObj, array(
        ));
    }
    
}