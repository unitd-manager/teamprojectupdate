<?
class CP_Common_Modules_AgileIms_Company_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_company');
        $modules->registerModule($modObj, array(
        ));
    }
}