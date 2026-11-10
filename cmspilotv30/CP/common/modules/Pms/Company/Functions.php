<?
class CP_Common_Modules_Pms_Company_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_company');
        $modules->registerModule($modObj, array(
        ));
    }
}