<?
class CP_Admin_Modules_Edukite_Task_Functions extends CP_Common_Modules_Edukite_Task_Functions
{

    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_task');
        $modules->registerModule($modObj, array(
        ));
    }

}
