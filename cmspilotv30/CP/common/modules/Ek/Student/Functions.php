<?
class CP_Common_Modules_Ek_Student_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_student');
        $modules->registerModule($modObj, array(
        ));
    }
}