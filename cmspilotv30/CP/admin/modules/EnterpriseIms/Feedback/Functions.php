<?
class CP_Admin_Modules_EnterpriseIms_Feedback_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enterpriseIms_feedback');
        $modules->registerModule($modObj, array(
        ));
    }
}