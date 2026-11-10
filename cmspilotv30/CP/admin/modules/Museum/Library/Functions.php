<?
class CP_Admin_Modules_Museum_Library_Functions extends CP_Common_Modules_Museum_Library_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('museum_library');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList'   => array('new', 'import')
        ));
    }

}