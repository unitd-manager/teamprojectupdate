<?
class CP_Admin_Modules_Directory_Borough_Functions extends CP_Common_Modules_Directory_Borough_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_borough');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'export')
           ,'hasMultiLang' => 1
        ));
    }
}