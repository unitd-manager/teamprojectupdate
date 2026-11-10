<?
class CP_Admin_Modules_Directory_Street_Functions extends CP_Common_Modules_Directory_Street_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_street');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'export')
           ,'hasMultiLang' => 1
        ));
    }
}