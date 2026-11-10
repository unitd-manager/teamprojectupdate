<?
class CP_Admin_Modules_Directory_Reports_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_reports');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'actBtnsList' => array()
           ,'hasMultiLang' => 1
        ));
    }
}