<?
class CP_Admin_Modules_Wine_Appellation_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('wine_appellation');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
        ));
    }
}