<?
class CP_Www_Modules_WebBasic_Gallery_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('webBasic_gallery');
        $modules->registerModule($modObj, array(
             'tableName' => 'content'
            ,'keyField'  => 'content_id'
        ));
    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_content', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_content', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
