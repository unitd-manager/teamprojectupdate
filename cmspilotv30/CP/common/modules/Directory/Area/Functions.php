<?
class CP_Common_Modules_Directory_Area_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_area');
        $modules->registerModule($modObj, array(
        ));
    }

}