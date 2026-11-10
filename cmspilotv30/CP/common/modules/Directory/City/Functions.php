<?
class CP_Common_Modules_Directory_City_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_city');
        $modules->registerModule($modObj, array(
        ));
    }
}