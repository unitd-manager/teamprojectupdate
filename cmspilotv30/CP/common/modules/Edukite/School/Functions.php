<?
class CP_Common_Modules_Edukite_School_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_school');
        $modules->registerModule($modObj, array(
        ));

    }
}