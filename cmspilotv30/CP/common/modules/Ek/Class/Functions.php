<?
class CP_Common_Modules_Ek_Class_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_class');
        $modules->registerModule($modObj, array(
        ));
    }
}