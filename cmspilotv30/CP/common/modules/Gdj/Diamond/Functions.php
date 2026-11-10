<?
class CP_Common_Modules_Gdj_Diamond_Functions
{
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('gdj_diamond');
        $modObj['tableName'] = 'product';
        $modObj['keyField']  = 'product_id';
        $modules->registerModule($modObj, array(
            'moduleGroup'   => 'gdj'
           ,'hasFlagInList' => 0
           ,'hasMultiLang'  => 1
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
        $mediaObj = $mediaArr->getMediaObj('gdj_diamond', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_diamond', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('gdj_diamond', 'certificate', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
}