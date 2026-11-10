<?
class CP_Admin_Modules_Directory_State_Functions extends CP_Common_Modules_Directory_State_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_state');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'export')
           ,'hasMultiLang' => 1
        ));
    }
}