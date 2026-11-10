<?
class CP_Admin_Modules_EnterpriseIms_Insurance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_insurance');
        $modules->registerModule($modObj, array(
            'title'         => 'Insurance'
        ));
    }
}