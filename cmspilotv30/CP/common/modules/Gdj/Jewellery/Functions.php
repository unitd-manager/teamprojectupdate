<?
class CP_Common_Modules_Gdj_Jewellery_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('gdj_jewellery');
        $modObj['tableName'] = 'product';
        $modObj['keyField']  = 'product_id';
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'actBtnsList'   => array('new', 'import')
        ));
    }
    
    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_jewellery', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_jewellery', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_jewellery', 'certificate', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    
}