<?
class CP_Common_Modules_Museum_Library_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('museum_library');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('museum_library', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
}