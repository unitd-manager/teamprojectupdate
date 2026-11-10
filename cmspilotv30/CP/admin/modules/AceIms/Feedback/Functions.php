<?
class CP_Admin_Modules_AceIms_Feedback_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_feedback');
        $modules->registerModule($modObj, array(
        ));
    }
}