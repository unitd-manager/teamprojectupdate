<?
class CP_Admin_Modules_Common_Region_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('common_region');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
        ));
    }
}