<?
class CP_Admin_Modules_AgileIms_Feedback_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_feedback');
        $modules->registerModule($modObj, array(
            'actBtnsEdit' => array('save', 'apply', 'delete')
        ));
    }
}