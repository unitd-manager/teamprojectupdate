<?
class CP_Common_Modules_Directory_State_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_state');
        $modules->registerModule($modObj, array(
        ));
    }

}