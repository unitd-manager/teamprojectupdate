<?
class CP_Admin_Modules_Directory_Building_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_building');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'export')
           ,'hasMultiLang' => 1
        ));
    }
}