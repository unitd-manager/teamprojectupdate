<?
class CP_Common_Modules_AceIms_Company_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_company');
        $modules->registerModule($modObj, array(
        ));
    }
}