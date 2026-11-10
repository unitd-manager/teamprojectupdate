<?
class CP_Admin_Modules_AceIms_Grade_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_grade');
        $modules->registerModule($modObj, array(
        ));
    }
}