<?
class CP_Common_Modules_Ek_Parent_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_parent');
        $modules->registerModule($modObj, array(
        ));

    }
}