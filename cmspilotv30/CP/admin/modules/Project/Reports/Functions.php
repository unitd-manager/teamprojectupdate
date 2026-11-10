<?
class CP_Admin_Modules_Project_Reports_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }
}