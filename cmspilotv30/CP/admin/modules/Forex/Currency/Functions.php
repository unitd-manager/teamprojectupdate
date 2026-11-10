<?
class CP_Admin_Modules_Forex_Currency_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('forex_currency');
        $modules->registerModule($modObj, array(
            'actBtnsList' => array('new', 'export', 'import')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('forex_currency', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}