<?
class CP_Www_Modules_Edukloud_Student_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_student');
        $modules->registerModule($modObj, array(
        ));
    }
}