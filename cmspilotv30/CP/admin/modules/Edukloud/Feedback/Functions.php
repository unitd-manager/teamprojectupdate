<?
class CP_Admin_Modules_Edukloud_Feedback_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_feedback');
        $modules->registerModule($modObj, array(
        ));
    }
}