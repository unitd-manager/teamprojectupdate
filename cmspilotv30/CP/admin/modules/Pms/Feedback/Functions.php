<?
class CP_Admin_Modules_Pms_Feedback_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_feedback');
        $modules->registerModule($modObj, array(
        ));
    }
}