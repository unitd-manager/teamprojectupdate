<?
class CP_Common_Modules_Ek_Subject_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_subject');
        $modules->registerModule($modObj, array(
        ));
    }
}