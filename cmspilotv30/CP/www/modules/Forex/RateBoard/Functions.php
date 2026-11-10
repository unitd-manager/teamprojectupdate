<?
class CP_Www_Modules_Forex_RateBoard_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('forex_rateBoard');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('forex_currency', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
