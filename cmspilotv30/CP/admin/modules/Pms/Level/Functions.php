<?
class CP_Admin_Modules_Pms_Level_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_level');
        $modules->registerModule($modObj, array(
            'title' => 'Year'
        ));
    }
}