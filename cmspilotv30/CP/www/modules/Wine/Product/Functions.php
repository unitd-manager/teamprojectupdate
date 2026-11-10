<?

class CP_Www_Modules_Wine_Product_Functions {

    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('wine_product');
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
        $mediaObj = $mediaArr->getMediaObj('wine_product', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'count' => 'single'
            ,'isMediaLangSpecific' => false
        ));
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('wine_product', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
            'isMediaLangSpecific' => false
        ));
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('wine_product', 'relatedOffer', 'image');

        $mediaArr->registerMedia($mediaObj, array(
            'isMediaLangSpecific' => false
        ));
    }
}