<?
class CP_Common_Modules_Museum_Collection_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('museum_collection');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('museum_collection', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
}