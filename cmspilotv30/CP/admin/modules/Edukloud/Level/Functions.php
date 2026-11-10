<?
class CP_Admin_Modules_Edukloud_Level_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_level');
        $modules->registerModule($modObj, array(
            'title' => 'Year'
        ));
    }
}