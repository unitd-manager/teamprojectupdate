<?
class CP_Common_Modules_Directory_Borough_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_borough');
        $modules->registerModule($modObj, array(
        ));
    }

}