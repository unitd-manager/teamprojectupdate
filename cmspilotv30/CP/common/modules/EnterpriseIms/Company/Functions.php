<?
class CP_Common_Modules_EnterpriseIms_Company_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_company');
        $modules->registerModule($modObj, array(
        ));
    }
}