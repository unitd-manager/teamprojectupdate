<?
class CP_Common_Modules_Directory_Menu_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('directory_menu');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_menu', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'count' => 'single'
        ));
    }    
}