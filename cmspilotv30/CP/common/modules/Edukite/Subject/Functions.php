<?
class CP_Common_Modules_Edukite_Subject_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_subject');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
    }
}