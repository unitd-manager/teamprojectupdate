<?
class CP_Common_Modules_Common_site_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('common_site');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $cpCfg = Zend_Registry::get('cpCfg');
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('common_site', 'logo', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('common_site', 'secondLogo', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}