<?
class CP_Common_Modules_Ek_School_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_school');
        $modules->registerModule($modObj, array(
        ));

    }
}