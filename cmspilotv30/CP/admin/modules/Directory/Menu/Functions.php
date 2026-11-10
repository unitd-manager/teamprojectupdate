<?
class CP_Admin_Modules_Directory_Menu_Functions extends CP_Common_Modules_Directory_Menu_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('directory_menu');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList'   => array('new')
        ));
    }
}