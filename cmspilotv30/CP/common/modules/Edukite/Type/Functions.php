<?
class CP_Common_Modules_Edukite_Type_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_type');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
    }
}