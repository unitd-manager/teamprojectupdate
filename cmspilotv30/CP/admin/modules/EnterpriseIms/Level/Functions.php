<?
class CP_Admin_Modules_EnterpriseIms_Level_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_level');
        $modules->registerModule($modObj, array(
            'title' => 'Year'
        ));
    }
}