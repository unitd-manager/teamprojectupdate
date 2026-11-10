<?
class CP_Common_Modules_EnterpriseIms_Course_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enterpriseIms_course');
        $modules->registerModule($modObj, array(
            'title' => 'Program'
        ));
    }
}