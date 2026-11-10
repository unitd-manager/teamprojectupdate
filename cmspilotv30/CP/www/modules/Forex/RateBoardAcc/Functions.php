<?
class CP_Www_Modules_Forex_RateBoardAcc_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('forex_rateBoardAcc');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('account_currency', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj);
    }
}
