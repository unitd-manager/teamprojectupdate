<?
class CP_Common_Modules_Ads_Banner_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('ads_banner');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     * @return <type>
     */    
     function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ads_banner', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ads_banner', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}