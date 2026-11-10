<?
class CP_Common_Modules_Edukite_Notice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_notice');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukite_notice', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
            'maxWidthL' => 900
           ,'maxHeightL' => 900
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukite_notice', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
    }
}