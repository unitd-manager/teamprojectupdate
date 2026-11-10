<?
class CP_Www_Modules_WebBasic_Section_Functions
{

    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('webBasic_section');
        $modules->registerModule($modObj, array(
        ));
    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_section', 'banner', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_section', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
