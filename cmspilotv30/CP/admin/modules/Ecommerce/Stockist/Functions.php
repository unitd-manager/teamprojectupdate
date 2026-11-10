<?
class CP_Admin_Modules_Ecommerce_Stockist_Functions extends CP_Common_Modules_Ecommerce_Stockist_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ecommerce_stockist');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ecommerce_stockist', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}