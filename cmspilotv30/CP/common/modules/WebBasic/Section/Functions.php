<?
class CP_Common_Modules_WebBasic_Section_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('webBasic_section');
        $modules->registerModule($modObj, array(
        ));
    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_section', 'banner', 'image');

        $mediaArr->registerMedia($mediaObj, array(
             'maxWidthL' => 2000
            ,'maxHeightL' => 2000
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('webBasic_section', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}