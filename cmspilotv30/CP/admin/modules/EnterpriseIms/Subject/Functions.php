<?
class CP_Admin_Modules_EnterpriseIms_Subject_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_subject');
        $modules->registerModule($modObj, array(
            'title'         => 'Subject'
        ));
    }
}